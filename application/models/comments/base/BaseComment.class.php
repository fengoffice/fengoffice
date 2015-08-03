<?php

  /**
  * BaseComment class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseComment extends ContentDataObject {
  
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
    * Set value of 'object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setObjectId($value) {
      return $this->setColumnValue('object_id', $value);
    } // setId() 
    
    
    /**
    * Return value of 'rel_object_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRelObjectId() {
      return $this->getColumnValue('rel_object_id');
    } // getRelObjectId()
    
    /**
    * Set value of 'rel_object_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRelObjectId($value) {
      return $this->setColumnValue('rel_object_id', $value);
    } // setRelObjectId() 
    
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
    * Return value of 'author_name' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAuthorName() {
      return $this->getColumnValue('author_name');
    } // getAuthorName()
    
    /**
    * Set value of 'author_name' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAuthorName($value) {
      return $this->setColumnValue('author_name', $value);
    } // setAuthorName() 
    
    /**
    * Return value of 'author_email' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAuthorEmail() {
      return $this->getColumnValue('author_email');
    } // getAuthorEmail()
    
    /**
    * Set value of 'author_email' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAuthorEmail($value) {
      return $this->setColumnValue('author_email', $value);
    } // setAuthorEmail() 
    
    /**
    * Return value of 'author_homepage' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getAuthorHomepage() {
      return $this->getColumnValue('author_homepage');
    } // getAuthorHomepage()
    
    /**
    * Set value of 'author_homepage' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setAuthorHomepage($value) {
      return $this->setColumnValue('author_homepage', $value);
    } // setAuthorHomepage() 
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return Comments 
    */
    function manager() {
      if(!($this->manager instanceof Comments)) $this->manager = Comments::instance();
      return $this->manager;
    } // manager
  
  } // BaseComment 

?>