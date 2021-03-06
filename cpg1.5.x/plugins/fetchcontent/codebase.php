<?php
/********************************************************
  Coppermine 1.5.x plugin - FetchContent
  *******************************************************
  Copyright (c) 2010 Coppermine dev team
  *******************************************************
  This program is free software; you can redistribute 
  it and/or modify it under the terms of the GNU General
  Public License as published by the Free Software
  Foundation; either version 3 of the License, or 
  (at your option) any later version.
  *******************************************************
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
  *******************************************************/

if (!defined('IN_COPPERMINE')) {
	die('Not in Coppermine...');
}

$superCage = Inspekt::makeSuperCage();

$thisplugin->add_action('plugin_install','fetchcontent_install'); // Add plugin_install action$thisplugin->add_action('plugin_uninstall','fetchcontent_uninstall'); // Add plugin_uninstall action

if ($superCage->get->keyExists('file')) {
    if ($matched = $superCage->get->getMatched('file', "/^([a-zA-Z0-9_\-]+)(\/{0,1}?)([a-zA-Z0-9_\-]+)$/")) {
        require_once('./plugins/fetchcontent/configuration.php');
		if ($matched[0] == 'fetchcontent/docs_' . $documentation_file ) {
			$thisplugin->add_filter('page_meta','fetchcontent_meta');
		}
    }
}

// install
function fetchcontent_install() {
    global $CONFIG;
	// Add the config options for the plugin	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_image_denied', '2')");
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_check_referer', '0')");
	$url = parse_url($CONFIG['site_url']);
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_domainlist', '{$url['host']}')");
    cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_enable_logging', '1')");
    cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_non_image', '1')");
    cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_debug', '0')");
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_max_cols', {$CONFIG['thumbcols']})");
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_max_rows', {$CONFIG['thumbrows']})");
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_hide_location', '0')");
	$cachefolder = 'fetchcontent';
	$loopcounter = '';
	while (file_exists($CONFIG['fullpath'] . $cachefolder . $loopcounter) == TRUE) {
	    $loopcounter++;
	}
	$CONFIG['plugin_fetchcontent_cachefolder'] = $cachefolder . $loopcounter;
	unset($cachefolder);
    @mkdir($CONFIG['fullpath'] . $CONFIG['plugin_fetchcontent_cachefolder'], $CONFIG['default_dir_mode']);
    @chmod($CONFIG['fullpath'] . $CONFIG['plugin_fetchcontent_cachefolder'], $CONFIG['default_dir_mode']);
	cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (`name`, `value`) VALUES ('plugin_fetchcontent_cachefolder', '{$CONFIG['plugin_fetchcontent_cachefolder']}')");
	// Add the table
	$query = <<< EOT
        CREATE TABLE IF NOT EXISTS {$CONFIG['TABLE_PREFIX']}plugin_fetchcontent (
          pid mediumint(10) NOT NULL default '0',
          imgtype enum('thumbnail','intermediate', 'full-size') NOT NULL default 'thumbnail',
          filepath varchar(255) NOT NULL default '',
          width smallint(6) NOT NULL default '0',
          height smallint(6) NOT NULL default '0',
          filesize int(11) NOT NULL default '0',
          timestamp int(11) NOT NULL default '0'
        ) TYPE=MyISAM COMMENT='Contains the info for the temporary files of the fetchcontent plugin';
EOT;
    cpg_db_query($query);
	
    return true;
}


// uninstall and drop settings tablefunction fetchcontent_uninstall() {
    global $CONFIG;
	// Delete the plugin config records	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_image_denied'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_check_referer'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_domainlist'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_enable_logging'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_non_image'");
    cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_debug'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_max_cols'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_max_rows'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_hide_location'");
	cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_fetchcontent_cachefolder'");
	// Delete temporary files
	$loopCounter = 0;
	$result = cpg_db_query("SELECT * FROM {$CONFIG['TABLE_PREFIX']}plugin_fetchcontent");
	while ($row = mysql_fetch_assoc($result)) {
		cpg_folder_file_delete($CONFIG['fullpath'] . $CONFIG['plugin_fetchcontent_cachefolder']. $row['filepath']);
		$loopCounter++;
	}
	// Delete temporary folder
	@rmdir($CONFIG['fullpath'] . $CONFIG['plugin_fetchcontent_cachefolder']);

	// Drop the temporary files table
	$query = "DROP TABLE IF EXISTS {$CONFIG['TABLE_PREFIX']}plugin_fetchcontent";
    cpg_db_query($query);

    return true;
}

function fetchcontent_meta($meta) {
	$meta = <<< EOT
<link rel="stylesheet" href="docs/style/style.css" type="text/css" media="all" />
<link rel="stylesheet" type="text/css" href="docs/style/screen.css" media="screen" />
<link rel="stylesheet" type="text/css" href="docs/style/print.css" media="print" />
{$meta}
EOT;
	return $meta;
}

?>