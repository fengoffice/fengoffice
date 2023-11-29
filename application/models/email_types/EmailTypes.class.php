<?php

  /**
  * EmailTypes class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class EmailTypes extends BaseEmailTypes {
  
  	static private  $user_type_id = null;
  	static private  $personal_type_id = null;
  	static private  $work_type_id = null;
  	static private  $other_type_id = null;
  	
  	
  	static function getEmailTypeId($type){
  		switch($type){
  			case 'user': if (is_null(self::$user_type_id)){
  							self::$user_type_id = self::getTypeId($type);
  						 }
  						 return self::$user_type_id;
  						 break;
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
  		$email_type = EmailTypes::instance()->findOne(array('conditions' => array("`name` = ?",$type)));
  		if (!is_null($email_type)) return $email_type->getId();
  		else return null;
    }
  	
    
    static function getAllEmailTypesInfo($use_id_as_key = false) {
    	$types = EmailTypes::instance()->findAll();
    	$result = array();
    	foreach ($types as $type) {
    	    if($use_id_as_key){
                $result[$type->getId()] = array('id' => $type->getId(), 'code' => $type->getName(), 'name' => lang($type->getName()));
            }else{
    		    $result[] = array('id' => $type->getId(), 'code' => $type->getName(), 'name' => lang($type->getName()));
            }
    	}
    
    	return $result;
    }
    
  } // EmailTypes 

?>