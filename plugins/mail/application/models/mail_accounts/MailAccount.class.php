<?php

/**
 * MailAccount class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailAccount extends BaseMailAccount {

	private $owner;
	 
	/**
	 * Gets the account owner
	 *
	 * @return User
	 */
	function getOwner()
	{
		if (is_null($this->owner)){
			$this->owner = Contacts::findById($this->getContactId());
		}
		return $this->owner;
	}
	 
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) {
			$errors[] = lang('mail account name required');
		} // if
		if(!$this->validatePresenceOf('server')) {
			$errors[] = lang('mail account server required');
		} // if
		if(!$this->validatePresenceOf('password')) {
			$errors[] = lang('mail account password required');
		} // if
		if(!$this->validatePresenceOf('email')) {
			$errors[] = lang('mail account id required');
		} // if
	} // validate

	/* Return array of all emails
	 *
	 * @access public
	 * @param void
	 * @return one or more MailContents objects
	 */
	function getMailContents() {
		return MailContents::findAll(array(
			'conditions' => '`account_id` = ' . DB::escape($this->getId()),
			'order' => '`date` DESC'
		)); // findAll
	} // getMailContents

	function getUids($folder = null, $limit = null) {
		$sql = "SELECT `uid` FROM `" . MailContents::instance()->getTableName() . "` WHERE `account_id` = ". $this->getId();
		if (!is_null($folder)) {
			$sql .= " AND `imap_folder_name` = '$folder'";
		}
		if (!is_null($limit) && is_numeric($limit)) {
			$sql .= " LIMIT $limit";
		}
		$res = DB::execute($sql);
		$rows = $res->fetchAll();
		$uids = array();
		if (is_array($rows)) {
			foreach ($rows as $r) {
				$uids[] = $r['uid'];
			}
		}
		return $uids;
	}
	
	function getMaxUID($folder = null){
		$maxUID = "";
		$box_cond = "";
		$sql = "SELECT `uid` FROM `" . MailContents::instance()->getTableName() . "` WHERE `account_id` = ". $this->getId();
		if (!is_null($folder)) {
			$box_cond = " AND `imap_folder_name` = '$folder'";
		}
		if ($this->getIsImap()) {			
			$max_param = "object_id";
		}else{
			$max_param = "received_date";
		}
		$sql .= "$box_cond AND $max_param = (SELECT max($max_param) FROM `". MailContents::instance()->getTableName() . "` WHERE `account_id` = ". $this->getId(). " AND `state` in (0,1,4) $box_cond) LIMIT 1";
		
		$res = DB::execute($sql);
		$rows = $res->fetchAll();
		if (is_array($rows) && count($rows) > 0){
			$maxUID = $rows[0]['uid'];
		}
		return $maxUID;
	}
	
	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	

	/**
	 * Return view mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		return get_url('mail', 'view_account', $this->getId());
	} // getAccountUrl

	/**
	 * Return edit mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('mail', 'edit_account', $this->getId());
	} // getEditUrl

	/**
	 * Return add mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getAddUrl() {
		return get_url('mail', 'add_account');
	} // getEditUrl

	/**
	 * Return delete mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('mail', 'delete_account', $this->getId());
	} // getDeleteUrl


	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if $user can access this account
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		$accountUser = MailAccountContacts::getByAccountAndContact($this, $user);
		return $accountUser instanceof MailAccountContact;
	} // canView

	/**
	 * Check if specific user can add accounts
	 *
	 * @access public
	 * @param User $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(Contact $user) {
		return can_add_mail_accounts($user);
	} // canAdd

	/**
	 * Check if specific user can edit this account
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		if (logged_user() instanceof Contact && logged_user()->isAdministrator()) {
			return true;
		}
		return $this->canView($user);
	}

	/**
	 * Check if specific user can delete this account
	 *
	 * @access public
	 * @param User $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		if (logged_user() instanceof Contact && logged_user()->isAdministrator()) {
			return true;
		}
		$accountUser = MailAccountContacts::getByAccountAndContact($this, $user);
		//return $accountUser instanceof MailAccountContact && $accountUser->getCanEdit() || can_manage_security(logged_user());
                return $accountUser instanceof MailAccountContact && $accountUser->getCanEdit();
	} // canDelete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->getName();
	} // getObjectName

	/**
	 * Return object type name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return 'mail account';
	} // getObjectTypeName

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getEditUrl();
	} // getObjectUrl

	
	function delete($deleteMails = false){
		MailAccountContacts::deleteByAccount($this);
		if ($deleteMails) {
			session_commit();
			ini_set('memory_limit', '1024M');
			
			LinkedObjects::delete(array("(`object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).")) 
				or (`rel_object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId())."))")); 
			
      		SearchableObjects::delete(array("`rel_object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") "));
			ReadObjects::delete("`rel_object_id` IN (SELECT `object_id` FROM `".TABLE_PREFIX."mail_contents` WHERE `account_id` = " . DB::escape($this->getId()).") ");
			
			$account_email_ids = MailContents::findAll(array('id' => true, 'conditions' => '`account_id` = ' . DB::escape($this->getId()), 'include_trashed' => true));
			if (count($account_email_ids) > 0) {
				MailDatas::delete('id IN ('.implode(',', $account_email_ids).')');
				MailContents::delete('`account_id` = ' . DB::escape($this->getId()));
			}
		}
		if ($this->getIsImap()) {
			MailAccountImapFolders::delete('account_id = ' . $this->getId());
		}
		parent::delete();
	}

	/**
	 * Return smtp username that should be used according to smtp_use_Auth settings  
	 *
	 * @return unknown
	 */
	function smtpUsername(){
		$auth_level = $this->getSmtpUseAuth(); // 0 is no authentication, 1 is same as pop, 2 is use smtp specific settings
		if ($auth_level  == 0)	{
			return null;
		}
		else if ($auth_level == 1)	{
			return $this->getEmail();
		}
		else if ($auth_level == 2)	{
			return $this->getSmtpUsername();
		}
	}
	
	/**
	 * Return smtp password that should be used according to smtp_use_Auth settings  
	 *
	 * @return unknown
	 */
	function smtpPassword(){
		$auth_level = $this->getSmtpUseAuth(); // 0 is no authentication, 1 is same as pop, 2 is use smtp specific settings
		if ($auth_level  == 0)	{
			return null;
		}
		else if ($auth_level == 1)	{
			return $this->getPassword();
		}
		else if ($auth_level == 2)	{
			return $this->getSmtpPassword();
		}
	}
	
	function getFromName() {
		$user_settings = MailAccountContacts::getByAccountAndContact($this, logged_user());
		if ($user_settings instanceof MailAccountContact && $user_settings->getSenderName()) {
			return $user_settings->getSenderName();
		} else if ($this->getSenderName()) {
			return $this->getSenderName();
		} else if (logged_user() instanceof Contact) {
			return logged_user()->getObjectName();
		} else {
			return "";
		}
	}
	
	/**
	 * Return an array of memmber Ids
	 * Compatibility function: Mail accounts can be only in 1 member
	 * @deprecated
	 */
	function getMemberIds() {
		$memberId = $this->getMemberId();
		$return = array () ;
		if ($memberId) {
			$return[] = $memberId  ;
		}
		return $return ;
	}
}
?>