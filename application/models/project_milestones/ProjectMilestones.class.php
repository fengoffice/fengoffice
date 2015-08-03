<?php

/**
 * ProjectMilestones, generated on Sat, 04 Mar 2006 12:50:11 +0100 by
 * DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class ProjectMilestones extends BaseProjectMilestones {

	function __construct() {
		parent::__construct();
		$this->object_type_name = 'milestone';
	}
	

	/**
	 * Returns milestones from active context and parent members of the active context
	 *
	 * @param User $user
	 * @return array
	 */
	static function getActiveMilestonesByUser(Contact $user, $context = null) {
		if (is_null($context)) {
			$context = active_context();
		}
		
		$filter_option = config_option('milestone_selector_filter');
		
		$members = array();
		$parents = array();
		if ($filter_option == 'current_and_parents' || $filter_option == 'current') {
			foreach ($context as $k => $member) {
				if ($member instanceof Member) {
					if ($member->getDimension()->getCode() == 'tags') continue;
					$members[] = $member->getId();
					if ($filter_option == 'current_and_parents') {
						$tmp = $member->getParentMember();
						while ($tmp != null){
							$parents[] = $tmp->getId();
							$tmp = $tmp->getParentMember();
						}
					}
				}
			}
		}
		$members = array_merge($members, $parents);
		
		$pgs = logged_user()->getPermissionGroupIds();
		if (count($pgs) == 0) $pgs[] = 0;
		
		$permission_conditions = "EXISTS(SELECT sh.object_id FROM ".TABLE_PREFIX."sharing_table sh WHERE sh.object_id=o.id AND sh.group_id IN (".implode(',',$pgs)."))";
		
		if ($filter_option != 'all' && count($members) > 0) {
			$member_conditions = " AND EXISTS(SELECT om.object_id FROM ".TABLE_PREFIX."object_members om WHERE om.object_id=o.id AND om.member_id IN (".implode(',',$members)."))";
		} else {
			$member_conditions = "";
		}
		
		$conditions = "trashed_by_id = 0 AND archived_by_id = 0 AND $permission_conditions $member_conditions";
		$milestones = ProjectMilestones::findAll(array('conditions' => $conditions, 'order' => 'name'));

		return $milestones;
	} // getActiveMilestonesByUser

	/**
	 * Return active milestones that are assigned to the specific user and belongs to specific project
	 *
	 * @param User $user
	 * @param Project $project
	 * @return array
	 */
	static function getActiveMilestonesByUserAndProject(Contact $contact, $archived = false) {
		if ($archived) $archived_cond = "`archived_on` <> 0 AND ";
		else $archived_cond = "`archived_on` = 0 AND ";
		
		return self::findAll(array(
        	'conditions' => array('`is_template` = false AND (`assigned_to_contact_id` = ? OR `assigned_to_contact_id` = ? ) AND ' . $archived_cond . ' AND `completed_on` = ?', $contact->getId(), $contact->getCompanyId(), EMPTY_DATETIME),
        	'order' => '`due_date`'
        )); // findAll
	} // getActiveMilestonesByUserAndProject
	 

	/**
	 * Returns an unsaved copy of the milestone. Copies everything except open/closed state,
	 * anything that needs the task to have an id (like tags, properties, tasks),
	 * administrative info like who created the milestone and when, etc.
	 *
	 * @param ProjectMilestone $milestone
	 * @return ProjectMilestone
	 */
	function createMilestoneCopy(ProjectMilestone $milestone) {
		$new = new ProjectMilestone();
		$new->setObjectName($milestone->getObjectName());
		$new->setDescription($milestone->getDescription());
		$new->setIsUrgent($milestone->setIsUrgent());
		$new->setDueDate($milestone->getDueDate());
		return $new;
	}

	/**
	 * Copies tasks from milestoneFrom to milestoneTo.
	 *
	 * @param ProjectMilestone $milestoneFrom
	 * @param ProjectMilestone $milestoneTo
	 */
	function copyTasks(ProjectMilestone $milestoneFrom, ProjectMilestone $milestoneTo, $as_template = false) {
		//FIXME 
		foreach ($milestoneFrom->getTasks($as_template) as $sub) {
			if ($sub->getParentId() != 0) continue;
			$new = ProjectTasks::createTaskCopy($sub);
			
			$new->setMilestoneId($milestoneTo->getId());
			
			$new->save();
			
			$object_controller = new ObjectController();
			$members = $milestoneFrom->getMemberIds() ;
			if (count($members)) {
				$object_controller->add_to_members($new, $members);
			}
			
			/*
			foreach ($sub->getWorkspaces() as $workspace) {
				if (ProjectTask::canAdd(logged_user(), $workspace)) {
					$new->addToWorkspace($workspace);
				}
			}

			if (!$as_template && active_project() instanceof Project && ProjectTask::canAdd(logged_user(), active_project())) {
				$new->removeFromAllWorkspaces();
				$new->addToWorkspace(active_project());
			}
			
			*/
			$new->copyCustomPropertiesFrom($sub);
			$new->copyLinkedObjectsFrom($sub);
			ProjectTasks::copySubTasks($sub, $new, $as_template);
		}
	}
	
	
	/**
	 * Return Day milestones
	 *
	 * @access public
	 * @param DateTimeValue $date_start in user gmt
	 * @param DateTimeValue $date_end	in user gmt	 * 
	 */
	function getRangeMilestones(DateTimeValue $date_start, DateTimeValue $date_end, $archived = false) {
		
		$from_date = new DateTimeValue ( $date_start->getTimestamp () );
		$from_date = $from_date->beginningOfDay ();
		$to_date = new DateTimeValue ( $date_end->getTimestamp () );
		$to_date = $to_date->endOfDay ();
		
		//set dates to gmt 0 for sql
		$from_date->advance(-logged_user()->getTimezone() * (3600));
		$to_date->advance(-logged_user()->getTimezone() * (3600));
		
		$archived_cond = " AND `archived_on` ".($archived ? "<>" : "=")." 0";
		
		$conditions = DB::prepareString(' AND `is_template` = false AND `completed_on` = ? AND (`due_date` >= ? AND `due_date` < ?) ' . $archived_cond, array(EMPTY_DATETIME, $from_date, $to_date));
		
		$result = self::instance()->listing(array(
			"extra_conditions" => $conditions
		));
		
		return $result->objects;
	}
	
	
	
	private static $info_cache = null;
	
	static function getMilestonesInfo($mid) {
		if (self::$info_cache == null) {
			self::$info_cache = array();
			// completed
			$rows = DB::executeAll("select count(object_id) as row_count, milestone_id from ".TABLE_PREFIX."project_tasks use index (completed_on) where completed_on > '0000-00-00' group by milestone_id;");
			if (is_array($rows)) {
				foreach ($rows as $row) {
					if (array_var($row, 'milestone_id') > 0) {
						if (!isset(self::$info_cache[$row['milestone_id']])) {
							self::$info_cache[$row['milestone_id']] = array();
						}
						self::$info_cache[$row['milestone_id']]['tc'] = array_var($row, 'row_count');
					}
				}
			}
			// all milestone tasks
			$rows = DB::executeAll("select count(object_id) as row_count, milestone_id from ".TABLE_PREFIX."project_tasks use index (milestone_id) group by milestone_id;");
			if (is_array($rows)) {
				foreach ($rows as $row) {
					if (array_var($row, 'milestone_id') > 0) {
						if (!isset(self::$info_cache[$row['milestone_id']])) {
							self::$info_cache[$row['milestone_id']] = array();
						}
						self::$info_cache[$row['milestone_id']]['tnum'] = array_var($row, 'row_count');
					}
				}
			}
		}
		
		return array_var(self::$info_cache, $mid);
	}

} // ProjectMilestones

?>