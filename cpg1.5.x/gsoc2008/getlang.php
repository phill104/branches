<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2008 Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.
  
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

define('IN_COPPERMINE', true);
define('ADMIN_PHP', true);

define('LANG_DIR', 'lang');

require('include/init.inc.php');

$dir = opendir(LANG_DIR);
$lang_files = array();
while ($file = readdir($dir)) {
    if (is_file(LANG_DIR . '/' . $file)) {
        $lang_files[] = $file;
    }
}
closedir($dir);
asort($lang_files);

/*if (isset($_GET['get'])) {
    $file_index = (int)$_GET['get'];*/
if ($superCage->get->keyExists('get')) {
		$file_index = $superCage->get->getInt('get') ;   
    if ((isset($lang_files[$file_index]))) {
        header("Content-type: application/php");
        header("Content-Disposition: attachment; filename={$lang_files[$file_index]}");
        fpassthru(fopen(LANG_DIR . '/' . $lang_files[$file_index], 'r'));
        exit;
    }
}

pageheader('Language files');
starttable('100%', 'Language files');
foreach($lang_files as $index => $file) {
    echo <<<EOT
                <tr>
                        <td class="tableb">
                                <img src="images/folder.gif" alt="">&nbsp;<a href="{$CPG_PHP_SELF}?get=$index">$file</a>
                        </td>
                </tr>
EOT;
}
endtable();
pagefooter();
ob_end_flush();

?>
