<?php
/**************************************************
  Coppermine 1.5.x plugin - hidden_features
  *************************************************
  Copyright (c) 2014 eenemeenemuu
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

require "./plugins/hidden_features/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/hidden_features/lang/{$CONFIG['lang']}.php")) {
    require "./plugins/hidden_features/lang/{$CONFIG['lang']}.php";
}

$name = $lang_plugin_hidden_features['hidden_features'];
$description = $lang_plugin_hidden_features['description'];
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '1.0';
$plugin_cpg_version = array('min' => '1.5');


$install_info = '<a href="http://forum.coppermine-gallery.net/index.php/topic,TODO.0.html" rel="external" class="admin_menu">'.cpg_fetch_icon('announcement', 1).$lang_plugin_hidden_features['announcement_thread'].'</a>';
$extra_info = '<a href="index.php?file=hidden_features/admin" class="admin_menu">'.cpg_fetch_icon('config', 1).$name.' '.$lang_gallery_admin_menu['admin_lnk'].'</a>'.$install_info;

?>