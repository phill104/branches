<?php
/**************************************************
  Coppermine 1.5.x Plugin - Geo IP Lookup (geoip)
  *************************************************
  Copyright (c) 2010 Joachim M�ller
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

// Add plugin_install action

// Add plugin_uninstall action


if (GALLERY_ADMIN_MODE) {
	if ($CONFIG['plugin_geoip_scope'] == '1' && file_exists("./plugins/geoip/GeoLiteCity.dat")) {
        include_once('plugins/geoip/geoipcity.inc.php');
        include_once('plugins/geoip/geoipregionvars.php');
	} else {
	    include_once('plugins/geoip/geoip.inc.php');
	}
	$thisplugin->add_filter('ip_information','plugin_geoip_flag');
}

// install
function plugin_geoip_install() {
    global $CONFIG;
	// Add the config options for the plugin
}

// uninstall and drop settings
    global $CONFIG;
	$superCage = Inspekt::makeSuperCage();
    if (!checkFormToken()) {
        global $lang_errors;
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }
    // Delete the plugin config records
}

function plugin_geoip_flag($ip) {  
	global $CONFIG;
	$return = '';
	if ($ip != '') {
		if ($CONFIG['plugin_geoip_scope'] == '1' && file_exists("./plugins/geoip/GeoLiteCity.dat")) {
		    $gi = geoip_open('plugins/geoip/GeoLiteCity.dat',GEOIP_STANDARD);
		    $record = geoip_record_by_addr($gi, $ip);
    		if ($record->country_code != '' && file_exists('images/flags/' . strtolower($record->country_code) . '.png') == TRUE) {
    			$return = '<img src="images/flags/' . strtolower($record->country_code) . '.png" border="0" width="16" height="11" alt="" title="' . geoip_country_name_by_addr($gi, $ip) . '" style="margin-left:1px;" />';
    		}
    		if ($record->city != '') {
    			$return .= $record->city;
    		}
		    geoip_close($gi);
		} else {
    		$gi = geoip_open('plugins/geoip/GeoIP.dat',GEOIP_STANDARD);
    		$country_code = geoip_country_code_by_addr($gi, $ip);
    		if ($country_code != '' && file_exists('images/flags/' . strtolower($country_code) . '.png') == TRUE) {
    			$return = '<img src="images/flags/' . strtolower($country_code) . '.png" border="0" width="16" height="11" alt="" title="' . geoip_country_name_by_addr($gi, $ip) . '" style="margin-left:1px;" />';
    		}
    		geoip_close($gi);
		}
	}
	return $return;
}



?>