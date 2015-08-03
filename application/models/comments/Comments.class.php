<?php

  /**
  * Comments, generated on Wed, 19 Jul 2006 22:17:32 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Comments extends BaseComments {
    
	function __construct() {
		parent::__construct();
		$this->object_type_name = 'comment';
	}
  	
    /**
    * Return object comments
    *
    * @param ContentDataObject $object
    * @param boolean $exclude_private Exclude private comments
    * @return array
    */
	static function getCommentsByObject(ContentDataObject $object, $include_trashed = false) {
		$trashed_condition = $include_trashed ? "" : " AND `trashed_on`=0";
		return self::findAll(array(
			'conditions' => array('`rel_object_id` = ?'. $trashed_condition, $object->getObjectId()),
			'order' => '`created_on`'
		));
    } // getCommentsByObject
    
    /**
    * Return object comments for objects sharing the same object type
    *
    * @param ContentDataObject $object
    * @param boolean $exclude_private Exclude private comments
    * @return array
    */
    static function getCommentsByObjectIds($object_ids, $include_trashed = false) {
      $trashed_condition = $include_trashed ? "" : " AND `trashed_on`=0";
     
      return self::findAll(array(
          'conditions' => array('`rel_object_id` IN(' . $object_ids . ')'.$trashed_condition),
          'order' => '`created_on`'
      )); // array      
    } // getCommentsByObject
    
    /**
    * Return number of comments for specific object
    *
    * @param ContentDataObject $object
    * @param boolean $exclude_private Exclude private comments
    * @return integer
    */
    static function countCommentsByObject(ContentDataObject $object, $exclude_private = false) {
        return self::count(array('`rel_object_id` = ? ', $object->getObjectId()));
    } // countCommentsByObject
  
    /**
    * Drop comments by object
    *
    * @param ContentDataObject
    * @return boolean
    */
    static function dropCommentsByObject(ContentDataObject $object) {
      return Comments::delete(array('`rel_object_id` = ?', $object->getObjectId()));
    } // dropCommentsByObject
    
} // Comments 

?>