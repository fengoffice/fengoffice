<?php

  /**
  * BaseTabPanelPermission class
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  abstract class BaseTabPanelPermission extends DataObject {
  
  	  
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
    * Return value of 'tab_panel_id' field
    *
    * @access public
    * @param void
    * @return string 
    */
    function getTabPanelId() {
      return $this->getColumnValue('tab_panel_id');
    } // getTabPanelId()
    
    /**
    * Set value of 'tab_panel_id' field
    *
    * @access public   
    * @param string $value
    * @return boolean
    */
    function setTabPanelId($value) {
      return $this->setColumnValue('tab_panel_id', $value);
    } // setTabPanelId()
    
    
         
    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return TabPanelPermissions 
    */
    function manager() {
      if(!($this->manager instanceof TabPanelPermissions)) $this->manager = TabPanelPermissions::instance();
      return $this->manager;
    } // manager
  
  } // BaseTabPanelPermission 

?>