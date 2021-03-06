<?php

/**
 * Log OAuth requests
 * 
 * @version $Id: OAuthRequestLogger.php 5 2008-02-13 12:29:12Z marcw@pobox.com $
 * @author Marc Worrell <marc@mediamatic.nl>
 * @copyright (c) 2007 Mediamatic Lab
 * @date  Dec 7, 2007 12:22:43 PM
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

class OAuthRequestLogger 
{
	static private $logging			= 0;
	static private $enable_logging	= null;
	static private $store_log		= null;
	static private $note 			= '';
	static private $user_id			= null;
	static private $request_object	= null;
	static private $sent			= null;
	static private $received		= null;
	static private $log				= array();
	
	/**
	 * Start any logging, checks the system configuration if logging is needed.
	 * 
	 * @param OAuthRequest  $request_object
	 */
	static function start ( $request_object = null )
	{
		if (defined('OAUTH_LOG_REQUEST'))
		{
			if (is_null(OAuthRequestLogger::$enable_logging))
			{
				OAuthRequestLogger::$enable_logging = true;
			}
			if (is_null(OAuthRequestLogger::$store_log))
			{
				OAuthRequestLogger::$store_log = true;
			}
		}

		if (OAuthRequestLogger::$enable_logging && !OAuthRequestLogger::$logging)
		{
			OAuthRequestLogger::$logging        = true;
			OAuthRequestLogger::$request_object = $request_object;
			ob_start();

			// Make sure we flush our log entry when we stop the request (eg on an exception)
			register_shutdown_function(array('OAuthRequestLogger','flush'));		
		}
	}
	

	/**
	 * Force logging, needed for performing test connects independent from the debugging setting.
	 * 
	 * @param boolean  store_log		(optional) true to store the log in the db
	 */
	static function enableLogging ( $store_log = null )
	{
		OAuthRequestLogger::$enable_logging = true;
		if (!is_null($store_log))
		{
			OAuthRequestLogger::$store_log = $store_log;
		}
	}	


	/**
	 * Logs the request to the database, sends any cached output.
	 * Also called on shutdown, to make sure we always log the request being handled.
	 */
	static function flush ()
	{
		if (OAuthRequestLogger::$logging)
		{
			OAuthRequestLogger::$logging = false;

			if (is_null(OAuthRequestLogger::$sent))
			{
				// What has been sent to the user-agent?
				$data  = ob_get_contents();
				if (strlen($data) > 0)
				{
					ob_end_flush();
				}
				else
				{
					ob_end_clean();
				}
				$hs    = headers_list();
				$sent  = implode("\n", $hs) . "\n\n" . $data;
			}
			else
			{
				// The request we sent
				$sent  = OAuthRequestLogger::$sent;
			}
			
			if (is_null(OAuthRequestLogger::$received))
			{
				// Build the request we received
				$hs0   = getallheaders();
				$hs    = array();
				foreach ($hs0 as $h => $v)
				{
					$hs[] = "$h: $v";
				}

				$data  = '';
				$fh    = @fopen('php://input', 'r');
				if ($fh)
				{
					while (!feof($fh))
					{
						$s = fread($fh, 1024);
						if (is_string($s))
						{
							$data .= $s;
						}
					}
					fclose($fh);
				}
				$received = implode("\n", $hs) . "\n\n" . $data;
			}
			else
			{
				// The answer we received
				$received  = OAuthRequestLogger::$received;
			}

			// The request base string
			if (OAuthRequestLogger::$request_object)
			{
				$base_string = OAuthRequestLogger::$request_object->signatureBaseString();
			}
			else
			{
				$base_string = '';
			}

			// Figure out to what keys we want to log this request
			$keys = array();
			if (OAuthRequestLogger::$request_object)
			{
				$consumer_key = OAuthRequestLogger::$request_object->getParam('oauth_consumer_key', true);
				$token        = OAuthRequestLogger::$request_object->getParam('oauth_token',        true);

				switch (get_class(OAuthRequestLogger::$request_object))
				{
				// tokens are access/request tokens by a consumer
				case 'OAuthServer':
				case 'OAuthRequestVerifier':
					$keys['ocr_consumer_key'] = $consumer_key;
					$keys['oct_token']        = $token;
					break;

				// tokens are access/request tokens to a server
				case 'OAuthRequester':
				case 'OAuthRequestSigner':
					$keys['osr_consumer_key'] = $consumer_key;
					$keys['ost_token']        = $token;
					break;
				}
			}
			
			// Log the request
			if (OAuthRequestLogger::$store_log)
			{
				$store = OAuthStore::instance();
				$store->addLog($keys, $received, $sent, $base_string, OAuthRequestLogger::$note, OAuthRequestLogger::$user_id);
			}
			
			OAuthRequestLogger::$log[] = array(
					'keys'    		=> $keys,
					'received'		=> $received,
					'sent'			=> $sent,
					'base_string'	=> $base_string,
					'note'			=> OAuthRequestLogger::$note
					);
		}
	}


	/**
	 * Add a note, used by the OAuthException to log all exceptions.
	 * 
	 * @param string note
	 */
	static function addNote ( $note )
	{
		OAuthRequestLogger::$note .= $note . "\n\n";
	}

	/**
	 * Set the OAuth request object being used
	 * 
	 * @param OAuthRequest request_object
	 */
	static function setRequestObject ( $request_object )
	{
		OAuthRequestLogger::$request_object = $request_object;
	}


	/**
	 * Set the relevant user (defaults to the current user)
	 * 
	 * @param int user_id
	 */
	static function setUser ( $user_id )
	{
		OAuthRequestLogger::$user_id = $user_id;
	}
	
	
	/**
	 * Set the request we sent
	 * 
	 * @param string request
	 */
	static function setSent ( $request )
	{
		OAuthRequestLogger::$sent = $request;
	}

	/**
	 * Set the reply we received
	 * 
	 * @param string request
	 */
	static function setReceived ( $reply )
	{
		OAuthRequestLogger::$received = $reply;
	}
	
	
	/**
	 * Get the the log till now
	 * 
	 * @return array
	 */
	static function getLog ()
	{
		return OAuthRequestLogger::$log;
	}

	static function finish ()
	{
		// This function was undefined, but called in a couple of places.
	}
}

/* vi:set ts=4 sts=4 sw=4 binary noeol: */

?>