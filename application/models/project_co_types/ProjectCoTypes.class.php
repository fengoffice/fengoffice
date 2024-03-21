<?php

  /**
  * ProjectCoTypes, generated on Tue, 04 Jul 2006 06:46:08 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectCoTypes extends BaseProjectCoTypes {
  
  	static function getObjectTypesByManager($manager, $order = "name") {
  		return self::instance()->findAll(array(
  			'conditions' => "`object_manager` = '". $manager . "'", 
  			'order' => "`$order` ASC"));
  	}
  	
  } // ProjectCoTypes 

?>