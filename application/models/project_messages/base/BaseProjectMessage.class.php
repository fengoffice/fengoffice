<?php

  /**
  * BaseProjectMessage class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectMessage extends ContentDataObject {
  
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
      return $this->setColumnValue('text', $value);
    } // setText() 
    
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
    * @return ProjectMessages 
    */
    function manager() {
      if(!($this->manager instanceof ProjectMessages)) $this->manager = ProjectMessages::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectMessage 

?>