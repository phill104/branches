<?php
/**************************************************
  Coppermine 1.5.x Plugin - Compress HTML
  *************************************************
  Copyright (c) 2010 Timos-Welt (www.timos-welt.de)
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
  
$name='CompressHTML';
$description='<p>THIS PLUGIN MUST BE THE LAST IN THE CHAIN! MOVE ALL WAY DOWNWARDS IN PLUGIN MANAGER!</p>';
$author='<a href="http://www.timos-welt.de"  rel="external" class="external">Timos-Welt</a>';
$version='1.2';
$plugin_cpg_version = array('min' => '1.5');
$install_info = "There's nothing to configure after installation. You may open codebase.php and comment out the marked line if this plugin breaks certain scripts. ";
$extra_info = '<span class="admin_menu external"><a href="http://forum.coppermine-gallery.net/index.php/topic,57545.0.html" rel="external" title="CompressHTML Support">CompressHTML Support</a></span>'
    . '<p>This plugin MUST be the last one in the chain! Move it all the way downwards in plugin manager. This plugin has no effect in admin mode, log off to see it working.</p>';
?>