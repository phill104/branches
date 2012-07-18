<?php
/**************************************************
  Coppermine 1.5.x Plugin - picture_navigation
  *************************************************
  Copyright (c) 2010-2012 eenemeenemuu
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

if (defined('DISPLAYIMAGE_PHP')) {
    $thisplugin->add_action('page_start', 'picture_navigation_page_start');
}

function picture_navigation_page_start() {
    js_include('plugins/picture_navigation/picture_navigation.js');
}

?>