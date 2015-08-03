<?php
abstract class SecurityController {

	abstract function  __construct () ;

	abstract function getLoggedUser();
	
	abstract function isLoggedIn(); 
	
	abstract function canWrite($bookId) ;

	abstract function canRead($bookId) ;
	
	abstract function canDelete($bookId);
	
	abstract function canCreate($parentResourceId) ; 

	function checkLoggedIn() {
		if (! $this->isLoggedIn() ) {
			throw new GsError(777,"Permission denied: You need to be logged in");
		}
	}
	
	function checkWrite ($bookId) {
		if (! $this->canWrite($bookId) ) {
			throw new GsError(777,"Permission denied: You are no allowed to write the book");
		}
	}

	function checkRead ($bookId) {
		if (! $this->canRead($bookId) ) {
			throw new GsError(777,"Permission denied: You are no allowed to open the book");
		}
	}
	
	function checkCreate() {
		if (! $this-> canCreate(null) ) {
			throw new GsError(777,"Permission denied: You are no allowed to create the book");
		}
	}
	
	function checkDelete($bookId) {
		if (! $this-> canDelete($bookId) ) {
			throw new GsError(777,"Permission denied: You are no allowed to delete the book");
		}
	}
	
	
}
	