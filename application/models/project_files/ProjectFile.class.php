<?php

/**
 * ProjectFile class
 * Generated on Tue, 04 Jul 2006 06:46:08 +0200 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectFile extends BaseProjectFile {
	



	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('description','name');


	/**
	 * Cached file type object
	 *
	 * @var FileType
	 */
	private $file_type;

	
	/**
	 * Last revision instance
	 *
	 * @var ProjectFileRevision
	 */
	private $last_revision;

	
	/**
	 * Cached checkout user object reference
	 *
	 * @var User
	 */
	private $checked_out_by = null;

	
	/**
	 * Contruct the object
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		$this->addProtectedAttribute('system_filename', 'filename', 'type_string', 'filesize');
		parent::__construct();
	} // __construct


	function getTitle(){
		return $this->getFilename();
	}
	
	
	function getFilename() {
		return $this->getObjectName() ;
	}

	/**
	 * Return all file revisions
	 *
	 * @param void
	 * @return array
	 */
	function getRevisions($exclude_last = false, $asc = false) {
		if($exclude_last) {
			$last_revision = $this->getLastRevision();
			if($last_revision instanceof ProjectFileRevision) $conditions = DB::prepareString('`object_id` <> ? AND `file_id` = ?', array($last_revision->getId(), $this->getId()));
		} // if
		
		$dir = $asc ? 'ASC' : 'DESC';
		
		if(!isset($conditions)) $conditions = DB::prepareString("`file_id` = ? AND `trashed_on` = '0000-00-00 00:00:00'", array($this->getId()));
		
		return ProjectFileRevisions::find(array(
	        'conditions' => $conditions,
	        'order' => '`created_on` ' . $dir
        )); // find
	} // getRevisions

	/**
	 * Return the number of file revisions
	 *
	 * @param void
	 * @return integer
	 */
	function countRevisions() {
		return ProjectFileRevisions::count(array(
        '`file_id` = ?', $this->getId()
		)); // count
	} // countRevisions

	/**
	 * Return revision number of last revision. If there is no revisions return 0
	 *
	 * @param void
	 * @return integer
	 */
	function getRevisionNumber() {
		$last_revision = $this->getLastRevision();
		return $last_revision instanceof ProjectFileRevision ? $last_revision->getRevisionNumber() : 0;
	} // getRevisionNumber

	/**
	 * Return last revision of this file
	 *
	 * @param void
	 * @return ProjectFileRevision
	 */
	function getLastRevision() {
		if(is_null($this->last_revision)) {
			$this->last_revision = ProjectFileRevisions::findOne(array(
	          'conditions' => array('`file_id` = ? and `trashed_by_id`=0', $this->getId()),
			  'order' => '`created_on` DESC',
	          'limit' => 1,
			)); // findOne
		} // if
		return $this->last_revision;
	} // getLastRevision

	
	/**
	 * Return file type object
	 *
	 * @param void
	 * @return FileType
	 */
	function getFileType() {
		$revision = $this->getLastRevision();
		return $revision instanceof ProjectFileRevision ? $revision->getFileType() : null;
	} // getFileType

	
	/**
	 * Return URL of file type icon
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTypeIconUrl($showImage = true, $size = '48x48') {
		$last_revision = $this->getLastRevision();
		return $last_revision instanceof ProjectFileRevision ? $last_revision->getTypeIconUrl($showImage, $size) : '';
	} // getTypeIconUrl

	
	// ---------------------------------------------------
	//  Check out
	// ---------------------------------------------------

	
	/**
	 * Checck out file
	 *
	 * @param bool $autoCheckOut Is true when the file was automatically checked out on edit
	 * @param User $user If null, logged user is used
	 * @return boolean
	 */
	function checkOut($autoCheckOut = false, $user = null){
		if(!$user)
			$user = logged_user();
		if($this->getCheckedOutById() != 0 && !$user->isAdministrator())
			return false;
		$this->setWasAutoCheckedAuto($autoCheckOut);	
		$this->setCheckedOutById($user->getId());
		$this->setCheckedOutOn(DateTimeValueLib::now());
		$this->setMarkTimestamps(false);
		$this->save();
		return true;
	}// checkOutByLoggedUser
	
	
	function checkIn() {
		if (!$this->canCheckin(logged_user())) {
			return false;
		}
		$this->setCheckedOutById(0);
		$this->setCheckedOutOn(EMPTY_DATETIME);
		$this->save();
		return true;
	}
	
	
	function cancelCheckOut() {
		if (!$this->canCheckin(logged_user())) {
			return false;
		}
		$this->setCheckedOutById(0);
		$this->setCheckedOutOn(EMPTY_DATETIME);
		$this->setMarkTimestamps(false);
		$this->save();
		return true;
	}
	
	
	function isCheckedOut()
	{
		return $this->getCheckedOutById() > 0;
	}

	
	/**
	 * Return user who checked out this message
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCheckedOutBy() {
		if(is_null($this->checked_out_by)) {
			if($this->columnExists('checked_out_by_id')) $this->checked_out_by = Contacts::findById($this->getCheckedOutById());
		} //
		return $this->checked_out_by;
	} // getCreatedBy

	
	/**
	 * Return display name of checkout user
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCheckedOutByDisplayName() {
		$checked_out_by = $this->getCheckedOutBy();
		return $checked_out_by instanceof Contact ? $checked_out_by->getObjectName() : lang('n/a');
	} // getCreatedByDisplayName

	
	/**
	 * Return card URL of created by user
	 *
	 * @param void
	 * @return string
	 */
	function getCheckedOutByCardUrl() {
		$checked_out_by = $this->getCheckedOutBy();
		return $checked_out_by instanceof Contact ? $checked_out_by->getCardUserUrl() : null;
	} // getCreatedByCardUrl


	// ---------------------------------------------------
	//  Revision interface
	// ---------------------------------------------------

	/**
	 * Return file type ID
	 *
	 * @param void
	 * @return integer
	 */
	function getFileTypeId() {
		$revision = $this->getLastRevision();
		return $revision instanceof ProjectFileRevision ? $revision->getFileTypeId() : null;
	} // getFileTypeId

	
	/**
	 * Return type string. We need to know mime type when forwarding file
	 * to the client
	 *
	 * @param void
	 * @return string
	 */
	function getTypeString() {
		$revision = $this->getLastRevision();
		return $revision instanceof ProjectFileRevision ? $revision->getTypeString() : ($this->getType() ==  ProjectFiles::TYPE_WEBLINK ? lang('weblink') : null);
	} // getTypeString

	
	/**
	 * Return file size in bytes
	 *
	 * @param void
	 * @return integer
	 */
	function getFileSize() {
		$revision = $this->getLastRevision();
		return $revision instanceof ProjectFileRevision ? $revision->getFileSize() : null;
	} // getFileSize

	
	/**
	 * Return file content
	 *
	 * @param void
	 * @return string
	 */
	function getFileContent() {
		$revision = $this->getLastRevision();
		return $revision instanceof ProjectFileRevision ? $revision->getFileContent() : null;
	} // getFileContent

	
	// ---------------------------------------------------
	//  Util functions
	// ---------------------------------------------------

	/**
	 * This function will process uploaded file
	 *
	 * @param array $uploaded_file
	 * @param boolean $create_revision Create new revision or update last one
	 * @param string $revision_comment Revision comment, if any
	 * @return ProjectFileRevision
	 */
	function handleUploadedFile($uploaded_file, $create_revision = true, $revision_comment = '') {
		$revision = null;
		if(!$create_revision) {
			$revision = $this->getLastRevision();
		} // if

		if (!is_array($uploaded_file) || count($uploaded_file) == 0) {
			throw new Exception(lang('uploaded file bigger than max upload size', format_filesize(get_max_upload_size())));
		}
		
		if(!($revision instanceof ProjectFileRevision)) {
			$revision = new ProjectFileRevision();
			$revision->setFileId($this->getId());
			$revision->setRevisionNumber($this->getNextRevisionNumber());
			//$revision->setRevisionNumber(78);

			if((trim($revision_comment) == '') && ($this->countRevisions() < 1)) {
				$revision_comment = lang('initial versions');
			} // if
		} // if

		if (strtolower(substr($uploaded_file['name'], -4)) == '.pdf' && $uploaded_file['type'] != 'application/pdf') {
			$uploaded_file['type'] = 'application/pdf';
		}

		$revision->deleteThumb(false); // remove thumb

		// We have a file to handle!

		//executes only while uploading files
		if(!is_array($uploaded_file) || !isset($uploaded_file['name']) || !isset($uploaded_file['size']) || !isset($uploaded_file['type']) || (!isset($uploaded_file['tmp_name']) || !is_readable($uploaded_file['tmp_name']) )) {
			throw new InvalidUploadError($uploaded_file);
		} // if

		if(isset($uploaded_file['error']) && ($uploaded_file['error'] > UPLOAD_ERR_OK)) {
			throw new InvalidUploadError($uploaded_file);
		} // if

		//eyedoc MOD
		$extension = get_file_extension(basename($uploaded_file['name']));
		if(($uploaded_file['type'] == 'application/octet-stream') && ($extension == 'eyedoc')) $uploaded_file['type'] = 'text/html';
		//eyedoc MOD
		
		// calculate hash
		if ($revision->columnExists('hash')) {
			$hash = hash_file("sha256", $uploaded_file['tmp_name']);
			$revision->setColumnValue('hash', $hash);
		}
		
		$repository_id = FileRepository::addFile($uploaded_file['tmp_name'], array('name' => $uploaded_file['name'], 'type' => $uploaded_file['type'], 'size' => $uploaded_file['size']));

		$revision->setRepositoryId($repository_id);
		$revision->deleteThumb(false);
		$revision->setFilesize($uploaded_file['size']);
		if ($uploaded_file['type'] == 'application/x-unknown-application') {
			$type = Mime_Types::instance()->get_type($extension);
			if ($type) {
				$revision->setTypeString($type);
			} else {
				$revision->setTypeString($uploaded_file['type']);
			}
		} else { 
			$revision->setTypeString($uploaded_file['type']);
		}
		
		if(trim($extension)) {
			$file_type = FileTypes::getByExtension($extension);
			if($file_type instanceof Filetype) {
                                if(!$file_type->getIsAllow()){
                                    flash_error(lang('file extension no allow'));
                                    return;
                                }else{
                                    $revision->setFileTypeId($file_type->getId());
                                }
				
			} // if
		} // if

		$revision->setComment($revision_comment);
		$revision->save();

		$this->last_revision = $revision; // update last revision
		
		return $revision;
	} // handleUploadedFile

	
	/**
	 * Return next revision number
	 *
	 * @param void
	 * @return integer
	 */
	function getNextRevisionNumber() {
		$last_revision = $this->getLastRevision();
		return $last_revision instanceof ProjectFileRevision ? $last_revision->getRevisionNumber() + 1 : 1;
	} // getNextRevisionNumber

	
	function isModifiable(){
		$co_by = $this->getCheckedOutById();
		if($co_by && $co_by != logged_user()->getId() )
			return false;
		return strcmp($this->getTypeString(),'txt')==0 
			|| strcmp($this->getTypeString(),'sprd')==0 
			|| strcmp($this->getTypeString(),'prsn')==0 
			|| substr($this->getTypeString(), 0, 4) == "text";
	}
	
	
	function isDisplayable() {
		return ( substr($this->getTypeString(), 0, 4) == "text" && $this->getTypeString() != "text/x-log" );
		// TODO Dewtect browser mimetypes
	}
	
	
	function isMP3() {
		return $this->getTypeString() == 'audio/mpeg' || $this->getTypeString() == 'audio/mp3';	
	}
	
	
	function getFileContentWithRealUrls() {
		$content = $this->getFileContent();
		return $content;
	}
	
	
	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return file modification URL
	 *
	 * @param void
	 * @return string
	 */
	function getModifyUrl() {
		if (strcmp('sprd', $this->getTypeString()) == 0) {
			return get_url('files', 'add_spreadsheet', array(
					'id' => $this->getId()
			));
		} else if (strcmp('txt', $this->getTypeString()) == 0 || strcmp('text/html', $this->getTypeString()) == 0) {
			return get_url('files', 'add_document', array(
					'id' => $this->getId()
			));
		} else if (strcmp('prsn', $this->getTypeString()) == 0) {
			return get_url('files', 'add_presentation', array(
					'id' => $this->getId()
			));
		} else if (substr($this->getTypeString(), 0, 4) == "text") {
			return get_url('files', 'text_edit', array(
					'id' => $this->getId()
			));
		} else {
			return get_url('files', 'edit_file', array(
					'id' => $this->getId()
			));
		}
	} // getModifyUrl

	
	/**
	 * Return file viewing URL
	 *
	 * @param void
	 * @return string
	 */
	function getOpenUrl() {
		if (strcmp('sprd', $this->getTypeString()) == 0) {
			return get_url('files', 'add_spreadsheet', array(
					'id' => $this->getId()
			));
		} else if (strcmp('txt', $this->getTypeString()) == 0) {
			return get_url('files', 'add_document', array(
					'id' => $this->getId()
			));
		} else if (strcmp('prsn', $this->getTypeString()) == 0) {
			return get_url('files', 'add_presentation', array(
					'id' => $this->getId()
			));
		} else {
			return get_url('files', 'download_file', array(
					'id' => $this->getId()
			));
		}
	} // getOpenUrl
	 
	
	/**
	 * Return slideshow URL
	 *
	 * @param void
	 * @return string
	 */
	function getSlideshowUrl() {
		return get_url('files', 'slideshow', array(
			'fileId' => $this->getId())
		); // get_url
	} // getModifyUrl

	
	/**
	 * Return file details URL
	 *
	 * @param void
	 * @return string
	 */
	function getDetailsUrl() {
		return get_url('files', 'file_details', array(
        'id' => $this->getId()
		)); // get_url
	} // getDetailsUrl
	
	
	function getViewUrl() {
		return $this->getDetailsUrl();
	}

	
	/**
	 * Return revisions URL
	 *
	 * @param void
	 * @return string
	 */
	function getRevisionsUrl() {
		return $this->getDetailsUrl() . '#revisions';
	} // getRevisionsUrl

	
	/**
	 * Return comments URL
	 *
	 * @param void
	 * @return string
	 */
	function getCommentsUrl() {
		return $this->getDetailsUrl() . '#objectComments';
	} // getCommentsUrl

	
	/**
	 * Return file download URL
	 *
	 * @param void
	 * @return string
	 */
	function getDownloadUrl() {
		return get_url('files', 'download_file', array(
        'id' => $this->getId())
		); // get_url
	} // getDownloadUrl

	
	/**
	 * Return edit file URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		$edit_func = 'edit_file';
		if($this->getType() == 1){
			$edit_func = 'edit_weblink';
		}
		return get_url('files', $edit_func, array(
        'id' => $this->getId())
		); // get_url
	} // getEditUrl

	
	/**
	 * Return checkout file URL
	 *
	 * @param void
	 * @return string
	 */
	function getCheckoutUrl() {
		return get_url('files', 'checkout_file', array(
        'id' => $this->getId())
		); // get_url
	} // getCheckoutUrl

	
	/**
	 * Return checkin file URL
	 *
	 * @param void
	 * @return string
	 */
	function getCheckinUrl() {
		return get_url('files', 'checkin_file', array(
        'id' => $this->getId())
		); // get_url
	} // getCheckinUrl

	
	/**
	 * Return copy file URL
	 *
	 * @return string
	 */
	function getCopyUrl() {
		return get_url('files', 'copy', array(
			'id' => $this->getId()
		));
	}
	
	
	/** Return undo checkout file URL
	 *
	 * @param void
	 * @return string
	 */
	function getUndoCheckoutUrl() {
		return get_url('files', 'undo_checkout', array(
        	'id' => $this->getId())); // get_url
	} // getUndoCheckoutUrl

	
	/**
	 * Return delete file URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('files', 'delete_file', array(
        'id' => $this->getId())
		); // get_url
	} // getDeleteUrl

	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	function canAdd(Contact $user, $context,&$notAllowedMember = ''){
		return can_add($user, $context, ProjectFiles::instance()->getObjectTypeId(),$notAllowedMember);
	}

	
	/**
	 * Retrns value of CAN_UPLOAD_FILES permission
	 *
	 * @param Contact $user
	 * @param Project $project
	 * @return boolean
	 */
	function canUpload(Contact $user, Member $member, $context_members) {
		return $this->canAddToMember($user, $member, $context_members);
	} // canUpload

	
	/**
	 * Empty implementation of abstract method. Message determins if user have view access
	 *
	 * @param void
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canView

	
	/**
	 * Returns true if user can download this file
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canDownload(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canDownload


	/**
	 * Check if specific user can edit this file
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		if ($this->isCheckedOut() && $this->getCheckedOutById() != $user->getId())return false;
		return can_write($user, $this->getMembers(), $this->getObjectTypeId());
	} // canEdit

	
	/**
	 * Check if specific user can delete this comment
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		if ($this->isCheckedOut() && $this->getCheckedOutById() != $user->getId())return false;
		return can_delete($user,$this->getMembers(), $this->getObjectTypeId());
	} // canDelete

	
	function canCheckout(Contact $user){
		return !$this->isCheckedOut() && can_write($user, $this->getMembers(), $this->getObjectTypeId());
	}

	
	function canCheckin(Contact $user){
		return $this->isCheckedOut() && can_write($user, $this->getMembers(), $this->getObjectTypeId())
		&& ($user->isAdministrator() || $user->isModerator() || $user->getId() == $this->getCheckedOutById());
	}

	
	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @param array $error
	 * @return null
	 */
	function validate(&$errors) {
		$extension = get_file_extension(basename($this->getFilename()));
		$known_type = FileTypes::getByExtension($extension);
		
		if(!$this->validatePresenceOf('name') || ($this->getFilename() == ".".$extension && $known_type instanceof FileType)) {
			$errors[] = lang('filename required');
		}
		if ($this->getType() != ProjectFiles::TYPE_DOCUMENT){
			if(!$this->validatePresenceOf('url') || $this->getUrl() == 'http://') {
				$errors[] = lang('weblink required');
			} // if				
		}
	} // validate
	
	/**
	 * Delete this file and all of its revisions
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		$this->clearRevisions();
		return parent::delete();
	} // delete
	
	
	/**
	 * Remove all revisions associate with this file
	 *
	 * @param void
	 * @return null
	 */
	function clearRevisions() {
		$revisions = $this->getRevisions();
		if(is_array($revisions)) {
			foreach($revisions as $revision) {
				$revision->delete();
			} // foreach
		} // if
	} // clearRevisions

	
	/**
	 * Remove all object relations from the database
	 *
	 * @param void
	 * @return boolean
	 */
	function clearObjectRelations() {
		return LinkedObjects::clearRelationsByObject($this);
	} // clearObjectRelations

	
	/**
	 * This function will return content of specific searchable column.
	 *
	 * It uses inherited behaviour for all columns except for `filecontent`. In case of this column function will return
	 * file content if file type is marked as searchable (text documents, office documents etc).
	 *
	 * @param string $column_name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if($column_name == 'filecontent') {
			$file_type = $this->getFileType();

			// Unknown type or type not searchable
			if(!($file_type instanceof FileType) || !$file_type->getIsSearchable()) {
				return null;
			} // if

			$content = $this->getFileContent();
			if(strlen($content) <= MAX_SEARCHABLE_FILE_SIZE) {
				return strip_tags($content);
			} // if
		} else {
			return parent::getSearchableColumnContent($column_name);
		} // if
	} // getSearchableColumnContent

	
	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getDetailsurl();
	} // getObjectUrl

	
	function getDashboardObject() {
		$ret = parent::getDashboardObject();
		$ret["mimeType"] = $this->getTypeString();
		return $ret;
	}
	
	
	function setFilename($name) {
		return $this->setObjectName($name) ;
	}
	
	/**
	 * 
	 * 
	 */
	function addToSharingTable() {
		// if classified or not belongs to an email
		$member_ids = array();
		$members = $this->getMembers();
		foreach ($members as $m) {
			$d = $m->getDimension();
			if ($d instanceof Dimension && $d->getIsManageable()) $member_ids[] = $m->getId();
		}
		if ($this->getMailId() == 0 || count($member_ids) > 0) {
			$revisions = $this->getRevisions();
			if (is_array($revisions)) {
				foreach ($revisions as $revision) {
					$revision->addToSharingTable();
				}
			}
			parent::addToSharingTable();
		} else {
			// if not classified and belongs to an email
			$mail = MailContents::findById($this->getMailId());
			if ($mail instanceof MailContent) {
				DB::execute("DELETE FROM ".TABLE_PREFIX."sharing_table WHERE object_id=".$this->getId());
				
				$macs = MailAccountContacts::findAll(array('conditions' => array('`account_id` = ?', $mail->getAccountId())));
				foreach ($macs as $mac) {
					$c = Contacts::findById($mac->getContactId());
					if ($c instanceof Contact) {
						$values = "(".$c->getPermissionGroupId().",".$this->getId().")";
						DB::execute("INSERT INTO ".TABLE_PREFIX."sharing_table (group_id, object_id) VALUES $values ON DUPLICATE KEY UPDATE group_id=group_id;");
					}
				}
			}
		}
	}

} // ProjectFile

?>