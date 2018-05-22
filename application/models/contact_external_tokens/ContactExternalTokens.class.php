<?php

/**
 * ContactExternalTokens class
 */
class ContactExternalTokens extends BaseContactExternalTokens {

	/**
	 * Return ContactExternalToken by token
	 *
	 * @access public
	 * @param string $token
	 * @return ContactExternalToken
	 */
	function findByToken($token){
	    return self::findOne(array(
	       'conditions' => array('`token` = ?', $token) 
	    ));
	}


}

?>