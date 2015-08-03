<?php

  /**
  * BaseProjectTaskDependency class
  * Written on Tue, 27 Oct 2007 16:53:08 -0300
  *
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  abstract class BaseProjectTaskDependency extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'previous_task_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getPreviousTaskId() {
      return $this->getColumnValue('previous_task_id');
    } // getPreviousTaskId()
    
    /**
    * Set value of 'previous_task_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPreviousTaskId($value) {
      return $this->setColumnValue('previous_task_id', $value);
    } // setPreviousTaskId() 
    
  
    /**
    * Return value of 'task_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getTaskId() {
      return $this->getColumnValue('task_id');
    } // getTaskId()
    
    /**
    * Set value of 'task_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setTaskId($value) {
      return $this->setColumnValue('task_id', $value);
    } // setTaskId() 
    

  
    /**
    * Set value of 'created_on' field
    *
    * @access public   
    * @param DateTimeValue $value
    * @return boolean
    */
    function setCreatedOn($value) {
      return $this->setColumnValue('created_on', $value);
    } // setCreatedOn() 
    
    /**
    * Return value of 'created_by_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    } // getCreatedById()
    
        
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectTaskDependency 
    */
    function manager() {
      if(!($this->manager instanceof ProjectTaskDependencies )) $this->manager =  ProjectTaskDependencies::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectTaskDependency

?>