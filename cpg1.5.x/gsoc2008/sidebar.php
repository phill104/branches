<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2008 Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision: 3241 $
  $LastChangedBy: gaugau $
  $Date: 2006-08-18 08:52:27 +0200 (Fr, 18 Aug 2006) $
**********************************************/

// ------------------------------------------------------------------------- //
// Sidebar script (c) 2004 Tarique Sani <tarique@sanisoft.com>,              //
// Amit Badkas <amit@sanisoft.com>                                           //
// ------------------------------------------------------------------------- //

define('IN_COPPERMINE', true);
define('SIDEBAR_PHP', true);
require('include/init.inc.php');

// determine if visitor is allowed to access this page
if (USER_ID) {
  // the visitor is logged in
  if ($CONFIG['display_sidebar_user'] == 0 && !GALLERY_ADMIN_MODE) {
    cpg_die($lang_common['error'], $lang_errors['access_denied'], __FILE__, __LINE__);
  }
} else { // the visitor is not logged in
  if ($CONFIG['display_sidebar_guest'] == 0) {
    cpg_die($lang_common['error'], $lang_errors['access_denied'], __FILE__, __LINE__);
  }
}


//if ($_GET['action'] == 'install') {
if ($superCage->get->getAlpha('action') == 'install') {
//////// install --- start
pageheader($lang_sidebar_php['sidebar'] . ' - ' . $lang_sidebar_php['install']);
?>

<?php

starttable('100%', $CONFIG['gallery_name']. ' - ' . $lang_sidebar_php['sidebar'] , 1);

print <<< EOT
<tr>
<td class="tableh2">{$lang_sidebar_php['install_explain']}</td>
</tr>
<tr>
<td class="tableh2">
<div id="detecting">
EOT;

starttable('100%', $lang_sidebar_php['os_browser_detect'] , 1);
print <<< EOT
<tr>
<td class="tableb">
EOT;
printf($lang_sidebar_php['os_browser_detect_explain'], '<a href="javascript:unhide_all();">', '</a>');
print <<< EOT
</td>
</tr>
EOT;
endtable();
print <<< EOT
<br />
</div>

<div id="mozilla" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['mozilla'] , 1);
print <<< EOT
<tr>
<td class="tableb">
EOT;
printf($lang_sidebar_php['mozilla_explain'], '<a href="javascript:addPanel()">', '</a>');
print <<< EOT
</td>
</tr>
EOT;
endtable();
print <<< EOT
<br />
</div>
<div id="ie5win" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['ie_win'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
EOT;
 printf($lang_sidebar_php['ie_win_explain'], '<a href="javascript:void(open(\''.$CONFIG['ecards_more_pic_target'].'sidebar.php\',\'_search\'));">', '</a>');
print <<< EOT
  </td>
</tr>
EOT;
endtable();
print <<< EOT
<br />
</div>

<div id="ie7win" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['ie7_win'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
EOT;
 printf($lang_sidebar_php['ie7_win_explain'], '<a href="javascript:void(open(\''.$CONFIG['ecards_more_pic_target'].'sidebar.php\',\'_search\'));">', '</a>');
//printf($lang_sidebar_php['ie_win_explain'], '<a href="javascript:'. "window.external.AddFavorite('foo.htm', '{$CONFIG['gallery_name']} - {$lang_sidebar_php['sidebar']}')" .';">', '</a>');
//printf($lang_sidebar_php['ie_win_explain'], '<a href="javascript:window.external.AddFavorite(location.href, \''.$CONFIG['gallery_name'].' - '. $lang_sidebar_php['sidebar'].'\');">', '</a>');
print <<< EOT
  </td>
</tr>
EOT;
endtable();
print <<< EOT
<br />
</div>

<div id="ie5mac" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['ie_mac'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
EOT;
printf($lang_sidebar_php['ie_mac_explain'], '<a href="'.$CONFIG['ecards_more_pic_target'].'sidebar.php">', '</a>');
print <<< EOT
  </td>
</tr>
EOT;
endtable();
print <<< EOT
<br />
</div>
<div id="opera" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['opera'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
EOT;
printf($lang_sidebar_php['opera_explain'], '<a href="'.$CONFIG['ecards_more_pic_target'].'sidebar.php" rel="sidebar" title="'.$CONFIG['gallery_name'].'">', '</a>');
print <<< EOT
</td>
</tr>
EOT;

endtable();
print <<< EOT
</div>
<div id="additional" style="display:none">
EOT;
starttable('100%', $lang_sidebar_php['additional_options'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
EOT;
printf($lang_sidebar_php['additional_options_explain'], '<a href="javascript:unhide_all();">', '</a>');
print <<< EOT
  </td>
</tr>
EOT;

endtable();
print <<< EOT
</div>

<script type="text/javascript">
function addPanel() {
  if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
    window.sidebar.addPanel("{$CONFIG['gallery_name']} - {$lang_sidebar_php['sidebar']}", "{$CONFIG['ecards_more_pic_target']}sidebar.php", "");
  } else {
    alert('{$lang_sidebar_php['cannot_add_sidebar']}');
  }
}

function unhide_all() {
  document.getElementById('detecting').style.display = 'none';
  document.getElementById('additional').style.display = 'none';
  document.getElementById('mozilla').style.display = 'block';
  document.getElementById('ie5win').style.display = 'block';
  document.getElementById('ie7win').style.display = 'block';
  document.getElementById('ie5mac').style.display = 'block';
  document.getElementById('opera').style.display = 'block';
}

function os_browser_detection() {
  // browser detection.
  // Usually, browser detection is buggy and should not be used. However, the sidebar works only in mainstream browsers anyway and requires JavaScript, so we can be pretty sure that the user has it enabled if this is suppossed to work in the first place.
   var detection_success = 0;
   if (navigator.userAgent.indexOf('Firefox') != -1 || navigator.userAgent.indexOf('Netscape') != -1 || navigator.userAgent.indexOf('Konqueror') != -1 || navigator.userAgent.indexOf('Gecko') != -1) {
       document.getElementById('mozilla').style.display = 'block';
       document.getElementById('additional').style.display = 'block';
       document.getElementById('detecting').style.display = 'none';
       detection_success = 1;
   }
   if (navigator.userAgent.indexOf('Opera') != -1) {
       document.getElementById('opera').style.display = 'block';
       document.getElementById('additional').style.display = 'block';
       document.getElementById('detecting').style.display = 'none';
       detection_success = 1;
   }
   if (navigator.userAgent.indexOf('MSIE') != -1) {
       if (navigator.userAgent.indexOf('Mac') != -1) {
           document.getElementById('ie5mac').style.display = 'block';
           document.getElementById('additional').style.display = 'block';
           document.getElementById('detecting').style.display = 'none';
           detection_success = 1;
       } else {
           if(navigator.userAgent.indexOf('MSIE 7') != -1) {
             document.getElementById('ie7win').style.display = 'block';
             document.getElementById('additional').style.display = 'block';
             document.getElementById('detecting').style.display = 'none';
             detection_success = 1;
           } else {
             document.getElementById('ie5win').style.display = 'block';
             document.getElementById('additional').style.display = 'block';
             document.getElementById('detecting').style.display = 'none';
             detection_success = 1;
           }
       }
   }
}


self.onload = os_browser_detection();
</script>
<noscript>
EOT;
starttable('100%', $lang_common['error'] , 1);
print <<< EOT
<tr>
  <td class="tableb">
{$lang_common['javascript_needed']}
</td>
</tr>
EOT;

endtable();
print <<< EOT
</noscript>
</td>
</tr>
EOT;
endtable();


pagefooter();
ob_end_flush();
//////// install --- end
} else {
////////////////////// regular sidebar code starts here ///////////////////

global $CONFIG, $HIDE_USER_CAT, $FORBIDDEN_SET,$cpg_show_private_album;
if (!empty($FORBIDDEN_SET) && !$cpg_show_private_album) {
        $album_filter = ' and ' . str_replace('p.', 'a.', $FORBIDDEN_SET);
}
$sql = "SELECT aid FROM {$CONFIG['TABLE_ALBUMS']} as a WHERE category>=" . FIRST_USER_CAT . $album_filter;
$result = cpg_db_query($sql);
$album_count = mysql_num_rows($result);
if (!$album_count) {
        $HIDE_USER_CAT = 1;
}


$dtree_counter=0;

function get_tree_subcat_data($parent, $dtree_parent = 0) {
        global $CONFIG, $HIDE_USER_CAT, $catStr, $dtree_counter;
        if ($CONFIG['categories_alpha_sort'] == 1) {
                $cat_sort_order = 'name';
        }else{
                $cat_sort_order = 'pos';
        }
        $sql = "SELECT cid, name " . "FROM {$CONFIG['TABLE_CATEGORIES']} " . "WHERE parent = '$parent' " . "ORDER BY ". $cat_sort_order;
        $result = cpg_db_query($sql);
        if (($cat_count = mysql_num_rows($result)) > 0) {
                $rowset = cpg_db_fetch_rowset($result);
                $pos = 0;
                foreach ($rowset as $subcat) {
                        if ($subcat['cid'] == USER_GAL_CAT && $HIDE_USER_CAT == 1) {

                        } else {
                                $dtree_counter++;
                                $catStr .= "d.add(".$dtree_counter.",".$dtree_parent.",'".addslashes($subcat['name'])."','index.php?cat=".$subcat['cid']."','');\n";
                                $dtree_temp=$dtree_counter;
                                get_tree_subcat_data($subcat['cid'], $dtree_temp);
                                get_tree_album_data($subcat['cid'], $dtree_temp);
                        }
                }
                if ($parent == 0) {
                        get_tree_album_data($parent,0);
                }
        }
}

function get_tree_album_data($category,$dtree_parent) {
        global $catStr,$ALBUM_SET, $dtree_counter;
        global $CONFIG, $HIDE_USER_CAT, $FORBIDDEN_SET,$cpg_show_private_album;
        $album_filter='';
        $pic_filter='';
        if (!empty($FORBIDDEN_SET) && !$cpg_show_private_album) {
                $album_filter = ' and '.str_replace('p.','a.',$FORBIDDEN_SET);
                $pic_filter = ' and '.str_replace('p.',$CONFIG['TABLE_PICTURES'].'.',$FORBIDDEN_SET);
        }
        if ($category == USER_GAL_CAT) {
                $sql = "SELECT DISTINCT user_id, user_name FROM {$CONFIG['TABLE_USERS']}, {$CONFIG['TABLE_ALBUMS']} WHERE  10000 + {$CONFIG['TABLE_USERS']}.user_id = {$CONFIG['TABLE_ALBUMS']}.category ORDER BY user_name ASC";
                $result = cpg_db_query($sql);
                if (($cat_count = mysql_num_rows($result)) > 0) {
                        $rowset = cpg_db_fetch_rowset($result);
                        foreach ($rowset as $subcat) {
                                $dtree_counter++;
                                $catStr .= "d.add(".$dtree_counter.",".$dtree_parent.",'".addslashes($subcat['user_name'])."','index.php?cat=". (FIRST_USER_CAT + (int) $subcat['user_id']) ."');\n";
                                get_tree_album_data(FIRST_USER_CAT + (int) $subcat['user_id'], $dtree_counter);
                        }
                }
        } else {
                if ($category == USER_GAL_CAT) {
                        $sql = "SELECT aid,title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = $category ".$ALBUM_SET .$album_filter . " ORDER BY pos";
                } else {
                        $unaliased_album_filter = str_replace('a.','',$album_filter);
                        $sql = "SELECT aid,title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = $category ".$ALBUM_SET .$unaliased_album_filter . " ORDER BY pos";
                }
                $result = cpg_db_query($sql);
                if (($cat_count = mysql_num_rows($result)) > 0) {
                        $rowset = cpg_db_fetch_rowset($result);
                        foreach ($rowset as $subcat) {
                                $dtree_counter++;
                                $catStr .= "d.add(".$dtree_counter.",".$dtree_parent.",'".addslashes($subcat['title'])."','thumbnails.php?album=".$subcat['aid']."','');\n";
                        }
                }
        }
}

get_tree_subcat_data(0,0);


$output = "d = new dTree('d');\n";
$output .= "d.add(0,-1,'".$CONFIG['gallery_name'].$lang_list_categories['home']."','index.php');\n";
$output .= $catStr;
$output .= "document.write(d);\n";

if (defined('THEME_HAS_SIDEBAR_GRAPHICS')) {
    $location= $THEME_DIR;
} else {
    $location= '';
}


// Load template parameters
$params = array(
    '{LANG_DIR}' => $lang_text_dir,
    '{TITLE}' => $CONFIG['gallery_name']. ' - ' . $lang_sidebar_php['sidebar'],
    '{CHARSET}' => $CONFIG['charset'] == 'language file' ? $lang_charset : $CONFIG['charset'],
    '{SIDEBAR_CONTENT}' => $output,
    '{SEARCH_TITLE}' => $lang_sidebar_php['search'],
    '{RELOAD_TITLE}' => $lang_sidebar_php['reload'],
    '{THEME}' => $CONFIG['theme'],
    '{LOCATION}' => $location,
    );
// Parse template
echo template_eval($template_sidebar, $params);

}
?>