<?php
/**
 * Net_IMAP provides an implementation of the IMAP protocol
 *
 * PHP Version 4
 *
 * @category  Networking
 * @package   Net_IMAP
 * @author    Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 * @copyright 1997-2003 The PHP Group
 * @license   PHP license
 * @version   CVS: $Id: IMAP.php 7 2010-01-22 18:14:51Z acio $
 * @link      http://pear.php.net/package/Net_IMAP
 */

/**
 * IMAPProtocol.php holds protocol implementation for IMAP.php
 */
require_once 'Net/IMAPProtocol.php';


/**
 * Provides an implementation of the IMAP protocol using PEAR's
 * Net_Socket:: class.
 *
 * @category Networking
 * @package  Net_IMAP
 * @author   Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 * @license  PHP license
 * @link     http://pear.php.net/package/Net_IMAP
 */
class Net_IMAP extends Net_IMAPProtocol
{
    /**
     * Constructor
     *
     * Instantiates a new Net_SMTP object, overriding any defaults
     * with parameters that are passed in.
     *
     * @param string $host           The server to connect to.
     * @param int    $port           The port to connect to.
     * @param bool   $enableSTARTTLS Enable STARTTLS support
     */
    function Net_IMAP(&$ret = null, $host = 'localhost', $port = 143, $enableSTARTTLS = true, $options = null)
    {
        $this->Net_IMAPProtocol();
        if ($options) $this->setStreamContextOptions($options);
        $ret = $this->connect($host, $port, $enableSTARTTLS);
    }



    /**
     * Attempt to connect to the IMAP server located at $host $port
     *
     * @param string $host           The IMAP server
     * @param string $port           The IMAP port
     * @param bool   $enableSTARTTLS enable STARTTLS support
     *
     *          It is only useful in a very few circunstances
     *          because the contructor already makes this job
     *
     * @return true on success or PEAR_Error
     *
     * @access  public
     * @since   1.0
     */
    function connect($host, $port, $enableSTARTTLS = true)
    {
        $ret = $this->cmdConnect($host, $port);
        if ($ret === true) {
            // Determine server capabilities
            $res = $this->cmdCapability();

            // check if we can enable TLS via STARTTLS 
            // (requires PHP 5 >= 5.1.0RC1 for stream_socket_enable_crypto)
            if ($this->hasCapability('STARTTLS') === true 
                && $enableSTARTTLS === true 
                && function_exists('stream_socket_enable_crypto') === true) {
                if (PEAR::isError($res = $this->cmdStartTLS())) {
                    return $res;
                }
            }
            return $ret;
        }
        if (empty($ret)) {
            return new PEAR_Error("Unexpected response on connection");
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (isset($ret['RESPONSE']['CODE'])) {
            if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
                return new PEAR_Error($ret['RESPONSE']['CODE']
                                      . ', ' 
                                      . $ret['RESPONSE']['STR_CODE']);
            }
        }

        return $ret;
    }



    /**
     * Attempt to authenticate to the IMAP server.
     *
     * @param string  $user            The userid to authenticate as.
     * @param string  $pass            The password to authenticate with.
     * @param string  $useauthenticate true: authenticate using
     *        the IMAP AUTHENTICATE command. false: authenticate using
     *        the IMAP AUTHENTICATE command. 'string': authenticate using
     *        the IMAP AUTHENTICATE command but using the authMethod in 'string'
     * @param boolean $selectMailbox   automaticaly select inbox on login 
     *        (false does not)
     *
     * @return  true on success or PEAR_Error
     *
     * @access  public
     * @since   1.0
     */
    function login($user, $pass, $useauthenticate = true, $selectMailbox=true)
    {
        if ($useauthenticate) {
            // $useauthenticate is a string if the user hardcodes an AUTHMethod
            // (the user calls $imap->login("user","password","CRAM-MD5"); for 
            // example!

            if (is_string($useauthenticate)) {
                $method = $useauthenticate;
            } else {
                $method = null;
            }

            //Try the selected Auth method
            if (PEAR::isError($ret = $this->cmdAuthenticate($user, 
                                                            $pass, 
                                                            $method))) {
                // Verify the methods that we have in common with the server
                if (is_array($this->_serverAuthMethods)) {
                    $commonMethods = array_intersect($this->supportedAuthMethods, $this->_serverAuthMethods);
                } else {
                    $this->_serverAuthMethods = null;
                }
                if ($this->_serverAuthMethods == null 
                    || count($commonMethods) == 0 
                    || $this->supportedAuthMethods == null) {
                    // The server does not have any auth method, so I try LOGIN
                    if ( PEAR::isError($ret = $this->cmdLogin($user, $pass))) {
                        return $ret;
                    }
                } else {
                    return $ret;
                }
            }
            if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
                return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                      . ', ' 
                                      . $ret['RESPONSE']['STR_CODE']);
            }
        } else {
            //The user request "PLAIN"  auth, we use the login command
            if (PEAR::isError($ret = $this->cmdLogin($user, $pass))) {
                return $ret;
            }
            if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
                return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                      . ', ' 
                                      . $ret['RESPONSE']['STR_CODE']);
            }
        }

        if ($selectMailbox) {
            //Select INBOX
            if (PEAR::isError($ret = $this->cmdSelect($this->getCurrentMailbox()))) {
                return $ret;
            }
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Disconnect function. Sends the QUIT command
     * and closes the socket.
     *
     * @param boolean $expungeOnExit (default = false)
     *
     * @return  mixed   true on success / Pear_Error on failure
     *
     * @access  public
     */
    function disconnect($expungeOnExit = false)
    {
        if ($expungeOnExit) {
            if (PEAR::isError($ret = $this->cmdExpunge())) {
                return $ret;
            }
            if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
                $ret = $this->cmdLogout();
                return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                      . ', ' 
                                      . $ret['RESPONSE']['STR_CODE']);
            }
        }

        if (PEAR::isError($ret = $this->cmdLogout())) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }



    /**
     * Changes the default/current mailbox to $mailbox
     *
     * @param string $mailbox Mailbox to select
     *
     * @return mixed true on success / Pear_Error on failure
     *
     * @access public
     */
    function selectMailbox($mailbox)
    {
        if (PEAR::isError($ret = $this->cmdSelect($mailbox))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Checks the mailbox $mailbox
     *
     * @param string $mailbox Mailbox to examine
     *
     * @return mixed true on success / Pear_Error on failure
     *
     * @access public
     */
    function examineMailbox($mailbox)
    {
        if (PEAR::isError($ret = $this->cmdExamine($mailbox))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        //$ret_aux["EXISTS"]=$ret["PARSED"]["EXISTS"];
        //$ret_aux["RECENT"]=$ret["PARSED"]["RECENT"];
        $ret = $ret['PARSED'];
        return $ret;
    }



    /**
     * Returns the raw headers of the specified message.
     *
     * @param int     $msg_id   Message number
     * @param string  $part_id  Part ID
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed Either raw headers or false on error
     *
     * @access public
     */
    function getRawHeaders($msg_id, $part_id = '', $uidFetch = false)
    {
        if ($part_id != '') {
            $command = 'BODY[' . $part_id . '.HEADER]';
        } else {
            $command = 'BODY[HEADER]';
        }
        if ($uidFetch == true) {
            $ret = $this->cmdUidFetch($msg_id, $command);
        } else {
            $ret = $this->cmdFetch($msg_id, $command);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret = $ret['PARSED'][0]['EXT'][$command]['CONTENT'];
        return $ret;
    }



    /**
     * Returns the  headers of the specified message in an
     * associative array. Array keys are the header names, array
     * values are the header values. In the case of multiple headers
     * having the same names, eg Received:, the array value will be
     * an indexed array of all the header values.
     *
     * @param int     $msg_id      Message number
     * @param boolean $keysToUpper false (default) original header names
     *                             true change keys (header names) toupper
     * @param string  $part_id     Part ID
     * @param boolean $uidFetch    msg_id contains UID's instead of Message 
     *                             Sequence Number if set to true
     *
     * @return mixed Either array of headers or false on error
     *
     * @access public
     */
    function getParsedHeaders($msg_id, 
                              $keysToUpper = false, 
                              $part_id = '', 
                              $uidFetch = false)
    {
        if (PEAR::isError($ret = $this->getRawHeaders($msg_id, 
                                                      $part_id, 
                                                      $uidFetch))) {
            return $ret;
        }

        $raw_headers = rtrim($ret);
        // Unfold headers
        $raw_headers = preg_replace("/\r\n[ \t]+/", ' ', $raw_headers);
        $raw_headers = explode("\r\n", $raw_headers);
        foreach ($raw_headers as $value) {
            $name = substr($value, 0, $pos = strpos($value, ':'));
            if ($keysToUpper) {
                $name = strtoupper($name);
            }
            $value = ltrim(substr($value, $pos + 1));
            if (isset($headers[$name]) && is_array($headers[$name])) {
                $headers[$name][] = $value;
            } elseif (isset($headers[$name])) {
                $headers[$name] = array($headers[$name], $value);
            } else {
                $headers[$name] = $value;
            }
        }
        return $headers;
    }



    /**
     * Returns an array containing the message ID, and the UID
     * of each message selected.
     * message selection can be a valid IMAP command, a number or an array of
     * messages
     *
     * @param mixed $msg_id Message number or an array
     *
     * @return mixed Either array of message data or PearError on error
     *
     * @access public
     */
    function getMessagesListUid($msg_id = null)
    {
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }
		
		if (PEAR::isError($ret = $this->cmdUidFetch($message_set,
				'(UID)'))) {
				return $ret;
		}
		
		if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
			return new PEAR_Error($ret['RESPONSE']['CODE']
					. ', '
					. $ret['RESPONSE']['STR_CODE']);
		}
		
		if(!isset($ret['PARSED'])) return;
		
		foreach ($ret['PARSED'] as $msg) {
			$ret_aux[] = array('msg_id' => $msg['NRO'],
					'uidl'   => $msg['EXT']['UID']);
		}
		        
        return $ret_aux;
    }
    
    
    /**
     * Returns an array containing the message ID, the size and the UID
     * of each message selected.
     * message selection can be a valid IMAP command, a number or an array of
     * messages
     *
     * @param string $msg_id Message number
     *
     * @return mixed Either array of message data or PearError on error
     *
     * @access public
     */
    function getMessagesList($msg_id = null)
    {
    	if ($msg_id != null) {
    		if (is_array($msg_id)) {
    			$message_set = $this->_getSearchListFromArray($msg_id);
    		} else {
    			$message_set = $msg_id;
    		}
    	} else {
    		$message_set = '1:*';
    	}
    	
    	if (PEAR::isError($ret = $this->cmdFetch($message_set,
    			'(RFC822.SIZE UID)'))) {
    			return $ret;
    	}
    		 
    	if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
    		return new PEAR_Error($ret['RESPONSE']['CODE']
    			. ', '
    			. $ret['RESPONSE']['STR_CODE']);
    	}
    	foreach ($ret['PARSED'] as $msg) {
    		$ret_aux[] = array('msg_id' => $msg['NRO'],
    				'size'   => $msg['EXT']['RFC822.SIZE'],
    				'uidl'   => $msg['EXT']['UID']);
    	}
    	    
    	return $ret_aux;
    }



    /**
     * Message summary
     *
     * @param mixed   $msg_id   Message number
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed Either array of headers or PEAR::Error on error
     *
     * @access public
     */
    function getSummary($msg_id = null, $uidFetch = false)
    {
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }
        if ($uidFetch) {
            $ret = $this->cmdUidFetch($message_set,
                                      '(RFC822.SIZE UID FLAGS ENVELOPE INTERNALDATE BODY.PEEK[HEADER.FIELDS (CONTENT-TYPE)])');
        } else {
            $ret = $this->cmdFetch($message_set,
                                   '(RFC822.SIZE UID FLAGS ENVELOPE INTERNALDATE BODY.PEEK[HEADER.FIELDS (CONTENT-TYPE)])');
        }
        // $ret=$this->cmdFetch($message_set,"(RFC822.SIZE UID FLAGS ENVELOPE INTERNALDATE BODY[1.MIME])");
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        // print "<hr>"; var_dump($ret["PARSED"]); print "<hr>";

        if (isset($ret['PARSED'])) {
            for ($i=0; $i<count($ret['PARSED']); $i++) {
                $a                 = isset($ret['PARSED'][$i]['EXT']['ENVELOPE']) ? $ret['PARSED'][$i]['EXT']['ENVELOPE'] : null;
                $a['MSG_NUM']      = isset($ret["PARSED"][$i]['NRO']) ? $ret["PARSED"][$i]['NRO'] : null;
                $a['UID']          = isset($ret["PARSED"][$i]['EXT']['UID']) ? $ret["PARSED"][$i]['EXT']['UID'] : null;
                $a['FLAGS']        = isset($ret["PARSED"][$i]['EXT']['FLAGS']) ? $ret["PARSED"][$i]['EXT']['FLAGS'] : null;
                $a['INTERNALDATE'] = isset($ret["PARSED"][$i]['EXT']['INTERNALDATE']) ? $ret["PARSED"][$i]['EXT']['INTERNALDATE'] : null;
                $a['SIZE']         = isset($ret["PARSED"][$i]['EXT']['RFC822.SIZE']) ? $ret["PARSED"][$i]['EXT']['RFC822.SIZE'] : null;
                if (isset($ret['PARSED'][$i]['EXT']['BODY[HEADER.FIELDS (CONTENT-TYPE)]']['CONTENT'])) {
                    if (preg_match('/^content-type: (.*);/iU', $ret['PARSED'][$i]['EXT']['BODY[HEADER.FIELDS (CONTENT-TYPE)]']['CONTENT'], $matches)) {
                        $a['MIMETYPE'] = strtolower($matches[1]);
                    }
                } elseif (isset($ret['PARSED'][$i]['EXT']['BODY[HEADER.FIELDS ("CONTENT-TYPE")]']['CONTENT'])) {
                    // some versions of cyrus send "CONTENT-TYPE" and CONTENT-TYPE only
                    if (preg_match('/^content-type: (.*);/iU', $ret['PARSED'][$i]['EXT']['BODY[HEADER.FIELDS ("CONTENT-TYPE")]']['CONTENT'], $matches)) {
                        $a['MIMETYPE'] = strtolower($matches[1]);
                    }
                }
                $env[] = $a;
                $a     = null;
            }
            return $env;
        }

        //return $ret;
    }



    /**
     * Returns the body of the message with given message number.
     *
     * @param string  $msg_id   Message number
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     * @param string  $partId   Part ID
     *
     * @return mixed Either message body or PEAR_Error on failure
     * @access public
     */
    function getBody($msg_id, $uidFetch = false, $partId = '')
    {
        if ($partId != '') {
            $partId = '.' . strtoupper($partId);
        }
        if ($uidFetch) {
            $ret = $this->cmdUidFetch($msg_id, 'BODY' . $partId . '[TEXT]');
        } else {
            $ret = $this->cmdFetch($msg_id, 'BODY' . $partId . '[TEXT]');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret = $ret['PARSED'][0]['EXT']['BODY[TEXT]']['CONTENT'];
        // $ret=$resp["PARSED"][0]["EXT"]["RFC822"]["CONTENT"];
        return $ret;
    }


    /**
     * Returns the body of the message with given message number.
     *
     * @param int     $msg_id   Message number
     * @param string  $partId   Message number
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed Either message body or false on error
     *
     * @access public
     */
    function getBodyPart($msg_id, $partId, $uidFetch = false)
    {
        if ($uidFetch) {
            $ret = $this->cmdUidFetch($msg_id, 'BODY[' . $partId . ']');
        } else {
            $ret = $this->cmdFetch($msg_id, 'BODY[' . $partId . ']');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret = $ret['PARSED'][0]['EXT']['BODY[' . $partId . ']']['CONTENT'];
        //$ret=$resp["PARSED"][0]["EXT"]["RFC822"]["CONTENT"];
        return $ret;
    }



    /**
     * Returns the body of the message with given message number.
     *
     * @param int     $msg_id   Message number
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed Either message body or false on error
     *
     * @access public
     */
    function getStructure($msg_id, $uidFetch = false)
    {
        // print "IMAP.php::getStructure<pre>";
        // $this->setDebug(true);
        // print "<pre>";
        if ($uidFetch) {
            $ret = $this->cmdUidFetch($msg_id, 'BODYSTRUCTURE');
        } else {
            $ret = $this->cmdFetch($msg_id, 'BODYSTRUCTURE');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret = $ret['PARSED'][0]['EXT']['BODYSTRUCTURE'][0];

        $mimeParts = array();
        $this->_parseStructureArray($ret, $mimeParts);

        return array_shift($mimeParts);
    }


    /**
     * Parse structure array
     *
     * @param array $_structure  Structure array
     * @param array &$_mimeParts Mime parts
     * @param int   $_partID     Part ID
     *
     * @return nothing
     *
     * @access private
     */
    function _parseStructureArray($_structure, &$_mimeParts, $_partID = '') 
    {
        // something went wrong
        if (!is_array($_structure)) {
            return false;
        }

        // print "Net_IMAP::_parseStructureArray _partID: $_partID";
        $mimeParts = array();
        $subPartID = 1;
        if ($_partID == '') {
            $partID = '';
        } else {
            $partID = $_partID.'.';
        }
        if (is_array($_structure[0])) {
            $this->_parseStructureMultipartArray($_structure, $_mimeParts, $_partID);
        } else {
            switch (strtoupper($_structure[0])) {
            case 'TEXT':
                $this->_parseStructureTextArray($_structure, 
                                                $_mimeParts, 
                                                $partID . $subPartID);
                break;

            case 'MESSAGE':
                $this->_parseStructureMessageArray($_structure, 
                                                   $_mimeParts, 
                                                   $partID . $subPartID);
                break;

            default:
                $this->_parseStructureApplicationArray($_structure, 
                                                       $_mimeParts, 
                                                       $partID . $subPartID);
                break;
            }
        }
    }
    


    /**
     * Parse multibpart structure array
     *
     * @param array   $_structure       Structure array
     * @param array   &$_mimeParts      Mime parts
     * @param int     $_partID          Part ID
     * @param boolean $_parentIsMessage Parent is message/rfc822
     *
     * @return noting
     *
     * @access private
     */
    function _parseStructureMultipartArray($_structure, 
                                           &$_mimeParts, 
                                           $_partID, 
                                           $_parentIsMessage = false) 
    {
        // a multipart/mixed, multipart/report or multipart/alternative 
        // or multipart/related get's no own partid, if the parent 
        // is message/rfc822
        if ($_parentIsMessage == true && is_array($_structure[0])) {
            foreach ($_structure as $structurePart) {
                if (!is_array($structurePart)) {
                    $subType = strtolower($structurePart);
                    break;
                }
            }
            if ($subType == 'mixed' 
                || $subType == 'report' 
                || $subType == 'alternative' 
                || $subType == 'related') {
                $_partID = substr($_partID, 0, strrpos($_partID, '.'));
            }
        }
        
        $subPartID = 1;
        if ($_partID == '') {
            $partID = '';
        } else {
            $partID = $_partID . '.';
        }
        $subMimeParts = array();
        foreach ($_structure as $structurePart) {
            if (is_array($structurePart)) {
                if (is_array($structurePart[0])) {
                    // another multipart inside the multipart
                    $this->_parseStructureMultipartArray($structurePart, 
                                                         $subMimeParts, 
                                                         $partID . $subPartID);
                } else {
                    switch(strtoupper($structurePart[0])) {
                    case 'IMAGE':
                        $this->_parseStructureImageArray($structurePart, 
                                                         $subMimeParts, 
                                                         $partID . $subPartID);
                        break;
                    case 'MESSAGE':
                        $this->_parseStructureMessageArray($structurePart, 
                                                           $subMimeParts, 
                                                           $partID . $subPartID);
                        break;
                    case 'TEXT':
                        $this->_parseStructureTextArray($structurePart, 
                                                        $subMimeParts, 
                                                        $partID . $subPartID);
                        break;
                    default:
                        $this->_parseStructureApplicationArray($structurePart, 
                                                               $subMimeParts, 
                                                               $partID . $subPartID);
                        break;
                    }
                }
                $subPartID++;
            } else {
                $part           = new stdClass;
                $part->type     = 'MULTIPART';
                $part->subType  = strtoupper($structurePart);
                $part->subParts = $subMimeParts;
          
                if ($_partID == '') {
                    $part->partID = 0;
                    $_mimeParts   = array(0 => $part);
                } else {
                    $part->partID         = $_partID;
                    $_mimeParts[$_partID] = $part;
                }
                return;
            }
        }
    }



    /**
     * Parse structure image array
     *
     * @param array $_structure  Structure array
     * @param array &$_mimeParts Mime parts
     * @param int   $_partID     Part ID
     *
     * @return noting
     *
     * @access private
     */
    function _parseStructureImageArray($_structure, &$_mimeParts, $_partID) 
    {
        // print "Net_IMAP::_parseStructureImageArray _partID: $_partID<br>";
        $part         = $this->_parseStructureCommonFields($_structure);
        $part->cid    = $_structure[3];
        $part->partID = $_partID;
        
        $_mimeParts[$_partID] = $part;
    }


    
    /**
     * Parse structure application array
     *
     * @param array $_structure  Structure array
     * @param array &$_mimeParts Mime parts
     * @param int   $_partID     Part ID
     *
     * @return noting
     *
     * @access private
     */
    function _parseStructureApplicationArray($_structure, 
                                             &$_mimeParts, 
                                             $_partID) 
    {
        // print "Net_IMAP::_parseStructureApplicationArray _partID: $_partID<br>";
        $part = $this->_parseStructureCommonFields($_structure);
        if (is_array($_structure[8])) {
            if (isset($_structure[8][0]) && $_structure[8][0] != 'NIL') {
                $part->disposition = strtoupper($_structure[8][0]);
            }
            if (is_array($_structure[8][1])) {
                foreach ($_structure[8][1] as $key => $value) {
                    if ($key%2 == 0) {
                        $part->dparameters[strtoupper($_structure[8][1][$key])] = $_structure[8][1][$key+1];
                    }
                }
            }
        }
        $part->partID = $_partID;
        
        $_mimeParts[$_partID] = $part;
    }



    /**
     * Parse structure message array
     *
     * @param array $_structure  Structure array
     * @param array &$_mimeParts Mime parts
     * @param int   $_partID     Part ID
     *
     * @return nothing
     *
     * @access private
     */
    function _parseStructureMessageArray($_structure, &$_mimeParts, $_partID)
    {
        // print "Net_IMAP::_parseStructureMessageArray _partID: $_partID<br>";
        $part = $this->_parseStructureCommonFields($_structure);
        
        if (is_array($_structure[8][0])) {
            $this->_parseStructureMultipartArray($_structure[8], 
                                                 $subMimeParts, 
                                                 $_partID . '.1', 
                                                 true);
        } else {
            $this->_parseStructureArray($_structure[8], 
                                        $subMimeParts, 
                                        $_partID);
        }
        
        if (is_array($subMimeParts)) {
            $part->subParts = $subMimeParts;
        }
        $part->partID = $_partID;

        $_mimeParts[$_partID] = $part;
    }
    


    /**
     * Parse structure text array
     *
     * @param array $_structure  Structure array
     * @param array &$_mimeParts Mime parts
     * @param int   $_partID     Part ID
     *
     * @return nothing
     *
     * @access private
     */
    function _parseStructureTextArray($_structure, &$_mimeParts, $_partID) 
    {
        // print "Net_IMAP::_parseStructureTextArray _partID: $_partID<br>";
        $part        = $this->_parseStructureCommonFields($_structure);
        $part->lines = $_structure[7];

        if (is_array($_structure[8])) {
            if (isset($_structure[8][0]) && $_structure[8][0] != 'NIL') {
                $part->disposition = strtoupper($_structure[8][0]);
            }
            if (is_array($_structure[8][1])) {
                foreach ($_structure[8][1] as $key => $value) {
                    if ($key%2 == 0) {
                        $part->dparameters[strtoupper($_structure[8][1][$key])] = $_structure[8][1][$key+1];
                    }
                }
            }
        }

        if (is_array($_structure[9])) {
            if (isset($_structure[9][0]) && $_structure[9][0] != 'NIL') {
                $part->disposition = strtoupper($_structure[9][0]);
            }
            if (is_array($_structure[9][1])) {
                foreach ($_structure[9][1] as $key => $value) {
                    if ($key%2 == 0) {
                        $part->dparameters[strtoupper($_structure[9][1][$key])] = $_structure[9][1][$key+1];
                    }
                }
            }
        }

        $part->partID = $_partID;
        
        $_mimeParts[$_partID] = $part;
    }



    /**
     * Parse structure common fields
     *
     * @param array &$_structure Structure array
     *
     * @return object part object (stdClass)
     *
     * @access private
     */
    function _parseStructureCommonFields(&$_structure) 
    {
        // print "Net_IMAP::_parseStructureTextArray _partID: $_partID<br>";
        $part          = new stdClass;
        $part->type    = strtoupper($_structure[0]);
        $part->subType = strtoupper($_structure[1]);
        
        if (is_array($_structure[2])) {
            foreach ($_structure[2] as $key => $value) {
                if ($key%2 == 0) {
                    $part->parameters[strtoupper($_structure[2][$key])] = $_structure[2][$key+1];
                }
            }
        }
        $part->encoding = strtoupper($_structure[5]);
        $part->bytes    = $_structure[6];
        
        return $part;
    }



    /**
     * Returns the entire message with given message number.
     *
     * @param int     $msg_id               Message number (default = null)
     * @param boolean $indexIsMessageNumber true if index is a message number
     *                                      (default = true)
     *
     * @return mixed  Either entire message or false on error
     *
     * @access public
     */
    function getMessages($msg_id = null, $indexIsMessageNumber=true)
    {
        // $resp=$this->cmdFetch($msg_id,"(BODY[TEXT] BODY[HEADER])");
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }

        if (!$indexIsMessageNumber) {
            $ret = $this->cmdUidFetch($message_set, 'RFC822');
        } else {
            $ret = $this->cmdFetch($message_set, 'RFC822');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $msg) {
                if (isset($msg['EXT']['RFC822']['CONTENT'])) {
                    if ($indexIsMessageNumber) {
                        $ret_aux[$msg['NRO']] = $msg['EXT']['RFC822']['CONTENT'];
                    } else {
                        $ret_aux[] = $msg['EXT']['RFC822']['CONTENT'];
                    }
                }
            }
            return $ret_aux;
        }
        return array();
    }



    /**
     * Returns number of messages in this mailbox
     *
     * @param string $mailbox the mailbox (default is current mailbox)
     *
     * @return mixed Either number of messages or Pear_Error on failure
     *
     * @access public
     */
    function getNumberOfMessages($mailbox = '')
    {
        if ($mailbox == '' || $mailbox == null) {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdStatus($mailbox, 'MESSAGES'))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'])) {
                // if this array does not exists means that there is no 
                // messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'];
            }

        }
        return 0;
    }



    /**
     * Returns number of UnSeen messages in this mailbox
     *
     * @param string $mailbox the mailbox (default is current mailbox)
     *
     * @return mixed Either number of messages or Pear_Error on failure
     *
     * @access public
     */
    function getNumberOfUnSeenMessages($mailbox = '')
    {
        if ($mailbox == '' ) {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdStatus($mailbox, 'UNSEEN'))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret["RESPONSE"]["CODE"] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'])) {
                // if this array does not exists means that there is no messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'];
            }

        }
        return 0;
    }



    /**
     * Returns number of UnSeen messages in this mailbox
     *
     * @param string $mailbox the mailbox (default is current mailbox)
     *
     * @return mixed Either number of messages or Pear_Error on failure
     *
     * @access public
     */
    function getNumberOfRecentMessages($mailbox = '')
    {
        if ($mailbox == '' ) {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdStatus($mailbox, 'RECENT'))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'])) {
                // if this array does not exists means that there is no messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'];
            }

        }
        return 0;
    }



    /**
     * Returns number of UnSeen messages in this mailbox
     *
     * @param string $mailbox the mailbox (default is current mailbox)
     *
     * @return mixed Either number of messages or Pear_Error on error
     *
     * @access public
     */
    function getStatus($mailbox = '')
    {
        if ($mailbox == '') {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdStatus($mailbox, array('MESSAGES', 'RECENT', 'UIDNEXT', 'UIDVALIDITY', 'UNSEEN')))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'])) {
            return $ret['PARSED']['STATUS']['ATTRIBUTES'];
        }
        return 0;
    }



    /**
     * Returns an array containing the message envelope
     *
     * @param string  $mailbox  get's not used anywhere (will be removed 
     *                          with next major release)
     * @param mixed   $msg_id   Message number (default = null)
     * @param boolean $uidFetch msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed Either the envelopes or Pear_Error on error
     *
     * @access public
     */
    function getEnvelope($mailbox = '', $msg_id = null, $uidFetch = false)
    {
        if ($mailbox == '') {
            $mailbox = $this->getCurrentMailbox();
        }

        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }


        if ($uidFetch) {
            $ret = $this->cmdUidFetch($message_set, 'ENVELOPE');
        } else {
            $ret = $this->cmdFetch($message_set, 'ENVELOPE');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED'])) {
            for ($i=0; $i<count($ret['PARSED']); $i++) {
                $a            = $ret['PARSED'][$i]['EXT']['ENVELOPE'];
                $a['MSG_NUM'] = $ret['PARSED'][$i]['NRO'];
                $env[]        = $a;
            }
            return $env;
        }

        return new PEAR_Error('Error, undefined number of messages');
    }



    /**
     * Returns the sum of all the sizes of messages in $mailbox
     *           WARNING!!!  The method's performance is not good
     *                       if you have a lot of messages in the mailbox
     *                       Use with care!
     *
     * @param string $mailbox The mailbox (default is current mailbox)
     *
     * @return mixed Either size of maildrop or false on error
     *
     * @access public
     */
    function getMailboxSize($mailbox = '')
    {

        if ($mailbox != '' && $mailbox != $this->getCurrentMailbox()) {
            // store the actual selected mailbox name
            $mailbox_aux = $this->getCurrentMailbox();
            if (PEAR::isError($ret = $this->selectMailbox($mailbox))) {
                return $ret;
            }
        }

        $ret = $this->cmdFetch('1:*', 'RFC822.SIZE');
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            // Restore the default mailbox if it was changed
            if ($mailbox != '' && $mailbox != $this->getCurrentMailbox()) {
                if (PEAR::isError($ret = $this->selectMailbox($mailbox_aux))) {
                        return $ret;
                }
            }
            // return 0 because the server says that there is no message 
            // in the mailbox
            return 0;
        }

        $sum = 0;

        if (!isset($ret['PARSED'])) {
            // if the server does not return a "PARSED"  part
            // we think that it does not suppoprt select or has no messages 
            // in it.
            return 0;
        }
        foreach ($ret['PARSED'] as $msgSize) {
            if (isset($msgSize['EXT']['RFC822.SIZE'])) {
                $sum += $msgSize['EXT']['RFC822.SIZE'];
            }
        }

        if ($mailbox != '' && $mailbox != $this->getCurrentMailbox()) {
            // re-select the mailbox
            if (PEAR::isError($ret = $this->selectMailbox($mailbox_aux))) {
                return $ret;
            }
        }

        return $sum;
    }



    /**
     * Marks a message for deletion. Only will be deleted if the
     * disconnect() method is called with auto-expunge on true or expunge()
     * method is called.
     *
     * @param int     $msg_id   Message to delete (default = null)
     * @param boolean $uidStore msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true (default = false)
     *
     * @return mixed true on success / Pear_Error on failure
     *
     * @access public
     */
    function deleteMessages($msg_id = null, $uidStore = false)
    {
        /* As said in RFC2060...
        C: A003 STORE 2:4 +FLAGS (\Deleted)
                S: * 2 FETCH FLAGS (\Deleted \Seen)
                S: * 3 FETCH FLAGS (\Deleted)
                S: * 4 FETCH FLAGS (\Deleted \Flagged \Seen)
                S: A003 OK STORE completed
        */
        // Called without parammeters deletes all the messages in the mailbox
        // You can also provide an array of numbers to delete those emails
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }


        $dataitem = '+FLAGS.SILENT';
        $value    = '\Deleted';
        if ($uidStore == true) {
            $ret = $this->cmdUidStore($message_set, $dataitem, $value);
        } else {
            $ret = $this->cmdStore($message_set, $dataitem, $value);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Copies mail from one folder to another
     *
     * @param string  $dest_mailbox   mailbox name to copy sessages to
     * @param mixed   $msg_id         the messages that I want to copy (all 
     *                                by default) it also can be an array
     * @param string  $source_mailbox mailbox name from where the messages are 
     *                                copied (default is current mailbox)
     * @param boolean $uidCopy        msg_id contains UID's instead of Message 
     *                                Sequence Number if set to true
     *
     * @return mixed true on Success/PearError on Failure
     *
     * @access  public
     * @since   1.0
     */
    function copyMessages($dest_mailbox, 
                          $msg_id = null, 
                          $source_mailbox = null, 
                          $uidCopy = false)
    {
        if ($source_mailbox == null) {
            $source_mailbox = $this->getCurrentMailbox();
        } else {
            if (PEAR::isError($ret = $this->selectMailbox($source_mailbox))) {
                return $ret;
            }
        }
        // Called without parammeters copies all messages in the mailbox
        // You can also provide an array of numbers to copy those emails
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }

        if ($uidCopy == true) {
            $ret = $this->cmdUidCopy($message_set, $dest_mailbox);
        } else {
            $ret = $this->cmdCopy($message_set, $dest_mailbox);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE']
                                  . ', '
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }



    /**
     * Appends a mail to  a mailbox
     *
     * @param string $rfc_message the message to append in RFC822 format
     * @param string $mailbox     mailbox name to append to (default is 
     *                            current mailbox)
     * @param string $flags_list  set flags appended message
     *
     * @return mixed true on success / Pear_Error on failure
     *
     * @access  public
     * @since   1.0
     */
    function appendMessage($rfc_message, $mailbox = null, $flags_list = '')
    {
        if ($mailbox == null) {
            $mailbox = $this->getCurrentMailbox();
        }
        $ret = $this->cmdAppend($mailbox, $rfc_message, $flags_list);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Returns the namespace
     *
     * @return mixed namespace or PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function getNamespace()
    {
        if (PEAR::isError($ret = $this->cmdNamespace())) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        foreach ($ret['PARSED']['NAMESPACES'] as $type => $singleNameSpace) {
            if (!is_array($singleNameSpace)) {
                continue;
            }

            foreach ($singleNameSpace as $nameSpaceData) {
                $nameSpaces[$type][] = array(
                    'name'      => $this->utf7Decode($nameSpaceData[0]),
                    'delimter'  => $this->utf7Decode($nameSpaceData[1])
                );
            }
        }
        
        return $nameSpaces;
    }



    /******************************************************************
    **                                                               **
    **           MAILBOX RELATED METHODS                             **
    **                                                               **
    ******************************************************************/

    /**
     * Gets the HierachyDelimiter character used to create subfolders  
     * cyrus users "."
     * and wu-imapd uses "/"
     *
     * @param string $mailbox The mailbox to get the hierarchy from
     *
     * @return string The hierarchy delimiter
     *
     * @access public
     * @since 1.0
     */
    function getHierarchyDelimiter($mailbox = '')
    {
        /* RFC2060 says: 
            "the command LIST "" "" means get the hierachy delimiter:
             An empty ("" string) mailbox name argument is a special request to
             return the hierarchy delimiter and the root name of the name given
             in the reference.  The value returned as the root MAY be null if
             the reference is non-rooted or is null.  In all cases, the
             hierarchy delimiter is returned.  This permits a client to get the
             hierarchy delimiter even when no mailboxes by that name currently
             exist."
        */
        if (PEAR::isError($ret = $this->cmdList($mailbox, ''))) {
            return $ret;
        }
        if (isset($ret['PARSED'][0]['EXT']['LIST']['HIERACHY_DELIMITER'])) {
            return $ret['PARSED'][0]['EXT']['LIST']['HIERACHY_DELIMITER'];
        }
        return new PEAR_Error('the IMAP Server does not support HIERACHY_DELIMITER!');
    }



    /**
     * Returns an array containing the names of the selected mailboxes
     *
     * @param string $reference          base mailbox to start the search 
     *                                   (default is current mailbox)
     * @param string $restriction_search false or 0 means return all mailboxes
     *                                   true or 1 return only the mailbox that
     *                                   contains that exact name
     *                                   2 return all mailboxes in that 
     *                                   hierarchy level
     * @param string $returnAttributes   true means return an assoc array 
     *                                   containing mailbox names and mailbox 
     *                                   attributes,false - the default - means
     *                                   return an array of mailboxes
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function getMailboxes($reference = '', 
                          $restriction_search = 0, 
                          $returnAttributes = false)
    {

        if (is_bool($restriction_search)) {
            $restriction_search = (int) $restriction_search;
        }

        if (is_int($restriction_search)) {
            switch ($restriction_search) {
            case 0:
                $mailbox = '*';
                break;
            case 1:
                $mailbox   = $reference;
                $reference = '';
                break;
            case 2:
                $mailbox = '%';
                break;
            }
        } else {
            if (is_string($restriction_search)) {
                $mailbox = $restriction_search;
            } else {
                return new PEAR_Error('Wrong data for 2nd parameter');
            }
        }

        if (PEAR::isError($ret = $this->cmdList($reference, $mailbox))) {
            return $ret;
        }

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret_aux = array();
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {

                // If the folder has the \NoSelect atribute we don't put in 
                // the list
                // it solves a bug in wu-imap that crash the IMAP server if 
                // we select that mailbox
                if (isset($mbox['EXT']['LIST']['NAME_ATTRIBUTES'])) {
                    // if (!in_array('\NoSelect', $mbox['EXT']['LIST']['NAME_ATTRIBUTES'])) {
                    if ($returnAttributes) {
                        $ret_aux[] = array(
                            'MAILBOX'            => $mbox['EXT']['LIST']['MAILBOX_NAME'],
                            'ATTRIBUTES'         => $mbox['EXT']['LIST']['NAME_ATTRIBUTES'],
                            'HIERACHY_DELIMITER' => $mbox['EXT']['LIST']['HIERACHY_DELIMITER']);
                    } else {
                        $ret_aux[] = $mbox['EXT']['LIST']['MAILBOX_NAME'];
                    }
                    // }
                }
            }
        }
        return $ret_aux;
    }



    /**
     * Check if the mailbox name exists
     *
     * @param string $mailbox Mailbox name to check existance
     *
     * @return mixed boolean true/false or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function mailboxExist($mailbox)
    {
        // true means do an exact match
        if (PEAR::isError($ret = $this->getMailboxes($mailbox, true))) {
            return $ret;
        }
        if (count($ret) > 0) {
            foreach ($ret as $mailbox_name) {
                if ($mailbox_name == $mailbox) {
                    return true;
                }
            }
        }
        return false;
    }



    /**
     * Creates the mailbox $mailbox
     *
     * @param string $mailbox Mailbox name to create
     * @param array  $options Options to pass to create (default is no options)
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function createMailbox($mailbox, $options = null)
    {
        if (PEAR::isError($ret = $this->cmdCreate($mailbox, $options))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Deletes the mailbox $mailbox
     *
     * @param string $mailbox Name of the Mailbox that should be deleted
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function deleteMailbox($mailbox)
    {
        // TODO verificar que el mailbox se encuentra vacio y, sino borrar los 
        // mensajes antes~!!!!!!
        // ToDo find someone who can translate the above todo

        if (PEAR::isError($ret = $this->cmdDelete($mailbox))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }
    
    
    
    /**
     * Renames the mailbox $mailbox
     *
     * @param string $oldmailbox mailbox name to rename
     * @param string $newmailbox new name for the mailbox
     * @param array  $options    options to pass to rename
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function renameMailbox($oldmailbox, $newmailbox, $options = null)
    {
        if (PEAR::isError($ret = $this->cmdRename($oldmailbox, 
                                                  $newmailbox, 
                                                  $options))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }




    /******************************************************************
    **                                                               **
    **           SUBSCRIPTION METHODS                                **
    **                                                               **
    ******************************************************************/

    /**
     * Subscribes to the selected mailbox
     *
     * @param string $mailbox Mailbox name to subscribe (default is current 
     *                        mailbox)
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function subscribeMailbox($mailbox = null)
    {
        if ($mailbox == null) {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdSubscribe($mailbox))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Removes the subscription to a mailbox
     *
     * @param string $mailbox Mailbox name to unsubscribe (default is current 
     *                        mailbox)
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function unsubscribeMailbox($mailbox = null)
    {
        if ($mailbox == null) {
            $mailbox = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdUnsubscribe($mailbox))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Lists the subscription to mailboxes
     *
     * @param string  $reference          Mailbox name start the search (see to 
     *                                    getMailboxes() )
     * @param string  $restriction_search Mailbox_name Mailbox name filter the 
     *                                    search (see to getMailboxes() )
     * @param boolean $returnAttributes   Return the attributes
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function listsubscribedMailboxes($reference = '',
                                     $restriction_search = 0,
                                     $returnAttributes = false)
    {
        if (is_bool($restriction_search)) {
            $restriction_search = (int) $restriction_search;
        }

        if (is_int($restriction_search)) {
            switch ($restriction_search) {
            case 0:
                $mailbox = '*';
                break;
            case 1:
                $mailbox   = $reference;
                $reference = '%';
                break;
            case 2:
                $mailbox = '%';
                break;
            }
        } else {
            if (is_string($restriction_search)) {
                $mailbox = $restriction_search;
            } else {
                return new PEAR_Error('UPS... you ');
            }
        }

        if (PEAR::isError($ret = $this->cmdLsub($reference, $mailbox))) {
            return $ret;
        }
        // $ret=$this->cmdLsub($mailbox_base, $mailbox_name);

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        $ret_aux = array();
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {
                if (isset($mbox['EXT']['LSUB']['MAILBOX_NAME'])) {
                    if ($returnAttributes) {
                        $ret_aux[] = array(
                            'MAILBOX'            => $mbox['EXT']['LSUB']['MAILBOX_NAME'],
                            'ATTRIBUTES'         => $mbox['EXT']['LSUB']['NAME_ATTRIBUTES'],
                            'HIERACHY_DELIMITER' => $mbox['EXT']['LSUB']['HIERACHY_DELIMITER']
                            );
                    } else {
                        $ret_aux[] = $mbox['EXT']['LSUB']['MAILBOX_NAME'];
                    }
                }
            }
        }
        return $ret_aux;
    }




    /******************************************************************
    **                                                               **
    **           FLAGS METHODS                                       **
    **                                                               **
    ******************************************************************/

    /**
     * Lists the flags of the selected messages
     *
     * @param mixed $msg_id the message list
     *
     * @return mixed array on success/PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function getFlags($msg_id = null)
    {
        // You can also provide an array of numbers to those emails
        if ($msg_id != null) {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        } else {
            $message_set = '1:*';
        }


        if (PEAR::isError($ret = $this->cmdFetch($message_set, 'FLAGS'))) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $flags = array();
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $msg_flags) {
                if (isset($msg_flags['EXT']['FLAGS'])) {
                    $flags[] = $msg_flags['EXT']['FLAGS'];
                }
            }
        }
        return $flags;
    }



    /**
     * Sets the flags of the selected messages
     *
     * @param mixed   $msg_id   the message list or string "all" for all
     * @param mixed   $flags    flags to set (space separated String or array)
     * @param string  $mod      "set" to set flags (default),
     *                          "add" to add flags,
     *                          "remove" to remove flags
     * @param boolean $uidStore msg_id contains UID's instead of Message 
     *                          Sequence Number if set to true
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function setFlags($msg_id, $flags, $mod = 'set', $uidStore = false)
    {
        // you can also provide an array of numbers to those emails
        if ($msg_id == 'all') {
            $message_set = '1:*';
        } else {
            if (is_array($msg_id)) {
                $message_set = $this->_getSearchListFromArray($msg_id);
            } else {
                $message_set = $msg_id;
            }
        }
        
        $flaglist = '';
        if (is_array($flags)) {
            $flaglist = implode(' ', $flags);
        } else {
            $flaglist = $flags;
        }
        
        switch ($mod) {
        case 'set':
            $dataitem = 'FLAGS';
            break;
        case 'add':
            $dataitem = '+FLAGS';
            break;
        case 'remove':
            $dataitem = '-FLAGS';
            break;
        default:
            // Wrong Input
            return new PEAR_Error('wrong input for $mod');
            break;
        }
        
        if ($uidStore == true) {
            $ret = $this->cmdUidStore($message_set, $dataitem, $flaglist);
        } else {
            $ret = $this->cmdStore($message_set, $dataitem, $flaglist);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }



    /**
     * adds flags to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     * @param mixed $flags  Flags to set (space separated String or array)
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function addFlags($msg_id, $flags)
    {
        return $this->setFlags($msg_id, $flags, $mod = 'add');
    }



    /**
     * adds the Seen flag (\Seen) to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function addSeen($msg_id)
    {
        return $this->setFlags($msg_id, '\Seen', $mod = 'add');
    }



    /**
     * adds the Answered flag (\Answered) to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public 
     * @since 1.1
     */
    function addAnswered($msg_id)
    {
        return $this->setFlags($msg_id, '\Answered', $mod = 'add');
    }



    /**
     * adds the Deleted flag (\Deleted) to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function addDeleted($msg_id)
    {
        return $this->setFlags($msg_id, '\Deleted', $mod = 'add');
    }



    /**
     * adds the Flagged flag (\Flagged) to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function addFlagged($msg_id)
    {
        return $this->setFlags($msg_id, '\Flagged', $mod = 'add');
    }



    /**
     * adds the Draft flag (\Draft) to the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function addDraft($msg_id)
    {
        return $this->setFlags($msg_id, '\Draft', $mod = 'add');
    }



    /**
     * remove flags from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     * @param mixed $flags  Flags to remove (space separated string or array)
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeFlags($msg_id, $flags)
    {
        return $this->setFlags($msg_id, $flags, $mod = 'remove');
    }



    /**
     * remove the Seen flag (\Seen) from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeSeen($msg_id)
    {
        return $this->setFlags($msg_id, '\Seen', $mod = 'remove');
    }



    /**
     * remove the Answered flag (\Answered) from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeAnswered($msg_id)
    {
        return $this->setFlags($msg_id, '\Answered', $mod = 'remove');
    }



    /**
     * remove the Deleted flag (\Deleted) from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeDeleted($msg_id)
    {
        return $this->setFlags($msg_id, '\Deleted', $mod = 'remove');
    }



    /**
     * remove the Flagged flag (\Flagged) from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeFlagged($msg_id)
    {
        return $this->setFlags($msg_id, '\Flagged', $mod = 'remove');
    }



    /**
     * remove the Draft flag (\Draft) from the selected messages
     *
     * @param mixed $msg_id The message list or string "all" for all
     *
     * @return mixed true on success/PearError on failure
     *
     * @access public
     * @since 1.1
     */
    function removeDraft($msg_id)
    {
        return $this->setFlags($msg_id, '\Draft', $mod = 'remove');
    }



    /**
     * check the Seen flag
     *
     * @param mixed $message_nro The message to check
     *
     * @return mixed true or false if the flag is set PearError on Failure
     *
     * @access public
     * @since 1.0
     */
    function isSeen($message_nro)
    {
        return $this->hasFlag($message_nro, '\Seen');
    }



    /**
     * check the Answered flag
     *
     * @param mixed $message_nro The message to check
     *
     * @return mixed true or false if the flag is set PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function isAnswered($message_nro)
    {
        return $this->hasFlag($message_nro, '\Answered');
    }



    /**
     * check the flagged flag
     *
     * @param mixed $message_nro The message to check
     *
     * @return mixed true or false if the flag is set PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function isFlagged($message_nro)
    {
        return $this->hasFlag($message_nro, '\Flagged');
    }



    /**
     * check the Draft flag
     *
     * @param mixed $message_nro The message to check
     *
     * @return mixed true or false if the flag is set PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function isDraft($message_nro)
    {
        return $this->hasFlag($message_nro, '\Draft');
    }



    /**
     * check the Deleted flag
     *
     * @param mixed $message_nro The message to check
     *
     * @return mixed true or false if the flag is set PearError on failure
     *
     * @access public
     * @since 1.0
     */
    function isDeleted($message_nro)
    {
        return $this->hasFlag($message_nro, '\Deleted');
    }



    /**
     * checks if a flag is set
     *
     * @param mixed  $message_nro The message to check
     * @param string $flag        The flag that should be checked
     *
     * @return mixed true or false if the flag is set PearError on Failure
     *
     * @access public
     * @since 1.0
     */
    function hasFlag($message_nro, $flag)
    {
        if (PEAR::isError($resp = $this->getFlags($message_nro))) {
            return $resp;
        }
        if (isset($resp[0])) {
            if (is_array($resp[0])) {
                if (in_array($flag, $resp[0])) {
                    return true;
                }
            }
        }
        return false;
    }




    /******************************************************************
    **                                                               **
    **           MISC METHODS                                        **
    **                                                               **
    ******************************************************************/

    
    /**
     * expunge function. Sends the EXPUNGE command
     *
     * @return mixed true on success / PEAR Error on failure
     *
     * @access public
     * @since 1.0
     */
    function expunge()
    {
        if (PEAR::isError($ret = $this->cmdExpunge())) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * Search function. Sends the SEARCH command
     *
     * @param string  $search_list Search criterias
     * @param boolean $uidSearch   If set to true UID SEARCH is send instead of SEARCH
     *
     * @return mixed Message array or PEAR Error on failure
     *
     * @access public
     * @since 1.0
     */
    function search($search_list, $uidSearch = false)
    {
        if ($uidSearch) {
            $ret = $this->cmdUidSearch($search_list);
        } else {
            $ret = $this->cmdSearch($search_list);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return $ret['PARSED']['SEARCH']['SEARCH_LIST'];
    }



    /**
     * sort function. Sends the SORT command
     *
     * @param string  $sort_list   sort program
     * @param string  $charset     charset specification (default = 'US-ASCII')
     * @param string  $search_list searching criteria
     * @param boolean $uidSort     if set to true UID SORT is send instead 
     *                             of SORT
     *
     * @return mixed message array or PEAR Error on failure
     *
     * @access public
     * @since 1.1
     */
    function sort($sort_list, 
                  $charset = 'US-ASCII', 
                  $search_list = '', 
                  $uidSort = false)
    {
        $sort_command = sprintf("(%s) %s %s", 
                                $sort_list, 
                                strtoupper($charset), 
                                $search_list);

        if ($uidSort) {
            $ret = $this->cmdUidSort($sort_command);
        } else {
            $ret = $this->cmdSort($sort_command);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE']
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return $ret['PARSED']['SORT']['SORT_LIST'];
    }




    /******************************************************************
    **                                                               **
    **           QUOTA METHODS                                       **
    **                                                               **
    ******************************************************************/


    /**
     * Returns STORAGE quota details
     *
     * @param string $mailbox_name Mailbox to get quota info. (default is 
     *                             current mailbox)
     *
     * @return assoc array contaning the quota info on success or 
     *                      PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function getStorageQuotaRoot($mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdGetQuotaRoot($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            // if the error is that the user does not have quota set 
            // return  an array and not pear error
            if (substr(strtoupper($ret['RESPONSE']['STR_CODE']),
                       0,
                       9) == 'QUOTAROOT') {
                return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
            }
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['EXT']['QUOTA']['STORAGE'])) {
            return $ret['PARSED']['EXT']['QUOTA']['STORAGE'];
        }
        return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
    }



    /**
     * Returns STORAGE quota details
     *
     * @param string $mailbox_name Mailbox to get quota info. (default is 
     *                             current mailbox)
     *
     * @return assoc array contaning the quota info on success or PEAR_Error 
     *                     on failure
     *
     * @access public
     * @since 1.0
     */
    function getStorageQuota($mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdGetQuota($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            // if the error is that the user does not have quota set 
            // return  an array and not pear error
            if (substr(strtoupper($ret['RESPONSE']['STR_CODE']),
                       0,
                       5) == 'QUOTA') {
                return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
            }
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['EXT']['QUOTA']['STORAGE'])) {
            return $ret['PARSED']['EXT']['QUOTA']['STORAGE'];
        }
        return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
    }



    /**
     * Returns MESSAGES quota details
     *
     * @param string $mailbox_name Mailbox to get quota info. (default is 
     *                             current mailbox)
     *
     * @return assoc array contaning the quota info on success or PEAR_Error 
     *                     on failure
     *
     * @access public
     * @since 1.0
     */
    function getMessagesQuota($mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdGetQuota($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            // if the error is that the user does not have quota set return 
            // an array and not pear error
            if (substr(strtoupper($ret['RESPONSE']['STR_CODE']),
                       0,
                       5) == 'QUOTA') {
                return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
            }
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['EXT']['QUOTA']['MESSAGES'])) {
            return $ret['PARSED']['EXT']['QUOTA']['MESSAGES'];
        }
        return array('USED'=>'NOT SET', 'QMAX'=>'NOT SET');
    }
     
    

    /**
     * sets STORAGE quota
     *
     * @param string $mailbox_name Mailbox to set quota
     * @param int    $quota        Quotasize
     *
     * @return true on success or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function setStorageQuota($mailbox_name, $quota)
    {
        if (PEAR::isError($ret = $this->cmdSetQuota($mailbox_name, $quota))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * sets MESSAGES quota
     *
     * @param string $mailbox_name Mailbox to set quota
     * @param int    $quota        Quotasize
     *
     * @return true on success or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function setMessagesQuota($mailbox_name, $quota)
    {
        if (PEAR::isError($ret = $this->cmdSetQuota($mailbox_name,
                                                    '',
                                                    $quota))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }




    /******************************************************************
    **                                                               **
    **           ACL METHODS                                         **
    **                                                               **
    ******************************************************************/


    /**
     * get the Access Control List details
     *
     * @param string $mailbox_name Mailbox to get ACL info. (default is 
     *                             current mailbox)
     *
     * @return mixed string on success or false or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function getACL($mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdGetACL($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['USERS'])) {
            return $ret['PARSED']['USERS'];
        } else {
            return false;
        }
    }



    /**
     * Set ACL on a mailbox
     *
     * @param string $mailbox_name The mailbox
     * @param string $user         User to set the ACL
     * @param string $acl          ACL list
     *
     * @return mixed true on success or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function setACL($mailbox_name, $user, $acl)
    {
        if (PEAR::isError($ret = $this->cmdSetACL($mailbox_name, $user, $acl))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * deletes the ACL on a mailbox
     *
     * @param string $mailbox_name The mailbox
     * @param string $user         User to delete the ACL
     *
     * @return mixed true on success, or PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function deleteACL($mailbox_name, $user)
    {
        if (PEAR::isError($ret = $this->cmdDeleteACL($mailbox_name, $user))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }



    /**
     * returns the rights that the user logged on has on the mailbox
     * this method can be used by any user, not only the administrator
     *
     * @param string $mailbox_name The mailbox to query rights (default is 
     *                             current mailbox)
     *
     * @return mixed string containing the list of rights on success, or 
     *                      PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function getMyRights($mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdMyRights($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['GRANTED'])) {
            return $ret['PARSED']['GRANTED'];
        }

        return new PEAR_Error('Bogus response from server!');
    }



    /**
     * returns an array containing the rights for given user on the mailbox
     * this method can be used by any user, not only the administrator
     *
     * @param string $user         The user to query rights
     * @param string $mailbox_name The mailbox to query rights (default is 
     *                             current mailbox)
     *
     * @return mixed string containing the list of rights on success, or 
     *               PEAR_Error on failure
     *
     * @access public
     * @since 1.0
     */
    function getACLRights($user,$mailbox_name = null)
    {

        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }


        if (PEAR::isError($ret = $this->cmdListRights($mailbox_name, $user))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['GRANTED'])) {
            return $ret['PARSED']['GRANTED'];
        }

        return new PEAR_Error('Bogus response from server!');
    }




    /******************************************************************
    **                                                               **
    **           ANNOTATEMORE METHODS                                **
    **                                                               **
    ******************************************************************/


    /**
     * set annotation
     *
     * @param string $entry        Entry
     * @param array  $values       Values
     * @param string $mailbox_name Mailbox name (default is current mailbox)
     *
     * @return mixed true on success or PEAR Error on failure
     *
     * @access public
     * @since 1.0.2
     */
    function setAnnotation($entry, $values, $mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdSetAnnotation($mailbox_name, 
                                                         $entry, 
                                                         $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }


    /**
     * delete annotation
     *
     * @param string $entry        Entry
     * @param array  $values       Values
     * @param string $mailbox_name Mailbox name (default is current mailbox)
     *
     * @return mixed true on success or PEAR Error on failure
     *
     * @access public
     * @since 1.0.2
     */
    function deleteAnnotation($entry, $values, $mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdDeleteAnnotation($mailbox_name, 
                                                            $entry, 
                                                            $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        return true;
    }


    /**
     * get annotation
     *
     * @param string $entries      Entries
     * @param array  $values       Values
     * @param string $mailbox_name Mailbox name (default is current mailbox)
     *
     * @return mixed array containing annotations on success or PEAR Error 
     *               on failure
     *
     * @access public
     * @since 1.0.2
     */
    function getAnnotation($entries, $values, $mailbox_name = null)
    {
        if ($mailbox_name == null) {
            $mailbox_name = $this->getCurrentMailbox();
        }
        if (!is_array($entries)) {
            $entries = array($entries);
        }
        if (!is_array($values)) {
            $values = array($values);
        }

        if (PEAR::isError($ret = $this->cmdGetAnnotation($mailbox_name, 
                                                         $entries, 
                                                         $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if (strtoupper($ret['RESPONSE']['CODE']) != 'OK') {
            return new PEAR_Error($ret['RESPONSE']['CODE'] 
                                  . ', ' 
                                  . $ret['RESPONSE']['STR_CODE']);
        }
        $ret_aux = array();
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {
                $rawvalues = $mbox['EXT']['ATTRIBUTES'];
                $values    = array();
                for ($i = 0; $i < count($rawvalues); $i += 2) {
                    $values[$rawvalues[$i]] = $rawvalues[$i + 1];
                }
                $mbox['EXT']['ATTRIBUTES'] = $values;
                $ret_aux[]                 = $mbox['EXT'];
            }
        }
        if (count($ret_aux) == 1 && $ret_aux[0]['MAILBOX'] == $mailbox_name) {
            if (count($entries) == 1 && $ret_aux[0]['ENTRY'] == $entries[0]) {
                if (count($ret_aux[0]['ATTRIBUTES']) == 1 
                    && count($values) == 1) {
                    $attrs = array_keys($ret_aux[0]['ATTRIBUTES']);
                    $vals  = array_keys($values);
                    if ($attrs[0] == $vals[0]) {
                        return $ret_aux[0]['ATTRIBUTES'][$attrs[0]];
                    }
                }
            }
        }
        return $ret_aux;
    }



    /**
     * Transform an array to a list to be used in the cmdFetch method
     *
     * @param array $arr Array that should be transformed
     *
     * @return string transformed array
     *
     * @access private
     */
    function _getSearchListFromArray($arr)
    {
        $txt = implode(',', $arr);
        return $txt;
    }




    /*****************************************************
        Net_POP3 Compatibility functions:

        Warning!!!
            Those functions could dissapear in the future

    *********************************************************/

    
    /**
     * same as getMailboxSize()
     * Net_POP3 Compatibility function
     *
     * @return same as getMailboxSize();
     *
     * @access public
     */
    function getSize()
    {
        return $this->getMailboxSize();
    }

    /**
     * same as getNumberOfMessages($mailbox)
     * Net_POP3 Compatibility function
     *
     * @param string $mailbox Mailbox (default is current mailbox)
     *
     * @return same as getNumberOfMessages($mailbox)
     *
     * @access public
     */
    function numMsg($mailbox = null)
    {
        return $this->getNumberOfMessages($mailbox);
    }


    /**
     * Returns the entire message with given message number.
     * Net_POP3 Compatibility function
     *
     * @param int $msg_id Message number
     *
     * @return mixed Either entire message or PEAR Error on failure
     *
     * @access public
     */
    function getMsg($msg_id)
    {
        if (PEAR::isError($ret = $this->getMessages($msg_id, false))) {
            return $ret;
        }
        // false means that getMessages() must not use the msg number as array key
        if (isset($ret[0])) {
            return $ret[0];
        } else {
            return $ret;
        }
    }



    /**
     * same as getMessagesList($msg_id)
     * Net_POP3 Compatibility function
     *
     * @param int $msg_id Message number
     *
     * @return same as getMessagesList()
     *
     * @access public
     */
    function getListing($msg_id = null)
    {
        return $this->getMessagesList($msg_id);
    }


    
    /**
     * same as deleteMessages($msg_id)
     * Net_POP3 Compatibility function
     *
     * @param int $msg_id Message number
     *
     * @return same as deleteMessages()
     *
     * @access public
     */
    function deleteMsg($msg_id)
    {
        return $this->deleteMessages($msg_id);
    }

}
?>
