<?php

  /**
  * ContactConfigOptionValues
  *  
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactConfigOptionValues extends BaseContactConfigOptionValues {
  	
  	/**
    * Returns true if any option values are found for the user
    *
    * @param ConfigCategory $category
    * @param boolean $include_system_options Include system options in the result array
    * @return array
    */
    static function hasOptionValues(User $user, $category_name = null) {
    	$sql = "SELECT count(*) as c from " . TABLE_PREFIX . "user_ws_config_option_values val, " . TABLE_PREFIX . "user_ws_config_options opt WHERE val.option_id = opt.id and opt.is_system = 0 and val.user_id = " . $user->getId();
    	if ($category_name)
    		$sql .= " AND opt.category_name = '" . $category_name . "'";
    	
    	$ret = 0;
		$res = DB::execute($sql);
    	$rows=$res->fetchAll();
		return $rows[0]["c"] > 0;
    } // getOptionsByCategory
    
    function getFilterActivityMember($id,$member) {
            return ContactConfigOptionValues::findOne(array('conditions' => array('`option_id` = ? AND `member_id` = ? AND `contact_id` = ?', $id, $member, logged_user()->getId())));
    }
    
    function getFilterActivityDelete($id) {
            return DB::execute('DELETE FROM `' . TABLE_PREFIX . 'contact_config_option_values` WHERE `option_id` = ? AND `contact_id` = ?',$id, logged_user()->getId());
    }
    
//    /**
//    * Return all options in specific category
//    *
//    * @param ConfigCategory $category
//    * @param boolean $include_system_options Include system options in the result array
//    * @return array
//    */
//    static function getOptionsByCategory(ConfigCategory $category, $include_system_options = false) {
//      $conditions = $include_system_options ? 
//        array('`category_name` = ?', $category->getName()) : 
//        array('`category_name` = ? AND `is_system` = ?', $category->getName(), false);
//        
//      return self::findAll(array(
//        'conditions' => $conditions,
//        'order' => '`option_order`'
//      )); // findAll
//    } // getOptionsByCategory
//    
//    /**
//    * Return the number of config options in specific category
//    *
//    * @param ConfigCategory $category
//    * @param boolean $include_system_options
//    * @return integer
//    */
//    static function countOptionsByCategory(ConfigCategory $category, $include_system_options = false) {
//      $conditions = $include_system_options ? 
//        array('`category_name` = ?', $category->getName()) : 
//        array('`category_name` = ? AND `is_system` = ?', $category->getName(), false);
//        
//      return self::count($conditions);
//    } // countOptionsByCategory
//    
//    /**
//    * Return value of specific option
//    *
//    * @access public
//    * @param string $name
//    * @param mixed $default Default value that is returned in case of any error
//    * @return null
//    */
//    static function getOptionValue($name, $default = null) {      
//      $option = self::getByName($name);
//      return $option instanceof  ContactConfigOptionValue ? $option->getValue() : $default;
//    } // getOptionValue
//  
//    /**
//    * Return config option by name
//    *
//    * @access public
//    * @param string $name
//    * @return ConfigOption
//    */
//    static function getByName($name) {
//      return self::findOne(array(
//        'conditions' => array('`name` = ?', $name)
//      )); // if
//    } // getByName
    
  } // ContactConfigOptionValues 

?>