<?php
 	// If this file is called, all images in the database will be processed
 	// I don't know, if this causes a problem when there are a lot of images :-)

	global $CONFIG, $flf_lang_var;
	if (!GALLERY_ADMIN_MODE) cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
	pageheader($flf_lang_var['generateallheader']);
	
	starttable("90%");
	

	$insertedvalues=generateAllExifsIntoTable();
	print $insertedvalues[0]. $flf_lang_var['createall'];
	print "<br>";
	print $insertedvalues[1]." ".$flf_lang_var['contained_geo'];
	print "<br>";
	print $flf_lang_var['createall_success'];
	endtable();

	pagefooter();
	
	ob_end_flush();

?>
