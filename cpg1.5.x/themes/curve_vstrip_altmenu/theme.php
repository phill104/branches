<?php

define('THEME_HAS_PROGRESS_GRAPHICS', 1);
define('THEME_HAS_FILM_STRIP_GRAPHIC', 1); 

// HTML template for template  sub menu buttons
$template_sys_menu_button = $template_sub_menu_button = <<<EOT
<!-- BEGIN {BLOCK_ID} -->
    <li>
        <a href="{HREF_TGT}" title="" class="firstlevel"><span class="firstlevel">{HREF_LNK}</span></a>
    </li>
<!-- END {BLOCK_ID} -->
EOT;

// HTML template for sys menu
$template_sys_menu = <<<EOT

<ul class="dropmenu">
<!-- BEGIN home -->
    <li>
        <a href="{HOME_TGT}" title="" class="firstlevel" id="sbut_left"><span class="firstlevel" id="sbut_right">{HOME_ICO}&nbsp;Start</span></a>
        <ul>
            <!-- BEGIN register -->
                <li><a href="{REGISTER_TGT}" title=""><span>{REGISTER_ICO}&nbsp;{REGISTER_LNK}</span></a></li>
            <!-- END register -->
            <!-- BEGIN login -->
                <li><a href="{LOGIN_TGT}" title=""><span>{LOGIN_ICO}&nbsp;{LOGIN_LNK}</span></a></li>
            <!-- END login -->
            <!-- BEGIN logout -->
                <li><a href="{LOGOUT_TGT}" title=""><span>{LOGOUT_ICO}&nbsp;{LOGOUT_LNK}</span></a></li>
            <!-- END logout -->
            <!-- BEGIN sidebar -->
                <li><a href="{SIDEBAR_TGT}" title=""><span>{SIDEBAR_ICO}&nbsp;{SIDEBAR_LNK}</span></a></li>
            <!-- END sidebar -->
            <!-- BEGIN contact -->
                <li><a href="{CONTACT_TGT}" title=""><span>{CONTACT_ICO}&nbsp;{CONTACT_LNK}</span></a></li>
            <!-- END contact --> 
            <!-- BEGIN allow_memberlist -->
                <li><a href="{MEMBERLIST_TGT}" title=""><span>{MEMBERLIST_ICO}&nbsp;{MEMBERLIST_LNK}</span></a></li>
            <!-- END allow_memberlist -->
            <!-- BEGIN search -->
                <li><a href="{SEARCH_TGT}" title=""><span>{SEARCH_ICO}&nbsp;{SEARCH_LNK}</span></a></li>
            <!-- END search -->
        </ul>
    </li>
<!-- END home -->

<!-- BEGIN custom_link -->
    <li>
        <a href="{CUSTOM_LNK_TGT}" title="" class="firstlevel"><span class="firstlevel">{CUSTOM_LNK_LNK}</span></a>
    </li>
<!-- END custom_link -->

<!-- BEGIN album_list -->
    <li>
        <a href="{ALB_LIST_TGT}" title="" class="firstlevel"><span class="firstlevel">{ALB_LIST_ICO}&nbsp;{ALB_LIST_LNK}</span></a>
        <ul>
            <!-- BEGIN lastup -->
                <li><a href="{LASTUP_TGT}" title="" rel="nofollow"><span>{LASTUP_ICO}&nbsp;{LASTUP_LNK}</span></a></li>
            <!-- END lastup -->
            <!-- BEGIN lastcom -->
                <li><a href="{LASTCOM_TGT}" title="" rel="nofollow" ><span>{LASTCOM_ICO}&nbsp;{LASTCOM_LNK}</span></a></li>
            <!-- END lastcom -->
            <!-- BEGIN topn -->
                <li><a href="{TOPN_TGT}" title="" rel="nofollow"><span>{TOPN_ICO}&nbsp;{TOPN_LNK}</span></a></li>
            <!-- END topn -->
            <!-- BEGIN toprated -->
                <li><a href="{TOPRATED_TGT}" title="" rel="nofollow"><span>{TOPRATED_ICO}&nbsp;{TOPRATED_LNK}</span></a></li>
            <!-- END toprated -->
            <!-- BEGIN favpics -->
                <li><a href="{FAV_TGT}" title="" rel="nofollow"><span>{FAV_ICO}&nbsp;{FAV_LNK}</span></a></li>
            <!-- END favpics -->
            <!-- BEGIN browse_by_date -->
                <li><a href="{BROWSEBYDATE_TGT}" title="" rel="nofollow" class="greybox"><span>{BROWSEBYDATE_ICO}&nbsp;{BROWSEBYDATE_LNK}</span></a></li>
            <!-- END browse_by_date -->
        </ul>
    </li>
<!-- END album_list -->

<!-- BEGIN my_gallery -->
    <li>
        <a href="{MY_GAL_TGT}" title="" class="firstlevel"><span class="firstlevel">{MY_GAL_ICO}&nbsp;{MY_GAL_LNK}</span></a>
        <ul>
            <!-- BEGIN my_profile -->
                <li><a href="{MY_PROF_TGT}" title=""><span>{MY_PROF_ICO}&nbsp;{MY_PROF_LNK}</span></a></li>
            <!-- END my_profile -->
            <!-- BEGIN allow_memberlist -->
                <li><a href="{MEMBERLIST_TGT}" title=""><span>{MEMBERLIST_ICO}&nbsp;{MEMBERLIST_LNK}</span></a></li>
            <!-- END allow_memberlist -->
            <!-- BEGIN upload_approval -->
                <li><a href="{UPL_APP_TGT}" title=""><span>{UPL_APP_ICO}&nbsp;{UPL_APP_LNK}</span></a></li>
            <!-- END upload_approval -->
            <!-- BEGIN enter_admin_mode -->
                <li><a href="{ADM_MODE_TGT}" title=""><span>{ADM_MODE_ICO}&nbsp;{ADM_MODE_LNK}</span></a></li>
            <!-- END enter_admin_mode -->
            <!-- BEGIN leave_admin_mode -->
                <li><a href="{USR_MODE_TGT}" title=""><span>{USR_MODE_ICO}&nbsp;{USR_MODE_LNK}</span></a></li>
            <!-- END leave_admin_mode -->
        </ul>
    </li>
<!-- END my_gallery -->


<!-- BEGIN upload_pic -->
    <li>
        <a href="{UPL_PIC_TGT}" title="" class="firstlevel"><span class="firstlevel">{UPL_PIC_ICO}{UPL_PIC_LNK}</span></a>
    </li>
<!-- END upload_pic -->
</ul>

EOT;


// HTML template for gallery admin menu
$template_gallery_admin_menu = <<<EOT
              <ul class="dropmenu">
                                <li>
                                    <a href="#" title="" class="firstlevel"><span class="firstlevel">{FILES_ICO}&nbsp;{FILES_LNK}</span></a>
                                    <ul>
                                    <!-- BEGIN admin_approval -->
                                        <li><a href="editpics.php?mode=upload_approval" title=""><span>{UPL_APP_ICO}&nbsp;{UPL_APP_LNK}</span></a></li>
                                    <!-- END admin_approval -->
                                    <!-- BEGIN catmgr -->
                                        <li><a href="catmgr.php" title=""><span>{CATEGORIES_ICO}&nbsp;{CATEGORIES_LNK}</span></a></li>
                                    <!-- END catmgr -->
                                    <!-- BEGIN albmgr -->
                                        <li><a href="albmgr.php{CATL}" title=""><span>{ALBUMS_ICO}&nbsp;{ALBUMS_LNK}</span></a></li>
                                    <!-- END albmgr -->
                                    <!-- BEGIN picmgr -->
                                        <li><a href="picmgr.php" title=""><span>{PICTURES_ICO}&nbsp;{PICTURES_LNK}</span></a></li>
                                    <!-- end picmgr -->
                                    <!-- BEGIN batch_add -->
                                        <li><a href="searchnew.php" title=""><span>{SEARCHNEW_ICO}&nbsp;{SEARCHNEW_LNK}</span></a></li>
                                    <!-- END batch_add -->
                                    <!-- BEGIN admin_tools -->
                                        <li><a href="util.php?t={TIME_STAMP}#admin_tools" title=""><span>{UTIL_ICO}&nbsp;{UTIL_LNK}</span></a></li>
                                    <!-- END admin_tools -->
                                    </ul>
                                </li>
                                <li>
                                    <a href="#" class="firstlevel" title=""><span class="firstlevel">{INFO_ICO}&nbsp;{INFO_LNK}</span></a>
                                    <ul>
                                    <!-- BEGIN review_comments -->
                                        <li><a href="reviewcom.php" title=""><span>{COMMENTS_ICO}&nbsp;{COMMENTS_LNK}</span></a></li>
                                    <!-- END review_comments -->
                                    <!-- BEGIN log_ecards -->
                                        <li><a href="db_ecard.php" title=""><span>{DB_ECARD_ICO}&nbsp;{DB_ECARD_LNK}</span></a></li>
                                    <!-- END log_ecards -->
                                    <!-- BEGIN view_log_files -->
                                        <li><a href="viewlog.php" title=""><span>{VIEW_LOG_FILES_ICO}&nbsp;{VIEW_LOG_FILES_LNK}</span></a></li>
                                    <!-- END view_log_files -->
                                    <!-- BEGIN overall_stats -->
                                        <li><a href="stat_details.php?type=hits&amp;sort=sdate&amp;dir=&amp;sdate=1&amp;ip=1&amp;search_phrase=0&amp;referer=0&amp;browser=1&amp;os=1&amp;mode=fullscreen&amp;page=1&amp;amount=50" title="" ><span>{OVERALL_STATS_ICO}{OVERALL_STATS_LNK}</span></a></li>
                                    <!-- END overall_stats -->
                                    <!-- BEGIN check_versions -->
                                        <li><a href="versioncheck.php" title=""><span>{CHECK_VERSIONS_ICO}&nbsp;{CHECK_VERSIONS_LNK}</span></a></li>
                                    <!-- END check_versions -->
                                    <!-- BEGIN php_info -->
                                        <li><a href="phpinfo.php" title=""><span>{PHPINFO_ICO}&nbsp;{PHPINFO_LNK}</span></a></li>
                                    <!-- END php_info -->
                                    <!-- BEGIN show_news -->
                                        <li><a href="mode.php?what=news&amp;referer=$REFERER" title=""><span>{SHOWNEWS_ICO}&nbsp;{SHOWNEWS_LNK}</span></a></li>
                                    <!-- END show_news -->
                                    <!-- BEGIN documentation -->
                                        <li><a href="{DOCUMENTATION_HREF}" title=""><span>{DOCUMENTATION_ICO}&nbsp;{DOCUMENTATION_LNK}</span></a></li>
                                    <!-- END documentation -->
                                    </ul>
                                </li>
                            <!-- BEGIN config -->
                                <li>
                                    <a href="admin.php" title="" class="firstlevel"><span class="firstlevel">{ADMIN_ICO}&nbsp;{ADMIN_LNK}</span></a>
                                    <ul>
                                    <!-- BEGIN keyword_manager -->
                                        <li><a href="keywordmgr.php" title=""><span>{KEYWORDMGR_ICO}&nbsp;{KEYWORDMGR_LNK}</span></a></li>
                                    <!-- END keyword_manager -->
                                    <!-- BEGIN exif_manager -->
                                        <li><a href="exifmgr.php" title=""><span>{EXIFMGR_ICO}&nbsp;{EXIFMGR_LNK}</span></a></li>
                                    <!-- END exif_manager -->
                                    <!-- BEGIN plugin_manager -->
                                        <li><a href="pluginmgr.php" title=""><span>{PLUGINMGR_ICO}&nbsp;{PLUGINMGR_LNK}</span></a></li>
                                    <!-- END plugin_manager -->
                                    <!-- BEGIN bridge_manager -->
                                        <li><a href="bridgemgr.php" title=""><span>{BRIDGEMGR_ICO}&nbsp;{BRIDGEMGR_LNK}</span></a></li>
                                    <!-- END bridge_manager -->
                                    <!-- BEGIN update_database -->
                                        <li><a href="update.php" title=""><span>{UPDATE_DATABASE_ICO}&nbsp;{UPDATE_DATABASE_LNK}</span></a></li>
                                    <!-- END update_database -->
                                    </ul>
                                </li>
                            <!-- BEGIN usermgr -->
                                <li>
                                    <a href="usermgr.php" title="" class="firstlevel"><span class="firstlevel">{USERS_ICO}{USERS_LNK}</span></a>
                                    <ul>
                                    <!-- BEGIN banmgr -->
                                        <li><a href="banning.php" title=""><span>{BAN_ICO}{BAN_LNK}</span></a></li>
                                    <!-- END banmgr -->
                                    <!-- BEGIN groupmgr -->
                                        <li><a href="groupmgr.php" title=""><span>{GROUPS_ICO}{GROUPS_LNK}</span></a></li>
                                    <!-- END groupmgr -->
                                    <!-- BEGIN admin_profile -->
                                        <li><a href="profile.php?op=edit_profile" title=""><span>{MY_PROF_ICO}{MY_PROF_LNK}</span></a></li>
                                    <!-- END admin_profile -->
                                    </ul>
                                </li>
                            <!-- END usermgr -->
                                    </ul>
                                </li>
                            <!-- END config -->
              </ul>
            
EOT;


// HTML template for user admin menu
$template_user_admin_menu = <<<EOT
							<ul class="dropmenu">
                                <li>
									<a href="profile.php?op=edit_profile" title="{MY_PROF_TITLE}" class="firstlevel"><span class="firstlevel">{MY_PROF_ICO}{MY_PROF_LNK}</span></a>
									<ul>
										<li><a href="albmgr.php" title="{ALBMGR_TITLE}"><span>{ALBUMS_ICO}{ALBMGR_LNK}</span></a></li>
										<li><a href="modifyalb.php" title="{MODIFYALB_TITLE}"><span>{MODIFYALB_ICO}{MODIFYALB_LNK}</span></a></li>
										<li><a href="picmgr.php" title="{PICTURES_TITLE}"><span>{PICTURES_ICO}{PICTURES_LNK}</span></a></li>
									</ul>
								</li>
							</ul>

EOT;

// Function to start a 'standard' table
function starttable($width = '-1', $title = '', $title_colspan = '1', $zebra_class = '')
{
    global $CONFIG;

    if ($width == '-1') $width = $CONFIG['picture_table_width'];
    if ($width == '100%') $width = $CONFIG['main_table_width'];
    echo <<<EOT

<!-- Start standard table -->
<table align="center" width="$width" cellspacing="1" cellpadding="0" class="maintable $zebra_class">

EOT;
    if ($title) {
        echo <<<EOT
        <tr>
                <td class="" colspan="$title_colspan">
					<div class="cpg_starttable_outer">
						<div class="cpg_starttable_inner">
							$title
						</div>
					</div>
				</td>
        </tr>

EOT;
    } else {
	}
}

/******************************************************************************
** Section <<<$template_film_strip>>> - START
******************************************************************************/
// HTML template for filmstrip display
$template_film_strip = <<<EOT
<table><tr><td class="filmstrip_background" style="background-color: transparent; overflow-y: hidden; background-image: url({TILE1});"><img src="images/spacer.gif" width="28" height="1" alt="" border="0" /></td>
                   <td class="filmstrip_background"><table>
                     <tr>
                       <td class="prev_strip"></td>
                     </tr>
                     <tr>
                       <td valign="bottom"  style="{THUMB_TD_STYLE}" class="filmstrip_background" height="100%">
                       <div id="film"><table class="tape">{THUMB_STRIP}</table></div>
                       </td>
                     </tr>
                   <tr>
                     <td class="next_strip"></td>
                   </tr>
                  </table></td>
 
          <td class="filmstrip_background" style="background-color: transparent; background-image: url({TILE2});"><img src="images/spacer.gif" width="28" height="1" alt="" border="0" /></td>
        </tr>        
</table>

<!-- BEGIN thumb_cell -->
                <tr class="filmstrip_background"><td class="thumb" style="height:{$CONFIG['thumb_width']}px;vertical-align: middle; text-align: center;">
                  <a href="{LINK_TGT}" class="thumbLink" style="{ONE_WIDTH}">{THUMB}</a>
                </td></tr>
<!-- END thumb_cell -->
<!-- BEGIN empty_cell -->
                <tr><td valign="top" align="center" >&nbsp;</td></tr>
<!-- END empty_cell -->

EOT;
/******************************************************************************
** Section <<<$template_film_strip>>> - END
******************************************************************************/

/******************************************************************************
** Section <<<$template_breadcrumb>>> - START
******************************************************************************/
// HTML template for the breadcrumb
$template_breadcrumb = <<<EOT
<!-- BEGIN breadcrumb -->
        <tr>
            <td colspan="3" align="left">
    			<div class="cpg_starttable_outer">
    				<div class="cpg_starttable_inner">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="statlink">
                                    {BREADCRUMB}
                                </td>
                                <td class="statlink">
                                    <img src="images/spacer.gif" width="1" height="25" border="0" alt="" />
                                </td>
                            </tr>
                        </table>
    				</div>
    			</div>
            </td>
        </tr>
<!-- END breadcrumb -->
<!-- BEGIN breadcrumb_user_gal -->
        <tr>
            <td colspan="3" align="left">
    			<div class="cpg_starttable_outer">
    				<div class="cpg_starttable_inner">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="statlink">
                                    {BREADCRUMB}
                                </td>
                                <td class="statlink">
                                    {STATISTICS}
                                </td>
                                <td class="statlink">
                                    <img src="images/spacer.gif" width="1" height="25" border="0" alt="" />
                                </td>
                            </tr>
                        </table>
    				</div>
    			</div>
            </td>
        </tr>
<!-- END breadcrumb_user_gal -->

EOT;
/******************************************************************************
** Section <<<$template_breadcrumb>>> - END
******************************************************************************/

// HTML template for intermediate image display
$template_display_media = <<<EOT
        <tr>
                <td align="center" class="display_media" nowrap="nowrap">
                        <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                        <td align="center" style="{SLIDESHOW_STYLE}">
                                                {IMAGE}
                                        </td>
                                </tr>
                        </table>
                </td>
            </tr>
            <tr>
                <td>
                        <table width="100%" cellspacing="2" cellpadding="0" class="tableb tableb_alternate">
                                        <tr>
                                                <td align="center">
                                                        {ADMIN_MENU}
                                                </td>
                                        </tr>
                        </table>


<!-- BEGIN img_desc -->
                        <table cellpadding="0" cellspacing="0" class="tableb tableb_alternate" width="100%">
<!-- BEGIN title -->
                                <tr>
                                        <td class="tableb tableb_alternate"><h1 class="pic_title">
                                                {TITLE}
                                        </h1></td>
                                </tr>
<!-- END title -->
<!-- BEGIN caption -->
                                <tr>
                                        <td class="tableb tableb_alternate"><h2 class="pic_caption">
                                                {CAPTION}
                                        </h2></td>
                                </tr>
<!-- END caption -->
                        </table>
<!-- END img_desc -->
                </td>
        </tr>

EOT;


// Function to display the film strip
function theme_display_film_strip(&$thumb_list, $nbThumb, $album_name, $aid, $cat, $pos, $sort_options, $mode = 'thumb', $date='', $filmstrip_prev_pos, $filmstrip_next_pos,$max_block_items,$thumb_width)
{
    global $CONFIG, $THEME_DIR;
    global $template_film_strip, $lang_film_strip, $lang_common, $pic_count,$mar_pic;

    $superCage = Inspekt::makeSuperCage();

    static $template = '';
    static $thumb_cell = '';
    static $empty_cell = '';
    static $spacer = '';

    if (defined('THEME_HAS_FILM_STRIP_GRAPHIC')) { set_js_var('vertstrip', 1); }

    if ((!$template)) {
        $template = $template_film_strip;
        $thumb_cell = template_extract_block($template, 'thumb_cell');
        $empty_cell = template_extract_block($template, 'empty_cell');
    }

    $cat_link = is_numeric($aid) ? '' : '&amp;cat=' . $cat;
    $date_link = $date=='' ? '' : '&amp;date=' . $date;

    if ($superCage->get->getInt('uid')) {
        $uid_link = '&amp;uid=' . $superCage->get->getInt('uid');
    } else {
        $uid_link = '';
    }

    $i = 0;
    $thumb_strip = '';
    foreach($thumb_list as $thumb) {
        $i++;
        if ($mode == 'thumb') {
            if ($thumb['pos'] == $pos && !$superCage->get->keyExists('film_strip')) {
                    $thumb['image'] = str_replace('class="image"', 'class="image middlethumb"', $thumb['image']);
            }
            // determine if thumbnail link targets should open in a pop-up
            if ($CONFIG['thumbnail_to_fullsize'] == 1) { // code for full-size pop-up
                if (!USER_ID && $CONFIG['allow_unlogged_access'] <= 2) {
                    $target = 'javascript:;" onclick="alert(\''.sprintf($lang_errors['login_needed'],'','','','').'\');';
                } elseif (USER_ID && USER_ACCESS_LEVEL <= 2) {
                    $target = 'javascript:;" onclick="alert(\''.sprintf($lang_errors['access_intermediate_only'],'','','','').'\');';
                } else {
                    $target = 'javascript:;" onclick="MM_openBrWindow(\'displayimage.php?pid=' . $thumb['pid'] . '&fullsize=1\',\'' . uniqid(rand()) . '\',\'scrollbars=yes,toolbar=no,status=no,resizable=yes,width=' . ((int)$thumb['pwidth']+(int)$CONFIG['fullsize_padding_x']) .  ',height=' .   ((int)$thumb['pheight']+(int)$CONFIG['fullsize_padding_y']). '\');';
                }
            } elseif ($aid == 'lastcom' || $aid == 'lastcomby') {
                $page = cpg_get_comment_page_number($thumb['msg_id']);
                $page = (is_numeric($page)) ? "&amp;page=$page" : '';
                $target = "displayimage.php?album=$aid$cat_link$date_link&amp;pid={$thumb['pid']}$uid_link&amp;msg_id={$thumb['msg_id']}$page#comment{$thumb['msg_id']}";
            } else {
                $target = "displayimage.php?album=$aid$cat_link$date_link&amp;pid={$thumb['pid']}$uid_link#top_display_media";
            }
            $params = array(
                '{LINK_TGT}' => $target,
                '{THUMB}' => $thumb['image'],
                '{ONE_WIDTH}'  => "width:".$thumb_width."px; float: left" ,
                );
        } else {
            $params = array(
                '{LINK_TGT}' => "index.php?cat={$thumb['cat']}",
                '{THUMB}' => $thumb['image'],
                '{ONE_WIDTH}'  => "width:".$thumb_width."px; float: left" ,
                );
        }
        $thumb_strip .= template_eval($thumb_cell, $params);
    }

        $tile1 = $THEME_DIR . 'images/tile1.gif';
        $tile2 = $THEME_DIR . 'images/tile2.gif';


    if (defined('THEME_HAS_NAVBAR_GRAPHICS')) {
        $location = $THEME_DIR;
    } else {
        $location= '';
    }
    $max_itme_width_ul = $max_block_items;
    if(($max_block_items%2)==0){
        $max_itme_width_ul = $max_block_items +1;
    }
    $set_width_to_film = "width:".($max_block_items*($thumb_width+4))."px; position:relative;";

    $params = array('{THUMB_STRIP}' => $thumb_strip,
        '{COLS}' => $i,
        '{TILE1}' => $tile1,
        '{TILE2}' => $tile2,
        '{SET_WIDTH}'  => $set_width_to_film,
        );

    ob_start();
    echo '<div id="filmstrip">';
    if (!defined('THEME_HAS_FILM_STRIP_GRAPHIC')) { starttable($CONFIG['picture_table_width']); }
    echo template_eval($template, $params);
    if (!defined('THEME_HAS_FILM_STRIP_GRAPHIC')) { endtable(); }
    echo '</div>';
    $film_strip = ob_get_contents();
    ob_end_clean();

    return $film_strip;
}


function theme_display_image($nav_menu, $picture, $votes, $pic_info, $comments, $film_strip)
{
    global $CONFIG, $LINEBREAK;

    $superCage = Inspekt::makeSuperCage();

    $width = $CONFIG['picture_table_width'];

    echo '<a name="top_display_media"></a>'; // set the navbar-anchor
    starttable();
    echo $nav_menu;
    endtable();

    starttable();

    echo "<tr>";
    echo "<td height='100%'><table width='100%'><!-- gb before picture -->";    
    echo $picture;
    echo "</table></td><!-- gb after picture -->";
    if ($CONFIG['display_film_strip'] == 1) {
    echo "<td width='200' height='100%' class=\"tablebspecial\"><!-- gb before film_strip -->";
          echo $film_strip;
    echo "</td><!-- gb after film_strip -->";
    }
    echo "</tr>";


    endtable();


    echo $votes;

    $picinfo = $superCage->cookie->keyExists('picinfo') ? $superCage->cookie->getAlpha('picinfo') : ($CONFIG['display_pic_info'] ? 'block' : 'none');
    echo $LINEBREAK . '<div id="picinfo" style="display: '.$picinfo.';">' . $LINEBREAK;
    starttable();
    echo $pic_info;
    endtable();
    echo '</div>' . $LINEBREAK;

    echo '<a name="comments_top"></a>';
    echo '<div id="comments">' . $LINEBREAK;
    echo $comments;
    echo '</div>' . $LINEBREAK;

}


function theme_main_menu($which)
{
    global $AUTHORIZED, $CONFIG, $album, $actual_cat, $cat, $REFERER, $CPG_PHP_SELF;
    global $lang_main_menu, $template_sys_menu, $template_sub_menu, $lang_gallery_admin_menu;

    static $sys_menu = '', $sub_menu = '';
    if ($$which != '') {
        return $$which;
    }

    //Check whether user has permission to upload file to the current album if any
    $upload_allowed = false;
    if (isset($album) && is_numeric($album)) {
        if (GALLERY_ADMIN_MODE) {
            $upload_allowed = true;
        } else {
            if (USER_ID) {
                $query = "SELECT null FROM {$CONFIG['TABLE_ALBUMS']} WHERE category='" . (FIRST_USER_CAT + USER_ID) . "' AND aid = '$album'";
                $user_albums = cpg_db_query($query);
                if (mysql_num_rows($user_albums)) {
                    $upload_allowed = true;
                } else {
                    $upload_allowed = false;
                }
            }

            if (!$upload_allowed) {
                $query = "SELECT null FROM {$CONFIG['TABLE_ALBUMS']} WHERE category < " . FIRST_USER_CAT . " AND uploads='YES' AND (visibility = '0' OR visibility IN ".USER_GROUP_SET.") AND aid = '$album'";
                $public_albums = cpg_db_query($query);

                if (mysql_num_rows($public_albums)) {
                    $upload_allowed = true;
                } else {
                    $upload_allowed = false;
                }
            }
        }
    }

    $album_l = isset($album) ? "?album=$album" : '';
    $album_12 = ($upload_allowed) ? "?album=$album" : '';
    $cat_l = (isset($actual_cat))? "?cat=$actual_cat" : (isset($cat) ? "?cat=$cat" : '?cat=0');
    $cat_l2 = isset($cat) ? "&amp;cat=$cat" : '';
    $my_gallery_id = FIRST_USER_CAT + USER_ID;

  if ($which == 'sys_menu' ) {
    if (USER_ID) { // visitor is logged in
        template_extract_block($template_sys_menu, 'login');
        if ($CONFIG['contact_form_registered_enable'] == 0) {
          template_extract_block($template_sys_menu, 'contact');
        }
        if ($CONFIG['display_sidebar_user'] != 2) {
          template_extract_block($template_sys_menu, 'sidebar');
        }
        
        list($timestamp, $form_token) = getFormToken();
        
    } else { // visitor is not logged in
        if ($CONFIG['contact_form_guest_enable'] == 0) {
          template_extract_block($template_sys_menu, 'contact');
        }
        if ($CONFIG['display_sidebar_guest'] != 2) {
          template_extract_block($template_sys_menu, 'sidebar');
        }
        template_extract_block($template_sys_menu, 'logout');
        template_extract_block($template_sys_menu, 'my_profile');
        
        $timestamp = $form_token = '';
    }

    if (!USER_IS_ADMIN) {
        template_extract_block($template_sys_menu, 'enter_admin_mode');
        template_extract_block($template_sys_menu, 'leave_admin_mode');
    } else {
        if (GALLERY_ADMIN_MODE) {
            template_extract_block($template_sys_menu, 'enter_admin_mode');
        } else {
            template_extract_block($template_sys_menu, 'leave_admin_mode');
        }
    }

    if (!USER_CAN_CREATE_ALBUMS) {
        template_extract_block($template_sys_menu, 'my_gallery');
    }

    if (USER_CAN_CREATE_ALBUMS) {
        template_extract_block($template_sys_menu, 'my_profile');
    }

    if (!USER_CAN_UPLOAD_PICTURES && !USER_CAN_CREATE_ALBUMS) {
        template_extract_block($template_sys_menu, 'upload_pic');
    }

    if (USER_ID || !$CONFIG['allow_user_registration']) {
        template_extract_block($template_sys_menu, 'register');
    }

    if (!USER_ID || !$CONFIG['allow_memberlist'] || GALLERY_ADMIN_MODE) {
        template_extract_block($template_sys_menu, 'allow_memberlist');
    }

    $param = array(
        '{HOME_TGT}' => $CONFIG['home_target'],
    '{HOME_ICO}' => cpg_fetch_icon('home', 1),
        '{HOME_LNK}' => $lang_main_menu['home_lnk'],
        '{CONTACT_TGT}' => "contact.php?referer=$REFERER",
    '{CONTACT_ICO}' => cpg_fetch_icon('contact', 1),
        '{CONTACT_LNK}' => $lang_main_menu['contact_lnk'],
        '{MY_GAL_TGT}' => "index.php?cat=$my_gallery_id",
    '{MY_GAL_ICO}' => cpg_fetch_icon('my_gallery', 1),
        '{MY_GAL_LNK}' => $lang_main_menu['my_gal_lnk'],
        '{MEMBERLIST_TGT}' => "usermgr.php",
    '{MEMBERLIST_ICO}' => cpg_fetch_icon('memberlist', 1),
        '{MEMBERLIST_LNK}' => $lang_main_menu['memberlist_lnk'],
        '{MY_PROF_TGT}' => "profile.php?op=edit_profile",
    '{MY_PROF_ICO}' => cpg_fetch_icon('my_profile', 1),
        '{MY_PROF_LNK}' => $lang_main_menu['my_prof_lnk'],
        '{ADM_MODE_TGT}' => "mode.php?admin_mode=1&amp;referer=$REFERER",
    '{ADM_MODE_ICO}' => cpg_fetch_icon('admin_mode_on', 1),
        '{ADM_MODE_LNK}' => $lang_main_menu['adm_mode_lnk'],
        '{USR_MODE_TGT}' => "mode.php?admin_mode=0&amp;referer=$REFERER",
    '{USR_MODE_ICO}' => cpg_fetch_icon('admin_mode_off', 1),
        '{USR_MODE_LNK}' => $lang_main_menu['usr_mode_lnk'],
        '{SIDEBAR_TGT}' => "sidebar.php?action=install",
        '{SIDEBAR_LNK}' => $lang_main_menu['sidebar_lnk'],
    '{SIDEBAR_ICO}' => cpg_fetch_icon('sidebar', 1),
        '{UPL_PIC_TGT}' => "upload.php$album_12",
        '{UPL_PIC_LNK}' => $lang_main_menu['upload_pic_lnk'],
    '{UPL_PIC_ICO}' => cpg_fetch_icon('upload', 1),
        '{REGISTER_TGT}' => "register.php",
        '{REGISTER_LNK}' => $lang_main_menu['register_lnk'],
    '{REGISTER_ICO}' => cpg_fetch_icon('add_user', 1),
        '{LOGIN_LNK}' => $lang_main_menu['login_lnk'],
    '{LOGIN_ICO}' => cpg_fetch_icon('login', 1),
        '{LOGOUT_TGT}' => "logout.php?form_token=$form_token&amp;timestamp=$timestamp&amp;referer=$REFERER",
        '{LOGOUT_LNK}' => $lang_main_menu['logout_lnk'] . " [" . stripslashes(USER_NAME) . "]",
    '{LOGOUT_ICO}' => cpg_fetch_icon('logout', 1),
        '{UPL_APP_LNK}' => $lang_gallery_admin_menu['upl_app_lnk'],
        '{UPL_APP_TGT}' => "editpics.php?mode=upload_approval",
    '{UPL_APP_ICO}' => cpg_fetch_icon('file_approval', 1),
    '{ALB_LIST_TGT}' => "index.php$cat_l",
        '{ALB_LIST_LNK}' => $lang_main_menu['alb_list_lnk'],
    '{ALB_LIST_ICO}' => cpg_fetch_icon('alb_mgr', 1),
        '{CUSTOM_LNK_TGT}' => $CONFIG['custom_lnk_url'],
        '{CUSTOM_LNK_LNK}' => $CONFIG['custom_lnk_name'],
    '{CUSTOM_ICO}' => cpg_fetch_icon('online', 1),
        '{LASTUP_TGT}' => "thumbnails.php?album=lastup$cat_l2",
        '{LASTUP_LNK}' => $lang_main_menu['lastup_lnk'],
    '{LASTUP_ICO}' => cpg_fetch_icon('last_uploads', 1),
        '{LASTCOM_TGT}' => "thumbnails.php?album=lastcom$cat_l2",
        '{LASTCOM_LNK}' => $lang_main_menu['lastcom_lnk'],
    '{LASTCOM_ICO}' => cpg_fetch_icon('comment', 1),
        '{TOPN_TGT}' => "thumbnails.php?album=topn$cat_l2",
        '{TOPN_LNK}' => $lang_main_menu['topn_lnk'],
    '{TOPN_ICO}' => cpg_fetch_icon('most_viewed', 1),
        '{TOPRATED_TGT}' => "thumbnails.php?album=toprated$cat_l2",
        '{TOPRATED_LNK}' => $lang_main_menu['toprated_lnk'],
    '{TOPRATED_ICO}' => cpg_fetch_icon('top_rated', 1),
        '{FAV_TGT}' => "thumbnails.php?album=favpics",
        '{FAV_LNK}' => $lang_main_menu['fav_lnk'],
    '{FAV_ICO}' => cpg_fetch_icon('favorites', 1),
        '{BROWSEBYDATE_TGT}' => 'calendar.php',
        '{BROWSEBYDATE_LNK}' => $lang_main_menu['browse_by_date_lnk'],
    '{BROWSEBYDATE_ICO}' => cpg_fetch_icon('calendar', 1),
        '{SEARCH_TGT}' => "search.php",
        '{SEARCH_LNK}' => $lang_main_menu['search_lnk'],
    '{SEARCH_ICO}' => cpg_fetch_icon('search', 1),
        '{UPL_APP_LNK}' => $lang_gallery_admin_menu['upl_app_lnk'],
        '{UPL_APP_TGT}' => "editpics.php?mode=upload_approval",
    '{UPL_APP_ICO}' => cpg_fetch_icon('file_approval', 1),
        );


        if ($CPG_PHP_SELF != 'login.php') {
            $param['{LOGIN_TGT}'] = "login.php?referer=$REFERER";
        } else {
            $param['{LOGIN_TGT}'] = "login.php";
        }

    if (!$CONFIG['custom_lnk_url']) {
        template_extract_block($template_sys_menu, 'custom_link');
    } 
    
    $sys_menu = template_eval($template_sys_menu, $param);
  }
  return $$which;
}



?>