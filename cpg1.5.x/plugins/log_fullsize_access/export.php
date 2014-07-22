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

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}

$separator = ";";
$output = 'id'.$separator.'pid'.$separator.'user_id'.$separator.'filename'.$separator.'user_name'.$separator.'timestamp'."\n";;
$result = cpg_db_query("
                SELECT id, l.pid, l.user_id, filename, user_name, timestamp FROM {$CONFIG['TABLE_PREFIX']}plugin_log_fullsize_access AS l 
                INNER JOIN {$CONFIG['TABLE_PICTURES']} AS p ON l.pid = p.pid
                INNER JOIN {$CONFIG['TABLE_USERS']} AS u ON l.user_id = u.user_id");
while ($row = mysql_fetch_assoc($result)) {
    $output .= $row['id'].$separator.$row['pid'].$separator.$row['user_id'].$separator.$row['filename'].$separator.$row['user_name'].$separator.$row['timestamp']."\n";
}

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename=export_'.time().'.csv');
header('Pragma: no-cache');
echo $output;

?>