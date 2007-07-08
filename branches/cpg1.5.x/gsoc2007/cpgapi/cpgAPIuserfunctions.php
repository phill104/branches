<?php
/*
 * PHP library for all user related functions in CPG
 * @author xnitingupta
 */

/**
 * Class for user related functions
 */
class userfunctions {

  /* Login function. Authenticates password.
   * @ username
   * @ password
   * @ return USER_DATA on success, false on failure
   */
  function login ($username, $password) {
    global $DBS;

    $encpassword = md5($password);
    // Check for user in users table
    $sql =  "SELECT {$DBS->field['user_id']}, {$DBS->field['username']}, {$DBS->field['active']} FROM {$DBS->usertable}";
    $sql .= " WHERE {$DBS->field['username']} = '$username' AND BINARY {$DBS->field['password']} = '$encpassword'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       if (mysql_result($results, 0, $DBS->field['active']) == "YES") {
          $USER_DATA = mysql_fetch_assoc($results);
          mysql_free_result($results);
          return $USER_DATA;
       }  else {
          mysql_free_result($results);
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "not_active";
          return $USER_DATA;
       }
    }  else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "login_error";
       return $USER_DATA;
    }
  }

  /* Logout function. Resets the session key.
   * @ username
   */
  function logout ($username) {
    global $DBS, $CF;
    $sql = "UPDATE {$DBS->usertable} SET {$DBS->field['sessionkey']}='' WHERE {$DBS->field['username']} = '$username'";
    $DBS->sql_update($sql);
  }

  /* Login confirm function. Sets the session key and the last visit time.
   * @ username
   * @ return sessionkey
   */
  function setlastvisit ($username) {
    global $DBS, $CF;
    $sessionkey = $CF->str_makerand(15,25, true, false, true);
    $sql = "UPDATE {$DBS->usertable} SET {$DBS->field['lastvisit']}=NOW(), {$DBS->field['sessionkey']}='{$sessionkey}' WHERE {$DBS->field['username']} = '$username'";
    $DBS->sql_update($sql);
    return $sessionkey;
  }

  /* Fetches and returns the most recent session key corresponding to a single user.
   * @ username
   * @ return sessionkey
   */
  function getsessionkey ($username) {
    global $DBS;

    $sql =  "SELECT {$DBS->field['sessionkey']} FROM {$DBS->usertable} WHERE {$DBS->field['username']} = '$username'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       $sessionkey = mysql_result($results, 0, $DBS->field['sessionkey']);
       mysql_free_result($results);
       return $sessionkey;
    }  else {
       return '';
    }
  }

  /* Shows the data corresponding to a single user.
   * @ USER_DATA
   */
  function showdata ($USER_DATA) {
    global $DISPLAY;

    print "<userdata>\n";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       print "<" . $DISPLAY->userpersonalfields[$i] . ">\n";
       print $USER_DATA[$DISPLAY->userpersonalfields[$i]];
       print "</" . $DISPLAY->userpersonalfields[$i] . ">\n";
    }
    if($USER_DATA['sessionkey']!="")
      print "<sessionkey>" . $USER_DATA['sessionkey'] . "</sessionkey>\n";

    $GROUP_DATA = $this->getgroupdata($USER_DATA['user_id']);
    for($j = 0; $j < count($GROUP_DATA); $j++) {
       print "<group>\n";
       for($i=0;$i<count($DISPLAY->groupfields);$i++) {
          print "<" . $DISPLAY->groupfields[$i] . ">\n";
          print $GROUP_DATA[$j][$DISPLAY->groupfields[$i]];
          print "</" . $DISPLAY->groupfields[$i] . ">\n";
       }
       print "</group>\n";
    }
    print "</userdata>\n";
  }

  /* Fetches and returns the data corresponding to a single user.
   * @ username
   * @ return USER_DATA
   */
  function getpersonaldata ($username) {
    global $DBS, $DISPLAY;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->field[$DISPLAY->userpersonalfields[$i]]} AS {$DISPLAY->userpersonalfields[$i]}";
    }

    $sql =  "SELECT $fieldstring FROM {$DBS->usertable}";
    $sql .= " WHERE {$DBS->field['username']} = '$username'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       $USER_DATA = mysql_fetch_assoc($results);
       mysql_free_result($results);
       return $USER_DATA;
    }  else {
       return false;
    }
  }

  /* Authorizes the user. Checks for a valid session key and permissions for the operations being performed
   * @ username
     @ sessionkey
     @ perm
     @ return true if authorized, otherwise exits immediately
   */
  function authorizeuser($username, $sessionkey, $perm) {
    global $CF;

    $userkey = $this->getsessionkey($username);
    if($userkey=='' || $userkey!=$sessionkey) {
       print "<messagecode>invalid_session_error</messagecode>";
       $CF->safeexit();
    }

   if ($perm!="login") {
      /* Check if there is a current user and if she has appropriate permissions */
      $USER_DATA = $this->getpersonaldata($username);

      if ($USER_DATA) {
         $GROUP_DATA = $this->getgroupdata($USER_DATA['user_id']);
         if ($GROUP_DATA) {
            $authorized = false;
            for($i = 0; $i < count($GROUP_DATA); $i++) {
               if($GROUP_DATA[$i][$perm]) {
                  $authorized = true;
                  break;
               }
            }

            if ($authorized) {
               return true;
            }  else {
               print "<messagecode>query_permission_error</messagecode>\n";
               $CF->safeexit();
            }
         }  else {
            print "<messagecode>user_data_invalid_error</messagecode>\n";
            $CF->safeexit();
         }
      }  else  {
         print "<messagecode>user_data_invalid_error</messagecode>\n";
         $CF->safeexit();
      }
    }
    return true;
  }

  /* Fetches and returns the group data corresponding to a single user.
   * @ userid
   * @ return GROUP_DATA[]
   */
  function getgroupdata ($user_id) {
    global $DBS, $DISPLAY;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->groupfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->group[$DISPLAY->groupfields[$i]]} AS {$DISPLAY->groupfields[$i]}";
    }

    $res = array();
    $sql = "SELECT {$DBS->userxgroup['group_id']} FROM {$DBS->userxgrouptable} WHERE {$DBS->userxgroup['user_id']} = '{$user_id}'";
    $results = $DBS->sql_query($sql);
    for($i = 0; $i < mysql_numrows($results); $i++) {
      $isql =  "SELECT $fieldstring FROM {$DBS->groupstable}";
      $isql .= " WHERE {$DBS->group['group_id']}=" . mysql_result($results, $i, $DBS->userxgroup['group_id']);
      $iresult = $DBS->sql_query($isql);
      if (mysql_num_rows($iresult)) {
         $res[$i] = mysql_fetch_assoc($iresult);
         mysql_free_result($iresult);
      }
    }
    mysql_free_result($results);
    return $res;
  }

  /* Fetches and returns the group data corresponding to a single group.
   * @ grop_id
   * @ return GROUP_DATA
   */
  function getsinglegroupdata ($group_id) {
    global $DBS, $DISPLAY;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->groupfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->group[$DISPLAY->groupfields[$i]]} AS {$DISPLAY->groupfields[$i]}";
    }

    $isql =  "SELECT $fieldstring FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}={$group_id}";
    $iresult = $DBS->sql_query($isql);
    if (mysql_num_rows($iresult)) {
       $res = mysql_fetch_assoc($iresult);
       mysql_free_result($iresult);
    }
    return $res;
  }

  /* Fetches and returns the data corresponding to all users.
   * @ return USER_DATA[]
   */
  function getalluserdata () {
    global $DBS, $DISPLAY;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->field[$DISPLAY->userpersonalfields[$i]]} AS {$DISPLAY->userpersonalfields[$i]}";
    }

    $sql =  "SELECT $fieldstring FROM {$DBS->usertable}";
    $results = $DBS->sql_query($sql);

    $res = array();
    for ($i = 0; $i < mysql_numrows($results); $i++) {
       $res[$i] = mysql_fetch_assoc($results);
    }
    mysql_free_result($results);
    return $res;
  }

  /* Fetches and returns the data corresponding to all groups.
   * @ return GROUP_DATA[]
   */
  function getallgroupdata () {
    global $DBS, $DISPLAY;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->groupfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->group[$DISPLAY->groupfields[$i]]} AS {$DISPLAY->groupfields[$i]}";
    }

    $sql =  "SELECT $fieldstring FROM {$DBS->groupstable}";
    $results = $DBS->sql_query($sql);
    $res = array();
    for ($i = 0; $i < mysql_numrows($results); $i++) {
       $res[$i] = mysql_fetch_assoc($results);
    }
    mysql_free_result($results);
    return $res;
  }

  /* Function to add a new user to the database.
   * @ username
   * @ password
   * @ group_id
   * @ email
   * @ active
   * @ profile[]
   * @ return USER_DATA
   */
  function adduser ($addusername, $password, $group_id, $email, $profile) {
    global $DBS, $DISPLAY, $CONFIG, $CF, $LANG;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->field[$DISPLAY->userpersonalfields[$i]]} AS {$DISPLAY->userpersonalfields[$i]}";
    }

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       mysql_free_result($results);
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "duplicate_username";
       return $USER_DATA;
    }

    // Check for duplicate email addresses
    if ($CONFIG['allow_duplicate_emails_addr'] == "0") {
       $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['email']}='" . $email . "'";
       $results = $DBS->sql_query($sql);
       if (mysql_num_rows($results)) {
          mysql_free_result($results);
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "duplicate_email";
          return $USER_DATA;
       }
    }

    $act_key = $CF->str_makerand(15,25, true, false, true);

    $active = "YES";
    if ($CONFIG['reg_requires_valid_email']) {
        if (!$CONFIG['admin_activation']==1) {
           $act_link = rtrim($CONFIG['site_url'], '/') . '/index.htm?pg=activatediv&username=' . $addusername . 'key=' . $act_key;
           $template_vars = array(
               '{SITE_NAME}' => $CONFIG['gallery_name'],
               '{USER_NAME}' => $username,
               '{ACT_LINK}' => $act_link
            );
            if (!cpg_mail($email, sprintf($LANG['register']['confirm_email_subject'], $CONFIG['gallery_name']), nl2br(strtr($LANG['register_confirm_email'], $template_vars)))) {
               $USER_DATA = array();
               $USER_DATA['error'] = true;
               $USER_DATA['messagecode'] = "failed_sending_email";
               return $USER_DATA;
            }
            $active = "NO";
        }
    }

    if ($CONFIG['admin_activation']==1) {
       $active = "NO";
    }

    // email notification to admin
    if ($CONFIG['reg_notify_admin_email']) {
       if ($CONFIG['admin_activation']==1) {
          $act_link = rtrim($CONFIG['site_url'], '/') . '/index.htm?pg=activatediv&username=' . $addusername . '&act_key=' . $act_key;
          $template_vars = array(
             '{SITE_NAME}' => $CONFIG['gallery_name'],
             '{USER_NAME}' => $user_name,
             '{ACT_LINK}' => $act_link,
          );
          cpg_mail('admin', sprintf($LANG['register']['notify_admin_request_email_subject'], $CONFIG['gallery_name']), nl2br(strtr($LANG['register_approve_email'], $template_vars)));
       }  else {
          cpg_mail('admin', sprintf($LANG['register']['notify_admin_email_subject'], $CONFIG['gallery_name']), sprintf($LANG['register']['notify_admin_email_body'], $user_name));
       }
    }

    $sql =  "INSERT INTO {$DBS->usertable} ({$DBS->field['username']},{$DBS->field['password']},{$DBS->field['active']},{$DBS->field['email']},{$DBS->field['regdate']},{$DBS->field['lastvisit']},{$DBS->field['act_key']},{$DBS->field['profile1']},{$DBS->field['profile2']},{$DBS->field['profile3']},{$DBS->field['profile4']},{$DBS->field['profile5']},{$DBS->field['profile6']}) VALUES ('{$addusername}', md5('{$password}'), '{$active}', '{$email}', NOW(), NOW(),'{$act_key}','{$profile[1]}','{$profile[2]}','{$profile[3]}','{$profile[4]}','{$profile[5]}','{$profile[6]}')";
    $DBS->sql_update($sql);

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $adduserid = mysql_result($results, 0, $DBS->field['user_id']);
       mysql_free_result($results);
    }

    $sql =  "INSERT INTO {$DBS->userxgrouptable} ({$DBS->userxgroup['user_id']},{$DBS->userxgroup['group_id']}) VALUES ('{$adduserid}', '{$group_id}')";
    $DBS->sql_update($sql);

    return $this->getpersonaldata($addusername);
  }


  /* Function to modify the profile of a user
   * @ username
   * @ password
   * @ email
   * @ profile[]
   * @ return USER_DATA
   */
  function modifyprofile ($addusername, $password, $email, $profile) {
    global $DBS, $DISPLAY, $CONFIG, $CF, $LANG;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->field[$DISPLAY->userpersonalfields[$i]]} AS {$DISPLAY->userpersonalfields[$i]}";
    }

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (!mysql_num_rows($results)) {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "username_not_exist";
       return $USER_DATA;
    }  else {
       $oldemail = mysql_result($results, 0, $DBS->field['email']);
       mysql_free_result($results);
    }

    // Check for duplicate email addresses
    if ($CONFIG['allow_duplicate_emails_addr'] == "0" && $oldemail!=$email) {
       $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['email']}='" . $email . "'";
       $results = $DBS->sql_query($sql);
       if (mysql_num_rows($results)) {
          mysql_free_result($results);
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "duplicate_email";
          return $USER_DATA;
       }
    }

    $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['email']}='{$email}', {$DBS->field['profile1']}='{$profile[1]}', {$DBS->field['profile2']}='{$profile[2]}', {$DBS->field['profile3']}='{$profile[3]}', {$DBS->field['profile4']}='{$profile[4]}', {$DBS->field['profile5']}='{$profile[5]}', {$DBS->field['profile6']}='{$profile[6]}' WHERE {$DBS->field['username']}='{$addusername}'";
    $DBS->sql_update($sql);

    if ($password!="") {
       $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['password']}=md5('{$password}') WHERE {$DBS->field['username']}='{$addusername}'";
       $DBS->sql_update($sql);
    }

    return $this->getpersonaldata($addusername);
  }

  /* Function to update a user
   * @ addusername
   * @ password
   * @ email
   * @ active
   * @ return USER_DATA
   */
  function updateuser ($addusername, $password, $email, $active) {
    global $DBS, $DISPLAY, $CONFIG, $CF, $LANG;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->userpersonalfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->field[$DISPLAY->userpersonalfields[$i]]} AS {$DISPLAY->userpersonalfields[$i]}";
    }

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (!mysql_num_rows($results)) {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "username_not_exist";
       return $USER_DATA;
    }  else {
       $oldemail = mysql_result($results, 0, $DBS->field['email']);
       mysql_free_result($results);
    }

    // Check for duplicate email addresses
    if ($CONFIG['allow_duplicate_emails_addr'] == "0" && $oldemail!=$email) {
       $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['email']}='" . $email . "'";
       $results = $DBS->sql_query($sql);
       if (mysql_num_rows($results)) {
          mysql_free_result($results);
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "duplicate_email";
          return $USER_DATA;
       }
    }

    $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['email']}='{$email}', {$DBS->field['active']}='{$active}' WHERE {$DBS->field['username']}='{$addusername}'";
    $DBS->sql_update($sql);

    if ($password!="") {
       $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['password']}=md5('{$password}') WHERE {$DBS->field['username']}='{$addusername}'";
       $DBS->sql_update($sql);
    }

    return $this->getpersonaldata($addusername);
  }


  /* Activation function.
   * @ addusername
   * @ act_key
   * @ return USER_DATA on success, false on failure
   */
  function activate ($addusername, $act_key) {
    global $CF, $DBS;

    // Check for user in users table
    $sql =  "SELECT {$DBS->field['user_id']}, {$DBS->field['username']} FROM {$DBS->usertable}";
    $sql .= " WHERE {$DBS->field['username']} = '{$addusername}' AND {$DBS->field['act_key']} = '{$act_key}'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       $act_key = $CF->str_makerand(15,25, true, false, true);
       $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['active']}='YES', {$DBS->field['act_key']}={$act_key} WHERE {$DBS->field['username']}='{$addusername}'";
       $DBS->sql_update($sql);
       return $this->getpersonaldata($addusername);
    }  else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "activation_error";
       return $USER_DATA;
    }
  }

  /* Forgot Password
   * @ addusername
   * @ email
   * @ return true on success, false on failure
   */
  function forgotpassword ($addusername, $email) {
    global $DBS, $CF;

    // Check for user in users table
    $sql =  "SELECT {$DBS->field['user_id']}, {$DBS->field['username']} FROM {$DBS->usertable}";
    $sql .= " WHERE {$DBS->field['username']} = '{$addusername}' AND {$DBS->field['email']} = '{$email}'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       $pass_key = $CF->str_makerand(15,25, true, false, true);
       $pass_link = rtrim($CONFIG['site_url'], '/') . '/index.htm#pg=generatepassworddiv&username=' . $addusername . '&pass_key=' . $pass_key;

       if (!cpg_mail($email, sprintf($LANG['forgot_passwd']['account_verify_subject'], $CONFIG['gallery_name']), sprintf($LANG['forgot_passwd']['account_verify_body'], $pass_link)))  {
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "failed_sending_email";
          return $USER_DATA;
       }

       $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['act_key']}='{$pass_key}' WHERE {$DBS->field['username']}='{$addusername}'";
       $DBS->sql_update($sql);
       return $this->getpersonaldata($addusername);
    }  else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "forgotpassword_error";
       return $USER_DATA;
    }
  }

  /* Function to generate new password
   * @ addusername
   * @ pass_key
   * @ return USER_DATA on success, false on failure
   */
  function generatepassword ($addusername, $pass_key) {
    global $DBS, $CF;

    // Check for user in users table
    $sql =  "SELECT {$DBS->field['user_id']}, {$DBS->field['username']}, {$DBS->field['email']} FROM {$DBS->usertable}";
    $sql .= " WHERE {$DBS->field['username']} = '{$addusername}' AND {$DBS->field['act_key']} = '{$pass_key}'";
    $results = $DBS->sql_query($sql);

    if (mysql_num_rows($results)) {
       $password = $CF->str_makerand(8,12, true, false, true);
       $pass_key = $CF->str_makerand(15,25, true, false, true);
       $email = mysql_result($results, 0, $DBS->field['email']);
       $login_link = rtrim($CONFIG['site_url'], '/') . '/index.htm#pg=logindiv';

       if (!cpg_mail($email, sprintf($LANG['forgot_passwd']['passwd_reset_subject'], $CONFIG['gallery_name']), sprintf($LANG['forgot_passwd']['passwd_reset_body'], $addusername, $password, $login_link))) {
          $USER_DATA = array();
          $USER_DATA['error'] = true;
          $USER_DATA['messagecode'] = "failed_sending_email";
          return $USER_DATA;
       }

       $sql =  "UPDATE {$DBS->usertable} SET {$DBS->field['password']}= md5('{$password}'), {$DBS->field['act_key']}='{$pass_key}' WHERE {$DBS->field['username']}='{$addusername}'";
       $DBS->sql_update($sql);
       return $this->getpersonaldata($addusername);
    }  else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "forgotpassword_error";
       return $USER_DATA;
    }
  }

  /* Function to remove an existing user from the database.
   * @ username
   * @ return USER_DATA if user exists, false if user does not exist
   */
  function removeuser ($addusername) {
    global $DBS, $DISPLAY;

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $adduserid = mysql_result($results, 0, $DBS->field['user_id']);
       mysql_free_result($results);
    } else {
       return false;
    }

    $res = $this->getpersonaldata($addusername);
    $sql =  "DELETE FROM {$DBS->usertable} WHERE {$DBS->field['username']}='{$addusername}'";
    $DBS->sql_update($sql);
    $sql =  "DELETE FROM {$DBS->userxgrouptable} WHERE {$DBS->userxgroup['user_id']}='{$adduserid}'";
    $DBS->sql_update($sql);

    return $res;
  }


  /* Function to add a new group to the database.
   * @ groupname
   * @ admin
   * @ return GROUP_DATA
   */
  function addgroup ($groupname, $admin) {
    global $DBS, $DISPLAY;

    if($admin=="YES" || $admin=="1") $admin = 1;
    else $admin = 0;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->groupfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->group[$DISPLAY->groupfields[$i]]} AS {$DISPLAY->groupfields[$i]}";
    }

    $sql =  "SELECT $fieldstring FROM {$DBS->groupstable} WHERE {$DBS->group['groupname']}='{$groupname}'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       mysql_free_result($results);
       $GROUP_DATA = array();
       $GROUP_DATA['error'] = true;
       $GROUP_DATA['messagecode'] = "duplicate_groupname";
       return $GROUP_DATA;
    }

    $sql =  "INSERT INTO {$DBS->groupstable} ({$DBS->group['groupname']},{$DBS->group['admin']}) VALUES ('{$groupname}', '{$admin}')";
    $DBS->sql_update($sql);

    $sql = "SELECT * FROM {$DBS->groupstable} WHERE {$DBS->group['groupname']}='" . $groupname . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $group_id = mysql_result($results, 0, $DBS->group['group_id']);
       mysql_free_result($results);
    }

    return $this->getsinglegroupdata($group_id);
  }

  /* Function to update a group in the database.
   * @ group_id
   * @ groupname
   * @ admin
   * @ return GROUP_DATA
   */
  function updategroup ($group_id, $groupname, $admin) {
    global $DBS, $DISPLAY;

    if($admin=="YES" || $admin=="1") $admin = 1;
    else $admin = 0;

    $fieldstring = "";
    for($i=0;$i<count($DISPLAY->groupfields);$i++) {
       if($i!=0) $fieldstring .= ", ";
       $fieldstring .= "{$DBS->group[$DISPLAY->groupfields[$i]]} AS {$DISPLAY->groupfields[$i]}";
    }

    $sql =  "SELECT * FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}='{$group_id}'";
    $results = $DBS->sql_query($sql);
    if (!mysql_num_rows($results)) {
       $GROUP_DATA = array();
       $GROUP_DATA['error'] = true;
       $GROUP_DATA['messagecode'] = "group_not_exist";
       return $GROUP_DATA;
    }  else {
       $oldname = mysql_result($results, 0, $DBS->group['groupname']);
       mysql_free_result($results);
    }

    if ($oldname!=$groupname) {
       $sql =  "SELECT $fieldstring FROM {$DBS->groupstable} WHERE {$DBS->group['groupname']}='{$groupname}'";
       $results = $DBS->sql_query($sql);
       if (mysql_num_rows($results)) {
          mysql_free_result($results);
          $GROUP_DATA = array();
          $GROUP_DATA['error'] = true;
          $GROUP_DATA['messagecode'] = "duplicate_groupname";
          return $GROUP_DATA;
       }
    }

    $sql =  "UPDATE {$DBS->groupstable} SET {$DBS->group['groupname']}='{$groupname}', {$DBS->group['admin']}='{$admin}' WHERE {$DBS->group['group_id']}='{$group_id}'";
    $DBS->sql_update($sql);

    return $this->getsinglegroupdata($group_id);
  }

  /* Function to remove an existing group from the database.
   * @ group_id
   * @ return GROUP_DATA if group exists, false if group does not exist
   */
  function removegroup ($group_id) {
    global $DBS, $DISPLAY;

    $sql = "SELECT * FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}='" . $group_id . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $group_id = mysql_result($results, 0, $DBS->group['group_id']);
       mysql_free_result($results);
    } else {
       return false;
    }

    $res = $this->getsinglegroupdata($group_id);
    $sql =  "DELETE FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}='{$group_id}'";
    $DBS->sql_update($sql);
    $sql =  "DELETE FROM {$DBS->userxgrouptable} WHERE {$DBS->userxgroup['group_id']}='{$group_id}'";
    $DBS->sql_update($sql);

    return $res;
  }

  /* Function to add a user to a group in the database.
   * @ username
   * @ group_id
   * @ return USER_DATA if user and group exist, false otherwise
   */
  function addusertogroup ($addusername, $group_id) {
    global $DBS, $DISPLAY;

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $adduserid = mysql_result($results, 0, $DBS->field['user_id']);
       mysql_free_result($results);
    } else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "username_not_exist";
       return $USER_DATA;
    }

    $sql = "SELECT * FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}='" . $group_id . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       mysql_free_result($results);
    } else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "group_not_exist";
       return $USER_DATA;
    }

    $sql =  "INSERT INTO {$DBS->userxgrouptable} ({$DBS->userxgroup['user_id']},{$DBS->userxgroup['group_id']}) VALUES ('{$adduserid}', '{$group_id}')";
    $DBS->sql_update($sql);

    return $this->getpersonaldata($addusername);
  }

  /* Function to remove a user from a group in the database.
   * @ username
   * @ group_id
   * @ return USER_DATA if user and group exist, false otherwise
   */
  function removeuserfromgroup ($addusername, $group_id) {
    global $DBS, $DISPLAY;

    $sql = "SELECT * FROM {$DBS->usertable} WHERE {$DBS->field['username']}='" . $addusername . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       $adduserid = mysql_result($results, 0, $DBS->field['user_id']);
       mysql_free_result($results);
    } else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "username_not_exist";
       return $USER_DATA;
    }

    $sql = "SELECT * FROM {$DBS->groupstable} WHERE {$DBS->group['group_id']}='" . $group_id . "'";
    $results = $DBS->sql_query($sql);
    if (mysql_num_rows($results)) {
       mysql_free_result($results);
    } else {
       $USER_DATA = array();
       $USER_DATA['error'] = true;
       $USER_DATA['messagecode'] = "group_not_exist";
       return $USER_DATA;
    }

    $sql =  "DELETE FROM {$DBS->userxgrouptable} WHERE {$DBS->userxgroup['user_id']}='{$adduserid}' AND {$DBS->userxgroup['group_id']}='{$group_id}'";
    $DBS->sql_update($sql);

    return $this->getpersonaldata($addusername);
  }

}

?>