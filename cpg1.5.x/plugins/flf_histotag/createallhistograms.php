<?php
 	// If this file is called, all images in the database will be processed
 	// I don't know, if this causes a problem when there are a lot of images :-)
	
	global $CONFIG, $flf_lang_var;
	if (!GALLERY_ADMIN_MODE) cpg_die(ERROR, $lang_errors['perm_denied'], __FILE__, __LINE__);
	pageheader('Create all Histogrrams for all Images!');
	require_once('include/histotag_histogram_support.php');
	starttable("90%");
	
	print $flf_lang_var['createallhistograms'];
	$insertedvalues=generateAllHistograms();

	print $insertedvalues." ".$flf_lang_var['createallhistograms_success'];
	
	endtable();

	pagefooter();
	
	ob_end_flush();

?>
