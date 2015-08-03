<?php

  /**
  * BaseProjectWebpage class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseProjectWebpage extends ContentDataObject {
  
  	 
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
    * Return value of 'url' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getUrl() {
      return $this->getColumnValue('url');
    } // getUrl()
    
    /**
    * Set value of 'url' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setUrl($value) {
      return $this->setColumnValue('url', $value);
    } // setUrl()
    
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
    * @return ProjectWebpages
    */
    function manager() {
      if(!($this->manager instanceof ProjectWebpages)) $this->manager = ProjectWebpages::instance();
      return $this->manager;
    } // manager

} // BaseProjectWebpage 

?>