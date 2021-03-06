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
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

if (defined('DISPLAYIMAGE_PHP')) {
	$thisplugin->add_filter('page_meta','annotate_meta');
	$thisplugin->add_filter('file_data','annotate_file_data');
}

$thisplugin->add_action('plugin_install','annotate_install');
$thisplugin->add_action('plugin_uninstall','annotate_uninstall');
$thisplugin->add_action('plugin_cleanup','annotate_cleanup');

function annotate_meta(){

	$meta  = "\n" . '<script src="plugins/annotate/lib/httpreq.js" type="text/javascript"></script>';
	$meta .= "\n" . '<script src="plugins/annotate/lib/photonotes.js" type="text/javascript"></script>';
	$meta .= "\n" . '<link rel="stylesheet" href="plugins/annotate/lib/photonotes.css" type="text/css" />';
	$meta .= "\n";

	return $meta;
}

function annotate_file_data($CURRENT_PIC_DATA){

	global $CONFIG;
	
	if (is_image($CURRENT_PIC_DATA['filename'])){

		$sql = "SELECT * FROM {$CONFIG['TABLE_PREFIX']}notes WHERE pid = {$CURRENT_PIC_DATA['pid']}";
		$result = cpg_db_query($sql);
		
		$notes = array();
		
		while ($row = mysql_fetch_assoc($result)) {
			$row['note'] = addslashes($row['note']);
			$notes[] = $row;
		}
				
		mysql_free_result($result);
	
		$jsarray = arrayToJS4($notes, 'annotations');

		$html =& $CURRENT_PIC_DATA['html'];
		
		$html = '<div class="Photo fn-container" id="PhotoContainer">' . $html . '</div>';

		if (USER_ID){
			
		$html .= <<< EOT
		
		<div style="clear: both; padding-top: 20px">
			<form action="" method="post">
				<input type="submit" class="button" name="addname" value="Annotate" onclick="return addnote()" />
			</form>
		</div>

EOT;

		}
		
		$user_id  = USER_ID;
		$admin = GALLERY_ADMIN_MODE ? 'true' : 'false';
		
		$html .= <<< EOT
		
<script type="text/javascript">

var $jsarray

/* create the Photo Note Container */
var container = document.getElementById('PhotoContainer');

var notes = new PhotoNoteContainer(container);

for (var n = 0; n < annotations.length; n++){
	
	/* create a note */
	var size = new PhotoNoteRect(annotations[n].posx, annotations[n].posy, annotations[n].width, annotations[n].height);
	var note = new PhotoNote(annotations[n].note,'note' + n, size);
	
	/* implement the save/delete functions */
	note.onsave = function (note) { return ajax_save(note); };
	note.ondelete = function (note) { return ajax_delete(note); };
	
	/* assign the note id number */
	note.nid = annotations[n].nid;
	
	if (!$admin && note.user_id != $user_id) note.editable = false;
	
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

function addnote(){

	var newNote = new PhotoNote('','note' + n,new PhotoNoteRect(10,10,50,50));
	newNote.onsave = function (note) { return ajax_save(note); };
	newNote.ondelete = function (note) { return ajax_delete(note); };
	notes.AddNote(newNote);
	newNote.Select();
	newNote.nid = 0;
	
	return false;
}

function ajax_save(note){

	var data = 'add=' + {$CURRENT_PIC_DATA['pid']} + '&nid=' + note.nid + '&posx=' + note.rect.left + '&posy=' + note.rect.top + '&width=' + note.rect.width + '&height=' + note.rect.height + '&note=' + encodeURI(note.text);

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

	return $CURRENT_PIC_DATA;
}

// Based on code by Rob Williams
//Convert a PHP array to a JavaScript one (rev. 4)
function arrayToJS4($array, $baseName) {

	$return = '';

   //Write out the initial array definition
   $return .= ($baseName . " = new Array(); \r\n ");    

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
         	$return .= ($value . "; \r\n ");
         } else if (is_string($value)) {
            //Output as a string, as we did before       
            $return .= ("'" . $value . "'; \r\n ");
         } else if ($value === false) {
            //Explicitly output false
            $return .= ("false; \r\n ");
         } else if ($value === NULL) {
            //Explicitly output null
            $return .= ("null; \r\n ");
         } else if ($value === true) {
            //Explicitly output true
            $return .= ("true; \r\n ");
         } else {
            //Output the value directly otherwise
            $return .= ($value . "; \r\n ");
         }
      }
   }
   
   return $return;
}


function annotate_install() {
    global $thisplugin, $CONFIG;

	$sql = "DROP TABLE IF EXISTS `{$CONFIG['TABLE_PREFIX']}notes`";
	cpg_db_query($sql);
	
	$sql = <<< EOT
	
CREATE TABLE IF NOT EXISTS `{$CONFIG['TABLE_PREFIX']}notes` (
  `nid` smallint(5) unsigned NOT NULL auto_increment,
  `pid` mediumint(8) unsigned NOT NULL,
  `posx` smallint(5) unsigned NOT NULL,
  `posy` smallint(5) unsigned NOT NULL,
  `width` smallint(5) unsigned NOT NULL,
  `height` smallint(5) unsigned NOT NULL,
  `note` text NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`nid`),
  KEY `pid` (`pid`)
) TYPE=MyISAM ;

EOT;

	return cpg_db_query($sql);
 }