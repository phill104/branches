<?php
 /** 
 * BrainFeeder for CPG
 * Version 1.0 RC
 * written  by Hallvard Natvik <hallvard@natvik.com>
 * Send me a mail if you use it
 * 
 * This is free software made available to you without any guarantees or obligations
 * You can use this sofware anyway you like.
 *------------------------------------------------------------
 *  
 * This file, rss.php, should be installed in your main coppermine folder or directory (same place as Coppermine's index.php)
 * Realtime mode: The feed is generated on-the-fly when the RSS aggregator client contacts this script. Only one feed can be generated.
 * Batch mode: The feeds are generated by a cron job or by manually running this script. All batch feeds are generated.
 * When operating in Batch mode, you save server resources. You should give the URL to the output XML-file to your subscribers, 
 * not the URL to this script to achieve this. The XML file will be written to the coppermine directory if no path is given in the
 * configuration screen. Make sure that this script has writing rights to the directory.
 * 
 * By using "...?feed=<feed name>" you can actually run a Batch feed in Realtime
 * 
 * Start-up syntax: 
 * host.com/rss.php -> runs all batch feeds (ie. generating rss files) ($run_type 0)
 * host.com/rss.php?feed=news -> generates the feed named 'news' to the browser (realtime $run_type 1)
 * host.com/rss.php?fid=1 -> generates feed number one to the browser (realtime $run_type 2) 
 */
 
 $x=0;
 define ('IN_COPPERMINE', TRUE);
 define ('GENERATOR', "BrainFeeder for Coppermine 1.2 by Hallvard Natvik");
 require_once('include/init.inc.php');
 require_once ('plugins/brainfeeder/init.inc.php'); 
 require_once ('plugins/brainfeeder/brainfeeder.php'); 
 include("include/exif_php.inc.php"); 
 $feed_name="";
 $feed_id=0;
 $run_type =0;
 $random=FALSE;
 $where ="WHERE feed_mode ='Batch'";
 $feed_mode = TRUE;
 $channel_image = FALSE;
 $rss_filename ="";

$lang_picinfo = array(
  'title' =>'File information',
  'Filename' => 'Filename',
  'Album name' => 'Album name',
  'Rating' => 'Rating (%s votes)',
  'Keywords' => 'Keywords',
  'File Size' => 'File Size',
  'Date Added' => 'Date added', //cpg1.4
  'Dimensions' => 'Dimensions',
  'Displayed' => 'Displayed',
  'URL' => 'URL', //cpg1.4
  'Make' => 'Make', //cpg1.4
  'Model' => 'Model', //cpg1.4
  'DateTime' => 'Date Time', //cpg1.4
  'ISOSpeedRatings'=>'ISO', //cpg1.4
  'MaxApertureValue' => 'Max Aperture', //cpg1.4
  'FocalLength' => 'Focal length', //cpg1.4
  'Comment' => 'Comment',
  'addFav'=>'Add to Favorites',
  'addFavPhrase'=>'Favorites',
  'remFav'=>'Remove from Favorites',
  'iptcTitle'=>'IPTC Title',
  'iptcCopyright'=>'IPTC Copyright',
  'iptcKeywords'=>'IPTC Keywords',
  'iptcCategory'=>'IPTC Category',
  'iptcSubCategories'=>'IPTC Sub Categories',
  'ColorSpace' => 'Color Space', //cpg1.4
  'ExposureProgram' => 'Exposure Program', //cpg1.4
  'Flash' => 'Flash', //cpg1.4
  'MeteringMode' => 'Metering Mode', //cpg1.4
  'ExposureTime' => 'Exposure Time', //cpg1.4
  'ExposureBiasValue' => 'Exposure Bias', //cpg1.4
  'ImageDescription' => ' Image Description', //cpg1.4
  'Orientation' => 'Orientation', //cpg1.4
  'xResolution' => 'X Resolution', //cpg1.4
  'yResolution' => 'Y Resolution', //cpg1.4
  'ResolutionUnit' => 'Resolution Unit', //cpg1.4
  'Software' => 'Software', //cpg1.4
  'YCbCrPositioning' => 'YCbCrPositioning', //cpg1.4
  'ExifOffset' => 'Exif Offset', //cpg1.4
  'IFD1Offset' => 'IFD1 Offset', //cpg1.4
  'FNumber' => 'FNumber', //cpg1.4
  'ExifVersion' => 'Exif Version', //cpg1.4
  'DateTimeOriginal' => 'DateTime Original', //cpg1.4
  'DateTimedigitized' => 'DateTime digitized', //cpg1.4
  'ComponentsConfiguration' => 'Components Configuration', //cpg1.4
  'CompressedBitsPerPixel' => 'Compressed Bits Per Pixel', //cpg1.4
  'LightSource' => 'Light Source', //cpg1.4
  'ISOSetting' => 'ISO Setting', //cpg1.4
  'ColorMode' => 'Color Mode', //cpg1.4
  'Quality' => 'Quality', //cpg1.4
  'ImageSharpening' => 'Image Sharpening', //cpg1.4
  'FocusMode' => 'Focus Mode', //cpg1.4
  'FlashSetting' => 'Flash Setting', //cpg1.4
  'ISOSelection' => 'ISO Selection', //cpg1.4
  'ImageAdjustment' => 'Image Adjustment', //cpg1.4
  'Adapter' => 'Adapter', //cpg1.4
  'ManualFocusDistance' => 'Manual Focus Distance', //cpg1.4
  'DigitalZoom' => 'Digital Zoom', //cpg1.4
  'AFFocusPosition' => 'AF Focus Position', //cpg1.4
  'Saturation' => 'Saturation', //cpg1.4
  'NoiseReduction' => 'Noise Reduction', //cpg1.4
  'FlashPixVersion' => 'Flash Pix Version', //cpg1.4
  'ExifImageWidth' => 'Exif Image Width', //cpg1.4
  'ExifImageHeight' => 'Exif Image Height', //cpg1.4
  'ExifInteroperabilityOffset' => 'Exif Interoperability Offset', //cpg1.4
  'FileSource' => 'File Source', //cpg1.4
  'SceneType' => 'Scene Type', //cpg1.4
  'CustomerRender' => 'Customer Render', //cpg1.4
  'ExposureMode' => 'Exposure Mode', //cpg1.4
  'WhiteBalance' => 'White Balance', //cpg1.4
  'DigitalZoomRatio' => 'Digital Zoom Ratio', //cpg1.4
  'SceneCaptureMode' => 'Scene Capture Mode', //cpg1.4
  'GainControl' => 'Gain Control', //cpg1.4
  'Contrast' => 'Contrast', //cpg1.4
  'Sharpness' => 'Sharpness', //cpg1.4
  'ManageExifDisplay' => 'Manage Exif Display', //cpg1.4
  'submit' => 'Submit', //cpg1.4
  'success' => 'Information updated successfully.', //cpg1.4
  'details' => 'Details', //cpg1.4
);

 
 if (isset($_GET['feed'])) {
     $feed_name=$_GET['feed'];
     $where = "WHERE feed_name='".$feed_name."'";
     $feed_mode = FALSE;
     $run_type= 1;
 } elseif (isset($_GET['fid'])) {
     $fid=$_GET['fid'];
     $where = "WHERE fid=".$fid;
     $feed_mode = FALSE;
     $run_type= 2;
 }
 
 //logg("runtype: ".$run_type);
 
$query = "SELECT * FROM ".$CONFIG['TABLE_brainfeeder']." ".$where;
//logg("\n1:".$query);
$result = cpg_db_query($query);
$rows = cpg_db_fetch_rowset($result);

$pic_fields = "*, pic.title as title, concat(filepath, filename) as file, 
                    concat(filepath,'".$CONFIG['thumb_pfx'] ."', filename) as thumb, 
                    concat(filepath,'".$CONFIG['normal_pfx'] ."', filename) as normal, 
                    SUBSTRING_INDEX(filename,'.',-1) as mimetype";
    $alb_fields = "alb.title as atitle, description, visibility, category";
    $base_query = "SELECT ".$pic_fields.", ".$alb_fields." 
                    FROM ".$CONFIG['TABLE_PICTURES']." pic, ".$CONFIG['TABLE_ALBUMS']." alb, ".$CONFIG['TABLE_FILETYPES']." mtypes  
                    WHERE alb.aid = pic.aid and approved ='YES' and SUBSTRING_INDEX(filename,'.',-1)= extension";
    $restr_visibility = " AND visibility = 0 ";
    $onlypic = " AND content = 'image' ";
    $picvid = " AND (content = 'image' OR content ='movie') ";
foreach ($rows as $row)  {         // for each feed
    $random = FALSE;
    if (isset($row['logo_keyw']) && strlen(trim($row['logo_keyw'])) >0) {
        $logo_row=get_by_keyword($row['logo_keyw'],1, FALSE);
        $thumb_url = $CONFIG['ecards_more_pic_target'].$CONFIG['fullpath'].$logo_row[0]['thumb'];
    }
    if (isset($row['feed_incl_restr']) && $row['feed_incl_restr']=="No" ) $base_query .= $restr_visibility;
     
    if (isset($row['feed_random']) && $row['feed_random']=="Random" ) $random = TRUE;
     
    switch ($row['feed_media']) {
        case "Pictures":
            $base_query .= $onlypic;
            break;
        case "Pictures and video": 
            $base_query .= $picvid;
            break;
    }
    switch ($row['feed_type']) {
        case "Any":
            $pic_array = get_any_pics($row['items_to_include'],$random);
            break;
        case "Keyword":
            $pic_array = get_by_keyword($row['feed_parameter'], $row['items_to_include'],$random);
            break;
        case "Album";
            $pic_array = get_by_aid($row['feed_parameter'], $row['items_to_include'],$random);
            break;
        case "Category";
            $pic_array = get_by_catid($row['feed_parameter'], $row['items_to_include'],$random);
            break;
        case "Hits";
            $pic_array = get_by_hits($row['items_to_include'],$random);
            break;
        case "Rating";
            $pic_array = get_by_rating($row['items_to_include'],$random);
            break;
    }
//logg("\nfeedmode: ".$feed_mode)   ;
    $rss_channel = new feed_generator($feed_mode,"2.0",$CONFIG['charset']);
    $rss_channel->set_permalink(FALSE);
    
    //$rss_channel->debug=TRUE;
    $channel = array (
        'title' =>$row['feed_title'],
        'link' =>$row['feed_link'],
        'description' =>$row['feed_description'],
        'copyright' =>$row['feed_copyright'],
        'language' =>'en-us',
        'ttl' =>720,
        'category'=> $row['feed_keywords'],
        'generator' =>GENERATOR,
        'managingEditor' =>$CONFIG['gallery_admin_email'],
        'webMaster' =>$CONFIG['gallery_admin_email'],
        'pubDate' =>date(DATE_RFC822),
        'lastBuildDate' =>date(DATE_RFC822)
        );
    
//    logg(str_replace(",","\n",json_encode($channel)));

    $channel_image = array (
        'url' =>$thumb_url,
        'link' =>$row['feed_link'],
        'title'=> $row['feed_title'],
        'description'=> "",
        );
//    logg(str_replace(",","\n",json_encode($channel_image)));

    $rss_channel->build_channel($channel);
    $rss_channel->set_channel_image($channel_image);
    //logg ("\n\nChannel: ");
//    logg(str_replace(",","\n",json_encode($rss_channel->channel)));  

    foreach ($pic_array as $pic) {  //for each picture that goes into the feed
        //print_r ($pic);
        if (strlen(trim($pic['title']))<1) {
            $pic['title']=$row['item_def_title'];
        }
        $item = array(
        'title'=> strip_tags(bb_decode($pic['title'])),
        'author'=>$pic['owner_name'],
        'ttl'=>$pic['feed_refresh'],
        'link'=>$CONFIG['ecards_more_pic_target']."displayimage.php?pos=-".$pic['pid'],
        'guid'=>$CONFIG['ecards_more_pic_target']."displayimage.php?pos=-".$pic['pid'],
        'description'=>make_pic_descr($pic,$row) 
        );
        if ($row['feed_enclosure']!="No") {
            $pic_url=make_pic_url($pic,$row['feed_enclosure'], TRUE);
           // die ($pic_url);
            $item['enclosure'] =$pic_url;
            //logg("\npicurl: ".$pic_url);
        }
        if ($row['feed_source']=="Yes") $item['source'] =$CONFIG['ecards_more_pic_target'];
        if ($row['feed_category_opt']=="Yes") {
         $item['category']=$pic['keywords'];
        }
        $rss_channel->add_items($item);
    }
    $rss_filename = $row['file_name'];
    $rss_channel->createFeed();
}

function get_any_pics($number=10,$random) {
    global $base_query;
    $in=" AND pid IN ";
    $query = $base_query;
    $order = " ORDER BY ctime desc";
    if ($random) {
        $limit = " LIMIT 1000";
    } else {
        $limit .= " LIMIT $number";
    }
    //logg("\n2:".$query);
    $result = cpg_db_query($query.$order.$limit); 
    if ($random) {
        $in .= randomizer($result, $number);
       // die ($query.$in.$order);
        $result = cpg_db_query($query.$in.$order);
    } 
    return cpg_db_fetch_rowset($result);
}

function get_by_keyword($keyword, $number=10,$random) {
    global $base_query;
    $in=" AND pid IN ";
    $query = $base_query." and keywords LIKE '%$keyword%' ";
    $order =" ORDER BY ctime desc";
    if ($random) {
        $limit = " LIMIT 1000";
    } else {
        $limit .= " LIMIT $number";
    }
    //logg("\n3:".$query);
    $result = cpg_db_query($query.$order.$limit); 
    if ($random) {
        $in .= randomizer($result, $number);
       // die ($query.$in.$order);
        $result = cpg_db_query($query.$in.$order);
    }
    return cpg_db_fetch_rowset($result);
}

function get_by_aid($aid, $number=10,$random) {
    global $base_query;
    $in=" AND pid IN ";
    $query = $base_query." and pic.aid=$aid";
    $order =" ORDER BY ctime desc";
    if ($random) {
        $limit = " LIMIT 1000";
    } else {
        $limit .= " LIMIT $number";
    }
    //logg("\n4:".$query);
    $result = cpg_db_query($query.$order.$limit); 
    if ($random) {
        $in .= randomizer($result, $number);
       // die ($query.$in.$order);
        $result = cpg_db_query($query.$in.$order);
    }
    return cpg_db_fetch_rowset($result);
}

function get_by_catid($catid, $number=10,$random) {
    global $CONFIG;
    global $base_query;
    $in=" AND pid IN ";
    $query = $base_query." and (category=$catid OR category in (SELECT cid from ".$CONFIG['TABLE_CATEGORIES']." WHERE parent=".$catid. " ))";
    $order =" ORDER BY ctime desc";
    if ($random) {
        $limit = " LIMIT 1000";
    } else {
        $limit .= " LIMIT $number";
    }
    //logg("\n5:".$query);
    $result = cpg_db_query($query.$order.$limit); 
    if ($random) {
        $in .= randomizer($result, $number);
       // die ($query.$in.$order);
        $result = cpg_db_query($query.$in.$order);
    }
    return cpg_db_fetch_rowset($result);
}

function get_by_hits($number=10,$random) {
    global $CONFIG;
    global $base_query;
    $query = $base_query." ORDER BY hits desc LIMIT $number";
    //logg("\n5:".$query);
    $result = cpg_db_query($query); 
    return cpg_db_fetch_rowset($result);
}

function get_by_rating($number=10,$random) {
    global $CONFIG;
    global $base_query;
    $query = $base_query." ORDER BY pic_rating desc LIMIT $number";
    //logg("\n5:".$query);
    $result = cpg_db_query($query); 
    return cpg_db_fetch_rowset($result);
}

function make_pic_descr($pic, $row){
    global $CONFIG;
    $txt = "";
    $breadcrumb =" ";
    $ret = "<table><tr><td>";
    $pic_url = make_pic_url($pic,$size, FALSE);
    $size = isset($row['pic_size']) ? $row['pic_size'] : "Thumb";
    $comments = isset($row['item_comments']) ? $row['item_comments'] : "No";
    $content = isset($row['item_content']) ? $row['item_content'] : "&nbsp;";
    
    switch ($size) {
        case "Normal":
            $width = $CONFIG['picture_width'];
            break;
        case "Thumb":
            $width = $CONFIG['thumb_width'];
            break;
    }
    $ret .= "<a href =\"".$CONFIG['ecards_more_pic_target']."displayimage.php?pos=-".$pic['pid']."\">";
    $ret .= "<img src=\"".$pic_url."\" width=\"".$width."\" alt=\"RSS image\" /></a></td>";
   $n =0;
   $content = "x".$content;    //x to avoid hitting anchor in first position (0= false)
    while ($pos = strpos($content,"{")) {
        $n++;
        $len = strpos($content,"}") - $pos + 1;
        $cmd = substr($content,$pos,$len);
        $exif = exif_parse_file("albums/".$pic['file']);
        switch ($cmd) {
            case "{br}":
                $content = str_replace($cmd,"<br />",$content);
                break;
            case "{adate}":
                $content = str_replace($cmd,strftime("%x",$pic['ctime']),$content);
                break;
            case "{album}":
                $content = str_replace($cmd,$pic['atitle'],$content);
                break;
            case "{fname}":
                $content = str_replace($cmd,$pic['filename'],$content);
                break;
            case "{fsize}":
                $content = str_replace($cmd,intval($pic['filesize']/1024)." kB",$content);
                break;
            case "{title}":
                $content = str_replace($cmd,$pic['title'],$content);
                break;
            case "{hits}":
                $content = str_replace($cmd,$pic['hits'],$content);
                break;
            case "{rating}":
                $content = str_replace($cmd,$pic['rating'],$content);
                break;
            case "{descr}":
                $content = str_replace($cmd,$pic['caption'],$content);
                break;
            case "{miniCMS}":
                if (isset($CONFIG['TABLE_CMS'])) $txt = getMiniCMS($pic['pid']);
                $content = str_replace($cmd,$txt,$content);
                break;
            case "{owner}":
                $content = str_replace($cmd,$pic['owner_name'],$content);    
                break;
            case "{cat}":
                 $i=$pic['category'];
                 if (!isset($alb_name[$i])) {
                    $query = "SELECT title FROM ".$CONFIG['TABLE_ALBUMS']." WHERE aid=".$pic['category'];
                    if ($result = cpg_db_query($query)) {
                        $title = cpg_db_fetch_row($result);
                        $alb_name[$pic['category']] = $title['title'];
                    } 
                }    
                $name =$alb_name[$pic['category']] ;
                $content = str_replace($cmd,$name,$content);
                break;
            case "{bread}":
                $breadcrumb = " ";
                breadcrumb($pic['category'], $breadcrumb, $BREADCRUMB_TEXT);
                $content = str_replace($cmd,$breadcrumb,$content);    
                break;
            case "{make}":
                $exif = exif_parse_file("albums/".$pic['file']);
                $param = substr($exif['Make'],0,-1);
                $content = str_replace($cmd,$param,$content);    
                break;
            case "{model}":
                $param = substr($exif['Model'],0,-1);
                $content = str_replace($cmd,$param,$content);    
                break;
            case "{keyw}":
                  $content = str_replace($cmd,$pic['keywords'],$content);
                break;
            case "{date}":
                $param = substr($exif['DateTime Original'],0,-1);
                $content = str_replace($cmd,$param,$content);    
                break;
            default:
            /*print_r ($exif);
            die();*/
                $lookup= substr($cmd,1,-1);
                if (isset($exif[$lookup])) {
                    $param = $exif[$lookup];
                    if (!ord(substr($param,-1))) {
                        $param = substr($param,0,-1);  //some exif values ends with a ascii 0 character
                    }
                } else {
                    $param = substr($cmd,1,-1)." not valid anchor";
                   // $param = $cmd." not valid anchor";
                }
                $content = str_replace($cmd,$param,$content);
                
                break;
        }
      //  if ($n>5) die ("ut: ".$content);
    } 
    $content = substr($content,1);
    
    $ret .= "<td valign=\"top\">".$content."</td></tr>";
    if ($comments=="Yes") {
        $coms = get_pic_comments($pic['pid']);
        if (count($coms)>0) {
            foreach ($coms as $com) {
                $ret .= "<tr><td>&nbsp</td><td><p>".$com['msg_author'].": ".$com['msg_body']."</td></tr>";
            }
        }
    }
    $ret .= "</table>";
    return bb_decode($ret);
}

function randomizer($result,$number) {
    $i=$t=0;
    $found = FALSE;
    $ret ="(";
    $pics = cpg_db_fetch_rowset($result);
    $max = count($pics)-1;
    while ($i<$number) {
        while (!$found && count($pics)>0) {
            $w = rand(0,$max);
            if (isset($pics[$w])) {
                $found = TRUE;
                $ret .=$pics[$w]['pid'].",";
                unset ($pics[$w]);
            }
         $t++;  //counts how many retries to find the next available picture
         if ($t==40) $found = TRUE; //to avoid too many loops on small data sets
        }
        $found = FALSE;
        $t = 0;
        $i++;
    }
    $ret = substr_replace($ret,')',-1,1);
    return $ret;
}

function logg ($txt) {
    $hndl=fopen("queries.txt","a");
    fwrite($hndl,$txt);
    fclose($hndl);
}

function make_pic_url($pic, $size,$attach=FALSE) {
    global $CONFIG;
    $extension = pathinfo($pic['file']);
    $extension = $extension['extension'];
    if (stripos("0jpgjpegpngbmpgif", $extension) || $attach) {
        $picture = TRUE;
        switch ($size) {
            case "Thumb": 
                $filename = $pic['thumb'];
                break;
            case "Normal":
                $filename = $pic['normal'];
                if (!file_exists($CONFIG['fullpath'].$filename)) $filename = $pic['file'];
                break;
            case "Original":
                $filename = $pic['file'];
                break;
            default:
                $filename = $pic['normal'];
                if (!file_exists($CONFIG['fullpath'].$filename)) $filename = $pic['file'];
                break;
        }
    } else {
        $picture = FALSE;
        $filename ="images/thumb_".strtolower($extension).".jpg";
        if (!file_exists($filename)) $filename = "images/thumb_document.jpg";
    }
    if ($picture) {
        $filename = $CONFIG['fullpath'].$filename; 
    } 
    $path_arr = explode("/",$filename);
    $c = count($path_arr);
    $i=0;
    $pathstr ="";
    for ($i=0;$i<$c;$i++) {
       $pathstr .= str_replace("+"," ",urlencode($path_arr[$i]))."/";
    }
    $pathstr = substr($pathstr,0,-1); //remove trailing "/"
    $retval = $CONFIG['ecards_more_pic_target'].$pathstr; //.$filename;
    return $retval;

}

function get_pic_comments ($pid) {
    global $CONFIG;
    $comment_query = "SELECT msg_author, msg_date, msg_body FROM ".$CONFIG['TABLE_COMMENTS']." WHERE pid=".$pid." LIMIT 5";
    logg($comment_query);
    $result = cpg_db_query($comment_query);
    $rows = cpg_db_fetch_rowset($result);
    return $rows;
}

function getMiniCMS($pid) {
    global $CONFIG;
    $cmsquery = "SELECT content FROM ".$CONFIG['TABLE_CMS']." WHERE type = 2 and conid= ".$pid;
    if ($result = cpg_db_query($cmsquery)) {
        $txt = cpg_db_fetch_row($result);   
        $txt = html_entity_decode($txt['content']);
    } else $txt ="&nbsp;";
    return $txt;
}
?>
