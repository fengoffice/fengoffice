<?php

  /**
  * BaseSystemPermission class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  abstract class BaseSystemPermission extends DataObject {
  
  	  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------
  
    /**
    * Return value of 'permission_group_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getPermissionGroupId() {
      return $this->getColumnValue('permission_group_id');
    } // getPermissionGroupId()
    
    
    /**
    * Set value of 'permission_group_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setPermissionGroupId($value) {
      return $this->setColumnValue('permission_group_id', $value);
    } // setPermissionGroupId() 
    
    
    
    /**
    * Return value of 'can_manage_configuration' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanManageConfiguration() {
      return $this->getColumnValue('can_manage_configuration');
    } // getCanManageConfiguration()
    
    
    /**
    * Set value of 'can_manage_configuration' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanManageConfiguration($value) {
      return $this->setColumnValue('can_manage_configuration', $value);
    } // setCanManageConfiguration() 
    
    
	/**
    * Return value of 'can_manage_security' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanManageSecurity() {
      return $this->getColumnValue('can_manage_security');
    } // getCanManageSecurity()
    
    
    /**
    * Set value of 'can_manage_security' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanManageSecurity($value) {
      return $this->setColumnValue('can_manage_security', $value);
    } // setCanManageSecurity() 
   
    
    /**
    * Return value of 'can_manage_templates' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanManageTemplates() {
      return $this->getColumnValue('can_manage_templates');
    } // getCanManageTemplates()
    
    
    /**
    * Set value of 'can_manage_templates' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanManageTemplates($value) {
      return $this->setColumnValue('can_manage_templates', $value);
    } // setCanManageTemplates() 
    
    
    
    /**
    * Return value of 'can_manage_time' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanManageTime() {
      return $this->getColumnValue('can_manage_time');
    } // getCanManageTime()
    
    
    /**
    * Set value of 'can_manage_time' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanManageTime($value) {
      return $this->setColumnValue('can_manage_time', $value);
    } // setCanManageTime()
    
    
    /**
    * Return value of 'can_add_mail_accounts' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getCanAddMailAccounts() {
      return $this->getColumnValue('can_add_mail_accounts');
    } // getCanAddMailAccounts()
    
    
    /**
    * Set value of 'can_add_mail_accounts' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setCanAddMailAccounts($value) {
      return $this->setColumnValue('can_add_mail_accounts', $value);
    } // setCanAddMailAccounts()    
    
    
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return SystemPermissions 
    */
    function manager() {
      if(!($this->manager instanceof SystemPermissions)) $this->manager = SystemPermissions::instance();
      return $this->manager;
    } // manager
    
    function getCanManageDimensions(){
    	return $this->getColumnValue('can_manage_dimensions');
    }
    
    function setCanManageDimensions($value){
		return $this->setColumnValue('can_manage_dimensions', $value);
    }
      
    function getCanManageDimensionMembers(){
    	return $this->getColumnValue('can_manage_dimension_members');
    }
    
    function setCanManageDimensionMembers($value){
		return $this->setColumnValue('can_manage_dimension_members', $value);
    }
      
    function getCanManageTasks(){
    	return $this->getColumnValue('can_manage_tasks');
    }
    
    function setCanManageTasks($value){
		return $this->setColumnValue('can_manage_tasks', $value);
    }
    
    function getCanTasksAssignee(){
    	return $this->getColumnValue('can_task_assignee');
    }
    
    function setCanTasksAssignee($value){
		return $this->setColumnValue('can_task_assignee', $value);
    }
      
    function getCanManageBilling(){
    	return $this->getColumnValue('can_manage_billing');
    }
    
    function setCanManageBilling($value){
		return $this->setColumnValue('can_manage_billing', $value);
    }
      
    function getCanViewBilling(){
    	return $this->getColumnValue('can_view_billing');
    }
    
    function setCanViewBilling($value){
		return $this->setColumnValue('can_view_billing', $value);
    }    
    
    
    function getUpdateOtherUsersInvitations(){
    	return $this->getColumnValue('can_update_other_users_invitations');
    }
    
    function setUpdateOtherUsersInvitations($value){
    	return $this->setColumnValue('can_update_other_users_invitations', $value);
    }
    
    function getCanLinkObjects(){
    	return $this->getColumnValue('can_link_objects');
    }
    
    function setCanLinkObjects($value){
    	return $this->setColumnValue('can_link_objects', $value);
    }
    
    function getSettedPermissions(){
		$columns=$this->getColumns();
		$permissions=array();
		foreach ($columns as $column){
			if($this->getColumnValue($column)==1){
				$permissions[]=$column;
			}
		}
	  	return $permissions;
  	}
  	function getNotSettedPermissions(){
		$columns=$this->getColumns();
		$permissions=array();
		foreach ($columns as $column){
			if($this->getColumnValue($column)==0){
				$permissions[]=$column;
			}
		}
	  	return $permissions;
  	}
  } // BaseSystemPermission 

?>