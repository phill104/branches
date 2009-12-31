<?php
/**
 * Coppermine Photo Gallery
 *
 * Copyright (c) 2003-2009 Coppermine Dev Team
 * v1.1 originally written by Gregory DEMAR
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Coppermine version: 1.5.xx
 *
 * LightBox plugin ver 1.0 for Coppermine
 *
 * Plugin Written by Joe Carver - http://gallery.josephcarver.com/natural/ - http://i-imagine.net/artists/ - http://photos-by.joe-carver.com/
 * Based on plugin by jeepguy_1980 and mod. by Sander Weyens
 * 27 December 2009
 * 
 *  Using NFLightbox Copyright (c) 2009, Helori LAMBERTY NotesFor.net
 *
*/

	pageheader('LightBox Configuration');
	
	if (!GALLERY_ADMIN_MODE) cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
	
    global $lang_plugin_php, $CONFIG, $lang_common, $lang_pluginmgr_php, $lang_admin_php, $icon_array;
	
	// variables for plugin
		$lb_bor  = $CONFIG['plugin_lightbox_nfn_border'];	
		$lb_tim  = $CONFIG['plugin_lightbox_nfn_slidetimer'];
		$lb_spd  = $CONFIG['plugin_lightbox_nfn_sizespeed'];
		$lb_not  = $CONFIG['plugin_lightbox_nfn_notimer'];			
		$lb_ext  = $CONFIG['plugin_lightbox_nfn_image_exit'];
		$lb_cap  = $CONFIG['plugin_lightbox_nfn_caption'];
		$lb_fol  = $CONFIG['plugin_lightbox_nfn_nofollow'];			
		$lb_max  = $CONFIG['plugin_lightbox_nfn_maxpics'];
	
		$sel_not = '';	
		if ($lb_not  == '1') {
	   	$sel_not = 'selected="selected"';
		}
		$sel_notb = '';	
		if ($lb_not  == '0') {
	   	$sel_notb = 'selected="selected"';
		}

		$sel_ext = '';	
		if ($lb_ext  == '1') {
	   	$sel_ext = 'selected="selected"';
		}
		$sel_extb = '';	
		if ($lb_ext  == '0') {
	   	$sel_extb = 'selected="selected"';
		}

		$sel_cap = '';	
		if ($lb_cap  == '1') {
	   	$sel_cap = 'selected="selected"';
		}
		$sel_capb = '';	
		if ($lb_cap  == '0') {
	   	$sel_capb = 'selected="selected"';
		}
	
		$sel_fol = '';	
		if ($lb_fol  == '1') {
	   	$sel_fol = 'selected="selected"';
		}
		$sel_folb = '';	
		if ($lb_fol  == '0') {
	   	$sel_folb = 'selected="selected"';
		}

	
	// Make form 
		$lb_superCage = Inspekt::makeSuperCage();
	
	starttable("85%");
	echo <<< EOT
	<tr><td>
	<form name="cpgform" id="cpgform" action="{$_SERVER['REQUEST_URI']}" method="post">
	<table border="0" cellspacing="2" cellpadding="2" width= "85%">
			<tr>
			<td><br /><b>Configure the settings of your LightBox plugin.</b></td>
			</tr>
			<tr>
			<td></td>
			</tr>
			<tr>
			<td> 
			<hr> Show slideshow timer bar - <b>Will use more of visitor's cpu</b>
			</td>
			<td>
			<br />
			<select name="notimer" id="notimer">
                 <option value="1" $sel_not>No timer bar</option>
                 <option value="0" $sel_notb>Show timer bar</option>
            </select>
			</td>
			</tr>
			<tr>
			<td> 
			<hr> Return to last or first slide's page on exit
			</td>
			<td> 
			<br />
			<select name="exit" id="exit">
                 <option value="1" $sel_ext>Return to last</option>
                 <option value="0" $sel_extb>Return to first</option>
            </select>
			</td>
			</tr>
			<tr>
			<td> 
			<hr> Show image captions below titles
			</td>
			<td>
			<br />
			<select name="caption" id="caption">
                 <option value="1" $sel_cap>Show captions</option>
                 <option value="0" $sel_capb>No captions</option>
            </select>
			</td>
			</tr>
			<tr>
			<td> 
			<hr> Add attribute "nofollow" to LightBox links
			</td>
			<td>
			<br />
			<select name="nofollow" id="nofollow">
                 <option value="1" $sel_fol>Add nofollow</option>
                 <option value="0" $sel_folb>No nofollow</option>
            </select>
			</td>
			</tr> 
			<tr>			
			<td>
			<hr> Set slideshow timer interval<br />-  set in milliseconds - 1000 = 1 second 
			</td>
			</tr> 
			<tr>		
			<td class="tableb" align="right">Slideshow timer:</td>
            <td>
            <input type="text" name="slidetimer" class="textinput" size="3" value="$lb_tim" />ms
            </td>
            </tr>
			<tr>			
			<td>
			<hr> Set image swap time <br />-  set in milliseconds - 500 = 1/2 second
			</td>
			</tr> 
			<tr>		
			<td class="tableb" align="right">Swap time:</td>
            <td>
            <input type="text" name="sizespeed" class="textinput" size="3" value="$lb_spd" />ms
            </td>
            </tr>
			<tr>			
			<td>
			<hr> Set width of border <br />-  Set in pixels
			</td>
			</tr> 
			<tr>		
			<td class="tableb" align="right">Border width:</td>
            <td>
            <input type="text" name="border" class="textinput" size="2" value="$lb_bor" />pixels
            </td>
            </tr>
			<tr>			
			<td>
			<hr> Set number of files in album to list for Slideshow<br /> <b> 1 = All files in album</b> 
			<br /> Large albums and galleries might want to limit to 500 or less
			</td>
			</tr> 
			<tr>		
			<td class="tableb" align="right">Files in piclist:</td>
            <td>
            <input type="text" name="maxpics" class="textinput" size="4" value="$lb_max" />files listed
            </td>
            </tr>			
			<tr>
			<td> </td>
			</tr> 
			<tr>
			<td class="tableb" align="right">
            </td>
            <td>
            </td>
            </tr>	
			<tr>
			<td class="tableb" align="right"><br /></td>
            </tr>
            <tr>
            <td class="tableb">
            </td>
            <td>
			</td>
			</tr>
			<tr>
			<td class="tableb">
			</td>
			<td>
			</td>
			</tr>
			<tr>			
	       <td class="tableb" align="right"><br /><br />
			<span>
			<input name="update" type="hidden" id="update" value="1" />
			<input type="submit" name="submit" value="Submit" /> 
		</span>
		</td>
		</tr>
		</td>		
		</table>
		</form>
EOT;

	//change values in the configuration table - minimal data filtering assumes admin use only
	if ($lb_superCage->post->keyExists('update')) {

  $lb_bor = $lb_superCage->post->getInt('border');
  $lb_tim = $lb_superCage->post->getInt('slidetimer');
  $lb_spd = $lb_superCage->post->getInt('sizespeed');
  $lb_not = $lb_superCage->post->getInt('notimer');
  $lb_ext = $lb_superCage->post->getRaw('exit');
  $lb_cap = $lb_superCage->post->getInt('caption');
  $lb_fol = $lb_superCage->post->getInt('nofollow');
  $lb_max = $lb_superCage->post->getInt('maxpics');  
  
  	if ($lb_bor == '') cpg_die(ERROR, 'ERROR - BORDER FIELD EMPTY', __FILE__, __LINE__);
	if ($lb_tim == '') cpg_die(ERROR, 'ERROR - TIMER FIELD EMPTY', __FILE__, __LINE__);
	if ($lb_spd == '') cpg_die(ERROR, 'ERROR - RESIZE FIELD EMPTY', __FILE__, __LINE__);
	if ($lb_max == '') cpg_die(ERROR, 'ERROR - MAX FILES FIELD EMPTY', __FILE__, __LINE__);
	if ($lb_fol < 0) cpg_die(ERROR, 'ERROR - FOLLOW FIELD EMPTY', __FILE__, __LINE__);
	if ($lb_cap < 0) cpg_die(ERROR, 'ERROR - CAPTION FIELD EMPTY', __FILE__, __LINE__);	
	
		            
	    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_bor' WHERE name = 'plugin_lightbox_nfn_border'");
	    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_tim' WHERE name = 'plugin_lightbox_nfn_slidetimer'");	
	    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_spd' WHERE name = 'plugin_lightbox_nfn_sizespeed'");
	    cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_not' WHERE name = 'plugin_lightbox_nfn_notimer'");
		cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_ext' WHERE name = 'plugin_lightbox_nfn_image_exit'");
		cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_cap' WHERE name = 'plugin_lightbox_nfn_caption'");
		cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_fol' WHERE name = 'plugin_lightbox_nfn_nofollow'");
		cpg_db_query("UPDATE {$CONFIG['TABLE_CONFIG']} SET value = '$lb_max' WHERE name = 'plugin_lightbox_nfn_maxpics'");		
		
		
        cpgRedirectPage('index.php?file=lightbox_notes_for_net/admin', $lang_common['information'], 'LightBox plugin settings saved successfully', 1);
		
        exit;
	}

	endtable();

	pagefooter();
	
	ob_end_flush();

?>