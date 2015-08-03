<?php

  abstract class BaseSharingTableFlag extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------

    function getId() {
      return $this->getColumnValue('id');
    }
    
    function setId($value) {
      return $this->setColumnValue('id', $value);
    } 
       
    
    function getPermissionGroupId() {
      return $this->getColumnValue('permission_group_id');
    }
    
    function setPermissionGroupId($value) {
      return $this->setColumnValue('permission_group_id', $value);
    }
    
    
  	function getMemberId() {
      return $this->getColumnValue('member_id');
    }
    
    function setMemberId($value) {
      return $this->setColumnValue('member_id', $value);
    }
    
    
  	function getExecutionDate() {
      return $this->getColumnValue('execution_date');
    }
    
    function setExecutionDate($value) {
      return $this->setColumnValue('execution_date', $value);
    }
    
    
  	function getPermissionString() {
      return $this->getColumnValue('permission_string');
    }
    
    function setPermissionString($value) {
      return $this->setColumnValue('permission_string', $value);
    }
    
    
  	function getCreatedById() {
      return $this->getColumnValue('created_by_id');
    }
    
    function setCreatedById($value) {
      return $this->setColumnValue('created_by_id', $value);
    }
    
    function getObjectId() {
      return $this->getColumnValue('object_id');
    }
    
    function setObjectId($value) {
    	return $this->setColumnValue('object_id', $value);
    }
    
  
    function manager() {
      if(!($this->manager instanceof SharingTableFlags)) $this->manager = SharingTableFlags::instance();
      return $this->manager;
    }
  
  }
