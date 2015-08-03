<?php

/**
 * ProjectFileRevision class
 * Generated on Tue, 04 Jul 2006 06:46:08 +0200 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectFileRevision extends BaseProjectFileRevision {

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('comment', 'filecontent');

	/**
	 * Parent file object
	 *
	 * @var ProjectFile
	 */
	private $file;

	/**
	 * Cached file type object
	 *
	 * @var FileType
	 */
	private $file_type;

	/**
	 * Construct file revision object
	 *
	 * @param void
	 * @return ProjectFileRevision
	 */
	function __construct() {
		$this->addProtectedAttribute('file_id', 'file_type_id', 'system_filename', 'thumb_filename', 'revision_number', 'type_string', 'filesize');
		parent::__construct();
	} // __construct

	
	/**
	 * Return parent file object
	 *
	 * @param void
	 * @return ProjectFile
	 */
	function getFile() {
		if(is_null($this->file)) {
			$this->file = ProjectFiles::findById($this->getFileId());
		} // if
		return $this->file;
	} // getFile

	
	/**
	 * Return file type object
	 *
	 * @param void
	 * @return FileType
	 */
	function getFileType() {
		if(is_null($this->file_type)) {
			$this->file_type = FileTypes::findById($this->getFileTypeId());
		} // if
		return $this->file_type;
	} // getFileType

	
	/**
	 * Return content of this file
	 *
	 * @param void
	 * @return string
	 */
	function getFileContent() {
		return FileRepository::getFileContent($this->getRepositoryId());
	} // getFileContent

	
	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	
	/**
	 * This function will return content of specific searchable column. It uses inherited
	 * behaviour for all columns except for `filecontent`. In case of this column function
	 * will return file content if file type is marked as searchable (text documents, office
	 * documents etc).
	 *
	 * @param string $column_name Column name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if($column_name == 'filecontent') {
			$file_type = $this->getFileType();
			// Unknown type or type not searchable
			if(!($file_type instanceof FileType)) {
				return null;
			} // if
			
			// Simple search for .txt and .html documents
			if ($file_type->getIsSearchable()){
				try {
					$content = strip_tags($this->getFileContent()); // Remove unnecesary html tags
					if(strlen($content) > MAX_SEARCHABLE_FILE_SIZE) {
						$content = substr($content, 0, MAX_SEARCHABLE_FILE_SIZE);
					}
					return $content; 
				} catch (Exception $e) {
					if (!defined('DONT_LOG') || DONT_LOG == false) {
						Logger::log($e->getMessage());
					}
					return "";
				}
			} else {
			
				// Search for .doc and .ppt documents
				if (($this->getFileType()->getExtension() == "doc" || $this->getFileType()->getExtension() == "ppt") 
					&& FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem){
					
					$backend = FileRepository::getBackend();
					if ($backend->isInRepository($this->getRepositoryId())){
						$filepath = $backend->getFilePath($this->getRepositoryId());
						$fileContents = $this->cat_file($filepath,$this->getFileType()->getExtension());
						
						if ($fileContents) {
							if (strlen($fileContents) > MAX_SEARCHABLE_FILE_SIZE) {
								$fileContents = substr($fileContents, 0, MAX_SEARCHABLE_FILE_SIZE);
							}
						    return $fileContents;
						}
					}
				}
				return null;
			}
		} 
		else
			return parent::getSearchableColumnContent($column_name);
	} // getSearchableColumnContent

	
	/**
	 * Create image thumbnail. This function will return true on success, false otherwise
	 *
	 * @param void
	 * @return boolean
	 */
	protected function createThumb() {
		do {
			$source_file = CACHE_DIR . '/' . sha1(uniqid(rand(), true));
		} while(is_file($source_file));

		if(!file_put_contents($source_file, $this->getFileContent()) || !is_readable($source_file)) {
			return false;
		} // if

		do {
			$temp_file = CACHE_DIR . '/' . sha1(uniqid(rand(), true));
		} while(is_file($temp_file));

		try {
			Env::useLibrary('simplegd');

			$image = new SimpleGdImage($source_file);
			$thumb = $image->scale(100, 100, SimpleGdImage::BOUNDARY_DECREASE_ONLY, false);
			$thumb->saveAs($temp_file, IMAGETYPE_PNG);

			$public_filename = PublicFiles::addFile($temp_file, 'png');
			if($public_filename) {
				$this->setThumbFilename($public_filename);
				$this->save();
			} // if

			$result = true;
		} catch(Exception $e) {
			$result = false;
		} // try

		@unlink($source_file);
		@unlink($temp_file);
		return $result;
	} // createThumb
	

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	
	/**
    * Return object URl
    *
    * @access public
    * @param void
    * @return string
    */
    function getObjectUrl() {
    	$file = $this->getFile();
		return $file  instanceof ProjectFile ? $file->getObjectUrl()  : null;
    } // getObjectUrl
	
    
	/**
	 * Return revision details URL
	 *
	 * @param void
	 * @return string
	 */
	function getDetailsUrl() {
		$file = $this->getFile();
		return $file  instanceof ProjectFile ? $file->getDetailsUrl() . '#revision' . $this->getId() : null;
	} // getDetailsUrl

	
	/**
	 * Show download URL
	 *
	 * @param void
	 * @return string
	 */
	function getDownloadUrl() {
		return get_url('files', 'download_revision', array('id' => $this->getId()));
	} // getDownloadUrl

	
	/**
	 * Return edit revision URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('files', 'edit_file_revision', array('id' => $this->getId()));
	} // getEditUrl

	
	/**
	 * Return delete revision URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('files', 'delete_file_revision', array('id' => $this->getId()));
	} // getDeleteUrl

	
	/**
	 * Return thumb URL
	 *
	 * @param void
	 * @return string
	 */
	function getThumbUrl() {
		if($this->getThumbFilename() == '') {
			$this->createThumb();
		} // if

		if(trim($this->getThumbFilename())) {
			return PublicFiles::getFileUrl($this->getThumbFilename());
		} else {
			return '';
		} // if
	} // getThumbUrl

	
	/**
	 * Return URL of file type icon. If we are working with image file type this function
	 * will return thumb URL if it success in creating it
	 *
	 * @param void
	 * @return string
	 */
	function getTypeIconUrl($showImage = true, $size = '48x48') {
		// return image depending on type string
		$image = "file.png";
		$mimeType = str_replace(array("/", "+"), "-", $this->getTypeString());
		$theme = config_option("theme", DEFAULT_THEME);
		$base = ROOT . "/" . PUBLIC_FOLDER . "/assets/themes/$theme/images/$size/types/";
		$extension = get_file_extension($this->getFile()->getFilename());
		if (is_file($base . $extension . ".png")) {
			$image = $extension . ".png";
		}
		$temp = $mimeType;
		$x = 0;
		while (true) {
			$x++;
			if (is_file($base . $temp . ".png")) {
				$image = $temp . ".png";
				break;
			} else {
				if ($x > 10) break;
				$i = strrpos($temp, "-");
				if ($i < 0) break;
				$temp = substr($temp, 0, $i);
			}
		}
		return get_image_url("$size/types/$image");
	} // getTypeIconUrl

	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	
	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_add($user, $context, ProjectFileRevisions::instance()->getObjectTypeId(), $notAllowedMember);
	}
	

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
		return can_delete($user,$this->getMembers(), $this->getObjectTypeId());
	} // canDelete

	
	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	
	private function cat_file($fname, $extension){
		if ((defined('CATDOC_PATH') && CATDOC_PATH != '' && $extension == "doc") || 
			(defined('CATPPT_PATH') && CATPPT_PATH != '' && $extension == "ppt")){
			exec(($extension == "doc"? CATDOC_PATH . ' -a ' : CATPPT_PATH) . ' ' . escapeshellarg($fname) . ' 2>&1', $result, $return_var);
			if ($return_var > 0){
				Logger::log(implode(" ",$result),Logger::WARNING);	// catdoc command not found
				return false;
			}
			return trim(implode(" ",$result));
		} else return false;
	}
	
	
	/**
	 * Validate before save. This one is used to keep the data in sync. Users
	 * can't create revisions directly...
	 *
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('file_id')) {
			$errors[] = lang('file revision file_id required');
		} // if
		if(!$this->validatePresenceOf('repository_id')) {
			$errors[] = lang('file revision filename required');
		} // if
		if (config_option('file_revision_comments_required') && $this->isNew()){
			if(!$this->validatePresenceOf('comment')) {
				$errors[] = lang('file revision comment required');
			} // if
		}
	} // validate

	
	/**
	 * Delete from DB and from the disk
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		if ($this->getTypeString() == 'sprd') {
			try {
				$bookId = $this->getFileContent();
				ob_start();
				include_once ROOT . "/" . PUBLIC_FOLDER . "/assets/javascript/gelSheet/php/config/settings.php";
				include_once ROOT . "/" . PUBLIC_FOLDER . "/assets/javascript/gelSheet/php/util/db_functions.php";
				include_once ROOT . "/" . PUBLIC_FOLDER . "/assets/javascript/gelSheet/php/controller/BookController.class.php";
				$bc = new BookController();
				$bc->deleteBook($bookId);
				ob_end_clean();
			} catch (Error $e) {
			}
		}
		try {
			FileRepository::deleteFile($this->getRepositoryId());
		} catch (Exception $ex) {
			Logger::log($ex->getMessage());
		}
		$this->deleteThumb(false);
		return parent::delete();
	} // delete

	
	/**
	 * Delete thumb
	 *
	 * @param boolean $save
	 * @return boolean
	 */
	function deleteThumb($save = true) {
		$thumb_filename = $this->getThumbFilename();
		if($thumb_filename) {
			$this->setThumbFilename('');
			PublicFiles::deleteFile($this->getThumbFilename());
		} // if

		if($save) {
			return $this->save();
		} // if

		return true;
	} // deleteThumb

	
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
		$file = $this->getFile();
		return $file instanceof ProjectFile ? $file->getObjectName() . ' revision #' . $this->getRevisionNumber() : 'Unknown file revision';
	} // getObjectName

	
  	function getUniqueObjectId(){
    	return $this->getFile()->getUniqueObjectId() . 'r' . $this->getRevisionNumber();
    }
    
    function getViewUrl() {
    	if (!$this->isLoaded()){
    		$fileRevision = ProjectFileRevisions::instance()->findById($this->getId(), 1);
    		$file = $fileRevision->getFile();
    	}else{
    		$file = $this->getFile();
    	}
    	
    	if ( $file ) {
    		return $file->getViewUrl();
    	}else{
    		return "#";
    	}
    }

    
} // ProjectFileRevision

?>