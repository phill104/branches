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
  Coppermine version: 1.5.x
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/
/*********************************************
  Coppermine Plugin - Custom Thumbnail
  ********************************************
  Copyright (c) 2009 eenemeenemuu
**********************************************/

require_once "./plugins/custom_thumb/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/custom_thumb/lang/{$CONFIG['lang']}.php")) {
    require_once "./plugins/custom_thumb/lang/{$CONFIG['lang']}.php";
}

$name = 'Custom Thumbnail';
$description = $lang_plugin_custom_thumb['description'];
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '1.2';
$extra_info = $install_info = '<a href="http://forum.coppermine-gallery.net/index.php/topic,60272.0.html" rel="external" class="admin_menu external">'.cpg_fetch_icon('announcement', 1).'Announcement thread for '.$name.' plugin</a>';

?>
