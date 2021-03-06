<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2007 Coppermine Dev Team
  v0.1 originally written by Nitin Gupta

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.5.0
  $HeadURL$
  $Revision: 2 $
  $LastChangedBy: xnitingupta $
  $Date: 6:01 PM 6/1/2007 $
**********************************************/

/**
 * Class specifying everthing about the database structure
 */
class dbspecs {
  var $db, $table, $usertable, $groupstable, $configtable, $field, $group, $sessiontable, $sessionfield;
  var $categorytable, $catfield, $albumtable, $albumfield, $picturetable, $picturefield, $filetypetable, $filetypefield;
  var $commentstable, $commentsfield, $hitstatstable, $hitstatsfield, $votestable, $votesfield, $votestatstable, $votestatsfield;
  var $dbactive = false;

  function initialize() {
    // Database connection settings
    global $CONFIG;

    $this->db = array(
        'name' => $CONFIG['dbname'],
        'host' => $CONFIG['dbserver'] ? $CONFIG['dbserver'] : 'localhost',
        'user' => $CONFIG['dbuser'],
        'password' => $CONFIG['dbpass'],
        'prefix' =>$CONFIG['TABLE_PREFIX']
    );

    // Board table names
    $this->table = array(
        'users' => 'users',
        'sessions' => 'sessions',
        'groups' => 'usergroups',
        'config' => 'config',
        'categories' => 'categories',
        'albums' => 'albums',
        'pictures' => 'pictures',
        'filetypes' => 'filetypes',
        'comments' => 'comments',
        'hitstats' => 'hit_stats',
        'votes' => 'votes',
        'votestats' => 'vote_stats'
    );
	
    // Derived full table names
    $this->usertable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['users'];
    $this->sessiontable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['sessions'];
    $this->groupstable =  '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['groups'];
    $this->configtable =  '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['config'];
    $this->categorytable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['categories'];
    $this->albumtable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['albums'];
    $this->picturetable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['pictures'];
    $this->filetypetable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['filetypes'];
	$this->commentstable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['comments'];
	$this->hitstatstable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['hitstats']; 
	$this->votestable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['votes']; 
	$this->votestatstable = '`' . $this->db['name'] . '`.' . $this->db['prefix'] . $this->table['votestats']; 
	
    // Table field names
    $this->field = array(
        'username' => 'user_name', // name of 'username' field in users table
        'user_id' => 'user_id', // name of 'id' field in users table
        'user_group' => 'user_group',
        'password' => 'user_password', // name of the password field in the users table
        'email' => 'user_email', // name of 'email' field in users table
        'regdate' => 'user_regdate', // name of 'registered' field in users table
        'group_list' => 'user_group_list',
        'lastvisit' => 'user_lastvisit', // last time user logged in
        'active' => 'user_active', // is user account active?
        'act_key' => 'user_actkey', // user activation key
        'profile1' => 'user_profile1',
        'profile2' => 'user_profile2',
        'profile3' => 'user_profile3',
        'profile4' => 'user_profile4',
        'profile5' => 'user_profile5',
        'profile6' => 'user_profile6'
    );

   // Table field names
    $this->sessionfield = array(
        'sessionkey' => 'session_id',
        'user_id' => 'user_id'
    );

    // Group field names
    $this->group = array(
        'groupname' => 'group_name',
        'group_id' => 'group_id',
        'admin' => 'has_admin_access',
        'can_rate_pictures' => 'can_rate_pictures',
        'can_send_ecards' => 'can_send_ecards',
        'can_post_comments' => 'can_post_comments',
        'can_upload_pictures' => 'can_upload_pictures',
        'can_create_albums' => 'can_create_albums',
        'pub_upload' => 'pub_upl_need_approval',
        'prv_upload' => 'prv_upl_need_approval',
        'upload_form_config' => 'upload_form_config',
        'custom_user_upload' => 'custom_user_upload',
        'num_file_upload' => 'num_file_upload',
        'num_URI_upload' => 'num_URI_upload'
    );
    
    $this->catfield = array(
    	'cid' => 'cid',
    	'ownerid' => 'owner_id',
    	'name' => 'name',
    	'description' => 'description',
    	'parent' => 'parent',
    	'pos' => 'pos',
    	'thumb' => 'thumb'
    );
    
    $this->albumfield = array(
    	'aid' => 'aid',
    	'title' => 'title',
    	'description' => 'description',
    	'visibility' => 'visibility',
    	'uploads' => 'uploads',
    	'comments' => 'comments',
    	'votes' => 'votes',
    	'pos' => 'pos',
    	'category' => 'category',
    	'thumb' => 'thumb',
    	'keyword' => 'keyword',
    	'alb_password' => 'alb_password',
    	'alb_password_hint' => 'alb_password_hint',
    	'moderator_group' => 'moderator_group',
    	'alb_hits' => 'alb_hits'
    );
    
    $this->picturefield = array(
    	'pid' => 'pid',
    	'aid' => 'aid',
    	'filepath' => 'filepath',
    	'filename' => 'filename',
    	'filesize' => 'filesize',
    	'total_filesize' => 'total_filesize',
    	'pwidth' => 'pwidth',
    	'pheight' => 'pheight',
    	'hits' => 'hits',
    	'mtime' => 'mtime',
    	'ctime' => 'ctime',
    	'ownerid' => 'owner_id',
    	'ownername' => 'owner_name',
    	'pic_rating' => 'pic_rating',
    	'votes' => 'votes',
    	'title' => 'title',
    	'caption' => 'caption',
    	'keywords' => 'keywords',
    	'approved' => 'approved',
    	'galleryicon' => 'galleryicon',
    	'user1' => 'user1',
    	'user2' => 'user2',
    	'user3' => 'user3',
    	'user4' => 'user4',
    	'url_prefix' => 'url_prefix',
    	'pic_raw_ip' => 'pic_raw_ip',
    	'pic_hdr_ip' => 'pic_hdr_ip',
    	'lasthit_ip' => 'lasthit_ip',
    	'pos' => 'position'
    );
    
    $this->filetypefield = array(
    	'extension' => 'extension',
    	'mime' => 'mime',
    	'content' => 'content',
    	'player' => 'player'
    );
    
    $this->commentsfield = array(
    	'pid' => 'pid',
		'msgid' => 'msg_id',
		'msgauthor' => 'msg_author',
		'msgbody' => 'msg_body',
		'msgdate' => 'msg_date',
		'msg_raw_ip' => 'msg_raw_ip',
		'msg_hdr_ip' => 'msg_hdr_ip',
		'author_md5_id' => 'author_md5_id',
		'author_id' => 'author_id',
		'approval' => 'approval'
    );
    
    $this->hitstatsfield = array(
    	'sid' => 'sid',
		'pid' => 'pid',
		'ip' => 'ip',
		'search_phrase' => 'search_phrase',
		'sdate' => 'sdate',
		'referer' => 'referer',
		'browser' => 'browser',
		'os' => 'os'
    );    

    $this->votesfield = array(
    	'pid' => 'pic_id',
		'user_md5_id' => 'user_md5_id',
		'vote_time' => 'vote_time'
    );    

    $this->votestatsfield = array(
    	'sid' => 'sid',
		'pid' => 'pid',
		'ip' => 'ip',
		'rating' => 'rating',
		'sdate' => 'sdate',
		'referer' => 'referer',
		'browser' => 'browser',
		'os' => 'os',
    	'uid' => 'uid'
    );    
    
  }

  function sql_connect() {
    global $DBS, $CF;
    mysql_connect($this->db['host'], $this->db['user'], $this->db['password']);
    @mysql_select_db($this->db['name']) or $CF->unsafeexit("server_connection_error");
    $this->dbactive = true;
  }

  function sql_disconnect() {
    mysql_close();
  }
 
  function sql_query($sqlquery) {
    $results = mysql_query($sqlquery);
    return $results;
  }

  function sql_update($sqlquery) {
    mysql_query($sqlquery);
  }

  function sql_insert_id() {
  	return mysql_insert_id();
  }

}

?>