<?php
/**************************************************
  Coppermine 1.5.x Plugin - Userpics to sub-directory
  *************************************************
  Copyright (c) 2015 eenemeenemuu
  *************************************************
  $HeadURL$
  $Revision$
  $LastChangedBy: eenemeenemuu $
  $Date$
**************************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

if (!GALLERY_ADMIN_MODE) {
    cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
}

$limit = 50;

//$result = cpg_db_query("SELECT pid, aid, filepath, filename FROM {$CONFIG['TABLE_PICTURES']} WHERE filepath LIKE '{$CONFIG['userpics']}%' AND filepath NOT LIKE CONCAT('%/', aid, '/') LIMIT $limit");
// move just .jpg files for the moment - TODO: add support for non-image files and possible custom thumbnails
$result = cpg_db_query("SELECT pid, aid, filepath, filename FROM {$CONFIG['TABLE_PICTURES']} WHERE filepath LIKE '{$CONFIG['userpics']}%' AND filepath NOT LIKE CONCAT('%/', aid, '/') AND filename LIKE '%.jpg' LIMIT $limit");

if (mysql_num_rows($result)) {
    pageheader('Moving', '<meta http-equiv="refresh" content="1; URL=index.php?file=userpics_to_subdir/move">');
    echo '<table width="100%"><tr><td class="tableh1" width="50%">File</td><td class="tableh1" width="50%">Moved to</td></tr>';
} else {
    pageheader('Done');
    echo '<table width="100%"><tr><td>Done.</td></tr>';
}

while ($row = mysql_fetch_assoc($result)) {
    $dest_dir_sql = $row['filepath'].$row['aid'].'/';
    $dest_dir = $CONFIG['fullpath'].$dest_dir_sql;
    $src_dir = $CONFIG['fullpath'].$row['filepath'];
    $images = array($row['filename'], $CONFIG['normal_pfx'].$row['filename'], $CONFIG['thumb_pfx'].$row['filename'], $CONFIG['orig_pfx'].$row['filename']);

    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, octdec($CONFIG['default_dir_mode']));
        if (!is_dir($dest_dir)) {
            cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['err_mkdir'], $dest_dir), __FILE__, __LINE__);
        }
        @chmod($dest_dir, octdec($CONFIG['default_dir_mode'])); //silence the output in case chmod is disabled
        $fp = fopen($dest_dir . '/index.php', 'w');
        fwrite($fp, ' ');
        fclose($fp);
    }

    foreach ($images as $image) {
        if (!file_exists($src_dir.$image)) {
            continue;
        }
        if (!rename($src_dir.$image, $dest_dir.$image)) {
            cpg_die(CRITICAL_ERROR, sprintf($lang_db_input_php['err_move'], $src_dir.$image, $dest_dir), __FILE__, __LINE__);
        } else {
            echo '<tr><td>'.$src_dir.$image.'</td><td>'.$dest_dir.'</td></tr>';
        }
    }

    cpg_db_query("UPDATE {$CONFIG['TABLE_PICTURES']} SET filepath = '$dest_dir_sql' WHERE pid = {$row['pid']}");
}

echo '</table>';
pagefooter();

?>