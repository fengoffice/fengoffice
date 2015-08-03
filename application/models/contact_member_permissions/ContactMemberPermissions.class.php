<?php

/**
 * ContactMemberPermissions
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ContactMemberPermissions extends BaseContactMemberPermissions {
	
	private static $readable_members = array();
	private static $writable_members = array();
	
	/**
	 * 
	 * Checks if user can access the member for a specified access level
	 * @param $permission_group_ids - string array: User permission group ids
	 * @param $member_id - integer: Member Id
	 * @param $user - Contact
	 * @param $access_level - enum: ACCESS_LEVEL_READ, ACCESS_LEVEL_WRITE, ACCESS_LEVEL_DELETE
	 * @param $check_administrator bool - if user is super administrator do not check permission
	 */
	function contactCanAccessMemberAll($permission_group_ids, $member_id, $user, $access_level, $check_administrator = true) {
		if ($user instanceof Contact && $user->isAdministrator () && $check_administrator) {
			return true;
		}
		
		$member = Members::findById($member_id);
		if ($member instanceof Member && !$member->getDimension()->getDefinesPermissions()) {
			return true;
		}
		
		$disabled_ots = array();
		$disableds = DB::executeAll("SELECT object_type_id FROM ".TABLE_PREFIX."tab_panels WHERE object_type_id>0 AND enabled=0");
		if (is_array($disableds)) {
			$disabled_ots = array_flat($disableds);
		}
		
		$ws_ot = ObjectTypes::findByName('workspace')->getId();
		$comment_ot = ObjectTypes::findByName('comment')->getId();
		$disabled_ots[] = $ws_ot;
		$disabled_ots[] = $comment_ot;
		$disabled_ot_cond = "";
		if (count($disabled_ots) > 0) {
			$disabled_ot_cond = "AND object_type_id NOT IN (".implode(",",$disabled_ots).")";
		}
		
		if ($access_level == ACCESS_LEVEL_READ) {
			if (!isset(self::$readable_members["$permission_group_ids"])) {
				$res = DB::execute("SELECT DISTINCT member_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id IN (" . $permission_group_ids . ") $disabled_ot_cond" );
				$rows = $res->fetchAll();
				if (is_array($rows)) {
					self::$readable_members["$permission_group_ids"] = array();
					foreach ($rows as $row) {
						self::$readable_members["$permission_group_ids"][] = $row['member_id'];
					}
				}
			}
			return in_array($member_id, self::$readable_members["$permission_group_ids"]);						
		} else {
			
			if (!isset(self::$writable_members["$permission_group_ids"])) {
				$res = DB::execute("SELECT DISTINCT member_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE can_write=1 AND permission_group_id IN (" . $permission_group_ids . ") $disabled_ot_cond" );
				$rows = $res->fetchAll();
				if (is_array($rows)) {
					self::$writable_members["$permission_group_ids"] = array();
					foreach ($rows as $row) {
						self::$writable_members["$permission_group_ids"][] = $row['member_id'];
					}
				}
			}
			return in_array($member_id, self::$writable_members["$permission_group_ids"]);
			
		}
	}
	
	function contactCanReadObjectTypeinMember($permission_group_ids, $member_id, $object_type_id, $can_write = false, $can_delete = false, $user = null) {
		if ($user instanceof Contact && $user->isAdministrator ()) {
			return true;
		}
		$can_write_cond = $can_write ? " AND `can_write` = 1" : "";
		$can_delete_cond = $can_delete ? " AND `can_delete` = 1" : "";
		
		$ret = false;
		Hook::fire('can_read_ot_in_member', array('pgs'=>$permission_group_ids, 'ot'=>$object_type_id, 'can_write' => $can_write_cond, 'can_delete' => $can_delete_cond), $ret);
		if ($ret) return $ret;
		
		$res = DB::execute("SELECT permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE `member_id` = '$member_id' AND `object_type_id` = '$object_type_id' AND 
	  							`permission_group_id` IN ( $permission_group_ids ) $can_write_cond $can_delete_cond limit 1");

		return $res->numRows() > 0;
	}
	
	
	function canAccessObjectTypeinMembersPermissionGroups($permission_group_ids, $member_ids, $object_type_id, $can_write = false, $can_delete = false) {
		if (is_array($permission_group_ids)) {
			$permission_group_ids = implode(",", $permission_group_ids);
		}
		if (is_array($member_ids)) {
			$member_ids = implode(",", $member_ids);
		}
		
		$can_write_cond = $can_write ? " AND `can_write` = 1" : "";
		$can_delete_cond = $can_delete ? " AND `can_delete` = 1" : "";
		
		$ot_cond = $object_type_id > 0 ? "AND `object_type_id` = '$object_type_id'" : "";
		
		$sql = "SELECT permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE `member_id` IN (".$member_ids.") $ot_cond AND `permission_group_id` IN ( $permission_group_ids ) $can_write_cond $can_delete_cond";
		$rows = DB::executeAll($sql);

		$res = array();
		if ($rows && is_array($rows)) {
			foreach ($rows as $row) $res[] = $row['permission_group_id'];
		}
		return $res;
	}
	
	
	
	function getActiveContextPermissions(Contact $contact, $object_type_id, $context, $dimension_members, $can_write = false, $can_delete = false) {
		if ($contact instanceof Contact && $contact->isAdministrator ()) {
			return $dimension_members;
		}
		$allowed_members = array ();
		
		$permission_group_ids = ContactPermissionGroups::getContextPermissionGroupIdsByContactCSV ( $contact->getId () );
		$perm_ids_array = explode ( ",", $permission_group_ids );
		
		foreach ( $perm_ids_array as $pid ) {
			foreach ( $dimension_members as $member_id ) {
				//check if exists a context permission group for this object type id in this member
				$contact_member_permission = self::findById ( array ('permission_group_id' => $pid, 'member_id' => $member_id, 'object_type_id' => $object_type_id ) );
				if ($contact_member_permission instanceof ContactMemberPermission && (! $can_write || $contact_member_permission->getCanWrite () && ! $can_delete || $contact_member_permission->getCanDelete ())) {
					$permission_contexts = PermissionContexts::findAll ( array ('`contact_id` = ' . $contact->getId (), 'permission_group_id' => $pid, 'member_id' => $member_id ) );
					//check if the actual context applies to this permission group
					if (! is_null ( $permission_contexts )) {
						$dimensions = array ();
						$context_members = array ();
						foreach ( $permission_contexts as $pc ) {
							$member = $pc->getMember ();
							$dimension_id = $member->getDimensionId ();
							if (! in_array ( $dimension_id, $dimensions )) {
								$dimensions [] = $dimension_id;
								$context_members [$dimension_id] = array ();
							}
							$context_members [$dimension_id] [] = $member;
						}
						$include = true;
						foreach ( $dimensions as $dim_id ) {
							$members_in_context = array ();
							foreach ( $context_members [$dim_id] as $value ) {
								if (in_array ( $value, $context ))
									$members_in_context [] = $value;
							}
							if (count ( $members_in_context ) == 0) {
								$include = $include && false;
							}
						}
						if ($include && count ( $dimensions ) != 0)
							$allowed_members [] = $member_id;
					}
				}
			}
		}
		return $allowed_members;
	}
	
	/**
	 * Enter description here ...
	 * @param Contact $contact
	 * @param array of ObjectType $types
	 * @param array of int  $members
	 */
	function grantAllPermissions(Contact $contact, $members) {
		if ($contact->getUserType() > 0  && count($members)) {
			$userType = $contact->getUserTypeName() ;
			$permissions = array(); // TO fill sharing table
			$gid = $contact->getPermissionGroupId ();
			foreach ( $members as $member_id ) {
				//new 
				$member = Members::findById($member_id);
				$dimension = $member->getDimension();
				
				$types = array();
				$member_types = DimensionObjectTypeContents::getContentObjectTypeIds($dimension->getId(), $member->getObjectTypeId());
				if (count($member_types)) {
					switch ( $userType ) {
						case 'Super Administrator':  case 'Administrator': case 'Manager': case 'Executive' :
							$types = $member_types;
							break;
						case 'Collaborator Customer': case 'Non-Exec Director':
							foreach (ObjectTypes::findAll(array("conditions"=>" name NOT IN ('mail') ")) as $type) {//TODO This sucks 
								$types[]=$type->getId();
							}
							break;
						case 'Internal Collaborator':  case 'External Collaborator': 
							foreach (ObjectTypes::findAll(array("conditions"=>" name NOT IN ('mail','contact', 'report') ")) as $type) {//TODO This sucks 
								$types[]=$type->getId();
							}
							break;
						case 'Guest Customer':
							foreach (ObjectTypes::findAll(array("conditions"=>" name IN ('message', 'weblink', 'event', 'file') ")) as $type) {//TODO This sucks 
								$types[]=$type->getId();
							}
							break;
						case 'Guest':
							foreach (ObjectTypes::findAll(array("conditions"=>" name IN ('message', 'weblink', 'event') ")) as $type) {//TODO This sucks 
								$types[]=$type->getId();
							}
							break;
					}
				}
				foreach ( $types as $type_id ) {
					if (! ContactMemberPermissions::instance ()->findOne ( array ("conditions" => 
							"permission_group_id = $gid	AND 
							member_id = $member_id AND 
							object_type_id = $type_id" ) )) {
						$cmp = new ContactMemberPermission ();
						$cmp->setPermissionGroupId ( $gid );
						$cmp->setMemberId ( $member_id );
						$cmp->setObjectTypeId ( $type_id );
						if ($userType != "Guest" && $userType != "Guest Customer" ){
							$cmp->setCanWrite ( 1 );
							$cmp->setCanDelete ( 1 );
						}else{
							$cmp->setCanWrite ( 0 );
							$cmp->setCanDelete ( 0 );
						}
						$cmp->save ();
						
						
						$perm = new stdClass();
						$perm->m = $member_id;
						$perm->r = 1;
						$perm->w = 1;
						$perm->d = 1;
						$perm->o = $type_id;
						$permissions[] = $perm;
						
					}
				}
			}
			if (count($permissions)) {
				$stCtrl = new SharingTableController();
				$stCtrl->afterPermissionChanged($contact->getPermissionGroupId(), $permissions);
			}
			
			
		}
	}

} 
