<?php
/**************************************************
  Coppermine 1.5.x Plugin - jquery_update
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

$name = 'jQuery update';
$description = 'Use more recent jQuery versions';
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '0.1';
$plugin_cpg_version = array('min' => '1.5');

$announcement_icon = cpg_fetch_icon('announcement', 1);

$extra_info = $install_info = <<<EOT
    <a href="http://forum.coppermine-gallery.net/index.php/topic,TODO.0.html" rel="external" class="admin_menu">{$announcement_icon}Announcement thread</a>
EOT;

?>