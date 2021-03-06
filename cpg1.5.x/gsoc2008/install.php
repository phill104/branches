<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2008 Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License version 3
  as published by the Free Software Foundation.

  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision: 4015 $
  $LastChangedBy: SaWeyy $
  $Date: 2007-10-31 10:23:22 +0100 (wo, 31 okt 2007) $
**********************************************/
########################
####Install Main Code###
########################
define('IN_COPPERMINE', true);
define('INSTALL_PHP', true);
define('VERSIONCHECK_PHP', true);
if (!defined('COPPERMINE_VERSION')) { // we need to define the constant COPPERMINE_VERSION that normally get's populated by include/init.inc.php, as we check the repository against that version number
	define('COPPERMINE_VERSION', '1.5.0');
}
// include Inspekt for sanitization
$incp = get_include_path().PATH_SEPARATOR.dirname(__FILE__).PATH_SEPARATOR.dirname(__FILE__).DIRECTORY_SEPARATOR.'include';
set_include_path($incp);
require_once "include/Inspekt.php";
$superCage = Inspekt::makeSuperCage();

$install = new CPGInstall();

// get installation step
if($superCage->get->keyExists('step') && is_array($install->config['steps_done']) &&  in_array($superCage->get->getInt('step'), $install->config['steps_done'])) {
	$step = $superCage->get->getInt('step');
} else {
	if(isset($install->config['step'])) {
		$step = $install->config['step'];
	} else {
		$step = '1';
	}
}

// check if we have an update instead of a next step request
if($superCage->post->keyExists('update_lang')) $step = 1;
if($superCage->post->keyExists('update_im_path')) $step = 4;
if($superCage->post->keyExists('update_check_connection')) $step = 6;
if($superCage->post->keyExists('update_create_db')) $step = 7;

// if installation is done, only show last page
if(isset($install->config['install_finished'])) $step = 11;


$install->setTmpConfig('steps_done', $step, true);
// check if the installer has already been run succefully
if($install->error != '') {
	html_header();
	html_installer_locked();
	html_footer();
	exit;
}

// check if we have new user input and put it in the temp config
if($superCage->post->keyExists('thumb_method')) $install->setTmpConfig('thumb_method', $superCage->post->getAlnum('thumb_method'));
if($superCage->post->keyExists('im_path') && $superCage->post->getPath('im_path') != (dirname($superCage->server->getPath('SCRIPT_FILENAME') . DIRECTORY_SEPARATOR))) $install->setTmpConfig('im_path', $superCage->post->getPath('im_path'));

switch($step) {
	case 1:		// Initialisation & natural language selection
		//write a coockie to check in the next step
		setcookie('cpg_install_cookie_check', 'passed', time() + 3600);
		
		$install->page_title = $install->language['title_welcome'];
		html_header();
		html_welcome();
		html_footer();
		break;
		
	case 2:		// Are all mandatory files there (versioncheck has to be completed first)
		// Here we also do an extensive version check of php/mysql + check of javascript/cookies/register_globals
		// the cookie for this check is inserted in the previous step!
		// javascript is tested by altering a hidden form element in the previous step.
		
		//PHP VERSION CHECK
		$php_version = phpversion();
		$required_php_version = '4.1.0';
		if(version_compare($required_php_version, $php_version, '>=')){
			//check if php_version is actualy a version number
			if($php_version == ''){
				//version could not be detected, show corresponding error
				$install->error .= sprintf($install->language['version_undetected'], required_php_version, 'PHP') . '<br /><br />';
			}else{
				//user is using incompatible php version
				$install->error .= sprintf($install->language['version_incompatible'], $php_version, 'PHP', $required_php_version) . '<br /><br />';
			}
		}
		//MySQL VERSION CHECK
		ob_start();
		$coulwegettheinfoofphpinfo = phpinfo();
		$php_info = ob_get_clean ();
		preg_match('%<tr><td class="e">Client API version </td><td class="v">([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})%', $php_info, $temp_version);
		$mysql_version = $temp_version[1];
		$required_mysql_version = '3.23.23';
		if(version_compare($required_mysql_version, $mysql_version, '>=')){
			//check if php_version is actualy a version number
			if($mysql_version == ''){
				//version could not be detected, show corresponding error
				$install->error .= sprintf($install->language['version_undetected'], $required_mysql_version, 'MySQL') . '<br /><br />';
			}else{
				//user is using incompatible php version
				$install->error .= sprintf($install->language['version_incompatible'], $mysql_version, 'MySQL', $required_mysql_version) . '<br /><br />';
			}
		}
		//COOKIE CHECK
		if($superCage->cookie->getAlpha('cpg_install_cookie_check') != 'passed'){
			//no cookie found, you're in trouble now :)
			$install->error .= $install->language['no_cookie'] . '<br /><br />';
		}
		//JAVASCRIPT CHECK
		if($superCage->post->getAlpha('javascript_check') != 'passed' && !$install->config['javascript_test_passed']){
			//javascripts seems to be disabled, send them the message...
			$install->error .= $install->language['no_javascript'] . '<br /><br />';
		}else{
			$install->config['javascript_test_passed'] = true;
		}
		//REGISTER_GLOBALS CHECK
		if(ini_get(register_globals)){
			//register_globals is turned on, please turn it of.
			$install->error .= $install->language['register_globals_detected'] . '<br /><br />';
		}
		
		$install->page_title = $install->language['title_file_check'];
		html_header();
		if($install->error != ''){
			html_error(false /*false to not include a button*/);
		}
		html_content($install->checkFiles()); 
		html_footer();
		$install->setTmpConfig('step', '3');
		break;
		
	case 3:		// Check if the folder permissions are set up properlyµ
		$install->page_title = $install->language['title_dir_check'];
		if(!$install->checkPermissions()) {
			// not all permissions were set right, or folder doesn't exist
			html_header();
			html_error();
			html_content();// show all checked folders?
			html_footer();
		} else {
			// permissions are set alright, continue
			html_header();
			html_content($install->language['perm_ok']);// show all checked folders?
			html_footer();
			$install->setTmpConfig('step', '4');
		}
		break;
		
	case 4:		// Check available image processors & let user choose
		$install->page_title = $install->language['title_imp'];
		$image_processors = $install->checkImageProcessor();
		html_header();
		if($install->error != '') {
			html_error();
		} else {
			$install->setTmpConfig('step', '5');
		}
			$content = $install->language['im_packages'] . '<br /><br />';
		$imp_list = '<select name="thumb_method" >';
		if(isset($image_processors['gd2']))	{
			// gd2 is avilable, add it to the list
			$imp_list .= '<option value="gd2">GD2</option>';
			$content .= '<b>GDlib</b> Version 2. <br />';
			$selected = 'gd2';
		} elseif(isset($image_processors['gd1']))	{
			// gd1 is avilable, add it to the list
			$imp_list .= '<option value="gd">GD</option>';
			$content .= '<b>GDlib</b> Version 1. <br />';
			$selected = 'gd1';
		}
		// check configuration options of im_path
		if(isset($image_processors['im']))	{
			// im is available, add it to the list
			$path = str_replace(array('.exe', '"'), '',$image_processors['im']['path']);
			$path = substr($path, 0, (strlen($path) - 7));
			$imp_list .= '<option value="im">ImageMagick</option>';
			$content .= '<b>ImageMagick</b> Version ' . substr($image_processors['im']['version'], 20, 7) . '(at: ' . $path .')';
			$selected = 'im';
		} else {
			$im_not_found .= '<br /><br /><fieldset style="width:90%" title="ImageMagick">' . $install->language['im_not_found'] .'</fieldset>';
		}
		// check configuration options
		if(isset($install->config['thumb_method'])) $selected = $install->config['thumb_method'];
		$imp_list .= '</select>';
		$imp_list = str_replace($selected . '"', $selected . '" selected="selected"', $imp_list);
		
		// if no image library is found, tell the user so, and select gd2
		if(!isset($selected)) {
			$content .= '<br /><br /><fieldset style="width:90%" title="GD">' . $install->language['no_gd'] . '</fieldset><br /><br />' . $im_not_found;
		} else {
			$content .= '<br /><br />' . $install->language['installer_selected'] . ' \'' . $selected . '\'<br /><br />' . $imp_list . $im_not_found;
		}
		
		// add IM path box
		(isset($install->config['im_path']) && $superCage->post->getPath('im_path') != (dirname($superCage->server->getPath('SCRIPT_FILENAME') . DIRECTORY_SEPARATOR))) ? $path = $install->config['im_path'] : $path = $path;
		$content .= '<br /><br />' . $install->language['im_path'] . '<br /><input type="text" size="70" name="im_path" value="' . $path . '" /><input type="submit" name="update_im_path" value="' . $install->language['check_path'] . '" />';
		
		html_content($content);
		html_footer();
		break;
		
	case 5:		// Check if the image library information has been set up properly - display some basic test images created with the image library selected
		$install->page_title = $install->language['title_imp_test'];
		html_header();
		$content = '<center>' . $install->testImageProcessor() . '</center>';
		if($install->error != '') {
			html_error();
		} else {
			$install->setTmpConfig('step', '6');
		}
		html_content($content);
		html_footer();
		break;
		
	case 6:		// Ask user for mysql host, username and password, try to establish a connection using that info
		$install->page_title = $install->language['title_mysql_user'];
		// check if we are trying to test the connection
		if($superCage->post->keyExists('update_check_connection') || (isset($install->config['db_host']) && $superCage->post->keyExists('db_host'))) {
			// here we do not use the setTmpConfig funtion, as this function always writes the new file
			// and it will be written in the third step...
			$install->config['db_host'] = $superCage->post->getRaw('db_host');
			$install->config['db_user'] = $superCage->post->getRaw('db_user');
			$install->setTmpConfig('db_password', $superCage->post->getRaw('db_password'));
			
			// test the connection
			$install->checkSqlConnection();	
		}
		html_header();
		if($install->error != '') {
			html_error();
		} else {
			if(isset($install->config['db_password'])) $install->setTmpConfig('step', '7');
		}
		html_mysql_start();
		html_footer();
		break;
		
	case 7:		// Ask the user if he wants to use an existing db or if he wants the installer to create a new database. Try to perform the selected choice. Ask for the table prefix
		$install->page_title = $install->language['title_mysql_db_sel'];
		// save the db data from previous step
		if($superCage->post->keyExists('db_host')  && !isset($install->config['db_populated'])) {
			// here we do not use the setTmpConfig funtion, as this function always writes the new file
			// and it will be written in the third step...
			$install->config['db_host'] = $superCage->post->getRaw('db_host');
			$install->config['db_user'] = $superCage->post->getRaw('db_user');
			$install->setTmpConfig('db_password', $superCage->post->getRaw('db_password'));
			if($install->error != '') {
				$install->error .= '<br /><br />' . sprintf($install->language['please_go_back'], '<a href="install.php?step=' . ($step - 1) . '">', '</a>');
			}
		} elseif($superCage->post->keyExists('update_create_db') && trim($superCage->post->getRaw('new_db_name')) != '') {
			// try to create a new database.
			$install->createMysqlDb(trim($superCage->post->getRaw('new_db_name')));
			// save table prefix
			$install->setTmpConfig('db_prefix', $superCage->post->getRaw('db_prefix'));
		}
		$install->checkSqlConnection();	
		html_header();
		if($install->error != '') {
			html_error();
		} else {
			html_mysql_select_db();
			$install->setTmpConfig('step', '8');
		}
		html_footer();
		break;
		
	case 8:		// save db_prefix/_name and finally create the tables
		$install->page_title = $install->language['title_mysql_pop'];
		// save the db data from previous step
		if($superCage->post->keyExists('db_name') && !isset($install->config['db_populated'])) {
			$install->setTmpConfig('db_name', $superCage->post->getRaw('db_name'));
			$install->setTmpConfig('db_prefix', $superCage->post->getRaw('db_prefix'));
		}
		// populate db if not done yet
		$set_populated = false;
		if(!isset($install->config['db_populated']) && isset($install->config['db_name'])) {
			$msg = $install->language['db_populating'];
			if($install->populateMysqlDb()) {
				$set_populated = true;
			}
		} elseif(!isset($install->config['db_populated']) && !isset($install->config['db_name'])) {
			$msg = sprintf($install->language['not_here_yet'], '<b><a href="install.php?step=7">', '</a></b>');
		}

		if(isset($install->config['db_populated']))	{
			html_header();
			if($install->error != '') {
				html_error();
			}
			$install->temp_data = '<tr><td><br /><br /><br />' . $install->language['db_alr_populated'] . '<br /><br /><br /><br /></td></tr>';
			html_content($install->language['db_populating']);
			html_footer();
			$install->setTmpConfig('step', '9');
		} else {
			if($set_populated) {
				// this is a lock to see if the db has been created yet	
				$install->setTmpConfig('db_populated', 'done');	
				$install->setTmpConfig('step', '9');
			}
			html_header();
			if($install->error != '') {
				html_error();
			}
			html_content($msg);
			html_footer();
			break;
		}
		break;
		
	case 9:		// Ask for coppermine admin username, password and email address
		$install->page_title = $install->language['title_admin'];
		if($superCage->post->keyExists('admin_username')) {
			// check validity of admin details
			$admin_username = $superCage->post->getMatched('admin_username', '/\A\w*\Z/');
			if($admin_username[0] == '' || !$admin_username) {
				// admin username not correct
				$install->error .= $install->language['user_err'] . '<br />';
			} else {
				$install->setTmpConfig('admin_username', $superCage->post->getAlnum('admin_username'));
			}
			$admin_password = $superCage->post->getMatched('admin_password', '/\A\w*\Z/');
			$admin_password_verif = $superCage->post->getMatched('admin_password_verif', '/\A\w*\Z/');
			if($admin_password[0] != $admin_password_verif[0] || !$admin_password || $admin_password[0] == '') {
				// admin password not correct
				$install->error .= $install->language['pass_err'] . '<br />';
			} else {
				$install->setTmpConfig('admin_password', $superCage->post->getAlnum('admin_password'));
			}
			$email = $superCage->post->getMatched('admin_email', '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i');
			$ver_email = $superCage->post->getMatched('admin_email_verif', '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i');
			if($email[0] != $ver_email[0] || $email[0] == '') {
				// admin email not correct
				$install->error .= $install->language['email_no_match'] . '<br />';
			} else {
				$install->setTmpConfig('admin_email', $email[0]);
			}		
		}
		
		if(isset($install->config['admin_username']) && isset($install->config['admin_password']) && isset($install->config['admin_email']) && !isset($install->config['install_finished'])){
			// create admin
			if($install->createAdmin()) {
				// add finished flag to config
				//$install->setTmpConfig('install_finished', 'done'); // OVI
				$install->setTmpConfig('admin_account_created', 'done');
				$install->setTmpConfig('step', '10'); // OVI
				// redirect to last page
				header('Location: install.php?step=' . ($step + 1));
				exit;
			}
		}
		html_header();
		if($install->error != '') {
			html_error();
		} else {
			if(isset($install->config['admin_username'])) $install->setTmpConfig('step', '10');
		}
		html_admin();
		html_footer();
		break;


// OVI

	case 10:		// Ask for storage module
		$install->page_title = $install->language['title_storage_module'];

		if($superCage->post->keyExists('storage_module'))
		{
			$install->setTmpConfig('storage_module', $superCage->post->getRaw('storage_module'));
		}

		if(isset($install->config['storage_module']) && !isset($install->config['install_finished']))
		{
			// set active storage module
			if($install->setStorageModule())
			{
				// add finished flag to config
				$install->setTmpConfig('install_finished', 'done');
				// set step
				$install->setTmpConfig('step', '11');
				// redirect to last page
				header('Location: install.php?step=' . ($step + 1));
				exit;
			}
		}
		html_header();
		if($install->error != '') {
			html_error();
		} else {
			if(isset($install->config['storage_module'])) $install->setTmpConfig('step', '11');
		}
		html_storage_module();
		html_footer();
		break;

	case 11:	// Finally check if everything is set up properly and tell the user so
		// write real config file
		$install->writeConfig();
		
		$install->page_title = $install->language['title_finished'];
		html_header();
		if($install->error != '') {
			html_error();
		}
		html_finish();
		html_footer();
		
		// delete temp config + created test images!!
		$files_to_remove = array(
			'albums/combined_generated.jpg',
			'albums/giftest_generated.gif',
			'albums/giftest_generated.jpg',
			'albums/jpgtest_generated.jpg',
			'albums/pngtest_generated.jpg',
			'albums/pngtest_generated.png',
			'albums/scaled_generated.jpg',
			'albums/texttest_generated.jpg',
			'include/config.tmp',			
		);
		foreach($files_to_remove as $file){
			if(is_file($file)){
				unlink($file);
			}
		}
		
		break;	
}

########################
####Install Templates###
########################

/* html_header()
 * 
 * prints the header
 */
function html_header() {
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Coppermine - <?php echo $install->language['installer']; ?></title><link type="text/css" rel="stylesheet" href="installer.css">
</head>
<body>
 <div align="center">
  <div style="width:600px;">
  <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
    <tr>
      <td valign="top" style="background-color:#EFEFEF"><img src="images/coppermine-logo.png" width="260" height="60" border="0" alt="" /><br />
      </td>
    </tr>
  </table>
<?php
html_stepper();
}

/* html_footer()
 * 
 * prints the footer
 */
function html_footer() {
?>
  </div>
 </div>
</body>
</html>
<noscript><plaintext>
<?php
}

/* html_stepper()
 * 
 * prints current installation step with links to the next ones
 */
function html_stepper() {
	global $install, $step;
	$stepper = '';
	$tpl_step_done = '<td class="stepper_d" onMouseOver="this.className=\'stepper_do\'" onMouseOut="this.className=\'stepper_d\'" onClick="document.location=\'install.php?step=%s\'"><a href="install.php?step=%s" title="Step: %s">%s</a></td>';
	$tpl_step_current = '<td class="stepper_c"><span title="Step: %s">%s</span></td>';
	$tpl_step_notyet = '<td class="stepper_n"><span title="Step: %s">%s</span></td>';
	for($i = 1; $i < 12; $i++) {
		if($i == $step) {
			$stepper .= sprintf($tpl_step_current, $i, $i);
		} elseif(is_array($install->config['steps_done']) && in_array($i, $install->config['steps_done'])) {
			$stepper .= sprintf($tpl_step_done, $i, $i, $i, $i);
		} else {
			$stepper .= sprintf($tpl_step_notyet, $i, $i);
		}
	}
?>
	<table class="stepper_table">
	<tr height="20">
	<td>&nbsp;</td><?php echo $stepper; ?>
	</tr>
	</table>
	<table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
      <tr>
       <td class="tableh1" colspan="2"><h2><?php echo $install->page_title; ?></h2>
       </td>
      </tr>
       <tr>
      <td valign="top" style="background-color:#FF0000; color:#FFFFFF;"><h5>Warning, this installer is still in alpha, you can use the old one by going <a href="install_old.php">here</a></h5><br />
      </td>
    </tr>
	</table>
<?php
}

/* html_installer_locked()
 * 
 * prints the error when the installer is locked
 */
function html_installer_locked() {
	global $install;
?>
      <form action="index.php" style="margin:0px;padding:0px" name="cpgform" id="cpgform">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableh1" colspan="2"><h2><?php echo $install->language['installer_locked']; ?></h2>
          </td>
         </tr>
         <tr>
          <td class="tableh2" colspan="2" align="center"><span class="error">&#149;&nbsp;&#149;&nbsp;&#149;&nbsp;<?php echo $install->language['error']; ?>&nbsp;&#149;&nbsp;&#149;&nbsp;&#149;</span>
          </td>
         </tr>
         <tr>
          <td class="tableb" colspan="2"><?php echo $install->error; ?>
          </td>
         </tr>
         <tr>
          <td colspan="2" align="center" class="tableb"><br />
            <input type="submit" value="<?php echo $install->language['go_to_main']; ?>" /><br /><br />
          </td>
         </tr>
		</table>
      </form>
<?php
}

/* html_welcome()
 * 
 * prints the welcome message at the start of installation + language selection
 */
function html_welcome() {
    global $install;
	if(!$install->setTmpConfig('step', '2')) {
		$next_step = 1;
	} else {
		$next_step = 2;
	}
?>
      <form action="install.php?step=<?php echo $next_step; ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2"><?php echo $install->language['cpg_info']; ?></a>.
          </td>
         </tr>
		
<?php
    if ($install->error != '') {	
?>
         <tr>
          <td class="tableh2" colspan="2" align="center"><span class="error">&#149;&nbsp;&#149;&nbsp;&#149;&nbsp;<?php echo $install->language['error']; ?>&nbsp;&#149;&nbsp;&#149;&nbsp;&#149;</span>
          </td>
         </tr>
         <tr>
          <td class="tableb" colspan="2"><?php echo $install->language['error_need_corr']; ?><br /><br /><b><?php echo $install->error; ?></b>
          </td>
         </tr>
<?php
    }
    ?>
    	 <tr>
          <td class="tableh1" colspan="2"><b><?php echo $install->language['select_lang']; ?></b>
          </td>
         </tr>
         <tr>
          <td class="tableb" align="center" colspan="2"><?php echo $install->getLangSelect(); ?><input type="submit" name="update_lang" value="<?php echo $install->language['change_lang']; ?>" />
          </td>
         </tr>
		<tr>
		  <td colspan="2" align="center" class="tableh2"><br />
            <input type="submit" value="<?php echo $install->language['lets_go']; ?>" /><br /><br />
          </td>
         </tr>
	</table>
    <input type="hidden" name="javascript_check" value="failed" />
  </form>
  <!-- This code will check if javascript is enabled -->
      <script type="text/javascript">
		document.forms[0][3].value = "passed";
      </script>
	<?php
}

/* html_content()
 * 
 * prints standard page with variable content
 */
function html_content($content) {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo ($step + 1); ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2"><?php echo $content; ?><br /><br /><br /></td>
         </tr>
         <?php
		 if($install->temp_data != '') {
			echo $install->temp_data;
		 }
		 ?>
		 <tr>
		  <td colspan="2" align="center" class="tableh2"><br />
            <input type="submit" value="<?php echo $install->language['continue']; ?>" /><br /><br />
          </td>
         </tr>
		</table>
	  </form>	
<?php
}

/* html_error()
 * 
 * prints error message
 *
 * @param boolean $button
 */
function html_error($button = true) {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo $step; ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
		 <tr>
          <td class="tableh2" colspan="2" align="center"><span class="error">&#149;&nbsp;&#149;&nbsp;&#149;&nbsp;<?php echo $install->language['error']; ?>&nbsp;&#149;&nbsp;&#149;&nbsp;&#149;</span>
          </td>
         </tr>
         <tr>
          <td class="tableb" colspan="2"><?php echo $install->language['error_need_corr']; ?><br /><br /><b><?php echo $install->error; ?></b>
          </td>
         </tr>
          <?php
		  if($button){
		  	?>
            <tr>
		      <td colspan="2" align="center" class="tableh2"><br />
              <input type="submit" value="<?php echo $install->language['try_again']; ?>" /><br /><br />
              </td>
            </tr>
            <?php
		  }  
		  ?>
		</table>
	 </form>	
<?php
}

/* html_mysql_start()
 * 
 * prints page with basic MySql config
 */
function html_mysql_start() {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo ($step + 1); ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2">
		  <?php echo $install->language['sect_mysql_info']; ?><br /><br />
		  </td>
         </tr>
		 <tr>
          <td colspan="2">&nbsp;</td>
         </tr>
		 <?php 
		 	if($install->mysql_connected) {
				?>
		<tr>
			<td colspan="2" align="center"><fieldset><?php echo $install->language['mysql_succ']; ?></fieldset></td>
		</tr>	
				<?php 
			}
		 ?>
		 <tr>
          <td align="right"><?php echo $install->language['mysql_host']; ?></td>
		  <td><input type="text" name="db_host" value="<?php echo (isset($install->config['db_host'])) ? $install->config['db_host'] : 'localhost'; ?>" /></td>
         </tr>
		 <tr>
          <td align="right">MySql <?php echo $install->language['username']; ?></td>
		  <td><input type="text" name="db_user" value="<?php echo $install->config['db_user']; ?>" /></td>
         </tr>
		 <tr>
          <td align="right">MySql <?php echo $install->language['password']; ?></td>
		  <td><input type="password" name="db_password" value="<?php echo $install->config['db_password']; ?>" /></td>
         </tr>
		 <tr>
		 <td colspan="2" align="center">
		  	<input type="submit" name="update_check_connection" value="<?php echo $install->language['mysql_test_connection']; ?>" /><br />
          </td>
         </tr>
         <?php 
		 	if($install->mysql_connected) {
				?>
		<tr>
		  <td colspan="2" align="center" class="tableh2">
            <input type="submit" value="<?php echo $install->language['continue']; ?>" /><br /><br />
          </td>
         </tr>	
				<?php 
			} else {
				?>
		<tr>
		  <td colspan="2" align="center" class="tableh2">&nbsp;<br /><br /></td>
         </tr>	
				<?php 
			}
		 ?>
		 
		</table>
	  </form>	
<?php
}


/* html_mysql_select_db()
 * 
 * prints page for db selection
 */
function html_mysql_select_db() {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo ($step + 1); ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2">
		  <?php echo $install->language['sect_mysql_sel_db']; ?><br /><br />
		  </td>
         </tr>
		 <tr>
          <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
          <td align="right"><?php echo $install->language['mysql_db_name']; ?></td>
		  <td><?php echo ($dbs = $install->getMysqlDbs()) ? $dbs : '<input type="text" name="db_name" value="' . $install->config['db_name'] . '" />'; ?></td>
         </tr>
		 <tr>
          <td align="right"><?php echo $install->language['mysql_create_db']; ?></td>
		  <td><input type="text" name="new_db_name" /><input type="submit" name="update_create_db" value="<?php echo $install->language['mysql_create_btn']; ?>" /></td>
         </tr>
		 <tr>
		 <td colspan="2">&nbsp;</td>
         </tr>
         <tr>
          <td align="right"><?php echo $install->language['mysql_tbl_pref']; ?></td>
		  <td><input type="text" name="db_prefix" value="<?php echo isset($install->config['db_prefix']) ? $install->config['db_prefix'] : 'cpg15x_'; ?>" /></td>
         </tr>
		 <tr>
		  <td colspan="2" align="center" class="tableh2">
            <input type="submit" value="<?php echo $install->language['populate_db']; ?>" /><br /><br />
          </td>
         </tr>
		</table>
	  </form>	
<?php
}

/* html_admin()
 * 
 * prints page for admin details
 */
function html_admin() {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo $step; ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2">
		  <?php echo $install->language['sect_create_adm']; ?><br /><br />
		  </td>
         </tr>
		 <tr>
          <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
          <td align="right"><?php echo $install->language['username']; ?></td>
		  <td><input type="text" name="admin_username" value="<?php echo $install->config['admin_username']; ?>"  /></td>
         </tr>
         <tr>
          <td align="right"><?php echo $install->language['password']; ?></td>
		  <td><input type="password" name="admin_password" value="<?php echo $install->config['admin_password']; ?>" /></td>
         </tr>
          <tr>
          <td align="right"><?php echo $install->language['password_verif']; ?></td>
		  <td><input type="password" name="admin_password_verif" value="<?php echo $install->config['admin_password']; ?>" /></td>
         </tr>
         <tr>
          <td align="right"><?php echo $install->language['email']; ?></td>
		  <td><input type="text" name="admin_email" value="<?php echo $install->config['admin_email']; ?>" /></td>
         </tr>
          <tr>
          <td align="right"><?php echo $install->language['email_verif']; ?></td>
		  <td><input type="text" name="admin_email_verif" value="<?php echo $install->config['admin_email']; ?>" /></td>
         </tr>
		 <tr>
		 <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
		  <td colspan="2" align="center" class="tableh2">
            <input type="submit" value="<?php echo $install->language['continue'] ?>" /><br /><br />
          </td>
         </tr>
		</table>
	  </form>	
<?php
}

/* html_storage_module()
 * 
 * prints the page where the user selects the storage module
 */
function html_storage_module() {
	global $install, $step;
?>
      <form action="install.php?step=<?php echo $step; ?>" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2">
		  <?php echo $install->language['sect_storage_module']; ?><br /><br />
		  </td>
         </tr>
		 <tr>
          <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
          <td align="right"><?php echo $install->language['active_storage_module']; ?></td>
		  <td>
				<select name="storage_module">
					<?PHP
						$install = new CPGInstall();
						$storage_modules = $install->getStorageModules();
						$default_storage_module = $install->getDefaultStorageModule();
						foreach($storage_modules as $storage_module)
						{
							echo "<option value='".$storage_module."'";
							if($storage_module == $default_storage_module)
								echo " selected";
							echo ">".$storage_module."</option>\n";
						}
					?>
				</select>
         </tr>
          <tr>
          <td colspan="2" align="center"><?PHP echo $install->language['active_storage_module_extrainfo']; ?></td>
         </tr>
		 <tr>
		 <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
		  <td colspan="2" align="center" class="tableh2">
            <input type="submit" value="<?php echo $install->language['last_step'] ?>" /><br /><br />
          </td>
         </tr>
		</table>
	  </form>	
<?php
}

/* html_finish()
 * 
 * prints last page of installer
 */
function html_finish() {
	global $install;
?>
      <form action="index.php" name="cpgform" id="cpgform" method="post" style="margin:0px;padding:0px">
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="maintable">
         <tr>
          <td class="tableb" colspan="2">
		  <?php echo $install->language['ready_to_roll']; ?><br /><br />
		  </td>
         </tr>
		 <tr>
		 <td colspan="2">&nbsp;</td>
         </tr>
		 <tr>
		  <td colspan="2" align="center" class="tableh2">
            <input type="submit" value="<?php echo $install->language['finish'] ?>" /><br /><br />
          </td>
         </tr>
		</table>
	  </form>	
<?php
}

########################
#####CPGInstall Class###
########################
class CPGInstall{
	var $language;	// (array) holds the language
	var $config;	// (array) temp configuration and checks
	var $error;		// (string) holds errors
	var $tmp_config = 'include/config.tmp.php'; // (string) temporary config file
	var $mysql_connection; 			// (mysql_connection) connection to the db
	var $mysql_connected = false;	// (bool) connected to the db?
	var $page_title; // (string) holds the title of the current installation step
	var $temp_data;	// holds various data
	var $available_languages; // (array) holds available langs in first step
	
	/*
	* CPGInstall()
	*
	* Init function, loads the configuration
	*
	*/
	function CPGInstall() {
		$this->loadTempConfig();
		$this->getLanguage();
	}
	
	/*
	* loadTempConfig()
	*
	* Check if install has already been done, else load temporary config.
	* if the unserialize doesn't work, it will be tried again for 10 times before showing an error
	*
	* @param int $rp
	*/
	function loadTempConfig($rp=0) {
		if (file_exists('include/config.inc.php')) {
			$this->getLanguage();
			$this->error = '<h3>'.$this->language['already_succ'].'</h3>'.$this->language['already_succ_explain'];
			return false;
		} else {
			// read the temporary file
			if(file_exists($this->tmp_config)) {
				include($this->tmp_config);
				$this->config = $install_config;
			} else {
				$this->config = array();
			}
		}
	}
	
	/* setTmpConfig()
	 * 
	 * Adds a value to the temp config
	 *
	 * @param string $key
	 * @param string $value
	 * @param bool $isarray
	 *
	 * @return bool
	 */
	function setTmpConfig($key, $value, $isarray = false) {
		if(!$isarray) {
			$this->config[$key] = $value;
		} else {
			$this->config[$key][] = $value;
			$this->config[$key] = array_unique($this->config[$key]);
		}
		if(!$this->createTempConfig()) {
			// can't write temp config, set error
			$this->error .= '<br /><br />' . $this->language['tmp_conf_error'];
			return false;
		}
		return true;
	}
	
	/*
	* createTempConfig()
	*
	* Creates a temporary config file or appends new config vars.
	*
	* @return bool $success
	*/
	function createTempConfig() {
		if($handle = @fopen($this->tmp_config, 'w')) {
			//$config = serialize($this->config);
			//create php array in config
			$config = '<?php' . "\n" . $this->arrayToString($this->config, '$install_config') . "\n" . '?>';
			fwrite($handle, $config);
			fclose($handle);
			$success = true;
		} else {
			// could not write tmp config, add error
			$this->error = sprintf($this->language['cant_write_tmp_conf'], $tmp_config);
			$success = false;
		}
		return $success;
	}
	
	/*
	* arrayToString()
	*
	* Creates a text version of an array
	*
	* @param array $array
	* @param string $array_name
	* @param string $indent
	*
	* @return string $array_string
	*/
	function arrayToString($array, $array_name, $indent = '') {
		if(is_array($array)){
			if($indent == ''){
				$array_string = $indent . $array_name . " = array(" . "\n";
			}else{
				$array_string = $indent . "'" .  $array_name . "' => array(" . "\n";
			}
			
			foreach($array as $key => $value){
				if(is_array($value)){
					$array_string .= $this->arrayToString($value, $key, $indent . '		');
				}else{
					$array_string .= $indent . "		'$key' => '$value'," . "\n";
				}
			}
			if($indent == ''){
				$array_string .= ');' . "\n";
			}else{
				$array_string .= $indent . '),' . "\n";
			}
		}else{
			return $array;
		}
		
		return $array_string;
	}
	
	/*
	* getLanguage()
	*
	* Tries to find the default lang of the user
	*
	* @return array $language
	*/
	function getLanguage() {
		$superCage = Inspekt::makeSuperCage();
		
		// try to find the users language if we don't have one defined yet
		if(!isset($this->config['lang'])) {
			include_once('include/select_lang.inc.php');
			$this->setTmpConfig('lang', $USER['lang']);
			$this->loadTempConfig();
		}

		// change default language
		if($lang = $superCage->post->getMatched('lang_list', '/^[a-z0-9_-]+$/')) {
			$this->setTmpConfig('lang', $lang[0]);
			$this->loadTempConfig();
		} 
		if($this->language == '') {
			include('lang/english.php');
			$lang_en = $lang_install;
			$lang_en_versioncheck = $lang_versioncheck_php;
			if (file_exists('lang/' . $this->config['lang'] . '.php')) {
				// include this lang
				include('lang/' . $this->config['lang'] . '.php');
			}
			// provide fallback
			$this->language = array_merge($lang_en, $lang_install);
			$this->language['versioncheck'] = isset($lang_versioncheck_php) ? $lang_versioncheck_php : $lang_en_versioncheck;
		}
		return $this->language;
	}
	
	/*
	* getLangSelect()
	*
	* Returns a select box to choose the default labguage
	*
	* @return string $lang_select
	*/
	function getLangSelect() {
		$superCage = Inspekt::makeSuperCage();
		
		if(!is_array($this->available_languages)) {
			$dir = opendir('lang/');
			while ($file = readdir($dir)) {
				$extension = ltrim(substr($file,strrpos($file,'.')),'.');
				$filenameWithoutExtension = str_replace('.' . 'php', '', $file);
				if (is_file('lang/' . $file) && $extension == 'php') {
					$available_languages[] = $filenameWithoutExtension;
				}
			}
			closedir($dir);
			natcasesort($available_languages);
			$this->available_languages = $available_languages;
		}
		
		$lang_select = '<select name="lang_list">' . "\n";
		foreach($this->available_languages as $key => $language) {
			$lang_select .= "				    	<option " . (($this->config['lang'] == $language) ? 'selected="selected"' : '') . " value=\"{$language}\">{$language}</option>" . "\n";
		}
		$lang_select .= '				</select>';
		
		return $lang_select;
	}
	
	/*
	* checkFiles()
	*
	* Checks if all mandatory files are available via versioncheck.php
	* The returned string is the result of the versioncheck in HTML
	*
	* @return string
	*/
	function checkFiles() {
		// Set the parameters that normally get popualted by the option form
		$displayOption_array['errors_only'] = 1;
		$lang_versioncheck_php = $this->language['versioncheck'];
		require_once('include/versioncheck.inc.php');
		
		// Connect to the repository and populate the array with data from the XML file
		$file_data_array = cpgVersioncheckConnectRepository($displayOption_array);
		// Populate the array additionally with local data
		//$CONFIG['full_path'] = '';
		//$CONFIG['user_pics'] = '';
		$file_data_array = cpg_versioncheckPopulateArray($file_data_array, $displayOption_array, $textFileExtensions_array, $imageFileExtensions_array, $CONFIG, $maxLength_array, $lang_versioncheck_php);
		$file_data_count = count($file_data_array);
		// Print the results
		$outputResult = cpg_versioncheckCreateHTMLOutput($file_data_array, $textFileExtensions_array, $lang_versioncheck_php, $majorVersion, $displayOption_array);
		return sprintf($lang_versioncheck_php['files_folder_processed'], $outputResult['display'], $outputResult['total'], $outputResult['error']);
	}
	
	/*
	* checkPermissions()
	*
	* Checks if all folders have the right permissions and exist
	*
	* @return bool $peCheck
	*/
	function checkPermissions() {
		$peCheck = true;
		// If another dir has to be added, you can define as many possible permissions as you want, 
		// but if it only has to be a derictory, the use the $only_folders array (will only be checked on existance)
		// Always add the maximum required permission as the first item as the installer will try to chmod the files to that value.
		$files_to_check = array(
			'./albums'	=> array('777', '755'),
			'./include'	=> array('777', '755'),
			'./albums/userpics'	=> array('777', '755'),
			'./albums/edit'		=> array('777', '755'),
		);
		// No longer needed, will be checked in versioncheck.
		// This array is here to allow a simple check on dir existance. If it was in the other array, 
		// we should have to add all possible permissions an that would be stupid ;-)
		/*$only_folders = array(
			'./sql',
		);*/
		// clear the file status cache to make sure we are reading the most recent info of the file.
		clearstatcache();
		
		// start creating table with results
		$this->temp_data = "<tr><td align=\"center\"><table><tr><td><b>{$this->language['directory']}</b></td><td width=\"25%\"><b>{$this->language['c_mode']}</b></td><td width=\"25%\"><b>{$this->language['r_mode']}</b></td><td width=\"10%\"><b>{$this->language['status']}</b></td></tr>";
		foreach($files_to_check as $folder => $perm) {
			$possible_modes = '';
			// create a string of all allowed permissions
			foreach($perm as $p){
				$possible_modes .= " '" . $p . "' " . $this->language['or'];
			}
			// remove the last 'or ' of the string
			$possible_modes = substr($possible_modes, 0, (strlen($possible_modes) - 3));
			$not_ok = '<font color="red">' . $this->language['nok'] . '</font>';
			$_ok = '<font color="green">' . $this->language['ok'] . '</font>';
			
			// check folder existance
			if(!is_dir($folder)) {
				$peCheck = false;
				$this->error .= sprintf($this->language['subdir_called'], $folder) . '<br />';
				$this->temp_data .= "<tr><td>$folder</td><td>{$this->language['n_a']}</td><td>$possible_modes</td><td>{$not_ok}</td></tr>";
			} else {
				// try to create a file in the folder
				$test_file = $folder . '/testwritability';
				$file_handle = @fopen($test_file, 'w');
				$mode = substr(sprintf('%o', fileperms($folder)), -3);
				if(!$file_handle){
					//file could not be created, try to modify the mode
					if(@chmod($folder, (int)("0" . $perm[0]))) {
						// we have changed the mode, jippie :)
						clearstatcache();
						$mode = substr(sprintf('%o', fileperms($folder)), -3);
						// again try to write a file to the folder
						$file_handle2 = @fopen($test_file, 'w');
						if(!$file_handle2){
							//not working, admin will have to check this by hand, add error
							$peCheck = false;
							$this->error .= sprintf($this->language['perm_error'], $folder, $possible_modes) . " '" . $perm . "'.<br />";
							$this->temp_data .= "<tr><td>$folder</td><td>'$mode'</td><td>$possible_modes</td><td>{$not_ok}</td></tr>";
						}else{
							//close handle and remove file
							fclose($file_handle2);
							unlink($test_file);
							$this->temp_data .= "<tr><td>$folder</td><td>'$mode'</td><td>$possible_modes</td><td>{$_ok}</td></tr>";
						}
					} else {
						// could not change mode, add error.
						$peCheck = false;
						$this->error .= sprintf($this->language['perm_error'], $folder, $possible_modes) . " '" . $perm . "'.<br />";
						$this->temp_data .= "<tr><td>$folder</td><td>'$mode'</td><td>$possible_modes</td><td>{$not_ok}</td></tr>";
					}
				}else{
					//close file handle and remove file
					fclose($file_handle);
					unlink($test_file);
					$this->temp_data .= "<tr><td>$folder</td><td>'$mode'</td><td>$possible_modes</td><td>{$_ok}</td></tr>";
				}
			}	
		}
		/*we don't need to check the sql dir, as those files will be checked in versioncheck.
		foreach($only_folders as $folder) {
			// check folder existance
			if(!is_dir($folder)) {
				// could not detect folder
				$peCheck = false;
				$this->error .= sprintf($this->language['subdir_called'], $folder) . '<br />';
				$this->temp_data .= "<tr><td>$folder</td><td colspan=\"2\">{$this->language['no_dir']}</td><td>{$this->language['nok']}</td></tr>";
			} else {
				// folder exists
				$this->temp_data .= "<tr><td>$folder</td><td colspan=\"2\">{$this->language['dir_ok']}</td><td>{$this->language['ok']}</td></tr>";
			}
		}*/
		$this->temp_data .= '</table></td></tr>';
		return $peCheck;
	}
	
	/*
	* checkImageProcessor()
	*
	* Checks which image processors are available and tries to find IM.
	*
	* @return array $imagesProcessors
	*/
	function checkImageProcessor() {
		if($im = $this->getIM()) {
			$imagesProcessors['im'] = $im;
		}
		$gd = $this->getGDVersion();
		switch($gd) {
			case 1:
				// check basic functionality
				if($this->checkBasicGD(1)) {
					$imagesProcessors['gd1'] = 'installed';
				}
				break;
			case 2:
				// check basic functionalityµ
				if($this->checkBasicGD()) {
					$imagesProcessors['gd2'] = 'installed';
				}
				break;
			default:
				break;
		}
		return $imagesProcessors;
	}
	
	/*
	* testImageProcessor()
	*
	* Extensive test on the image processor of choise
	*
	* @return string $results
	*/
	function testImageProcessor(){
		//check which library to use
		switch($this->config['thumb_method']){
			case 'gd1':
				$image_processor = new GDtest(1);
				break;
			case 'gd2':
				$image_processor = new GDtest(2);
				break;
			case 'im':
				$image_processor = new IMtest($this->config['im_path']);
				break;
			default:
				$image_processor = new GDtest(2);
				break;
		}
		$results = $this->createImageTestResult($image_processor->testReadWrite());
		$results .= $this->createImageTestResult($image_processor->testCombineImage());
		$results .= $this->createImageTestResult($image_processor->testTextOnImage());
		$results .= $this->createImageTestResult($image_processor->testScale());
		
		return $results;
	}
	
	/**
	* getGDVersion()
	*
	* Get which version of GD is installed, if any. 
	* Returns the version (1 or 2) of the GD extension.
	* 
	* @return int $version
	*/
	function getGDVersion() {
		// check if gd is loaded
		if (!extension_loaded('gd')) {
			$version = 0; 
		} else {
			// Use the gd_info() function if possible.
			if (function_exists('gd_info')) {
				$ver_info = gd_info();
				preg_match('/\d/', $ver_info['GD Version'], $match);
				$version = $match[0];
			} else {
				// get available gd functions to determine the version
				$gd_functions = get_extension_funcs('gd');
				if(in_array('imagecreatetruecolor', $gd_functions)) {
					$version = 2;
				} elseif(in_array('imagecreate', $gd_functions)) {
					$version = 1;
				} else {
					$version = 0;
				}
			}
		}
		return $version;
	} 
	
	/*
	* checkBasicGD()
	*
	* Some basic testing if GD is working correctly.
	*
	* @param int $gd_version
	*
	* @return bool
	*/
	function checkBasicGD($gd_version = 2) {
		if($gd_version == 1) {
			$im = imagecreate(1, 1);
			$tst_image = "albums/gd1.jpg";
			imagejpeg($im, $tst_image);
			$size = $this->cpgGetimagesize($tst_image);
			@unlink($tst_image);
			if($size[2] == 2) {
				return true;
			} else {
				return false;
			}
		} else {
			$im = imagecreatetruecolor(1, 1);
			$tst_image = "albums/gd2.jpg";
			imagejpeg($im, $tst_image);
			$size = $this->cpgGetimagesize($tst_image);
			@unlink($tst_image);
			if($size[2] == 2) {
				return true;
			} else {
				return false;
			}	
		}
	}
	
	/*
	* getIM()
	*
	* Some basic testing if IM is installed & working correctly.
	*
	* @return array $im
	*/
	function getIM() {
		$im_paths = array(
			'convert',
			'/imagemagick/convert',
			'/imagemagick/bin/convert',
			'/local/bin/convert',
			'/local/bin/imagemagick/convert',
			'/local/bin/imagemagick/bin/convert',
			'/usr/local/convert',
			'/usr/local/bin/convert',
			'/usr/local/bin/imagemagick/convert',
			'/usr/local/bin/imagemagick/bin/convert',
			'/usr/bin/convert',
			'C:/Program Files/ImageMagick/convert.exe',
			'C:/ImageMagick/convert.exe',
			'/usr/bin/imagemagick/convert',
			'/usr/bin/imagemagick/bin/convert',
			'/usr/sbin/convert',
			'/bin/convert',
			'/bin/imagemagick/convert',
			'/bin/imagemagick/bin/convert'
			);
		// add trailing slash if nececary
		if (!preg_match('|[/\\\\]\Z|', $this->config['im_path']) && $this->config['im_path'] != '') {
            $this->config['im_path'] .= '/';
		}
		// add user defined path to paths array
		if($this->config['im_path'] != '') {
			// add unix version
			$im_paths[] = $this->config['im_path'] . 'convert';
			// add windows version
			$im_paths[] = '"' . $this->config['im_path'] . 'convert.exe"';
			// both versions are added so we can't make mistakes on finding which OS is used.
		}
		
		// check if IM is on default path
		foreach ($im_paths as $key => $path) {
			if(stristr($path, ':/')) {
				$path = '"' . $path . '"';
			}
			$command = "$path --version";
			// execute an im command to test if it is working
			@exec($command, $exec_output, $exec_retval);
			$version = @$exec_output[0] . @$exec_output[1];
			if($version != '') {
				// IM is found and working.
				// check for spaces in the path (we don't want those...)
				if(preg_match('/ /', $path)) {
					$path = str_replace(array('.exe', '"'), '',$path);
					$path = substr($path, 0, (strlen($path) - 7));
					$this->error = sprintf($this->language['im_path_space'], $path);
					return false;
				}
				// do a real image conversion check
				$tst_image = "albums/userpics/im.gif";
				exec ("$path images/coppermine-logo.png $tst_image", $output, $result);
				$size = $this->cpgGetimagesize($tst_image);
				@unlink($tst_image);
				$im_installed = ($size[2] == 1);
				// convert tool found, but did not work as expected
				if (!$im_installed) {
					$this->error = sprintf($this->language['im_no_convert_ex'], $path);
					return false;
				}
				// convert tool returned errors, add them to our error list
				if ($result && count($output)) {
					$this->error = $this->language['conv_said'] . '<br /></pre>';
					foreach($output as $line) $this->error .= htmlspecialchars($line);
					$this->error .= "</pre>";
					return false;
            	}
				
				// all went fine, return version info
				$im = array(
					'version' => $version, 
					'path' => $path
				);
				return $im;
				break;
			}
		}
		return false;
		
	}
	
	/*
	* checkSqlConnection()
	*
	* Tests if we can create a MySql connection
	*
	* @return bool
	*/
	function checkSqlConnection() {
		// we only need 1 connection
		if($this->mysql_connected) {
			return true;
		} else {
			(isset($this->config['db_name'])) ? $db_name = $this->config['db_name'] : $db_name = '';
			// check for MySql support of PHP
			if (!function_exists('mysql_connect')){
				$this->error = $this->language['no_mysql_support'];
				return false;
			// try to connect with given auth parameters
			} elseif (! $connect_id = @mysql_connect($this->config['db_host'], $this->config['db_user'], $this->config['db_password'])) {
				$this->error = $this->language['no_mysql_conn'] . '<br />' . $this->language['mysql_error'] . mysql_error();
				return false;
			// if a database is specified, try to select it.
			} elseif ($db_name != '') {
				if( !mysql_select_db($db_name, $connect_id)) {
					$this->error = sprintf($this->language['mysql_wrong_db'], $db_name);
					return false;
				}
			}
			// set our connection id
			$this->mysql_connection = $connect_id;
			$this->mysql_connected = true;
			return true;
		}
	}
	
	/*
	* getMysqlDbs()
	*
	* Gets all available mysql databases to create coppermine in.
	* If users doesn't have permission, it returns false.
	*
	* @return string $db_select
	*/
	function getMysqlDbs() {
		// Get a connection with the db
		if(!$this->checkSqlConnection()) {
			return false;
		}
		// get a list of db's
		if($db_list = @mysql_list_dbs($this->mysql_connection)) {
			// create dropdown box
			$db_select = '<select name="db_name">';
			while ($row = mysql_fetch_object($db_list)) {
				$db = $row->Database;
				($db == $this->config['db_name']) ? $sel = ' selected="selected"' : $sel = '';
				$db_select .= '<option name="' . $db . '"' . $sel . ' >' . $db . '</option>';
			}
			$db_select .= '</select>';
			return $db_select;
		} else {
			// probably no permission to do this.
			//$this->error = $this->language['mysql_no_sel_dbs'] . '<br />' . $this->language['mysql_error'] . '<br />' . mysql_error($this->mysql_connection);
			return false;
		}
	}
	
	/*
	* createMysqlDb()
	*
	* Tries to create CPG database.
	* If users doesn't have permission, it returns false.
	*
	* @return bool
	*/
	function createMysqlDb($db_name) {
		// Get a connection with the db
		if(!$this->checkSqlConnection()) {
			return false;
		}
		$query = 'CREATE DATABASE ' . $db_name;
		// try to create new db
		if(!mysql_query($query, $this->mysql_connection)) {
			$this->error = $this->language['mysql_no_create_db'] . '<br />' . $this->language['mysql_error'] . '<br />' . mysql_error($this->mysql_connection);
			return false;
		} else {
			$this->setTmpConfig('db_name', $db_name);
		}
		return true;
	}
	
	/*
	* populateMysqlDb()
	*
	* Executes sql file commands in db
	*
	* @return bool
	*/
	function populateMysqlDb() {
		// define some vars so we can easily find the at the top and change if needed.
		$db_schema = "sql/schema.sql";
		$db_basic = "sql/basic.sql";
		
		// check if all config values are present.
		if(!isset($this->config['thumb_method'])) 	{ $this->error = $this->language['no_thumb_method']; 	return false;}
		if (@get_magic_quotes_runtime()) set_magic_quotes_runtime(0);
		// Get a connection with the db.
		if(!$this->checkSqlConnection()) {
			return false;
		}
		// Check if we can read the db_schema file
		if (($sch_open = fopen($db_schema, 'r')) === FALSE){
			$this->error = sprintf($this->language['sql_file_not_found'], $db_schema);
			return false;
		} else {
			$sql_query = fread($sch_open, filesize($db_schema));
			// Check if we can read the db_basic file
			if (($bas_open = fopen($db_basic, 'r')) === FALSE){
				$this->error = sprintf($this->language['sql_file_not_found'], $db_basic);
				return false;
			} else {
				$sql_query .= fread($bas_open, filesize($db_basic));
			}
		}
		// Create our fantastic cage object
		$superCage = Inspekt::makeSuperCage();
		require_once('include/sql_parse.php');
		// Get gallery directory
		$possibilities = array('REDIRECT_URL', 'PHP_SELF', 'SCRIPT_URL', 'SCRIPT_NAME','SCRIPT_FILENAME');
		foreach ($possibilities as $test){
			if ($matches = $superCage->server->getMatched($test, '/([^\/]+\.php)$/')) {
				$CPG_PHP_SELF = $matches[1];
				break;
			}
		}
		
		$gallery_dir = strtr(dirname($CPG_PHP_SELF), '\\', '/');
		$gallery_url_prefix = 'http://' . $superCage->server->getEscaped('HTTP_HOST') . $gallery_dir . (substr($gallery_dir, -1) == '/' ? '' : '/');
					
		// Set configuration values for image package
		$sql_query .= "REPLACE INTO CPG_config VALUES ('thumb_method', '{$this->config['thumb_method']}');\n";
		$sql_query .= "REPLACE INTO CPG_config VALUES ('impath', '{$this->config['im_path']}');\n";
		$sql_query .= "REPLACE INTO CPG_config VALUES ('ecards_more_pic_target', '$gallery_url_prefix');\n";
		$sql_query .= "REPLACE INTO CPG_config VALUES ('gallery_admin_email', '{$this->config['admin_email']}');\n";
		// Enable silly_safe_mode if test has shown it is not configured properly
		if ($this->checkSillySafeMode()) {
			$sql_query .= "REPLACE INTO CPG_config VALUES ('silly_safe_mode', '1');\n";
		}
		// Test write permissions for main dir
		if (!is_writable('.')) {
			$sql_query .= "REPLACE INTO CPG_config VALUES ('default_dir_mode', '0777');\n";
			$sql_query .= "REPLACE INTO CPG_config VALUES ('default_file_mode', '0666');\n";
		}
		// Update table prefix
		$sql_query = preg_replace('/CPG_/', $this->config['db_prefix'], $sql_query);
	
		$sql_query = remove_remarks($sql_query);
		$sql_query = split_sql_file($sql_query, ';');
		
		$this->temp_data .= '<tr><td>';
		foreach($sql_query as $q) {
			$is_table = false;
			//check if we are creating a table so we can add it to the output
			if (preg_match('/(CREATE TABLE IF NOT EXISTS `?|CREATE TABLE `?)([\w]*)`?/i', $q, $table_match)) {
				$table = $table_match[2];
				$is_table = true;
			}
			if (! mysql_query($q, $this->mysql_connection)) {
				$this->error = $this->language['mysql_error'] . mysql_error($this->mysql_connection) . ' ' . $this->language['on_q'] . " '$q'";
				if($is_table) $this->temp_data .= "<br />" . sprintf($this->language['create_table'], $table) . '&nbsp;&nbsp;&nbsp;&nbsp;' . $this->language['status'] . ':... ' . $this->language['nok'];
				return false;
			} else {
				if($is_table) $this->temp_data .= "<br />" . sprintf($this->language['create_table'], $table). '&nbsp;&nbsp;&nbsp;&nbsp;' . $this->language['status'] . ':... ' . $this->language['ok'];
			}
		}
		$this->temp_data .= '<br /><br /><br /></td></tr>';
		return true;
	}
	
	/*
	* createAdmin()
	*
	* Creates the Coppermine admin.
	*
	* @return bool
	*/
	function createAdmin() {
		if(!isset($this->config['admin_username']) || $this->config['admin_username'] == '') { $this->error = $this->language['no_admin_username']; 	return false;}
		if(!isset($this->config['admin_password']) || $this->config['admin_password'] == '') { $this->error = $this->language['no_admin_password']; 	return false;}
		if(!isset($this->config['admin_email']) || $this->config['admin_email'] == '') 	{ $this->error = $this->language['no_admin_email']; 	return false;}
		
		// Insert the admin account
		$sql_query .= "INSERT INTO {$this->config['db_prefix']}users (user_id, user_group, user_active, user_name, user_password, user_lastvisit, user_regdate, user_group_list, user_email, user_profile1, user_profile2, user_profile3, user_profile4, user_profile5, user_profile6, user_actkey ) VALUES (1, 1, 'YES', '{$this->config['admin_username']}', md5('{$this->config['admin_password']}'), NOW(), NOW(), '', '{$this->config['admin_email']}', '', '', '', '', '', '', '');\n";
		// Get a connection with the db.
		if(!$this->checkSqlConnection()) {
			return false;
		}
		if (! mysql_query($sql_query, $this->mysql_connection)) {
			$this->error = $this->language['mysql_error'] . mysql_error($this->mysql_connection) . ' ' . $this->language['on_q'] . " '$sql_query'";
			return false;
		}
		return true;
	}
	
	/*
	* setStorageModule
	*
	* Sets the active Storage module.
	*
	* @return bool
	*/
	function setStorageModule()
	{
		if(!isset($this->config['storage_module']))
			return false;

		if(!$this->checkSqlConnection())
		{
			return false;
		}

		$query = "UPDATE ".$this->config['db_prefix']."config SET value='".mysql_real_escape_string($this->config['storage_module'])."' WHERE name='storage_module'";

		if(!mysql_query($query, $this->mysql_connection))
			return false;

		return true;
	}

	/*
	* getStorageModules()
	*
	* Returns an array with the available storage modules.
	*
	* @return bool
	*/
	function getStorageModules()
	{
		$storage_modules_dir = "storage/modules";
		$storage_modules = array();
		$dir = opendir($storage_modules_dir);
		while ($file = readdir($dir))
			if(is_dir($storage_modules_dir."/".$file) && $file[0]!=".")
				$storage_modules[] = $file;
		natcasesort($storage_modules);
		return $storage_modules;
	}

	/*
	* getDefaultStorageModule()
	*
	* Returns the name of the default storage module.
	*
	* @return bool
	*/
	function getDefaultStorageModule()
	{
		return "local_fs";
	}
	
	/*
	* checkSillySafeMode()
	*
	* Test if safe mode is misconfigured
	*
	* @return bool
	*/
	function checkSillySafeMode() {
		$test_file = "albums/userpics/dummy/dummy.txt";
		@mkdir(dirname($test_file), 0755);
		$fd = @fopen($test_file, 'w');
		if (!$fd) {
			@rmdir(dirname($test_file));
			return true;
		}
		fclose($fd);
		@unlink($test_file);
		@rmdir(dirname($test_file));
		return false;
	}
	
	/*
	* finalCheck()
	*
	* Check if everything is configured correctly
	*
	* @return array $results
	*/
	function writeConfig() {
		// this is used to prevent sc#rwing up the color coding in my editor.
		$end_php_tag = '?>';
		$config = <<<EOT
<?php
// Coppermine configuration file
// MySQL configuration
\$CONFIG['dbserver'] =                         '{$this->config['db_host']}';        // Your database server
\$CONFIG['dbuser'] =                         '{$this->config['db_user']}';        // Your mysql username
\$CONFIG['dbpass'] =                         '{$this->config['db_password']}';                // Your mysql password
\$CONFIG['dbname'] =                         '{$this->config['db_name']}';        // Your mysql database name


// MySQL TABLE NAMES PREFIX
\$CONFIG['TABLE_PREFIX'] =                '{$this->config['db_prefix']}';
$end_php_tag
EOT;
		//write config file to disk
		if ($fd = @fopen('include/config.inc.php', 'wb')) {
			fwrite($fd, $config);
			fclose($fd);
		} else {
			$this->error = '<hr /><br />' . $this->language['unable_write_config'] . '<br /><br />';
		}
	}
	
	/*
	 * function createImageTestResult()
	 *
	 * Creates a table to show the results of a test
	 *
	 * @param array $results
	 *
	 * @return string $tables
	 */
	function createImageTestResult($results){
		$tables = '';
	
		foreach($results as $test_title => $test_result){
			if(isset($test_result['error'])){
				//there was an error, show this to the user
				$result_error_tpl = <<<EOT
		<table>
			<tr>
				<th colspan="2" align="left">{$this->language[$test_title]}</th>
			</tr>
			<tr>
				<td width="200px">{$this->language[$test_result['error']]}</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>{$this->language['generated_image']}</td>
				<td>{$this->language['reference_image']}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<br /><br />
EOT;
				$tables .= $result_error_tpl;
				$this->error = $this->language['imp_test_error'];
			}else{
				//all went as it had to, put info on template
				$generated_size = $this->cpgGetimagesize($test_result['created']);
				$generated_size = $generated_size[0] . 'x' . $generated_size[1];
				$reference_size = $this->cpgGetimagesize($test_result['original']);
				$reference_size = $reference_size[0] . 'x' . $reference_size[1];
				$result_ok_tpl = <<<EOT
		<table>
			<tr>
				<th colspan="2" align="left">{$this->language[$test_title]}</th>
			</tr>
			<tr>
				<td width="200px"><img src="{$test_result['created']}" /></td>
				<td><img src="{$test_result['original']}" /></td>
			</tr>
			<tr>
				<td>{$this->language['generated_image']}</td>
				<td>{$this->language['reference_image']}</td>
			</tr>
			<tr>
				<td>$generated_size {$this->language['pixels']}</td>
				<td>$reference_size {$this->language['pixels']}</td>
			</tr>
		</table>
		<br /><br />
EOT;
				$tables .= $result_ok_tpl;
			}
		}
		
		return $tables;
	}	
	
	/*
	 * function cpgGetimagesize()
	 *
	 * Try to get the size of an image, this is custom built as some webhosts disable this function or do weird things with it
	 *
	 * @param string $image
	 * @param boolean $force_cpg_function
	 *
	 * @return array $size
	 */
	function cpgGetimagesize($image, $force_cpg_function = false){
		if (!function_exists('getimagesize') || $force_cpg_function){  
			//custom function borrowed from http://www.wischik.com/lu/programmer/get-image-size.html
			$f = @fopen($image, 'rb'); 
			if($f === false){
				return false;
			} 
			fseek($f, 0, SEEK_END); 
			$len = ftell($f);
			if ($len < 24) {
				fclose($f); 
				return false;
			}
			fseek($f, 0); 
			$buf = fread($f, 24);
			if($buf === false){
				fclose($f); 
				return false;
			}
			if(ord($buf[0]) == 255 && ord($buf[1]) == 216 && ord($buf[2]) == 255 && ord($buf[3]) == 224 && $buf[6] == 'J' && $buf[7] == 'F' && $buf[8] == 'I' && $buf[9] == 'F'){ 
				$pos=2; 
				while (ord($buf[2]) == 255){
					if (ord($buf[3]) == 192 || ord($buf[3]) == 193 || ord($buf[3]) == 194 || ord($buf[3]) == 195 || ord($buf[3]) == 201 || ord($buf[3]) == 202 || ord($buf[3]) == 203){
						break; // we've found the image frame
					}
					$pos += 2 + (ord($buf[4]) << 8) + ord($buf[5]);
					if ($pos+12>$len){
						break; // too far
					}
					fseek($f,$pos); 
					$buf = $buf[0] . $buf[1] . fread($f,12);
				}
			}
			fclose($f);
		
			// GIF:
			if($buf[0] == 'G' && $buf[1] == 'I' && $buf[2] == 'F'){
				$x = ord($buf[6]) + (ord($buf[7])<<8);
				$y = ord($buf[8]) + (ord($buf[9])<<8);
				$type = 1;
			}
		
			// JPEG:
			if(ord($buf[0]) == 255 && ord($buf[1]) == 216 && ord($buf[2]) == 255){ 
				$y = (ord($buf[7])<<8) + ord($buf[8]);
				$x = (ord($buf[9])<<8) + ord($buf[10]);
				$type = 2;
			}
		
			// PNG:
			if(ord($buf[0]) == 0x89 && $buf[1] == 'P' && $buf[2] == 'N' && $buf[3] == 'G' && ord($buf[4]) == 0x0D && ord($buf[5]) == 0x0A && ord($buf[6]) == 0x1A && ord($buf[7]) == 0x0A && $buf[12] == 'I' && $buf[13] == 'H' && $buf[14] == 'D' && $buf[15] == 'R'){
				$x = (ord($buf[16])<<24) + (ord($buf[17])<<16) + (ord($buf[18])<<8) + (ord($buf[19])<<0);
				$y = (ord($buf[20])<<24) + (ord($buf[21])<<16) + (ord($buf[22])<<8) + (ord($buf[23])<<0);
				$type = 3;
			}
		
			if (isset($x, $y, $type)){
				return false;
			}
			return array($x, $y, $type, 'height="' . $x . '" width="' . $y . '"');
		}else{
			$size = getimagesize($image);
			if(!$size){
				//false was returned
				return $this->cpgGetimagesize($image, true/*force the use of custom function*/);
			}else if(!isset($size[0]) || !isset($size[1])){
				//webhost possibly changed getimagesize functionality
				return $this->cpgGetimagesize($image, true/*force the use of custom function*/);
			}else {
				//function worked as expected, return the results
				return $size;
			}
		}

	}
}

########################
###### GDtest Class ####
########################
class GDtest{
	var $version = 1; //version of the GD lib to use
	var $image_path = 'albums/'; //path to store temp images
	
	/*
	 * function GDtest()
	 *
	 * Initializes the class with the version of GD to use.
	 *
	 * @param int $version
	 * @param string $image_path
	 */
	function GDtest($version = 1, $image_path = ''){
		$this->version = $version;
		if($image_path != ''){
			$this->image_path = $image_path;
		}
	}
	
	/*
	 * function testRead()
	 *
	 * Do basic tests on reading and writing file formats
	 *
	 * @return array $results
	 */
	function testReadWrite(){
		//create gif test image
		$test_gif = imagecreatefromgif('images/install/giftest.gif');
		if(!$test_gif){
			//put error in array
			$results['read_gif'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			if(imagegif($test_gif, $this->image_path . 'giftest_generated.gif')){
				//put results in array
				$results['read_gif'] = array(
					'original'	=> 'images/install/giftest.gif',
					'created'	=> $this->image_path . 'giftest_generated.gif',
				);
			}else {
				//put error in array
				$results['read_gif'] = array(
					'error' 	=> 'write_error',
				);
			}
		}
		@imagedestroy($test_gif);
		
		//create png test image
		$test_png = imagecreatefrompng('images/install/pngtest.png');
		if(!$test_png){
			//put error in array
				$results['read_png'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			if(imagepng($test_png, $this->image_path . 'pngtest_generated.png')){
				//put results in array
				$results['read_png'] = array(
					'original'	=> 'images/install/pngtest.png',
					'created'	=> $this->image_path . 'pngtest_generated.png'
				);
			}else{
				//put error in array
				$results['read_png'] = array(
					'error' 	=> 'write_error',
				);
				
			}
		}
		@imagedestroy($test_png);
		
		//create jpg test image
		$test_jpg = imagecreatefromjpeg('images/install/jpgtest.jpg');
		if(!$test_jpg){
			//put error in array
				$results['read_jpg'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			if(imagejpeg($test_jpg, $this->image_path . 'jpgtest_generated.jpg')){
				//put results in array
				$results['read_jpg'] = array(
					'original'	=> 'images/install/jpgtest.jpg',
					'created'	=> $this->image_path . 'jpgtest_generated.jpg',
				);
			}else {
				//put error in array
				$results['read_jpg'] = array(
					'error' 	=> 'write_error',
				);
				
			}
		}
		@imagedestroy($test_jpg);
		
		return $results;
	}
	
	/*
	 * function testCombineImage()
	 *
	 * test combining of images
	 *
	 * @return array $results
	 */
	function testCombineImage(){
		$source_a = imagecreatefromjpeg('images/install/jpgtest.jpg');
		$source_b = imagecreatefromgif('images/install/combine_b.gif');
		imagecopymerge($source_a, $source_b, 66, 1, 0, 0, imagesx($source_b), imagesy($source_b), 100);
		
		if(imagejpeg($source_a, $this->image_path . 'combined_generated.jpg')){
			//put results in array
			$results['combine'] = array(
				'original'	=> 'images/install/combined.jpg',
				'created'	=> $this->image_path . 'combined_generated.jpg',
			);
		}else {
			//put error in array
			$results['combine'] = array(
				'error' 	=> 'write_error',
			);
		}
		@imagedestroy($source_a);
		@imagedestroy($source_b);
		
		return $results;
	}
	
	/*
	 * function testTextOnImage()
	 *
	 * test adding text to images
	 *
	 * @return array $results
	 */
	function testTextOnImage(){
		$text = '2008 © Susanna Thornton';
		$font = 'images/install/LiberationSans-Regular.ttf';
		$source = imagecreatefromjpeg('images/install/jpgtest.jpg');
		$front_color = imagecolorallocate($source, 255, 255, 255);
		
		imagettftext($source, 9/*size*/, 0/*angle*/, 55/*left*/, 110/*top*/, $front_color/*color*/, $font, $text);
		if(imagejpeg($source, $this->image_path . 'texttest_generated.jpg')){
			//put results in array
			$results['text'] = array(
				'original'	=> 'images/install/texttest.jpg',
				'created'	=> $this->image_path . 'texttest_generated.jpg',
			);
		}else {
			//put error in array
			$results['text'] = array(
				'error' 	=> 'write_error',
			);
		}
		@imagedestroy($source_a);
		
		return $results;
	}
	
	/*
	 * function testScale()
	 *
	 * test scaling of images
	 *
	 * @return array $results
	 */
	 function testScale(){
	 	$image = imagecreatefromjpeg('images/install/jpgtest.jpg');
	 	if ($this->version == 2){
            $generated = @imagecreatetruecolor(100, 57);
        }else{
			$generated = imagecreate(100, 57);
		}
		$final = @imagecopyresampled($generated, $image, 0, 0, 0, 0, 100, 57, 200, 113);
		if(!$final){
			$final = @imagecopyresized($generated, $image, 0, 0, 0, 0, 100, 57, 200, 113);
		}
		imagejpeg($generated, $this->image_path . 'scaled_generated.jpg');
		
		if($final){
			//put results in array
			$results['scale'] = array(
				'original'	=> 'images/install/scaled.jpg',
				'created'	=> $this->image_path . 'scaled_generated.jpg',
			);
		}else{
			//put error in array
			$results['scale'] = array(
				'error' 	=> 'scale_error',
			);
		}
		
		return $results;
	 }
	
}

########################
###### IMtest Class ####
########################
class IMtest{
	var $image_path = 'albums/'; //path to store temp images
	var $IMpath = ''; //path to imageMagick
	var $CPGpath = ''; //path to root cpg folder
	
	/*
	 * function IMtest()
	 *
	 * Initializes the class.
	 *
	 * @param string $IMpath
	 * @param string $image_path
	 */
	function IMtest($IMpath, $image_path = ''){
		$this->IMpath = $IMpath . 'convert';
		if($image_path != ''){
			$this->image_path = $image_path;
		}
		$this->CPGpath = realpath('./');
		$this->CPGpath = str_replace('\\', '/', $this->CPGpath);
	}
	
	/*
	 * function createImagePath()
	 *
	 * Creates the path to the images, $dest is to tell
	 * if we are using the destination path 
	 *
	 * @param string $image_path
	 * @param boolean $dest
	 *
	 * @return string $path
	 */
	function createImagePath($image_path, $dest = false){
		if($dest){
			$path = $this->image_path . $image_path;
		}else{
			$path = $image_path;
		}
		return $path;
	}
	
	/*
	 * function testRead()
	 *
	 * Do basic tests on reading and writing file formats
	 *
	 * @return array $results
	 */
	function testReadWrite(){
		$output = array();
		
		//create gif test image
		$gif_command = '' . $this->IMpath . ' ' . $this->createImagePath('images/install/giftest.gif') . ' ' . $this->createImagePath('giftest_generated.jpg', true);
		exec($gif_command, $output, $retval);
		
		if($retval){
			//an error occured, add to array
			$results['read_gif'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			//all went fine
			$results['read_gif'] = array(
					'original'	=> 'images/install/giftest.gif',
					'created'	=> $this->image_path . 'giftest_generated.jpg',
				);
		}
		
		//create png test image
		$png_command = $this->IMpath . ' ' . $this->createImagePath('images/install/pngtest.png') . ' ' . $this->createImagePath('pngtest_generated.jpg', true);
		exec($png_command, $output, $retval);
		
		if($retval){
			//an error occured, add to array
			$results['read_png'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			//all went fine
			$results['read_png'] = array(
					'original'	=> 'images/install/pngtest.png',
					'created'	=> $this->image_path . 'pngtest_generated.jpg',
				);
		}
		
		//create jpg test image
		$jpg_command = $this->IMpath . ' ' . $this->createImagePath('images/install/jpgtest.jpg') . ' ' . $this->createImagePath('jpgtest_generated.jpg', true);
		exec($jpg_command, $output, $retval);
		
		if($retval){
			//an error occured, add to array
			$results['read_jpg'] = array(
					'error' 	=> 'read_error',
				);
		}else{
			//all went fine
			$results['read_jpg'] = array(
					'original'	=> 'images/install/jpgtest.jpg',
					'created'	=> $this->image_path . 'jpgtest_generated.jpg',
				);
		}

		return $results;
	}
	
	/*
	 * function testCombineImage()
	 *
	 * test combining of images
	 *
	 * @return array $results
	 */
	function testCombineImage(){
		$source_a = 'images/install/jpgtest.jpg';
		$source_b = 'images/install/combine_b.gif';
		
		$combine_command = $this->IMpath . ' ' . $this->createImagePath($source_a) . ' ' . $this->createImagePath($source_b) . ' -geometry +66+1   -composite   ' . $this->createImagePath('combined_generated.jpg', true);
		
		exec($combine_command, $output, $retval);	
		if($retval){
			//an error occured, add to array
			$results['combine'] = array(
					'error' 	=> 'combine_error',
				);
		}else{
			//all went fine
			$results['combine'] = array(
					'original'	=> 'images/install/combined.jpg',
					'created'	=> $this->image_path . 'combined_generated.jpg',
				);
		}
		
		return $results;
	}
	
	/*
	 * function testTextOnImage()
	 *
	 * test adding text to images
	 *
	 * @return array $results
	 */
	function testTextOnImage(){
		$text = '2008 © Susanna Thornton';
		$font = 'images/install/LiberationSans-Regular.ttf';
		$source = 'images/install/jpgtest.jpg';
		
		$text_command = '' . $this->IMpath . ' ' . $this->createImagePath($source) . ' -fill white -font ' . $font . ' -pointsize 12 -draw "text  50,110 \'' . $text . '\'" ' . $this->createImagePath('texttest_generated.jpg', true);  
		
		exec($text_command, $output, $retval);
		if($retval){
			//an error occured, add to array
			$results['text'] = array(
					'error' 	=> 'text_error',
				);
		}else{
			//all went fine
			$results['text'] = array(
					'original'	=> 'images/install/texttest.jpg',
					'created'	=> $this->image_path . 'texttest_generated.jpg',
				);
		}

		return $results;
	}
	
	/*
	 * function testScale()
	 *
	 * test scaling of images
	 *
	 * @return array $results
	 */
	 function testScale(){ 	
		$scale_command = $this->IMpath . ' -geometry 100x57 ' . $this->createImagePath('images/install/jpgtest.jpg') . ' ' . $this->createImagePath('scaled_generated.jpg', true);
		exec($scale_command, $output, $retval);
		
		if($retval){
			//put error in array
			$results['scale'] = array(
				'error' 	=> 'scale_error',
			);
		}else{
			//put results in array
			$results['scale'] = array(
				'original'	=> 'images/install/scaled.jpg',
				'created'	=> $this->image_path . 'scaled_generated.jpg',
			);
		}
		
		return $results;
	 }
	
}

?>
