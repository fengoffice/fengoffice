<?php
Hook::register('core_dimensions');

function core_dimensions_after_edit_profile($user, &$ignored) {
	$person_member = Members::findOne(array("conditions" => "`object_id` = (".$user->getId().") AND `dimension_id` = (SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code` = 'feng_persons')"));
	
	if ($person_member instanceof Member) {
		$person_member->setName($user->getObjectName());
		$person_member->save();
		evt_add("reload dimension tree", array('dim_id' => $person_member->getDimensionId(),'dim_id' => $person_member->getDimensionId(), 'node' => $person_member->getId()));
	}
	
}


function core_dimensions_after_dragdrop_classify($objects, &$member) {
	$count = 0;
	foreach ($objects as $obj) {
		$count++;
		if ($obj instanceof Contact) {
			if (!isset($persons_dim)) $persons_dim = Dimensions::findByCode("feng_persons");
			if (!isset($dim_ids)) $dim_ids = array($persons_dim->getId());
			$contact_member = Members::findOneByObjectId($obj->getId(), $persons_dim->getId());
			
			$affected_dimensions = core_dim_create_member_associations($obj, $contact_member, array($member), $count == count($objects));
			$dim_ids = array_merge($dim_ids, $affected_dimensions);
		}
	}
	if (isset($dim_ids)) {
		foreach (array_unique($dim_ids) as $dim_id) {
			evt_add("reload dimension tree", array('dim_id' => $dim_id, 'node' => null));
		}
	}
}

function core_dimensions_after_contact_quick_add(Contact $contact, &$null) {
	$member = Members::findOneByObjectId($contact->getId(), Dimensions::findByCode("feng_persons")->getId());
	if (!$member instanceof Member) return;
	$dim_ids = array($member->getDimensionId());
	
	$active_context = active_context();
	foreach ($active_context as $selection) {
		if ($selection instanceof Member) {
			$contact->addToMembers(array($selection));
			$affected_dimensions = core_dim_create_member_associations($contact, $member, array($selection), false);
			$dim_ids = array_merge($dim_ids, $affected_dimensions);
		}
	}
	foreach (array_unique($dim_ids) as $dim_id) {
		evt_add("reload dimension tree", array('dim_id' => $dim_id, 'node' => null));
	}
}


function core_dimensions_after_add_to_members($object, &$added_members) {
	
	// Add to persons and users dimensions
	$user_ids = array();
	if (logged_user() instanceof Contact) $user_ids[] = logged_user()->getId();
	
	if ($object instanceof ProjectTask) {
		/* @var $object ProjectTask */
		if ($object->getAssignedById() > 0) $user_ids[] = $object->getAssignedById();
		if ($object->getAssignedToContactId() > 0) $user_ids[] = $object->getAssignedToContactId();
	}
	if ($object instanceof ProjectEvent) {
		/* @var $object ProjectEvent */
		$invitations = EventInvitations::findAll(array("conditions" => "`event_id` = ".$object->getId()));
		foreach ($invitations as $inv) $user_ids[] = $inv->getContactId();
	}
	
	// only simple contacts, not users
	if ($object instanceof Contact && !$object->isUser()) {
		$member = Members::findOne(array("conditions" => "`object_id` = (".$object->getId().") AND `dimension_id` = (SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code` = 'feng_persons')"));
		if ($member instanceof Member) {
			$object->addToMembers(array($member));
			
			core_dim_create_member_associations($object, $member, $added_members);
		}
	}
	
	$context = active_context();
	if(count($context) > 0){
		foreach ($context as $selection) {
			if ($selection instanceof Member && $selection->getDimension()->getCode() == 'feng_persons') {
				$object->addToMembers(array($selection));
			}
		}
	}
	
	core_dim_add_to_person_user_dimensions ($object, $user_ids);
}

/**
 * 
 * Instantiates dimension asociations for the contact member in feng_persons and the members where the contact belongs to.
 * @param $contact The contact
 * @param $contact_member Member of the contact
 * @param $all_members Members where the object belongs
 */
function core_dim_create_member_associations(Contact $contact, $contact_member, $all_members, $reload_dim = true) {
	
	$creator = logged_user();
	if (!$creator instanceof Contact) {
		$oc = Contacts::instance()->getOwnerCompany();
		if ($oc instanceof Contact) {
			$creator = $oc->getCreatedBy();
		}
	}
	if (!$creator instanceof Contact) {
		return array();
	}
	
	$affected_dimensions = array();
	if ($contact->isUser()) {
		$contact_pgs = $contact->getPermissionGroupIds();
		$del_sub_query = "SELECT member_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id in (".implode(',', $contact_pgs).")";
	} else {
		$del_sub_query = "SELECT member_id FROM ".TABLE_PREFIX."object_members WHERE object_id='".$contact->getId()."'";
	}
	
	if (!$contact_member instanceof Contact) return array();
	
	// one way
	$associations = DimensionMemberAssociations::getAssociatations ( $contact_member->getDimensionId(), $contact_member->getObjectTypeId() );
	foreach ( $associations as $a ) {
		foreach ( $all_members as $m ) {
			if ($m->getDimensionId() == $a->getAssociatedDimensionMemberAssociationId() && $m->getObjectTypeId() == $a->getAssociatedObjectType()) {
				
				$mpm = MemberPropertyMembers::findOne(array('id' => true, 'conditions' => array('association_id = ? AND member_id = ? AND property_member_id = ?', $a->getId(), $contact_member->getId(), $m->getId())));
				//if (!$mpm instanceof MemberPropertyMember) {
				if (is_null($mpm)) {
					$sql = "INSERT INTO " . TABLE_PREFIX . "member_property_members (association_id, member_id, property_member_id, is_active, created_on, created_by_id)
						VALUES (" . $a->getId() . "," . $contact_member->getId() . "," . $m->getId() . ", 1, NOW()," . $creator->getId() . ") ";
					DB::execute( $sql );
					$affected_dimensions[$m->getDimensionId()] = $m->getDimensionId();
				}
			}
		}
		MemberPropertyMembers::instance()->delete('association_id = '.$a->getId().' AND member_id = '.$contact_member->getId() . " AND property_member_id NOT IN ($del_sub_query)");
	}
	
	// reverse way
	$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`associated_dimension_id` = ? AND `associated_object_type_id` = ?", $contact_member->getDimensionId(), $contact_member->getObjectTypeId())));
	foreach ( $associations as $a ) {
		foreach ( $all_members as $m ) {
			if ($m->getDimensionId() == $a->getDimensionId() && $m->getObjectTypeId() == $a->getObjectTypeId()) {
				
				$mpm = MemberPropertyMembers::findOne(array('id' => true, 'conditions' => array('association_id = ? AND property_member_id = ? AND member_id = ?', $a->getId(), $contact_member->getId(), $m->getId())));
				//if (!$mpm instanceof MemberPropertyMember) {
				if (is_null($mpm)) {
					$sql = "INSERT INTO " . TABLE_PREFIX . "member_property_members (association_id, property_member_id, member_id, is_active, created_on, created_by_id)
						VALUES (" . $a->getId() . "," . $contact_member->getId() . "," . $m->getId() . ", 1, NOW()," . $creator->getId() . ") ";
					DB::execute( $sql );
					$affected_dimensions[$m->getDimensionId()] = $m->getDimensionId();
				}
			}
		}
		MemberPropertyMembers::instance()->delete('association_id = '.$a->getId().' AND property_member_id = '.$contact_member->getId() . " AND member_id NOT IN ($del_sub_query)");
	}
	
	// reload affected dimensions
	if ($reload_dim) {
		foreach ($affected_dimensions as $dim_id) {
			evt_add("reload dimension tree", array('dim_id' => $dim_id, 'node' => null));
		}
	}
	
	return $affected_dimensions;
}

/**
 * 
 * After editing permissions refresh associations and object_members for the contact owner of the permission_group modified
 * @param $pg_id Permission group id
 * @param $ignored Ignored
 */
function core_dimensions_after_save_contact_permissions($pg_id, &$ignored) {
	$pg = PermissionGroups::findById($pg_id);
	if ($pg instanceof PermissionGroup && $pg->getContactId() > 0 && $pg->getType() == 'permission_groups') {
		$user = Contacts::findById($pg->getContactId());
		if (!$user instanceof Contact || !$user->isUser()) return;
		
		$member_ids = array();
		$cmp_rows = DB::executeAll("SELECT member_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE permission_group_id=$pg_id");
		if (is_array($cmp_rows) && count($cmp_rows) > 0) {
			$cmps = array_flat($cmp_rows);
			foreach ($cmps as $mid) {
				$member_ids[$mid] = $mid;
			}
		}
		if (count($member_ids) == 0) return;
		
		$members = Members::findAll(array('conditions' => 'id IN ('.implode(',', $member_ids).')'));
		$persons_dim = Dimensions::findByCode("feng_persons");
		$user_member = Members::findOneByObjectId($user->getId(), $persons_dim->getId());
		
		$affected_dimensions = core_dim_create_member_associations($user, $user_member, $members);
		
		// remove from all members of the affected dimensions
		if (count($affected_dimensions) > 0) {
			$affected_member_ids = Members::findAll(array('id' => true, 'conditions' => 'dimension_id IN ('.implode(',', $affected_dimensions).')'));
			if (count($affected_member_ids) > 0) {
				ObjectMembers::removeObjectFromMembers($user, logged_user(), $members, $affected_member_ids);
			}
		}		
		// add user content object to associated members
		$obj_controller = new ObjectController();
		ObjectMembers::addObjectToMembers($user->getId(), $members);
		
		// add user content object to sharing table
		$user->addToSharingTable();
	}
}


function core_dimensions_after_save_member_permissions($params, &$ignored) {
	$member = array_var($params, 'member');
	if (!$member instanceof Member || !($member->getId()>0)) return;
	$permission_group_ids = array();
	
	$cmp_rows = DB::executeAll("SELECT DISTINCT permission_group_id FROM ".TABLE_PREFIX."contact_member_permissions WHERE member_id = '".$member->getId()."' AND permission_group_id IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE type IN ('permission_groups','user_groups'))");
	if (is_array($cmp_rows)) {
		foreach ($cmp_rows as $row) {
			$permission_group_ids[$row['permission_group_id']] = $row['permission_group_id'];
		}
	}
	
	$contacts = array();
	// users
	if (count($permission_group_ids) > 0) {
		$contacts = Contacts::findAll(array('conditions' => 'user_type > 0 && permission_group_id IN ('.implode(',', $permission_group_ids).')'));
	}

	$contact_ids = array(0);
	
	$persons_dim = Dimensions::findByCode("feng_persons");

	core_dim_remove_contacts_member_associations($member);
	
	foreach ($contacts as $contact) {
		$contact_id = $contact->getId();
		$contact_member = Members::findOneByObjectId($contact_id, $persons_dim->getId());
		if ($contact_member instanceof Member) {
			core_dim_add_contact_member_associations($contact_member, $member);
			
			if ($contact instanceof Contact && $contact->isUser()) {
				$has_project_permissions = ContactMemberPermissions::instance()->count("permission_group_id = '".$contact->getPermissionGroupId()."' AND member_id = ".$member->getId()) > 0;
				if (!$has_project_permissions) {
					RoleObjectTypePermissions::createDefaultUserPermissions($contact, $member);
				}
			}
		}
		// add user content object to customer member
		ObjectMembers::addObjectToMembers($contact_id, array($member));
		$contact_ids[] = $contact_id;
	}
	
	// remove contacts whose members are no longer associated to the customer member
	$previous_users_in_member = Contacts::instance()->listing(array(
		'member_ids' => array($member->getId()),
		'ignore_context' => true,
		'extra_conditions' => ' AND e.user_type > 0 AND e.object_id NOT IN ('.implode(',', $contact_ids).')',
	))->objects;
	foreach ($previous_users_in_member as $prev_u) {
		ObjectMembers::removeObjectFromMembers($prev_u, logged_user(), array($member), array($member->getId()));
	}
	
	// refresh dimensions
	evt_add("reload dimension tree", array('dim_id' => $persons_dim->getId(), 'node' => null));
}

function core_dim_add_contact_member_associations($contact_member, $member) {
	// one way
	$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ? AND `associated_dimension_id` = ? AND `associated_object_type_id` = ?", 
		$member->getDimensionId(), $member->getObjectTypeId(), $contact_member->getDimensionId(), $contact_member->getObjectTypeId())));
	foreach ( $associations as $a ) {
		$mpm = MemberPropertyMembers::findOne(array('conditions' => array('association_id = ? AND member_id = ? AND property_member_id = ?', $a->getId(), $member->getId(), $contact_member->getId())));
		if (!$mpm instanceof MemberPropertyMember) {
			$mpm = new MemberPropertyMember();
			$mpm->setAssociationId($a->getId());		
			$mpm->setMemberId($member->getId());
			$mpm->setPropertyMemberId($contact_member->getId());
			$mpm->setIsActive(1);
			$mpm->save();
		}
	}
	
	// reverse way
	$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`dimension_id` = ? AND `object_type_id` = ? AND `associated_dimension_id` = ? AND `associated_object_type_id` = ?", 
		$contact_member->getDimensionId(), $contact_member->getObjectTypeId(), $member->getDimensionId(), $member->getObjectTypeId())));
	foreach ( $associations as $a ) {
		$mpm = MemberPropertyMembers::findOne(array('conditions' => array('association_id = ? AND property_member_id = ? AND member_id = ?', $a->getId(), $member->getId(), $contact_member->getId())));
		if (!$mpm instanceof MemberPropertyMember) {
			$mpm = new MemberPropertyMember();
			$mpm->setAssociationId($a->getId());		
			$mpm->setMemberId($contact_member->getId());
			$mpm->setPropertyMemberId($member->getId());
			$mpm->setIsActive(1);
			$mpm->save();
		}
	}
}

function core_dim_remove_contacts_member_associations(Member $member) {
	$persons_dim = Dimensions::findByCode("feng_persons");
	// one way
	$associations = DimensionMemberAssociations::getAssociatations ( $member->getDimensionId(), $member->getObjectTypeId() );
	foreach ( $associations as $a ) {
		if ($a->getAssociatedDimensionMemberAssociationId() == $persons_dim->getId()) {
			$condition = "association_id = ".$a->getId()." AND member_id = ".$member->getId()." AND property_member_id IN 
				(SELECT m.id FROM ".TABLE_PREFIX."members m WHERE m.object_type_id=".$a->getAssociatedObjectType()." AND m.dimension_id=".$a->getAssociatedDimensionMemberAssociationId().")";
			MemberPropertyMembers::instance()->delete($condition);
		}
	}
	
	// reverse way
	$associations = DimensionMemberAssociations::findAll(array("conditions" => array("`associated_dimension_id` = ? AND `associated_object_type_id` = ?", $member->getDimensionId(), $member->getObjectTypeId())));
	foreach ( $associations as $a ) {
		if ($a->getDimensionId() == $persons_dim->getId()) {
			$condition = "association_id = ".$a->getId()." AND property_member_id = ".$member->getId()." AND member_id IN 
				(SELECT m.id FROM ".TABLE_PREFIX."members m WHERE m.object_type_id=".$a->getObjectTypeId()." AND m.dimension_id=".$a->getDimensionId().")";
			MemberPropertyMembers::instance()->delete($condition);
		}
	}
}


function core_dimensions_after_add_subscribers($params, &$ignored) {
	
	// Add to persons and users dimensions
	core_dim_add_to_person_user_dimensions (array_var($params, 'object'), array_var($params, 'user_ids'));
}


function core_dimensions_after_insert($object, &$ignored) {
	// add member in persons dimension for new contact
	if ($object instanceof Contact && !isset($_POST['user'])) {
		//core_dim_add_new_contact_to_person_dimension($object);
	}
}

/**
 * @param unknown_type $object
 * @param unknown_type $ignored
 */
function core_dimensions_after_update($object, &$ignored) {
	static $objectsProcessed = array();
	
	if ($object instanceof Contact  && !array_var($objectsProcessed,$object->getId())) {
		$person_dim = Dimensions::findOne(array("conditions" => "`code` = 'feng_persons'"));
		$person_ot = ObjectTypes::findOne(array("conditions" => "`name` = 'person'"));
		$company_ot = ObjectTypes::findOne(array("conditions" => "`name` = 'company'"));
		
		$members = Members::findByObjectId($object->getId(), $person_dim->getId());

		if (count($members) == 1 ){ /* @var $member Member */
			$member = $members[0];
			$member->setName($object->getObjectName());
			
			$parent_member_id = $member->getParentMemberId() ;
			$depth = $member->getDepth();
			if ($object->getCompanyId() > 0) {
				$pmember = Members::findOne(array('conditions' => '`object_id` = '.$object->getCompanyId().' AND `object_type_id` = '.$company_ot->getId(). ' AND `dimension_id` = '.$person_dim->getId()));
				if ($pmember instanceof Member) {
					$member->setParentMemberId($pmember->getId());
					$member->setDepth($pmember->getDepth() + 1);
				} else {
					$member->setDepth(1);
					$member->setParentMemberId(0);
				}
			}else{
				//Is first level 
				$member->setDepth(1);
				$member->setParentMemberId(0);
			}
			$object->modifyMemberValidations($member);
			$member->save();
			// reload only if not disabling or enabling user
			if (!(array_var($_REQUEST, 'c') == 'account' && (array_var($_REQUEST, 'a') == 'disable' || array_var($_REQUEST, 'a') == 'restore_user'))) {
				evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId()));
			}
			$objectsProcessed[$object->getId()] = true ;
		}
	}
}

function core_dimensions_after_user_add($object, $ignored) {
	if ($object instanceof Contact) {
		core_dim_add_new_contact_to_person_dimension($object);
	}
}

/**
 * 
 * Fires AFTER User is deleted - Contact.class.php
 * Deletes All members associated with that user  
 * @param Contact $user
 */
function core_dimensions_after_user_deleted(Contact $user, $null) {
	$uid = $user->getId();
	
	if ( $myStuff = Members::findById($user->getPersonalMemberId() ) ) {
		$myStuff->delete();
	}
	
	// Delete All members
	$members = Members::instance()->findByObjectId($uid) ;
	if ( count($members) ) {
		foreach ($members as $member){
			$member->delete();
			evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId()));
		}
	}
}


function core_dimensions_after_object_delete_permanently($object_ids) {
	$person_dim = Dimensions::findByCode('feng_persons');
	$members = Members::findAll(array('conditions' => "`object_id` IN (".implode(",",$object_ids).") AND `dimension_id` = " . $person_dim->getId()));
	foreach ($members as $mem) {
		$obj = Objects::findObject($mem->getObjectId());
		// ensure that the associated object no longer exists before deleting the member
		if (!$object instanceof ContentDataObject) {
			$mem->delete();
		}
	}
}

function core_dimensions_after_object_controller_trash($ids) {
	if (!is_array($ids) && $ids > 0) {
		$person_dim = Dimensions::findByCode('feng_persons');
		if($person_dim instanceof Dimension) {
			$ot = ObjectTypes::findOne(array('conditions' => "`id` IN (SELECT `o`.`object_type_id` FROM `".TABLE_PREFIX."objects` `o` WHERE `o`.`id` = ".DB::escape(array_var($_GET, 'object_id')).")"));
			if ($ot && $ot->getName() == 'contact') {
				evt_add('select dimension member', array('dim_id' => $person_dim->getId(), 'node' => 'root'));
				ajx_current("empty");
				redirect_to(get_url('contact', 'init'));
			}
		}
	}
}


function core_dim_add_new_contact_to_person_dimension($object) {
	/* @var $object Contact */
	$person_ot = ObjectTypes::findOne(array("conditions" => "`name` = 'person'"));
	$company_ot = ObjectTypes::findOne(array("conditions" => "`name` = 'company'"));
	$person_dim = Dimensions::findOne(array("conditions" => "`code` = 'feng_persons'"));
	
	if ($person_ot instanceof ObjectType && $person_dim instanceof Dimension) {
		$oid = $object->isCompany() ? $company_ot->getId() : $person_ot->getId();
		$tmp_mem = Members::findOne(array("conditions" => "`dimension_id` = ".$person_dim->getId()." AND `object_type_id` = $oid AND `object_id` = ".$object->getId()));
		$reload_dimension = true;
		if ($tmp_mem instanceof Member) {
			$member = $tmp_mem;
			$reload_dimension = false;
		} else {
		
			$member = new Member();
			$member->setName($object->getObjectName());
			$member->setDimensionId($person_dim->getId());
			
			$parent_member_id = 0;
			$depth = 1;
			if ($object->isCompany()) {
				$member->setObjectTypeId($company_ot->getId());
			} else {
				$member->setObjectTypeId($person_ot->getId());
				if ($object->getCompanyId() > 0) {
					$pmember = Members::findOne(array('conditions' => '`object_id` = '.$object->getCompanyId().' AND `object_type_id` = '.$company_ot->getId(). ' AND `dimension_id` = '.$person_dim->getId()));
					if ($pmember instanceof Member) {
						$parent_member_id = $pmember->getId();
						$depth = $pmember->getDepth() + 1;
					}
				}
			}
			$member->setParentMemberId($parent_member_id);
			$member->setDepth($depth);
			
			$member->setObjectId($object->getId());
			$member->save();
		}
		
		$sql = "INSERT INTO `".TABLE_PREFIX."contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`)
				 SELECT `c`.`permission_group_id`, ".$person_dim->getId().", 'check'
				 FROM `".TABLE_PREFIX."contacts` `c` 
				 WHERE `c`.`is_company`=0 AND `c`.`user_type`!=0 AND `c`.`disabled`=0 AND `c`.`object_id`=".$object->getId()."
				 ON DUPLICATE KEY UPDATE `dimension_id`=`dimension_id`;";
		DB::execute($sql);
		
		$sql = "INSERT INTO `".TABLE_PREFIX."contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
				 SELECT `c`.`permission_group_id`, ".$member->getId().", `ot`.`id`, (`c`.`object_id` = ".$object->getId().") as `can_write`, (`c`.`object_id` = ".$object->getId().") as `can_delete`
				 FROM `".TABLE_PREFIX."contacts` `c` JOIN `".TABLE_PREFIX."object_types` `ot` 
				 WHERE `c`.`is_company`=0 AND `c`.`object_id`=".$object->getId()."
				 	AND `c`.`user_type`!=0 AND `c`.`disabled`=0
					AND `ot`.`type` IN ('content_object', 'comment', 'located')
				 ON DUPLICATE KEY UPDATE `member_id`=`member_id`;";
		DB::execute($sql);
		DB::execute("DELETE FROM `".TABLE_PREFIX."contact_member_permissions` WHERE `permission_group_id` = 0;");
		
		
		// NEW! Add contact to its own member to be searchable
		if (logged_user() instanceof Contact ){
			$object->addToMembers(array($member));
			$object->addToSharingTable();
		}
		
		// add permission to creator
		if ($object->getCreatedBy() instanceof Contact) {
			$record_count = ContactMemberPermissions::count(array("`permission_group_id` = ? AND `member_id` = ?", $object->getCreatedBy()->getPermissionGroupId(), $member->getId()));
			if ($record_count == 0) {
				DB::execute("INSERT INTO `".TABLE_PREFIX."contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
				 SELECT ".$object->getCreatedBy()->getPermissionGroupId().", ".$member->getId().", `ot`.`id`, 1, 1
				 FROM `".TABLE_PREFIX."object_types` `ot` 
				 WHERE `ot`.`type` IN ('content_object', 'comment', 'located');");
			}
		}
		
		if ($reload_dimension) {
			evt_add("reload dimension tree", array('dim_id' => $member->getDimensionId()));
		}
	}
}


function core_dim_add_to_person_user_dimensions ($object, $user_ids) {
	if (logged_user() instanceof Contact) {
		
		$members = Members::findAll(array("conditions" => "`object_id` IN (".implode(",", $user_ids).") AND `dimension_id` IN (SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code` IN ('feng_persons'))"));
		if (is_array($members) && count($members) > 0) {
			$object->addToMembers($members);
		}
	}
}

function core_dim_add_company_to_users_dimension($object, $user_dim, $company_ot) {
	
	$member = new Member();
	$member->setName($object->getObjectName());
	$member->setObjectTypeId($company_ot->getId());
	$member->setDimensionId($user_dim->getId());
	$member->setDepth(1);
	$member->setParentMemberId(0);
	$member->setObjectId($object->getId());
	$member->save();
	
	// permisssions
	$sql = "INSERT INTO `".TABLE_PREFIX."contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`)
			 SELECT `c`.`permission_group_id`, ".$user_dim->getId().", 'check'
			 FROM `".TABLE_PREFIX."contacts` `c` 
			 WHERE `c`.`is_company`=0 AND `c`.`user_type`!=0 AND `c`.`disabled`=0
			 ON DUPLICATE KEY UPDATE `dimension_id`=`dimension_id`;";
	DB::execute($sql);
	
	$sql = "INSERT INTO `".TABLE_PREFIX."contact_member_permissions` (`permission_group_id`, `member_id`, `object_type_id`, `can_write`, `can_delete`)
			 SELECT `c`.`permission_group_id`, ".$member->getId().", `ot`.`id`, (`c`.`object_id` = ".$object->getId().") as `can_write`, (`c`.`object_id` = ".$object->getId().") as `can_delete`
			 FROM `".TABLE_PREFIX."contacts` `c` JOIN `".TABLE_PREFIX."object_types` `ot` 
			 WHERE `c`.`is_company`=0 
			 	AND `c`.`user_type`!=0 AND `c`.`disabled`=0
				AND `ot`.`type` IN ('content_object', 'comment')
			 ON DUPLICATE KEY UPDATE `member_id`=`member_id`;";
	DB::execute($sql);
	
	return $member;
}


function core_dimensions_quickadd_extra_fields($parameters) {
	if (array_var($parameters, 'dimension_id') == Dimensions::findByCode("feng_persons")->getId()) {
		tpl_display(PLUGIN_PATH."/core_dimensions/templates/quickadd_extra_fields.php");
	}
}
