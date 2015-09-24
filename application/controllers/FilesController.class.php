<?php

/**
 * Controller that is responsible for handling project files related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>,  Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class FilesController extends ApplicationController {

	private $protocol;
	
	/**
	 * Construct the FilesController
	 *
	 * @access public
	 * @param void
	 * @return FilesController
	 */
	function __construct() {
		parent::__construct();
		
		$this->protocol = (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS')) ? 'https' : 'http';
		
		prepare_company_website_controller($this, 'website');
	} // __construct

	function init() {
		$js_manager_info = array('js_file' => "og/FileManager.js");
		Hook::fire('change_js_manager', $this, $js_manager_info);
		require_javascript(array_var($js_manager_info, 'js_file'), array_var($js_manager_info, 'plugin'));
		
		ajx_current("panel", "files", null, null, true);
		ajx_replace(true);
	}
	
	/**
	 * Show files index page (list recent files)
	 *
	 * @param void
	 * @return null
	 */
	function index() {
		tpl_assign('allParam', array_var($_GET,'all'));
		tpl_assign('userParam',  array_var($_GET,'user'));
		tpl_assign('typeParam',  array_var($_GET,'type'));
		if(isset($error))
		tpl_assign('error', $error);
	} // index

	// ---------------------------------------------------
	//  Files
	// ---------------------------------------------------

	function view() {
		$this->file_details();
	}
	
	/**
	 * Show file details
	 *
	 * @param void
	 * @return null
	 */
	function file_details() {
		$this->addHelper('textile');
		
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$revisions = $file->getRevisions();
		if(!count($revisions) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) {
			flash_error(lang('no file revisions in file'));
			ajx_current("empty");
			return;
		} // if

		//read object for this user
		$file->setIsRead(logged_user()->getId(),true);

		tpl_assign('file', $file);
		tpl_assign('last_revision', $file->getLastRevision());
		tpl_assign('revisions', $revisions);
		tpl_assign('order', null);
		tpl_assign('page', null);
		ajx_extra_data(array("title" => $file->getFilename(), 'icon'=>'ico-file'));
		ajx_set_no_toolbar(true);

		ApplicationReadLogs::createLog($file, ApplicationReadLogs::ACTION_READ);
	} // file_details

	function slideshow() {
		$this->setLayout('slideshow');
		$fileid = array_var($_GET, 'fileId');
		$file = ProjectFiles::instance()->findById($fileid);
		if(!$file->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$content = $error = null;
		if (!$file) {
			$error = 'File not found';
		} else if (strcmp($file->getTypeString(), 'prsn') != 0) {
			$error = 'File is not a presentation';
		} else {
			$content = remove_css_and_scripts($file->getFileContent());
		}
		tpl_assign('error', $error);
		tpl_assign('content', $content);
	}//slideshow

	/**
	 * Download specific file
	 *
	 * @param void
	 * @return null
	 */
	function download_file() {
		ajx_current("empty");

		$inline = (boolean) array_var($_GET, 'inline', false);
		
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			return;
		} // if
		
		//if don't have revision
		if(!($file->getLastRevision() instanceof ProjectFileRevision)) {
			flash_error(lang('file dnx'));
			return;
		} // if
		
		if(!$file->canDownload(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		if ($file->getTypeString() == 'sprd') die(lang("not implemented"));
		session_commit();

		if(array_var($_GET, 'checkout')){
			if(get_id('checkout') == 1){
				if(!$file->checkOut()){
					flash_error(lang('document checked out'));
					return;
				}
			}
		}

		if(get_id('validate') == 1){
			evt_add('download document', array('id' => get_id(), 'reloadDocs' => true));
			return;
		}
		
		$file->setIsRead(logged_user()->getId(), true);

		ApplicationReadLogs::createLog($file, ApplicationReadLogs::ACTION_DOWNLOAD);

		download_from_repository($file->getLastRevision()->getRepositoryId(), $file->getTypeString(), $file->getFilename(), !$inline);
		die();
	} // download_file
	
	
	/**
	 * get a public file
	 * @param $id the repository id of the file
	 * @return file url
	 */
	function get_public_file(){
		$id = array_var($_GET, 'id', 0);
		if (FileRepository::isInRepository($id) && FileRepository::getFileAttribute($id, 'public', false)) {
			$type = FileRepository::getFileAttribute($id,'type');
			download_from_repository($id, $type, $id, false);
			die();
		} // if
		die(lang('file dnx'));
	}// get_public_file
	
	
	function download_image() {
		$inline = (boolean) array_var($_GET, 'inline', false);
			
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			die();
		} // if
			
		if(!$file->canDownload(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			die();
		} // if
		session_commit();
		download_from_repository($file->getLastRevision()->getRepositoryId(), $file->getTypeString(), $file->getFilename(), !$inline);
		die();
	} // download_image

	
	function checkout_file()
	{
		ajx_current("empty");
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			return;
		} // if

		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		try{
			DB::beginWork();
			$file->checkOut();
			DB::commit();
			ApplicationLogs::createLog($file, ApplicationLogs::ACTION_CHECKOUT);
			flash_success(lang('success checkout file'));
			ajx_current("reload");
		}
		catch(Exception $e)
		{
			DB::rollback();
			flash_error($e->getMessage());
		}
	}
	
	
	function undo_checkout(){
		ajx_current("empty");
		$file = ProjectFiles::findById(get_id());
		if(!$file instanceof ProjectFile) {
			flash_error(lang('file dnx'));
			return;
		} // if

		if(!$file->canCheckin(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		try {
			$file->cancelCheckOut();
			
			flash_success(lang("success undo checkout file"));
			if (array_var($_GET, 'back', false)) {
				ajx_current("back");
			} else {
				ajx_current("reload");
			}
		} catch (Exception $e){
			Db::rollback();
			flash_error($e->getMessage());
		}
	}
	
	
	/**
	 * Download specific revision
	 *
	 * @param void
	 * @return null
	 */
	function download_revision() {
		$inline = (boolean) array_var($_GET, 'inline', false);
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!($file->canDownload(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		session_commit();
		download_from_repository($revision->getRepositoryId(),$revision->getTypeString(), $file->getFilename(), !$inline);
		die();
	} // download_revision

	
	/**
	 * Add weblink
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_weblink() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file_data = array_var($_POST, 'webpage');
	
		$file = new ProjectFile();
		
		if (!is_array(array_var($_POST, 'webpage'))) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
		}
		
		if(is_array(array_var($_POST, 'webpage'))) {
			try {
				$type = ProjectFiles::TYPE_WEBLINK;
				$file->setType($type);
				$file->setFilename(array_var($file_data, 'name'));
				$file->setFromAttributes($file_data);
				
				$url = array_var($file_data, 'url', '');
				if ($url && strpos($url, ':') === false) {
					$url = $this->protocol ."://". $url;
					$file->setUrl($url);
					
				}
				
				DB::beginWork();
				$file->save();
				
				$revision = new ProjectFileRevision();
				$revision->setFileId($file->getId());
				$revision->setRevisionNumber($file->getNextRevisionNumber());
				$revision->setFileTypeId(FileTypes::getByExtension('webfile')->getId());
				$revision->setTypeString($file->getUrl());
				$revision->setRepositoryId('webfile');
				$revision_comment = array_var($file_data, 'revision_comment', lang('initial versions'));
				$revision->setComment($revision_comment);
				
				
				
				$revision->save();
		
				$member_ids = json_decode(array_var($_POST, 'members'));
		
				//link it!
				$object_controller = new ObjectController();
				$object_controller->add_subscribers($file);
				$object_controller->add_to_members($file, $member_ids);
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);
		
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
		
		
				flash_success(lang('success add webpage', $file->getObjectName()));
				ajx_current("back");
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
				// Error...
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		
		}
		
			
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		
	} // add_weblink
	
	
	/**
	 * Add file
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_file() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file_data = array_var($_POST, 'file');

		$file = new ProjectFile();
		
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		
		if (!is_array(array_var($_POST, 'file'))) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
		}
		
		if (is_array(array_var($_POST, 'file'))) {
			foreach ($file_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			$this->setLayout("html");
			
			$upload_option = array_var($file_data, 'upload_option');
			$skipSettings = false;
			try {
				DB::beginWork();
				if ($upload_option && $upload_option != -1){
					$skipSettings = true;
					$file = ProjectFiles::findById($upload_option);
					$old_subs = $file->getSubscribers();
					
					// Mantain old subscribers
					foreach($old_subs as $user) {
						$value = "user_" . $user->getId();
						if (is_array($_POST['subscribers'])) {
							if (array_var($_POST['subscribers'], $value, null) != 'checked')
								$_POST['subscribers'][$value] = 'checked';
						}
					}
					
					if ($file->isCheckedOut()){
						if (!$file->canCheckin(logged_user())){
							flash_error(lang('no access permissions'));
							ajx_current("empty");
							return;
						}
						$file->setCheckedOutById(0);
					} else {  // Check for edit permissions
						if (!$file->canEdit(logged_user())){
							flash_error(lang('no access permissions'));
							ajx_current("empty");
							return;
						}
					}
				} else {
					
					$type = array_var($file_data, 'type');
					$file->setType($type);
					$file->setFilename(array_var($file_data, 'name'));
					$file->setFromAttributes($file_data);
					
					$file->setIsVisible(true);
				}
				
				$file->setAttachToNotification(array_var($file_data, 'attach_to_notification'));
				if(array_var($file_data, 'default_subject_sel') == 'subject'){
					$file->setDefaultSubject(array_var($file_data, 'default_subject_text'));
				}else{
					$file->setDefaultSubject('');
				}
				
				$file->save();
				
				if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());

					$revision_comment = array_var($file_data, 'revision_comment');
					$revision = $file->handleUploadedFile($uploaded_file, true, $revision_comment); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
					unset($_SESSION[$upload_id]);
				} else if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {
					$url = array_var($file_data, 'url', '');
					if ($url && strpos($url, ':') === false) {
						$url = $this->protocol ."://". $url;
						$file->setUrl($url);
						$file->save();
					}
					
					$revision = new ProjectFileRevision();
					$revision->setFileId($file->getId());
					$revision->setRevisionNumber($file->getNextRevisionNumber());
					$revision->setFileTypeId(FileTypes::getByExtension('webfile')->getId());
					$revision->setTypeString($file->getUrl());
					$revision->setRepositoryId('webfile');
					$revision_comment = array_var($file_data, 'revision_comment', lang('initial versions'));
					$revision->setComment($revision_comment);
					$revision->save();
				}
				$object_controller = new ObjectController();
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				//Add properties
				if (!$skipSettings){
					$object_controller->add_to_members($file, $member_ids);
				}

				//Add links
			    $object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);
				
				DB::commit();

				set_user_config_option('notify_myself_too', array_var($file_data, 'notify_myself_too'));
				if (array_var($file_data, 'notify_myself_too')) {
					logged_user()->notify_myself = true;
				}
				
				ApplicationLogs::createLog($file,ApplicationLogs::ACTION_ADD);
								
				if (array_var($file_data, 'notify_myself_too')) {
					logged_user()->notify_myself = false;
				}
				
				
				$ajx_file =  array();
				$ajx_file["file"][]= get_class($file->manager()) . ':' . $file->getId();

				flash_success(lang('success add file', $file->getFilename()));
	          	if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	ajx_add("overview-panel", "reload");
	          	ajx_extra_data($ajx_file);
	          	
	          	if (array_var($_REQUEST, 'modal')) {
	          		evt_add("reload current panel");
	          	}

			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				
				if ($e instanceof InvalidUploadError) {
					Logger::log("InvalidUploadError\n".$e->getTraceAsString());
					Logger::log(print_r($e->getAdditionalParams(), 1));
				} else {
					Logger::log("Error when uploading file: ".$e->getMessage()."\n".$e->getTraceAsString());
				}
				ajx_current("empty");

				// If we uploaded the file remove it from repository
				if(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
			} // try
		} // if
	} // add_file
	
	/**
	 * add_file_from_multi
	 *
	 * @access public
	 * @param $file_data
	 * @param $uploaded_file
	 * @param $member_ids
	 * @return file id
	 */
	function add_file_from_multi($file_data, $uploaded_file, $member_ids, $upload_option) {
		try {
			if($upload_option != -1){
				$file = ProjectFiles::findById($upload_option);
			}else{
				$file = new ProjectFile();
			}			
				
			$type = array_var($file_data, 'type');
			$file->setType($type);
			$file->setFilename(array_var($file_data, 'name'));
			$file->setFromAttributes($file_data);
	
			$file->setIsVisible(true);

			$file->setAttachToNotification(array_var($file_data, 'attach_to_notification'));
			if(array_var($file_data, 'default_subject_sel') == 'subject'){
				$file->setDefaultSubject(array_var($file_data, 'default_subject_text'));
			}else{
				$file->setDefaultSubject('');
			}
		
			DB::beginWork();
			
			$file->save();
		
			if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
				// handle uploaded file
				$revision_comment = array_var($file_data, 'revision_comment');
				$revision = $file->handleUploadedFile($uploaded_file, true, $revision_comment); // handle uploaded file
				@unlink($uploaded_file['tmp_name']);
			}
					
			$object_controller = new ObjectController();
			//$member_ids = json_decode(array_var($_POST, 'members'));
		
			if (count($member_ids) > 0 || !array_var($file_data, 'composing_mail')) {
				$object_controller->add_to_members($file, $member_ids);
			}
			$object_controller->add_subscribers($file, array('user_'.logged_user()->getId() => '1'));
			
			//If New file 
			if($upload_option == -1){
				//Add links
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				if (!array_Var($file_data, 'composing_mail')) {
					$object_controller->add_custom_properties($file);
				}
			}
			DB::commit();
			
			set_user_config_option('notify_myself_too', array_var($file_data, 'notify_myself_too'));
			if (array_var($file_data, 'notify_myself_too')) {
				logged_user()->notify_myself = true;
			}
			
			ApplicationLogs::createLog($file,ApplicationLogs::ACTION_ADD);
			
			if (array_var($file_data, 'notify_myself_too')) {
				logged_user()->notify_myself = false;
			}			
			
			return $file->getId();
		}catch(Exception $e) {
			DB::rollback();
			Logger::log("Error when uploading file: ".$e->getMessage()."\n".$e->getTraceAsString());
				// If we uploaded the file remove it from repository
			if(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
				FileRepository::deleteFile($revision->getRepositoryId());
			} // if			
			
			flash_error($e->getMessage());
		} // try
		
	} // add_file
	
	/**
	 * add_multiple_files
	 * Use this function to upload multiple files
	 * @access public
	 * @param null
	 */
	function add_multiple_files() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
	
		$file_data = array_var($_POST, 'file');
		
		if (!is_array(array_var($_POST, 'file'))) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
		}
	
		$file = new ProjectFile();
			
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
	
		if (is_array(array_var($_POST, 'file'))) {
			
			$this->setLayout("html");
				
			$upload_option = array_var($file_data, 'upload_option',-1);
			$skipSettings = false;
			try {
				
				$upload_id = array_var($file_data, 'upload_id');
				$uploaded_file = array_var($_SESSION, $upload_id, array());
				$member_ids = json_decode(array_var($_POST, 'members'));
				
				$notAllowedMember = '';
				$members = Members::findAll(array('conditions' => 'id IN ('.implode(',', $member_ids).')'));
				if(!$file->canAdd(logged_user(), $members, $notAllowedMember )) {
					if (str_starts_with($notAllowedMember, '-- req dim --')) $err_msg = lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in));
					else trim($notAllowedMember) == "" ? $err_msg = lang('you must select where to keep', lang('the file')) : $err_msg = lang('no context permissions to add',lang("files"),$notAllowedMember );
					
					throw new Exception($err_msg);
				}
				
				//files ids to return
				$file_ids = array();
				
				foreach ($uploaded_file['name'] as $key => $file_name) {
					$file_data_mult = $file_data;
					$file_data_mult['name'] = $file_name;
					$uploaded_file_mult['name'] = $file_name;
					$uploaded_file_mult['size'] = $uploaded_file['size'][$key];
					$uploaded_file_mult['type'] = $uploaded_file['type'][$key];
					$uploaded_file_mult['tmp_name'] = $uploaded_file['tmp_name'][$key];
					$uploaded_file_mult['error'] = $uploaded_file['error'][$key];
					
					if(count($uploaded_file['name']) != 1){
						$upload_option = -1;
					}
					
					if($file_data['name'] != ""){
						$file_data_mult['name'] = $file_data['name'];
					}
					$file_ids[] = $this->add_file_from_multi($file_data_mult, $uploaded_file_mult, $member_ids, $upload_option);
				}
				unset($_SESSION[$upload_id]);
					
				$ajx_file =  array();
				$file_titles = "";
				//data to return
				foreach ($file_ids as $file_id) {
					$file_to_ret = ProjectFiles::findById($file_id);
					if($file_to_ret instanceof ProjectFile){
						$ajx_file["file"][]= get_class($file_to_ret->manager()) . ':' . $file_to_ret->getId();
						$file_titles .= ($file_titles == "" ? "" : ", ") . $file_to_ret->getFilename();
					}
				}
					
				flash_success(lang('success add file', $file_titles));
				if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
				} else {
					ajx_current("back");
				}
				ajx_add("overview-panel", "reload");
				ajx_extra_data($ajx_file);
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
	
			} catch(Exception $e) {				
				flash_error($e->getMessage());
				
				if ($e instanceof InvalidUploadError) {
					Logger::log("InvalidUploadError\n".$e->getTraceAsString());
					Logger::log(print_r($e->getAdditionalParams(), 1));
				} else {
					Logger::log("Error when uploading file: ".$e->getMessage()."\n".$e->getTraceAsString());
				}
				ajx_current("empty");
			} // try
		} else {
			if (array_var($_REQUEST, 'modal')) {
				ajx_current("empty");
				$this->setLayout("json");
				//print_modal_json_response(array('msg' => lang('success add file', $file_titles)));
				$this->setTemplate(get_template_path("empty"));
				evt_add("reload current panel");
			}
		}
	} // add_file
	
	function quick_add_files() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file_data = array_var($_POST, 'file');

		$file = new ProjectFile();
			
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('object_id', array_var($_GET, 'object_id'));
		tpl_assign('new_rev_file_id', array_var($_GET, 'new_rev_file_id'));
		tpl_assign('composing_mail', array_var($_GET, 'composing_mail'));
			
		if (is_array(array_var($_POST, 'file'))) {
			//$this->setLayout("html");
			$upload_option = array_var($file_data, 'upload_option');
			try {
				DB::beginWork();
				
				$uploading_revision_for_existing_file = false;
				$file_id = array_var($file_data, 'new_rev_file_id');
				if ($file_id > 0) {
					$f = ProjectFiles::findById($file_id);
					if ($f instanceof ProjectFile) {
						$file = $f;
						$uploading_revision_for_existing_file = true;
					}
				}
				
				if (!$uploading_revision_for_existing_file) {
					$type = array_var($file_data, 'type');
					$file->setType($type);
					$file->setFilename(array_var($file_data, 'name'));
					$file->setFromAttributes($file_data);
					
					$file->setIsVisible(true);
					
					$file->save();
				} else {
					// set updated on
					$file->setUpdatedById(logged_user()->getId());
					$file->setUpdatedOn(DateTimeValueLib::now());
					if ($file->isCheckedOut()){
						$file->checkIn();
					}
					$file->save();
				}
				$file->subscribeUser(logged_user());
					
				if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					$revision = $file->handleUploadedFile($uploaded_file, true, array_var($file_data, 'revision_comment')); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
					unset($_SESSION[$upload_id]);
				} else if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {
					$url = array_var($file_data, 'url', '');
					if ($url && strpos($url, ':') === false) {
						$url = $this->protocol . $url;
						$file->setUrl($url);
						$file->save();
					}
					$revision = new ProjectFileRevision();
					$revision->setFileId($file->getId());
					$revision->setRevisionNumber($file->getNextRevisionNumber());
					$revision->setFileTypeId(FileTypes::getByExtension('webfile')->getId());
					$revision->setTypeString($file->getUrl());
					$revision->setRepositoryId('webfile');
					$revision_comment = array_var($file_data, 'revision_comment', lang('initial versions'));
					$revision->setComment($revision_comment);
					$revision->save();
				}

				$member_ids = array();
				$object_controller = new ObjectController();
									
				if(array_var($file_data, 'member_ids')) {
					$member_ids = explode(',', array_var($file_data, 'member_ids'));
					if (is_numeric($member_ids) && $member_ids > 0) $member_ids = array($member_ids);
				}elseif(array_var($file_data, 'object_id')){
					$object = Objects::findObject(array_var($file_data, 'object_id'));
					if ($object instanceof ContentDataObject) {
						$member_ids = $object->getMemberIds();
						$object_controller->add_to_members($file, $member_ids);
					} else {
						// add only to logged_user's person member						
					}
				}elseif(count(active_context_members(false)) > 0 ){
					$object_controller->add_to_members($file, active_context_members(false));
				}else {
					// add only to logged_user's person member					
				}
				
				DB::commit();
				
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_UPLOAD, false, false);
				
				ajx_extra_data(array("file_id" => $file->getId()));
				ajx_extra_data(array("file_name" => $file->getFilename()));
				ajx_extra_data(array("icocls" => 'ico-file ico-' . str_replace(".", "_", str_replace("/", "-", $file->getTypeString()))));

				if (!array_var($_POST, 'no_msg')) {
					flash_success(lang('success add file', $file->getFilename()));
				}
				
				if ($uploading_revision_for_existing_file) {
					ajx_current("reload");
				} else {
					ajx_current("empty");
				}
				
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");

				// If we uploaded the file remove it from repository
				if(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
			} // try
		} // if
	} // quick_add_files
	
	/**
	 * quick_add_multiple_files
	 * Use this function to upload multiple files
	 * @access public
	 * @param null
	 */
	function quick_add_multiple_files() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file_data = array_var($_POST, 'file');
	
		$file = new ProjectFile();
			
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		tpl_assign('genid', array_var($_GET, 'genid'));
		tpl_assign('object_id', array_var($_GET, 'object_id'));
		tpl_assign('composing_mail', array_var($_GET, 'composing_mail'));
		
		if (is_array(array_var($_POST, 'file'))) {
			//$this->setLayout("html");
			$upload_option = array_var($file_data, 'upload_option',-1);
			try {
								
				//members
				$member_ids = array();
				$object_controller = new ObjectController();
				if(count(active_context_members(false)) > 0 ){
					$member_ids = active_context_members(false);
					
				} elseif(array_var($file_data, 'member_ids')) {
					$member_ids = explode(',', array_var($file_data, 'member_ids'));
					if (is_numeric($member_ids) && $member_ids > 0) $member_ids = array($member_ids);
					
				}elseif(array_var($file_data, 'object_id')){
					$object = Objects::findObject(array_var($file_data, 'object_id'));
					if ($object instanceof ContentDataObject) {
						$member_ids = $object->getMemberIds();
					} else {
						// add only to logged_user's person member
					}
				} else {
					// add only to logged_user's person member
				}			
				
				$upload_id = array_var($file_data, 'upload_id');
				$uploaded_file = array_var($_SESSION, $upload_id, array());
				
				//files ids to return
				$file_ids = array();
				if (isset($uploaded_file['name']) && is_array($uploaded_file['name'])) {
					foreach ($uploaded_file['name'] as $key => $file_name) {
						if (count($uploaded_file['name']) == 1 && array_var($file_data, 'name') != "" && array_var($file_data, 'name') != $file_name) {
							$file_name = array_var($file_data, 'name');
						}
						$file_data_mult = $file_data;
						$file_data_mult['name'] = $file_name;
						$uploaded_file_mult['name'] = $file_name;
						$uploaded_file_mult['size'] = $uploaded_file['size'][$key];
						$uploaded_file_mult['type'] = $uploaded_file['type'][$key];
						$uploaded_file_mult['tmp_name'] = $uploaded_file['tmp_name'][$key];
						$uploaded_file_mult['error'] = $uploaded_file['error'][$key];
						
						if(count($uploaded_file['name']) != 1){
							$upload_option = -1;
						}
						
						$file_ids[] = $this->add_file_from_multi($file_data_mult, $uploaded_file_mult, $member_ids, $upload_option);
					}
				}
				unset($_SESSION[$upload_id]);
	
				
				
				//data to return
				$files_data_to_return = array();
				foreach ($file_ids as $file_id) {
					$file_to_ret = ProjectFiles::findById($file_id);
					if (!$file_to_ret instanceof ProjectFile) continue;
					$file_data = array();
					$file_data["file_id"] = $file_to_ret->getId();
					$file_data["file_name"] = $file_to_ret->getFilename();
					$file_data["icocls"] = 'ico-file ico-' . str_replace(".", "_", str_replace("/", "-", $file_to_ret->getTypeString()));
					$files_data_to_return[] = $file_data;
				}
				
				ajx_extra_data(array("files_data" => $files_data_to_return));
					
				ajx_current("empty");
	
			} catch(Exception $e) {				
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // quick_add_files
	
	
	function temp_file_upload() {
		ajx_current("empty");
		$id = array_var($_GET, 'id');
		$uploaded_file = array_var($_FILES, 'file_file');
		$multiple = isset($_FILES['file_file']) && is_array($_FILES['file_file']['name']);
		if($multiple){
			foreach ($_FILES['file_file']['name'] as $file) {
				$fname[] = ROOT . "/tmp/".gen_id();
			}
		}else{
			$fname = ROOT . "/tmp/$id";
		}
		
		
		if (!empty($uploaded_file['tmp_name'])) {
			if($multiple){
				foreach ($fname as $key => $file) {
					copy($uploaded_file['tmp_name'][$key], $file);
				}
			}else{
				copy($uploaded_file['tmp_name'], $fname);
			}			
			$_SESSION[$id] = array(
				'name' => $uploaded_file['name'],
				'size' => $uploaded_file['size'],
				'type' => $uploaded_file['type'],
				'tmp_name' => $fname,
				'error' => $uploaded_file['error']
			);
		}
	}
	
	
	private function upload_document_image($url, $filename, $img_num) {
		$file_dt = array();
		$file_content = file_get_contents(html_entity_decode($url));
		$extension = get_file_extension($url);
		if (strpos($extension, "c=files") !== FALSE) $extension = "jpg";
		$name = $filename . "-img-$img_num.$extension";
		$description = lang("this file is included in document", $filename);
		
		$tmp_name = ROOT . "/tmp/" . gen_id() . $extension;
		file_put_contents($tmp_name, $file_content);
		
		$file_dt['name'] = $name;
		$file_dt['size'] = strlen($file_content);
		$file_dt['type'] = Mime_Types::instance()->get_type($extension);
		$file_dt['tmp_name'] = $tmp_name;
		
		$file = ProjectFiles::getByFilename($name);
		if ($file) {
			$file->delete();
		}
		$file = new ProjectFile();
		$file->setIsVisible(true);
		$file->setFilename($name);
		$file->setDescription($description);
		$file->setArchivedById(logged_user()->getId());
		$file->setArchivedOn(DateTimeValueLib::now());
		$file->save();
		
		$file->handleUploadedFile($file_dt, true, $description);
		
		//$FIXME file->addToWorkspace(active_or_personal_project());
		ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
		
		unlink($tmp_name);
		return $file->getId();
	}
	
	
	function save_document() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$postFile = array_var($_POST, 'file');
		$fileId = array_var($postFile, 'id');
		if($fileId > 0) {
			//edit document
			try {
				$file = ProjectFiles::findById($fileId);
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				$file_content = array_var($_POST, 'fileContent');
				$content_changed = $file_content != $file->getFileContent();
				if (!$content_changed && $file->getObjectName() == $postFile['name']){
					
					flash_error(lang('there are no changes'));
					
				} else {
					DB::beginWork();
					$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
					$revision_comment = array_var($postFile, 'comment');
	
					$file_content = array_var($_POST, 'fileContent');
					$image_file_ids = array();
					preg_match_all("/<img[^>]*src=[\"']([^\"']*)[\"']/", $file_content, $matches);
					$urls = array_var($matches, 1);
					if (is_array($urls)) {
						$img_num = 1;
						foreach ($urls as $url) {
							if (strpos(html_entity_decode($url), get_url('files', 'download_image')) === false ) {
								$img_file_id = self::upload_document_image($url, $file->getFilename(), $img_num);
								$file_content = str_replace($url, get_url('files', 'download_image', array('id' => $img_file_id, 'inline' => 1)) , $file_content);
								$image_file_ids[] = $img_file_id;
							}
							$img_num++;
						}
					}
					
					$file_dt['name'] = $file->getFilename();
					$file_dt['size'] = strlen($file_content);
					$file_dt['type'] = array_var($_POST, 'fileMIME', 'text/html');
					$file_dt['tmp_name'] = ROOT . '/tmp/' . rand () ;
					$handler = fopen($file_dt['tmp_name'], 'w');
					fputs($handler,$file_content);
					fclose($handler);
					$name = array_var($postFile, 'name');
					if (array_var($postFile, 'extension') != '') {
						$name = $name .".". array_var($postFile, 'extension');
					}
	
					$file->setFilename($name);
					$file->save();
					if ($content_changed) {
						$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
					}
					
					if (array_var($_POST, 'checkin', false)) {
						$file->checkIn();
						ajx_current("back");
					}
					
					$object_controller = new ObjectController();
					$file_member_ids = $file->getMemberIds();
					if (count($image_file_ids) > 0) {
						$image_files = ProjectFiles::findAll(array('conditions' => 'id IN ('.implode(',',$image_file_ids).')'));
						foreach ($image_files as $img_file) {
							$object_controller->add_to_members($img_file, $file_member_ids, null, false);
							$img_file->setMailId($file->getId());
							$img_file->save();
						}
					}
					
					//subscribe user if not subscribed
					if(!$file->isSubscriber(logged_user())) {
						$file->subscribeUser(logged_user());
					} // if
					
					
					DB::commit();
					ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);
					unlink($file_dt['tmp_name']);
	
					flash_success(lang('success save file', $file->getFilename()));
					evt_add("document saved", array("id" => $file->getId(), "instance" => array_var($_POST, 'instanceName')));
					ajx_add("overview-panel", "reload");
				}
			} catch(Exception $e) {
				DB::rollback();
				if (array_var($file_dt, 'tmp_name') && is_file(array_var($file_dt, 'tmp_name'))) {
					unlink(array_var($file_dt, 'tmp_name'));
				}
				flash_error(lang('error while saving'), $e->getMessage());
				
			} // try
		} else  {
			// new document
			$notAllowedMember = '';
			if (!ProjectFile::canAdd(logged_user(), active_context(),$notAllowedMember)) {
				if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
				else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the file'))) : flash_error(lang('no context permissions to add',lang("documents"),$notAllowedMember));
				ajx_current("empty");
				return ;
			} // if
			
			// prepare the file object
			$file = new ProjectFile();
			$name = array_var($postFile, 'name');
			if (array_var($postFile, 'extension') != '') {
				$name = $name .".". array_var($postFile, 'extension');
			}
			$file->setObjectTypeId($file->getObjectTypeId());
			$file->setFilename($name);
			$file->setIsVisible(true);
			
			
			//seteo esto para despues setear atributos
			$file_content = array_var($_POST, 'fileContent');
			$image_file_ids = array();
			preg_match_all("/<img[^>]*src=[\"']([^\"']*)[\"']/", $file_content, $matches);
			$urls = array_var($matches, 1);
			if (is_array($urls)) {
				$img_num = 1;
				foreach ($urls as $url) {
					if (strpos($url, get_url('files', 'download_image')) === false ) {
						$img_file_id = self::upload_document_image($url, $file->getFilename(), $img_num);
						$file_content = str_replace($url, get_url('files', 'download_image', array('id' => $img_file_id, 'inline' => 1)) , $file_content);
						$image_file_ids[] = $img_file_id;
					}
					$img_num++;
				}
			}
			
			if (array_var($postFile, 'extension') != '') {
				$postFile['name'] = $postFile['name'] .".". array_var($postFile, 'extension');
			}
			
			$file_dt['name'] = array_var($postFile,'name');
			$file_dt['size'] = strlen($file_content);
			$file_dt['type'] = array_var($_POST, 'fileMIME', 'text/html');

			$file->setCreatedOn(new DateTimeValue(time()) );
			try {
				DB::beginWork();
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, array_var($_POST, 'fileContent'));
				fclose($handler);

				$revision_comment = array_var($postFile, 'comment');
				
				$file->save();
				$file->subscribeUser(logged_user());
				$revision = $file->handleUploadedFile($file_dt, true, $revision_comment); //FIXME

				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$object_controller = new ObjectController();
				
				// file is added to current context members
				$member_ids = array();
				$selection = active_context();
				foreach ($selection as $member) {
					if ($member instanceof Member) $member_ids[] = $member->getId();
				}
				$object_controller->add_to_members($file, $member_ids);
				
				if (count($image_file_ids) > 0) {
					$image_files = ProjectFiles::findAll(array('conditions' => 'id IN ('.implode(',',$image_file_ids).')'));
					foreach ($image_files as $img_file) {
						$object_controller->add_to_members($img_file, $member_ids, null, false);
						$img_file->setMailId($file->getId());
						$img_file->save();
					}
				}
				
				
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
				flash_success(lang('success save file', $file->getObjectName()));
				evt_add("document saved", array("id" => $file->getId(), "instance" => array_var($_POST, 'instanceName')));
				unlink($file_dt['tmp_name']);
				
			} catch(Exception $e) {
				DB::rollback();

				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if	(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				}
				flash_error(lang('error while saving').": ".$e->getMessage());
			} // try
		}
	}

	function save_presentation() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$postFile = array_var($_POST, 'file');
		$fileid = array_var($postFile, 'id');
		if($fileid > 0) {
			//edit presentation
			try {
				$file = ProjectFiles::findById($fileid);
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				DB::beginWork();
				$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
				$revision_comment = '';

				$file_dt['name'] = $file->getFilename();
				$file_content = unescapeSLIM(array_var($_POST, 'slimContent'));
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'prsn';
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand() ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler,$file_content);
				fclose($handler);
				$file->setFilename(array_var($postFile, 'name'));
				$file->save();
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				if (array_var($_POST, 'checkin', false)) {
					$file->checkIn();
					ajx_current("back");
				}
				
				
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
				evt_add("presentation saved", array("id" => $file->getId()));
				
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				flash_error(lang('error while saving'));
				
			} // try
		} else  {
			// new presentation
			$notAllowedMember = '';
			if (!ProjectFile::canAdd(logged_user(), active_context(), $notAllowedMember)) {
				if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
				else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the file'))) : flash_error(lang('no context permissions to add',lang("presentations"),$notAllowedMember));
				$this->redirectToReferer(get_url('files'));
				return ;
			} // if

			// prepare the file object
			$file = new ProjectFile();
			$file->setFilename(array_var($postFile, 'name'));
			$file->setIsVisible(true);

			//seteo esto para despues setear atributos
			$file_content = unescapeSLIM(array_var($_POST, 'slimContent'));
			$file_dt['name'] = array_var($postFile, 'name');
			$file_dt['size'] = strlen($file_content);
			$file_dt['type'] = 'prsn';

			$file->setCreatedOn(new DateTimeValue(time()) );
			try {
				DB::beginWork();
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, unescapeSLIM(array_var($_POST, 'slimContent')));
				fclose($handler);

				$file->save();
				$file->subscribeUser(logged_user());
				$revision = $file->handleUploadedFile($file_dt, true);

				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				
				$object_controller = new ObjectController();
				
				// file is added to current context members
				$member_ids = array();
				$selection = active_context();
				foreach ($selection as $member) {
					if ($member instanceof Member) $member_ids[] = $member->getId();
				}
				$object_controller->add_to_members($file, $member_ids);
				
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
				flash_success(lang('success save file', $file->getFilename()));
				evt_add("presentation saved", array("id" => $file->getId()));
				unlink($file_dt['tmp_name']);
				
			} catch(Exception $e) {
				DB::rollback();
				
				tpl_assign('file', new ProjectFile()); // reset file
				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if	(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				}
				flash_error(lang('error while saving').": ".$e->getMessage());
				
			} // try
		}
	}

	function save_spreadsheet() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		$id = get_id();
		$file_content = array_var($_GET, "book");
		$name = trim(array_var($_GET, 'name', ''));
		if ($id > 0) {
			//edit spreadsheet
			if ($name == '') $name = $file->getFilename();
			try {
				$file = ProjectFiles::findById(get_id());
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				DB::beginWork();
				$file->setFilename($name);
				$post_revision = true;
				$revision_comment = '';

				$file_dt['name'] = $name;
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'sprd';
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand () ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);
				$file->save();
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
								
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
				ajx_add("overview-panel", "reload");
				ajx_extra_data(array("sprdID" => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				tpl_assign('error', $e);
				flash_error(lang('error while saving'));
			} // try
		} else  {
			//new spreadsheet
			if ($name == '') $name = lang('new spreadsheet');
			try {
				if(!ProjectFile::canAdd(logged_user() , active_context())) {
					flash_error(lang('no access permissions'));
					$this->redirectToReferer(get_url('files'));
					return ;
				} // if
	
				// create the file object
				$file = new ProjectFile();
				$file->setFilename($name);
				$file->setIsVisible(true);
	
				//seteo esto para despues setear atributos
				$file_dt['name'] = $name;
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'sprd';
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);

				$file->setCreatedOn(new DateTimeValue(time()));
				
				DB::beginWork();

				$file->save();
				$file->subscribeUser(logged_user());
				//FIXME $file->addToWorkspace(active_or_personal_project());
				$revision = $file->handleUploadedFile($file_dt, true); // handle uploaded file
				
				
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
				unlink($file_dt['tmp_name']);
				flash_success(lang('success add file', $file->getFilename()));
				ajx_extra_data(array("sprdID" => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();
					
				tpl_assign('error', $e);
				tpl_assign('file', new ProjectFile()); // reset file
				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if (isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
				flash_error(lang('error while saving'));
			} // try
		}//new spreadsheet
	}

	function text_edit() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file_data = array_var($_POST, 'file');
		if (!isset($file_data)) {
			// open text file
			$file = ProjectFiles::findById(get_id());
			if (!($file instanceof ProjectFile)) {
				flash_error(lang('file dnx'));
				ajx_current("empty");
				return;
			} // if

			if (!$file->canEdit(logged_user())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

				
			tpl_assign('file', $file);
		} else {
			ajx_current("empty");
			// save new file content
			try {
				$file = ProjectFiles::findById(array_var($file_data, 'id'));
				if (!($file instanceof ProjectFile)) {
					flash_error(lang('file dnx'));
					ajx_current("empty");
					return;
				} // if
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					return;
				} // if
				DB::beginWork();
				$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
				$revision_comment = '';

				$file_dt['name'] = $file->getFilename();
				$file_content = EncodingConverter::instance()->convert(detect_encoding(array_var($_POST, 'fileContent'), array('UTF-8','ISO-8859-1')), array_var($file_data, 'encoding'),array_var($_POST, 'fileContent'));
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = $file->getTypeString();
				$file_dt['tmp_name'] = ROOT . '/tmp/' . rand () ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);
				
				$file->save();
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				
				DB::commit();
				ApplicationLogs::createLog($file,ApplicationLogs::ACTION_EDIT);
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				flash_error(lang('error while saving'));
			} // try
		}// if
	} // text_edit

	
	function add_document() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (get_id() > 0) {
			//open a document
			try {
				DB::beginWork();
				
				$file = ProjectFiles::findById(get_id());
				if (!($file instanceof ProjectFile)) {
					throw new Exception(lang('file dnx'));
				}
	
				if(!$file->canEdit(logged_user()) || !$file->isModifiable()) {
					if ($file->isCheckedOut() && !$file->canCheckin(logged_user())) {
						throw new Exception(lang('error document checked out by another user'));
					} else {
						throw new Exception(lang('no access permissions'));
					}
				}
				
				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$file_data = array_var($_POST, 'file');
				if (!is_array($file_data)) {
					$file_data = array('description' => $file->getDescription());
				}
	
				tpl_assign('file', $file);
				tpl_assign('file_data', $file_data);
				DB::commit();
			} catch (Exception $e) {
				ajx_current("empty");
				DB::rollback();
				flash_error($e->getMessage());
			}
		} else {
			//new document
			if (!ProjectFile::canAdd(logged_user(), active_context(), $notAllowedMember )) {
				if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
				else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the file'))) : flash_error(lang('no context permissions to add', lang("documents"),$notAllowedMember));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			$file_data = array_var($_POST, 'file');
			
			tpl_assign('file', $file);
			tpl_assign('file_data', $file_data);
		}//end new document
	} // add_document

	
	function add_spreadsheet() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (get_id() > 0) {
			//open a spreadsheet
			$file = ProjectFiles::findById(get_id());
			if(!($file instanceof ProjectFile)) {
				flash_error(lang('file dnx'));
				ajx_current("empty");
				return;
			} // if

			if(!$file->canEdit(logged_user())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

			tpl_assign('file', $file);
		} else {
			// new spreadsheet
			if (!ProjectFile::canAdd(logged_user(), active_context())) {
				flash_error(lang('no context permissions to add',lang("spreadsheets")));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			tpl_assign('file', $file);
		}
	} // add_spreadsheet

	
	function add_presentation() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (get_id() > 0) {
			//open presentation
			try {
				DB::beginWork();
				$this->setTemplate('add_presentation');
				$file = ProjectFiles::findById(get_id());
	
				if (!($file instanceof ProjectFile)) {
					throw new Exception(lang('file dnx'));
				} // if
	
				if (!$file->canEdit(logged_user())) {
					if ($file->isCheckedOut() && !$file->canCheckin(logged_user())) {
						throw new Exception(lang('error document checked out by another user'));
					} else {
						throw new Exception(lang('no access permissions'));
					}
				} // if
	
				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$file_data = array_var($_POST, 'file');
				if (!is_array($file_data)) {
					$file_data = array(
						'description' => $file->getDescription(),
					); // array
				} // if
				tpl_assign('file', $file);
				tpl_assign('file_data', $file_data);
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		} else {
			//new presentation
			$notAllowedMember = '' ;
			if (!ProjectFile::canAdd(logged_user(), active_context(), $notAllowedMember)) {
				if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
				else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the file'))) : flash_error(lang('no context permissions to add',lang("presentations"), $notAllowedMember));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			$file_data = array_var($_POST, 'file');

			tpl_assign('file', $file);
			tpl_assign('file_data', $file_data);
		}
	}

	
	function list_files() {
		
		ajx_current("empty");
		// get query parameters 
		$start = (integer)array_var($_GET,'start');
		$limit = (integer)array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET,'sort');
		$order_dir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit)+1;
		$type = array_var($_GET,'type');
		$user = array_var($_GET,'user');

		// if there's an action to execute, do so 
		if (array_var($_GET, 'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
				if (isset($file) && $file->canDelete(logged_user())) {
					try{
						DB::beginWork();
						$file->trash();
						DB::commit();
						ApplicationLogs::createLog($file, ApplicationLogs::ACTION_TRASH);
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					if (!$file instanceof ProjectFile) {
						evt_add("popup", array('title' => lang('error'), 'message' => lang("file dnx")));
					} else if (!$file->canDelete(logged_user())) {
						evt_add("popup", array('title' => lang('error'), 'message' => lang("cannot delete file", $file->getObjectName())));
					}
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success delete files", $succ));
			} else {
				flash_error(lang("error delete files", $err));
			}

		} else if (array_var($_GET, 'action') == 'markasread') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
					try {
						$file->setIsRead(logged_user()->getId(),true);
						$succ++;
						
					} catch(Exception $e) {
						$err ++;
					} // try
				}//for
			if ($succ <= 0) {
				flash_error(lang("error markasread files", $err));
			}
		}else if (array_var($_GET, 'action') == 'markasunread') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$succ = 0; $err = 0;
				foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
					try {
						$file->setIsRead(logged_user()->getId(),false);
						$succ++;

					} catch(Exception $e) {
						$err ++;
					} // try
				}//for
			if ($succ <= 0) {
				flash_error(lang("error markasunread files", $err));
			}
		}
		 else if (array_var($_GET, 'action') == 'zip_add') {
			$this->zip_add();
		} else if (array_var($_GET, 'action') == 'archive') {
			$ids = explode(',', array_var($_GET, 'ids'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
				if (isset($file) && $file->canEdit(logged_user())) {
					try{
						DB::beginWork();
						$file->archive();
						DB::commit();
						ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ARCHIVE);
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success archive objects", $succ));
			} else {
				flash_error(lang("error archive objects", $err));
			}
		}
		
		Hook::fire('classify_action', null, $ret);		
		$join_params = null;
		$select_columns = null;
		$extra_conditions = "";
		if (strpos($order, 'p_') == 1 ){
			$cpId = substr($order, 3);
			$order = 'customProp';
		}
		if ($order == ProjectFiles::ORDER_BY_POSTTIME) {
			$order = '`created_on`';
		} else if ($order == ProjectFiles::ORDER_BY_MODIFYTIME) {
			$order = '`updated_on`';
		} else if ($order == ProjectFiles::ORDER_BY_SIZE) {
			$order = '`jt`.`filesize`';
			$join_params = array(
				'table' => ProjectFileRevisions::instance()->getTableName(),
				'jt_field' => 'file_id',
				'e_field' => 'object_id',
			);
			$extra_conditions .= " AND `jt`.`object_id` = (SELECT max(`x`.`object_id`) FROM ".TABLE_PREFIX."project_file_revisions `x` WHERE `x`.`file_id` = `e`.`object_id`)";
		}else if ($order == 'customProp') {
			$order = 'IF(ISNULL(jt.value),1,0),jt.value';
			$join_params['join_type'] = "LEFT ";
			$join_params['table'] = "".TABLE_PREFIX."custom_property_values";
			$join_params['jt_field'] = "object_id";
			$join_params['e_field'] = "object_id";
			$join_params['on_extra'] = "AND custom_property_id = ".$cpId;
			$extra_conditions .= " AND ( custom_property_id = ".$cpId. " OR custom_property_id IS NULL)";
			$select_columns = array("DISTINCT o.*", "e.*");
		} else {
			$order = '`name`';
		} // if
		
		// filter attachments of other people if not filtering
		$tmp_mids = array();
		foreach (active_context() as $selection) {
			if ($selection instanceof Member) {
				$d = $selection->getDimension();
				if ($d instanceof Dimension && $d->getIsManageable()) $tmp_mids[] = $selection->getId();
			}
		}
		if (count($tmp_mids) == 0) {
			if (Plugins::instance()->isActivePlugin('mail')) {
				$extra_conditions .= " AND IF(e.mail_id=0, true, EXISTS (SELECT mac.contact_id FROM ".TABLE_PREFIX."mail_account_contacts mac 
					WHERE mac.contact_id=o.created_by_id AND mac.account_id=(SELECT mc.account_id FROM ".TABLE_PREFIX."mail_contents mc WHERE mc.object_id=e.mail_id)))";
			}
		}
		
		Hook::fire("listing_extra_conditions", null, $extra_conditions);
		
		$only_count_result = array_var($_GET, 'only_result',false);
		
		$context = active_context();
		$objects = ProjectFiles::instance()->listing(array(
			"order"=>$order,
			"order_dir" => $order_dir,
			"extra_conditions"=> $extra_conditions,
			"show_only_member_objects" => user_config_option('show_only_member_files'),
			'count_results' => false,
			'only_count_results' => $only_count_result,
			"join_params"=> $join_params,
			"start"=> $start,
			"limit"=> $limit,
			"select_columns"=> $select_columns
		));
		
		
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(ProjectFiles::instance()->getObjectTypeId());
		
		// prepare response object 
		$listing = array(
			"totalCount" => $objects->total,
			"start" => $start,
			"objType" => ProjectFiles::instance()->getObjectTypeId(),
			"files" => array(),
		);
		
		if (is_array($objects->objects)) {
			$index = 0;
			$ids = array();
			foreach ($objects->objects as $o) {
				$coName = "";
				$coId = $o->getCheckedOutById();
				if ($coId != 0) {
					if ($coId == logged_user()->getId()) {
						$coName = "self";
					} else {
						$coUser = Contacts::findById($coId);
						if ($coUser instanceof Contact) {
							$coName = $coUser->getObjectName();
						} else {
							$coName = "";
						}
					}
				}

				if ($o->isMP3()) {
					$songname = $o->getProperty("songname");
					$artist = $o->getProperty("songartist");
					$album = $o->getProperty("songalbum");
					$track = $o->getProperty("songtrack");
					$year = $o->getProperty("songyear");
					$duration = $o->getProperty("songduration");
					$songInfo = json_encode(array($songname, $artist, $album, $track, $year, $duration, $o->getDownloadUrl(), $o->getFilename(), $o->getId()));
				} else {
					$songInfo = array();
				}
				
				$ids[] = $o->getId();
				$values = array(
					"id" => $o->getId(),
					"ix" => $index++,
					"object_id" => $o->getId(),
					"ot_id" => $o->getObjectTypeId(),
					"name" => $o->getObjectName(),
					"type" => $o->getTypeString(),
					"mimeType" => $o->getTypeString(),
					"createdBy" => clean($o->getCreatedByDisplayName()),
					"createdById" => $o->getCreatedById(),
					"dateCreated" => $o->getCreatedOn() instanceof DateTimeValue ? ($o->getCreatedOn()->isToday() ? format_time($o->getCreatedOn()) : format_datetime($o->getCreatedOn())) : '',
					"dateCreated_today" => $o->getCreatedOn() instanceof DateTimeValue ? $o->getCreatedOn()->isToday() : 0,
					"updatedBy" => clean($o->getUpdatedByDisplayName()),
					"updatedById" => $o->getUpdatedById(),
					"dateUpdated" => $o->getUpdatedOn() instanceof DateTimeValue ? ($o->getUpdatedOn()->isToday() ? format_time($o->getUpdatedOn()) : format_datetime($o->getUpdatedOn())) : '',
					"dateUpdated_today" => $o->getUpdatedOn() instanceof DateTimeValue ? $o->getUpdatedOn()->isToday() : 0,
					"icon" => $o->getTypeIconUrl(),
					"size" => format_filesize($o->getFileSize()),
					"url" => $o->getOpenUrl(),
					"manager" => get_class($o->manager()),
					"checkedOutByName" => $coName,
					"checkedOutById" => $coId,
					"isModifiable" => $o->isModifiable() && $o->canEdit(logged_user()),
					"modifyUrl" => $o->getModifyUrl(),
					"songInfo" => $songInfo,
					"ftype" => $o->getType(),
					"url" => $o->getUrl(),
					"memPath" => json_encode($o->getMembersIdsToDisplayPath()),
					"genid" => gen_id(),
				);
				if ($o->isMP3()) {
					$values['isMP3'] = true;
				}
				Hook::fire('add_classification_value', $o, $values);
				
				foreach ($custom_properties as $cp) {
					$values['cp_'.$cp->getId()] = get_custom_property_value_for_listing($cp, $o);
				}
				
				$listing["files"][] = $values;
			}
			
			$read_objects = ReadObjects::getReadByObjectList($ids, logged_user()->getId());
			foreach($listing["files"] as &$data) {
				$data['isRead'] = isset($read_objects[$data['object_id']]);
			}
			
			ajx_extra_data($listing);
			tpl_assign("listing", $listing);
		}else{
			throw new Error("Not array", $code);			
		}
	}

	
	function open_file() {
		$fileId = $_GET['id'];
		$file = ProjectFiles::findById($fileId);
		if (!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		if ($file) {
			$this->redirectToUrl($file->getModifyUrl());
		} else {
			flash_error(lang('file dnx'));
			ajx_current("empty");
		}
	}

	/**
	 * Edit file properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_file() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_file');

		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$file_data = array_var($_POST, 'file');
		if(!is_array($file_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$file_data = array(
				'description' => $file->getDescription(),
				'attach_to_notification' => $file->getAttachToNotification(),
				'default_subject' => $file->getDefaultSubject(),
				'file_id' => get_id()
			); // array
		} // if
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);

		if(is_array(array_var($_POST, 'file'))) {
			foreach ($file_data as $k => &$v) {
				$v = remove_scripts($v);
			}
			try {
				
				DB::beginWork();
				$handle_file = array_var($file_data, 'update_file'); // change file?
				$post_revision = $handle_file && array_var($file_data, 'version_file_change'); // post revision?
				$revision_comment = trim(array_var($file_data, 'revision_comment')); // user comment?

				$file->setFromAttributes($file_data);

				$file->setAttachToNotification(array_var($file_data, 'attach_to_notification'));
				if(array_var($file_data, 'default_subject_sel') == 'subject'){
					$file->setDefaultSubject(array_var($file_data, 'default_subject_text'));
				}else{
					$file->setDefaultSubject('');
				}
				
				$fname = array_var($file_data, 'name');
				$ext = trim(array_var($file_data, 'extension'));
				if ($ext != '') {
					$fname .= "." . $ext;
				}
				$file->setFilename($fname);
				
				if ($file->getType() == ProjectFiles::TYPE_WEBLINK) {
					$url = array_var($file_data, 'url', '');
					if ($url && strpos($url, ':') === false) {
						$url = $this->protocol . $url;
					}
					$file->setUrl($url);
					$revision = $file->getLastRevision();
					/* @var $revision ProjectFileRevision */
					if (!$revision instanceof ProjectFileRevision || $post_revision) {
						$revision = new ProjectFileRevision();
						$revision->setFileId($file->getId());
						$revision->setRevisionNumber($file->getNextRevisionNumber());
						$revision->setFileTypeId(FileTypes::getByExtension('webfile')->getId());
						$revision->setRepositoryId('webfile');
						$revision->setComment($revision_comment);
					}
				
					$revision->setTypeString($file->getUrl());
					$revision->save();
				}
				
				$file->save();
				
				if( $handle_file) {
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					$file->handleUploadedFile($uploaded_file, $post_revision, $revision_comment); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
				} // if

				$member_ids = json_decode(array_var($_POST, 'members'));
				$object_controller = new ObjectController();
				$object_controller->add_to_members($file, $member_ids);
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);

				$file->resetIsRead();
				
				DB::commit();
				set_user_config_option('notify_myself_too', array_var($file_data, 'notify_myself_too'));
				if (array_var($file_data, 'notify_myself_too')) {
					logged_user()->notify_myself = true;
				}
				
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);
				
				if (array_var($file_data, 'notify_myself_too')) {
					logged_user()->notify_myself = false;
				}
				
				flash_success(lang('success edit file', $file->getFilename()));
				ajx_current("back");
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
			} catch(Exception $e) {				
				DB::rollback();
				if (array_var($_REQUEST, 'modal')) {
					$this->setLayout("json");
					$this->setTemplate(get_template_path("empty"));					
					print_modal_json_response(array('errorCode' => 1, 'errorMessage' => $e->getMessage(), 'showMessage' => 1), true, true);
				} else {
					flash_error($e->getMessage());
				}
				ajx_current("empty");
			} // try
		} // if
	} // edit_file

	/**
	 * Edit weblink properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_weblink() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_weblink');
		
		$file = ProjectFiles::findById(get_id());
		
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		
		$file_data = array_var($_POST, 'webpage');
		if(!is_array($file_data)) {
			// set layout for modal form
			if (array_var($_REQUEST, 'modal')) {
				$this->setLayout("json");
				tpl_assign('modal', true);
			}
			$file_data = array(
					'description' => $file->getDescription(),
					'name' => $file->getFilename(),
					'url' => $file->getUrl(),
					'attach_to_notification' => $file->getAttachToNotification(),
					'default_subject' => $file->getDefaultSubject(),
					'file_id' => get_id()
			); // array
		} // if
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);//*******************
	
		if(is_array(array_var($_POST, 'webpage'))) {
			try {
								
				$file->setFilename(array_var($file_data, 'name'));
				$file->setFromAttributes($file_data);
	
				$url = array_var($file_data, 'url', '');
				if ($url && strpos($url, ':') === false) {
					$url = $this->protocol ."://". $url;
					$file->setUrl($url);
					
				}
	
				DB::beginWork();
				$file->save();
	
				$member_ids = json_decode(array_var($_POST, 'members'));
	
				//link it!
				$member_ids = json_decode(array_var($_POST, 'members'));
				$object_controller = new ObjectController();
				$object_controller->add_to_members($file, $member_ids);
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);
				
				$file->resetIsRead();
	
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);	
	
				flash_success(lang('success edit webpage', $file->getObjectName()));
				ajx_current("back");
				
				if (array_var($_REQUEST, 'modal')) {
					evt_add("reload current panel");
				}
				// Error...
			} catch(Exception $e) {
				DB::rollback();
				if (array_var($_REQUEST, 'modal')) {
					ajx_extra_data(array('error' => $e->getMessage()));
				} else {
					flash_error($e->getMessage());
				}
				ajx_current("empty");
			}
	
		}
	}
	
	function release_file() {
		ajx_current("empty");
		$id = array_var($_GET, 'id');
		$file = ProjectFiles::findById(get_id());
		if ($file instanceof ProjectFile) {
			$file->cancelCheckOut();
		}
	}
	
	
	function auto_checkin() {
		ajx_current("empty");
		ProjectFiles::closeAutoCheckedoutFilesByUser();
	}

	
	function auto_checkout(){
		$this->checkout_file();
	}
	
	function checkin_file() {
		$this->setTemplate('add_file');

		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$file_data = array_var($_POST, 'file');
		if(!is_array($file_data)) {
			$file_data = array(
				'description' => $file->getDescription(),
			); // array
		} // if
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		tpl_assign('checkin', true);
			
		if(is_array(array_var($_POST, 'file'))) {
			try {
				DB::beginWork();
				$handle_file = true; // change file?
				$post_revision = $handle_file && array_var($file_data, 'version_file_change') == 'checked'; // post revision?
				$revision_comment = $post_revision ? trim(array_var($file_data, 'revision_comment')) : ''; // user comment?

				$file->setFromAttributes($file_data);
				$file->setFilename(array_var($file_data, 'name'));
				$file->checkIn();

				$file->setAttachToNotification(array_var($file_data, 'attach_to_notification'));
				if(array_var($file_data, 'default_subject_sel') == 'subject'){
					$file->setDefaultSubject(array_var($file_data, 'default_subject_text'));
				}else{
					$file->setDefaultSubject('');
				}
				
				$file->save();
				
				if ($handle_file) {
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					if (count($uploaded_file) > 0) {
						$file->handleUploadedFile($uploaded_file, $post_revision, $revision_comment); // handle uploaded file
						@unlink($uploaded_file['tmp_name']);
					}
				} // if

				$object_controller = new ObjectController();
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);

				//subscribe user if not subscribed
				if(!$file->isSubscriber(logged_user())) {				
					$file->subscribeUser(logged_user());
				} // if
				
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_EDIT);
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_CHECKIN);
				
				flash_success(lang('success add file', $file->getFilename()));
				ajx_current("back");
			} catch(Exception $e) {
				
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // checkin_file

	
	/**
	 * Delete file
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_file() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		try {
			DB::beginWork();
			$file->trash();
			DB::commit();
			ApplicationLogs::createLog($file, ApplicationLogs::ACTION_TRASH);
			
			flash_success(lang('success delete file', $file->getFilename()));
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
			ajx_add("overview-panel", "reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error delete file'));
			ajx_current("empty");
		} // try
	}

	
	function check_filename(){
		ajx_current("empty");
		$filename = array_var($_POST, 'filename');
		$member_ids = json_decode(array_var($_POST, 'members'));
		$files = ProjectFiles::getAllByFilename($filename, $member_ids) ;

		if (is_array($files) && count($files) > 0){
			$files_array = array();

			foreach ($files as $file){
				if ($file->getId() != array_var($_GET, 'id') && $file->canView(logged_user())){
					$files_array[] = array(
						"id" => $file->getId(),
						"name" => $file->getFilename(),
						"description" => $file->getDescription(),
						"type" => $file->getTypeString(),
						"size" => $file->getFilesize(),
						"created_by_id" => $file->getCreatedById(),
						"created_by_name" => $file->getCreatedByDisplayName(),
						"created_on" => $file->getCreatedOn() instanceof DateTimeValue ? $file->getCreatedOn()->getTimestamp() : 0,
						"is_checked_out" => $file->isCheckedOut(),
						"checked_out_by_name" => $file->getCheckedOutByDisplayName(),
						"can_check_in" => $file->canCheckin(logged_user()),
						"can_edit" => $file->canEdit(logged_user())
					);
				}
			}

			if (count($files_array) > 0){
				ajx_extra_data(array(
					"files" => $files_array
				));
			} else {
				ajx_extra_data(array(
					"id" => 0,
					"name" => $filename
				));
			}
		} else {
			ajx_extra_data(array(
				"id" => 0,
				"name" => $filename
			));
		}
	}

	
	function filenameExists($filename){
		$file = ProjectFiles::getByFilename($filename);
		return $file instanceof ProjectFile;
	}
	

	// ---------------------------------------------------
	//  Revisions
	// ---------------------------------------------------

	/**
	 * Update file revision (comment)
	 *
	 * @param void
	 * @return null
	 */
	function edit_file_revision() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$this->setTemplate('add_file_revision');
			
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$revision_data = array_var($_POST, 'revision');
		if(!is_array($revision_data)) {
			$revision_data = array(
				'comment' => $revision->getComment(),
			); // array
		} // if
			
		tpl_assign('revision', $revision);
		tpl_assign('file', $file);
		tpl_assign('revision_data', $revision_data);
			
		if(is_array(array_var($_POST, 'revision'))) {
			try {
				
				DB::beginWork();
				$revision->setComment(array_var($revision_data, 'comment'));
				$revision->save();

				DB::commit();
				ApplicationLogs::createLog($revision, ApplicationLogs::ACTION_EDIT);
				
				flash_success(lang('success edit file revision'));
				ajx_current("back");
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // edit_file_revision

	
	/**
	 * Delete selected revision (if you have proper permissions)
	 *
	 * @param void
	 * @return null
	 */
	function delete_file_revision() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$all_revisions = $file->getRevisions();
		if(count($all_revisions) == 1) {
			flash_error(lang('cant delete only revision'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		try {
			DB::beginWork();
			$revision->trash();
			DB::commit();
			ApplicationLogs::createLog($revision, ApplicationLogs::ACTION_TRASH);
			
			flash_success(lang('success trash file revision'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error trash object'));
			ajx_current("empty");
		} // try
	} // delete_file_revision

	
	/**
	 * Loads the logged user's mp3 files
	 *
	 */
	function get_mp3() {
		/* TODO re-implement 
		ajx_current("empty");

		//get arguments
		$context = active_context();
		$type = 'audio/mpeg';

		//query
		$files = ProjectFiles::getUserFiles(logged_user(), $context, $tag, $type,
		ProjectFiles::ORDER_BY_NAME, 'ASC');
		if (!is_array($files)) $files = array();

		//prepare response object 
		$mp3 = array(
			'mp3' => array()
		);
		foreach ($files as $f) {
			$songname = $f->getProperty("songname");
			$artist = $f->getProperty("songartist");
			$album = $f->getProperty("songalbum");
			$track = $f->getProperty("songtrack");
			$year = $f->getProperty("songyear");
			$duration = $f->getProperty("songduration");
			$mp3["mp3"][] = array($songname, $artist, $album, $track, $year, $duration, $f->getDownloadUrl(), $f->getFilename(), $f->getId()
			);
		}
		ajx_extra_data($mp3);*/
	}

	
	function copy() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_set_no_toolbar();
		$id = get_id();
		$file = ProjectFiles::findById($id);
		if (!$file instanceof ProjectFile) {
			flash_error("file dnx");
			ajx_current("empty");
			return;
		}
		if (!$file->canView(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		
		$original_members = $file->getMembers();
		$members = $file->getAllowedMembersToAdd(logged_user(), $original_members);
		
		if (!$file->canAdd(logged_user(), $members, $notAllowedMember) ){
			if (str_starts_with($notAllowedMember, '-- req dim --')) flash_error(lang('must choose at least one member of', str_replace_first('-- req dim --', '', $notAllowedMember, $in)));
			else trim($notAllowedMember) == "" ? flash_error(lang('you must select where to keep', lang('the file'))) : flash_error(lang('no context permissions to add',lang("files"), $notAllowedMember));
			ajx_current("empty");
			return;
		}
		
		try {
			
			DB::beginWork();
			$copy = $file->copy(false);
			$copy->setFilename(lang('copy of file', $file->getFilename()));
			$copy->save();
			$copy->addToMembers($members);
			$copy->addToSharingTable();

			$rev_data = array();
			$rev_data['name'] = $copy->getFilename();
			$rev_data['size'] = $file->getFileSize();
			$rev_data['type'] = $file->getTypeString();
			$rev_data['tmp_name'] = ROOT . '/tmp/' . rand () ;
			$handler = fopen($rev_data['tmp_name'], 'w');
			$file_content = $file->getLastRevision()->getFileContent();
			fputs($handler, $file_content);
			fclose($handler);
			$copy->handleUploadedFile($rev_data, false, lang("copied from file", $file->getFilename(), $file->getUniqueObjectId()));
			DB::commit();

			$this->setTemplate('file_details');
			tpl_assign('file', $copy);
			tpl_assign('last_revision', $copy->getLastRevision());
			tpl_assign('revisions', $copy->getRevisions());
                        tpl_assign('order', null);
                        tpl_assign('page', null);
                        ajx_extra_data(array("title" => $copy->getFilename(), 'icon'=>'ico-file'));
                        ajx_set_no_toolbar(true);
                        tpl_assign('object_id', $copy->getId());
                        tpl_assign('id', $copy->getId());
                        //read object for this user
                        $copy->setIsRead(logged_user()->getId(),true);
                        ApplicationReadLogs::createLog($copy, ApplicationReadLogs::ACTION_READ);

		} catch (Exception $ex) {
			DB::rollback();
			flash_error($ex->getMessage());
			ajx_current("empty");
		}
	}

	
	function zip_extract() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		$fileId = array_var($_GET, 'id');
		ajx_current("empty");
		if (!zip_supported()) {
			flash_error(lang('zip not supported'));
			return;
		}

		$file = ProjectFiles::findById($fileId);
		if (!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (!$file) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
		} else {
			$old_memory_limit = ini_get('memory_limit');
			if (php_config_value_to_bytes($old_memory_limit) < 96*1024*1024) {
				ini_set('memory_limit', '96M');
			}
			@set_time_limit(0);
			session_commit();
			$content = $file->getLastRevision()->getFileContent();
			$filepath = ROOT.'/tmp/'.rand().'.zip';
			$handle = fopen($filepath, 'wb');
			fwrite($handle, $content);
			fclose($handle);
			
			$encoder = EncodingConverter::instance();

			$file_count = 0;
			$zip = new ZipArchive();
			if ($zip->open($filepath)) {
				$tmp_dir = ROOT.'/tmp/'.rand().'/';
				$zip->extractTo($tmp_dir);
				$i=0;
				$members = $file->getMemberIds();				
				while ($e_name = $zip->getNameIndex($i++)) {					
					$tmp_path = $tmp_dir.$e_name;  
					
					//removes weird characters
					$e_name = preg_match_all('/([\x09\x0a\x0d\x20-\x7e]'. // ASCII characters
					'|[\xc2-\xdf][\x80-\xbf]'. // 2-byte (except overly longs)
					'|\xe0[\xa0-\xbf][\x80-\xbf]'. // 3 byte (except overly longs)
					'|[\xe1-\xec\xee\xef][\x80-\xbf]{2}'. // 3 byte (except overly longs)
					'|\xed[\x80-\x9f][\x80-\xbf])+/', // 3 byte (except UTF-16 surrogates)
					$e_name, $clean_pieces);					
					$e_name = join('?', $clean_pieces[0]);
					
					if (!is_dir($tmp_path)) {
						$path_parts = pathinfo($tmp_path);
						$file_name = $path_parts['basename'];
						$this->upload_file(null, $file_name, $tmp_path, $members);
						$file_count++;
					}
				}			
				$zip->close();
				delete_dir($tmp_dir);
			}
			unlink($filepath);
			ajx_current("reload");
			flash_success(lang('success extracting files', $file_count));
		}
	} // zip_extract

	
	private function upload_file($file, $filename, $path, $members) {
		try {
			if ($file == null) {
				$file = new ProjectFile();
				$file->setFilename($filename);
				$file->setIsVisible(true);
				$file->setCreatedOn(new DateTimeValue(time()));
			}

			$file_dt['name'] = $file->getFilename();
			$file_dt['size'] = filesize($path);
			$file_dt['tmp_name'] = $path;
			$extension = trim(get_file_extension($filename));
			$file_dt['type'] = Mime_Types::instance()->get_type($extension);

			if(!trim($file_dt['type'])) $file_dt['type'] = 'text/html';

			DB::beginWork();
			$file->save();
			$ctrl = new ObjectController() ;
			if (is_array($members)) {
				$ctrl->add_to_members($file, array_flat($members));
			}
			
			$revision = $file->handleUploadedFile($file_dt, true, '');

			DB::commit();
			ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
			return true;
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
		return false;
	} // upload_extracted_file

	function zip_add() {
		if (logged_user()->isGuest()) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		ajx_current("empty");
		if (!zip_supported()) {
			flash_error(lang('zip not supported'));
			return;
		}

		$files = ProjectFiles::findByCSVIds(array_var($_GET, 'objects'), '`type` = 0');
		if (count($files) == 0) {
			flash_error(lang('no files to compress'));
			return;
		}
                
		$isnew = false;
		$file = null;
		if (array_var($_GET, 'filename')) {
			$filename = array_var($_GET, 'filename');
			$isnew = true;
		} else if (array_var($_GET, 'id')) {
			$file = ProjectFiles::findById(array_var($_GET, 'id'));
			$filename = $file->getFilename();
		}
		
		$tmp_zip_path = ROOT.'/tmp/'.rand().'.zip';
		$handle = fopen($tmp_zip_path, 'wb');
		if (!$isnew) {
			$content = $file->getLastRevision()->getFileContent();
			fwrite($handle, $content, $file->getLastRevision()->getFilesize());
		}
		fclose($handle);
		
		$zip = new ZipArchive();
		if (!$isnew) $zip->open($tmp_zip_path);
		else $zip->open($tmp_zip_path, ZipArchive::OVERWRITE);
		
		$tmp_dir = ROOT.'/tmp/'.rand().'/';
		mkdir($tmp_dir);
		$members = array();
		foreach ($files as $file_to_add) {
			if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
				$file_to_add_path = FileRepository::getBackend()->getFilePath($file_to_add->getLastRevision()->getRepositoryId());
			} else {
				$file_to_add_path = $tmp_dir . $file_to_add->getFilename();
				$handle = fopen($file_to_add_path, 'wb');
				fwrite($handle, $file_to_add->getLastRevision()->getFileContent(), $file_to_add->getLastRevision()->getFilesize());
				fclose($handle);
			}
			$zip->addFile($file_to_add_path, utf8_safe($file_to_add->getFilename()));
			$members[] = $file_to_add->getMemberIds();
		}
		$zip->close();
		delete_dir($tmp_dir);
                
		$this->upload_file($file, $filename, $tmp_zip_path, $members);
		unlink($tmp_zip_path);
		
		flash_success(lang('success compressing files', count($files)));
		ajx_current("reload");
	}
//	function zip_add() {
//		if (logged_user()->isGuest()) {
//			flash_error(lang('no access permissions'));
//			ajx_current("empty");
//			return;
//		}
//		ajx_current("empty");
//		if (!zip_supported()) {
//			flash_error(lang('zip not supported'));
//			return;
//		}
//
//		$files = ProjectFiles::findByCSVIds(array_var($_GET, 'objects'), '`type` = 0');
//		if (count($files) == 0) {
//			flash_error(lang('no files to compress'));
//			return;
//		}
//		
//		$isnew = false;
//		$file = null;
//		if (array_var($_GET, 'filename')) {
//			$filename = array_var($_GET, 'filename');
//			$isnew = true;
//		} else if (array_var($_GET, 'id')) {
//			$file = ProjectFiles::findById(array_var($_GET, 'id'));
//			$filename = $file->getFilename();
//		}
//		
//		$tmp_zip_path = ROOT.'/tmp/'.rand().'.zip';
//		$handle = fopen($tmp_zip_path, 'wb');
//		if (!$isnew) {
//			$content = $file->getLastRevision()->getFileContent();
//			fwrite($handle, $content, $file->getLastRevision()->getFilesize());
//		}
//		fclose($handle);
//		
//		$zip = new ZipArchive();
//		if (!$isnew) $zip->open($tmp_zip_path);
//		else $zip->open($tmp_zip_path, ZipArchive::OVERWRITE);
//		
//		$tmp_dir = ROOT.'/tmp/'.rand().'/';
//		mkdir($tmp_dir);
//		foreach ($files as $file_to_add) {
//			if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
//				$file_to_add_path = FileRepository::getBackend()->getFilePath($file_to_add->getLastRevision()->getRepositoryId());
//			} else {
//				$file_to_add_path = $tmp_dir . $file_to_add->getFilename();
//				$handle = fopen($file_to_add_path, 'wb');
//				fwrite($handle, $file_to_add->getLastRevision()->getFileContent(), $file_to_add->getLastRevision()->getFilesize());
//				fclose($handle);
//			}
//			$zip->addFile($file_to_add_path, $file_to_add->getFilename());
//			
//		}
//		$zip->close();
//		delete_dir($tmp_dir);
//
//		unlink($tmp_zip_path);
//		
//		flash_success(lang('success compressing files', count($files)));
//		ajx_current("reload");
//	}
	
	
	function display_content() {
		
		$file = ProjectFiles::findById(get_id());
		if (!$file instanceof ProjectFile) {
			die(lang("file dnx"));
		}
		if (!$file->canView(logged_user())) {
			die(lang("no access permissions"));
		}
		
		if (defined('SANDBOX_URL')) {
			$html_content = $file->getFileContentWithRealUrls();
		} else {
			$html_content = purify_html($file->getFileContentWithRealUrls());
		}
		$charset = "";
		if ($file->getTypeString() == "text/html") {
			$encoding = detect_encoding($html_content, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'));
			$charset = ";charset=".$encoding;
		}
		
		if ($file->getTypeString() == 'text/html') {
			// Include stylesheet from FCK Editor
			$css = '<style type="text/css">';
			$css .= file_get_contents(ROOT.'/public/assets/javascript/ckeditor/contents.css');
			$css .= '</style>';
			$html_content = $css.$html_content;
		}
		
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Content-Type: " . $file->getTypeString() . $charset);
		header("Content-Length: " . (string) strlen($html_content));

		print($html_content);
		die();
	}
	
	
	function fckimagesbrowser(){
		/* get query parameters */
		/* TODO re-implement this function
		$this->setLayout('html');
		ajx_current("empty");
		$start = array_var($_GET,'start');
		$limit = array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit)+1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();
		$context = active_context();
		$type = '%image/';
		$paginatedImages = ProjectFiles::getProjectFiles($context, null, $hide_private, $order, $orderdir, $page, $limit, false, $tag, $type, logged_user()->getId());
		tpl_assign('start',$start);
		tpl_assign('limit',$limit);
		tpl_assign('paginatedImages',$paginatedImages);*/
		
	}
	
	
	function fckimagesupload(){
		try {
			if ( isset( $_FILES['NewFile'] ) && !is_null( $_FILES['NewFile']['tmp_name'] ) )
			{
				$oFile = $_FILES['NewFile'] ;
			}else{
				$sErrorNumber = '202';
				echo $this->SendUploadResults( $sErrorNumber ) ;	
				return;
			}
			$sErrorNumber = '0' ;
			$sFileName = $oFile['name'] ;
			
			$file = new ProjectFile();
			$file->setFilename($sFileName);
						
			$file->setIsVisible(true);
			$file->setCreatedOn(new DateTimeValue(time()));		

	
			DB::beginWork();
			$file->save();
			//FIXME $workspaces = array(personal_project());
			/*FIXME if (is_array($workspaces)) {
				foreach ($workspaces as $ws) {
					$file->addToWorkspace($ws);
				}
			}*/
			$revision = $file->handleUploadedFile($oFile, true, '');
			DB::commit();
			ApplicationLogs::createLog($file, ApplicationLogs::ACTION_ADD);
			echo $this->SendUploadResults( $sErrorNumber, $file->getDownloadUrl() , $file->getFilename() ) ;
		} catch (Exception $e) {
			DB::rollback();			
			$sErrorNumber = '202';
			echo $this->SendUploadResults( $sErrorNumber ) ;
		}
		
	}
	
	private function SendUploadResults( $errorNumber, $fileUrl = '', $fileName = '', $customMsg = '' )
	{
		// Minified version of the document.domain automatic fix script (#1919).
		// The original script can be found at _dev/domain_fix_template.js
		$ret = "
		<script type=\"text/javascript\">
		(function(){
			var d=document.domain;
			while (true){
				try{
					var A=window.parent.document.domain;break;
				}catch(e) {};
				d=d.replace(/.*?(?:\.|$)/,'');
				if (d.length==0) 
					break;try{document.domain=d;
				}catch (e){
					break;
				}
			}
		})();
		";
	
		$rpl = array( '\\' => '\\\\', '"' => '\\"' ) ;
		$ret .= 'window.parent.OnUploadCompleted(' . $errorNumber . ',"' . strtr( $fileUrl, $rpl ) . '","' . strtr( $fileName, $rpl ) . '", "' . strtr( $customMsg, $rpl ) . '") ;' ;
		$ret .= '</script>' ;
		return $ret;
	}
	
	
	function reload_file_view() {
		ajx_current("reload");
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			return;
		}
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		}
		
		if (array_var($_REQUEST, 'checkout')) {
			try{
				DB::beginWork();
				$file->checkOut();
				DB::commit();
				ApplicationLogs::createLog($file, ApplicationLogs::ACTION_CHECKOUT);
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
			}
		}
	}

} // FilesController

?>
