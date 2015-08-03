<?php

  /**
  * BaseProjectMilestone class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectMilestone extends ContentDataObject {
  
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
    * Return value of 'description' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getDescription() {
      return $this->getColumnValue('description');
    } // getDescription()
    
    /**
    * Set value of 'description' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setDescription($value) {
      return $this->setColumnValue('description', $value);
    } // setDescription() 
    
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
    
       
    /** Return value of 'is_urgent' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsUrgent() {
      return $this->getColumnValue('is_urgent');
    } // getIsUrgent()
    
    /**
    * Set value of 'is_urgent' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsUrgent($value) {
      return $this->setColumnValue('is_urgent', $value);
    } // setIsUrgent() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectMilestones 
    */
    function manager() {
      if(!($this->manager instanceof ProjectMilestones)) $this->manager = ProjectMilestones::instance();
      return $this->manager;
    } // manager
  
 } // BaseProjectMilestone 

?>