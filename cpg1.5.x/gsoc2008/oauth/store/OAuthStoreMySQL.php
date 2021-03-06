<?php

/**
 * Storage container for the oauth credentials, both server and consumer side.
 * Based on MySQL
 * 
 * @version $Id: OAuthStoreMySQL.php 5 2008-02-13 12:29:12Z marcw@pobox.com $
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2007 Mediamatic Lab
 * @date  Nov 16, 2007 4:03:30 PM
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


class OAuthStoreMySQL
{
	/**
	 * The MySQL connection 
	 */
	protected $conn;


	/**
	 * Construct the OAuthStoreMySQL.
	 * In the options you have to supply either:
	 * - server, username, password and database (for a mysql_connect)
	 * - conn (for the connection to be used)
	 * 
	 * @param array options
	 */
	function __construct ( $options = array() )
	{
		if (isset($options['conn']))
		{
			$this->conn = $options['conn'];
		}
		else
		{
			if (isset($options['server']))
			{
				$server   = $options['server'];
				$username = $options['username'];
				
				if (isset($options['password']))
				{
					$this->conn = mysql_connect($server, $username, $options['password']);
				}
				else
				{
					$this->conn = mysql_connect($server, $username);
				}
			}
			else
			{
				// Try the default mysql connect
				$this->conn = mysql_connect();
			}

			if (isset($options['database']))
			{
				if (!mysql_select_db($options['database'], $this->conn))
				{
					$this->sql_errcheck();
				}
			}
			$this->query('set character set utf8');
		}
	}


	/**
	 * Find stored credentials for the consumer key and token. Used by an OAuth server
	 * when verifying an OAuth request.
	 * 
	 * TODO: also check the status of the consumer key
	 * 
	 * @param string consumer_key
	 * @param string token
	 * @param string token_type		false, 'request' or 'access'
	 * @exception OAuthException when no secrets where found
	 * @return array	assoc (consumer_secret, token_secret, osr_id, ost_id, user_id)
	 */
	public function getSecretsForVerify ( $consumer_key, $token, $token_type = 'access' )
	{
		global $CONFIG;
		if ($token_type === false)
		{
			$rs = $this->query_row_assoc('
						SELECT	osr_id, 
								osr_consumer_key		as consumer_key,
								osr_consumer_secret		as consumer_secret
						FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
						WHERE osr_consumer_key	= \'%s\'
						  AND osr_enabled		= 1
						', 
						$consumer_key);
			
			if ($rs)
			{
				$rs['token'] 		= false;
				$rs['token_secret']	= false;
				$rs['user_id']		= false;
				$rs['ost_id']		= false;
			}
		}
		else
		{
			$rs = $this->query_row_assoc('
						SELECT	osr_id, 
								ost_id,
								ost_usa_id_ref			as user_id,
								osr_consumer_key		as consumer_key,
								osr_consumer_secret		as consumer_secret,
								ost_token				as token,
								ost_token_secret		as token_secret
						FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
								JOIN ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
								ON ost_osr_id_ref = osr_id
						WHERE ost_token_type	= \'%s\'
						  AND osr_consumer_key	= \'%s\'
						  AND ost_token			= \'%s\'
					 	  AND osr_enabled		= 1
						', 
						$token_type, $consumer_key, $token);
		}
		
		if (empty($rs))
		{
			throw new OAuthException('The consumer_key "'.$consumer_key.'" token "'.$token.'" combination does not exist or is not enabled.');
		}
		return $rs;
	}


	/**
	 * Find the server details for signing a request, always looks for an access token.
	 * The returned credentials depend on which local user is making the request.
	 * 
	 * For signing we need all of the following:
	 * 
	 * consumer_key			consumer key associated with the server
	 * consumer_secret		consumer secret associated with this server
	 * token				access token associated with this server
	 * token_secret			secret for the access token
	 * signature_methods	signing methods supported by the server (array)
	 * 
	 * @todo filter on token type (we should know how and with what to sign this request, and there might be old access tokens)
	 * @param string uri	uri of the server
	 * @param int user_id	id of the logged on user
	 * @exception OAuthException when no credentials found
	 * @return array
	 */
	public function getSecretsForSignature ( $uri, $user_id )
	{
		// Find a consumer key and token for the given uri
		$ps		= parse_url($uri);
		$host	= isset($ps['host']) ? $ps['host'] : 'localhost';
		$path	= isset($ps['path']) ? $ps['path'] : '';
		
		if (empty($path) || substr($path, -1) != '/')
		{
			$path .= '/';
		}

		$secrets= $this->query_row_assoc('
					SELECT	ocr_consumer_key		as consumer_key,
							ocr_consumer_secret		as consumer_secret,
							oct_token				as token,
							oct_token_secret		as token_secret,
							ocr_signature_methods	as signature_methods
					FROM oauth_consumer_registry
						JOIN oauth_consumer_token ON oct_ocr_id_ref = ocr_id
					WHERE ocr_server_uri_host = \'%s\'
					  AND ocr_server_uri_path = LEFT(\'%s\', LENGTH(ocr_server_uri_path))
					  AND ocr_usa_id_ref	  = %d
					  AND oct_usa_id_ref	  = %d
					  AND oct_token_type      = \'access\'
					ORDER BY LENGTH(ocr_server_uri_path) DESC
					', $host, $path, $user_id, $user_id
					);
		
		if (empty($secrets))
		{
			throw new OAuthException('No server tokens available for '.$uri);
		}
		$secrets['signature_methods'] = explode(',', $secrets['signature_methods']);
		return $secrets;
	}


	/**
	 * Get the token and token secret we obtained from a server.
	 * 
	 * @param string	consumer_key
	 * @param string 	token
	 * @param string	token_type
	 * @param int		usr_id			the user requesting the token, 0 for public secrets
	 * @exception OAuthException when no credentials found
	 * @return array
	 */
	public function getServerTokenSecrets ( $consumer_key, $token, $token_type, $usr_id = 0 )
	{
		if ($token_type != 'request' && $token_type != 'access')
		{
			throw new OAuthException('Unkown token type "'.$token_type.'", must be either "request" or "access"');
		}

		$usr_where = $usr_id ? ' oct_usa_id_ref = %d ' : ' oct_usa_id_ref IS NULL ';

		// Take the most recent token of the given type
		$r = $this->query_row_assoc('
					SELECT	ocr_consumer_key		as consumer_key,
							ocr_consumer_secret		as consumer_secret,
							oct_token				as token,
							oct_token_secret		as token_secret,
							ocr_signature_methods	as signature_methods,
							ocr_server_uri			as server_uri,
							ocr_request_token_uri	as request_token_uri,
							ocr_authorize_uri		as authorize_uri,
							ocr_access_token_uri	as access_token_uri
					FROM oauth_consumer_registry
							JOIN oauth_consumer_token
							ON oct_ocr_id_ref = ocr_id
					WHERE ocr_consumer_key = \'%s\'
					  AND oct_token_type   = \'%s\'
					  AND oct_token        = \'%s\'
					  AND '.$usr_where.'
					', $consumer_key, $token_type, $token, $usr_id
					);
					
		if (empty($r))
		{
			throw new OAuthException('Could not find a "'.$token_type.'" token for consumer "'.$consumer_key.'" and user '.$usr_id);
		}
		if (isset($r['signature_methods']) && !empty($r['signature_methods']))
		{
			$r['signature_methods'] = explode(',',$r['signature_methods']);
		}
		else
		{
			$r['signature_methods'] = array();
		}
		return $r;		
	}


	/**
	 * Add a request token we obtained from a server.
	 * 
	 * @todo remove old tokens for this user and this ocr_id
	 * @param string consumer_key	key of the server in the consumer registry
	 * @param string token_type		one of 'request' or 'access'
	 * @param string token
	 * @param string token_secret
	 * @param int 	 usr_id			the user this token owns
	 * @exception OAuthException when server is not known
	 * @exception OAuthException when we received a duplicate token
	 */
	public function addServerToken ( $consumer_key, $token_type, $token, $token_secret, $usr_id )
	{
		if ($token_type != 'request' && $token_type != 'access')
		{
			throw new OAuthException('Unkwown token type "'.$token_type.'", must be either "request" or "access"');
		}

		$ocr_id = $this->query_one('
					SELECT ocr_id
					FROM oauth_consumer_registry
					WHERE ocr_consumer_key = \'%s\'
					', $consumer_key);
					
		if (empty($ocr_id))
		{
			throw new OAuthException('No server associated with consumer_key "'.$consumer_key.'"');
		}
		
		// Delete any old tokens with the same type for this user/server combination
		$this->query('
					DELETE FROM oauth_consumer_token
					WHERE oct_ocr_id_ref	= %d
					  AND oct_usa_id_ref	= %d
					  AND oct_token_type	= LOWER(\'%s\')
					',
					$ocr_id,
					$usr_id,
					$token_type);

		// Insert the new token
		$this->query('
					INSERT IGNORE INTO oauth_consumer_token
					SET oct_ocr_id_ref	= %d,
						oct_usa_id_ref  = %d,
						oct_token		= \'%s\',
						oct_token_secret= \'%s\',
						oct_token_type	= LOWER(\'%s\'),
						oct_timestamp	= NOW()
					',
					$ocr_id,
					$usr_id,
					$token,
					$token_secret,
					$token_type);
		
		if (!$this->query_affected_rows())
		{
			throw new OAuthException('Received duplicate token "'.$token.'" for the same consumer_key "'.$consumer_key.'"');
		}
	}


	/**
	 * Delete a server key.  This removes access to that site.
	 * 
	 * @param string consumer_key
	 * @param int user_id	user registering this server
	 * @param boolean user_is_admin
	 */
	public function deleteServer ( $consumer_key, $user_id, $user_is_admin = false )
	{
		$this->query('
				DELETE FROM oauth_consumer_registry
				WHERE ocr_consumer_key = \'%s\'
				  AND ocr_usa_id_ref   = %d
				', $consumer_key, $user_id);
	}
	
	
	/**
	 * Get a consumer from the consumer registry using the consumer key
	 * 
	 * @param string consumer_key
	 * @exception OAuthException when server is not found
	 * @return array
	 */	
	public function getServer( $consumer_key )
	{
		$r = $this->query_row_assoc('
				SELECT	ocr_id					as id,
						ocr_usa_id_ref			as user_id,
						ocr_consumer_key 		as consumer_key,
						ocr_consumer_secret 	as consumer_secret,
						ocr_signature_methods	as signature_methods,
						ocr_server_uri			as server_uri,
						ocr_request_token_uri	as request_token_uri,
						ocr_authorize_uri		as authorize_uri,
						ocr_access_token_uri	as access_token_uri
				FROM oauth_consumer_registry
				WHERE ocr_consumer_key = \'%s\'
				',	$consumer_key);
		
		if (empty($r))
		{
			throw new OAuthException('No server with consumer_key "'.$consumer_key.'" has been registered');
		}
			
		if (isset($r['signature_methods']) && !empty($r['signature_methods']))
		{
			$r['signature_methods'] = explode(',',$r['signature_methods']);
		}
		else
		{
			$r['signature_methods'] = array();
		}
		return $r;
	}


	/**
	 * Get a list of all server token this user has access to.
	 * 
	 * @param int usr_id
	 * @return array
	 */
	public function listServerTokens ( $usr_id )
	{
		$ts = $this->query_all_assoc('
					SELECT	ocr_consumer_key		as consumer_key,
							ocr_consumer_secret		as consumer_secret,
							oct_token				as token,
							oct_token_secret		as token_secret,
							oct_usa_id_ref			as usr_id,
							ocr_signature_methods	as signature_methods,
							ocr_server_uri			as server_uri,
							ocr_server_uri_host		as server_uri_host,
							ocr_server_uri_path		as server_uri_path,
							ocr_request_token_uri	as request_token_uri,
							ocr_authorize_uri		as authorize_uri,
							ocr_access_token_uri	as access_token_uri,
							oct_timestamp			as timestamp
					FROM oauth_consumer_registry
							JOIN oauth_consumer_token
							ON oct_ocr_id_ref = ocr_id
					WHERE oct_usa_id_ref = %d
					  AND oct_token_type = \'access\'
					ORDER BY ocr_server_uri_host, ocr_server_uri_path
					', $usr_id);
		return $ts;
	}


	/**
	 * Count how many tokens we have for the given server
	 * 
	 * @param string consumer_key
	 * @return int
	 */
	public function countServerTokens ( $consumer_key )
	{
		$count = $this->query_one('
					SELECT COUNT(oct_id)
					FROM oauth_consumer_token
							JOIN oauth_consumer_registry
							ON oct_ocr_id_ref = ocr_id
					WHERE oct_token_type   = \'access\'
					  AND ocr_consumer_key = \'%s\'
					', $consumer_key);
		
		return $count;
	}


	/**
	 * Get a specific server token for the given user
	 * 
	 * @param string consumer_key
	 * @param string token
	 * @param int user_id
	 * @exception OAuthException when no such token found
	 * @return array
	 */
	public function getServerToken ( $consumer_key, $token, $user_id )
	{
		$ts = $this->query_row_assoc('
					SELECT	ocr_consumer_key		as consumer_key,
							ocr_consumer_secret		as consumer_secret,
							oct_token				as token,
							oct_token_secret		as token_secret,
							oct_usa_id_ref			as usr_id,
							ocr_signature_methods	as signature_methods,
							ocr_server_uri			as server_uri,
							ocr_server_uri_host		as server_uri_host,
							ocr_server_uri_path		as server_uri_path,
							ocr_request_token_uri	as request_token_uri,
							ocr_authorize_uri		as authorize_uri,
							ocr_access_token_uri	as access_token_uri,
							oct_timestamp			as timestamp
					FROM oauth_consumer_registry
							JOIN oauth_consumer_token
							ON oct_ocr_id_ref = ocr_id
					WHERE ocr_consumer_key = \'%s\'
					  AND oct_usa_id_ref   = %d
					  AND oct_token_type   = \'access\'
					  AND oct_token        = \'%s\'
					', $consumer_key, $user_id, $token);
		
		if (empty($ts))
		{
			throw new OAuthException('No such consumer key ('.$consumer_key.') and token ('.$token.') combination for user "'.$user_id.'"');
		}
		return $ts;
	}


	/**
	 * Delete a token we obtained from a server.
	 * 
	 * @param string consumer_key
	 * @param string token
	 * @param int user_id
	 * @param boolean no_user_check
	 */
	public function deleteServerToken ( $consumer_key, $token, $user_id, $no_user_check = false )
	{
		if ($no_user_check)
		{
			$this->query('
				DELETE oauth_consumer_token 
				FROM oauth_consumer_token
						JOIN oauth_consumer_registry
						ON oct_ocr_id_ref = ocr_id
				WHERE ocr_consumer_key	= \'%s\'
				  AND oct_token			= \'%s\'
				', $consumer_key, $token);
		}
		else
		{
			$this->query('
				DELETE oauth_consumer_token 
				FROM oauth_consumer_token
						JOIN oauth_consumer_registry
						ON oct_ocr_id_ref = ocr_id
				WHERE ocr_consumer_key	= \'%s\'
				  AND oct_token			= \'%s\'
				  AND oct_usa_id_ref	= %d
				', $consumer_key, $token, $user_id);
		}
	}


	/**
	 * Get a list of all consumers from the consumer registry
	 * 
	 * @param string q	query term
	 * @param int user_id
	 * @return array
	 */	
	public function listServers ( $q = '', $user_id )
	{
		$q    = trim(str_replace('%', '', $q));
		$args = array();

		if (!empty($q))
		{
			$where = ' WHERE (	ocr_consumer_key like \'%%%s%%\'
						  	 OR ocr_server_uri like \'%%%s%%\'
						  	 OR ocr_server_uri_host like \'%%%s%%\'
						  	 OR ocr_server_uri_path like \'%%%s%%\'
						 AND ocr_usa_id_ref = %d
					';
			
			$args[] = $q;
			$args[] = $q;
			$args[] = $q;
			$args[] = $q;
			$args[] = $user_id;
		}
		else
		{
			$where = ' WHERE ocr_usa_id_ref = %d ';
			$args[] = $user_id;
		}

		$servers = $this->query_all_assoc('
					SELECT	ocr_id					as id,
							ocr_consumer_key 		as consumer_key,
							ocr_consumer_secret 	as consumer_secret,
							ocr_signature_methods	as signature_methods,
							ocr_server_uri			as server_uri,
							ocr_server_uri_host		as server_uri_host,
							ocr_server_uri_path		as server_uri_path,
							ocr_request_token_uri	as request_token_uri,
							ocr_authorize_uri		as authorize_uri,
							ocr_access_token_uri	as access_token_uri
					FROM oauth_consumer_registry
					'.$where.'
					ORDER BY ocr_server_uri_host, ocr_server_uri_path
					', $args);
		return $servers;
	}


	/**
	 * Register or update a server for our site (we will be the consumer)
	 * 
	 * (This is the registry at the consumers, registering servers ;-) )
	 * 
	 * @param array server
	 * @param int user_id	user registering this server
	 * @param boolean user_is_admin
	 * @exception OAuthException when fields are missing or on duplicate consumer_key
	 * @return consumer_key
	 */
	public function updateServer ( $server, $user_id, $user_is_admin = false )
	{
		foreach (array('consumer_key', 'consumer_secret', 'server_uri') as $f)
		{
			if (empty($server[$f]))
			{
				throw new OAuthException('The field "'.$f.'" must be set and non empty');
			}
		}
		
		if (!empty($server['id']))
		{
			$exists = $this->query_one('
						SELECT ocr_id
						FROM oauth_consumer_registry
						WHERE ocr_consumer_key = \'%s\'
						  AND ocr_id <> %d
						', $server['consumer_key'], $server['id']);
		}
		else
		{
			$exists = $this->query_one('
						SELECT ocr_id
						FROM oauth_consumer_registry
						WHERE ocr_consumer_key = \'%s\'
						', $server['consumer_key']);
		}

		if ($exists)
		{
			throw new OAuthException('The server with key "'.$server['consumer_key'].'" has already been registered');
		}

		$parts = parse_url($server['server_uri']);
		$host  = (isset($parts['host']) ? $parts['host'] : 'localhost');
		$path  = (isset($parts['path']) ? $parts['path'] : '/');

		if (isset($server['signature_methods']))
		{
			if (is_array($server['signature_methods']))
			{
				$server['signature_methods'] = strtoupper(implode(',', $server['signature_methods']));
			}
		}	
		else
		{
			$server['signature_methods'] = '';
		}

		if (!empty($server['id']))
		{
			// Check if the current user can update this server definition
			if (!$user_is_admin)
			{
				$ocr_usa_id_ref = $this->query_one('
									SELECT ocr_usa_id_ref
									FROM oauth_consumer_registry
									WHERE ocr_id = %d
									', $server['id']);
				
				if ($ocr_usa_id_ref != $user_id)
				{
					throw new OAuthException('The user "'.$user_id.'" is not allowed to update this server');
				}
			}
					
			// Update the consumer registration	
			$this->query('
					UPDATE oauth_consumer_registry
					SET ocr_consumer_key    	= \'%s\',
						ocr_consumer_secret 	= \'%s\',
						ocr_server_uri	    	= \'%s\',
						ocr_server_uri_host 	= \'%s\',
						ocr_server_uri_path 	= \'%s\',
						ocr_timestamp       	= NOW(),
						ocr_request_token_uri	= \'%s\',
						ocr_authorize_uri		= \'%s\',
						ocr_access_token_uri	= \'%s\',
						ocr_signature_methods	= \'%s\'
					WHERE ocr_id = %d
					', 
					$server['consumer_key'],
					$server['consumer_secret'],
					$server['server_uri'],
					strtolower($host),
					$path,
					isset($server['request_token_uri']) ? $server['request_token_uri'] : '',
					isset($server['authorize_uri'])     ? $server['authorize_uri']     : '',
					isset($server['access_token_uri'])  ? $server['access_token_uri']  : '',
					$server['signature_methods'],
					$server['id']
					);
		}
		else
		{
			$this->query('
					INSERT INTO oauth_consumer_registry
					SET ocr_usa_id_ref			= %d,
						ocr_consumer_key    	= \'%s\',
						ocr_consumer_secret 	= \'%s\',
						ocr_server_uri	    	= \'%s\',
						ocr_server_uri_host 	= \'%s\',
						ocr_server_uri_path 	= \'%s\',
						ocr_timestamp       	= NOW(),
						ocr_request_token_uri	= \'%s\',
						ocr_authorize_uri		= \'%s\',
						ocr_access_token_uri	= \'%s\',
						ocr_signature_methods	= \'%s\'
					', 
					$user_id,
					$server['consumer_key'],
					$server['consumer_secret'],
					$server['server_uri'],
					strtolower($host),
					$path,
					isset($server['request_token_uri']) ? $server['request_token_uri'] : '',
					isset($server['authorize_uri'])     ? $server['authorize_uri']     : '',
					isset($server['access_token_uri'])  ? $server['access_token_uri']  : '',
					$server['signature_methods']
					);
		
			$ocr_id = $this->query_insert_id();
		}
		return $server['consumer_key'];
	}


	/**
	 * Insert/update a new consumer with this server (we will be the server)
	 * When this is a new consumer, then also generate the consumer key and secret.
	 * Never updates the consumer key and secret.
	 * When the id is set, then the key and secret must correspond to the entry
	 * being updated.
	 * 
	 * (This is the registry at the server, registering consumers ;-) )
	 * 
	 * @param array consumer
	 * @param int user_id	user registering this consumer
	 * @param boolean user_is_admin
	 * @return string consumer key
	 */
	public function updateConsumer ( $consumer, $user_id, $user_is_admin = false )
	{
		global $CONFIG;
		foreach (array('requester_name', 'requester_email') as $f)
		{
			if (empty($consumer[$f]))
			{
				throw new OAuthException('The field "'.$f.'" must be set and non empty');
			}
		}

		if (!empty($consumer['id']))
		{
			foreach (array('consumer_key', 'consumer_secret') as $f)
			{
				if (empty($consumer[$f]))
				{
					throw new OAuthException('The field "'.$f.'" must be set and non empty');
				}
			}

			// Check if the current user can update this server definition
			if (!$user_is_admin)
			{
				$osr_usa_id_ref = $this->query_one('
									SELECT osr_usa_id_ref
									FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
									WHERE osr_id = %d
									', $consumer['id']);
				
				if ($osr_usa_id_ref != $user_id)
				{
					throw new OAuthException('The user "'.$user_id.'" is not allowed to update this consumer');
				}
			}
			
			$this->query('
				UPDATE ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
				SET osr_requester_name		= \'%s\',
					osr_requester_email		= \'%s\',
					osr_callback_uri		= \'%s\',
					osr_application_uri		= \'%s\',
					osr_application_title	= \'%s\',
					osr_application_descr	= \'%s\',
					osr_application_notes	= \'%s\',
					osr_application_type	= \'%s\',
					osr_application_commercial = IF(%d,1,0),
					osr_timestamp			= NOW()
				WHERE osr_id              = %d
				  AND osr_consumer_key    = \'%s\'
				  AND osr_consumer_secret = \'%s\'
				',
				$consumer['requester_name'],
				$consumer['requester_email'],
				isset($consumer['callback_uri']) 		? $consumer['callback_uri'] 			 : '',
				isset($consumer['application_uri']) 	? $consumer['application_uri'] 			 : '',
				isset($consumer['application_title'])	? $consumer['application_title'] 		 : '',
				isset($consumer['application_descr'])	? $consumer['application_descr'] 		 : '',
				isset($consumer['application_notes'])	? $consumer['application_notes'] 		 : '',
				isset($consumer['application_type']) 	? $consumer['application_type'] 		 : '',
				isset($consumer['application_commercial']) ? $consumer['application_commercial'] : 0,
				$consumer['id'],
				$consumer['consumer_key'],
				$consumer['consumer_secret']
				);
				

			$consumer_key = $consumer['consumer_key'];
		}
		else
		{
			$consumer_key	  = $this->generateKey(true);
//			$consumer_secret= $this->generateKey();
                        $consumer_secret = ''; // Still need to find a way to tell the consumer secret to the consumer in a secure manner. {daorange}

			$this->query('
				INSERT INTO ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
				SET osr_enabled				= 1,
					osr_status				= \'active\',
					osr_usa_id_ref			= %d,
					osr_consumer_key		= \'%s\',
					osr_consumer_secret		= \'%s\',
					osr_requester_name		= \'%s\',
					osr_requester_email		= \'%s\',
					osr_callback_uri		= \'%s\',
					osr_application_uri		= \'%s\',
					osr_application_title	= \'%s\',
					osr_application_descr	= \'%s\',
					osr_application_notes	= \'%s\',
					osr_application_type	= \'%s\',
					osr_application_commercial = IF(%d,1,0),
					osr_timestamp			= NOW(),
					osr_issue_date			= NOW()
				',
				$user_id,
				$consumer_key,
				$consumer_secret,
				$consumer['requester_name'],
				$consumer['requester_email'],
				isset($consumer['callback_uri']) 		? $consumer['callback_uri'] 			 : '',
				isset($consumer['application_uri']) 	? $consumer['application_uri'] 			 : '',
				isset($consumer['application_title'])	? $consumer['application_title'] 		 : '',
				isset($consumer['application_descr'])	? $consumer['application_descr'] 		 : '',
				isset($consumer['application_notes'])	? $consumer['application_notes'] 		 : '',
				isset($consumer['application_type']) 	? $consumer['application_type'] 		 : '',
				isset($consumer['application_commercial']) ? $consumer['application_commercial'] : 0
				);
		}
		return $consumer_key;

	}



	/**
	 * Delete a consumer key.  This removes access to our site for all applications using this key.
	 * 
	 * @param string consumer_key
	 * @param int user_id	user registering this server
	 * @param boolean user_is_admin
	 */
	public function deleteConsumer ( $consumer_key, $user_id, $user_is_admin = false )
	{
		global $CONFIG;
		$this->query('
				DELETE FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
				WHERE osr_consumer_key = \'%s\'
				  AND osr_usa_id_ref   = %d
				', $consumer_key, $user_id);
	}	
	
	
	
	/**
	 * Fetch a consumer of this server, by consumer_key.
	 * 
	 * @param string consumer_key
	 * @exception OAuthException when consumer not found
	 * @return array
	 */
	public function getConsumer ( $consumer_key )
	{
		global $CONFIG;
		$consumer = $this->query_row_assoc('
						SELECT	*
						FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
						WHERE osr_consumer_key = \'%s\'
						', $consumer_key);
		
		if (!is_array($consumer))
		{
			throw new OAuthException('No consumer with consumer_key "'.$consumer_key.'"');
		}

		$c = array();
		foreach ($consumer as $key => $value)
		{
			$c[substr($key, 4)] = $value;
		}
		$c['user_id'] = $c['usa_id_ref'];
		return $c;
	}


	/**
	 * Add an unautorized request token to our server.
	 * 
	 * @param string consumer_key
	 * @return array (token, token_secret)
	 */
	public function addConsumerRequestToken ( $consumer_key )
	{
		global $CONFIG;
		$token  = $this->generateKey(true);
		$secret = $this->generateKey();
		$osr_id	= $this->query_one('
						SELECT osr_id
						FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
						WHERE osr_consumer_key = \'%s\'
						  AND osr_enabled      = 1
						', $consumer_key);

		if (!$osr_id)
		{
			throw new OAuthException('No server with consumer_key "'.$consumer_key.'" or consumer_key is disabled');
		}	

		$this->query('
				INSERT INTO ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
				SET ost_osr_id_ref		= %d,
					ost_usa_id_ref		= 0,
					ost_token			= \'%s\',
					ost_token_secret	= \'%s\',
					ost_token_type		= \'request\'
				ON DUPLICATE KEY UPDATE
					ost_osr_id_ref		= VALUES(ost_osr_id_ref),
					ost_usa_id_ref		= VALUES(ost_usa_id_ref),
					ost_token			= VALUES(ost_token),
					ost_token_secret	= VALUES(ost_token_secret),
					ost_token_type		= VALUES(ost_token_type),
					ost_timestamp		= NOW()
				', $osr_id, $token, $secret);
		
		return array('token'=>$token, 'token_secret'=>$secret);
	}
	
	
	/**
	 * Fetch the consumer request token, by request token.
	 * 
	 * @param string token
	 * @return array  token and consumer details
	 */
	public function getConsumerRequestToken ( $token )
	{
		global $CONFIG;
		$rs = $this->query_row_assoc('
				SELECT	ost_token			as token,
						ost_token_secret	as token_secret,
						osr_consumer_key	as consumer_key,
						osr_consumer_secret	as consumer_secret,
						ost_token_type		as token_type,
						ost_osr_id_ref		as consumer_id
				FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
						JOIN ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
						ON ost_osr_id_ref = osr_id
				WHERE ost_token_type = \'request\'
				  AND ost_token      = \'%s\'
				', $token);
		
		return $rs;
	}
	

	/**
	 * Delete a consumer token.  The token must be a request or authorized token.
	 * 
	 * @param string token
	 */
	public function deleteConsumerRequestToken ( $token )
	{
		global $CONFIG;
		$this->query('
					DELETE FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
					WHERE ost_token 	 = \'%s\'
					  AND ost_token_type = \'request\'
					', $token);
	}
	

	/**
	 * Upgrade a request token to be an authorized request token.
	 * 
	 * @param string token
	 * @param int	 user_id  user authorizing the token
	 */
	public function authorizeConsumerRequestToken ( $token, $user_id )
	{
		global $CONFIG;
		$this->query('
					UPDATE ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
					SET ost_authorized = 1,
						ost_usa_id_ref = %d
					WHERE ost_token      = \'%s\'
					  AND ost_token_type = \'request\'
					', $user_id, $token);
	}


	/**
	 * Count the consumer access tokens for the given consumer.
	 * 
	 * @param string consumer_key
	 * @return int
	 */
	public function countConsumerAccessTokens ( $consumer_key )
	{
		global $CONFIG;
		$count = $this->query_one('
					SELECT COUNT(ost_id)
					FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
							JOIN ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
							ON ost_osr_id_ref = osr_id
					WHERE ost_token_type   = \'access\'
					  AND osr_consumer_key = \'%s\'
					', $consumer_key);
		
		return $count;
	}


	/**
	 * Exchange an authorized request token for new access token.
	 * 
	 * @param string token
	 * @param int	 user_id  user authorizing the token
	 * @exception OAuthException when token could not be exchanged
	 * @return array (token, token_secret)
	 */
	public function exchangeConsumerRequestForAccessToken ( $token )
	{
		global $CONFIG;
		$new_token  = $this->generateKey(true);
		$new_secret = $this->generateKey();

		$this->query('
					UPDATE ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
					SET ost_token			= \'%s\',
						ost_token_secret	= \'%s\',
						ost_token_type		= \'access\',
						ost_timestamp		= NOW()
					WHERE ost_token      = \'%s\'
					  AND ost_token_type = \'request\'
					  AND ost_authorized = 1
					', $new_token, $new_secret, $token);
		
		if ($this->query_affected_rows() != 1)
		{
			throw new OAuthException('Can\'t exchange request token "'.$token.'" for access token. No such token or not authorized');
		}
		return array('token' => $new_token, 'token_secret' => $new_secret);
	}


	/**
	 * Fetch the consumer access token, by access token.
	 * 
	 * @param string token
	 * @param int user_id
	 * @exception OAuthException when token is not found
	 * @return array  token and consumer details
	 */
	public function getConsumerAccessToken ( $token, $user_id )
	{
		global $CONFIG;
		$rs = $this->query_row_assoc('
				SELECT	ost_token				as token,
						ost_token_secret		as token_secret,
						osr_consumer_key		as consumer_key,
						osr_consumer_secret		as consumer_secret,
						osr_application_title	as application_title,
						osr_application_descr	as application_descr
				FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
						JOIN ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
						ON ost_osr_id_ref = osr_id
				WHERE ost_token_type = \'access\'
				  AND ost_token      = \'%s\'
				  AND ost_usa_id_ref = %d
				', $token, $user_id);
		
		if (empty($rs))
		{
			throw new OAuthException('No server_token "'.$token.'" for user "'.$user_id.'"');
		}
		return $rs;
	}


	/**
	 * Delete a consumer access token.
	 * 
	 * @param string token
	 * @param int user_id
	 */
	public function deleteConsumerAccessToken ( $token, $user_id )
	{
		global $CONFIG;
		$this->query('
					DELETE FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
					WHERE ost_token 	 = \'%s\'
					  AND ost_token_type = \'access\'
					  AND ost_usa_id_ref = %d
					', $token, $user_id);
	}


	/**
	 * Fetch a list of all consumers
	 * 
	 * @param int user_id
	 * @return array
	 */
	public function listConsumers ( $user_id )
	{
		global $CONFIG;
		$rs = $this->query_all_assoc('
				SELECT	osr_id					as id,
						osr_consumer_key 		as consumer_key,
						osr_consumer_secret		as consumer_secret,
						osr_enabled				as enabled,
						osr_status 				as status,
						osr_issue_date			as issue_date,
						osr_application_title	as application_title,
						osr_application_descr	as application_descr,
						osr_requester_name		as requester_name,
						osr_requester_email		as requester_email
				FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
				WHERE osr_usa_id_ref = %d
				ORDER BY osr_application_title
				', $user_id);
		return $rs;
	}


	/**
	 * Fetch a list of all consumer tokens accessing the account of the given user.
	 * 
	 * @param int user_id
	 * @return array
	 */
	public function listConsumerTokens ( $user_id )
	{
		global $CONFIG;
		$rs = $this->query_all_assoc('
				SELECT	osr_consumer_key 		as consumer_key,
						osr_consumer_secret		as consumer_secret,
						osr_enabled				as enabled,
						osr_status 				as status,
						osr_application_uri		as application_uri,
						osr_application_title	as application_title,
						osr_application_descr	as application_descr,
						ost_timestamp			as timestamp,	
						ost_token				as token,
						ost_token_secret		as token_secret
				FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
					JOIN ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
					ON ost_osr_id_ref = osr_id
				WHERE ost_usa_id_ref = %d
				  AND ost_token_type = \'access\'
				ORDER BY osr_application_title
				', $user_id);
		return $rs;
	}


	/**
	 * Check an nonce/timestamp combination.  Clears any nonce combinations
	 * that are older than the one received.
	 * 
	 * @param string	consumer_key
	 * @param string 	token
	 * @param int		timestamp
	 * @param string 	nonce
	 * @exception OAuthException	thrown when the nonce is not in sequence
	 */
	public function checkServerNonce ( $consumer_key, $token, $timestamp, $nonce )
	{
		global $CONFIG;
		if ($nonce == '') {
			throw new OAuthException('No oauth_nonce provided. Request rejected.');
		}

		$r = $this->query_row('
							SELECT MAX(osn_timestamp), MAX(osn_timestamp) > %d
							FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_nonce
							WHERE osn_consumer_key = \'%s\'
							  AND osn_token        = \'%s\'
							', $timestamp, $consumer_key, $token);

		if (!empty($r) && $r[1])
		{
			throw new OAuthException('Timestamp is out of sequence. Request rejected');
		}

		// Insert the new combination
		$this->query('
				INSERT IGNORE INTO ' . $CONFIG['TABLE_PREFIX'] . 'oauth_nonce
				SET osn_consumer_key	= \'%s\',
					osn_token			= \'%s\',
					osn_timestamp		= %d,
					osn_nonce			= \'%s\'
				', $consumer_key, $token, $timestamp, $nonce);
		
		if ($this->query_affected_rows() == 0)
		{
			throw new OAuthException('Duplicate timestamp/nonce combination, possible replay attack.  Request rejected.');
		}

		// Clean up all timestamps older than the one we just received
		$this->query('
				DELETE FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_nonce
				WHERE osn_consumer_key	= \'%s\'
				  AND osn_token			= \'%s\'
				  AND osn_timestamp     < %d
				', $consumer_key, $token, $timestamp);
	}


	/**
	 * Generate a unique key
	 * 
	 * @param boolean unique	force the key to be unique
	 * @return string
	 */
	public function generateKey ( $unique = false )
	{
		$key = md5(uniqid(rand(), true));
		if ($unique)
		{
			list($usec,$sec) = explode(' ',microtime());
			$key .= dechex($usec).dechex($sec);
		}
		return $key;
	}


	/**
	 * Add an entry to the log table
	 * 
	 * @param array keys (osr_consumer_key, ost_token, ocr_consumer_key, oct_token)
	 * @param string received
	 * @param string sent
	 * @param string base_string
	 * @param string notes
	 * @param int (optional) user_id
	 */
	public function addLog ( $keys, $received, $sent, $base_string, $notes, $user_id = null )
	{
		$superCage = Inspekt::makeSuperCage();

		$args = array();
		$ps   = array();
		foreach ($keys as $key => $value)
		{
			$args[] = $value;
			$ps[]   = "olg_$key = '%s'";
		}

		if ($superCage->server->keyExists('REMOTE_ADDR'))
		{			
			$matches = $superCage->server->getMatched('REMOTE_ADDR', '/^[0-9]{1,3}(\.[0-9]{1,3}){3}$/');
			$remote_ip = $matches[0];
		}	
		else if ($superCage->server->keyExists('REMOTE_IP'))
		{
			$matches = $superCage->server->getMatched('REMOTE_IP', '/^[0-9]{1,3}(\.[0-9]{1,3}){3}$/');
			$remote_ip = $matches[0];
		}
		else
		{
			$remote_ip = '0.0.0.0';
		}

		// Build the SQL
		$ps[] = "olg_received  	= '%s'";						$args[] = $this->makeUTF8($received);
		$ps[] = "olg_sent   	= '%s'";						$args[] = $this->makeUTF8($sent);
		$ps[] = "olg_base_string= '%s'";						$args[] = $base_string;
		$ps[] = "olg_notes   	= '%s'";						$args[] = $this->makeUTF8($notes);
		$ps[] = "olg_usa_id_ref = NULLIF(%d,0)";				$args[] = $user_id;
		$ps[] = "olg_remote_ip  = IFNULL(INET_ATON('%s'),0)";	$args[] = $remote_ip;

		$this->query('INSERT INTO oauth_log SET '.implode(',', $ps), $args);
	}
	
	
	/**
	 * Get a page of entries from the log.  Returns the last 100 records
	 * matching the options given.
	 * 
	 * @param array options
	 * @param int user_id	current user
	 * @return array log records
	 */
	public function listLog ( $options, $user_id )
	{
		$where = array();
		$args  = array();
		if (empty($options))
		{
			$where[] = 'olg_usa_id_ref = %d';
			$args[]  = $user_id;
		}
		else
		{
			foreach ($options as $option => $value)
			{
				if (strlen($value) > 0)
				{
					switch ($option)
					{
					case 'osr_consumer_key':
					case 'ocr_consumer_key':
					case 'ost_token':
					case 'oct_token':
						$where[] = 'olg_'.$option.' = \'%s\'';
						$args[]  = $value;	
						break;				
					}
				}
			}
			
			$where[] = '(olg_usa_id_ref IS NULL OR olg_usa_id_ref = %d)';
			$args[]  = $user_id;
		}

		$rs = $this->query_all_assoc('
					SELECT olg_id,
							olg_osr_consumer_key 	AS osr_consumer_key,
							olg_ost_token			AS ost_token,
							olg_ocr_consumer_key	AS ocr_consumer_key,
							olg_oct_token			AS oct_token,
							olg_usa_id_ref			AS user_id,
							olg_received			AS received,
							olg_sent				AS sent,
							olg_base_string			AS base_string,
							olg_notes				AS notes,
							olg_timestamp			AS timestamp,
							INET_NTOA(olg_remote_ip) AS remote_ip
					FROM oauth_log
					WHERE '.implode(' AND ', $where).'
					ORDER BY olg_id DESC
					LIMIT 0,100', $args);

		return $rs;
	}



	/**
	 * Initialise the database
	 */
	public function install ()
	{
		require_once dirname(__FILE__) . '/mysql/install.php';
	}
	
	
	/**
	 * Check to see if a string is valid utf8
	 * 
	 * @param string $s
	 * @return boolean
	 */
	protected function isUTF8 ( $s )
	{
		return preg_match('%(?:
	       [\xC2-\xDF][\x80-\xBF]              # non-overlong 2-byte
	       |\xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
	       |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	       |\xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
	       |\xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
	       |[\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	       |\xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
	       )+%xs', $s);
	}
	
	
	/**
	 * Make a string utf8, replacing all non-utf8 chars with a '.'
	 * 
	 * @param string
	 * @return string
	 */
	protected function makeUTF8 ( $s )
	{
		if (function_exists('iconv'))
		{
			do
			{
				$ok   = true;
				$text = @iconv('UTF-8', 'UTF-8//TRANSLIT', $s);
				if (strlen($text) != strlen($s))
				{
					// Remove the offending character...
					$s  = $text . '.' . substr($s, strlen($text) + 1);
					$ok = false;
				}
			}
			while (!$ok);
		}
		return $s;
	}
	
	
	
	
	/** Some simple helper functions for querying the mysql db **/

	/**
	 * Perform a query, ignore the results
	 * 
	 * @param string sql
	 * @param vararg arguments (for sprintf)
	 */
	protected function query ( $sql )
	{
		$sql = $this->sql_printf(func_get_args());
		if (!($res = mysql_query($sql, $this->conn)))
		{
			$this->sql_errcheck($sql);
		}		
		if (is_resource($res)) {
            mysql_free_result($res);
        }
    }
    

    /**
     * Perform a query, ignore the results
     * 
     * @param string sql
     * @param vararg arguments (for sprintf)
     * @return array
     */
    protected function query_all_assoc ( $sql )
    {
        $sql = $this->sql_printf(func_get_args());
        if (!($res = mysql_query($sql, $this->conn)))
        {
            $this->sql_errcheck($sql);
        }
        $rs = array();
        while ($row  = mysql_fetch_assoc($res))
        {
            $rs[] = $row;
        }
        mysql_free_result($res);
        return $rs;
    }
    
    
    /**
     * Perform a query, return the first row
     * 
     * @param string sql
     * @param vararg arguments (for sprintf)
     * @return array
     */
    protected function query_row_assoc ( $sql )
    {
        $sql = $this->sql_printf(func_get_args());
        if (!($res = mysql_query($sql, $this->conn)))
        {
            $this->sql_errcheck($sql);
        }
        if ($row = mysql_fetch_assoc($res))
        {
            $rs = $row;
        }
        else
        {
            $rs = false;
        }
        mysql_free_result($res);
        return $rs;
    }

    
    /**
     * Perform a query, return the first row
     * 
     * @param string sql
     * @param vararg arguments (for sprintf)
     * @return array
     */
    protected function query_row ( $sql )
    {
        $sql = $this->sql_printf(func_get_args());
        if (!($res = mysql_query($sql, $this->conn)))
        {
            $this->sql_errcheck($sql);
        }
        if ($row = mysql_fetch_array($res))
        {
            $rs = $row;
        }
        else
        {
            $rs = false;
        }
        mysql_free_result($res);
        return $rs;
    }
    
        
    /**
     * Perform a query, return the first column of the first row
     * 
     * @param string sql
     * @param vararg arguments (for sprintf)
     * @return mixed
     */
    protected function query_one ( $sql )
    {
        $sql = $this->sql_printf(func_get_args());
        if (!($res = mysql_query($sql, $this->conn)))
        {
            $this->sql_errcheck($sql);
        }
        $val = @mysql_result($res, 0, 0);
        mysql_free_result($res);
        return $val;
    }
    
    
    /**
     * Return the number of rows affected in the last query
     */
    protected function query_affected_rows ()
    {
        return mysql_affected_rows($this->conn);
    }


    /**
     * Return the id of the last inserted row
     * 
     * @return int
     */
    protected function query_insert_id ()
    {
        return mysql_insert_id($this->conn);
    }
    
    
    protected function sql_printf ( $args )
    {
        $sql  = array_shift($args);
        if (count($args) == 1 && is_array($args[0]))
        {
            $args = $args[1];
        }
        $args = array_map(array($this, 'sql_escape_string'), $args);
        return vsprintf($sql, $args);
    }
    
    
    protected function sql_escape_string ( $s )
    {
        if (is_string($s))
        {
            return mysql_real_escape_string($s, $this->conn);
        }
        else if (is_null($s))
        {
            return NULL;
        }
        else if (is_bool($s))
        {
            return intval($s);
        }
        else if (is_int($s) || is_float($s))
        {
            return $s;
        }
        else
        {
            return mysql_real_escape_string(strval($s), $this->conn);
        }
    }
    
    
    protected function sql_errcheck ( $sql )
    {
        if (mysql_errno($this->conn))
        {            
//            die($sql . "\n\n" . mysql_error());
            throw new OAuthException('SQL error');
        }
    }

    /** 
     * Get information about a consumer from the osr_id (see modified getConsumerRequestToken())
     */
    public function getConsumerInfo($osr_id) {
        global $CONFIG;
        $rs = $this->query_all_assoc('
                SELECT    osr_usa_id_ref            as requester_id,
                        osr_consumer_key         as consumer_key,
                        osr_consumer_secret        as consumer_secret,
                        osr_enabled                as enabled,
                        osr_status                 as status,
                        osr_issue_date            as issue_date,
                        osr_application_title    as application_title,
                        osr_application_descr    as application_descr,
                        osr_requester_name        as requester_name,
                        osr_requester_email        as requester_email
                FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_registry
                WHERE osr_id = %d
                ', $osr_id);
        return $rs;
    }

    public function checkTokenExpired($user_id, $token, $timestamp) {        
        global $CONFIG;

        if (!$token) {
            return;
        }

        $r = $this->query_row('
                            SELECT MAX(ost_timestamp)
                            FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
                            WHERE ost_usa_id_ref = \'%d\'
                            ', $user_id);

        if (time() - strtotime($r[0]) > 86400) {
            throw new OAuthException('Token "' . $token . '" was issued over 24 hours ago and is now expired.');
        }
    }

    public function lookup_id($token) {
        global $CONFIG;
        $r = $this->query_row('
                            SELECT ost_usa_id_ref as user
                            FROM ' . $CONFIG['TABLE_PREFIX'] . 'oauth_token
                            WHERE ost_token = \'%s\'
                            ', $token);
        if ($r) {
            return $r['user'];
        }

        else {
            //throw new OAuthException('No use associated with token "' . $token . '"');
            return false;
        }
    }

    public function group_info($user_id) {
        global $CONFIG;
        $r = $this->query_row('
                            SELECT user_group
                            FROM ' . $CONFIG['TABLE_PREFIX'] . 'users
                            WHERE user_id = \'%d\'
                            ', $user_id);
        if ($r['user_group']) {
            $group_info = $this->query_row_assoc('
                                SELECT     can_upload_pictures AS can_upload,
                                        has_admin_access AS admin_access
                                FROM ' . $CONFIG['TABLE_PREFIX'] . 'usergroups
                                WHERE group_id = \'%d\'
                                ', $r['user_group']);
            return $group_info;
        }

        else {            
            return false;
        }
    }
}


/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>