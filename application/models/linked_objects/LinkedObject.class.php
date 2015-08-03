<?php

  /**
  *  LinkedObject class
  * Generated on Wed, 26 Jul 2006 11:18:14 +0200 by DataObject generation tool
  *
  * @author Marcos Saiz <marcos.saiz@gmail.com>
  */
  class  LinkedObject extends BaseLinkedObject {
  
   
    /**
    * Return object connected with this action
    *
    * @access public
    * @param void
    * @return ProjectDataObject
    */
    function getObject1() {
      return Objects::findObject($this->getRelObjectId());
    } // getObject
    
    function getObject2() {
      return Objects::findObject($this->getObjectId());
    } // getObject
    
    /**
    * Return object connected with this action, that is not equal to the one received
    *
    * @access public
    * @param  ProjectDataObject $object
    * @return ProjectDataObject
    */
    function getOtherObject($object) {
      if (($object->getObjectId()!= $this->getObjectId()) ) {
      		return Objects::findObject($this->getObjectId());
      } else {
      		return Objects::findObject($this->getRelObjectId());
      }
    } // getObject
    
  } //  LinkedObjects 

?>