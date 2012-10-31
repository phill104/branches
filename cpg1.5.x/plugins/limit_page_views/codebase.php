<?php
/**************************************************
  Coppermine 1.5.x Plugin - limit_page_views
  *************************************************
  Copyright (c) 2012 eenemeenemuu
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

$thisplugin->add_action('page_start', 'limit_page_views_main');

function limit_page_views_main() {
    if (!GALLERY_ADMIN_MODE) {
        global $CONFIG;

        $allowed_page_views = 200;
        $timeframe = 60; // in seconds

        $num_views = mysql_result(cpg_db_query("SELECT COUNT(*) FROM {$CONFIG['TABLE_PREFIX']}page_views WHERE timestamp >= ".(time() - $timeframe)), 0);

        if ($num_views > $allowed_page_views) {
            load_template();
            cpg_die(INFORMATION, "Too many page views. Please try again later.", __FILE__, __LINE__);
        } else {
            cpg_db_query("DELETE FROM {$CONFIG['TABLE_PREFIX']}page_views WHERE timestamp < ".(time() - $timeframe));
            cpg_db_query("INSERT INTO {$CONFIG['TABLE_PREFIX']}page_views VALUES(".time().")");
        }
    }
}


$thisplugin->add_action('plugin_install', 'limit_page_views_install');

function limit_page_views_install() {
    global $CONFIG;
    cpg_db_query("CREATE TABLE {$CONFIG['TABLE_PREFIX']}page_views (timestamp int(11))");
    return true;
}


$thisplugin->add_action('plugin_uninstall', 'limit_page_views_uninstall');

function limit_page_views_uninstall() {
    global $CONFIG;
    cpg_db_query("DROP TABLE {$CONFIG['TABLE_PREFIX']}page_views");
    return true;
}

?>