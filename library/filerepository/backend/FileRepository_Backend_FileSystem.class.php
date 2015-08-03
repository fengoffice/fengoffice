<?php

/**
 * File repository backend that stores file in destination folder on file system
 *
 * @package FileRepository.backend
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class FileRepository_Backend_FileSystem implements FileRepository_Backend {


	/** Names of database tables, prefix will be added in front of them **/
	const FILES_TABLE = 'file_repo';
	const ATTRIBUTES_TABLE = 'file_repo_attributes';

	/**
	 * Path to repository directory in the file system
	 *
	 * @var string
	 */
	private $repository_dir;

	private $table_prefix;
	 
	/**
	 * Construct the FileRepository_Backend_FileSystem
	 *
	 * @param string $repository_dir Path to the file system repository
	 * @return FileRepository_Backend_FileSystem
	 */
	function __construct($repository_dir, $table_prefix) {
		$this->setTablePrefix($table_prefix);
		$this->setRepositoryDir($repository_dir);
	} // __construct

	// ---------------------------------------------------
	//  Backend implementation
	// ---------------------------------------------------

	/**
	 * Return array of all files in repository
	 *
	 * @param void
	 * @return null
	 */
	function listFiles() {
		$files_table = $this->getFilesTableName();
		if ($result = DB::execute("SELECT `id` FROM $files_table ORDER BY `order`")) {
			$ids = array();
			while ($row= $result->fetchRow()) {
				$ids[] = $row['id'];
			} // while
			return $ids;
		} // if
		return array();
	} // listFiles

	/**
	 * Return number of files in repository
	 *
	 * @param void
	 * @return integer
	 */
	function countFiles() {
		$files_table = $this->getFilesTableName();
		if ($result = DB::execute("SELECT COUNT(`id`) AS 'row_count' FROM $files_table")) {
			if ($row = $result->fetchRow()) {
				return $row['row_count'];
			}
		} // if
		return 0;
	} // countFiles

	/**
	 * Read the content of the file and return it
	 *
	 * @param string $file_id
	 * @return string
	 */
	function getFileContent($file_id) {
		if(!$this->isInRepository($file_id)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		$file_path = $this->getFilePath($file_id);
		if(!is_file($file_path) || !is_readable($file_path)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		return file_get_contents($file_path);
	} // getFileContent

	/**
	 * Return all file attributes for specific file. If file has no attributes empty array is
	 * returned
	 *
	 * @param string $file_id
	 * @return array
	 * @throws FileNotInRepositoryError
	 */
	function getFileAttributes($file_id) {
		if(!$this->isInRepository($file_id)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		$attributes_table = $this->getAttributesTableName();
		$escaped_id = DB::escape($file_id);
		if ($result = DB::execute("SELECT `attribute`, `value` FROM $attributes_table WHERE `id` = $escaped_id")) {
			$attributes = array();
			while ($row = $result->fetchRow()) {
				$attributes[$row['attribute']] = eval($row['value']);
			} // while
			return $attributes;
		} // if
		return array();

		//return is_array($this->attributes[$file_id]) ? $this->attributes[$file_id] : array();
	} // getFileAttributes

	/**
	 * Return attributes table name
	 *
	 * @param boolean $escape Escape table name
	 * @return string
	 */
	protected function getAttributesTableName($escape = true) {
		$table_name = $this->getTablePrefix() . self::ATTRIBUTES_TABLE;
		return $escape ? '`' . $table_name . '`' : $table_name;
	} // getAttributesTableName

	/**
	 * Return value of specific file attribute
	 *
	 * @param string $file_id
	 * @param string $attribute_name
	 * @param mixed $default Default value is returned when attribute is not found
	 * @return mixed
	 * @throws FileNotInRepositoryError if file is not in repository
	 */
	function getFileAttribute($file_id, $attribute_name, $default = null) {
		if(!$this->isInRepository($file_id)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		$attributes_table = $this->getAttributesTableName();
		$escaped_id = DB::escape($file_id);
		$escaped_attribute = DB::escape($attribute_name);
		if ($result = DB::execute("SELECT `value` FROM $attributes_table WHERE `id` = $escaped_id AND `attribute` = $escaped_attribute")) {
			if ($row = $result->fetchRow()) {
				return eval($row['value']);
			} // if
		} // if
		return $default;
		 
	} // getFileAttribute

	/**
	 * Set attribute value for specific file
	 *
	 * @param string $file_id
	 * @param string $attribute_name
	 * @param mixed $attribute_value Objects and resources are not supported. Scalars and arrays are
	 * @return null
	 * @throws FileNotInRepositoryError If $file_id does not exists in repository
	 * @throws InvalidParamError If we have an object or a resource as attribute value
	 */
	function setFileAttribute($file_id, $attribute_name, $attribute_value) {
		
		if(!$this->isInRepository($file_id)) {
			//throw new FileNotInRepositoryError($file_id);
		} // if

		if(is_object($attribute_value) || is_resource($attribute_value)) {
			throw new InvalidParamError('$attribute_value', $attribute_value, 'Objects and resources are not supported as attribute values');
		} // if

		$attributes_table = $this->getAttributesTableName();
		$escaped_id = DB::escape($file_id);
		$escaped_attribute = DB::escape($attribute_name);
		$escaped_value = DB::escape('return ' . var_export($attribute_value, true) . ';');

		if ($result = DB::execute("SELECT `value` FROM $attributes_table WHERE `id` = $escaped_id AND `attribute` = $escaped_attribute")) {
			if (!$result->fetchRow()) {
				DB::execute("INSERT INTO $attributes_table (`id`, `attribute`, `value`) VALUES ($escaped_id, $escaped_attribute, $escaped_value)");
			} else {
				DB::execute("UPDATE $attributes_table SET `value` = $escaped_value WHERE `id` = $escaped_id AND `attribute` = $escaped_attribute");
			} // if
		} // if
	} // setFileAttribute

	/**
	 * Add file to the repository
	 *
	 * @param string $source Path of the source file
	 * @param array $attributes Array of file attributes
	 * @return string File ID
	 * @throws FileDnxError if source is not readable
	 * @throws FailedToCreateFolderError if we fail to create subdirectory
	 * @throws FileRepositoryAddError if we fail to move file to the repository
	 */
	function addFile($source, $attributes = null) {

		if(!is_readable($source)) {
			throw new FileDnxError($source);
		} // if

		$file_id = $this->getUniqueId();
		$file_path = $this->getFilePath($file_id);
		$destination_dir = dirname($file_path);

		if(!is_dir($destination_dir)) {
			//if(!force_mkdir_from_base($this->getRepositoryDir(), dirname($this->idToPath($file_id)), 0777)) {
			if(!force_mkdir($destination_dir, 0777)) {
				throw new FailedToCreateFolderError($destination_dir);
			} // if
			if (is_exec_available()) exec("chmod -R 777 $destination_dir");
		} // if

		if(!copy($source, $file_path)) {
			throw new FileRepositoryAddError($source, $file_id);
		} // if

		if(is_array($attributes)) {
			foreach($attributes as $attribute_name => $attribute_value) {
				$this->setFileAttribute($file_id, $attribute_name, $attribute_value);
			} // foreach
		} // if
		
		Hook::fire('after_adding_file_to_repository', $file_path, $ret);
		return $file_id;
	} // addFile

	/**
	 * Update content of specific file
	 *
	 * @param string $file_id
	 * @param string $source
	 * @return boolean
	 * @throws FileDnxError if source file is not readable
	 * @throws FileNotInRepositoryError if $file_id is not in the repository
	 * @throws FileRepositoryAddError if we fail to update file
	 */
	function updateFileContent($file_id, $source) {
		if(!is_readable($source)) {
			throw new FileDnxError($source);
		} // if

		if(!$this->isInRepository($file_id)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		$file_path = $this->getFilePath($file_id);

		if(!copy($source, $file_path)) {
			throw new FileRepositoryAddError($source, $file_id);
		} // if

		return true;
	} // updateFileContent

	/**
	 * Delete file from the repository
	 *
	 * @param string $file_id
	 * @return boolean
	 * @throws FileNotInRepositoryError if $file_id is not in the repository
	 * @throws FileRepositoryDeleteError if we fail to delete file
	 */
	function deleteFile($file_id) {
		if(!$this->isInRepository($file_id)) {
			throw new FileNotInRepositoryError($file_id);
		} // if

		$file_path = $this->getFilePath($file_id);

		if(!unlink($file_path)) {
			throw new FileRepositoryDeleteError($file_id);
		} // if

		$this->cleanUpDir($file_id);
		
		// delete attributes
		$attributes_table = $this->getAttributesTableName();
		$escaped_id = DB::escape($file_id);

		try {
			DB::execute("DELETE FROM $attributes_table WHERE `id` = $escaped_id");
		} catch (Exception $e) {}

		return true;
	} // deleteFile

	/**
	 * Drop all files from repository
	 *
	 * @param void
	 * @return null
	 */
	function cleanUp() {
		$dir = dir($this->getRepositoryDir());
		if($dir) {
			while(false !== ($entry = $dir->read())) {
				if(str_starts_with($entry, '.')) continue; // '.', '..' and hidden files ('.svn' for instance)
				$path = with_slash($this->getRepositoryDir()) . $entry;
				if(is_dir($path)) {
					delete_dir($path);
				} elseif(is_file($path)) {
					unlink($path);
				} // if
			} // while
		} // if
		$attributes_table = $this->getAttributesTableName();
		DB::execute("DELETE FROM $attributes_table");
	} // cleanUp

	/**
	 * Check if specific file is in repository
	 *
	 * @param string $file_id
	 * @return boolean
	 */
	function isInRepository($file_id) {
		/* directory structure for files has been changed
		 * since Feng Office 1.4, because storing files as
		 * a path of 5 char directories means that at some
		 * point we will reach the 32.000 subdirectories
		 * limit in ext2 and ext3 filesystems. To avoid this,
		 * subdirectories are now 3 chars long, so that we
		 * know we'll never have more than 4096 subdirectories.
		 * New files are automatically stored like this,
		 * and old files are moved to the new path in this
		 * function when they are accessed.
		 */
		$is_file = is_file($this->getFilePath($file_id));
		if ($is_file) {
			return true;
		} else {
			if (is_file($this->_getFilePathOld($file_id))) {
				$this->_moveToNewPath($file_id);
				return is_file($this->getFilePath($file_id));
			} else {
				return false;
			}
		}
		return false; 
	} // isInRepository

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Return file path by file_id. This function does not check if file really
	 * exists in repository, it just creates and returns the path
	 *
	 * @param string $file_id
	 * @return string
	 */
	function getFilePath($file_id) {
		return with_slash($this->getRepositoryDir()) . $this->idToPath($file_id);
	} // getFilePath

	/**
	 * This function will clean up the file dir after the file was deleted
	 *
	 * @param string $file_id
	 * @return null
	 */
	private function cleanUpDir($file_id) {
		$path = $this->idToPath($file_id);

		if(!$path) return;

		$path_parts = explode('/', $path);
		$repository_path = with_slash($this->getRepositoryDir());

		$for_cleaning = array(
		$repository_path . $path_parts[0] . '/' . $path_parts[1] . '/' . $path_parts[2],
		$repository_path . $path_parts[0] . '/' . $path_parts[1],
		$repository_path . $path_parts[0],
		); // array

		foreach($for_cleaning as $dir) {
			if(is_dir_empty($dir)) {
				delete_dir($dir);
			} else {
				return; // break, not empty
			} // if
		} // foreach
	} // cleanUpDir

	/**
	 * Convert file ID to repository file path
	 *
	 * @param string $file_id
	 * @return string
	 */
	private function idToPath($file_id) {
		if(strlen($file_id) == 40) {
			$parts = array();
			for($i = 0; $i < 3; $i++) {
				$parts[] = substr($file_id, $i * 3, 3);
			} // for
			$parts[] = substr($file_id, 9, 31);
			return implode('/', $parts);
		} else {
			return null;
		} // if
	} // idToPath

	/**
	 * Return unique file ID
	 *
	 * @param void
	 * @return string
	 */
	private function getUniqueId() {
		do {
			$id = sha1(uniqid(rand(), true));
			$file_path = $this->getFilePath($id);
		} while(is_file($file_path));
		return $id;
	} // getUniqueId

	// ---------------------------------------------------
	//  Attribute handling
	// ---------------------------------------------------



	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get repository_dir
	 *
	 * @param null
	 * @return string
	 */
	function getRepositoryDir() {
		return $this->repository_dir;
	} // getRepositoryDir

	/**
	 * Set repository_dir value
	 *
	 * @param string $value
	 * @return null
	 * @throws DirDnxError
	 * @throws DirNotWritableError
	 */
	function setRepositoryDir($value) {
		if(!is_null($value) && !is_dir($value)) {
			throw new DirDnxError($value);
		} // if

		/*if(!folder_is_writable($value)) {
			throw new DirNotWritableError($value);
		} // if*/

		$this->repository_dir = $value;
	} // setRepositoryDir
	
	/**
	 * Get table_prefix
	 *
	 * @param null
	 * @return string
	 */
	function getTablePrefix() {
		return $this->table_prefix;
	} // getTablePrefix

	/**
	 * Set table_prefix value
	 *
	 * @param string $value
	 * @return null
	 */
	function setTablePrefix($value) {
		$this->table_prefix = $value;
	} // setTablePrefix
	
	// --------------------------------------------------------------
	// Deprecated, do not use directly
	// --------------------------------------------------------------
	
	/**
	* @deprecated
	* @param $file_id
	* @return unknown_type
	*/
	private function _idToPathOld($file_id) {
		if(strlen($file_id) == 40) {
			$parts = array();
			for($i = 0; $i < 3; $i++) {
				$parts[] = substr($file_id, $i * 5, 5);
			} // for
			$parts[] = substr($file_id, 15, 25);
			return implode('/', $parts);
		} else {
			return null;
		} // if
	}

	/**
	 * @deprecated
	 * @param $file_id
	 * @return unknown_type
	 */
	private function _getFilePathOld($file_id) {
		return with_slash($this->getRepositoryDir()) . $this->_idToPathOld($file_id);
	}
	
	function _moveToNewPath($file_id) {
		$old_path = $this->_getFilePathOld($file_id);
		if (!is_file($old_path)) return;
		$new_path = $this->getFilePath($file_id);
		$dir = dirname($new_path);
		force_mkdir($dir, 0777);
		rename($old_path, $new_path);
		$this->_cleanUpDirOld($file_id);
		return $new_path;
	}
	
	private function _cleanUpDirOld($file_id) {
		$path = $this->_idToPathOld($file_id);
		if(!$path) return;
		$path_parts = explode('/', $path);
		$repository_path = with_slash($this->getRepositoryDir());
		$for_cleaning = array(
			$repository_path . $path_parts[0] . '/' . $path_parts[1] . '/' . $path_parts[2],
			$repository_path . $path_parts[0] . '/' . $path_parts[1],
			$repository_path . $path_parts[0],
		); // array
		foreach($for_cleaning as $dir) {
			if(is_dir_empty($dir)) {
				delete_dir($dir);
			} else {
				return; // break, not empty
			} // if
		} // foreach
	}
	
} // FileRepository_Backend_FileSystem

?>