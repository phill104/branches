<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('IN_COPPERMINE', true);
define('THUMBNAILS_PHP', true);
define('INDEX_PHP', true);

include('include/archive.php');

echo '<p>Deleting old zip files...</p>';
$dir = $CONFIG['fullpath'].'edit/';
if ($handle = opendir($dir)) {
    while (false !== ($entry = readdir($handle))) {
        if (preg_match('/^pictures-[0-9a-f]+.zip$/', $entry) && filemtime($dir.$entry) < time() - 2 * CPG_DAY) {
            unlink($dir.$entry);
        }
    }
    closedir($handle);
}

echo '<p>Creating file list...</p>';
$filelist = array();
$aid = $superCage->get->getInt('aid');
get_meta_album_set(0);
$query = "SELECT filepath, filename FROM {$CONFIG['TABLE_PICTURES']} AS pictures , (SELECT keyword FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid = '" . $aid . "' ) AS keyword, {$CONFIG['TABLE_ALBUMS']} AS r $RESTRICTEDWHERE AND r.aid = pictures.aid AND (pictures.aid = '" . $aid . "' OR ( keyword.keyword <> '' AND CONCAT(';', keywords, ';') LIKE CONCAT('%;', keyword.keyword, ';%')))";
$result = cpg_db_query($query);
$rowset = cpg_db_fetch_rowset($result);

foreach ($rowset as $row) {
    $fileentry = $row['filepath'].$row['filename'];
    $filelist[] = $fileentry;
}

echo '<p>Creating zip file...</p>';
$filename = 'edit/pictures-' . uniqid(null) . '.zip';
$zip = new zip_file($filename);

$options = array(
    'basedir'    => "./{$CONFIG['fullpath']}",
    'recurse'    => 0,
    'storepaths' => 0,
);

$zip->set_options($options);
$zip->add_files($filelist);
$zip->create_archive();

echo '<p>Downloading...</p>';

ob_end_clean();

header('Location: ' . $CONFIG['site_url'] . $CONFIG['fullpath'] . $filename);

?>