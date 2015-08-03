<?php

/**
 * OTemplates, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class COTemplates extends BaseCOTemplates {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'template';
	}
	
	/**
	 * Returns true if the object blongs to the texmplate context ($memberIds)
	 * 
	 * @param ContentDataObject $object
	 * @param array $memberIds
	 */
	static function validateObjectContext($object, $memberIds) {
		$valid = true ;
		// Dimensiones requeridas para el tipo de objecto 
		$dimensions  = Dimensions::getAllowedDimensions(self::instance()->getObjectTypeId() ) ;
		$requiredDimensions  = array();
		foreach ($dimensions as $dim) {
			if ($dim['is_required']) {
				$requiredDimensions[$dim['dimension_id']]= $dim ; // Performance, dim id in the array key  ! ! !
			}
		}
		
		// Miembros del Objeto
		$objMembers = $object->getMemberIds() ;
		
		// P/cada miembro
		foreach ( $objMembers as $mid ) {
			$member = Members::instance()->findById($mid) ;
			if ($member instanceof Member ) { /* @var  $member Member */
				$did = $member->getDimensionId() ;
				// Si la dimension del miembro esta en la requeridas
				if (array_var($requiredDimensions, $did)) {
					if (! in_array($mid, $memberIds)) {
						$valid = false; 
					}
				}		
			}
		}
		if ( !$valid ) { 
			throw new DAOValidationError($this, array(lang("template members do not match object members")) );
		}
	}
	
	static function findAllowed() {

		$ctxMembers  =  active_context_members() ;
		$permissionGroups = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId()) ;
		// Find members where user can add tasks 
		$sql = "
			SELECT distinct(member_id) 
			FROM ".TABLE_PREFIX."contact_member_permissions o 
			WHERE object_type_id = ".ProjectTasks::instance()->getObjectTypeId()." 
			AND permission_group_id IN ( $permissionGroups ) AND can_write= 1 
			
			UNION (
				SELECT DISTINCT id from ".TABLE_PREFIX."members m WHERE m.dimension_id IN
				(
					SELECT DISTINCT dimension_id FROM ".TABLE_PREFIX."contact_dimension_permissions WHERE permission_group_id IN ($permissionGroups) AND permission_type ='allow all'
				)	
			)";
			
		$res = DB::execute($sql);
		$members = array();
		while ( $row  = $res->fetchRow() ){
			$members[] = $row['member_id'];
			
		}
		
		if (!count($members)) return ;
		
		// Find templates that belongs to any $member 
		$sql = "
			SELECT distinct(id) AS id
			FROM ".TABLE_PREFIX."object_members om
			INNER JOIN ".TABLE_PREFIX."templates t ON t.object_id = om.object_id
			INNER JOIN ".TABLE_PREFIX."objects o ON om.object_id = o.id
			WHERE
			    member_id IN (".implode(',', $members).")
			AND is_optimization = 0
			GROUP BY om.object_id		
		";
		
		$res = DB::execute($sql);
		$tpls = array();	

		
		// Iterate on the results and make som filtering
		while ( $row  = $res->fetchRow() ){
			$tpl = COTemplates::instance()->findById($row['id']);
			$templateMembers  = $tpl->getMemberIds() ;
			
			
			if ( !count(array_intersect($templateMembers, $ctxMembers)) ){
				//array_intersect($templateMembers, $ctxMembers)
				continue;
			}
			
			// Chcheck if all template members are included in $mebers
			// TODO: PERFORMENCE This should be done in one sql instead of filtering here
			if ( !count(array_diff($templateMembers, $members))) {
				$tpls[] = $tpl ;
			}else{
				
			}
		}	
		return $tpls ;
	}
	
	

	
	/**
	 * @deprecated
	 * 
	 */
	static function _findAllowed() {
			
		//1.  Find members where user can add tasks 
		//$sqlMembers = "

		$sql = "
			SELECT distinct(id) AS id
			FROM ".TABLE_PREFIX."object_members om
			INNER JOIN ".TABLE_PREFIX."templates t ON t.object_id = om.object_id
			INNER JOIN ".TABLE_PREFIX."objects o ON om.object_id = o.id
			WHERE
			    member_id IN (  
			    	SELECT distinct(member_id) 
					FROM ".TABLE_PREFIX."contact_member_permissions o 
					WHERE object_type_id = ".ProjectTasks::instance()->getObjectTypeId()." 
					AND permission_group_id IN ( ".ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId()) ." ) AND can_write= 1 
				)
				AND is_optimization = 0
			GROUP BY om.object_id		
		";
		
		$res = DB::execute($sql);
		$tpls = array();
		
		// Iterate on the results and make som filtering
		while ( $row  = $res->fetchRow() ){
			$tpl = COTemplates::instance()->findById($row['id']);
			$tpls[] = $tpl ;
		}	
		return $tpls ;
	}
	
	
} 
