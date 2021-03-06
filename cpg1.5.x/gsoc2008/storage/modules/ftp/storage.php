<?php

// if we're not in coppermine, exit
if (!defined('IN_COPPERMINE')) { die('Not in Coppermine...');}

/**
 * This class, which should exist in any storage module, contains
 * standard function for storing, deleting and generating image urls
 */
class storage
{

	var $active_connections = array(); // keeps a list of active connections to FTP servers
	var $config = array(); // configuration array loaded via the constructor
	var $system_thumbs = array("nopic.jpg", "private.jpg");
		
	function storage()
	{
		global $CONFIG;
		$this->config = $CONFIG;
	}

	/**
	 * store_file($fileContainer)
	 *
	 * This function gets an image object, which contains data about an image,
	 * makes some checks, then calls ftp_store_file($fileContainer);
	 * 
	 * @param image
	 * @return nothing, for now
	 * @see ftp_store_file
	 */
	function store_file($fileContainer)
	{
		if(!is_array($fileContainer->thumb_paths))
			cpg_die(ERROR, 'The $fileContainer->thumb_paths variable sent to the storage module is not an array');
		
		if(!sizeof($fileContainer->thumb_paths) && !sizeof($fileContainer->original_path))
			return;
		
		$this->ftp_store_file($fileContainer);
	}
	
	/**
	 * ftp_store_file($fileContainer)
	 * 
	 * This function stores an image and its thumbnails to the right
	 * FTP accounts.
	 *
	 * @param image
	 * @return nothing, for now
	 */
	function ftp_store_file($fileContainer)
	{
		// adds the original url to the list of thumbs, as only this list of thumbnails is used to upload files
		if(isset($fileContainer->original_path) && strlen($fileContainer->original_path)) $fileContainer->thumb_paths[] = $fileContainer->original_path;

		// gets one or more servers where this image will be uploaded. The servers are selected based on different
		// factors and rules, which are configured in config.inc.php
		$servers = $this->get_target_servers($fileContainer);

		if(sizeof($servers) && sizeof($fileContainer->thumb_paths)) // if we got any servers where to store the files
		{
		
			$relative_path_to_store = dirname(reset($fileContainer->thumb_paths));
			//echo "rel: ".$relative_path_to_store."<br>\n"; 
		
			foreach($servers as $server) // store the image and its thumbnails to each server in the list
			{
				$conn_id = $this->get_ftp_connection_id($server); // this queries the open connections list for the right $conn_id.
				// If a connection to this server does not exist it will be created, then the connection id will be returned
			
				$levels = $this->ftp_chdir_mkdir($conn_id, explode("/", $server['subfolder_path'].$relative_path_to_store));
			
				if($levels!=FALSE) // if we managed to change to the right path, try to upload the files
				{
					foreach($fileContainer->thumb_paths as $local_file_path)
					{
						$remote_filename = basename($local_file_path); // gets only the filename from the path
						if(!ftp_put($conn_id, $remote_filename, $local_file_path, FTP_BINARY)) // uploads an image to the FTP account
						{
							echo "Couldn't upload file ".$local_file_path."<br>\n";
					 		return FALSE; // TODO: Better error handling
						}
					}
					for($i=0; $i<$levels; $i++) ftp_cdup($conn_id); // using the depth calculated above, we change the path back to the
				}
				else // we couldn't change the folder to the right path where we need to upload the images
				{
					// TODO: use a msg_box
					// TODO - error handling
					echo "err levels == false";		
				}
				
				// TODO: Check if all ids exist
				$sql_pic2server = "INSERT INTO {$this->config['TABLE_FTP_PIC2SERVER']} SET pic_id='{$fileContainer->id}', server_id='{$server['id']}'";
				//echo "sql_pic2server: ".$sql_pic2server."<br>\n"; 
				cpg_db_query($sql_pic2server);
				$sql_quota = "UPDATE {$this->config['TABLE_FTP_SERVERS']} SET used=used+{$fileContainer->total_filesize}, free=free-{$fileContainer->total_filesize} WHERE id='{$server['id']}'";
				//echo "sql_quota: ".$sql_quota."<br>\n";
				cpg_db_query($sql_quota);
				// TODO: Error handling
				// TODO: Rollback in case of errors		
				
			} // foreach($fileContainer->thumb_paths as $fileContainer)

			// if $this->CONFIG['storage_ftp_keep_local_copy'] is set to false, delete the local files
			if(isset($this->config['storage_ftp_keep_local_copy']) && $this->config['storage_ftp_keep_local_copy']==false)
				if(sizeof($fileContainer->thumb_paths))
					foreach($fileContainer->thumb_paths as $local_file_path)
						if(is_file($local_file_path))
							unlink($local_file_path);
			
		} // if(sizeof($servers) && sizeof($fileContainer->thumb_paths))// TODO: Else what? Error message?
	} // ftp_store_file

	/**
	 * replace_file($fileContainer)
	 *
	 * This function gets an image object and calls ftp_replace_file($fileContainer);
	 * 
	 * @see ftp_replace_file
	 */	
	function replace_file($fileContainer)
	{
		$this->ftp_replace_file($fileContainer);
	}

	/**
	 * ftp_replace_file($fileContainer)
	 * 
	 * The function overwrites a file already located on one or
	 * more FTP servers with a new image.
	 *
	 */
	function ftp_replace_file($fileContainer)
	{
		$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_PIC2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_PIC2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_PIC2SERVER']}.pic_id='{$fileContainer->id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive'";
		$result = cpg_db_query($sql);
		while($server = mysql_fetch_assoc($result))
	    {
			$conn_id = $this->get_ftp_connection_id($server); // this queries the open connections list for the right $conn_id.
			$relative_path_to_store = $server['subfolder_path'].$fileContainer->original_path;
			//echo "Saving ".$fileContainer->original_path." to ".$relative_path_to_store." on ".$server['username']."<br>";
			if(!ftp_put($conn_id, $relative_path_to_store, $fileContainer->original_path, FTP_BINARY)) // uploads an image to the FTP account
			{
		    	echo "Couldn't replace file ".$fileContainer."<br>\n";
		    	return FALSE; // TODO: Better error handling
			}
	    }
	    
	    if(isset($this->config['storage_ftp_keep_local_copy']) && $this->config['storage_ftp_keep_local_copy']==false)
    		if(sizeof($fileContainer->original_path))
		    if(is_file($fileContainer->original_path))
    			@unlink($fileContainer->original_path);
	    
	}

	/**
	 * rename_file($fileContainer, $new_name)
	 *
	 * This function gets an image object and calls ftp_rename_file($fileContainer, $new_name);
	 * 
	 * @see ftp_rename_file
	 */	
	function rename_file($fileContainer, $new_name)
	{
		return $this->ftp_rename_file($fileContainer, $new_name);
	}

	/**
	 * ftp_rename_file($fileContainer, $new_name)
	 * 
	 * The function renames a file already located on one or
	 * more FTP servers with a new image.
	 *
	 */
	function ftp_rename_file($fileContainer, $new_name)
	{
		$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_PIC2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_PIC2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_PIC2SERVER']}.pic_id='{$fileContainer->id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive'";
		$result = cpg_db_query($sql);
		while($server = mysql_fetch_assoc($result))
	    {
			$conn_id = $this->get_ftp_connection_id($server); // this queries the open connections list for the right $conn_id.
			$levels = $this->ftp_chdir_mkdir($conn_id, explode("/", $server['subfolder_path'].dirname($fileContainer->original_path)));			
			if($levels!=false)
			{
				$old_name = basename($fileContainer->original_path);
				$new_name = basename($new_name);
				if(!ftp_rename($conn_id, $old_name, $new_name)) // renames a file on the FTP account
				{
					echo "Couldn't rename file ".$fileContainer."<br>\n";
					return false; // TODO: Better error handling
				}			
				for($i=0; $i<$levels; $i++) ftp_cdup($conn_id); // using the depth calculated above, we change the path back to the original position
			}
			else
				{echo "Couldn't chdir"; exit(1);} // TODO: Error handling
	    }
		return true;
	}
	
	/**
	 * delete_file($fileContainer)
	 *
	 * This function gets an image object, which contains data about an image,
	 * makes some checks, then calls ftp_delete_file($fileContainer);
	 * 
	 * @param image
	 * @return nothing, for now
	 * @see ftp_delete_file
	 */
	function delete_file($fileContainer)
	{
		if(!is_array($fileContainer->thumb_paths))
			cpg_die(ERROR, 'The $fileContainer->thumb_paths variable sent to the storage module is not an array');
		
		if(!sizeof($fileContainer->thumb_paths) && !sizeof($fileContainer->original_path))
			return;
		
		$this->ftp_delete_file($fileContainer);
	}
	
	/**
	 * ftp_delete_file($fileContainer)
	 * 
	 * The function deletes an image and its thumbnails from one or more FTP accounts
	 *
	 * @param image
	 * @return nothing, for now
	 */
	function ftp_delete_file($fileContainer)
	{	

		// adds the original url to the list of thumbs, as only this list of thumbnails is used to delete files
		if(isset($fileContainer->original_path) && strlen($fileContainer->original_path)) $fileContainer->thumb_paths[] = $fileContainer->original_path;

		// gets one or more servers where this image will be uploaded. The servers are selected based on different
		// factors and rules, which are configured in config.inc.php
		$servers = $this->get_all_servers($fileContainer);
		
		if(sizeof($servers)) // if we found any servers to store the images
		{
			foreach($servers as $server)
			{
				$conn_id = $this->get_ftp_connection_id($server); // gets the existing connection id for this FTP account, or creates a new one
				
				if(sizeof($fileContainer->thumb_paths)) // if we have images to upload
				foreach($fileContainer->thumb_paths as $id => $local_file_path)
				{
					if(!ftp_delete($conn_id, $server['subfolder_path'].$local_file_path)) // delete an image from the FTP account
					{
						echo "Couldn't delete ".$server['subfolder_path'].$local_file_path." from the ".$server['hostname']." FTP server<br>\n";
				 		//return FALSE; // TODO: Better error handling (message output)
				 		// TODO: Delete folders if they are empty?
					}
				} // foreach($fileContainer->thumb_paths as $local_file_path)

				$sql_quota = "UPDATE {$this->config['TABLE_FTP_SERVERS']} SET used=used-{$fileContainer->total_filesize}, free=free+{$fileContainer->total_filesize} WHERE id='{$server['id']}'";
				//echo "sql_quota: ".$sql_quota."<br>\n";
				cpg_db_query($sql_quota);
				
			} // foreach($servers as $server)
		} // if(sizeof($servers))
		
		$sql_pic2server = "DELETE FROM {$this->config['TABLE_FTP_PIC2SERVER']} WHERE pic_id='{$fileContainer->id}'";
		//echo "del sql_pic2server: ".$sql_pic2server."<br>\n"; 
		cpg_db_query($sql_pic2server);
		
	} // ftp_delete_file
	
	/**
	 * get_ftp_connection_id($server)
	 *
	 * This function returns a connection id for a $server array. If there is already
	 * a connection to this server, it returns the connection. If a connection does
	 * not exist yet, it tries to open an ftp connection and then to log in, then
	 * returns the new connection. All new connections are stored into the
	 * $this->active_connections array. All existing connections are retrieved from
	 * the same array, $this->active_connections. So $this->active_connections is in
	 * effect a list of open connections, one for each FTP server/account. This should
	 * theoretically speed up things when we upload multiple files at the same time.
	 * Another reason to keep a list of connections is that when more advanced mirorring
	 * options are activated each image in a set could go to a different server.
	 *  
	 * @param server
	 * @return nothing, for now
	 */
	function get_ftp_connection_id($server, $ignore_errors = false)
	{
	
		$ssl_secure_connection = false;
		
		if(isset($server['ssl_secure_connection']) && is_bool($server['ssl_secure_connection']))
			$ssl_secure_connection = $server['ssl_secure_connection']; // checks if this is a secure FTP connection
	
		$hash = ""; // this has is a unique identifier for an FTP account (hostname+port+username)
		
		if(isset($server['port'])) // if a port is set, use it to generate the unique hash
			$hash = md5($server['hostname'].$server['port'].$server['username'].$ssl_secure_connection);
		else
			$hash = md5($server['hostname'].$server['username'].$ssl_secure_connection);
		
		if(!isset($this->active_connections[$hash])) // if a connection to this server does not exist, create it
		{
			$conn_id=false;
			
			if($ssl_secure_connection) // this is a secure FTP connection (SSL), use the right function
			{
				if(isset($server['port'])) // if a port is set, use it while connecting
					$conn_id = ftp_ssl_connect($server['hostname'], $server['port']); // needs PHP 4.3
				else
					$conn_id = ftp_ssl_connect($server['hostname']); // needs PHP 4.3
			}
			else // this is a normal FTP connection
			{
				if(isset($server['port'])) // if a port is set, use it while connecting
					$conn_id = ftp_connect($server['hostname'], $server['port']);
				else
					$conn_id = ftp_connect($server['hostname']);
			}
			
			if(!$conn_id && !$ignore_errors)
				cpg_die(ERROR, "Couldn't connect to the ".$server['hostname']." FTP account.");
			elseif(!$conn_id && $ignore_errors)
				return false;
			
			if(isset($server['timeout'])) // set the timeout for this server, if it set in the configuration
				ftp_set_option($conn_id, FTP_TIMEOUT_SEC, $server['timeout']);
				
			$this->active_connections[$hash] = $conn_id; // save this connection in the list of active connections
			
			$login_result = ftp_login($conn_id, $server['username'], $server['password']);
					
			if(!$login_result && !$ignore_errors)
				cpg_die(ERROR, "Couldn't login into the ".$server['hostname']." - ".$server['username']." FTP account.");
			elseif(!$login_result && $ignore_errors)
				return false;
			
		}
		
		return $this->active_connections[$hash]; // return the new or existing FTP connection
		
	}
	
	/**
	 * cleanup()
	 *
	 * This is a standard function that must exist in any storage module.
	 * It is not used at the moment. It calls close_ftp_connections
	 * 
	 * @return nothing, for now
	 * @see close_ftp_connections
	 */
	function cleanup()
	{
		$this->close_ftp_connections();
	}
	
	/**
	 * close_ftp_connections()
	 *
	 * This function goes through all open FTP connections and tries to
	 * close them.
	 * 
	 * @return nothing, for now
	 */
	function close_ftp_connections()
	{
		if(sizeof($this->active_connections)) // if there are any open connections
		foreach($this->active_connections as $hash => $conn_id)
		{
			ftp_close($conn_id);
		}
		
		$this->active_connections = array(); // when done closing connections, empty the list of active connections
	}
	
	/**
	 * build_url($fileContainer)
	 * 
	 * This function is called whenever we need to show an image or a thumbnail
	 * in the browser. This function gets a relative path to the image in question
	 * and prepends the right server path. The server where we server the image from
	 * is selected using the rules set in config.inc.php (rules thing not completed yet).
	 *
	 */
	function build_url($fileContainer)
	{	
		// TODO: Ignore down servers

		if(!isset($fileContainer->id) || in_array($fileContainer->filename, $this->system_thumbs))
		{
			return $fileContainer->original_path; // probably a local icon
		}

		// TODO: Show "Not available if we can't find any server"
		
		switch($this->config['storage_ftp_pic_url_source'])
		{
			case PIC_URL_SOURCE_LOCAL:
			{
			    return $fileContainer->original_path;
			    break;
			}
			case PIC_URL_SOURCE_RANDOM_SERVER:
			{
       			    $sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_PIC2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_PIC2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_PIC2SERVER']}.pic_id='{$fileContainer->id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive' ORDER BY RAND() LIMIT 1";
       			    $result = cpg_db_query($sql);
       			    $server = mysql_fetch_assoc($result);
			    return $server['prefix_url'].$fileContainer->original_path;
			    break;
			}
			case PIC_URL_SOURCE_FIRST_SERVER:
			{		
       			    $sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_PIC2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_PIC2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_PIC2SERVER']}.pic_id='{$fileContainer->id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive' LIMIT 1";
       			    $result = cpg_db_query($sql);
       			    $server = mysql_fetch_assoc($result);
			    return $server['prefix_url'].$fileContainer->original_path;
			    break;
			}
			default:
			{
			    cpg_die(ERROR, "CONFIG 'pic_url_source' is set to an unknown value");
			    break;
			}
		}
	}

	/**
	 * get_all_servers($fileContainer)
	 * 
	 * Returns all servers where an image is stored - used for delete, update
	 *
	 */		
	function get_all_servers($fileContainer)
	{
		$servers = array();			
       	$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_PIC2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_PIC2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_PIC2SERVER']}.pic_id='{$fileContainer->id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive'";
       	$result = cpg_db_query($sql);
       	while($server = mysql_fetch_assoc($result))
       		$servers[] = $server;
       	
       		// TODO: check if list is empty
       		
		return $servers;
	}

	/**
	 * get_target_servers($fileContainer)
	 * 
	 * Returns all servers where an image must be stored
	 *
	 */			
	function get_target_servers($fileContainer)
	{

		// TODO: remove
		//if(!isset($fileContainer->id))
		//{echo "mumu654<br>\n"; return array();}
	
		switch($this->config['storage_ftp_rule'])
		{
			case MIRROR_TO_ALL:
			{
				$servers = array();
       				$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} WHERE status!='inactive'";
       				$result = cpg_db_query($sql);
       				while ($row = mysql_fetch_assoc($result))
       				    $servers[] = $row;
				return $servers;
				break;
			}
			case MIRROR_TO_SOME:
			{
				$servers = array();
				if(!isset($this->config['storage_ftp_copies_per_file']))
					$this->config['storage_ftp_copies_per_file'] = 3;
       				$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} WHERE status!='inactive' ORDER BY free DESC LIMIT ".$this->config['storage_ftp_copies_per_file'];
       				$result = cpg_db_query($sql);
       				while ($row = mysql_fetch_assoc($result))
       				    $servers[] = $row;
				return $servers;
				break;
			}
			case MIRROR_USER_SHARDING:
			{
				if(!isset($fileContainer->id))
				    cpg_die(ERROR, '$fileContainer->id is not set in MIRROR_USER_SHARDING');

				if(!isset($fileContainer->owner_id))
				    cpg_die(ERROR, '$fileContainer->owner_id is not set in MIRROR_USER_SHARDING');

				$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_USER2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_USER2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_USER2SERVER']}.user_id='{$fileContainer->owner_id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive' ORDER BY {$this->config['TABLE_FTP_SERVERS']}.free DESC LIMIT ".$this->config['storage_ftp_copies_per_file'];
				$result = cpg_db_query($sql);

				if(!mysql_num_rows($result))
				{
					$sql = "SELECT id FROM {$this->config['TABLE_FTP_SERVERS']} WHERE status!='inactive' ORDER BY free DESC LIMIT ".$this->config['storage_ftp_copies_per_file'];
					$result = cpg_db_query($sql);
       				while($row = mysql_fetch_assoc($result))
						cpg_db_query("INSERT INTO {$this->config['TABLE_FTP_USER2SERVER']} SET user_id='{$fileContainer->owner_id}', server_id='{$row[id]}'");
					$sql = "SELECT * FROM {$this->config['TABLE_FTP_SERVERS']} JOIN {$this->config['TABLE_FTP_USER2SERVER']} ON {$this->config['TABLE_FTP_SERVERS']}.id={$this->config['TABLE_FTP_USER2SERVER']}.server_id WHERE {$this->config['TABLE_FTP_USER2SERVER']}.user_id='{$fileContainer->owner_id}' AND {$this->config['TABLE_FTP_SERVERS']}.status!='inactive' ORDER BY {$this->config['TABLE_FTP_SERVERS']}.free DESC LIMIT ".$this->config['storage_ftp_copies_per_file'];
					$result = cpg_db_query($sql);
				}

				if(!mysql_num_rows($result))
				{
					exit(1);
					cpg_die(ERROR, 'Could not assign servers to user in MIRROR_USER_SHARDING');
				}

				$servers = array();

       			while ($row = mysql_fetch_assoc($result))
					$servers[] = $row;

				return $servers;
				break;
			}
			default:
			{
				cpg_die(ERROR, "CONFIG 'storage_ftp_rule' is set to an unknown value");
				break;
			}
		}
	}
	
	/**
	 * ftp_chdir_mkdir($conn_id, $folder_elements, $levels=0)
	 * 
	 * This function changes the current folder to the right path where the images must be uploaded.
	 * If the path does not exist, it is created recursively. The function returns a number which represets
	 * the depth of the path (example: the relative "my/test/dir" folder has depth 3).
	 *
	 * @param image
	 * @return nothing, for now
	 */
	function ftp_chdir_mkdir($conn_id, $folder_elements, $levels=0)
	{
		if(!sizeof($folder_elements)) // if we don't need to change the current directory, return
			return $levels;
		
		$current_folder = array_shift($folder_elements); // gets the first folder in the path (example: it would return my for my/test/dir, which is in form of an array [my,test,dir])
		
		if(!ftp_chdir($conn_id, $current_folder)) // if we can't change into this folder, we probably need to create it
		{
			if(!ftp_mkdir($conn_id, $current_folder)) // if we can't create the folder, we have a problem
			{
				echo "Returning FALSE from ftp_mkdir<br>\n"; // TODO: Better error handling, use msgbox etc
				return FALSE; // TODO: Cleanup if transfer failed
			}
			
			if(!ftp_chdir($conn_id, $current_folder)) // If after we created the folder we can't get into it.. that's another big problem
			{
				echo "Returning FALSE from ftp_chdir<br>\n";
				return FALSE; // TODO: Cleanup if transfer failed
			}
		}
		
		return $this->ftp_chdir_mkdir($conn_id, $folder_elements, ++$levels); // rerun the function for the remaining path, going one more level deep
	}
	
}

?>
