<?php

  /**
  * ProjectFileRevisions, generated on Tue, 04 Jul 2006 06:46:08 +0200 by 
  * DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ProjectFileRevisions extends BaseProjectFileRevisions {
  
  	function __construct() {
		parent::__construct();
		$this->object_type_name = 'file revision';
	}
        
        function findByFile($file_id) {
                return ProjectFileRevisions::findOne(array('conditions' => array('`file_id` = ?', $file_id)));
        }
  		
  } // ProjectFileRevisions 

?>