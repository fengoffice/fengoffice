<?php
define('CONSOLE_MODE', true);
require_once('WebServicesBase.php');

include ROOT . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'permissions.php';

class FilesServices extends WebServicesBase {
	
	function FilesServices() {
		
		$this->__dispatch_map['listFiles'] = array(
            "in"  => array("username" => "string", "password" => "string", "tags" => "string", "workspaces" => "string", "name" => "string", "offset" => "int", "limit" => "int","type" => "string"),
            "out" => array("list" => "string")
		);
		
		$this->__dispatch_map['downloadFileBase64'] = array(
            "in"  => array("username" => "string", "password" => "string", "fileid" => "int", "do_checkout" => "boolean"),
            "out" => array("data" => "string")
		);
		
		$this->__dispatch_map['uploadFileBase64'] = array(
            "in"  => array("username" => "string", "password" => "string", "workspaces" => "string", "tags" => "string", "generate_rev" => "boolean", "filename" => "string", "description" => "string", "do_checkin" => "boolean", "data" => "string"),
            "out" => array("upload_ok" => "string")
		);
		
		$this->__dispatch_map['fileExists'] = array(
            "in"  => array("username" => "string", "password" => "string", "filename" => "string"),
            "out" => array("exists" => "string")
		);
		
		$this->__dispatch_map['checkoutFile'] = array(
            "in"  => array("username" => "string", "password" => "string", "fileid" => "int"),
            "out" => array("checkout_ok" => "string")
		);
		
		$this->__dispatch_map['checkinFile'] = array(
            "in"  => array("username" => "string", "password" => "string", "fileid" => "int"),
            "out" => array("checkin_ok" => "string")
		);
		
		$this->WebServicesBase();
	}
	
	function listFiles($username, $password, $tags, $workspaces, $name, $offset=0, $limit=1000, $type="") {
		$result = '';
		if ($this->loginUser($username, $password)) {
			$wspaces = Projects::findByCSVIds($workspaces);
			$ws = count($wspaces) > 0 ? $wspaces[0] : null; //TODO hay que buscar por todos los workspaces, por ahora solo funciona con el primero
			$alltags = explode(',', $tags);
			$tags = $alltags[0]; //TODO tambien falta hacer la b�squeda por varios tags, por ahora solo va a tomar el primero
			if (trim($tags) == '') $tags = null;
			
			if ($ws == null) {
				$files = ProjectFiles::getUserFiles(logged_user(), null, $tags, $type, ProjectFiles::ORDER_BY_NAME, 'ASC', $offset, $limit);
			} else {
				$listfiles = ProjectFiles::getProjectFiles($ws, null,
					false, ProjectFiles::ORDER_BY_NAME, 'ASC', ($offset/$limit), $limit, false, $tags,$type);
				if (is_array($listfiles) && count($listfiles))
					$files = $listfiles[0];
				else $files = array();
			}
			
			$name = trim($name);
			if (isset($files) && is_array($files)) {
				$this->initXml('files');
				foreach ($files as $f) {
					if ($name == '' || stristr($f->getFilename(), $name)) {
						$this->file_toxml($f);
					}
				}
				$result = $this->endXml();
			} else $result = '';
		}

		return $result;
	}
	
	function downloadFileBase64($username, $password, $fileid, $do_checkout) {
		$result = array();
		if ($this->loginUser($username, $password)) {
			$file = ProjectFiles::findById($fileid);
			$bytes = $file->getFileContent();
			$extension = trim(get_file_extension($file->getFilename()));
			$m_type = Mime_Types::get_type($extension);
			$result = base64_encode($bytes);
			
			if ($do_checkout) {
				$this->checkoutFile($username, $password, $fileid);
			}			
		}
		
		return $result;
	}
	
	function downloadFile($username, $password, $fileid) {
		$result = array();
		if ($this->loginUser($username, $password)) {
			$file = ProjectFiles::findById($fileid);
			$bytes = $file->getFileContent();
			$extension = trim(get_file_extension($file->getFilename()));
			$m_type = Mime_Types::get_type($extension);
			$result = new SOAP_Attachment('', $m_type, $file->getFilename(), $bytes);
		}
		
		return $result;
	}
	
	function uploadFileBase64($username, $password, $workspaces, $tags, $generate_rev, $filename, $description, $do_checkin, $data) {
		$decoded_data = base64_decode($data);
		return $this->uploadFile($username, $password, $workspaces, $tags, $generate_rev, $filename, $description, $do_checkin, $decoded_data);
	}
	
	function uploadFile($username, $password, $workspaces, $tags, $generate_rev, $filename, $description, $do_checkin, $data) {
		$result = array('status' => true, 'errorid' => 0, 'message' => '');
		if ($this->loginUser($username, $password)) {
			try {
				DB::beginWork();

				$file = null;
				$files = ProjectFiles::getAllByFilename($filename, logged_user()->getWorkspacesQuery());
				
				if (is_array($files) && count($files) > 0) {
					if ($generate_rev) {
						$file = ProjectFiles::findById($files[0]->getId());
						if ($file->isCheckedOut()) {
							if (!$file->canCheckin(logged_user())){
								$result['status'] = false;
								$result['errorid'] = 1004;
								$result['message'] = lang('no access permissions');
							}
							$file->setCheckedOutById(0);
						} else {  // Check for edit permissions
							if (!$file->canEdit(logged_user())){
								$result['status'] = false;
								$result['errorid'] = 1004;
								$result['message'] = lang('no access permissions');
							}
						}
					}
				}
				
				if ($result['status']) {
					$enteredWS = Projects::findByCSVIds($workspaces);
					$validWS = array();
					foreach ($enteredWS as $ws) {
						if (ProjectFile::canAdd(logged_user(), $ws)) {
							$validWS[] = $ws;
						}
					}
					
					if (count($validWS) == 0) {
						$result['status'] = false;
						$result['errorid'] = 1005;
						$result['message'] = 'Invalid workspaces given. Check access permissions.';					
					} else {
						$make_revision_comment = $file != null;
						if ($file == null) {
							$file = new ProjectFile();
							$file->setFilename($filename);
							$file->setIsVisible(true);
							$file->setIsPrivate(false);
							$file->setCommentsEnabled(true);
							$file->setAnonymousCommentsEnabled(false);
							$file->setCreatedOn(new DateTimeValue(time()) );
							$file->setDescription($description);
						}
						$file_dt['name'] = $file->getFilename();
						$file_dt['size'] = strlen($data);
						$file_dt['tmp_name'] = ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . rand();
						
						$extension = trim(get_file_extension($file->getFilename()));
						
						$file_dt['type'] = Mime_Types::instance()->get_type($extension);
						if(!trim($file_dt['type'])) {
							$file_dt['type'] = 'text/html';
						}
						
						$handle = fopen($file_dt['tmp_name'], "w");
						fwrite($handle, $data, $file_dt['size']);
						fclose($handle);
						
						$file->save();
						$revision = $file->handleUploadedFile($file_dt, true, ($make_revision_comment ? $description : ''));
						
						$file->setTagsFromCSV($tags);
						foreach ($validWS as $w) {
							$file->addToWorkspace($w);
						}
						
						foreach ($validWS as $w) {
							ApplicationLogs::createLog($file, $w, ApplicationLogs::ACTION_ADD);
						}
						
						$result['message'] = 'd' . str_pad($file->getId(), 3, '0', STR_PAD_LEFT) . 'r' . $file->getRevisionNumber();
						
						if (!$do_checkin) {
							$this->checkoutFile($username, $password, $file->getId());
						}
					}
				}
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				$result['message'] = $e->getMessage();
				$result['errorid'] = 1003;
				$result['status'] = false;

				// If we uploaded the file remove it from repository
				if(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				}
			}
		} else {
			$result['status'] = false;
			$result['errorid'] = 1002;
			$result['message'] = lang('invalid login data');
		}
		return $this->result_to_xml($result, 'result');
	}
	
	
	function checkoutFile($username, $password, $fileid)
	{
		$result = array('status' => true, 'errorid' => 0, 'message' => '');
		if ($this->loginUser($username, $password)) {
			$result = array('status' => true, 'message' => '');
			$file = ProjectFiles::findById($fileid);
			if(!($file instanceof ProjectFile)) {
				$result['message'] = lang('file dnx');
				$result['errorid'] = 1001;
				$result['status'] = false;
			} // if
	
			if($result['status'] && !$file->canEdit(logged_user())) {
				$result['message'] = lang('no access permissions');
				$result['errorid'] = 1004;
				$result['status'] = false;
			} // if
			
			if ($result['status']) {
				try{
					DB::beginWork();
					$file->checkOut();
					DB::commit();
				}
				catch(Exception $e)
				{
					DB::rollback();
					$result['message'] = $e->getMessage();
					$result['errorid'] = 1003;
					$result['status'] = '0';
				}
			}
		} else {
			$result['status'] = false;
			$result['errorid'] = 1002;
			$result['message'] = lang('invalid login data');
		}
		return $this->result_to_xml($result, 'result');
	}

	function checkinFile($username, $password, $fileid)
	{
		$result = array('status' => true, 'errorid' => 0, 'message' => '');
		if ($this->loginUser($username, $password)) {
			$file = ProjectFiles::findById($fileid);
			if(!($file instanceof ProjectFile)) {
				$result['message'] = lang('file dnx');
				$result['errorid'] = 1001;
				$result['status'] = false;
			} // if

			if($result['status'] && !$file->canEdit(logged_user())) {
				$result['message'] = lang('no access permissions');
				$result['errorid'] = 1004;
				$result['status'] = false;
			} // if

			if ($result['status']) {
				$tag_names = $file->getTagNames();
				$file_data = array(
				//'folder_id' => $file->getFolderId(),
					'description' => $file->getDescription(),
					'is_private' => $file->getIsPrivate(),
					'is_important' => $file->getIsImportant(),
					'comments_enabled' => $file->getCommentsEnabled(),
					'anonymous_comments_enabled' => $file->getAnonymousCommentsEnabled(),
					'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
					'workspaces' => $file->getWorkspacesNamesCSV(logged_user()->getWorkspacesQuery()),
				); // array
	
				try {
					$old_is_private = $file->isPrivate();
					$old_comments_enabled = $file->getCommentsEnabled();
					$old_anonymous_comments_enabled = $file->getAnonymousCommentsEnabled();
						
					DB::beginWork();
	
					$file->setCheckedOutById(0);
	
					if(!logged_user()->isMemberOfOwnerCompany()) {
						$file->setIsPrivate($old_is_private);
						$file->setCommentsEnabled($old_comments_enabled);
						$file->setAnonymousCommentsEnabled($old_anonymous_comments_enabled);
					} // if
					$file->save();
					$file->setTagsFromCSV(array_var($file_data, 'tags'));
					$file->save_properties($file_data);
					
					$ws = $file->getWorkspaces();
					foreach ($ws as $w) {
						ApplicationLogs::createLog($file, $w, ApplicationLogs::ACTION_EDIT);
					}
					DB::commit();
				} catch(Exception $e) {
					DB::rollback();
					$result['message'] = $e->getMessage();
					$result['errorid'] = 1003;
					$result['status'] = false;
				} // try
			}
		} else {
			$result['status'] = false;
			$result['errorid'] = 1002;
			$result['message'] = lang('invalid login data');
		}
		return $this->result_to_xml($result, 'result');
	}
	
	function fileExists($username, $password, $filename) {
		$result = array('status' => true, 'errorid' => 0, 'message' => '');
		if ($this->loginUser($username, $password)) {
			$file = ProjectFiles::getByFilename($filename);
			
			$result['status'] = $file != null;
			if ($file != null) {
				$this->initXml('result');
				$this->instance->startElement('status');
				$this->instance->text('true');
				$this->instance->endElement();
				$this->instance->startElement('errorid');
				$this->instance->text(0);
				$this->instance->endElement();
				$this->instance->startElement('message');
				$this->file_toxml($file);
				$this->instance->endElement();
				$xml = $this->endXml();
			} else {
				$result['errorid'] = 1001;
				$result['message'] = lang('file dnx');
				$xml = $this->result_to_xml($result, 'result');
			}
		} else {
			$result['status'] = false;
			$result['errorid'] = 1002;
			$result['message'] = lang('invalid login data');
			$xml = $this->result_to_xml($result, 'result');
		}
		return $xml;
	}
	
	private function file_toxml(ProjectFile $f) {
		$this->instance->startElement('file');
		
		$this->instance->startElement('id');
		$this->instance->text($f->getId());
		$this->instance->endElement();
		
		$this->instance->startElement('name');
		$this->instance->text(clean($f->getFilename()));
		$this->instance->endElement();
		
		$this->instance->startElement('description');
		$this->instance->text(clean($f->getDescription()));
		$this->instance->endElement();
		
		$this->instance->startElement('version');
		$this->instance->text($f->getRevisionNumber());
		$this->instance->endElement();
		
		$this->instance->startElement('modifiedOn');
		$this->instance->text($f->getLastRevision()->getCreatedOn()->format('d/m/Y'));
		$this->instance->endElement();
		
		$this->instance->startElement('modifiedBy');
		$creator = $f->getLastRevision()->getCreatedBy();
		$this->instance->text(clean($creator?$creator->getDisplayName():'unknown'));
		$this->instance->endElement();
		
		$this->instance->startElement('workspaces');
		$this->instance->text($f->getWorkspacesIdsCSV());
		$this->instance->endElement();
		
		$this->instance->startElement('tags');
		$this->instance->text(implode(', ', $f->getTagNames()));
		$this->instance->endElement();

		$this->instance->startElement('uid');
		$this->instance->text('d' . str_pad($f->getId(), 3, '0', STR_PAD_LEFT) . 'r' . $f->getRevisionNumber());
		$this->instance->endElement();
		
		$this->instance->endElement();
	}
			
}

$server = new SOAP_Server();
$webservice = new FilesServices();

$server->addObjectMap($webservice, 'http://schemas.xmlsoap.org/soap/envelope/');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
     $server->service($HTTP_RAW_POST_DATA);
} else {
     $disco = new SOAP_DISCO_Server($server, 'FengWebServices_files');
     header("Content-type: text/xml");

     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'wsdl') == 0) {
         echo $disco->getWSDL(); // show only the WSDL/XML output if ?wsdl is set in the address bar
     } else {
         echo $disco->getDISCO();
     }
}
?>