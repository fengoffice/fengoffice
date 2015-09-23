<?php

/**
 * Application logs manager class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ApplicationLogs extends BaseApplicationLogs {

	const ACTION_ADD         = 'add';
	const ACTION_UPLOAD      = 'upload';
	const ACTION_EDIT        = 'edit';
	const ACTION_DELETE      = 'delete';
	const ACTION_TRASH       = 'trash';
	const ACTION_UNTRASH     = 'untrash';
	const ACTION_CLOSE       = 'close';
	const ACTION_OPEN        = 'open';
	const ACTION_SUBSCRIBE   = 'subscribe';
	const ACTION_UNSUBSCRIBE = 'unsubscribe';
	const ACTION_COMMENT     = 'comment';
	const ACTION_LINK     	 = 'link';
	const ACTION_UNLINK      = 'unlink';
	const ACTION_LOGIN       = 'login';
	const ACTION_LOGOUT      = 'logout';
	const ACTION_ARCHIVE     = 'archive';
	const ACTION_UNARCHIVE   = 'unarchive';
	const ACTION_MOVE        = 'move';
	const ACTION_COPY        = 'copy';
	const ACTION_READ        = 'read';
	const ACTION_DOWNLOAD    = 'download';
	const ACTION_CHECKOUT    = 'checkout';
	const ACTION_CHECKIN     = 'checkin';
    const ACTION_MADE_SEVERAL_CHANGES     = 'made several changes';
	
	/**
	 * Create new log entry and return it
	 *
	 * Delete actions are automatically marked as silent if $is_silent value is not provided (not NULL)
	 *
	 * @param ApplicationDataObject $object
	 * @param Project $project
	 * @param DataManager $manager
	 * @param boolean $save Save log object before you save it
	 * @return ApplicationLog
	 */
	static function createLog($object, $action = null, $is_private = false, $is_silent = null, $save = true, $log_data = '') {
		
		$args = array (
			'action' => &$action,
			'is_private' => &$is_private,
			'is_silent' => &$is_silent,
			'save' => &$save,
			'log_data' => &$log_data
		);
		/**
		 * Modify log and notification parameters before creating the application log registry
		 */
		Hook::fire('application_logs_create', $object, $args);
		
		if(is_null($action)) {
			$action = self::ACTION_ADD;
		} // if
		if(!self::isValidAction($action)) {
			throw new Error("'$action' is not valid log action");
		} // if
		if($object instanceof TemplateTask || $object instanceof TemplateMilestone){
			$is_silent = true;
		}
		if(is_null($is_silent)) {
			$is_silent = $action == self::ACTION_DELETE;
		} else {
			$is_silent = (boolean) $is_silent;
		} // if

		if (!$is_silent) {
			try {
				Notifier::notifyAction($object, $action, $log_data);
			} catch (Exception $ex) {
				Logger::log($ex->getMessage());
			}
		}
		
		$log = new ApplicationLog();
		if (logged_user() instanceof Contact) {
			$log->setTakenById(logged_user()->getId());
		} else {
			$log->setTakenById(0);
		}
		if ($object instanceof ContentDataObject) {
			$log->setRelObjectId($object->getObjectId());
			$log->setObjectName($object->getObjectName());
		}
		if ($object instanceof Member) {
			$log->setMemberId($object->getId());
			$log->setRelObjectId($object->getObjectId());
			$log->setObjectName($object->getName());
		}
		
		$log->setAction($action);
		$log->setIsPrivate($is_private);
		$log->setIsSilent($is_silent);
		$log->setLogData($log_data);
		
		if($save) {
			$log->save();
		} // if
		
		return $log;
	} // createLog

	
	/**
	 * Check if specific action is valid
	 *
	 * @param string $action
	 * @return boolean
	 */
	static function isValidAction($action) {
		static $valid_actions = null;

		if(!is_array($valid_actions)) {
			$valid_actions = array(
			self::ACTION_UPLOAD,
			self::ACTION_ADD,
			self::ACTION_EDIT,
			self::ACTION_DELETE,
			self::ACTION_CLOSE,
			self::ACTION_OPEN,
			self::ACTION_TRASH,
			self::ACTION_UNTRASH,
			self::ACTION_SUBSCRIBE,
			self::ACTION_UNSUBSCRIBE,
			self::ACTION_COMMENT,
			self::ACTION_LINK,
			self::ACTION_UNLINK,
			self::ACTION_LOGIN,
			self::ACTION_LOGOUT,
			self::ACTION_ARCHIVE,
			self::ACTION_UNARCHIVE,
			self::ACTION_MOVE,
			self::ACTION_COPY,
			self::ACTION_READ,
			self::ACTION_DOWNLOAD,
			self::ACTION_CHECKOUT,
			self::ACTION_CHECKIN
			); // array
		} // if
		
		Hook::fire('application_logs_valid_action', null, $valid_actions);

		return in_array($action, $valid_actions);
	} // isValidAction
	
	static function countObjectLogs($object, $include_private = false, $include_silent = false, $extra_conditions="") {
		$private_filter = $include_private ? '1' : '0';
		$silent_filter = $include_silent ? '1' : '0';
		
		if ($object instanceof Contact && $object->isUser()){
			
			$userCond = " AND `taken_by_id` = " . $object->getId();
			
			return self::count('`is_private` <= '.$private_filter.' AND `is_silent` <= '.$silent_filter.' '.$userCond. $extra_conditions);
			
		} else {
			
			return self::count('`is_private` <= '.$private_filter.' AND `is_silent` <= '.$silent_filter.' AND 
				(`rel_object_id` = ('.$object->getId().') OR `rel_object_id` IN (SELECT com.object_id FROM '.TABLE_PREFIX.'comments com WHERE com.rel_object_id='.$object->getId().' )) 
				' . $extra_conditions
			);
		}
	}

	/**
	 * Return entries related to specific object
	 *
	 * If $include_private is set to true private entries will be included in result. If $include_silent is set to true
	 * logs marked as silent will also be included. $limit and $offset are there to control the range of the result,
	 * usually we don't want to pull the entire log but just the few most recent entries. If NULL they will be ignored
	 *
	 * @param ApplicationDataObject $object
	 * @param boolean $include_private
	 * @param boolean $include_silent
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	static function getObjectLogs($object, $include_private = false, $include_silent = false, $limit = null, $offset = null, $extra_conditions="") {
		$private_filter = $include_private ? 1 : 0;
		$silent_filter = $include_silent ? 1 : 0;		
		
		// User History
		if ($object instanceof Contact && $object->isUser()){		
			$private_filter = $include_private ? 1 : 0;
			$silent_filter = $include_silent ? 1 : 0;		
			$userCond = " AND `taken_by_id` = " . $object->getId();
			
			$conditions =  array(
				'`is_private` <= ? AND `is_silent` <= ? '.$userCond. $extra_conditions, 
				$private_filter, 
				$silent_filter); 
				
			return self::findAll(array(
				'conditions' => $conditions ,
				'order' => '`created_on` DESC',
				'limit' => $limit,
				'offset' => $offset,
			)); // findAll				
		} else {
			$logs = self::findAll(array(
				'conditions' => array('`is_private` <= ? AND `is_silent` <= ? AND 
					(`rel_object_id` = (?) OR `rel_object_id` IN (SELECT com.object_id FROM '.TABLE_PREFIX.'comments com WHERE com.rel_object_id=? )) 
					'.$extra_conditions, $private_filter, $silent_filter, $object->getId(), $object->getId()),
				'order' => '`created_on` DESC',
				'limit' => $limit,
				'offset' => $offset,
			)); // findAll
		}
		
		$next_offset = $offset + $limit;
		do {
			// Look for objects that user cannot see
			$removed = 0;
			foreach ($logs as $k => $log) {
				if ($log->getAction() == 'link') {
					$lobj = Objects::findObject($log->getLogData());
					if (!$lobj instanceof ApplicationDataObject || !can_access(logged_user(), $lobj->getMembers(), $lobj->getObjectTypeId(), ACCESS_LEVEL_READ)) {
						$removed++;
						unset($logs[$k]);
					}
				}
			}
			// Get more objects to substitute the removed ones
			if ($limit && $removed > 0) {
				$other_logs = self::findAll(array(
			        'conditions' => array('`is_private` <= ? AND `is_silent` <= ? AND `rel_object_id` = (?) AND `is_private` <= ? AND `is_silent` <= ? 
			        	AND (`rel_object_id`IN (SELECT `id` FROM '.Comments::instance()->getTableName(true).' WHERE `rel_object_id` = (?)) 
			        	AND `rel_object_id`IN (SELECT `object_id` FROM '.Timeslots::instance()->getTableName(true).' WHERE `rel_object_id` = (?)))'.$extra_conditions, $private_filter, $silent_filter, $object->getId(),$private_filter, $silent_filter, $object->getId(), $object->getId()),
			        'order' => '`created_on` DESC',
			        'limit' => $next_offset + $removed,
			        'offset' => $next_offset,
				)); // findAll
				$logs = array_merge($logs, $other_logs);
				$next_offset += $removed;
				if (count($logs) > $limit) $logs = array_slice($logs, 0, $limit);
			}
		} while ($removed > 0);
		
		return $logs;
	} // getObjectLogs

	static function getLastActivities() {
		$members = active_context_members(false); // Context Members Ids
		$options = explode(",",user_config_option("filters_dashboard",null,null,true));

		$extra_conditions = "action <> 'login' AND action <> 'logout' AND action <> 'subscribe' AND created_by_id > '0'";
		if($options[1] == 0){//do not show timeslots
			$extra_conditions .= "AND action <> 'open' AND action <> 'close' AND ((action <> 'add' OR action <> 'edit' OR action <> 'delete') AND object_name NOT LIKE 'Time%')";
		}
		
		// task assignment conditions
		if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_see_assigned_to_other_tasks')) {
			$extra_conditions .= " AND IF((SELECT o.object_type_id FROM ".TABLE_PREFIX."objects o WHERE o.id=rel_object_id)=(SELECT ot.id FROM ".TABLE_PREFIX."object_types ot WHERE ot.name='task'),
				(SELECT t.assigned_to_contact_id FROM ".TABLE_PREFIX."project_tasks t WHERE t.object_id=rel_object_id) = ".logged_user()->getId().",
				true)";
		}
		
		//do not display template tasks logs 
		$extra_conditions .= " AND IF((SELECT o.object_type_id FROM ".TABLE_PREFIX."objects o WHERE o.id=rel_object_id)=(SELECT ot.id FROM ".TABLE_PREFIX."object_types ot WHERE ot.name='template_task'), false, true)";

		// if logged user is guest dont show other users logs
		if (logged_user()->isGuest()) {
			$extra_conditions .= " AND `created_by_id`=".logged_user()->getId();
		}
		
		$members_sql = "";
		$is_member_child = "";
		if(count($members) > 0){
			$members_sql = "(EXISTS(
				SELECT om.object_id FROM  ".TABLE_PREFIX."object_members om
				WHERE om.member_id IN (" . implode ( ',', $members ) . ") AND rel_object_id = om.object_id
				GROUP BY object_id
				HAVING count(member_id) = ".count($members)."
			))";
			
			$is_member_child = "AND mem.parent_member_id IN (" . implode ( ',', $members ) . ")";
		}
		
		//permissions
		$logged_user_pgs = implode(',', logged_user()->getPermissionGroupIds());
			
		$permissions_condition = "al.rel_object_id IN (
		SELECT sh.object_id FROM ".TABLE_PREFIX."sharing_table sh
		WHERE al.rel_object_id = sh.object_id AND sh.object_id > 0
		AND sh.group_id  IN ($logged_user_pgs)
		)";

		
		$sql = "SELECT al.id FROM ".TABLE_PREFIX."application_logs al 
				WHERE $permissions_condition AND $extra_conditions";
		if ($members_sql != "") {
			$sql .= " AND $members_sql";
			
			//do not display users logs
			$sql .= " AND NOT EXISTS(SELECT con.object_id FROM ".TABLE_PREFIX."contacts con WHERE con.object_id=rel_object_id AND user_type > 0)";
		}
		$sql .= " ORDER BY created_on DESC LIMIT 100";
		$id_rows = array_flat(DB::executeAll($sql));
		
		// if logged user is guest dont show other users logs
		$user_condition = "";
		if (logged_user()->isGuest()) {
			$user_condition .= " AND `created_by_id`=".logged_user()->getId();
		}
		
		$member_logs_sql = "SELECT al.id FROM ".TABLE_PREFIX."application_logs al
									INNER JOIN ".TABLE_PREFIX."members mem ON mem.id=al.member_id 
										INNER JOIN ".TABLE_PREFIX."contact_member_cache cmcache ON cmcache.member_id=mem.id AND cmcache.contact_id = ".logged_user()->getId()."
											WHERE al.member_id>0
											$user_condition
											$is_member_child
							ORDER BY created_on DESC LIMIT 100";
		
		$m_id_rows = array_flat(DB::executeAll($member_logs_sql));
		
		$id_rows = array_filter(array_merge($id_rows, $m_id_rows));
		
		$logs = array();
		if (count($id_rows) > 0) {
			$logs = ApplicationLogs::findAll(array("condition" => "id IN (".implode(',',$id_rows).")", "order" => "created_on DESC"));
		}
		return $logs;
	}

} // ApplicationLogs

?>