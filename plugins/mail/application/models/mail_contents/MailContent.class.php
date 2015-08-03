<?php

/**
 * MailContent class
 * Generated on Wed, 15 Mar 2006 22:57:46 +0100 by DataObject generation tool
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class MailContent extends BaseMailContent {

	private $mail_data = null;

	/**
	 * Cache of account
	 * @var MailAccount
	 */
	private $account;

	protected $is_searchable = true;
	

	
	function getSummaryText () {
		return $this->getBodyPlain();
	}
	
	
	/**
	 * Array of searchable columns
	 * @var array
	 */
	protected $searchable_columns = array('from', 'from_name', 'to', 'cc', 'bcc', 'subject', 'body');
	 
	protected $mail_conversation_mail_ids;
	protected $mail_conversation_mail_ids_w_permissions;

	
	function getConversationMailIds($check_permissions = false){
		if ($check_permissions) {
			if (is_null($this->mail_conversation_mail_ids_w_permissions)) {
				$this->mail_conversation_mail_ids_w_permissions = MailContents::getMailIdsFromConversation($this, true);
			}
			return $this->mail_conversation_mail_ids_w_permissions;
		} else {
			if (is_null($this->mail_conversation_mail_ids)) {
				$this->mail_conversation_mail_ids = MailContents::getMailIdsFromConversation($this);
			}
			return $this->mail_conversation_mail_ids;
		} 
	}
	 
	/**
	 * Gets the owner mail account
	 * @return MailAccount
	 */
	function getAccount() {
		if (is_null($this->account)){
			$this->account = MailAccounts::instance()->getAccountById($this->getAccountId());
		} //if
		return $this->account;
	}
	
	/**
	 * 
	 * @param string $subject
	 * The subject is stored in two places: object.name and mailData.subject
	 */
	function setSubject($subject) {
		$this->getMailData()->setSubject($subject);
		$this->object->setName($subject);
	}
	
	function getSubject() {
		return $this->object->getName();
	}
	
	function getFullSubject() {
		return $this->getMailData()->getSubject();
	}
	
	function getTo() {
		return $this->getMailData()->getTo();
	}
	
	function setTo($to) {
		return $this->getMailData()->setTo($to);
	}
	
	function getCc() {
		return $this->getMailData()->getCc();
	}
	
	function setCc($cc) {
		return $this->getMailData()->setCc($cc);
	}
	
	function getBcc() {
		return $this->getMailData()->getBcc();
	}
	
	function setBcc($bcc) {
		return $this->getMailData()->setBcc($bcc);
	}
	
	function getBodyHtml() {
		return $this->getMailData()->getBodyHtml();
	}
	
	function setBodyHtml($html) {
		return $this->getMailData()->setBodyHtml($html);
	}
	
	function getBodyPlain() {
		return $this->getMailData()->getBodyPlain();
	}
	
	function setBodyPlain($plain) {
		return $this->getMailData()->setBodyPlain($plain);
	}
	
	 
	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('uid')) {
			$errors[] = lang('uid required');
		}
		if(!$this->validatePresenceOf('account_id')) {
			$errors[] = lang('account id required');
		}
	}
	
	function save() {
		parent::save();
		$this->getMailData()->setId($this->getId());
		$this->getMailData()->save();
	}

	/**
	 * @return MailData
	 */
	function getMailData() {
		if (!$this->mail_data instanceof MailData) $this->mail_data = MailDatas::findById($this->getObjectId());
		if (!$this->mail_data instanceof MailData) $this->mail_data = new MailData();
		return $this->mail_data;
	}
	
	function delete($delete_db_record = true) {
		$rows = DB::executeAll("SELECT count(`object_id`) as `c` FROM `".TABLE_PREFIX."mail_contents` WHERE `conversation_id` = " . DB::escape($this->getConversationId()));
		if (is_array($rows) && count($rows) > 0) {
			if ($rows[0]['c'] < 2) {
				// if no other emails in conversation, delete conversation
				DB::execute("DELETE FROM `".TABLE_PREFIX."mail_conversations` WHERE `id` = " . DB::escape($this->getCOnversationId()));
			}
		}
		if ($delete_db_record) {
			return parent::delete();
		} else {
			$this->mark_as_deleted();
			return $this->getObject()->delete();
		}
	}
	
	function clearEverything() {
		$this->clearContentFile();
		$this->clearMailData();
		parent::clearEverything();
	}
	
	function clearMailData() {
		$data = $this->getMailData();
		if ($data instanceof MailData) {
			$data->delete();
		}
	}
	
	function clearContentFile() {
		if ($this->getContentFileId() != '') {
			try {
				FileRepository::deleteFile($this->getContentFileId());
			} catch (Exception $e) {
				//Logger::log($e->getMessage());
			}
		}
	}
	
	function mark_as_deleted(){
		$this->setIsDeleted(true);
		$this->clearEverything();
		return $this->save();
	}

	function getTitle(){
		return $this->getSubject();
	}
	
	/**
	 * Returns the mail content. If it is in repository returns the file content,
	 * else tries to get the content from database (if column content exists).
	 * @access public
	 * @param void
	 * @return string
	 */
	function getContent() {
		if (FileRepository::isInRepository($this->getContentFileId())) {
			return FileRepository::getFileContent($this->getContentFileId());
			//return FileRepository::getFileContent($this->getContentFileId(), config_option('file_storage_adapter'));
		} else if ($this->getMailData()->columnExists('content')) {
			return $this->getMailData()->getContent();
		}
	} 
	


	/**
	 * Returns if the field is classified
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsClassified() {
		$wspaces = $this->getWorkspaces();
		return (is_array($wspaces) && count($wspaces) > 0);
	} // getIsClassified()
	
	
	
	/**
	 * Returns if the mail is a draft
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsDraft() {
		return ($this->getState() == 2);
	} // getIsDraft()
	
	
	/**
	 * Returns if the mail was sent
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getIsSent() {
		return ($this->getState() == 1 || $this->getState() == 3);
	} // getIsSent()

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
		if ($this->getState() == 2)
			return $this->getEditUrl(); // For drafts only
		else
			return get_url('mail', 'view', $this->getId());
	} // getAccountUrl
	
	/**
	 * Return edit mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		if ($this->getState() == 2)
			return get_url('mail', 'edit_mail', $this->getId()); // For drafts only
		else
			return get_url('mail', 'view', $this->getId());
	} // getAccountUrl
	
	function getShowContentsUrl() {
		return get_url('mail', 'view', $this->getId());
	} // getAccountUrl

	/**
	 * Return delete mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('mail', 'delete', $this->getId());
	} // getDeleteUrl

	/**
	 * Return classify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getClassifyUrl() {
		return get_url('mail', 'classify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl

	/**
	 * Return unclassify mail URL of this mail
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUnclassifyUrl() {
		return get_url('mail', 'unclassify', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getClassifyUrl
	
	/**
	 * Return send mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getSendMailUrl() {
		return get_url('mail', 'add_mail');
	} // getClassifyUrl

	/**
	 * Return reply mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getReplyMailUrl() {
		return get_url('mail', 'reply_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getReplyMailUrl
	
	
	/**
	 * Return forward mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getForwardMailUrl() {
		return get_url('mail', 'forward_mail', array( 'id' => $this->getId(), 'type' => 'email'));
	} // getForwardMailUrl
	
	/**
	 * Return print mail URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getPrintUrl() {
		return get_url('mail', 'print_mail', array( 'id' => $this->getId()));
	} // getPrintUrl
	
	function getSenderName() {
		$user = Contacts::getByEmail($this->getFrom());
		if ($user instanceof Contact && $user->canSeeUser(logged_user())) {
			return $user->getObjectName();
		} else {
			$contact = Contacts::getByEmail($this->getFrom());
			if ($contact instanceof Contact && $contact->canView(logged_user())) {
				return $contact->getObjectName();
			}
		}
		return $this->getFromName();
	}
	
	function getSenderUrl() {
		$user = Contacts::getByEmail($this->getFrom());
		if ($user instanceof Contact && $user->canSeeUser(logged_user())) {
			return $user->getCardUrl();
		} else {
			$contact = Contacts::getByEmail($this->getFrom());
			if ($contact instanceof Contact && $contact->canView(logged_user())) {
				return $contact->getCardUrl();
			}
		}
		return $this->getViewUrl();
	}
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Returns true if $user can view this email
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		$account = $this->getAccount();
		if ($account instanceof MailAccount) {
			return ($account->getContactId() == logged_user()->getId() || can_read_sharing_table($user, $this->getId(), false));
		}else{
			return can_read_sharing_table($user, $this->getId(), false);
		}
	}


	/**
	 * Returns true if $user can edit this email
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		$account = $this->getAccount();
		$members = $this->getMembers();
		
		$persons_dim = Dimensions::findByCode('feng_persons');
		$tmp = array();
		foreach ($members as $m) {
			if (!$persons_dim instanceof Dimension || $m->getDimensionId() != $persons_dim->getId()) $tmp[] = $m;
		}
		$members = $tmp;
		
		if ($account instanceof MailAccount) {
			// if classified
			if (count($members) > 0) {
				return $account->getContactId() == logged_user()->getId() || can_write($user, $members, $this->getObjectTypeId());
			} else {
				$macs = MailAccountContacts::instance()->count(array('`account_id` = ? AND `contact_id` = ? AND `can_edit` = 1', $account->getId(), $user->getId()));
				return $account->getContactId() == logged_user()->getId() || $macs > 0;
			}
		}else{
			if (count($members) > 0) {
				return can_write($user, $members, $this->getObjectTypeId());
			} else {
				return false;
			}
		}
	} 

	/**
	 * Check if specific user can add contacts to specific project
	 *
	 * @access public
	 * @param Contact $user
	 * @param Project $project
	 * @return booelean
	 */
	function canAdd(Contact $user, $context, &$notAllowedMember = '') {
		return can_add($user, $context, $this->getObjectTypeId(), $notAllowedMember);
	} // canAdd

	/**
	 * Returns true if $user can delete this email
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		$account = $this->getAccount();
		$members = $this->getMembers();
		
		$persons_dim = Dimensions::findByCode('feng_persons');
		$tmp = array();
		foreach ($members as $m) {
			if (!$persons_dim instanceof Dimension || $m->getDimensionId() != $persons_dim->getId()) $tmp[] = $m;
		}
		$members = $tmp;
		
		if ($account instanceof MailAccount) {
			// if classified
			if (count($members) > 0) {
				return $account->getContactId() == logged_user()->getId() || can_delete($user, $members, $this->getObjectTypeId());
			} else {
				$macs = MailAccountContacts::instance()->count(array('`account_id` = ? AND `contact_id` = ? AND `can_edit` = 1', $account->getId(), $user->getId()));
				return $account->getContactId() == logged_user()->getId() || $macs > 0;
			}
		}else{
			// if classified
			return can_delete($user, $members, $this->getObjectTypeId());			
		}
	}

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	function getSearchableColumnContent($column_name) {
		if ($column_name == 'body') {
			return $this->getTextBody();
		} else if ($this->getMailData()->columnExists($column_name)) {
			return $this->getMailData()->getColumnValue($column_name);
		} else {
			return parent::getSearchableColumnContent($column_name);
		}
	} // getSearchableColumnContent
	
    function addToSearchableObjects($wasNew = false){
    	$columns_to_drop = array();
    	if ($wasNew)
    		$columns_to_drop = $this->getSearchableColumns();
    	else {
			foreach ($this->getSearchableColumns() as $column_name){
				if (isset($this->searchable_composite_columns[$column_name])){
					foreach ($this->searchable_composite_columns[$column_name] as $colName){
						if ($this->isColumnModified($colName)){
							$columns_to_drop[] = $column_name;
							break;
						}
					}
				} else if ($column_name == 'body') {
					$columns_to_drop[] = $column_name;
				} else if ($this->getMailData()->columnExists($column_name) && $this->getMailData()->isColumnModified($column_name)) {
					$columns_to_drop[] = $column_name;
				} else if ($this->isColumnModified($column_name)) {
					$columns_to_drop[] = $column_name;
				}
			}
    	}
    	
    	if (count($columns_to_drop) > 0){
    		SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);

			foreach($columns_to_drop as $column_name) {
    			$content = $this->getSearchableColumnContent($column_name);
    			if(trim($content) != '') {

    				$searchable_object = SearchableObjects::findById(array('rel_object_id' => $this->getObjectId(), 'column_name' => $column_name));
    				if (!$searchable_object instanceof SearchableObject) {
    					$searchable_object = new SearchableObject();
    					$searchable_object->setRelObjectId($this->getObjectId());
    					$searchable_object->setColumnName($column_name);
    				}
    				 
    				$searchable_object->setContent($content);
    				$searchable_object->setContactId($this->getAccount() instanceof MailAccount ? $this->getAccount()->getContactId() : 0);
    				$searchable_object->save();
    			} // if
    		} // foreach
    	} // if

    	$rows = DB::executeAll("select column_name from ".TABLE_PREFIX."searchable_objects where rel_object_id=".$this->getObjectId());
    	
    	if ($wasNew){
    		SearchableObjects::dropContentByObjectColumn($this, 'uid');
    		$searchable_object = new SearchableObject();

    		$searchable_object->setRelObjectId($this->getObjectId());
    		$searchable_object->setColumnName('uid');
    		$searchable_object->setContent($this->getUniqueObjectId());
    		$searchable_object->setContactId($this->getAccount() instanceof MailAccount ? $this->getAccount()->getContactId() : 0);

    		$searchable_object->save();
    	}
    	$rows = DB::executeAll("select column_name from ".TABLE_PREFIX."searchable_objects where rel_object_id=".$this->getObjectId());
    	
    }
	
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
		return $this->manager()->object_type_name;

	} 

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl


	/**
	 * Return value of 'subject' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getName() {
		return $this->getSubject();
	} // getSubject()


  function getDashboardObject(){
    	$projectId = "0";
    	$project = "";
    	if (count($this->getWorkspaces()) > 0) {
    		$type = "email";
    	} else {
    		$type = "emailunclassified";
    	}
    	$tags = project_object_tags($this);
    	
    	$deletedOn = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn(), 'M j')) : lang('n/a');
		if ($this->getTrashedById() > 0)
			$deletedBy = Contacts::findById($this->getTrashedById());
    	if (isset($deletedBy) && $deletedBy instanceof Contact) {
    		$deletedBy = $deletedBy->getObjectName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
		
    	if ($this->getState() == 1 || $this->getState() == 3 || $this->getState() == 5) {
    		$createdBy = $this->getCreatedBy();
    	}
    	if (isset($createdBy) && $createdBy instanceof Contact) {
    		$createdById = $createdBy->getId();
    		$createdBy = $createdBy->getObjectName();
    	} else {
    		$createdById = 0;
    		$createdBy = $this->getFromName();
    	}
    	
  		$archivedOn = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn(), 'M j')) : lang('n/a');
  		if ($this->getArchivedById() > 0)
			$archivedBy = Contacts::findById($this->getArchivedById());
    	if (isset($archivedBy) &&  $archivedBy instanceof Contact) {
    		$archivedBy = $archivedBy->getObjectName();
    	} else {
    		$archivedBy = lang("n/a");
    	}
    	
    	$sentTimestamp = $this->getReceivedDate() instanceof DateTimeValue ? ($this->getReceivedDate()->isToday() ? format_time($this->getReceivedDate()) : format_datetime($this->getReceivedDate())) : lang('n/a');
    	
		return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName() != "" ? $this->getObjectName():lang('no subject'),
				"type" => $type,
				"tags" => $tags,
				"createdBy" => $createdBy,
				"createdById" => $createdById,
				"dateCreated" => $sentTimestamp,
				"updatedBy" => $createdBy,
				"updatedById" => $createdById,
				"dateUpdated" => $sentTimestamp,
				"wsIds" => $this->getWorkspacesIdsCSV(logged_user()->getWorkspacesQuery()),
    			"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
    			"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn,
    			"archivedById" => $this->getArchivedById(),
    			"archivedBy" => $archivedBy,
    			"dateArchived" => $archivedOn,
				"subject" => $this->getSubject(),
				"isRead" => $this->getIsRead(logged_user()->getId())
		);
	}
	
	/**
	 * Returns a plain text version of the email
	 * @return string
	 */
	function getTextBody() {
		if ($this->getBodyHtml()) {
			return html_to_text(html_entity_decode($this->getBodyHtml(),null, "UTF-8"));
		} else {
			return $this->getBodyPlain();
		}
	}
	
	
	function getFromContact(){
		$contacts = Contacts::findAll(array(
			'conditions' => " jt.email_address = '".clean($this->getFrom())."'",
			'join' => array(
				'jt_table' => ContactEmails::instance()->getTableName(),
				'jt_field' => 'contact_id',
				'e_field' => 'object_id',
			),
		));
		
		if (is_array($contacts) && count($contacts) > 0){
			return $contacts[0];
		}
		return null;
	}
	
	function getLinkedObjects() {
		$conv_emails = MailContents::getMailsFromConversation($this);
		$objects = array();
		foreach ($conv_emails as $mail){
			if(logged_user()->isMemberOfOwnerCompany()) {
				$mail_objects = $mail->getAllLinkedObjects();
			} else {
				if (is_null($mail->linked_objects)) {
					$mail->linked_objects = LinkedObjects::getLinkedObjectsByObject($this, true);
				}
				$mail_objects = $mail->linked_objects;
			}
			if (is_array($mail_objects)){
				foreach ($mail_objects as $mo){
					$objects[] = $mo;
				}
			}
		}
		
		if ($this->isTrashed()) {
			$include_trashed = true;
		} else {
			$include_trashed = false;
		}
		
		if ($include_trashed) {
			return $objects;
		} else {
			$ret = array();
			if (is_array($objects) && count($objects)) {
				foreach ($objects as $o) {
					if (!$o instanceof ContentDataObject || !$o->isTrashed()) {
						$ret[] = $o;
					}
				}
			}
			return $ret;
		}
	}
	

	/**
	 * Return object comments, filter private comments if user is not member of owner company
	 *
	 * @param void
	 * @return array
	 */
	function getComments() {
		return Comments::getCommentsByObjectIds(implode(',',$this->getConversationMailIds(true)));
	} // getComments
	
	

	function addToStatus($amount) {
		try {
			DB::beginWork();
			$state = -1;
			$saved = "false";
			$state = $this->getState();
			$this->setState($state + $amount);
			$this->save();
			$saved = "true";
			DB::commit();
			return true;
		} catch (Exception $e) {
			Logger::log("Could not advance email state, email skipped: ".$e->getMessage()."\nmail_id=".$this->getId()."\nstate=$state\nsaved=$saved");
			DB::rollback();
		}
		return false;
	}
	
	
	/**
	 * Override defaults. 
	 * Also adds mail to sharing table if is not categorized. 
	 * Only permissions for the account owner.  
	 * 
	 * @see ContentDataObject::addToSharingTable()
	 */
	function addToSharingTable() {	
		parent::addToSharingTable();
		$id = $this->getId();
		
		if(!$this->getAccount() instanceof MailAccount) return;
		
		$macs = MailAccountContacts::instance()->getByAccount($this->getAccount());
		foreach ($macs as $mac) {
		
			$contactId = $mac->getContactId();
			$contact = Contacts::instance()->findById($contactId);
			
			if (!$contact instanceof Contact) continue;
			
			$group_id = $contact->getPermissionGroupId();
			if ($group_id) {
				$sql = "INSERT INTO ".TABLE_PREFIX."sharing_table ( object_id, group_id ) VALUES ('$id','$group_id') ON DUPLICATE KEY UPDATE group_id=group_id";
				DB::execute($sql);
			}
			
		}
	}
	
	/**
	 * Use this function to order conversation
	 */
	function orderConversation() {
		$id = $this->getId();
		$convId = $this->getConversationId();
		$accountId = $this->getAccountId();
		$folderName = $this->getImapFolderName();
		$state = $this->getState();
		
		if ($state == 2) {
			$stateConditions = " AND state = '2'";
		} else if (in_array($state, array(1,3,5))) {
			$stateConditions = " AND state IN ('1','3','5')";
		} else if ($state == 0) {
			$stateConditions = " AND state = '0'";
		} else if ($state == 4) {
			$stateConditions = " AND state = '4'";
		} else if ($state >= 200) {
			$stateConditions = " AND state >= 200";
		}
		
		
		//revisar si soy el ultimo de la conersacion para mi carpeta
		$sql = "UPDATE `".TABLE_PREFIX."mail_contents` SET
						`conversation_last`=0
						WHERE `conversation_id`='$convId' AND `object_id` !='$id'  AND `account_id` ='$accountId' $stateConditions AND `conversation_last` ='1';
		";
		
		DB::execute($sql);		
	}
}
