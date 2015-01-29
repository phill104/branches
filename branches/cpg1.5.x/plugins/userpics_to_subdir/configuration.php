<?php
/**************************************************
  Coppermine 1.5.x Plugin - Userpics to sub-directory
  *************************************************
  Copyright (c) 2015 eenemeenemuu
  *************************************************
  $HeadURL$
  $Revision$
  $LastChangedBy: eenemeenemuu $
  $Date$
**************************************************/

$name = 'Userpics to sub-directory';
$description = 'Move existing pictures in "'.$CONFIG['fullpath'].$CONFIG['userpics'].'1xxxx/" to a sub-directory named according to the album ID they where uploaded to';
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '0.1';
$plugin_cpg_version = array('min' => '1.5');
$extra_info = $install_info = '<a href="http://forum.coppermine-gallery.net/index.php/topic,TODO.0.html" rel="external" class="admin_menu">'.cpg_fetch_icon('announcement', 1).'Announcement thread</a>';
$extra_info .= '<a href="index.php?file=userpics_to_subdir/move" class="admin_menu">'.cpg_fetch_icon('rightright', 1).'Move userpics to sub-directory</a>';;

?>