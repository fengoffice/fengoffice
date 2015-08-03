<?php

class LanguageController extends FrontController  {
	var $lang = "" ;//; 
	
	function __construct() {
		global $cnf ;
		$this->lang = $cnf['application']['language']  ;
	}
	
	function getLanguages ($current_lang = null) {
		
		if ($current_lang) {
			$this->lang = $current_lang ;
		}
		include_once (GS_ROOT."/util/lang/languages.js.php");
			
	}
}

?>