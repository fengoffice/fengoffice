<?php

/**
 * ProjectEvent class
 * Generated on Tue, 04 Jul 2006 06:46:08 +0200 by DataObject generation tool
 *
 * @author Marcos Saiz <marcos.saiz@gmail.com>
 */
class ProjectEvent extends BaseProjectEvent {

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array('name', 'description');


	/**
	 * Array of invitated Users
	 *
	 * @var array
	 */
	private $event_invitations = null;

	/**
	 * Contruct the object
	 *
	 * @param void
	 * @return null
	 */
	function __construct() {
		//      $this->addProtectedAttribute('system_Eventname', 'Eventname', 'type_string', 'Eventsize');
		parent::__construct();
	} // __construct

	function getUserName(){
		$user = Contacts::findById($this->getCreatedById());
		if ($user instanceof Contact ) return $user->getUsername();
		else return null;
	}
	
	function getTitle(){
		return $this->getSubject();
	}
	
	function isRepetitive() {
		return $this->getRepeatD() > 0 || $this->getRepeatM() > 0 || $this->getRepeatY() > 0 || $this->getRepeatH() > 0;
	}

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return Event modification URL
	 *
	 * @param void
	 * @return string
	 */
	function getModifyUrl() {
		return get_url('event','edit',array('id'=> $this->getId() ));
		
		
		//return get_url('event','submitevent',array('id'=> $this->getId() ));
		//antes: return get_url('event','modify',array('id'=> $this->getId() ));
		//ejemplo:http://localhost/fengoffice/index.php?ajax=true&a=modify&id=8&day=02&month=4&year=2008&c=event&_dc=1208295398801
	} // getModifyUrl

	/**
	 * Return Event viewing URL
	 *
	 * @param void
	 * @return string
	 */
	function getOpenUrl() {
		return $this->getModifyUrl();
	} // getOpenUrl
	 
	 
	/**
	 * Return Event details URL
	 *
	 * @param void
	 * @return string
	 */
	function getDetailsUrl() {
		return get_url('event', 'view', array('id' => $this->getId()));
	} // getDetailsUrl

	/**
	 * Return comments URL
	 *
	 * @param void
	 * @return string
	 */
	function getCommentsUrl() {
		return $this->getDetailsUrl() . '#objectComments';
	} // getCommentsUrl

	/**
	 * Return Event download URL
	 *
	 * @param void
	 * @return string
	 */
	function getDownloadUrl() {
		return $this->getModifyUrl();
	} // getDownloadUrl

	/**
	 * Return edit Event URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return $this->getModifyUrl();
	} // getEditUrl

	/**
	 * Return delete Event URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('event','delete',array('id'=> $this->getId() ));

	} // getDeleteUrl

	
	function getViewUrl() {
		return get_url('event', 'view', array('id' => $this->getId()));
	}
	
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

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
	 * Returns true if user can download this Event
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canDownload(Contact $user) {
		return can_read($user, $this->getMembers(), $this->getObjectTypeId());
	} // canDownload
	
	
	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return can_add($user, $context, ProjectEvents::instance()->getObjectTypeId(), $notAllowedMember);
	}


	/**
	 * Check if specific user can edit this Event
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

	function save() {
		return parent::save();
		
		// update reminders
		$id = $this->getId();
		$sql = "UPDATE `".TABLE_PREFIX."object_reminders` SET
			`date` = date_sub((SELECT `start` FROM `".TABLE_PREFIX."project_events` WHERE `id` = $id), interval `minutes_before` minute) 
			WHERE `object_id` = $id;";
		DB::execute($sql);
	}
	
	function delete() {
		// delete invitations
		$this->clearInvitations();
		parent::delete();
	}
	
	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------
	
	function getSubject() {
		return $this->getObjectName();
	}

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getDetailsUrl();
	} // getObjectUrl
	 
	 /**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->getObject()->validatePresenceOf('name')) $errors[] = lang('event subject required');
		if(!$this->validateMaxValueOf('description',3000)) $errors[] = lang('event description maxlength');
		if(!$this->getObject()->validateMaxValueOf('name', 100)) $errors[] = lang('event subject maxlength');
	} // validate
	
	
	function getInvitations() {
		if (is_null($this->event_invitations)) {
			$this->event_invitations = array();
			$invs = EventInvitations::instance()->findAll(array('conditions' => 'event_id='.$this->getId()));
			foreach ($invs as $inv) {
				$this->event_invitations[$inv->getContactId()] = $inv;
			}
		}
		return $this->event_invitations;
	}
	function setInvitations($invitations) {
		$this->event_invitations = $invitations;
	}
	
	
	function clearInvitations() {
		$this->event_invitations = array();
		EventInvitations::delete(array ('`event_id` = ?', $this->getId()));
	}
	
	
	function addInvitation($inv) {
		if (!is_array($this->event_invitations)) {
			$this->event_invitations = array();
		}
		if (isset($inv)) {
			$this->event_invitations[$inv->getContactId()] = $inv;
		}
	}

	function cloneEvent($new_st_date,$new_due_date) {
		$new_event = new ProjectEvent();

		$new_event->setObjectName($this->getObjectName());
		$new_event->setDescription($this->getDescription());
		$new_event->setTypeId($this->getTypeId());
		if ($this->getDuration() instanceof DateTimeValue ) {
			$new_event->setDuration(new DateTimeValue($this->getDuration()->getTimestamp()));
		}
		if ($this->getStart() instanceof DateTimeValue ) {
			$new_event->setStart(new DateTimeValue($this->getStart()->getTimestamp()));
		}
		$new_event->setOriginalEventId($this->getObjectId());
		$new_event->save();

		// set next values for repetetive task
		if ($new_event->getStart() instanceof DateTimeValue ) $new_event->setStart($new_st_date);
		if ($new_event->getDuration() instanceof DateTimeValue ) $new_event->setDuration($new_due_date);

		$invitations = EventInvitations::findByEvent($this->getId());
		if ($invitations) {
			foreach($invitations as $invitation){
				$invit = new EventInvitation();
				$invit->setEventId($new_event->getId());
				$invit->setContactId($invitation->getContactId());
				$invit->setInvitationState(logged_user() instanceof Contact && logged_user()->getId() == $invitation->getContactId() ? 1 : 0);
				$invit->save();
			}

		}
		$subscriptions = ObjectSubscriptions::findByEvent($this->getId());
		if ($subscriptions) {
			foreach($subscriptions as $subscription){
				$subscrip = new ObjectSubscription();
				$subscrip->setObjectId($new_event->getId());
				$subscrip->setContactId($subscription->getContactId());
				$subscrip->save();
			}
		}
		$reminders = ObjectReminders::findByEvent($this->getId());
		if ($reminders) {
			foreach($reminders as $reminder){
				$remind = new ObjectReminder();
				$remind->setObjectId($new_event->getId());
				$remind->setMinutesBefore($reminder->getMinutesBefore());
				$remind->setType($reminder->getType());
				$remind->setContext($reminder->getContext());
				$remind->setUserId(0);
				$date = $new_event->getStart();
				if ($date instanceof DateTimeValue) {
					$rdate = new DateTimeValue($date->getTimestamp() - $reminder->getMinutesBefore() * 60);
					$remind->setDate($rdate);
				}
				$remind->save();
			}
		}

		$member_ids = array();
		$context = active_context();
		foreach ($context as $selection) {
			if ($selection instanceof Member) $member_ids[] = $selection->getId();
		}
		$object_controller = new ObjectController();
		$object_controller->add_to_members($new_event, $member_ids); 

		return $new_event;
	}

	
	
	private function forwardRepDate($min_date) {
		if ($this->isRepetitive()) {
			if (!$this->getStart() instanceof DateTimeValue ||	!$min_date instanceof DateTimeValue) {
				return array('date' => $min_date, 'count' => 0); //This should not happen...
			}
			
			$date = new DateTimeValue($this->getStart()->getTimestamp());
			$count = 0;
			if($date->getTimestamp() >= $min_date->getTimestamp()) {
				return array('date' => $date, 'count' => $count);
			}
			
			while ($date->getTimestamp() < $min_date->getTimestamp()) {
				if ($this->getRepeatD() > 0) { 
					$date = $date->add('d', $this->getRepeatD());
				} else if ($this->getRepeatM() > 0) { 
					$date = $date->add('M', $this->getRepeatM());
				} else if ($this->getRepeatY() > 0) { 
					$date = $date->add('y', $this->getRepeatY());
				} else if ($this->getRepeatH() > 0) { 
					$date = $date->add('M', $this->getRepeatMjump());
				}
				$count++;
			}
			return array('date' => $date, 'count' => $count);
		} else {
			return array('date' => $min_date, 'count' => 0);
		}
	}
	
	
	function getRepetitiveInstances($from_date, $to_date) {
		$instances = array();
		if ($this->isRepetitive()) {
			$res = $this->forwardRepDate($from_date);
			
			$ref_date = $res['date'];
			$top_repeat_num = $this->getRepeatNum() - $res['count'];

			$last_repeat = $this->getRepeatEnd() instanceof DateTimeValue ? new DateTimeValue($this->getRepeatEnd()->getTimestamp()) : null;
			if (($this->getRepeatNum() > 0 && $top_repeat_num <= 0) || ($last_repeat instanceof DateTimeValue && $last_repeat->getTimestamp() < $ref_date->getTimestamp())) {
				return array();
			}
			
			$info = array();
			foreach ($this->getColumns() as $col) $info[$col] = $this->getColumnValue($col);
			foreach ($this->getObject()->getColumns() as $col) $info[$col] = $this->getObject()->getColumnValue($col);
			$event = new ProjectEvent();
			$event->setFromAttributes($info);
			$event->setStart(new DateTimeValue($this->getStart()->getTimestamp()));
			$event->setDuration(new DateTimeValue($this->getDuration()->getTimestamp()));
			$event->setId($this->getId());
			$event->setNew(false);
			$event->setInvitations($this->getInvitations());
			
			if (!$event->getRepeatH() > 0){
				$instances[] = $event;
			}
			$num_repetitions = 0;

			while ($ref_date->getTimestamp() < $to_date->getTimestamp()) {
				if (!($event->getStart() instanceof DateTimeValue)) return $instances;
				
				$diff = $ref_date->getTimestamp() - $event->getStart()->getTimestamp();
				$event->setStart(new DateTimeValue($ref_date->getTimestamp()));
				if ($event->getDuration() instanceof DateTimeValue) {
					$event->getDuration()->advance($diff);
				}
				
				$info = array();
				foreach ($event->getColumns() as $col) $info[$col] = $event->getColumnValue($col);
				foreach ($event->getObject()->getColumns() as $col) $info[$col] = $event->getObject()->getColumnValue($col);
				$new_event = new ProjectEvent();
				$new_event->setFromAttributes($info);
				$new_event->setId($event->getId());
				$new_event->setNew(false);
				$new_event->setInvitations($event->getInvitations());
				
				
				$new_due_date = null;
				$new_st_date = null;
				if ($event->getStart() instanceof DateTimeValue ) {
					$new_st_date = new DateTimeValue($event->getStart()->getTimestamp());
				}
				if ($event->getDuration() instanceof DateTimeValue ) {
					$new_due_date = new DateTimeValue($event->getDuration()->getTimestamp());
				}
				
				if ($event->getRepeatD() > 0) {
					if ($new_st_date instanceof DateTimeValue)
						$new_st_date = $new_st_date->add('d', $event->getRepeatD());
					if ($new_due_date instanceof DateTimeValue)
						$new_due_date = $new_due_date->add('d', $event->getRepeatD());
					$ref_date->add('d', $event->getRepeatD());
				}
				else if ($event->getRepeatM() > 0) {
					if ($new_st_date instanceof DateTimeValue)
						$new_st_date = $new_st_date->add('M', $event->getRepeatM());
					if ($new_due_date instanceof DateTimeValue)
						$new_due_date = $new_due_date->add('M', $event->getRepeatM());
					$ref_date->add('M', $event->getRepeatM());
				}
				else if ($event->getRepeatY() > 0) {
					if ($new_st_date instanceof DateTimeValue)
						$new_st_date = $new_st_date->add('y', $event->getRepeatY());
					if ($new_due_date instanceof DateTimeValue)
						$new_due_date = $new_due_date->add('y', $event->getRepeatY());
					$ref_date->add('y', $event->getRepeatY());
				}
				else if ($event->getRepeatH() > 0) {
					$ordinal = 'first ';
					switch ($event->getRepeatWnum()) {
						case 1:
							$ordinal = "first ";
							break;
						case 2:
							$ordinal = "second ";
							break;
						case 3:
							$ordinal = "third ";
							break;
						case 4:
							$ordinal = "fourth ";
							break;
					}
					
					$days = array("0" => 'sunday ',"1" => 'monday ' , "2" => 'tuesday ',
							"3" => 'wednesday ',"4" => 'thursday ',"5" => 'friday ',
							"6" => 'saturday ');
					$day_name = $days[$event->getRepeatDow()-1];
					
					if ($new_st_date instanceof DateTimeValue){		
						//set first day of the month
						$new_st_date->setDay(1);
						
						//date string
						$new_st_date_string = date("c", $new_st_date->getTimestamp());
						
						//go to the fixed day
						$new_st_date_fixed = new DateTime(date("c", strtotime($ordinal . $day_name ." of ". $new_st_date_string)));
						
						$new_st_date = new DateTimeValue($new_st_date_fixed->getTimestamp());						
					}
					if ($new_due_date instanceof DateTimeValue){
						//set first day of the month
						$new_due_date->setDay(1);
						
						//date string
						$new_due_date_string = date("c", $new_due_date->getTimestamp());
						
						//go to the fixed day
						$new_due_date_fixed = new DateTime(date("c", strtotime($ordinal . $day_name ." of ". $new_due_date_string)));
						
						$new_due_date = new DateTimeValue($new_due_date_fixed->getTimestamp());
					}
					
					$ref_date->add('M', $event->getRepeatMjump());									
				}
				
				if ($new_st_date instanceof DateTimeValue) $new_event->setStart($new_st_date);
				if ($new_due_date instanceof DateTimeValue) $new_event->setDuration($new_due_date);
				
				$num_repetitions++;
				if ($top_repeat_num > 0 && $top_repeat_num == $num_repetitions) break;
				if ($last_repeat instanceof DateTimeValue && $last_repeat->getTimestamp() < $ref_date->getTimestamp()) break;

				$instances[] = $new_event;
				$event = $new_event;
			}
		}

		return $instances;
	}
} // projectEvent

?>