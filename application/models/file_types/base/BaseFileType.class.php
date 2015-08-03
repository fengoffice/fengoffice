<?php

  /**
  * BaseFileType class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseFileType extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getId() {
      return $this->getColumnValue('id');
    } // getId()
    
    /**
    * Set value of 'id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } // setId() 
    
    /**
    * Return value of 'extension' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getExtension() {
      return $this->getColumnValue('extension');
    } // getExtension()
    
    /**
    * Set value of 'extension' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setExtension($value) {
      return $this->setColumnValue('extension', $value);
    } // setExtension() 
    
    /**
    * Return value of 'icon' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getIcon() {
      return $this->getColumnValue('icon');
    } // getIcon()
    
    /**
    * Set value of 'icon' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setIcon($value) {
      return $this->setColumnValue('icon', $value);
    } // setIcon() 
    
    /**
    * Return value of 'is_searchable' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsSearchable() {
      return $this->getColumnValue('is_searchable');
    } // getIsSearchable()
    
    /**
    * Set value of 'is_searchable' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsSearchable($value) {
      return $this->setColumnValue('is_searchable', $value);
    } // setIsSearchable() 
    
    /**
    * Return value of 'is_image' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsImage() {
      return $this->getColumnValue('is_image');
    } // getIsImage()
    
    /**
    * Set value of 'is_image' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsImage($value) {
      return $this->setColumnValue('is_image', $value);
    } // setIsImage() 
    
    /**
    * Return value of 'is_allow' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getIsAllow() {
      return $this->getColumnValue('is_allow');
    } // getIsAllow()
    
    /**
    * Set value of 'is_allow' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setIsAllow($value) {
      return $this->setColumnValue('is_allow', $value);
    } // setIsAllow() 
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return FileTypes 
    */
    function manager() {
      if(!($this->manager instanceof FileTypes)) $this->manager = FileTypes::instance();
      return $this->manager;
    } // manager
  
  } // BaseFileType 

?>