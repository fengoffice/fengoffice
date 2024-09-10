<?php

  /**
  * FileTypes, generated on Tue, 28 Mar 2006 00:25:04 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class FileTypes extends BaseFileTypes {
    
    /**
    * Return file type object by extension
    *
    * @access public
    * @param void
    * @return FileType
    */
    static function getByExtension($extension) {
      return self::instance()->findOne(array(
        'conditions' => '`extension` = ' . DB::escape(strtolower($extension))
      )); // findOne
    } // getByExtension
  
  } // FileTypes 

?>