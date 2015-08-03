<?php
OgHelper::includeBasic(); 

class OgSecurityController extends SecurityController {
	/**
	 * Opengoo Model
	 * @var CompanyWebsite
	 */
	var $cw = null;
	
	/**
	 * Opengoo User Model
	 * @var User
	 */
	var $loggedUser = null ;
	
	public function __construct() {
		$this->cw = OgHelper::getCompanyWebsite() ;
		$this->loggedUser = $this->cw->getLoggedUser() ;
	}	
	
	public function isLoggedIn() { 
		return ( $this->getLoggedUser() != null ); 
	}

	public function getLoggedUser() {
		return $this->loggedUser ;
	}
	public function canWrite($bookId) {
		return OgHelper::canWrite($bookId) ;	
	}
	
	public function canRead($bookId) {
		return OgHelper::canRead($bookId) ; 
	}
	
	public function canDelete($bookId) {
		return $this->isLoggedIn();	
	}
	
	public function canCreate( $parentResourceId ) {
		return OgHelper::canAdd() ;		 
	}
	
}
