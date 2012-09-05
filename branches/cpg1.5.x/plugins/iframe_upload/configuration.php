<?php
/**************************************************
  Coppermine 1.5.x Plugin - iframe_upload
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

$name = 'Iframe Upload';
$description = 'Upload videos from video file hosters to your gallery (YouTube, Google, Yahoo!, ...)';
$author = '<a href="http://forum.coppermine-gallery.net/index.php?action=profile;u=24278" rel="external" class="external">eenemeenemuu</a>';
$version = '1.0';
$plugin_cpg_version = array('min' => '1.5');
$announcement_icon = cpg_fetch_icon('announcement', 1);

$extra_info = <<<EOT
    <a href="http://forum.coppermine-gallery.net/index.php/topic,TODO.0.html" rel="external" class="admin_menu">{$announcement_icon}Announcement thread</a>
EOT;

$install_info = <<<EOT
    <a href="http://forum.coppermine-gallery.net/index.php/topic,TODO.0.html" rel="external" class="admin_menu">{$announcement_icon}Announcement thread</a>
    <strong>Please consider your country's law, if you are liable when embedding content. Also consider, that everyone who's allowed to upload files to your gallery could embed <u>malicious content</u> to the iframe.</strong>
EOT;

?>
