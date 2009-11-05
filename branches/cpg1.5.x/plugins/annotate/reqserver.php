<?php
/**************************************************
  Picture Annotation (annotate) plugin for cpg1.5.x
  *************************************************
  Copyright (c) 2003-2009 Coppermine Dev Team

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  *************************************************
  Coppermine version: 1.5.x
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**************************************************/

if (!defined('IN_COPPERMINE')) {
    die('Not in Coppermine...');
}

if (!USER_ID) {
    die('Access denied'); // Nobody will see that, as it's only for debugging purposes, so we can leave that string untranslated
} elseif (!GALLERY_ADMIN_MODE) {
    if ($CONFIG['plugin_annotate_permissions_registered'] < 2) {
        die('Access denied'); // Nobody will see that, as it's only for debugging purposes, so we can leave that string untranslated
    }
} 

$superCage = Inspekt::makeSuperCage();

if ($superCage->post->keyExists('add')){
    $pid = $superCage->post->getInt('add');
    $nid = $superCage->post->getInt('nid');
    $posx = $superCage->post->getInt('posx');
    $posy = $superCage->post->getInt('posy');
    $width = $superCage->post->getInt('width');
    $height = $superCage->post->getInt('height');
    $note = addslashes(urldecode($superCage->post->getRaw('note')));
    $time = time();
    if ($nid){
        $sql = "UPDATE {$CONFIG['TABLE_PREFIX']}plugin_annotate SET posx = $posx, posy = $posy, width = $width, height = $height, note = '$note' WHERE nid = $nid";
        if (!GALLERY_ADMIN_MODE) {
            $sql .= " AND user_id = " . USER_ID . " LIMIT 1";
        }
        cpg_db_query($sql);
        die("$nid");
    } else {
        $sql = "INSERT INTO {$CONFIG['TABLE_PREFIX']}plugin_annotate (pid, posx, posy, width, height, note, user_id, user_time) VALUES ($pid, $posx, $posy, $width, $height, '$note', " . USER_ID . ", '$time')";
        cpg_db_query($sql);
        $nid = mysql_insert_id($CONFIG['LINK_ID']);
        die("$nid");
    }
} elseif ($superCage->post->keyExists('remove')){
    $nid = $superCage->post->getInt('remove');
    $sql = "DELETE FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate WHERE nid = $nid";
    if (!GALLERY_ADMIN_MODE) {
        $sql .= " AND user_id = " . USER_ID . " LIMIT 1";
    }
    cpg_db_query($sql);
    die("$nid");
}
die("0"); // Just a precaution
