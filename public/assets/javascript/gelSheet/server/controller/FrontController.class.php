<?php
class FrontController {
	/**
	 * Security provider 
	 * @var SecurityController
	 */
	var $security = null ;

	
	
	function __construct() {
		//TODO Agarrar de configuracion o algun lado quien es el SecurityProvider..
		$this->security = new OgSecurityController()  ; 
	}
	
	
}

?>