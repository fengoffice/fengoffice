<?php

  /**
  * ImType class
  * Generated on Wed, 22 Mar 2006 15:35:34 +0100 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ImType extends BaseImType {
  
    /**
    * Return icon URL
    *
    * @access public
    * @param void
    * @return string
    */
    function getIconUrl() {
      return get_image_url('im/' . $this->getIcon());
    } // getIconUrl
    
  } // ImType 

?>