<?php

  /**
  * MailAccounts
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class MailAccountImapFolders extends BaseMailAccountImapFolders {
  	
	/**
    * Return Mail accounts Imap folder
    *
    * @param account id
    * @return array
    */
  	function getMailAccountImapFolders($accountid)
  	{
  		return MailAccountImapFolders::findAll(array(
        'conditions' => '`account_id` = ' . $accountid
      )); // findAll
  	}


	static function getSpecialUseFolder($accountid, $folder_key) {
		return self::findOne(array(
			'conditions' => array('`account_id` = ? AND `special_use` = ?', $accountid, $folder_key)
		)); // findAll
	}
	
	static function getByFolderName($accountid, $folder_name) {
		return self::findOne(array(
			'conditions' => array('`account_id` = ? AND `folder_name` = ?', $accountid, $folder_name)
		)); // findAll
	}

  } // MailAccounts 

?>