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
	 * Flag to notify invited people when this event is deleted or not
	 *
	 * @var boolean
	 */
	private $notify_invited_people_when_deleted = true;

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
		$user = Contacts::instance()->findById($this->getCreatedById());
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
		$url = $this->getModifyUrl();
		Hook::fire('modify_object_edit_url', $this, $url);
		return $url;
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
	
	
	static function canAdd(Contact $user, $context, &$notAllowedMember = ''){
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

	/**
	 * Saves the event. Generates a new uid if it doesn't have one yet.
	 * And update pending reminders.
	 * 
	 * @return boolean
	 */
	function save() {
		// generate uid if not set
		$this->generateUid(false);

		// set logged user as organizer if not set
		if ($this->getOrganizerId() == 0) {
			$this->setOrganizerId(logged_user()->getId());
		}

		// save the event
		$saveResult = parent::save();

		// update reminders
		$id = $this->getId();
		$sql = "
			UPDATE `".TABLE_PREFIX."object_reminders` SET
				`date` = date_sub(
					(SELECT `start` FROM `".TABLE_PREFIX."project_events` WHERE `id` = $id),
					interval `minutes_before` minute
				)
			WHERE `object_id` = $id;
		";
		DB::execute($sql);

		return $saveResult;
	}
	
	function delete() {
		// delete invitations
		$this->clearInvitations();
		parent::delete();
	}
	

	
	
	/**
	 * Set whether to notify invited people when the event is deleted.
	 *
	 * @param bool $value True to notify, false otherwise.
	 */
	function setNotifyInvitedPeopleWhenDeleted($value) {
		$this->notify_invited_people_when_deleted = $value;
	}
	
	/**
	 * Return whether to notify invited people when the event is deleted
	 *
	 * @return boolean True to notify, false otherwise
	 */
	function getNotifyInvitedPeopleWhenDeleted() {
		return $this->notify_invited_people_when_deleted;
	}
	

	/**
	 * Move this event to trash, and notify all invited people about it
	 *
	 * @param int $trashDate unix timestamp when the event was trashed
	 * @param boolean $fire_hook whether to fire hooks
	 * @return boolean
	 */
	function trash($trashDate = null, $fire_hook = true) {
		// notify invited people about the deletion of this event
		if ($this->getNotifyInvitedPeopleWhenDeleted()) {
			$this->notifyInvitedPeople('deleted');
		}

		// call parent class trash method
		return parent::trash($trashDate, $fire_hook);
	}


	/**
	 * Notify all invited people about this event via email notification.
	 *
	 * @param string $action what action to notify about (e.g. 'new', 'modified', 'deleted')
	 * @return void
	 */
	function notifyInvitedPeople($action) {
		// collect invited people and notify them
		$invitations = $this->getInvitations();
		$people = [];
		foreach ($invitations as $inv) {
			$people[] = $inv->getContact();
		}
		// send notification
		Notifier::notifyEventWithIcs($this, $people, $action, logged_user());
	}


	/**
	 * Notify the event organizer about the given invitation state.
	 *
	 * @param int $invitation_state one of the constants defined in EventInvitations
	 * @return void
	 */
	function notifyEventOrganizer($invitation_state) {
		$organizer = $this->getOrganizer();
		// if the event has no organizer or the current user is the organizer, do nothing
		if (!$organizer || $organizer->getId() == logged_user()->getId()) return;

		switch ($invitation_state) {
			case EventInvitations::EVENT_INVITATION_ACCEPTED: $invitation_state_text = 'accepted'; break;
			case EventInvitations::EVENT_INVITATION_DECLINED: $invitation_state_text = 'declined'; break;
			case EventInvitations::EVENT_INVITATION_TENTATIVE: $invitation_state_text = 'tentative'; break;
			default: return;
		}

		// send notification
		Notifier::notifyEventWithIcs($this, [$organizer], 'invitation-'.$invitation_state_text, logged_user());
	}


	/**
	 * Get changes for notification
	 *
	 * @param Contact $user User requesting the changes
	 * @return array An array of changes for notification
	 */
	function getChangesForNotification($user) {
		$changes = [];

		// Check if there is an old content object to compare against
		if (isset($this->old_content_object)) {
			// Calculate differences between current and old content objects
			$differences = ApplicationLogDetails::calculateSavedObjectDifferences($this, $this->old_content_object);
			foreach ($differences as $property => $diff) {
				// Handle changes in 'start' and 'duration' properties
				if ($property == 'start' || $property == 'duration') {

					// Determine old and new start values
					if (isset($differences['start'])) {
						$old_start = $differences['start']['old_value'];
						$new_start = $differences['start']['new_value'];
					} else {
						$old_start = $new_start = $this->getStart();
					}

					// Determine old and new duration values
					if (isset($differences['duration'])) {
						$old_duration = $differences['duration']['old_value'];
						$new_duration = $differences['duration']['new_value'];
					} else {
						$old_duration = $new_duration = $this->getDuration();
					}

					$tz_offset = $user->isUser() ? $user->getTimezone() : logged_user()->getTimezone();
					$user_id = $user->isUser() ? $user->getId() : logged_user()->getId();
					$time_format = user_config_option('time_format_use_24', null, $user_id) ? 'G:i' : 'g:i A';
					
					// Format old and new values for printing
					$old_val = format_descriptive_date($old_start, $tz_offset) . ' ' . format_time($old_start, $time_format, $tz_offset) . ' - ' . format_time($old_duration, $time_format, $tz_offset);
					$new_val = format_descriptive_date($new_start, $tz_offset) . ' ' . format_time($new_start, $time_format, $tz_offset) . ' - ' . format_time($new_duration, $time_format, $tz_offset);

					// Construct change array for 'when'
					$change = [
						'label' => lang('When'),
						'old_value' => $old_val,
						'new_value' => $new_val
					];
					$changes['when'] = $change;

				} else if ($property == 'description' || $property == 'name') {
					// Handle changes in 'description' and 'name' properties
					$change = [
						'label' => lang($property),
						'old_value' => $diff['old_value'],
						'new_value' => $diff['new_value']
					];
					$changes[$property] = $change;
				}
			}
		}

		return $changes;
	}


	/**
	 * Returns the organizer of the event
	 * @return Contact The organizer of the event
	 */
	function getOrganizer() {
		$organizer = null;
		if ($this->getOrganizerId() > 0) {
			$organizer = Contacts::instance()->findById($this->getOrganizerId());
		}
		if (!$organizer instanceof Contact) {
			$organizer = $this->getCreatedBy();
		}

		return $organizer;
	}
	
	/**
	 * Generate a unique identifier for the event.
	 * If the uid is not setted, it will be generated and the object will be saved.
	 * @param boolean $call_save If true, save the object after generate the uid.
	 * @return string The unique identifier for the event.
	 */
	function generateUid($call_save = true) {
		if ($this->getUid() != '') {
			return $this->getUid();
		}

		// Generate a unique id for the event
		$uid = uniqid('', true) . "@fengoffice.com";
		$this->setUid($uid);

		// Save the object if required
		if ($call_save) {
			$this->save();
		}

		return $uid;
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
	function validate($errors) {
		if(!$this->getObject()->validatePresenceOf('name')) $errors[] = lang('event subject required');
		if(!$this->validateMaxValueOf('description',3000)) $errors[] = lang('event description maxlength');
		if(!$this->getObject()->validateMaxValueOf('name', 100)) $errors[] = lang('event subject maxlength');
	} // validate
	
	
	function getInvitations() {
		
		$this->event_invitations = array();
		$invs = EventInvitations::instance()->findAll(array('conditions' => 'event_id='.$this->getId()));
		foreach ($invs as $inv) {
			$this->event_invitations[$inv->getContactId()] = $inv;
		}
		
		return $this->event_invitations;
	}
	function setInvitations($invitations) {
		$this->event_invitations = $invitations;
	}
	
	
	function clearInvitations() {
		$this->event_invitations = array();
		EventInvitations::instance()->delete(array ('`event_id` = ?', $this->getId()));
	}


	function getInvitation($contact_id) {
		return EventInvitations::instance()->findOne(array(
			'conditions' => array('event_id = ? AND contact_id = ?', $this->getId(), $contact_id)
		));
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
			if ($last_repeat instanceof DateTimeValue) {
				// to include the last repetition
				$last_repeat = $last_repeat->endOfDay();
			}
			
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
						
						// for all-day events use beggining of day to prevent that it goes to other day.
						if ($event->getTypeId() == 2) {
							$new_st_date = $new_st_date->beginningOfDay()->advance(logged_user()->getUserTimezoneValue() * -1, false);
						}
					}
					if ($new_due_date instanceof DateTimeValue){
						//set first day of the month
						$new_due_date->setDay(1);
						
						//date string
						$new_due_date_string = date("c", $new_due_date->getTimestamp());
						
						//go to the fixed day
						$new_due_date_fixed = new DateTime(date("c", strtotime($ordinal . $day_name ." of ". $new_due_date_string)));
						
						$new_due_date = new DateTimeValue($new_due_date_fixed->getTimestamp());
						
						// for all-day events use end of day to prevent that it goes to other day.
						if ($event->getTypeId() == 2) {
							$new_due_date = $new_due_date->endOfDay()->advance(logged_user()->getUserTimezoneValue() * -1, false);
						}
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
	
	
	
	function getArrayInfo() {
		$formatTime = user_config_option('time_format_use_24') ? 'G:i' : 'g:i A';
		$format = user_config_option('date_format').' '.$formatTime;
		
		$start_formatted = "";
		if ($this->getStart() instanceof DateTimeValue) {
			$start_formatted = $this->getTimezoneId()>0 ? format_datetime($this->getStart()) : $this->getStart()->format($format);
		}
		$duration_formatted = "";
		if ($this->getDuration() instanceof DateTimeValue) {
			$duration_formatted = $this->getTimezoneId()>0 ? format_datetime($this->getDuration()) : $this->getDuration()->format($format);
		}
		
		return array(
			'id' => $this->getId(),
			'object_id' => $this->getId(),
			'name' => $this->getObjectName(),
			'start' => $start_formatted,
			'duration' => $duration_formatted,
			'description' => $this->getDescription(),
			'type' => $this->getObjectTypeName(),
		);
	}
	
} // projectEvent

?>