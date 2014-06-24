<?php
/**************************************************
  Coppermine 1.5.x Plugin - auto_tag
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

$thisplugin->add_action('after_edit_file', 'auto_tag_after_edit_file');

function auto_tag_after_edit_file($pid) {
    global $CONFIG;
    static $keywords_array = null;

    if ($keywords_array === null) {
        $keywords_array = array();
        $result = cpg_db_query("SELECT keywords FROM {$CONFIG['TABLE_PICTURES']} WHERE keywords <> ''");
        if (mysql_num_rows($result)) {
            while (list($keywords) = mysql_fetch_row($result)) {
                $array = explode($CONFIG['keyword_separator'], html_entity_decode($keywords));
                foreach($array as $word) {
                    if (!in_array($word = utf_strtolower($word), $keywords_array) && trim($word)) {
                        $keywords_array[] = $word;
                    }
                }
            }
        }
    }

    if (!count($keywords_array)) {
        return;
    }

    $result = cpg_db_query("SELECT title, caption, keywords FROM {$CONFIG['TABLE_PICTURES']} WHERE pid = $pid LIMIT 1");
    if (!mysql_num_rows($result)) {
        return;
    }

    $picture = mysql_fetch_assoc($result);
    if (!$picture['title'] && !$picture['caption']) {
        return;
    }

    preg_match_all('/[\w]+/', $picture['title'], $matches_title);
    preg_match_all('/[\w]+/', $picture['caption'], $matches_caption);
    $word_array = array_merge($matches_title[0], $matches_caption[0]);

    if ($picture['keywords']) {
        $keyword_array = array();
        $array = explode($CONFIG['keyword_separator'], html_entity_decode($picture['keywords']));
        foreach($array as $word) {
            if (!in_array($word = utf_strtolower($word), $keyword_array) && trim($word)) {
                $keyword_array[] = $word;
            }
        }
    }

    $new_keyword = false;
    foreach ($word_array as $word) {
        $word = utf_strtolower($word);
        if (!in_array($word, $keyword_array) && in_array($word, $keywords_array)) {
            $new_keyword = true;
            $keyword_array[] = $word;
        }
    }

    if ($new_keyword) {
        cpg_db_query("UPDATE {$CONFIG['TABLE_PICTURES']} SET keywords = '".implode($CONFIG['keyword_separator'], $keyword_array)."' WHERE pid = $pid LIMIT 1");
    }
}

?>