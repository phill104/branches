<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2007 Coppermine Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision$
  $LastChangedBy$
  $Date$
**********************************************/

if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...');}

// encoding match for workaround

$multibyte_charset = 'utf-8, big5, shift_jis, euc-kr, gb2312';

$charset = $CONFIG['charset'] == 'language file' ? $GLOBALS['lang_charset'] : $CONFIG['charset'];

$sort_array = array('na' => 'filename ASC', 'nd' => 'filename DESC', 'ta'=>'title ASC', 'td'=>'title DESC', 'da' => 'pid ASC', 'dd' => 'pid DESC', 'pa' => 'position ASC', 'pd' => 'position DESC');
$sort_code = isset($USER['sort'])? $USER['sort'] : $CONFIG['default_sort_order'];
$sort_order = isset($sort_array[$sort_code]) ? $sort_array[$sort_code] : $sort_array[$CONFIG['default_sort_order']];

$allowed = array('title', 'caption', 'keywords', 'owner_name', 'filename', 'pic_raw_ip', 'pic_hdr_ip', 'user1', 'user2', 'user3', 'user4');

$mb_charset = stristr($multibyte_charset, $charset);

$search_string = str_replace('&quot;', '"', $search_string);
$search_string = str_replace('\'', '"', $search_string);
$search_string = preg_replace('/&.*;/i', '', $search_string);

if (!$mb_charset)
        $search_string = preg_replace('/[^0-9a-z %]/i', '', $search_string);

if (!isset($USER['search']['params'])){
        $USER['search']['params']['title'] = $USER['search']['params']['caption'] = $USER['search']['params']['keywords'] = $USER['search']['params']['filename'] = 1;
}

if (isset($_GET['params'])) unset($_GET['album']);

if (isset($_GET['album']) && $_GET['album'] == 'search') {
        $_GET = $USER['search'];
        $_GET['type'] = $USER['search']['type'];
}

$type = $_GET['type'] == 'AND' ? " AND " : " OR ";

if (isset($_GET['params']['pic_raw_ip'])) $_GET['params']['pic_hdr_ip'] = $_GET['params']['pic_raw_ip'];

if ($search_string && isset($_GET['params'])) {
        $sql = "SELECT * FROM {$CONFIG['TABLE_PICTURES']} WHERE ";
        $sections = array();

        if($_GET['type'] == 'regex') {
                $search_string = preg_replace('/[^\w\+\*\?\{\,\}\|\(\)\\\^\$\[\]\:\<\>\-\.]/','',$search_string);
                $search_string = addslashes($search_string);
                foreach($_GET['params'] as $param => $value) {
                        if (in_array($param, $allowed)) $fields[] = "$param REGEXP '$search_string'";
                }
                $sql .= ('((' . implode(' OR ', $fields) . '))');
        } else {
                $search_string = strtr($search_string, array('_' => '\_', '%' => '\%', '*' => '%'));

                $quote_offset = (preg_match('^(\s)*\"',$search_string) + 1);
                $split_search = explode('"',$search_string);
                foreach($split_search as $index => $string) {
                        if(($index & 1) && strlen($string)) {
                                $fields = array();
                                foreach ($_GET['params'] as $param => $value){
                                        if (in_array($param, $allowed)) $fields[] = "$param LIKE '%$string%'";
                                }
                                $sections[] = '(' . implode(' OR ', $fields) . ')';                                
                        } else if (strlen($string)) {
                                $words = explode(' ', $string);
                                foreach($words as $word) {
                                        if(strlen($word)) {
                                                $word = addslashes($word);
                                                $fields = array();
                                                foreach ($_GET['params'] as $param => $value){
                                                        if (in_array($param, $allowed)) $fields[] = "$param LIKE '%$word%'";
                                                }
                                                $sections[] = '(' . implode(' OR ', $fields) . ')';                                                
                                        }
                                }
                        }
                }
                

                $sql .= '(' . implode($type, $sections) . ')';
        }
        
        file_put_contents('sql.dat',$sql);

        $sql .= $_GET['newer_than'] ? ' AND ( ctime > UNIX_TIMESTAMP() - '.( (int) $_GET['newer_than'] * 60*60*24).')' : '';
        $sql .= $_GET['older_than'] ? ' AND ( ctime < UNIX_TIMESTAMP() - '.( (int) $_GET['older_than'] * 60*60*24).')' : '';
        $sql .=  " $ALBUM_SET AND approved = 'YES'";

        $temp = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $result = cpg_db_query($temp);
        $row = mysql_fetch_row($result);
        $count = $row[0];
                                              
        $sql .= " ORDER BY $sort_order $limit";
        $result = cpg_db_query($sql);
        $rowset = cpg_db_fetch_rowset($result);
        mysql_free_result($result);

        if ($set_caption) build_caption($rowset);
}
?>
