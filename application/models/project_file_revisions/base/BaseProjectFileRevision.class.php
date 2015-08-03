<?php

  /**
  * BaseProjectFileRevision class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class BaseProjectFileRevision extends ContentDataObject {
  
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
    * Return value of 'file_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getFileId() {
      return $this->getColumnValue('file_id');
    } // getFileId()
    
    /**
    * Set value of 'file_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setFileId($value) {
      return $this->setColumnValue('file_id', $value);
    } // setFileId() 
    
    /**
    * Return value of 'file_type_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getFileTypeId() {
      return $this->getColumnValue('file_type_id');
    } // getFileTypeId()
    
    /**
    * Set value of 'file_type_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setFileTypeId($value) {
      return $this->setColumnValue('file_type_id', $value);
    } // setFileTypeId() 
    
    /**
    * Return value of 'repository_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getRepositoryId() {
      return $this->getColumnValue('repository_id');
    } // getRepositoryId()
    
    /**
    * Set value of 'repository_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setRepositoryId($value) {
      return $this->setColumnValue('repository_id', $value);
    } // setRepositoryId() 
    
    /**
    * Return value of 'thumb_filename' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getThumbFilename() {
      return $this->getColumnValue('thumb_filename');
    } // getThumbFilename()
    
    /**
    * Set value of 'thumb_filename' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setThumbFilename($value) {
      return $this->setColumnValue('thumb_filename', $value);
    } // setThumbFilename()
    
    /**
    * Return value of 'revision_number' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getRevisionNumber() {
      return $this->getColumnValue('revision_number');
    } // getRevisionNumber()
    
    /**
    * Set value of 'revision_number' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setRevisionNumber($value) {
      return $this->setColumnValue('revision_number', $value);
    } // setRevisionNumber() 
    
    /**
    * Return value of 'comment' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getComment() {
      return $this->getColumnValue('comment');
    } // getComment()
    
    /**
    * Set value of 'comment' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setComment($value) {
      return $this->setColumnValue('comment', $value);
    } // setComment() 
    
    /**
    * Return value of 'type_string' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getTypeString() {
      return $this->getColumnValue('type_string');
    } // getTypeString()
    
    /**
    * Set value of 'type_string' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTypeString($value) {
      return $this->setColumnValue('type_string', $value);
    } // setTypeString() 
    
    /**
    * Return value of 'filesize' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getFilesize() {
      return $this->getColumnValue('filesize');
    } // getFilesize()
    
    /**
    * Set value of 'filesize' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setFilesize($value) {
      return $this->setColumnValue('filesize', $value);
    } // setFilesize() 
    
    /**
    * Return value of 'hash' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getHash() {
      return $this->getColumnValue('hash');
    } // getHash()
    
    /**
    * Set value of 'hash' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setHash($value) {
      return $this->setColumnValue('hash', $value);
    } // setHash() 

    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return ProjectFileRevisions 
    */
    function manager() {
      if(!($this->manager instanceof ProjectFileRevisions)) $this->manager = ProjectFileRevisions::instance();
      return $this->manager;
    } // manager
  
  } // BaseProjectFileRevision 

?>