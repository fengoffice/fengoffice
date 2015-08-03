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


	class BookController extends FrontController {

		public function __destruct(){}

		public function saveBook($book, $inputFormat = 'jsons', $outputFormat = 'dbs'){
			//$book = stripslashes($book);
			$newBook = new Book();
		
			switch ($inputFormat) {
				case 'json':
				default :
					
					$json_obj = json_decode($book);
					if(!isset($json_obj)){
						$error =  new GsError(401,"Ups!!! Sorry, Book has not received properly to server. Be aware you are running an alpha version.");
						if($error->isDebugging()){
							$error->addContentElement("Recieved data",$book);
						}
						throw $error;
					}
					
					//TODO: Remove when user functionalities added
					$json_obj->userId = 0;
					$newBook->fromJson($json_obj);
					break ;
			}
			

			
			if ($outputFormat == 'db') {
				// Permission check
				if ( is_numeric( $newBook->bookId ) ) {
					$this->security->checkWrite($newBook->bookId) ;
				}else {
					$this->security->checkCreate(); 
				}
				$newBook->save();
				
			}else {
				$controller= new ExportController();
				switch($outputFormat) {
					case 'xls':
						$controller->generateBook($newBook, $outputFormat);
						break;
					case 'xlsx':
						$controller->generateBook($newBook, $outputFormat);
						break;
					case 'pdf':
						$controller->generateBook($newBook, $outputFormat);
						break;
					case 'ods':
						$controller->generateBook($newBook, $outputFormat);
						break;
					default:
						$errors =  $newBook->save();
						if(!$errors)
							throw new Success('Book saved succesfully',"{'BookId':".$newBook->getId()."}");
						else {
							$error = new GsError(302,"Error saving book.");
							throw $error;						
						}
						break;
				}
			}
		}


		public function find ($id= null){
			if ($id!= null){
				$book= new Book();
				$book->load($id);
				return $book;
			}
			else{
				$error = new GsError(303,"Error loading book.");
				throw $error;
			}
		}

		public function getBooks(){
			$sql = "select * from ".table('books');
			$result= mysql_query($sql);
			while($row = mysql_fetch_object($result)){
				$books[] = array(
					'bookId'	=>	$row->bookId	,
					'bookName'	=> 	$row->bookName
				);
			}
			return $books;
		}

		
		function deleteBook($bookId) {
			if (@mysql_query("START TRANSACTION") &&
					@mysql_query("DELETE FROM `" . table('cells') . "` WHERE `SheetId` IN (SELECT `SheetId` FROM `" . table('sheets') . "` WHERE `BookId` = $bookId)") &&
					@mysql_query("DELETE FROM `" . table('mergedCells') . "` WHERE `SheetId` IN (SELECT `SheetId` FROM `" . table('sheets') . "` WHERE `BookId` = $bookId)") &&
					@mysql_query("DELETE FROM `" . table('rows') . "` WHERE `SheetId` IN (SELECT `SheetId` FROM `" . table('sheets') . "` WHERE `BookId` = $bookId)") &&
					@mysql_query("DELETE FROM `" . table('columns') . "` WHERE `SheetId` IN (SELECT `SheetId` FROM `" . table('sheets') . "` WHERE `BookId` = $bookId)") &&
					@mysql_query("DELETE FROM `" . table('sheets') . "` WHERE `BookId` = $bookId") &&
					@mysql_query("DELETE FROM `" . table('fontStyles') . "` WHERE `BookId` = $bookId") &&
					@mysql_query("DELETE FROM `" . table('books') . "` WHERE `BookId` = $bookId") &&
					@mysql_query("COMMIT")) {
//				echo "{'Error':0,'Message':'Book $bookId deleted succesfully','Data':{'BookId':".$bookId."}}";
//				throw new Success('Book deleted succesfully',"{'BookId':$bookId}");
			} else {
				$error = new GsError(302,"Error deleting book.");
				if($error->isDebugging()){
					$err = str_replace("'", '"', mysql_error());
					$error->addContentElement("BookId",$bookId);
					$error->addContentElement("MySql Error",$err);
				}
				throw $error;					
			}
		}
	}


?>