<?php
define('MAX_SEARCHABLE_FILE_SIZE', 1048576); // if file type is searchable script will load its content into search index. Using this constant you can set the max filesize of the file that will be imported. Noone wants 500MB in search index for single file
define('SESSION_LIFETIME', 14400);
define('REMEMBER_LOGIN_LIFETIME', 1209600); // two weeks

/**
 * Opengoo Helper
 * Implements basic operations for the opengoo-gelsheet integraion
 *
 */
class OgHelper {

	
	
	static function includeAll() {
		include_once OG_ROOT.'/cache/autoloader.php';

		if (is_array($GLOBALS['autoloader_classes'])) {
			foreach ($GLOBALS['autoloader_classes'] as $class) {
				include_once $class;
			}
		}
	}

	static function includeBasic() {
		static $first = true;
		include_once OG_ROOT.'/config/config.php';
		include_once OG_ROOT.'/environment/constants.php';
		include_once OG_ROOT.'/environment/library/database/DBResult.class.php';
		include_once OG_ROOT.'/environment/library/database/DB.class.php';
		include_once OG_ROOT.'/environment/library/database/adapters/AbstractDBAdapter.class.php';
		include_once OG_ROOT.'/environment/classes/Env.class.php';
		include_once OG_ROOT.'/environment/classes/hook/Hook.class.php';
		include_once OG_ROOT.'/environment/classes/Error.class.php';
		include_once OG_ROOT.'/environment/classes/errors/DAOValidationError.class.php';
		include_once OG_ROOT.'/environment/classes/Inflector.class.php';
		include_once OG_ROOT.'/environment/classes/datetimevalue/DateTimeValue.class.php';
		include_once OG_ROOT.'/environment/classes/datetimevalue/DateTimeValueLib.class.php';
		include_once OG_ROOT.'/environment/classes/Cookie.class.php';
		include_once OG_ROOT.'/environment/functions/general.php';
		include_once OG_ROOT.'/environment/classes/dataaccess/DataManager.class.php';
		include_once OG_ROOT.'/environment/classes/dataaccess/DataObject.class.php';
		include_once OG_ROOT.'/application/models/ApplicationDataObject.class.php';
		include_once OG_ROOT.'/application/models/ProjectDataObjects.class.php';
		include_once OG_ROOT.'/application/models/ProjectDataObject.class.php';
		
		include_once OG_ROOT.'/application/models/companies/base/BaseCompanies.class.php';
		include_once OG_ROOT.'/application/models/companies/base/BaseCompany.class.php';
		include_once OG_ROOT.'/application/models/companies/Company.class.php';
		include_once OG_ROOT.'/application/models/companies/Companies.class.php';
		include_once OG_ROOT.'/application/models/CompanyWebsite.class.php';
		
		include_once OG_ROOT.'/application/models/custom_properties/base/BaseCustomProperty.class.php';
		include_once OG_ROOT.'/application/models/custom_properties/base/BaseCustomProperties.class.php';
		include_once OG_ROOT.'/application/models/custom_properties/CustomProperty.class.php';
		include_once OG_ROOT.'/application/models/custom_properties/CustomProperties.class.php';
		
		include_once OG_ROOT.'/application/models/CompanyWebsite.class.php';
		
		include_once OG_ROOT.'/application/models/users/base/BaseUser.class.php';
		include_once OG_ROOT.'/application/models/users/User.class.php';
		include_once OG_ROOT.'/application/models/users/base/BaseUsers.class.php';
		include_once OG_ROOT.'/application/models/users/Users.class.php';
		include_once OG_ROOT.'/application/models/groups/base/BaseGroup.class.php';
		include_once OG_ROOT.'/application/models/groups/Group.class.php';
		include_once OG_ROOT.'/application/models/groups/base/BaseGroups.class.php';
		include_once OG_ROOT.'/application/models/groups/Groups.class.php';
		include_once OG_ROOT.'/application/models/project_files/base/BaseProjectFile.class.php';
		include_once OG_ROOT.'/application/models/project_files/ProjectFile.class.php';
		include_once OG_ROOT.'/application/models/project_files/base/BaseProjectFiles.class.php';
		include_once OG_ROOT.'/application/models/project_files/ProjectFiles.class.php';
		include_once OG_ROOT.'/application/models/object_user_permissions/base/BaseObjectUserPermission.class.php';
		include_once OG_ROOT.'/application/models/object_user_permissions/base/BaseObjectUserPermissions.class.php';
		include_once OG_ROOT.'/application/models/object_user_permissions/ObjectUserPermission.class.php';
		include_once OG_ROOT.'/application/models/object_user_permissions/ObjectUserPermissions.class.php';
		include_once OG_ROOT.'/application/models/group_users/base/BaseGroupUser.class.php';
		include_once OG_ROOT.'/application/models/group_users/base/BaseGroupUsers.class.php';
		include_once OG_ROOT.'/application/models/group_users/GroupUser.class.php';
		include_once OG_ROOT.'/application/models/group_users/GroupUsers.class.php';
		include_once OG_ROOT.'/application/models/workspace_object/base/BaseWorkspaceObject.class.php';
		include_once OG_ROOT.'/application/models/workspace_object/base/BaseWorkspaceObjects.class.php';
		include_once OG_ROOT.'/application/models/workspace_object/WorkspaceObject.class.php';
		include_once OG_ROOT.'/application/models/workspace_object/WorkspaceObjects.class.php';
		include_once OG_ROOT.'/application/models/projects/base/BaseProject.class.php';
		include_once OG_ROOT.'/application/models/projects/base/BaseProjects.class.php';
		include_once OG_ROOT.'/application/models/projects/Project.class.php';
		include_once OG_ROOT.'/application/models/projects/Projects.class.php';
		include_once OG_ROOT.'/application/models/project_users/base/BaseProjectUser.class.php';
		include_once OG_ROOT.'/application/models/project_users/base/BaseProjectUsers.class.php';
		include_once OG_ROOT.'/application/models/project_users/ProjectUser.class.php';
		include_once OG_ROOT.'/application/models/project_users/ProjectUsers.class.php';
		include_once OG_ROOT.'/application/models/project_file_revisions/base/BaseProjectFileRevisions.class.php';
		include_once OG_ROOT.'/application/models/project_file_revisions/ProjectFileRevisions.class.php';
		include_once OG_ROOT.'/application/models/errors/AdministratorDnxError.class.php';
		include_once OG_ROOT.'/application/helpers/permissions.php';
		include_once OG_ROOT.'/environment/classes/StringTwister.class.php';
		include_once OG_ROOT.'/environment/classes/localization/localization.php';
		include_once OG_ROOT.'/environment/classes/localization/Localization.class.php';
		include_once OG_ROOT.'/environment/classes/container/IContainer.class.php';
		include_once OG_ROOT.'/environment/classes/container/Container.class.php';
		include_once OG_ROOT.'/environment/classes/container/ObjectContainer.class.php';
		if ($first) {
			DB::connect(DB_ADAPTER, array(
				'host'    => DB_HOST,
				'user'    => DB_USER,
				'pass'    => DB_PASS,
				'name'    => DB_NAME,
				'persist' => DB_PERSIST
			));
			$first = false;
		}
	}

	static function getCompanyWebsite() {
		OgHelper::includeBasic();
		static $first = true;
		$cw = CompanyWebsite::instance();
		if ($first) {
			$cw->init();
			$first = false;
		}
		return $cw;
	}

	static function canWrite($bookId = null) {
		self::includeBasic();
		$file_id = self::ogBookId() ;
		if ($file_id == null) return false ;
		if ( $bookId != self::getGelsheetBookId($file_id)) return false ;
		
		
		$file = ProjectFiles::findById($file_id);
		if (!$file instanceof ProjectFile) return false;
		return can_write(self::getCompanyWebsite()->getLoggedUser(), $file);
	}

	static function canRead($bookId = null) {
		self::includeBasic();
		$file_id = self::ogBookId() ;
		if ($file_id == null) return false ;
		if ( $bookId != self::getGelsheetBookId($file_id)) return false ;
				
		$file = ProjectFiles::findById($file_id);
		if (!$file instanceof ProjectFile) return false;
		return can_read(self::getCompanyWebsite()->getLoggedUser(), $file);
	}
	
	static function canAdd() {
		self::includeBasic();
		$workspace_id = self::ogWorkspaceId() ;
		
		if ($workspace_id == null) return false ;
		$workspace = Projects::findById($workspace_id);
		if (!$workspace instanceof Project) return false;
		return can_add(self::getCompanyWebsite()->getLoggedUser(), $workspace, 'ProjectFiles');
	}
	
	static function ogBookId() {
		return (isset($_REQUEST['ogId']))?$_REQUEST['ogId']:null ; 
	}
	
	static function ogWorkspaceId() {
		return (isset($_REQUEST['ogWid']))?$_REQUEST['ogWid']:null ; 
	}
	
	static function getGelsheetBookId ($ogFileId) {
		$sql = "
			SELECT so.content as bookId FROM ".TABLE_PREFIX."searchable_objects so  INNER JOIN ".TABLE_PREFIX."project_file_revisions fr 
			ON fr.id = so.rel_object_id  
			WHERE fr.file_id  = $ogFileId AND so.column_name = 'filecontent'
			ORDER by revision_number DESC LIMIT 1
		"; 
		$res = @mysql_query($sql);
		if ($res) { 
			$row  = @mysql_fetch_object($res) ;
			if (is_numeric($row->bookId)) return $row->bookId ;
		}
		return null ; 	
	}
	
}
