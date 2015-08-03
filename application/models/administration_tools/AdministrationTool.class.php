<?php

  /**
  * AdministrationTool class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class AdministrationTool extends BaseAdministrationTool {
  
    /**
    * Return application tool display name
    *
    * @param void
    * @return string
    */
    function getDisplayName() {
      return lang('administration tool name ' . $this->getName());
    } // getDisplayName
    
    /**
    * Return full application tool description
    *
    * @param void
    * @return string
    */
    function getDisplayDescription() {
      return lang('administration tool desc ' . $this->getName());
    } // getDisplayDescription
    
    /**
    * Return tool URL
    *
    * @param void
    * @return string
    */
    function getToolUrl() {
      return get_url($this->getController(), $this->getAction());
    } // getToolUrl
    
  } // AdministrationTool 

?>