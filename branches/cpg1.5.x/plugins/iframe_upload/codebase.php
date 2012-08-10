<?php
/**************************************************
  Coppermine 1.5.x Plugin - iframe_upload
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

$thisplugin->add_action('plugin_install', 'iframe_upload_install');
$thisplugin->add_action('plugin_uninstall', 'iframe_upload_uninstall');
$thisplugin->add_filter('html_document', 'iframe_upload_html_document');

function iframe_upload_install() {
    global $CONFIG;

    cpg_db_query("INSERT IGNORE INTO {$CONFIG['TABLE_FILETYPES']} (extension,mime,content,player) VALUES ('iframe', 'text/html', 'document', '')");

    if (strpos($CONFIG['allowed_doc_types'], $extension) === FALSE) {
        cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = CONCAT(value, '/iframe') WHERE name = 'allowed_doc_types'");
    }

    return true;
}


function iframe_upload_uninstall() {
    global $CONFIG;
    $CONFIG['allowed_doc_types'] = str_replace('/iframe', $CONFIG['allowed_doc_types']);
    $CONFIG['allowed_doc_types'] = str_replace('iframe/', '', $CONFIG['allowed_doc_types']);
    $CONFIG['allowed_doc_types'] = str_replace('iframe', '', $CONFIG['allowed_doc_types']);
    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '{$CONFIG['allowed_doc_types']}' WHERE name = 'allowed_doc_types'");
    cpg_db_query("DELETE FROM {$CONFIG['TABLE_FILETYPES']} WHERE extension = 'iframe'");

    return true;
}


function iframe_upload_html_document($pic_html) {
    global $CONFIG, $CURRENT_PIC_DATA;

    if ($CURRENT_PIC_DATA['extension'] == 'iframe') {
        $contents = file_get_contents($CONFIG['fullpath'].$CURRENT_PIC_DATA['filepath'].$CURRENT_PIC_DATA['filename']);
        $width = $CURRENT_PIC_DATA['pwidth'] ? $CURRENT_PIC_DATA['pwidth'] : '95%';
        $height = $CURRENT_PIC_DATA['pheight'] ? $CURRENT_PIC_DATA['pheight'] : $CONFIG['picture_width'];
        $pic_html = "<iframe src=\"{$contents}\" width=\"{$width}\" height=\"{$height}\"></iframe><br />Source: <a href=\"{$contents}\">{$contents}</a>";
    }

    return $pic_html;
}


?>
