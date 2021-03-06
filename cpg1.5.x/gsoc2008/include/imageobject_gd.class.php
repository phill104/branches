<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2008 Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.
  
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

// OVI - temporary (?) Ubuntu PHP bugfix: https://bugs.launchpad.net/ubuntu/+source/php5/+bug/39719
// taken from http://www.php.net/manual/en/function.imagerotate.php#82261
// START
if(!function_exists("imagerotate"))
{

    function imagerotate($src_img, $angle)
    {
        $src_x = imagesx($src_img);
        $src_y = imagesy($src_img);
        if ($angle == 180)
        {
            $dest_x = $src_x;
            $dest_y = $src_y;
        }
        elseif (($angle == 90) || ($angle == 270))
        {
            $dest_x = $src_y;
            $dest_y = $src_x;
        }
        else
        {
            return $src_img;
        }
       
        $rotate=imagecreatetruecolor($dest_x,$dest_y);
        imagealphablending($rotate, false);
              
        switch ($angle)
        {
            case 270:
                $dest_x--;
                for ($y = 0; $y < $src_y; $y++)
                    for ($x = 0; $x < $src_x; $x++)
                        imagesetpixel($rotate, $dest_x - $y, $x, imagecolorat($src_img, $x, $y));
                break;
            case 90:
                $dest_y--;
                for ($y = 0; $y < $src_y; $y++)
                    for ($x = 0; $x < $src_x; $x++)
                        imagesetpixel($rotate, $y, $dest_y - $x, imagecolorat($src_img, $x, $y));
                break;
            case 180:
                $dest_x--;
                $dest_y--;
                for ($y = 0; $y < $src_y; $y++)
                    for ($x = 0; $x < $src_x; $x++)
                        imagesetpixel($rotate, $dest_x - $x, $dest_y - $y, imagecolorat($src_img, $x, $y));
                break;
        }
        return $rotate;
    }
}
// OVI - END

class imageObject{

         // image resource
         var $imgRes;
         // px
         var $height=0;
         var $width=0;
         // for img height/width tags
         var $string;
         // output report or error message
         var $message;
         // file + dir
         var $directory;
         var $filename;
         // output quality, 0 - 100
         var $quality;
         // truecolor available, boolean
         var $truecolor;

         //constructor
         function imageObject($directory,$filename,$previous=null)
        {
        $this->directory = $directory;
        $this->filename = $filename;
        $this->previous = $previous;
        $this->imgRes = $previous->imgRes;
        if (file_exists($directory.$filename)){
                        $this->filesize = round(filesize($directory.$filename)/1000);
                        if($this->filesize>0){
                                $size = @GetImageSize($directory.$filename);
                                if ($size && !$this->imgRes) {
                                        $this->imgRes = $this->getimgRes($directory.$filename,$size[2]);
                                }
                                if (function_exists("imagecreatetruecolor")){
                                        $this->truecolor = true;
                                }
                                $this->width = $size[0];
                                $this->height = $size[1];
                                $this->string = $size[3];
                                }
                        }// if
        }// constructor

         // private methods
         function getimgRes($name,&$type)
         {
           switch ($type){
              case 1:
              $im = imagecreatefromgif($name);
              break;
              case 2:
              $im = imagecreatefromjpeg($name);
              break;
              case 3:
              $im = imagecreatefrompng($name);
              break;
                      }
           return $im;
         }


         function createUnique(&$imgnew)
         {
           srand((double)microtime()*100000);
           $unique_str = "temp_".md5(rand(0,999999)).".jpg";
           @imagejpeg($imgnew,$this->directory.$unique_str,$this->quality);
           @imagedestroy($this->imgRes);
           //Don't clutter with old images
           @unlink($this->directory.$this->filename);
           //Create a new ImageObject
           return new imageObject($this->directory,$unique_str,$imgnew);
         }

         function createImage($new_w,$new_h)
         {
           if (function_exists("imagecreatetruecolor")){
             $retval = @imagecreatetruecolor($new_w,$new_h);
           }
           if (!$retval) $retval = imagecreate($new_w,$new_h);
           return $retval;
         }

         function cropImage(&$clipval)
         {
             $cliparray = split(",",$clipval);
             $clip_top = $cliparray[0];
             $clip_right = $cliparray[1];
             $clip_bottom = $cliparray[2];
             $clip_left = $cliparray[3];

             $new_w = $clip_right - $clip_left;
             $new_h = $clip_bottom - $clip_top;

             $dst_img = $this->createImage($new_w,$new_h);

             $result = @imagecopyresampled($dst_img, $this->imgRes, 0,0,$clip_left, $clip_top,$new_w, $new_h, $new_w, $new_h);
             if (!$result) $result = @imagecopyresized($dst_img, $this->imgRes, 0,0,$clip_left, $clip_top,$new_w, $new_h, $new_w, $new_h);

             return $this->createUnique($dst_img);

         }

         function rotateImage(&$angle){

          if ($angle == 180){
              $dst_img = @imagerotate($this->imgRes, $angle, 0);
          }else{
                  $width = imagesx($this->imgRes);
                  $height = imagesy($this->imgRes);
                  if ($width > $height){
                      $size = $width;
                      }else{
                      $size = $height;
                  }

                  $dst_img = $this->createImage($size, $size);
                  imagecopy($dst_img, $this->imgRes, 0, 0, 0, 0, $width, $height);
                  $dst_img = @imagerotate($dst_img, $angle, 0);
                  $this->imgRes = $dst_img;
                  $dst_img = $this->createImage($height, $width);

                  if ((($angle == 90) && ($width > $height)) || (($angle == 270) && ($width < $height))){
                          imagecopy($dst_img, $this->imgRes, 0, 0, 0, 0, $size, $size);

                  }

                  if ((($angle == 270) && ($width > $height)) || (($angle == 90) && ($width < $height))){
                          imagecopy($dst_img, $this->imgRes, 0, 0, $size - $height, $size - $width, $size, $size);
                  }
          }

           return $this->createUnique($dst_img);
         }



         function resizeImage($new_w=0,$new_h=0){

             $dst_img = $this->createImage($new_w,$new_h);

             $result = @imagecopyresampled($dst_img, $this->imgRes, 0, 0, 0, 0, $new_w, $new_h, $this->width,$this->height);
             if (!$result) $result = @imagecopyresized($dst_img, $this->imgRes, 0, 0, 0, 0, $new_w, $new_h, $this->width,$this->height);
             return $this->createUnique($dst_img);

         }


         function saveImage(){

         }

   }
 ?>
