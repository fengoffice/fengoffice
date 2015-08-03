<?php

  /**
  * ConfigCategories, generated on Wed, 02 Aug 2006 21:22:22 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ConfigCategories extends BaseConfigCategories {
    
    /**
    * Return all categories with possibility to exclude system categories (only account owner can see them)
    *
    * @param boolean $include_system_categories
    * @return array
    */
    function getAll($include_system_categories = false) {
      $conditions = $include_system_categories ? null : array('`is_system` = ?', false);
      return self::findAll(array(
        'conditions' => $conditions,
        'order' => '`category_order`'
      )); // array
    } // getAll
    
    
    static function getOptionsFromCategory($category_name) {
    	$rows = DB::executeAll("SELECT `name` FROM `".TABLE_PREFIX."config_options` WHERE `category_name` = '$category_name'");
    	$result = array();
    	if (is_array($rows)) {
    		foreach ($rows as $row) {
    			$result[] = $row['name'];
    		}
    	}
    	return $result;
    }
    
  } // ConfigCategories 

?>