<?php
/**************************************************
  Coppermine 1.4.x Plugin - HighSlide
  *************************************************
  Copyright (c) 2006 Borzoo Mossavari
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  *************************************************
  Skip Intermediate Page and show full page on the page
  Based on Highslide JS @ http://vikjavev.no/highslide/ 
  ***************************************************/
  
if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...');}

// submit your lang file for this plugin on the coppermine forums
// plugin will try to use the configured language if it is available.

if (file_exists("plugins/highslide/lang/{$CONFIG['lang']}.php")) {
  require "plugins/highslide/lang/{$CONFIG['lang']}.php";
} else {require "plugins/highslide/lang/english.php";}
?>