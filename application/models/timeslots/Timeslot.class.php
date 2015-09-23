<?php

/**
 * Timeslot class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
class Timeslot extends BaseTimeslot {

	/**
	 * Timeslot # for specific object
	 *
	 * @var integer
	 */
	protected $timeslot_num = null;
	
	protected $assigned_user = null;
	
	protected $rel_object = null;
	
	/**
	 * Return object connected with this action
	 *
	 * @access public
	 * @param void
	 * @return ContentDataObject
	 */
	function getRelObject() {
		if(is_null($this->rel_object)) {
			$this->rel_object = Objects::findObject($this->getRelObjectId());
		}
		return $this->rel_object;
	} // getObject

	
	/**
	 * Return timeslot #
	 *
	 * @param void
	 * @return integer
	 */
	function getTimeslotNum() {
		if(is_null($this->timeslot_num)) {
			$object = $this->getRelObject();
			$this->timeslot_num = $object instanceof ContentDataObject ? $object->getTimeslotNum($this) : 0;
		} // if
		return $this->timeslot_num;
	} // getTimeslotNum

	/**
    * Return user assigned to this timeslot
    *
    * @access public
    * @param void
    * @return User
    */
    function getUser() {
      if(is_null($this->assigned_user)) {
        $this->assigned_user = Contacts::findById($this->getContactId());
      }
      return $this->assigned_user;
    }
    
    function isOpen() {
    	return $this->getEndTime() == null;
    }
	
    function getMinutes(){
    	if (!$this->getStartTime())
    		return 0;
    		
    	$endTime = $this->getEndTime();
    	if (!$endTime)
    		$endTime = $this->isPaused() ? $this->getPausedOn() : DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getStartTime()->getTimestamp(),$endTime->getTimestamp(), $this->getSubtract());
    	
    	return $timeDiff['days'] * 1440 + $timeDiff['hours'] * 60 + $timeDiff['minutes'];
    }

    function getSeconds(){
    	if (!$this->getStartTime())
    		return 0;
    		
    	$endTime = $this->getEndTime();
    	if (!$endTime)
    		if ($this->getPausedOn())
    			$endTime = $this->getPausedOn();
    		else
    			$endTime = DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getStartTime()->getTimestamp(),$endTime->getTimestamp(), $this->getSubtract());
    	
    	return $timeDiff['days'] * 86400 + $timeDiff['hours'] * 3600  + $timeDiff['minutes']* 60 + $timeDiff['seconds'];
    }

    function getSecondsSincePause(){
    	if (!$this->getPausedOn())
    		return 0;
    		
    	$endTime = DateTimeValueLib::now();
    	$timeDiff = DateTimeValueLib::get_time_difference($this->getPausedOn()->getTimestamp(),$endTime->getTimestamp());
    	
    	return $timeDiff['days'] * 86400 + $timeDiff['hours'] * 3600  + $timeDiff['minutes']* 60 + $timeDiff['seconds'];
    }
    
    function isPaused(){
    	return $this->getPausedOn() != null;
    }
    
    function pause(){
    	if ($this->isPaused())
    		throw new Error('Timeslot is already paused');
    	$dt = DateTimeValueLib::now();
		$this->setPausedOn($dt);
    }
    
    function resume(){
    	if (!$this->isPaused())
    		throw new Error('Timeslot is not paused');
    	$dt = DateTimeValueLib::now();
    	$timeToSubtract = $dt->getTimestamp() - $this->getPausedOn()->getTimestamp();
		$this->setPausedOn(null);
		$this->setSubtract($this->getSubtract() + $timeToSubtract);
    }
    
    function close($description = null){
    	if ($this->isPaused()) {
    		$this->setEndTime($this->getPausedOn());
    	} else {
    	  	$dt = DateTimeValueLib::now();
			$this->setEndTime($dt);
    	}
        	
    	//Billing
		$user = Contacts::findById(array_var($timeslot_data, 'contact_id', logged_user()->getId()));
		$billing_category_id = $user->getDefaultBillingId();
		$bc = BillingCategories::findById($billing_category_id);
		if ($bc instanceof BillingCategory) {
			$this->setBillingId($billing_category_id);
			$hourly_billing = $bc->getDefaultValue();
			$this->setHourlyBilling($hourly_billing);
			$this->setFixedBilling(number_format($hourly_billing * $hours, 2));
			$this->setIsFixedBilling(false);
		}
		
		if ($description)
			$this->setDescription($description);
    }
    
    
	/**
	 * Return user who completed this timeslot
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	function getCompletedBy() {
		return $this->getUser();
	} // getCompletedBy
    
    // ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		$object = $this->getRelObject();
		return $object instanceof ContentDataObject ? $object->getObjectUrl() : '';
	} // getViewUrl

	/**
	 * Return add timeslot URL for specific object
	 *
	 * @param ContentDataObject $object
	 * @return string
	 */
	static function getOpenUrl(ContentDataObject $object) {
		return get_url('timeslot', 'open', array(
			'object_id' => $object->getId()
		));
	}
	
	static function getAddTimespanUrl(ContentDataObject $object) {
		return get_url('timeslot', 'add_timespan', array(
			'object_id' => $object->getId()
		));
	}
	
	/**
	 * Return close timeslot URL for specific object
	 *
	 * @param ContentDataObject $object
	 * @return string
	 */
	function getCloseUrl() {
		return get_url('timeslot', 'close', array(
			'id' => $this->getId()
		));
	}
	
	/**
	 * Return pause timeslot URL for specific object
	 *
	 * @param ContentDataObject $object
	 * @return string
	 */
	function getPauseUrl() {
		return get_url('timeslot', 'pause', array(
			'id' => $this->getId()
		));
	}
	
	/**
	 * Return resume timeslot URL for specific object
	 *
	 * @param ContentDataObject $object
	 * @return string
	 */
	function getResumeUrl() {
		return get_url('timeslot', 'resume', array(
			'id' => $this->getId()
		));
	}

	/**
	 * Return edit URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('timeslot', 'edit', array('id' => $this->getId()));
	}

	/**
	 * Return delete URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('timeslot', 'delete', array('id' => $this->getId()));
	}

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		if ($this->getRelObject() instanceof ContentDataObject) {
			return can_read($user, $this->getRelObject()->getMembers(), $this->getRelObject()->getObjectTypeId());
		} else {
			return can_read($user, $this->getMembers(), $this->getObjectTypeId());
		}
	}

	/**
	 * Empty implementation of static method.
	 *
	 * Add tag permissions are done through ContentDataObject::canTimeslot() method. This
	 * will return timeslot permissions for specified object
	 *
	 * @param User $user
	 * @param array $context
	 * @return boolean
	 */
	function canAdd(Contact $user, $context, &$notAllowedMember = '') {
		return can_add($user, $context, Timeslots::instance()->getObjectTypeId(), $notAllowedMember );
	}

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return ($user->getId() == $this->getContactId() || can_manage_time($user));
	}

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		return ($user->getId() == $this->getContactId() || can_manage_time($user));
	}

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Save the object
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		$is_new = $this->isNew();
		$saved = parent::save();
		if($saved) {
			$object = $this->getRelObject();
			if($object instanceof ContentDataObject) {
				if($is_new) {
					$object->onAddTimeslot($this);
				} else {
					$object->onEditTimeslot($this);
				}
			}
		}
		return $saved;
	}

	/**
	 * Delete timeslot
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		$deleted = parent::delete();
		if($deleted) {
			$object = $this->getRelObject();
			if($object instanceof ContentDataObject) {
				$object->onDeleteTimeslot($this);
			}
		}
		return $deleted;
	}

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		$object = $this->getRelObject();
		return $object instanceof ContentDataObject ? lang('timeslot on object', $object->getObjectName()) : lang($this->getObjectTypeName());
	}
	

	/**
	 * Return view tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	}

	function getArrayInfo($return_billing = false) {
		$task_name = '';
		
		$user = Contacts::findById($this->getContactId());
		if ($user instanceof Contact) {
			$displayname = $user->getObjectName();
		} else {
			$displayname = lang("n/a");
		}
		
		$general_info = $this->getObject()->getArrayInfo();
		
		$result = array(
			'id' => $this->getId(),
			'date' => $this->getStartTime()->getTimestamp(),
			'time' => $this->getSeconds(),
			'mids' => $this->getMemberIds(),
			'uid' => $this->getContactId(),
			'uname' => $displayname,
			'lastupdated' => $general_info['dateUpdated'],
			'lastupdatedby' => $general_info['updatedBy'],
			'memPath' => json_encode($this->getMembersIdsToDisplayPath()),
			'otid' => Timeslots::instance()->getObjectTypeId(),
		);
		if ($return_billing) {
			$result['hourlybilling'] = $this->getHourlyBilling();
			$result['totalbilling'] = $this->getFixedBilling();
		}
		
		if ($this->getDescription() != '')
			$result['desc'] = $this->getDescription();
			
		if ($task_name != '')
			$result['tn'] = $task_name;
		
		return $result;
	}

	
	
	
	/**
	 * Returns an array with the ids of the members that this object belongs to
	 *
	 */
	function getMemberIds() {
		
		if (is_null($this->memberIds)) {
			 $this->memberIds = ObjectMembers::getMemberIdsByObject($this->getRelObjectId() > 0 ? $this->getRelObjectId() : $this->getId());
		}
		return $this->memberIds ;
		
		//return ObjectMembers::getMemberIdsByObject($this->getId());
	}
	
	
	/**
	 * Returns an array with the members that this object belongs to
	 *
	 */
	function getMembers() {
		if ( is_null($this->members) ) {
			$this->members =  ObjectMembers::getMembersByObject($this->getRelObjectId() > 0 ? $this->getRelObjectId() : $this->getId());
		}
		return $this->members ;
	}
} // Timeslot

?>