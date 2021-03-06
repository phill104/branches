<?php
/**************************************************
  Coppermine 1.5.x Plugin - move_to_public
  *************************************************
  Copyright (c) 2010 eenemeenemuu
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

if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

if (GALLERY_ADMIN_MODE) {
    $thisplugin->add_filter('admin_menu','move_to_public_add_admin_button');
}

function move_to_public_add_admin_button($admin_menu) {
    global $lang_plugin_move_to_public, $move_to_public_icon_array;
    require_once './plugins/move_to_public/init.inc.php';
    $move_to_public_init_array = move_to_public_initialize();
    $lang_plugin_move_to_public = $move_to_public_init_array['language']; 
    $move_to_public_icon_array = $move_to_public_init_array['icon'];
    $new_button = '<div class="admin_menu admin_float"><a href="index.php?file=move_to_public/index" title="' . $lang_plugin_move_to_public['description'] . '">'. $move_to_public_icon_array['menu'] . $lang_plugin_move_to_public['admin_title'] . '</a></div>';
    $look_for = '<!-- END albmgr -->';
    $admin_menu = str_replace($look_for, $look_for . $new_button, $admin_menu);
    return $admin_menu;
}

?>
