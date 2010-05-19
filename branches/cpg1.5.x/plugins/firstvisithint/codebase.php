<?php
/**************************************************
  Coppermine 1.5.x Plugin - firstvisithint
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

  
if (!defined('IN_COPPERMINE')) {
	die('Not in Coppermine...');
}

$thisplugin->add_action('page_start','firstvisithint_include_js'); // Add js files

function firstvisithint_include_js() 
{
    global $JS;
    if (!USER_ID && !GALLERY_ADMIN_MODE)
    {
            $JS['includes'][] = "./plugins/firstvisithint/js/firstvisithint.js";
    }
}

?>