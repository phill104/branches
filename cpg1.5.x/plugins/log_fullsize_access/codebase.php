<?php
/**************************************************
  Coppermine 1.5.x Plugin - log_fullsize_access
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

$thisplugin->add_action('plugin_install', 'log_fullsize_access_plugin_install');

function log_fullsize_access_plugin_install() {
    global $CONFIG;

    cpg_db_query("CREATE TABLE IF NOT EXISTS {$CONFIG['TABLE_PREFIX']}plugin_log_fullsize_access (
                    id int(11) NOT NULL auto_increment,
                    pid int(11) NOT NULL,
                    user_id int(11) NOT NULL,
                    timestamp int(11) NOT NULL,
                    PRIMARY KEY (id) )");

    cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_CONFIG']} (name, value) VALUES ('plugin_log_fullsize_access_email', '0')");

    return true;
}

$thisplugin->add_filter('fullsize_html', 'log_fullsize_access_fullsize_html');

function log_fullsize_access_fullsize_html($fullsize_html) {
    global $CONFIG, $pid, $USER_DATA;

    if ($pid) {
        cpg_db_query("INSERT INTO {$CONFIG['TABLE_PREFIX']}plugin_log_fullsize_access (pid, user_id, timestamp) VALUES ('$pid', '".USER_ID."', UNIX_TIMESTAMP())");
        if ($CONFIG['plugin_log_fullsize_access_email']) {
            preg_match('/alt="(.*)"/U', $fullsize_html, $matches);
            cpg_mail($CONFIG['gallery_admin_email'], 'Fullsize access', $USER_DATA['user_name']."\t".$matches[1]);
        }
    }

    return $fullsize_html;
}

?>