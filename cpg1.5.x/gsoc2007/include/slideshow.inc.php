<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2007 Coppermine Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/
?>

<script language="JavaScript" type="text/JavaScript">
var x1;

x1 = createRequestObject();

function createRequestObject() {
  var x = false;

  try {
    x = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      x = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      x = false;
    }
  }

  if (!x && typeof XMLHttpRequest != "undefined") {
    x = new XMLHttpRequest();
  }

  return x;
}

function addHit(pid) {
  x1.open('get','addHit.php?pid='+pid);
  x1.onreadystatechange = function() {
    if (x1.readyState == 4) {
      return (x1.responseText);
    }
  }
  x1.send(null);
}

// (C) 2000 www.CodeLifter.com
// http://www.codelifter.com
// Free for all users, but leave in this  header
// NS4-6,IE4-6
// Fade effect only in IE; degrades gracefully
// ------------------------------------------------------------------------- //

// Set slideShowSpeed (milliseconds)
var slideShowSpeed = <?php echo (int)$_GET['slideshow'] ?>

// Agent sniffer shamelessly 'stolen' from the excellent X library from cross-browser.com
var xOp7=false,xOp5or6=false,xIE4Up=false,xNN4=false,xUA=navigator.userAgent.toLowerCase();
if(window.opera){
  xOp7=(xUA.indexOf('opera 7')!=-1 || xUA.indexOf('opera/7')!=-1);
  if (!xOp7) xOp5or6=(xUA.indexOf('opera 5')!=-1 || xUA.indexOf('opera/5')!=-1 || xUA.indexOf('opera 6')!=-1 || xUA.indexOf('opera/6')!=-1);
}
else if(document.layers) xNN4=true;
else {xIE4Up=document.all && xUA.indexOf('msie')!=-1 && parseInt(navigator.appVersion)>=4;}

// Duration of crossfade (seconds)
var crossFadeDuration = 3

// Specify the image files
var Pic = new Array() // don't touch this
var Pid = new Array()
var Title = new Array() // don't touch this
// to add more images, just continue
// the pattern, adding to the array below
<?php
$i = 0;
$j = 0;
$pid = (int)$_GET['pid'];
$start_img = '';
$pic_data = get_pic_data($_GET['album'], $pic_count, $album_name, -1, -1, false);
foreach ($pic_data as $picture) {

    if($CONFIG['thumb_use']=='ht' && $picture['pheight'] > $CONFIG['picture_width'] ){ // The wierd comparision is because only picture_width is stored
      $condition = true;
    }elseif($CONFIG['thumb_use']=='wd' && $picture['pwidth'] > $CONFIG['picture_width']){
      $condition = true;
    }elseif($CONFIG['thumb_use']=='any' && max($picture['pwidth'], $picture['pheight']) > $CONFIG['picture_width']){
      $condition = true;
        //thumb cropping
    }elseif($CONFIG['thumb_use']=='ex' && max($picture['pwidth'], $picture['pheight']) > $CONFIG['picture_width']){
      $condition = true;
    }else{
     $condition = false;
    }

    if (is_image($picture['filename'])) {
        if ($CONFIG['make_intermediate'] && $condition ) {
            $picture_url = get_pic_url($picture, 'normal');
        } else {
            $picture_url = get_pic_url($picture, 'fullsize');
        }

        if ( $picture['title'] ) {
            $Title = $picture['title'];
        } else {
            $Title = $picture['filename'];
        }
        echo "Pic[$i] = '" . htmlspecialchars($picture_url, ENT_QUOTES) . "';\n";
        echo "Pid[$i] = '" . $picture['pid'] . "'\n";
        echo "Title[$i] = '" . addslashes($Title) . "';\n";
        if ($picture['pid'] == $pid) {
            $j = $i;
            $start_img = $picture_url;
        }
        $i++;
    }
}
if (!$i) {
    echo "Pic[0] = 'images/thumb_document.jpg'\n";
}
?>

var t
var j = <?php echo "$j\n" ?>
var p = Pic.length
var pos = j

var preLoad = new Array()

function preLoadPic(index)
{
        if (Pic[index] != ''){
                window.status='Loading : '+Pic[index]
                preLoad[index] = new Image()
                preLoad[index].src = Pic[index]
                Pic[index] = ''
                window.status=''
        }
}

function runSlideShow(){
   if (xIE4Up){
            document.images.SlideShow.style.filter="blendTrans(duration=2)"
                document.images.SlideShow.style.filter= "blendTrans(duration=crossFadeDuration)"
      document.images.SlideShow.filters.blendTrans.Apply()
        }
        document.getElementById('Title').innerHTML = Title[j];
        document.images.SlideShow.src = preLoad[j].src
        if (xIE4Up){
           document.images.SlideShow.filters.blendTrans.Play()
        }
        addHit(Pid[j])

        pos = j

        j = j + 1
        if (j > (p-1)) j=0
        t = setTimeout('runSlideShow()', slideShowSpeed)
        preLoadPic(j)
}

function endSlideShow(){
        self.document.location = 'displayimage.php?album=<?php echo isset($_GET['album']) ? $_GET['album'] : '';
echo isset($_GET['cat']) ? '&cat=' . $_GET['cat'] : '';
echo isset($_GET['date']) ? '&date=' . $_GET['date'] : ''; ?>&pos='+pos
}

preLoadPic(j)

</script>