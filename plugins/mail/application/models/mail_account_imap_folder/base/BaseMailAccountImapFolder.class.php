<?php

  /**
  * BaseMailAccount class
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  abstract class BaseMailAccountImapFolder extends DataObject {
  
    // -------------------------------------------------------
    //  Access methods
    // -------------------------------------------------------

    /**
    * Return value of 'account_id' field
    *
    * @access public
    * @param void
    * @return integer 
    */
    function getAccountId() {
      return $this->getColumnValue('account_id');
    } // getAccountId()
    
    /**
    * Set value of 'account_id' field
    *
    * @access public   
    * @param integer $value
    * @return boolean
    */
    function setAccountId($value) {
      return $this->setColumnValue('account_id', $value);
    } // setAccountId() 
    
    /**
     * Return value of 'folder_name' field
     *
     * @access public
     * @param void
     * @return string
     */
    function getFolderName() {
    	return $this->getColumnValue('folder_name');
    } // getFolderName()

    /**
     * Set value of 'folder_name' field
     *
     * @access public
     * @param string $value
     * @return boolean
     */
    function setFolderName($value) {
    	return $this->setColumnValue('folder_name', $value);
    } // setFolderName()
    
       /**
    * Return value of 'check_folder' field
    *
    * @access public
    * @param void
    * @return boolean 
    */
    function getCheckFolder() {
      return $this->getColumnValue('check_folder');
    } // getCheckFolder()
    
    /**
    * Set value of 'check_folder' field
    *
    * @access public   
    * @param boolean $value
    * @return boolean
    */
    function setCheckFolder($value) {
      return $this->setColumnValue('check_folder', $value);
    } // setCheckFolder() 

    /**
    * Return manager instance
    *
    * @access protected
    * @param void
    * @return MailAccountImapFolders
    */
    function manager() {
      if(!($this->manager instanceof MailAccountImapFolders)) $this->manager = MailAccountImapFolders::instance();
      return $this->manager;
    } // manager
  
  } // BaseMailAccountImapFolder

?>