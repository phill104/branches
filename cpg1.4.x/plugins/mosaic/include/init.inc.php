<?php
/**************************************************
  CPG Mosaic Plugin for Coppermine Photo Gallery
  *************************************************
  Copyright (c) 2008 Thomas Lange <stramm@gmx.net>
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  *************************************************
  Coppermine version: 1.4.18
  Mosaic version: 1.0
  $Revision: 1.0 $
  $Author: stramm $
***************************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

global $CONFIG, $USER;

$lang = isset($USER['lang']) ? $USER['lang'] : $CONFIG['lang'];
if (!file_exists("plugins/mosaic/lang/{$lang}.php"))
  $lang = 'english';
require "plugins/mosaic/lang/{$lang}.php";

$CONFIG['TABLE_MOSAIC'] = $CONFIG['TABLE_PREFIX'] . "mosaic";
?>