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

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

$thisplugin->add_filter('page_html', 'jquery_update_page_html');

function jquery_update_page_html($html) {
    /**
     * Example for self hosted jQuery library - don't forget to upload it accordingly
     * $replace = 'js/jquery-1.11.0.min.js'; 
     *
     * Example for Google hosted library - always latest version of jQuery 1.x branch
     * $replace = '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js';
     */

    $replace = '//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js';

    $html = str_replace('js/jquery-1.3.2.js', $replace, $html);
    return $html;
}

?>