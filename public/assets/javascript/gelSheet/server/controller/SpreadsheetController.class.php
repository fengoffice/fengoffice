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
class SpreadsheetController extends FrontController  {
	

		
	public function loadBook($bookId){
		$this->security->checkRead($bookId);
		$bookController = new BookController();
		$book = $bookController->find($bookId);
		
		throw new Success(null,$book->toJson());
	}
	
	
	public function saveBook($book, $inputFormat, $outputFormat ) {
		$this->security->checkLoggedIn();		
		// The book Controller will check if has permission depending on the bookId
		// Here we cannot see the book id.. its inside $book !
		 
		$bookController = new BookController();
		// Do security checks and save the book
		return  $bookController->saveBook($book,$inputFormat,$outputFormat);
	}
	
	public function deleteBook($bookId) {
		$this->security->checkDelete($bookId);
		$bookController = new BookController();
		return  $bookController->deleteBook($bookId);
	}
	
}

?>