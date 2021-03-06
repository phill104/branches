<?php
/**************************************************
  Coppermine 1.5.x Plugin - Problem Solving CAPTCHA
  *************************************************
  Copyright (c) 2013 eenemeenemuu
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

require_once "./plugins/problem_solving_captcha/lang/english.php";
if ($CONFIG['lang'] != 'english' && file_exists("./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php")) {
    require_once "./plugins/problem_solving_captcha/lang/{$CONFIG['lang']}.php";
}

$name = $lang_plugin_problem_solving_captcha['problem_solving_captcha'];
$description = $lang_plugin_problem_solving_captcha['description'];
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '1.0';
$plugin_cpg_version = array('min' => '1.5');
$extra_info = '<a href="index.php?file=problem_solving_captcha/admin" class="admin_menu">'.cpg_fetch_icon('config', 1).$name.' '.$lang_gallery_admin_menu['admin_lnk'].'</a>';
$extra_info .= $install_info = '<a href="http://forum.coppermine-gallery.net/index.php/topic,76723.0.html" rel="external" class="admin_menu">'.cpg_fetch_icon('announcement', 1).$lang_plugin_problem_solving_captcha['announcement_thread'].'</a>';

?>