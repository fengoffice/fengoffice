<?php

/**
 * ProjectFiles, generated on Tue, 04 Jul 2006 06:46:08 +0200 by 
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectFiles extends BaseProjectFiles {
	
	const ORDER_BY_NAME = 'name';
	const ORDER_BY_POSTTIME = 'dateCreated';
	const ORDER_BY_MODIFYTIME = 'dateUpdated';
	const ORDER_BY_SIZE = 'size';
	const TYPE_DOCUMENT = 0;
	const TYPE_WEBLINK = 1;
	
	public function __construct() {
		parent::__construct ();
		$this->object_type_name = 'file';
	}
	

	/**
	 * Array of types that will script treat as images (provide thumbnail, add 
	 * it to insert image editor function etc)
	 *
	 * @var array
	 */
	public static $image_types = array ('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/png' ); // array
	

	/**
	 * Return all project files that were automatically checked out (on edit) by the user
	 *
	 * @param User $user 
	 * @return array
	 */
	static function closeAutoCheckedoutFilesByUser($user = null) {
		if (! $user)
			$user = logged_user ();
		try {
			$condstr = 'checked_out_by_id = ' . $user->getId () . ' AND was_auto_checked_out = 1 AND checked_out_on <> \'' . EMPTY_DATETIME . '\'';
			$files = self::findAll ( array ('conditions' => $condstr ) ); // findAll
			if ($files) {
				foreach ( $files as $file ) {
					$file->setWasAutoCheckedAuto ( $autoCheckOut );
					$file->setCheckedOutById ( 0 );
					$file->setCheckedOutOn ( EMPTY_DATETIME );
					$file->setMarkTimestamps ( false );
					$file->save ();
				}
				return true;
			}
			return false;
		} catch ( Exception $exc ) {
			flash_error ( lang ( 'error checkin file' ) );
			return false;
		}
	
	} // getAllFilesByProject
	

	/**
	 * Return file by name.
	 *
	 * @param $filename
	 * @return array
	 */
	static function getByFilename($filename, $order = '`id` DESC') {
		$conditions = array ('`name` = ?', $filename );
		
		return self::findOne ( array ('conditions' => $conditions, 'order' => $order ) );
	} // getByFilename
	
	
	/**
	 * Check that ther are not members containig files with such filename
	 * 
	 * @param unknown_type $filename
	 * @param unknown_type $member_ids
	 */
	static function getAllByFilename($filename, $member_ids = null) {
                $member_sql = "";
		if ( is_array($member_ids)  && count($member_ids) ) {
			$member_sql = " AND object_id IN ( 
				SELECT distinct(object_id) 
				FROM ".TABLE_PREFIX."object_members 
				WHERE member_id IN (".implode(",", $member_ids).") AND is_optimization = 0 
			)";
		}
		$conditions = array ('`name` = ?' . $member_sql, $filename );
		
		return self::findAll ( array ('conditions' => $conditions ) );
	} 
	

	/**
	 * Return files index page
	 *
	 * @param string $order_by
	 * @param integer $page
	 * @return string
	 */
	static function getIndexUrl($order_by = null, $page = null) {
		if (($order_by != ProjectFiles::ORDER_BY_NAME) && ($order_by != ProjectFiles::ORDER_BY_POSTTIME)) {
			$order_by = ProjectFiles::ORDER_BY_POSTTIME;
		} 
		

		// #PAGE# is reserved as a placeholder
		if ($page != '#PAGE#') {
			$page = ( integer ) $page > 0 ? ( integer ) $page : 1;
		} 
		

		return get_url ( 'files', 'index', array ('active_project' => active_project ()->getId (), 'order' => $order_by, 'page' => $page ) ); // array
	}
	

	/**
	 * Handle files uploaded using helper forms. This function will return array of uploaded 
	 * files when finished
	 *
	 * @param string $files_var_prefix If value of this variable is set only elements in $_FILES
	 * with key starting with $files_var_prefix will be handled
	 * @return array
	 */
	static function handleHelperUploads($context, $files_var_prefix = null) {
		//FIXME
		return null;
		
		if (! isset ( $_FILES ) || ! is_array ( $_FILES ) || ! count ( $_FILES )) {
			return null; // no files to handle
		} 
		

		$uploaded_files = array ();
		foreach ( $_FILES as $uploaded_file_name => $uploaded_file ) {
			if ((trim ( $files_var_prefix ) != '') && ! str_starts_with ( $uploaded_file_name, $files_var_prefix )) {
				continue;
			} 
			

			if (! isset ( $uploaded_file ['name'] ) || ! isset ( $uploaded_file ['tmp_name'] ) || ! is_file ( $uploaded_file ['tmp_name'] )) {
				continue;
			} 
			

			$uploaded_files [$uploaded_file_name] = $uploaded_file;
		} 
		

		if (! count ( $uploaded_file )) {
			return null; 
		}
		

		$result = array (); // we'll put all files here
		$expiration_time = DateTimeValueLib::now ()->advance ( 1800, false );
		
		foreach ( $uploaded_files as $uploaded_file ) {
			$file = new ProjectFile ();
			
			$file->setProjectId ( $project->getId () );
			$file->setFilename ( $uploaded_file ['name'] );
			$file->setIsVisible ( false );
			$file->setExpirationTime ( $expiration_time );
			$file->save ();
			
			$file->handleUploadedFile ( $uploaded_file ); // initial version
			

			$result [] = $file;
		}
		

		return count ( $result ) ? $result : null;
	} 
	

	function findByCSVIds($ids, $additional_conditions = NULL) {
		if (isset ( $additional_conditions )) {
			$additional_conditions = " AND $additional_conditions";
		} else {
			$additional_conditions = "";
		}
		return self::findAll ( array ('conditions' => "`id` IN ($ids) $additional_conditions" ) );
	}
	
} 
