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
  } // MailAccounts 

?>