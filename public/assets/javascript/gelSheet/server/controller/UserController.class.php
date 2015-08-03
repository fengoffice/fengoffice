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
	class UserController extends FrontController {

		private $currentUser;

		public function __construct($currentUser= null){

			if ($currentUser== null){


				if (isset($_SESSION['user']['id'])){

					$this->currentUser= $_SESSION['user']['id'];

				}

			}
			else {

				$this->currentUser= $currentUser;

			}


		}

		public function __destruct(){}

		public function getCurrentUser(){

			return $this->currentUser;

		}


		/*
		 * this function returns the books identification for the user
		 * when null it takes the current session user
		 */

		public function getUserBooks($user= null, $format = 'xml'){
			
				$id= null;
				if ($user== null){
					$id= $this->currentUser;
				}
				else{
					$id= $user;
				}
				$id =1;
				//$sql= "SELECT BookId, BookName, BookCreatedOn, UserName FROM ".table("books")." b INNER JOIN ".table("users")." u ON b.UserId = u.UserId WHERE b.UserId= $id";
				$sql= "SELECT BookId, BookName,  UserName FROM ".table("books")." b INNER JOIN ".table("users")." u ON b.UserId = u.UserId WHERE b.UserId= $id";
				$result= mysql_query($sql);
				//echo " # ".$sql." # ";
	
				$books= array();
				
				if ($format == 'json') {
					$json = "";
					while ($row= mysql_fetch_object($result)){
						$json .= ",{'id':'$row->BookId','name':'$row->BookName','date':'$row->BookCreatedOn','user':'$row->UserName'}";
						//$books [] = $row->BookId;
					}
					echo "{'files':[".substr($json,1)."]}";
					//return $books;
					$output = $json ;
				}elseif ($format == 'xml') {
					$xml =<<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ItemSearchResponse xmlns="http://webservices.amazon.com/AWSECommerceService/2006-06-28">
	<Items>
		<TotalResults>1</TotalResults>
		<TotalPages>1</TotalPages>
XML;
					while ($row = mysql_fetch_object($result)) {
						$bookId = $row->BookId ;
						$bookName = $row->BookName ;
						$created = '14/05/2008'; //TODO Take this from db
						
						$xml.=  "\t<Item>\n";
						$xml.=  "\t\t<ItemAttributes>\n";
						$xml.=  "\t\t\t<BookId>$bookId</BookId>\n";
						$xml.=  "\t\t\t<Name>$bookName</Name>\n";
						$xml.=  "\t\t\t<CreationDate>$created</CreationDate>\n";
						$xml.=  "\t\t</ItemAttributes>\n";
						$xml.=  "\t</Item>\n";
					}
					$xml .=<<< XML
	</Items>
</ItemSearchResponse>						
XML;
					header('Content-Type: text/xml');
					$output = $xml;				
				}
				echo $output ;
		}

	
		

		/*returns all users from database*/
		public function getUsers(){

			$sql= "SELECT * FROM ".table("users")."";

			$result= mysql_query($sql);

			$users= array();

			while ($row= mysql_fetch_row($result)){

				$users[] = array(
					'userId'	=>	$row->userId	,
					'userName'	=> 	$row->userName
				);

			}

			return $users;


		}


	}


?>