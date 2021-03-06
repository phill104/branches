<?php
/**************************************************
  Coppermine 1.5.x Plugin - Mouse wheel support for filmstrip
  *************************************************
  Copyright (c) 2010 Timo Schewe (www.timos-welt.de)
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


// Add jsquery plugin mousewheel
$thisplugin->add_action('page_start','include_js_wheelplug');


function include_js_wheelplug() {
  global $JS, $CPG_PHP_SELF;
	$stripwheel_pages_array = array('displayimage.php');
	if (in_array($CPG_PHP_SELF, $stripwheel_pages_array) == TRUE)
    {
    $JS['includes'][] = 'plugins/stripwheel/jquery.mousewheel.js';
    $JS['includes'][] = 'plugins/stripwheel/makewheel.js';
  }
}

?>