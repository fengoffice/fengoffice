<?php

  /**
  * AdministrationTools class
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class AdministrationTools extends BaseAdministrationTools {
  
    /**
    * Return all available tools
    *
    * @param void
    * @return array
    */
    static function getAll() {
      return self::instance()->findAll(array(
        'order' => '`order`'
      )); // findAll
    } // getAll
    
    /**
    * Return tool by name
    *
    * @param string $name
    * @return AdministrationTool
    */
    static function getByName($name) {
      return self::instance()->findOne(array(
        'conditions' => array('`name` = ?', $name),
      )); // findOne
    } // getByName
    
  } // AdministrationTools 

?>