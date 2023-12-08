<?php

  /**
  * 
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactConfigCategories extends BaseContactConfigCategories {
    
    /**
    * Return all categories with possibility to exclude system categories (only account owner can see them)
    *
    * @param boolean $include_system_categories
    * @return array
    */
    static function getAll($include_system_categories = false) {
      $conditions = $include_system_categories ? null : array('`is_system` = ?', false);
      return self::instance()->findAll(array(
        'conditions' => $conditions,
        'order' => '`category_order`'
      )); // array
    } // getAll
    
  } // ContactConfigCategories 

?>