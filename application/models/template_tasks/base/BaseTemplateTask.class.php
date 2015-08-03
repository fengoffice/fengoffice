<?php

/**
 * BaseTemplateTask class
 *
 * 
 */
abstract class BaseTemplateTask extends ContentDataObject {
	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'object_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		return $this->getColumnValue('object_id');
	} // getObjectId()

	/**
	 * Set value of 'object_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setObjectId($value) {
		return $this->setColumnValue('object_id', $value);
	} // setObjectId()
	
	/**
	 * Return value of 'template_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTemplateId() {
		return $this->getColumnValue('template_id');
	} // getTemplateId()
	
	/**
	 * Set value of 'template_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTemplateId($value) {
		return $this->setColumnValue('template_id', $value);
	} // setTemplateId()
	
	/**
	 * Return value of 'session_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getSessionId() {
		return $this->getColumnValue('session_id');
	} // getSessionId()
	
	/**
	 * Set value of 'session_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setSessionId($value) {
		return $this->setColumnValue('session_id', $value);
	} // setSessionId()
	

	/**
	 * Return value of 'parent_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getParentId() {
		return $this->getColumnValue('parent_id');
	} //  getParentId()

	/**
	 * Set value of 'parent_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setParentId($value) {
		return $this->setColumnValue('parent_id', $value);
	} // setparentId()
	
	/**
	 * Return value of 'parents_path' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getParentsPath() {
		return $this->getColumnValue('parents_path');
	} // getParentsPath()
	
	/**
	 * Set value of 'parents_path' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setParentsPath($value) {
		return $this->setColumnValue('parents_path', $value);
	} // setParentsPath()
	
	/**
	 * Return value of 'depth' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDepth() {
		return $this->getColumnValue('depth');
	} //  getDepth()
	
	/**
	 * Set value of 'depth' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDepth($value) {
		return $this->setColumnValue('depth', $value);
	} // setDepth()
	

	/**
	 * Return value of 'text' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getText() {
		return $this->getColumnValue('text');
	} // getText()

	/**
	 * Set value of 'text' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setText($value) {
		$value = remove_scripts($value);
		return $this->setColumnValue('text', $value);
	} // setText()

	/**
	 * Return value of 'assigned_to_contact_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAssignedToContactId() {
		return $this->getColumnValue('assigned_to_contact_id');
	} // getAssignedToContactId()

	/**
	 * Set value of 'assigned_to_contact_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAssignedToContactId($value) {
		return $this->setColumnValue('assigned_to_contact_id', $value);
	} // setAssignedToContactId()

	/**
	 * Return value of 'completed_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getCompletedOn() {
		return $this->getColumnValue('completed_on');
	} // getCompletedOn()

	/**
	 * Set value of 'completed_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setCompletedOn($value) {
		return $this->setColumnValue('completed_on', $value);
	} // setCompletedOn()

	/**
	 * Return value of 'completed_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getCompletedById() {
		return $this->getColumnValue('completed_by_id');
	} // getCompletedById()

	/**
	 * Set value of 'completed_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setCompletedById($value) {
		return $this->setColumnValue('completed_by_id', $value);
	} // setCompletedById()

	/**
	 * Return value of 'due_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getDueDate() {
		return $this->getColumnValue('due_date');
	} // getDueDate()

	/**
	 * Set value of 'due_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setDueDate($value) {
		return $this->setColumnValue('due_date', $value);
	} // setDueDate()


	/**
	 * Return value of 'start_date' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getStartDate() {
		return $this->getColumnValue('start_date');
	} // getStartDate()

	/**
	 * Set value of 'start_date' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setStartDate($value) {
		return $this->setColumnValue('start_date', $value);
	} // setStartDate()

	/**
	 * Return value of 'order' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getOrder() {
		return $this->getColumnValue('order');
	} // getOrder()

	/**
	 * Set value of 'order' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setOrder($value) {
		return $this->setColumnValue('order', $value);
	} // setOrder()

	/**
	 * Return value of 'milestone_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getMilestoneId() {
		return $this->getColumnValue('milestone_id');
	} // getMilestoneId()

	/**
	 * Set value of 'milestone_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setMilestoneId($value) {
		return $this->setColumnValue('milestone_id', $value);
	} // setMilestoneId()

	/**
	 * Return value of 'assigned_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getAssignedOn() {
		return $this->getColumnValue('assigned_on');
	} // getAssignedOn()

	/**
	 * Set value of 'assigned_on' field.
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setAssignedOn($value) {
		$this->setColumnValue('assigned_on', $value);
	} // setAssignedOn()

	/**
	 * Return value of 'assigned_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getAssignedById() {
		return $this->getColumnValue('assigned_by_id');
	} // getAssignedById()

	/**
	 * Set value of 'assigned_by_id' field.
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setAssignedById($value) {
		$this->setColumnValue('assigned_by_id', $value);
	} // setAssignedById()
	
	
	/**
	 * Return value of 'time_estimate' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTimeEstimate() {
		return $this->getColumnValue('time_estimate');
	} // getTimeEstimate()

	/**
	 * Set value of 'time_estimate' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTimeEstimate($value) {
		return $this->setColumnValue('time_estimate', $value);
	} // setTimeEstimate()
	
	/**
	 * Return value of 'priority' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getPriority() {
		return $this->getColumnValue('priority');
	} // getpriority()

	/**
	 * Set value of 'priority' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setPriority($value) {
		return $this->setColumnValue('priority', $value);
	} // setpriority()

	/**
	 * Return value of 'state' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getState() {
		return $this->getColumnValue('state');
	} // getState()

	/**
	 * Set value of 'State' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setState($value) {
		return $this->setColumnValue('state', $value);
	} // setState()

	/**
	 * Return value of 'started_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue
	 */
	function getStartedOn() {
		return $this->getColumnValue('started_on');
	} // getStartedOn()

	/**
	 * Set value of 'started_on' field
	 *
	 * @access public
	 * @param DateTimeValue $value
	 * @return boolean
	 */
	function setStartedOn($value) {
		return $this->setColumnValue('started_on', $value);
	} // setStartedOn()

	/**
	 * Return value of 'started_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getStartedById() {
		return $this->getColumnValue('started_by_id');
	} // getStartedById()

	/**
	 * Set value of 'started_by_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setStartedById($value) {
		return $this->setColumnValue('started_by_id', $value);
	} // setStartedById()

	
	/**
	 * Return value of 'from_template_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getFromTemplateId() {
		return $this->getColumnValue('from_template_id');
	} // getFromTemplateId()

	/**
	 * Set value of 'from_template_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setFromTemplateId($value) {
		return $this->setColumnValue('from_template_id', $value);
	} // setFromTemplateId()

	/**
	 * Return value of 'from_template_object_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getFromTemplateObjectId() {
		return $this->getColumnValue('from_template_object_id');
	} // getFromTemplateObjectId()
	
	/**
	 * Set value of 'from_template_object_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setFromTemplateObjectId($value) {
		return $this->setColumnValue('from_template_object_id', $value);
	} // setFromTemplateObjectId()
	
    /**
    * Return value of 'repeat_forever' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRepeatForever() {
      return $this->getColumnValue('repeat_forever');
    } //  getForever()
    
    /**
    * Set value of 'repeat_forever' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function  setRepeatForever($value) {
      return $this->setColumnValue('repeat_forever', $value);
    } //  setForever()

    
    /**
    * Return value of 'repeat_end' field
    *
    * @access public
    * @param void
    * @return Date 
    */
    function getRepeatEnd() {
      return $this->getColumnValue('repeat_end');
    } //  getRepeatEnd()
    
    /**
    * Set value of 'repeat_end' field
    *
    * @access public   
    * @param Date $value
    * @return boolean
    */
    function  setRepeatEnd($value) {
      return $this->setColumnValue('repeat_end', $value);
    } //  setRepeatEnd() 
    
    /**
    * Set value of 'repeat_num' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatNum($value) {
      return $this->setColumnValue('repeat_num', $value);
    } //  setRepeatNum() 
    
    /**
    * Return value of 'repeat_num' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatNum() {
      return $this->getColumnValue('repeat_num');
    } //  getRepeatNum()
    
    /**
    * Set value of 'repeat_d' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatD($value) {
      return $this->setColumnValue('repeat_d', $value);
    } //  setRepeatD() 
    
    /**
    * Return value of 'repeat_d' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatD() {
      return $this->getColumnValue('repeat_d');
    } //  setRepeatD()
    /**
    * Set value of 'repeat_m' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatM($value) {
      return $this->setColumnValue('repeat_m', $value);
    } //  getRepeatM() 
    
    /**
    * Return value of 'repeat_m' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatM() {
      return $this->getColumnValue('repeat_m');
    } //  getRepeatM()
    /**
    * Set value of 'repeat_y' field
    *
    * @access public   
    * @param Integer $value
    * @return boolean
    */
    function  setRepeatY($value) {
      return $this->setColumnValue('repeat_y', $value);
    } //  setRepeatY() 
    
    /**
    * Return value of 'repeat_y' field
    *
    * @access public
    * @param void
    * @return  Integer 
    */
    function getRepeatY() {
      return $this->getColumnValue('repeat_y');
    } //  getRepeatY()
    
	/**
	 * Return value of 'repeat_by' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getRepeatBy() {
		return $this->getColumnValue('repeat_by');
	} // getRepeatBy()

	/**
	 * Set value of 'repeat_by' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setRepeatBy($value) {
		return $this->setColumnValue('repeat_by', $value);
	} // setRepeatBy()

    /**
    * Return value of 'object_subtype' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getObjectSubtype() {
      return $this->getColumnValue('object_subtype');
    } // getObjectSubtype()
    
    /**
    * Set value of 'object_subtype' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectSubtype($value) {
      return $this->setColumnValue('object_subtype', $value);
    } // setObjectSubtype()
    
    
    /**
	 * Return value of 'percent_completed' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getPercentCompleted() {
		return $this->getColumnValue('percent_completed');
	} //  getPercentCompleted()

	/**
	 * Set value of 'percent_completed' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setPercentCompleted($value) {
		return $this->setColumnValue('percent_completed', $value);
	} // setPercentCompleted()
    
    
	/**
	 * Return value of 'use_due_time' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getUseDueTime() {
		return $this->getColumnValue('use_due_time');
	} // getUseDueTime()

	/**
	 * Set value of 'use_due_time' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setUseDueTime($value) {
		return $this->setColumnValue('use_due_time', $value);
	} // setUseDueTime()
	
	
	/**
	 * Return value of 'use_start_time' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getUseStartTime() {
		return $this->getColumnValue('use_start_time');
	} // getUseStartTime()

	/**
	 * Set value of 'use_start_time' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setUseStartTime($value) {
		return $this->setColumnValue('use_start_time', $value);
	} // setUseStartTime()
        
        /**
	 * Return value of 'original_task_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getOriginalTaskId() {
		return $this->getColumnValue('original_task_id');
	} // getOriginalTaskId()

	/**
	 * Set value of 'original_task_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setOriginalTaskId($value) {
		return $this->setColumnValue('original_task_id', $value);
	} // setOriginalTaskId()
        
        /**
	 * Return value of 'type_content' field
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTypeContent() {
		return $this->getColumnValue('type_content');
	} // getTypeContent()

	/**
	 * Set value of 'type_content' field
	 *
	 * @access public
	 * @param string $value
	 * @return boolean
	 */
	function setTypeContent($value) {
		return $this->setColumnValue('type_content', $value);
	} // setTypeContent()
        
        /**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return TemplateTasks
	 */
	function manager() {
		if(!($this->manager instanceof TemplateTasks)) $this->manager = TemplateTasks::instance();
		return $this->manager;
	} // manager
    
} // BaseTemplateTask


?>