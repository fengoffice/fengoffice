<?php

  /**
  *  ReadObjects, generated on Wed, 26 Jul 2006 11:18:14 +0200 by 
  * DataObject generation tool
  *
  * @author Nicolas Medeiros <nicolas@iugo.com.uy>
  */
  class  ReadObjects extends BaseReadObjects {
  
    /**
    * Return all unread objects ( ReadObjects) for specific object and user
    *
    * @param ProjectDataObject $object
    * @return array
    */
    static function getUnreadByObject(ApplicationDataObject $object, int $contact_id) {
      return self::findAll(array(
        'conditions' => array('(`rel_object_id` = ?) and `contact_id` = ? and is_read = 0', 
        		$object->getObjectId(), $contact_id),
        'order' => '`created_on`'
      )); // findAll
    } // getUnreadByObject
    
    /**
    * Return all read objects ( ReadObjects) for specific object and user
    *
    * @param ApplicationDataObject $object
    * @return array
    */
    static function getReadByObject(ApplicationDataObject $object, int $contact_id) {
      return self::findAll(array(
        'conditions' => array('(`rel_object_id` = ?) and `contact_id` = ? and is_read = 1', 
        		$object->getObjectId(), $contact_id),
        'order' => '`created_on`'
      )); // findAll
    } // getReadByObject
    
    
    
    /**
     * Return all read objects ( ReadObjects) for specific object and user
     *
     * @return array
     */
    static function getReadByObjectList($object_id_list, $contact_id) {
    	$idsCSV = implode(',',$object_id_list);
    	$rol = self::findAll(array(
       		'conditions' => array("`rel_object_id` in ($idsCSV) and `contact_id` = ? and is_read = 1", $contact_id)
    	)); // findAll
    	if (is_array($rol) && count($rol) > 0){
    		$result = array();
    		foreach ($rol as $ro){
    			$result[$ro->getRelObjectId()] = true;
    		}
    		return $result;
    	} else {
    		return array();
    	}
    } // getReadByObject
    
    
    /**
    * User has read object
    *
    * @param int $object_id
    * @param int $contact_id
    * @return bool
    */
    static function userHasRead( $contact_id, $object ) {
	  $perm = self::findOne(array(
        'conditions' => array('`contact_id` = ? and `rel_object_id` = ?', $contact_id, $object->getId())
      )); // findAll
      return $perm!=null && $perm->getIsRead();
    } //  userCanRead    
    
  } // clearRelationsByObject
