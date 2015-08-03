<?php

  /**
  * WebpageTypes class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class WebpageTypes extends BaseWebpageTypes {
    	
  	static private  $personal_type_id = null;
  	static private  $work_type_id = null;
  	static private  $other_type_id = null;
  	
  	
  	static function getWebpageTypeId($type){
  		switch($type){
  			case 'personal': if (is_null(self::$personal_type_id)){
  							self::$personal_type_id = self::getTypeId($type);
  						 }
  						 return self::$personal_type_id;
  						 break;
  			case 'work': if (is_null(self::$work_type_id)){
  							self::$work_type_id = self::getTypeId($type);
  						 }
  						 return self::$work_type_id;
  						 break;
  			case 'other': if (is_null(self::$other_type_id)){
  							self::$other_type_id = self::getTypeId($type);
  						 }
  						 return self::$other_type_id;
  						 break;
  			default: return self::getTypeId($type);
  		}
  	}
  	
  	
  	static function getTypeId($type){
  		$webpage_type = WebpageTypes::findOne(array('conditions' => array("`name` = ?",$type)));
  		if (!is_null($webpage_type)) return $webpage_type->getId();
  		else return null;
    }


    static function getAllWebpageTypesInfo() {
    	$types = WebpageTypes::findAll();
    	$result = array();
    	foreach ($types as $type) {
    		$result[] = array('id' => $type->getId(), 'code' => $type->getName(), 'name' => lang($type->getName()));
    	}
    
    	return $result;
    }
  } // WebpageTypes 

?>