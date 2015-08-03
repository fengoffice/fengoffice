<?php
/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */
	class User {
		
		/** this is the user class for the spreadsheet*/
		private $userId;
		private $userName;
	
		/*contruct functions*/
		
		public function __construct($userId= null){
			
			if ($userId== null){
				
				loadUser();
				
			}
			else{
				
				$this->userId= $userId;
				$this->userName= "default";
					
			}
			
			
		} 
		
		public function __destruct(){}
		
		private function loadUser(){			
			
			if (isset($_REQUEST['UserId'])){
				
					$this->userId= $_REQUEST['UserId'];				
			}			
		}
		
		
		/*getters functions*/
		
		public function getId (){
			
			return $this->userId;
			
		}
		
		public function getName (){
			
			
			return $this->userName;
			
			
		}
		
		/*setters functions*/
		
		public function setId ($userId){
			
			
			$this->userId= $userId;
			
		}
	
		public function setName ($userName){
			
			$this->userName= $userName;
			
		}
		
		
	}

?>