<?php
/**************************************************
  Coppermine 1.5.x Plugin - Picture Annotation (annotate)
  *************************************************
  Copyright (c) 2003-2009 Coppermine Dev Team
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

if (defined('DISPLAYIMAGE_PHP')) {
    $thisplugin->add_filter('page_meta','annotate_meta');
    $thisplugin->add_filter('file_data','annotate_file_data');
}
$thisplugin->add_action('plugin_configure','annotate_configure');
$thisplugin->add_action('page_start','annotate_page_start');
$thisplugin->add_action('plugin_install','annotate_install');
$thisplugin->add_action('plugin_uninstall','annotate_uninstall');
$thisplugin->add_action('plugin_cleanup','annotate_cleanup');
$thisplugin->add_filter('meta_album_get_pic_pos','annotate_get_pic_pos');
$thisplugin->add_filter('meta_album', 'annotate_meta_album');

function annotate_meta($meta){
    global $JS, $CONFIG, $lang_common, $lang_plugin_annotate;
    require_once './plugins/annotate/init.inc.php';
    $annotate_init_array = annotate_initialize();
    $lang_plugin_annotate = $annotate_init_array['language'];
    $annotate_icon_array = $annotate_init_array['icon'];
    if (in_array('plugins/annotate/lib/httpreq.js', $JS['includes']) != TRUE) {
        $JS['includes'][] = 'plugins/annotate/lib/httpreq.js';
    }
    if (in_array('plugins/annotate/lib/photonotes.js', $JS['includes']) != TRUE) {
        $JS['includes'][] = 'plugins/annotate/lib/photonotes.js';
    }
    set_js_var('lang_annotate_save', $lang_plugin_annotate['save']);
    set_js_var('lang_annotate_cancel', $lang_plugin_annotate['cancel']);
    set_js_var('lang_annotate_delete', $lang_common['delete']);
    set_js_var('lang_annotate_error_saving_note', $lang_plugin_annotate['error_saving_note']);
    set_js_var('lang_annotate_onsave_not_implemented', $lang_plugin_annotate['onsave_not_implemented']);
    set_js_var('lang_annotate_all_pics_of', str_replace('&quot;', '"', $lang_plugin_annotate['all_pics_of']));
    set_js_var('lang_annotate_note_empty', $lang_plugin_annotate['note_empty']);
    set_js_var('lang_annotate_annotated_by', $lang_plugin_annotate['annotated_by']);
    set_js_var('lang_annotate_view_profile', $lang_plugin_annotate['view_profile']);
    set_js_var('icon_annotate_ok', $annotate_icon_array['ok']);
    set_js_var('icon_annotate_cancel', $annotate_icon_array['cancel']);
    set_js_var('icon_annotate_delete', $annotate_icon_array['delete']);
    if (GALLERY_ADMIN_MODE) {
        set_js_var('visitor_annotate_permission_level', 3);
    } else {
        set_js_var('visitor_annotate_permission_level', annotate_get_level('permissions'));
    }
    set_js_var('visitor_annotate_user_id', USER_ID);
    set_js_var('annotate_notes_editable', annotate_notes_editable());
    $meta  .= '<link rel="stylesheet" href="plugins/annotate/lib/photonotes.css" type="text/css" />';
    return $meta;
}

function annotate_file_data($data){
    global $CONFIG, $LINEBREAK, $lang_plugin_annotate, $annotate_icon_array, $REFERER;
    // Determine if the visitor is allowed to have that button
    if (annotate_get_level('permissions') >= 2) {

        $menu_buttons = "";

        // list existing annotations of the currently viewed album
        $btns_person = "";
        if (annotate_get_level('display_notes') == 1) {
            $superCage = Inspekt::MakeSuperCage();
            if ($superCage->get->testInt('album')) {
                $result = cpg_db_query("
                    SELECT DISTINCT note FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate n
                    INNER JOIN {$CONFIG['TABLE_PICTURES']} p
                    ON p.pid = n.pid
                    WHERE p.aid = ".$superCage->get->getInt('album')."
                    ORDER BY note
                ");

                if (mysql_num_rows($result)) {
                    $btns_person .= "<div id=\"btns_person\" style=\"white-space:normal; cursor:default;\"> {$lang_plugin_annotate['rapid_annotation']}: ";
                    while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                        $note = stripslashes($row[0]);
                        $btns_person .= "<button onclick=\"return addnote('{$row[0]}')\" class=\"admin_menu\" title=\"".sprintf($lang_plugin_annotate['annotate_x_on_this_pic'], $note)."\">$note</button> ";
                    }
                    $btns_person .= "</div><hr />";
                    $data['menu'] = $btns_person.$data['menu'];
                }
                mysql_free_result($result);
            }
        }

        $livesearch_button = '<input id="livesearch_input" type="text" class="textinput" size="8" title="'.$lang_plugin_annotate['filter_annotations'].'" style="cursor:help; padding-right: 16px; background-image: url(images/icons/search.png); background-repeat: no-repeat; background-position: right center;" />';
        $livesearch_script = <<< EOT
            $(document).ready(function() {
                var alertTimerId = 0;
                $('#livesearch_input').keyup(function() {
                    $('#livesearch_input').addClass('blue');
                    clearTimeout(alertTimerId);
                    alertTimerId = setTimeout(function () {
                        $.post('index.php?file=annotate/reqserver', {livesearch:'1',q:$('#livesearch_input').val()}, function(data) { 
                            $('#livesearch_output').html(data); 
                            $('#livesearch_input').removeClass('blue');
                        });
                    }, 250);
                });
            });
EOT;

        if ($CONFIG['plugin_annotate_type'] > 0) {
            // free text
            if ($CONFIG['plugin_annotate_type'] == 1 || $CONFIG['plugin_annotate_type'] == 3) {
                $menu_buttons .= <<< EOT
                <script type="text/javascript">
                    document.write('<li><a href="javascript:void();" class="admin_menu" title="{$lang_plugin_annotate['plugin_name']}" onclick="return addnote(\'\');" rel="nofollow">');
                    document.write('{$annotate_icon_array['annotate']}{$lang_plugin_annotate['annotate']}');
                    document.write('</a></li>');
                </script>
EOT;
            }

            if ($CONFIG['plugin_annotate_type'] == 2 || $CONFIG['plugin_annotate_type'] == 3) {
                $select_options = "<option selected=\"selected\" disabled=\"disabled\">-- {$lang_plugin_annotate['annotate']} --</option>";
                $result = mysql_query("SELECT note FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate GROUP BY note ORDER BY note ASC");
                while ($row = mysql_fetch_row($result)) {
                    $select_options .= "<option value=\"{$row[0]}\">{$row[0]}</option>";
                }
                $menu_buttons .= <<< EOT
                <script type="text/javascript">
                    document.write('<li>&nbsp;<select id="livesearch_output" size="1" class="button" onchange="return addnote(this.options[this.selectedIndex].value);">$select_options</select>$livesearch_button</li>');
                    $livesearch_script
                </script>
EOT;
            }
        } else {
            // user list
            $select_options = "<option selected=\"selected\" disabled=\"disabled\">-- {$lang_plugin_annotate['annotate']} --</option>";
            $result = mysql_query("SELECT user_id, user_name FROM {$CONFIG['TABLE_USERS']} ORDER BY user_name ASC");
            while ($row = mysql_fetch_assoc($result)) {
                $select_options .= "<option value=\"{$row['user_name']}\">{$row['user_name']}</option>";
            }
            $menu_buttons .= <<< EOT
            <script type="text/javascript">
                document.write('<li>&nbsp;<select id="livesearch_output" size="1" class="button" onchange="return addnote(this.options[this.selectedIndex].value);">$select_options</select>$livesearch_button</li>');
                $livesearch_script
            </script>
EOT;
        }
    }

    if (is_image($data['filename'])) {
        if (function_exists(panorama_viewer_is_360_degree_panorama)) {
            // disable on 360� panoramas
            if (panorama_viewer_is_360_degree_panorama()) {
                return $data;
            }
        }
        
        global $cpg_udb;
        $sql = "SELECT n.*, u.".$cpg_udb->field['username']." AS user_name FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate n INNER JOIN ".$cpg_udb->usertable." u ON n.user_id = u.".$cpg_udb->field['user_id']." WHERE n.pid = {$data['pid']}";
        $result = cpg_db_query($sql);

        $notes = array();

        while ($row = mysql_fetch_assoc($result)) {
            //$row['note'] = addslashes($row['note']);
            $notes[] = $row;
        }

        mysql_free_result($result);
        $nr_notes = count($notes);

        // Visitor can view annotations in the first place?
        if (USER_ID && annotate_get_level('permissions') == 0) {
            // Stop processing the annotations any further
            return $data;
        } elseif (!USER_ID && annotate_get_level('permissions') == 0) {
            $result = cpg_db_query("SELECT MAX(value) FROM {$CONFIG['TABLE_CONFIG']} WHERE name LIKE 'plugin_annotate_permissions_%'");
            $max_permission_level = mysql_result($result, 0);
            mysql_free_result($result);
            if ($max_permission_level >= 1 && $nr_notes > 0 && $CONFIG['allow_user_registration'] != 0) {
                // There are annotations, so let's promote them
                if ($nr_notes == 1) {
                    $data['footer'] .= $lang_plugin_annotate['1_annotation_for_file'] . '<br />' . $LINEBREAK;
                } elseif ($nr_notes > 1) {
                    $data['footer'] .= sprintf($lang_plugin_annotate['x_annotations_for_file'], $nr_notes) . '<br />' . $LINEBREAK;
                }
                $data['footer'] .= sprintf(
                                            $lang_plugin_annotate['registration_promotion'],
                                            '<a href="login.php?referer='.$REFERER.'">',
                                            '</a>',
                                            '<a href="register.php?referer='.$REFERER.'">',
                                            '</a>'
                                            );
            }
            // Stop processing the annotations any further
            return $data;
        } 

        $jsarray = arrayToJS4($notes, 'annotations');

        $html =& $data['html'];

        $html = str_replace("<img ", "<img style=\"padding:0px\" ", $html);

        if (function_exists(panorama_viewer_image)) {
            $search = "/(<table.*style=\"table-layout:fixed.*<div style=\"overflow:auto.*>)(.*)(<\/div><\/td><\/tr><\/table>)/Uis";
            preg_match($search, $html, $panorama_viewer_matches);
            $html = preg_replace($search, "\\2", $html);
        }

        $container_width = $data['pwidth'];
        if ($data['mode'] == 'normal') {
            $imagesize = getimagesize($CONFIG['fullpath'].$data['filepath'].$CONFIG['normal_pfx'].$data['filename']);
            $container_width = $imagesize[0];
        }
        $container_width += 4;

        $html = '<div class="Photo fn-container" style="width:'.$container_width.'px;" id="PhotoContainer">' . $html . '</div>';

        if (function_exists(panorama_viewer_image)) {
            $html = $panorama_viewer_matches[1].$html.$panorama_viewer_matches[3];
        }

        // list annotations from the currently viewed picture and generate link to meta album
        if (annotate_get_level('display_links') == 1 && $nr_notes > 0) {
            $on_this_pic_array = array();
            $n = 0;
            foreach($notes as $value) {
                $note = stripslashes($value['note']);
                $on_this_pic_array[] = "<button onclick=\"window.location.href='thumbnails.php?album=shownotes&amp;note=".addslashes(str_replace(Array("#", "&"), Array("%23", "%26"), $note))."';\" class=\"admin_menu\" title=\"".sprintf($lang_plugin_annotate['all_pics_of'], $note)."\" onmouseover=\"notes.notes[$n].ShowNote(); notes.notes[$n].ShowNoteText();\" onmouseout=\"notes.notes[$n].HideNote(); notes.notes[$n].HideNoteText();\">$note</button> ";
                $n++;
            }
            sort($on_this_pic_array);
            $on_this_pic_div = "<div id=\"on_this_pic\" style=\"white-space:normal; cursor:default; padding-bottom:4px;\"> {$lang_plugin_annotate['on_this_pic']}: ";
            foreach($on_this_pic_array as $value) {
                $on_this_pic_div .= $value;
            }
            $on_this_pic_div .= "</div>";
            $html = $on_this_pic_div.$html;
        }

        // Display annotation statistics of the currently viewed album
        if (annotate_get_level('display_stats') == 1) {
            $superCage = Inspekt::MakeSuperCage();
            if ($superCage->get->testInt('album')) {
                $annotations_pic = $nr_notes;
                $annotated_pics = mysql_num_rows(cpg_db_query("SELECT DISTINCT n.pid FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate n INNER JOIN {$CONFIG['TABLE_PICTURES']} p ON p.pid = n.pid WHERE p.aid = ".$superCage->get->getInt('album')));
                $annotations_album = mysql_num_rows(cpg_db_query("SELECT DISTINCT n.nid FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate n INNER JOIN {$CONFIG['TABLE_PICTURES']} p ON p.pid = n.pid WHERE p.aid = ".$superCage->get->getInt('album')));
                $annotation_stats = "
                    <span title=\"{$lang_plugin_annotate['annotations_pic']}\">($annotations_pic)</span>
                    <span title=\"{$lang_plugin_annotate['annotations_album']}\">($annotations_album)</span>
                    <span title=\"{$lang_plugin_annotate['annotated_pics']}\">($annotated_pics)</span>
                ";
                $menu_buttons .= '<li>&nbsp;'.$annotation_stats.'</li>';
            }
        }

        $permission_level = annotate_get_level('permissions');
        $user_id = USER_ID;
        
        $html .= <<< EOT
        
<script type="text/javascript">

var $jsarray

/* create the Photo Note Container */
var container = document.getElementById('PhotoContainer');

var notes = new PhotoNoteContainer(container);

for (var n = 0; n < annotations.length; n++) {
    /* create a note */
    var size = new PhotoNoteRect(annotations[n].posx, annotations[n].posy, annotations[n].width, annotations[n].height);
    var note = new PhotoNote(annotations[n].note, 'note' + n, size, annotations[n].user_name, annotations[n].user_id);
    /* implement the save/delete functions */
    note.onsave = function (note) { return ajax_save(note); };
    note.ondelete = function (note) { return ajax_delete(note); };
    /* assign the note id number */
    note.nid = annotations[n].nid;
    if ($permission_level < 3 && annotations[n].user_id != $user_id) note.editable = false;
    /* add it to the container */
    notes.AddNote(note);
}

notes.HideAllNotes();

addEvent(container, 'mouseover', function() {
         notes.ShowAllNotes();
    });
    
 addEvent(container, 'mouseout', function() {
         notes.HideAllNotes();
    });

function addnote(note_text){
    if (js_vars.visitor_annotate_permission_level < 2) {
        return false;
    }
    var newNote = new PhotoNote(note_text, 'note' + n, new PhotoNoteRect(10,10,50,50), '', '');
    newNote.onsave = function (note) { return ajax_save(note); };
    newNote.ondelete = function (note) { return ajax_delete(note); };
    notes.AddNote(newNote);
    newNote.Select();
    newNote.nid = 0;
    return false;
}

function ajax_save(note){
    var data = 'add=' + {$data['pid']} + '&nid=' + note.nid + '&posx=' + note.rect.left + '&posy=' + note.rect.top + '&width=' + note.rect.width + '&height=' + note.rect.height + '&note=' + encodeURI(note.text);
    annotate_request(data, note);
    return true;
}

function ajax_delete(note){
    var data = 'remove=' + note.nid;
    annotate_request(data, note);
    return true;
}
</script>

    
EOT;

    }

    if (empty($data['menu'])) {
        $data['menu'] = '<div class="buttonlist align_right"><ul>'.$menu_buttons.'</ul></div>';
    } else {
        $data['menu'] = str_replace('</ul>', $menu_buttons.'</ul>', $data['menu']);
    }

    return $data;
}

// Based on code by Rob Williams
//Convert a PHP array to a JavaScript one (rev. 4)
function arrayToJS4($array, $baseName) {
    global $LINEBREAK;
    $return = '';
   //Write out the initial array definition
   $return .= $baseName . ' = new Array();'.$LINEBREAK;    
   //Reset the array loop pointer
   reset ($array);
   //Use list() and each() to loop over each key/value
   //pair of the array
   while (list($key, $value) = each($array)) {
      if (is_numeric($key)) {
         //A numeric key, so output as usual
         $outKey = "[" . $key . "]";
      } else {
         //A string key, so output as a string
         $outKey = "['" . $key . "']";
      }
      if (is_array($value)) {
         //The value is another array, so simply call
         //another instance of this function to handle it
         $return .= arrayToJS4($value, $baseName . $outKey);
      } else {
         //Output the key declaration
         $return .= ($baseName . $outKey . " = ");      
         //Now output the value
         if (is_numeric($value)){
            $return .= $value . ';'.$LINEBREAK;
         } else if (is_string($value)) {
            //Output as a string, as we did before       
            $return .= "'" . $value . "';".$LINEBREAK;
         } else if ($value === false) {
            //Explicitly output false
            $return .= 'false;'.$LINEBREAK;
         } else if ($value === NULL) {
            //Explicitly output null
            $return .= 'null;'.$LINEBREAK;
         } else if ($value === true) {
            //Explicitly output true
            $return .= 'true;'.$LINEBREAK;
         } else {
            //Output the value directly otherwise
            $return .= $value . ';'.$LINEBREAK;
         }
      }
   }
   return $return;
}


function annotate_install() {
    global $thisplugin, $CONFIG;
    // Create the super cage
    $superCage = Inspekt::makeSuperCage();
    $annotate_installation = 1;
    require 'include/sql_parse.php';
    // Perform the database changes
    $db_schema = $thisplugin->fullpath . '/schema.sql';
    $sql_query = fread(fopen($db_schema, 'r'), filesize($db_schema));
    $sql_query = preg_replace('/CPG_/', $CONFIG['TABLE_PREFIX'], $sql_query);
    $sql_query = remove_remarks($sql_query);
    $sql_query = split_sql_file($sql_query, ';');
    foreach($sql_query as $q) {
        cpg_db_query($q);
    }
    $db_schema = $thisplugin->fullpath . '/update.sql';
    $sql_query = fread(fopen($db_schema, 'r'), filesize($db_schema));
    $sql_query = preg_replace('/CPG_/', $CONFIG['TABLE_PREFIX'], $sql_query);
    $sql_query = remove_remarks($sql_query);
    $sql_query = split_sql_file($sql_query, ';');
    foreach($sql_query as $q) {
        @mysql_query($q);
    }

    if ($superCage->post->keyExists('submit')) {
        annotate_configuration_submit();
        return true;
    } else {
        return 1;
    }
}


function annotate_uninstall() {
    $superCage = Inspekt::makeSuperCage();

    if (!$superCage->post->keyExists('drop')) {
        return 1;
    }

    if (!checkFormToken()) {
        global $lang_errors;
        cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
    }

    if ($superCage->post->getInt('drop') == 1) {
        global $CONFIG;
        // Delete the plugin config records
        cpg_db_query("DELETE FROM {$CONFIG['TABLE_CONFIG']} WHERE name LIKE 'plugin_annotate_%'");
        // Drop the extra plugin table
        cpg_db_query("DROP TABLE IF EXISTS {$CONFIG['TABLE_PREFIX']}plugin_annotate");
    }
    return true;
}


function annotate_cleanup($action) {
    global $CONFIG, $lang_common, $lang_plugin_annotate;
    require_once './plugins/annotate/init.inc.php';
    $annotate_init_array = annotate_initialize();
    $lang_plugin_annotate = $annotate_init_array['language'];
    $superCage = Inspekt::makeSuperCage();
    $cleanup = $superCage->server->getEscaped('REQUEST_URI');
    if ($action == 1) {
        list($timestamp, $form_token) = getFormToken();
        echo <<< EOT
            <form action="{$cleanup}" method="post">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="tableb">
                            {$lang_plugin_annotate['prune_database']}
                        </td>
                        <td class="tableb">
                            <input type="radio" name="drop" id="drop_yes" value="1" checked="checked" />
                            <label for="drop_yes" class="clickable_option">{$lang_common['yes']}</label>
                        </td>
                        <td class="tableb">
                            <input type="radio" name="drop" id="drop_no"  value="0" />
                            <label for="drop_no" class="clickable_option">{$lang_common['no']}</label>
                        </td>
                        <td class="tableb">
                            <input type="hidden" name="form_token" value="{$form_token}" />
                            <input type="hidden" name="timestamp" value="{$timestamp}" />
                            <input type="submit" name="submit" value="{$lang_common['go']}" class="button" />
                        </td>
                    </tr>
                </table>
            </form>
EOT;
    }
}





//// New meta albums

// Meta album titles, custom pages
function annotate_page_start() {
    global $CONFIG, $lang_meta_album_names, $valid_meta_albums;

    require_once './plugins/annotate/init.inc.php';
    $annotate_init_array = annotate_initialize();
    $lang_plugin_annotate = $annotate_init_array['language'];
    $annotate_icon_array = $annotate_init_array['icon'];
    $superCage = Inspekt::MakeSuperCage();
    $note = $superCage->get->keyExists('note') ? $superCage->get->getRaw('note') : $superCage->cookie->getRaw($CONFIG['cookie_name'].'note');

    $lang_meta_album_names['lastnotes'] = $lang_plugin_annotate['lastnotes'];
    $lang_meta_album_names['shownotes'] = $lang_plugin_annotate['shownotes']." '$note'";

    $valid_meta_albums[] = 'lastnotes';
    $valid_meta_albums[] = 'shownotes';

    $superCage = Inspekt::makeSuperCage();
    if ($superCage->get->getAlpha('plugin') == "annotate" && $superCage->get->keyExists('delete_orphans')) {
        global $CONFIG;
        require_once './plugins/annotate/init.inc.php';
        $annotate_init_array = annotate_initialize();
        $lang_plugin_annotate = $annotate_init_array['language'];
        $annotate_icon_array = $annotate_init_array['icon'];
        load_template();
        pageheader($lang_plugin_annotate['delete_orphaned_entries']);

        if (version_compare(cpg_phpinfo_mysql_version(), '4.1', '>=')) {
            // we can use subqueries here
            cpg_db_query("DELETE FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate WHERE pid NOT IN (SELECT pid FROM {$CONFIG['TABLE_PICTURES']})");
        } else {
            $result = cpg_db_query("SELECT pid FROM {$CONFIG['TABLE_PICTURES']}");
            $pids = array();
            while ($row = mysql_fetch_row($result)) {
                $pids[] = $row[0];
            }
            $pids = implode(",", $pids);
            mysql_free_result($result);

            // cpg_db_query can cause browser to crash if debug output is enabled
            mysql_query("DELETE FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate WHERE pid NOT IN ($pids)");
        }
        $count = mysql_affected_rows();

        if ($count == 1) {
            $count_output = $lang_plugin_annotate['1_orphaned_entry_deleted'];
        } else {
            $count_output = sprintf($lang_plugin_annotate['x_orphaned_entries_deleted'], $count);
        }
        starttable('-1', $annotate_icon_array['delete'] . $lang_plugin_annotate['delete_orphaned_entries']);
        echo <<< EOT
        <tr>
            <td class="tableb">
                {$count_output}
            </td>
        </tr>
EOT;
        endtable();
        pagefooter();
        exit;
    }

    if ($superCage->get->getAlpha('plugin') == "annotate" && $superCage->get->keyExists('import')) {
        global $CONFIG;
        require_once './plugins/annotate/init.inc.php';
        $annotate_init_array = annotate_initialize();
        $lang_plugin_annotate = $annotate_init_array['language'];
        $annotate_icon_array = $annotate_init_array['icon'];
        load_template();
        pageheader($lang_plugin_annotate['import']);

        starttable('-1', $annotate_icon_array['import'] . $lang_plugin_annotate['import']);

        if ($superCage->get->keyExists('do') && $CONFIG['plugin_annotate_import'] != "1") {
            if (!mysql_query("SELECT user_time FROM {$CONFIG['TABLE_PREFIX']}notes")) {
                cpg_db_query("INSERT INTO {$CONFIG['TABLE_PREFIX']}plugin_annotate (pid, posx, posy, width, height, note, user_id, user_time) 
                              SELECT pid, posx, posy, width, height, note, user_id, UNIX_TIMESTAMP() FROM {$CONFIG['TABLE_PREFIX']}notes");
            } else {
                cpg_db_query("INSERT INTO {$CONFIG['TABLE_PREFIX']}plugin_annotate (pid, posx, posy, width, height, note, user_id, user_time) 
                              SELECT pid, posx, posy, width, height, note, user_id, user_time FROM {$CONFIG['TABLE_PREFIX']}notes");
            }
            echo '<tr><td class="tableb">'.sprintf($lang_plugin_annotate['import_success'], mysql_affected_rows()).'</td></tr>';
            cpg_db_query("INSERT INTO {$CONFIG['TABLE_CONFIG']} (name, value) VALUES ('plugin_annotate_import', '1')");
        } else {
            $notes_to_import = mysql_result(cpg_db_query("SELECT COUNT(*) FROM {$CONFIG['TABLE_PREFIX']}notes"), 0);
            if (!$notes_to_import) {
                echo '<tr><td class="tableb">'.sprintf($lang_plugin_annotate['import_found'], $notes_to_import).'</td></tr>';
            } elseif ($CONFIG['plugin_annotate_import'] == "1") {
                echo '<tr><td class="tableb">'.$lang_plugin_annotate['imported_already'].'</td></tr>';
            } else {
                echo '<tr><td class="tableb">'.sprintf($lang_plugin_annotate['import_found'], $notes_to_import).' <a href="index.php?plugin=annotate&import&do" class="admin_menu">'.$lang_plugin_annotate['import'].'</a></td></tr>';
            }
        }
        endtable();
        pagefooter();
        exit;
    }

    if ($superCage->get->getAlpha('plugin') == "annotate" && $superCage->get->keyExists('update_database')) {
        global $CONFIG;
        require_once './plugins/annotate/init.inc.php';
        $annotate_init_array = annotate_initialize();
        $lang_plugin_annotate = $annotate_init_array['language'];
        $annotate_icon_array = $annotate_init_array['icon'];
        load_template();
        pageheader($lang_plugin_annotate['update_database']);

        require 'include/sql_parse.php';
        $db_schema = './plugins/annotate/update.sql';
        $sql_query = fread(fopen($db_schema, 'r'), filesize($db_schema));
        $sql_query = preg_replace('/CPG_/', $CONFIG['TABLE_PREFIX'], $sql_query);
        $sql_query = remove_remarks($sql_query);
        $sql_query = split_sql_file($sql_query, ';');
        foreach($sql_query as $q) {
            @mysql_query($q);
        }
        starttable('-1', $annotate_icon_array['update_database'] . $lang_plugin_annotate['update_database']);
        echo <<< EOT
        <tr>
            <td class="tableb">
                {$lang_plugin_annotate['update_database_success']}
            </td>
        </tr>
EOT;
        endtable();
        pagefooter();
        exit;
    }

    if ($superCage->get->getAlpha('plugin') == "annotate" && $superCage->get->keyExists('manage')) {
        if (!GALLERY_ADMIN_MODE) {
            return;
        }

        global $CONFIG;
        require_once './plugins/annotate/init.inc.php';
        $annotate_init_array = annotate_initialize();
        $lang_plugin_annotate = $annotate_init_array['language'];
        $annotate_icon_array = $annotate_init_array['icon'];
        load_template();

        if ($superCage->post->keyExists('submit')) {
            if (!checkFormToken()) {
                global $lang_errors;
                cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
            }
            if ($superCage->get->keyExists('batch_rename')) {
                if (strlen($superCage->post->getRaw('note_new')) < 1) {
                    header("Location: index.php?plugin=annotate&manage&batch_rename&status=0&note_old=".$superCage->post->getRaw('note_old')."&note_new=".$superCage->post->getRaw('note_new'));
                } else {
                    cpg_db_query("UPDATE {$CONFIG['TABLE_PREFIX']}plugin_annotate SET note = '".addslashes(addslashes($superCage->post->getRaw('note_new')))."' WHERE note = '".addslashes(addslashes($superCage->post->getRaw('note_old')))."'");
                    header("Location: index.php?plugin=annotate&manage&batch_rename&status=1&note_old=".$superCage->post->getRaw('note_old')."&note_new=".$superCage->post->getRaw('note_new'));
                }
            }
            if ($superCage->get->keyExists('batch_delete')) {
                cpg_db_query("DELETE FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate WHERE note = '".addslashes(addslashes($superCage->post->getRaw('note_old')))."'");
                header("Location: index.php?plugin=annotate&manage&batch_delete&status=1&note_old=".$superCage->post->getRaw('note_old'));
            }
        }

        pageheader($lang_plugin_annotate['manage']);
        if ($superCage->get->keyExists('batch_rename')) {
            starttable("100%", $lang_plugin_annotate['batch_rename']);
        } elseif ($superCage->get->keyExists('batch_delete')) {
            starttable("100%", $lang_plugin_annotate['batch_delete']);
        } else {
            starttable("100%", $lang_plugin_annotate['manage']);
        }

        if ($superCage->post->keyExists('sure')) {
            if ($superCage->get->keyExists('batch_rename')) {
                global $lang_common;
                $note_new = $superCage->post->getRaw('note_new');
                if (strlen($note_new) < 1) {
                    echo '<tr><td class="tableb">'.$lang_plugin_annotate['note_empty'].' <a href="javascript:history.back();">'.$lang_common['back'].'</a></td></tr>';
                    endtable();
                    pagefooter();
                    die();
                }
                list($timestamp, $form_token) = getFormToken();
                echo '
                    <tr><td class="tableb">
                    <form method="post" action="index.php?plugin=annotate&manage&batch_rename">
                    '.sprintf($lang_plugin_annotate['sure_rename'], $superCage->post->getRaw('note_old'), $note_new).'
                    <input type="hidden" name="note_old" class="textinput" value="'.$superCage->post->getRaw('note_old').'" readonly="readonly">
                    <input type="hidden" name="note_new" class="textinput" value="'.$note_new.'" readonly="readonly">
                    <input type="hidden" name="form_token" value="'.$form_token.'" />
                    <input type="hidden" name="timestamp" value="'.$timestamp.'" />
                    <input type="submit" name="submit" class="button" value="'.$lang_common['go'].'">
                    <a href="javascript:history.back();">'.$lang_common['back'].'</a>
                    </form>
                    </td></tr>
                ';
            }
            if ($superCage->get->keyExists('batch_delete')) {
                global $lang_common;
                list($timestamp, $form_token) = getFormToken();
                echo '
                    <tr><td class="tableb">
                    <form method="post" action="index.php?plugin=annotate&manage&batch_delete">
                    '.sprintf($lang_plugin_annotate['sure_delete'], $superCage->post->getRaw('note_old')).'
                    <input type="hidden" name="note_old" class="textinput" value="'.$superCage->post->getRaw('note_old').'" readonly="readonly">
                    <input type="hidden" name="form_token" value="'.$form_token.'" />
                    <input type="hidden" name="timestamp" value="'.$timestamp.'" />
                    <input type="submit" name="submit" class="button" value="'.$lang_common['go'].'">
                    <a href="javascript:history.back();">'.$lang_common['back'].'</a>
                    </form>
                    </td></tr>
                ';
            }
        }

        if (!$superCage->post->keyExists('note_old')) {
            if ($superCage->get->keyExists('status')) {
                if ($superCage->get->keyExists('batch_rename')) {
                    if ($superCage->get->getInt('status') == 1) {
                        echo '<tr><td class="tableb">'.sprintf($lang_plugin_annotate['rename_success'], $superCage->get->getRaw('note_old'), $superCage->get->getRaw('note_new')).' </td></tr>';
                    }
                    if ($superCage->get->getInt('status') == 0) {
                        echo '<tr><td class="tableb">"'.sprintf($lang_plugin_annotate['rename_fail'], $superCage->get->getRaw('note_old'), $superCage->get->getRaw('note_new')).'. '.$lang_plugin_annotate['note_empty'].'</td></tr>'; 
                    }
                }
                if ($superCage->get->keyExists('batch_delete') && $superCage->get->getInt('status') == 1) {
                    echo '<tr><td class="tableb">'.sprintf($lang_plugin_annotate['delete_success'], $superCage->get->getRaw('note_old'), $superCage->get->getRaw('note_new')).' </td></tr>';
                }
            }
            if ($superCage->get->keyExists('note')) {
                if ($superCage->get->keyExists('batch_rename')) {
                    global $lang_common;
                    echo '
                        <tr><td class="tableb">
                        <form method="post">
                        <input type="text" name="note_old" size="40" class="textinput" value="'.$superCage->get->getRaw('note').'" readonly="readonly"> '.$lang_plugin_annotate['rename_to'].'
                        <input type="text" name="note_new" size="40" class="textinput" id="note_new">
                        <input type="submit" name="sure" class="button" value="'.$lang_common['go'].'">
                        </form> <script type="text/javascript"> document.getElementById("note_new").select(); </script>
                        </td></tr>
                    ';
                }
                if ($superCage->get->keyExists('batch_delete')) {
                    global $lang_common;
                    echo '
                        <tr><td class="tableb">
                        <form method="post">
                        '.$lang_common['delete'].'
                        <input type="text" name="note_old" class="textinput" value="'.$superCage->get->getRaw('note').'" readonly="readonly">
                        <input type="submit" name="sure" class="button" value="'.$lang_common['go'].'">
                        </form>
                        </td></tr>
                    ';
                }
            }

            $result = cpg_db_query("SELECT DISTINCT(note) FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate ORDER BY note");
            if (mysql_num_rows($result)) {
                $person_array = Array();
                while ($row = mysql_fetch_assoc($result)) {
                    $person_array[] = stripslashes($row['note']);
                }

                echo '<tr><td class="tableb" align="left">';
                for ($i = 0; $i < count($person_array); $i++) {
                    $note = str_replace(Array("#", "&"), Array("%23", "%26"), $person_array[$i]);
                    echo "
                        <a href=\"index.php?plugin=annotate&amp;manage&amp;batch_delete&amp;note={$note}\" title=\"{$lang_plugin_annotate['batch_delete']}\"><img src=\"images/icons/delete.png\" border=\"0\" /></a>
                        <a href=\"index.php?plugin=annotate&amp;manage&amp;batch_rename&amp;note={$note}\" title=\"{$lang_plugin_annotate['batch_rename']}\"><img src=\"images/icons/edit.png\" border=\"0\" /></a>
                        {$person_array[$i]}<br />
                    ";
                }
                echo '</td></tr>';
            }
            mysql_free_result($result);
        }

        endtable();
        pagefooter();
        exit;
    }
}


// Meta album get_pic_pos
function annotate_get_pic_pos($album) {
    global $CONFIG, $pid, $RESTRICTEDWHERE;

    switch ($album) {
        case 'lastnotes':
            $query = "SELECT MAX(nid) FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate WHERE pid = $pid";
            $result = cpg_db_query($query);
            $nid = mysql_result($result, 0);
            mysql_free_result($result);            

            $query = "SELECT COUNT(DISTINCT n.pid) 
                FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate AS n 
                INNER JOIN {$CONFIG['TABLE_PICTURES']} AS p ON n.pid = p.pid 
                INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r on r.aid = p.aid 
                $RESTRICTEDWHERE
                AND approved = 'YES'
                AND n.nid > $nid";

                $result = cpg_db_query($query);

                list($pos) = mysql_fetch_row($result);
                mysql_free_result($result);

            return strval($pos);
            break;

        case 'shownotes':
            $superCage = Inspekt::makeSuperCage();
            $note = $superCage->get->keyExists('note') ? $superCage->get->getRaw('note') : $superCage->cookie->getRaw($CONFIG['cookie_name'].'note');
            setcookie($CONFIG['cookie_name'].'note', $note);

            $note = addslashes(addslashes($note));

            $query = "SELECT DISTINCT p.pid 
                FROM {$CONFIG['TABLE_PICTURES']} AS p INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r ON p.aid = r.aid 
                INNER JOIN {$CONFIG['TABLE_PREFIX']}plugin_annotate n ON p.pid = n.pid 
                $RESTRICTEDWHERE 
                AND approved = 'YES' 
                AND n.note = '$note' 
                GROUP BY p.pid 
                ORDER BY p.pid DESC";

                $result = cpg_db_query($query);
                $pos = 0;
                while($row = mysql_fetch_assoc($result)) {
                    if ($row['pid'] == $pid) {
                        break;
                    }
                    $pos++;
                }
                mysql_free_result($result);

            return strval($pos);
            break;

        default: 
            return $album;
    }
}


// New meta albums
function annotate_meta_album($meta) {
    global $CONFIG, $CURRENT_CAT_NAME, $RESTRICTEDWHERE, $lang_plugin_annotate;
    require_once './plugins/annotate/init.inc.php';
    $annotate_init_array = annotate_initialize();
    $lang_plugin_annotate = $annotate_init_array['language'];
    $annotate_icon_array = $annotate_init_array['icon'];
    
    switch ($meta['album']) {
        case 'lastnotes':
            $album_name = $annotate_icon_array['annotate'] . ' ' . $lang_plugin_annotate['lastnotes'];
            if ($CURRENT_CAT_NAME) {
                $album_name .= " - $CURRENT_CAT_NAME";
            }

            $query = "SELECT DISTINCT n.pid 
                FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate AS n 
                INNER JOIN {$CONFIG['TABLE_PICTURES']} AS p ON n.pid = p.pid 
                INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r ON r.aid = p.aid 
                $RESTRICTEDWHERE";

            $result = cpg_db_query($query);
            $count = mysql_num_rows($result);
            mysql_free_result($result);

            $query = "SELECT *, user_time AS msg_date
                FROM {$CONFIG['TABLE_PICTURES']} AS p
                INNER JOIN {$CONFIG['TABLE_PREFIX']}plugin_annotate AS n1 ON p.pid = n1.pid 
                INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r ON r.aid = p.aid 
                $RESTRICTEDWHERE 
                AND approved = 'YES'
                AND n1.nid IN (SELECT MAX(n2.nid) FROM {$CONFIG['TABLE_PREFIX']}plugin_annotate AS n2 WHERE n1.pid = n2.pid)
                ORDER BY n1.nid DESC {$meta['limit']}";

            $result = cpg_db_query($query);
            $rowset = cpg_db_fetch_rowset($result);
            mysql_free_result($result);

            build_caption($rowset, array('msg_date'));
            break;

        case 'shownotes':
            if (annotate_get_level('permissions') < 1) {
                global $lang_errors;
                cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
            }

            $superCage = Inspekt::makeSuperCage();
            $note = $superCage->get->keyExists('note') ? $superCage->get->getRaw('note') : $superCage->cookie->getRaw($CONFIG['cookie_name'].'note');
            setcookie($CONFIG['cookie_name'].'note', $note);

            $album_name = cpg_fetch_icon('search', 2) . ' ' . $lang_plugin_annotate['shownotes'] . " '$note'";
            if ($CURRENT_CAT_NAME) {
                $album_name .= " - $CURRENT_CAT_NAME";
            }

            $note = addslashes(addslashes($note));

            $query = "SELECT p.pid FROM {$CONFIG['TABLE_PICTURES']} AS p INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r ON p.aid = r.aid INNER JOIN {$CONFIG['TABLE_PREFIX']}plugin_annotate n ON p.pid = n.pid $RESTRICTEDWHERE AND approved = 'YES' AND n.note = '$note' GROUP BY p.pid";
            $result = cpg_db_query($query);
            $count = mysql_num_rows($result);
            mysql_free_result($result);

            $query = "SELECT p.*, r.title FROM {$CONFIG['TABLE_PICTURES']} AS p INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS r ON p.aid = r.aid INNER JOIN {$CONFIG['TABLE_PREFIX']}plugin_annotate n ON p.pid = n.pid $RESTRICTEDWHERE AND approved = 'YES' AND n.note = '$note' GROUP BY p.pid ORDER BY p.pid DESC {$meta['limit']}";
            $result = cpg_db_query($query);
            $rowset = cpg_db_fetch_rowset($result);
            mysql_free_result($result);

            build_caption($rowset);
            break;

        default:
            return $meta;
    }
    
    $meta['album_name'] = $album_name;
    $meta['count'] = $count;
    $meta['rowset'] = $rowset;

    return $meta;
}


function annotate_configuration_save_value($name, $upper_limit) {
    if (!GALLERY_ADMIN_MODE) {
        global $lang_errors;
        cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
    }
    global $CONFIG;
    $superCage = Inspekt::makeSuperCage();
    $new_value = $superCage->post->getInt($name);
    
    if ($new_value >= 0 && $new_value <= $upper_limit) {
        if (!isset($CONFIG[$name])) {
            cpg_db_query("INSERT INTO {$CONFIG['TABLE_CONFIG']} (name, value) VALUES('$name', '$new_value')");
            $CONFIG[$name] = $new_value;
            return 1;
        } elseif ($new_value != $CONFIG[$name]) {
            cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$new_value' WHERE name = '$name'");
            $CONFIG[$name] = $new_value;
            return 1;
        }
    }

    return 0;
}


function annotate_configuration_submit() {
    if (!GALLERY_ADMIN_MODE) {
        global $lang_errors;
        cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
    }

    // Populate the form fields and run the queries for the submit form
    global $CONFIG;
    $config_changes_counter = 0;

    $config_changes_counter += annotate_configuration_save_value('plugin_annotate_type', 3);

    $result = cpg_db_query("SELECT group_id FROM {$CONFIG['TABLE_USERGROUPS']} WHERE has_admin_access != '1'");
    while($row = mysql_fetch_assoc($result)) {
        $config_changes_counter += annotate_configuration_save_value('plugin_annotate_permissions_'.$row['group_id'], 3);
    }
    mysql_free_result($result);

    $result = cpg_db_query("SELECT group_id FROM {$CONFIG['TABLE_USERGROUPS']}");
    while($row = mysql_fetch_assoc($result)) {
        $config_changes_counter += annotate_configuration_save_value('plugin_annotate_display_notes_'.$row['group_id'], 1);
        $config_changes_counter += annotate_configuration_save_value('plugin_annotate_display_links_'.$row['group_id'], 1);
        $config_changes_counter += annotate_configuration_save_value('plugin_annotate_display_stats_'.$row['group_id'], 1);
    }
    mysql_free_result($result);

    return $config_changes_counter;
}

function annotate_configure() {
    global $CONFIG, $cpg_udb, $THEME_DIR, $thisplugin, $lang_plugin_annotate, $lang_common, $annotate_icon_array, $lang_errors, $annotate_installation, $annotate_title, $LINEBREAK;
    $superCage = Inspekt::makeSuperCage();
    $additional_submit_information = '';
    if (!GALLERY_ADMIN_MODE) {
        cpg_die(ERROR, $lang_errors['access_denied'], __FILE__, __LINE__);
    }

    // Form submit?
    if ($superCage->post->keyExists('submit') == TRUE) {
        //Check if the form token is valid
        if(!checkFormToken()){
            cpg_die(ERROR, $lang_errors['invalid_form_token'], __FILE__, __LINE__);
        }
        $config_changes_counter = annotate_configuration_submit();
        if ($config_changes_counter > 0) {
            $additional_submit_information .= '<div class="cpg_message_success">' . $lang_plugin_annotate['changes_saved'] . '</div>';
        } else {
            $additional_submit_information .= '<div class="cpg_message_validation">' . $lang_plugin_annotate['no_changes'] . '</div>';
        }
    }

    // Check if guests have greater permissions than registered users
    if ($CONFIG['plugin_annotate_permissions_'.$cpg_udb->guestgroup] > mysql_result(cpg_db_query("SELECT MIN(value) FROM {$CONFIG['TABLE_CONFIG']} WHERE name LIKE 'plugin_annotate_permissions_%'"), 0)) {
        $additional_submit_information .= '<div class="cpg_message_warning">' . $lang_plugin_annotate['guests_more_permissions_than_registered'] . '</div>';
    }

    // Create the table row that is displayed during initial install
    if ($annotate_installation == 1) {
        $additional_submit_information .= '<div class="cpg_message_info">' . $lang_plugin_annotate['submit_to_install'] . '</div>';
    }

    if ($CONFIG['plugin_annotate_type'] == '0') {
        $option_output['plugin_annotate_type_0'] = 'checked="checked"';
        $option_output['plugin_annotate_type_1'] = '';
        $option_output['plugin_annotate_type_2'] = '';
        $option_output['plugin_annotate_type_3'] = '';
    } elseif ($CONFIG['plugin_annotate_type'] == '1') {
        $option_output['plugin_annotate_type_0'] = '';
        $option_output['plugin_annotate_type_1'] = 'checked="checked"';
        $option_output['plugin_annotate_type_2'] = '';
        $option_output['plugin_annotate_type_3'] = '';
    } elseif ($CONFIG['plugin_annotate_type'] == '2') {
        $option_output['plugin_annotate_type_0'] = '';
        $option_output['plugin_annotate_type_1'] = '';
        $option_output['plugin_annotate_type_2'] = 'checked="checked"';
        $option_output['plugin_annotate_type_3'] = '';
    } elseif ($CONFIG['plugin_annotate_type'] == '3') {
        $option_output['plugin_annotate_type_0'] = '';
        $option_output['plugin_annotate_type_1'] = '';
        $option_output['plugin_annotate_type_2'] = '';
        $option_output['plugin_annotate_type_3'] = 'checked="checked"';
    }
    
    list($timestamp, $form_token) = getFormToken();

    // Start the actual output
    echo <<< EOT
            <form action="" method="post" name="annotate_config" id="annotate_config">
EOT;

    starttable('100%', $annotate_icon_array['configure'] . $lang_plugin_annotate['configure_plugin'], 8);
    $display_stats_title = sprintf($lang_plugin_annotate['display_stats_title'], $lang_plugin_annotate['annotations_pic'], $lang_plugin_annotate['annotations_album'], $lang_plugin_annotate['annotated_pics']);
    echo <<< EOT
                    <tr>
                        <td valign="top" class="tableb">
                            {$lang_plugin_annotate['annotation_type']}
                        </td>
                        <td valign="top" class="tableb" colspan="7">
                            <input type="radio" name="plugin_annotate_type" id="plugin_annotate_type_0" class="radio" value="0" {$option_output['plugin_annotate_type_0']} />
                            <label for="plugin_annotate_type_0" class="clickable_option">{$lang_plugin_annotate['drop_down_registered_users']}</label>
                            <br />
                            <input type="radio" name="plugin_annotate_type" id="plugin_annotate_type_1" class="radio" value="1" {$option_output['plugin_annotate_type_1']} />
                            <label for="plugin_annotate_type_1" class="clickable_option">{$lang_plugin_annotate['free_text']}</label>
                            <br />
                            <input type="radio" name="plugin_annotate_type" id="plugin_annotate_type_2" class="radio" value="2" {$option_output['plugin_annotate_type_2']} />
                            <label for="plugin_annotate_type_2" class="clickable_option">{$lang_plugin_annotate['drop_down_existing_annotations']}</label>
                            <br />
                            <input type="radio" name="plugin_annotate_type" id="plugin_annotate_type_3" class="radio" value="3" {$option_output['plugin_annotate_type_3']} />
                            <label for="plugin_annotate_type_3" class="clickable_option">{$lang_plugin_annotate['free_text']} + {$lang_plugin_annotate['drop_down_existing_annotations']}</label>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" class="tableh2" rowspan="2">
                            {$lang_plugin_annotate['group']}
                        </td>
                        <td valign="middle" align="center" class="tableh2" colspan="4">
                            {$lang_plugin_annotate['permissions']}
                        </td>
                        <td valign="middle" align="center" class="tableh2" colspan="1" rowspan="2"><span title="{$lang_plugin_annotate['display_notes_title']}" style="cursor:help;">{$lang_plugin_annotate['display_notes']}</span>
                        </td>
                        <td valign="middle" align="center" class="tableh2" colspan="1" rowspan="2">{$lang_plugin_annotate['display_links']}
                        </td>
                        <td valign="middle" align="center" class="tableh2" colspan="1" rowspan="2"><span title="{$display_stats_title}" style="cursor:help;">{$lang_plugin_annotate['display_stats']}</span>
                        </td>
                    </tr>
                    <tr>
                        <td valign="middle" align="center" class="tableh2">
                            <span title="{$lang_plugin_annotate['no_access']}" style="cursor:help;">{$annotate_icon_array['permission_none']}---</span>
                        </td>
                        <td valign="middle" align="center" class="tableh2">
                            <span title="{$lang_plugin_annotate['read_annotations']}" style="cursor:help;">{$annotate_icon_array['permission_read']}R--</span>
                        </td>
                        <td valign="middle" align="center" class="tableh2">
                            <span title="{$lang_plugin_annotate['read_write_annotations']}" style="cursor:help;">{$annotate_icon_array['permission_write']}RW-</span>
                        </td>
                        <td valign="middle" align="center" class="tableh2">
                            <span title="{$lang_plugin_annotate['read_write_delete_annotations']}" style="cursor:help;">{$annotate_icon_array['permission_delete']}RWD</span>
                        </td>
                    </tr>
EOT;
    // Group output --- start
    $loopCounter = 0;
    $result = cpg_db_query("SELECT group_id, group_name FROM {$CONFIG['TABLE_USERGROUPS']} ORDER BY group_id ASC");
    while($row = mysql_fetch_assoc($result)) { // while-loop mysql_fetch_assoc groups --- start
        if ($loopCounter/2 == floor($loopCounter/2)) {
            $cell_style = 'tableb';
        } else {
            $cell_style = 'tableb tableb_alternate';
        }
        $group_output[$row['group_id']] = ''; 
        if (in_array($row['group_id'], $cpg_udb->admingroups)) {
            echo <<< EOT
                <tr>
                    <td valign="top" align="left" class="{$cell_style}">
                        {$row['group_name']}
                    </td>
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="radio" class="radio" disabled="disabled" />
                    </td>
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="radio" class="radio" disabled="disabled" />
                    </td>
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="radio" class="radio" disabled="disabled" />
                    </td>
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="radio" class="radio" checked="checked" />
                    </td>
EOT;
        } else {
            $row['permission'] = mysql_result(cpg_db_query("SELECT value FROM {$CONFIG['TABLE_CONFIG']} WHERE name = 'plugin_annotate_permissions_{$row['group_id']}'"),0);
            echo <<< EOT
                    <td valign="top" align="left" class="{$cell_style}">
                        {$row['group_name']}
                    </td>
EOT;
            for ($i=0; $i <= 3; $i++) {
                if (!is_numeric($row['permission']) && $i == 0) {
                    $checked = "checked=\"checked\"";
                } else {
                    $checked = $row['permission'] == $i ? "checked=\"checked\"" : "";
                }
                echo <<< EOT
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="radio" name="plugin_annotate_permissions_{$row['group_id']}" id="plugin_annotate_permissions_{$row['group_id']}_{$i}" class="radio" value="{$i}" $checked />
                    </td>
EOT;
            }
        }
        // display notes --- start
        if ($CONFIG['plugin_annotate_display_notes_'.$row['group_id']] == '1') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo <<< EOT
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="checkbox" name="plugin_annotate_display_notes_{$row['group_id']}" id="plugin_annotate_display_notes_{$row['group_id']}" class="checkbox" value="1" {$checked} />
                    </td>
EOT;
        // display notes --- end
        // display links --- start
        if ($CONFIG['plugin_annotate_display_links_'.$row['group_id']] == '1') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo <<< EOT
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="checkbox" name="plugin_annotate_display_links_{$row['group_id']}" id="plugin_annotate_display_links_{$row['group_id']}" class="checkbox" value="1" {$checked} />
                    </td>
EOT;
        // display links --- end
        // display stats --- start
        if ($CONFIG['plugin_annotate_display_stats_'.$row['group_id']] == '1') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }
        echo <<< EOT
                    <td valign="top" align="center" class="{$cell_style}">
                        <input type="checkbox" name="plugin_annotate_display_stats_{$row['group_id']}" id="plugin_annotate_display_stats_{$row['group_id']}" class="checkbox" value="1" {$checked} />
                    </td>
EOT;
        // display stats --- end
        $loopCounter++;
        echo <<< EOT
                    </tr>
EOT;
    } // while-loop mysql_fetch_assoc groups --- end
    mysql_free_result($result);
    // Group output --- end
    echo <<< EOT
                    <tr>
                        <td valign="middle" class="tablef">
                        </td>
                        <td valign="middle" class="tablef" colspan="7">
                            <input type="hidden" name="form_token" value="{$form_token}" />
                            <input type="hidden" name="timestamp" value="{$timestamp}" />
                            <button type="submit" class="button" name="submit" value="{$lang_common['ok']}">{$annotate_icon_array['ok']}{$lang_common['ok']}</button>
                        </td>
                    </tr>
EOT;
    endtable();
    echo <<< EOT
            {$additional_submit_information}
            </form>

EOT;
}


function annotate_get_level($what) {
    global $CONFIG, $cpg_udb;

    // Admin always have the highest permission
    if ($what == "permissions" && GALLERY_ADMIN_MODE) {
        return 3;
    }

    if (!USER_ID) {
        return $CONFIG["plugin_annotate_{$what}_".$cpg_udb->guestgroup];
    }

    $result = cpg_db_query("SELECT user_group, user_group_list FROM {$CONFIG['TABLE_USERS']} WHERE user_id = ".USER_ID);
    $user = mysql_fetch_assoc($result);
    mysql_free_result($result);
    if ($user['user_group_list'] != "") {
        $user_group_list = explode(",", $user['user_group_list']);
    }
    $user_group_list[] = $user['user_group'];
    
    for($i=0; $i<count($user_group_list); $i++) {
        $list[$i] = "name = 'plugin_annotate_{$what}_{$user_group_list[$i]}'";
    }

    $result = cpg_db_query("SELECT MAX(value) FROM {$CONFIG['TABLE_CONFIG']} WHERE ".implode(" OR ", $list));
    $level = mysql_result($result, 0);
    mysql_free_result($result);
    $level = $level > 0 ? $level : 0;

    return $level;
}


function annotate_notes_editable() {
    global $CONFIG;
    if ((GALLERY_ADMIN_MODE && $CONFIG['plugin_annotate_type'] != 0) || $CONFIG['plugin_annotate_type'] == 1 || $CONFIG['plugin_annotate_type'] == 3) {
        return true;
    } else {
        return false;
    }
}

?>