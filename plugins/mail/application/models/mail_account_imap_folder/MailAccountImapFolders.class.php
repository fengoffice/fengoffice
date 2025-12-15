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
  	static function getMailAccountImapFolders($accountid)
  	{
  		return MailAccountImapFolders::instance()->findAll(array(
        'conditions' => '`account_id` = ' . $accountid
      )); // findAll
  	}


  	/**
     * Return a Mail account Imap folder by its special use key
     *
     * @param int $accountid
     * @param string $folder_key
     * @return MailAccountImapFolder
     */
	static function getSpecialUseFolder($accountid, $folder_key) {
		return self::instance()->findOne(array(
			'conditions' => array('`account_id` = ? AND `special_use` = ?', $accountid, $folder_key)
		)); // findAll
	}
	
	/**
     * Return a Mail account Imap folder by its name
     *
     * @param int $accountid
     * @param string $folder_name
     * @return MailAccountImapFolder
     */
	static function getByFolderName($accountid, $folder_name) {
		return self::instance()->findOne(array(
			'conditions' => array('`account_id` = ? AND `folder_name` = ?', $accountid, $folder_name)
		)); // findAll
	}

  } // MailAccounts 

?>