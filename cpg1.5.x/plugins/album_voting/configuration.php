<?php
/**************************************************
  Coppermine 1.5.x Plugin - Vote for albums
  *************************************************
  Copyright (c) 2012 eenemeenemuu
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ********************************************
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**************************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

require "./plugins/album_voting/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/album_voting/lang/{$CONFIG['lang']}.php")) {
    require "./plugins/album_voting/lang/{$CONFIG['lang']}.php";
}

$name = $lang_plugin_album_voting['album_voting'];
$description = $lang_plugin_album_voting['description'];
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '1.1';
$plugin_cpg_version = array('min' => '1.5.20');
$extra_info = $install_info = '<a href="http://forum.coppermine-gallery.net/index.php/topic,74691.0.html" rel="external" class="admin_menu">'.cpg_fetch_icon('announcement', 1).$lang_plugin_album_voting['announcement_thread'].'</a>';

?>