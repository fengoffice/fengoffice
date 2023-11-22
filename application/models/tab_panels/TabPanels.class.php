<?php

  /**
  * TabPanels
  *
  * @author Alvaro Torterola <alvaro.torterola@fengoffice.com>
  */
  class TabPanels extends BaseTabPanels {
	
  	static function getEnabled() {
  		return self::instance()->findAll(array("condtitions" => "`enabled` = 1"));
  	}
  } // TabPanels 

?>