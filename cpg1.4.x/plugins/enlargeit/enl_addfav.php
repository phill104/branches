<?php
/**************************************************
  Coppermine 1.4.x Plugin - EnlargeIt! $VERSION$=2.15
  *************************************************
  Copyright (c) 2008 Timos-Welt (www.timos-welt.de)
  *************************************************
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 3 of the License, or
  (at your option) any later version.
  ***************************************************/

define('IN_COPPERMINE', true);
define('RATEPIC_PHP', true);

require('include/init.inc.php');

if (!USER_ID && $CONFIG['allow_unlogged_access'] == 0) {
    $redirect = $redirect . "login.php";
    header("Location: $redirect");
    exit();
}

global $ENLARGEITSET,$lang_enlargeit;
require('./plugins/enlargeit/include/load_enlargeitset.php');
require('./plugins/enlargeit/include/init.inc.php');

$pic = (int)$_GET['pid'];


// If user does not accept script's cookies, we don't accept the vote
if (!isset($_COOKIE[$CONFIG['cookie_name'] . '_data'])) {
    header("Location: $ref");
    exit;
}
// See if this picture is already present in the array
if (!in_array($pic, $FAVPICS)) {
    $FAVPICS[] = $pic;
    $enl_added = 1;
} else {
    $key = array_search($pic, $FAVPICS);
    unset ($FAVPICS[$key]);
    $enl_added = 0;
}

$data = base64_encode(serialize($FAVPICS));
setcookie($CONFIG['cookie_name'] . '_fav', $data, time() + 86400 * 30, $CONFIG['cookie_path']);
// If the user is logged in then put it in the DB
if (USER_ID > 0) {
    $sql = "UPDATE {$CONFIG['TABLE_FAVPICS']} SET user_favpics = '$data' WHERE user_id = " . USER_ID;
    cpg_db_query($sql);
    // User never stored a fav... so insert new row
    if (!mysql_affected_rows($CONFIG['LINK_ID'])) {
        $sql = "INSERT INTO {$CONFIG['TABLE_FAVPICS']} ( user_id, user_favpics) VALUES (" . USER_ID . ", '$data')";
        cpg_db_query($sql);
    }
}


echo "<table align=\"center\" cellspacing=\"1\" style=\"width:100%;height:100%\">";
echo "<tr><td width=\"100%\" align=\"center\" class=\"enl_infotablehead\"><b>".$lang_enlargeit['enl_tooltipfav']."</b></td></tr>";
echo "<tr><td width=\"100%\" align=\"center\" class=\"enl_infotable\"><b>";
if ($enl_added == 1) {
	echo $lang_enlargeit['enl_addedtofav'];
}
else
{
	echo $lang_enlargeit['enl_removedfromfav'];
}
echo "</b><br /><br />";
if ($ENLARGEITSET['enl_sefmode']) echo "<a href=\"thumbnails-favpics.html\">".$lang_enlargeit['enl_showfav']."</a>";
else echo "<br /><br /><a href=\"thumbnails.php?album=favpics\">".$lang_enlargeit['enl_showfav']."</a>";

echo "</td></tr></table>";
ob_end_flush();

?>