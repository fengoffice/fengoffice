<?php

  /**
  * BaseWorkspace class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseWorkspace extends DimensionObject {
  
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
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setId() 
    
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
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Workspaces 
    */
    function manager() {
      if(!($this->manager instanceof Workspaces)) $this->manager = Workspaces::instance();
      return $this->manager;
    } // manager
  
  } // BaseWorkspace 

?>