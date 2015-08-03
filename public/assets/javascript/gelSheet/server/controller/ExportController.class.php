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

	class ExportController {
		/*this controller manages the export functions*/

		private $file;
		private $book;
		private $objPHPExcel;
		private $objPHPOds;

		/*constructs*/

		/*the construct gets the book id for the exportation*/
		public function __construct() {}

		public function __destruct() {}

		
		function generateBook($book, $format) {

			$this->book= $book;			
			
			if (!$book->bookId)
				$bookName= "spreadsheet-1";	
			else	
				$bookName= "spreadsheet-$book->bookId";
							
			$filename= "default-".rand(1,9999);
			

			/*SET SPREADSHEET PROPERTIES*/
			if ($format!= "ods"){

				$this->objPHPExcel = new PHPExcel();
				$this->objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
				$this->objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
				$this->objPHPExcel->getProperties()->setTitle("Test Document");
				$this->objPHPExcel->getProperties()->setSubject("Test Document");
				$this->objPHPExcel->getProperties()->setDescription("Test document generated using PHP classes.");
				$this->objPHPExcel->getProperties()->setKeywords("office php");
				$this->objPHPExcel->getProperties()->setCategory("Test result file");

			}
			else{
				$this->objPHPOds= new PHPOds(); //create a new ods file
			}

			/*GENERATE THE SHEETS*/
			$this->_generateSheets($format);


			global $cnf;
			$currentDir= $cnf['path']['Temp']."/";  // Get the Storage Folder


			switch($format){

				case "ods":
							saveOds($this->objPHPOds,"$filename.$format"); //save the object to a ods file
							break;

				case "pdf":
							$objWriter1 = new PHPExcel_Writer_PDF($this->objPHPExcel);
							$objWriter1->writeAllSheets();
							$objWriter1->setTempDir($currentDir);
							$objWriter1->save("$filename.$format");	//save the object to a pdf file
							break;

				case "xls":
							$objWriter2 = new PHPExcel_Writer_Excel5($this->objPHPExcel);
							$objWriter2->setTempDir($currentDir);
							$objWriter2->save("$filename.$format");	//save the object to a xls file
							break;

				case "xlsx":
							$objWriter3 = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
							$objWriter3->save($currentDir."$filename.$format"); //save the object to a xlsx file
							break;

				case "csv":
							$objWriter4 = new PHPExcel_Writer_CSV($this->objPHPExcel);
							//$objWriter4->setTempDir($currentDir);
							$objWriter4->setDelimiter(';');
							$objWriter4->setEnclosure('');
							$objWriter4->setLineEnding("\r\n");
							$objWriter4->save("$filename.$format");	//save the object to a CSV file
							break;
							
				case "html":
							$objWriter5 = new PHPExcel_Writer_HTML($this->objPHPExcel);
							$objWriter5->writeAllSheets();
							//$objWriter5->setTempDir($currentDir);
							$objWriter5->save("$filename.$format");	//save the object to a HTML file
							break;
							

			}

			if ($format != "ods")
				$this->_send("$filename.$format", $format, $bookName);

		}
		
		
		

		/**
		 * Generates the sheet's workbook...
		 *
		 * @param String format extension
		 */
		function _generateSheets($format){


			$sheets= array();
			$sheets= $this->book->getSheets();
			$i= 0;


			if ($format=="ods"){

				foreach($sheets as $sheet){

					$cells= array();
					$cells= $sheet->getCells();


					foreach($cells as $cellarray){

						foreach($cellarray as $cell){

						$col= $cell->getDataColumn();
						$row= $cell->getDataRow();
						$data= $cell->getFormula();
						$fontId= $cell->getFontStyleId();
						$fontStyle= new FontStyle();
						$fontStyle= $this->book->getFontStyle($fontId);
						
						

						if (substr($data, 0, 1)== '=')

							$this->objPHPOds->addCell($i,$row,$col,substr($data, 1),'float');
							

						//TODO
						else /*OJO CON ESTO DISCERNIR ENTRE LOS DIFERENTES TIPOS*/

							$this->objPHPOds->addCell($i,$row,$col,$data,'string');

						}
						$this->objPHPOds->addStyle($fontStyle, $cell);
					}
					$i++;
				}
			}
			else{

				foreach($sheets as $sheet){

					if ($i>0)
						$this->objPHPExcel->createSheet();

					$this->objPHPExcel->setActiveSheetIndex($i);
					$j= $i + 1;
					$this->objPHPExcel->getActiveSheet()->setTitle("Sheet $j");

					$cells= array();
					
					$cells= $sheet->getCells();

					foreach($cells as $cellarray){

						$cell= new Cell();
						foreach ($cellarray as $cell){

						
							$col= $cell->getDataColumn();
							$row= $cell->getDataRow();
							$row++;
							
							$data= $cell->getFormula();
//							$this->objPHPExcel= new PHPExcel();
							
							
							$fontId= $cell->getFontStyleId();
							$fontStyle= new FontStyle();
							$fontStyle= $this->book->getFontStyle($fontId);
							$fontName= $fontStyle->getFontName();	
							$fcolor= substr($fontStyle->getFontColor(),1);
							
							if ($fcolor == "000000"){ 
								//echo "$row $col $fcolor<hr>";
							 	$ncolor= new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK);
							 	$ncolor->setRGB($fcolor);							 	
							}
							else{
								//echo "$row $col $fcolor<hr>";
								$ncolor= new PHPExcel_Style_Color();
								$ncolor->setRGB($fcolor);				
							}	
							
							$style= new PHPExcel_Style();
							$style->getFont()->setColor($ncolor);
							$style->getFont()->setName($fontName);
							$style->getFont()->setBold($fontStyle->getFontBold()== 1);
							$style->getFont()->setItalic($fontStyle->getFontItalic()==1);
							$style->getFont()->setSize($fontStyle->getFontSize());
							
							
							$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
							
							switch ($fontStyle->fontHAlign){
								
								case 0:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
									break;
								case 1:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
									break;
								case 2:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
									break;
								case 3:									
									$HzAlign= PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
									break;								
							}
				
							$VlAlign= PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
							
							switch ($fontStyle->fontVAlign){
								
								case 0:									
									$VlAlign= PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
									break;
								case 1:									
									$VlAlign= PHPExcel_Style_Alignment::VERTICAL_CENTER;
									break;
								case 2:									
									$VlAlign= PHPExcel_Style_Alignment::VERTICAL_TOP;							
															
							}						
							
							$style->getAlignment()->setHorizontal($HzAlign);
							$style->getAlignment()->setVertical($VlAlign);
							
							
							if ($fontStyle->getFontUnderline()!= 0){
								$style->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
							}
														
							$this->objPHPExcel->getActiveSheet()->duplicateStyle($style, PHPExcel_Cell::stringFromColumnIndex($col) . $row);
							$this->objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->setValueExplicit($data, PHPExcel_Cell_DataType::dataTypeForValue($data));							
											
						}
						
					}
						$i++;

					}

				}

		}


		/**
		 * Sends HTTP Headers to Download Archive...
		 *
		 * @param String $filename
		 */
		function _send($file, $format, $bookName){

			global $cnf;
			
		    //This will set the Content-Type to the appropriate setting for the file
		    switch( $format ) {
	          case "pdf": $ctype="application/pdf"; break;
		      case "exe": $ctype="application/octet-stream"; break;
		      case "zip": $ctype="application/zip"; break;
		      case "doc": $ctype="application/msword"; break;
		      case "xls": $ctype="application/vnd.ms-excel"; break;
		      case "xlsx": $ctype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
		      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
		      case "gif": $ctype="image/gif"; break;
		      case "png": $ctype="image/png"; break;
		      case "jpeg":
		      case "jpg": $ctype="image/jpg"; break;
		      case "mp3": $ctype="audio/mpeg"; break;
		      case "wav": $ctype="audio/x-wav"; break;
		      case "mpeg":
		      case "mpg":
		      case "mpe": $ctype="video/mpeg"; break;
		      case "mov": $ctype="video/quicktime"; break;
		      case "avi": $ctype="video/x-msvideo"; break;
		
		      //The following are for extensions that shouldn't be downloaded (sensitive stuff, like php files)
		      case "php":
		      case "htm":
		      case "html":
		      case "txt": die("<b>Cannot be used for ". $format ." files!</b>"); break;
		
		      default: $ctype="application/force-download";
		    }
		
		    
		    
		    
		    $bookName= $bookName;//.".".$format;
		    
		    //Begin writing headers
		    header("Pragma: public");
		    header("Expires: 0");
		    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		    header("Cache-Control: public");
		    header("Content-Description: File Transfer");
		   
		    //Use the switch-generated Content-Type
		    header("Content-Type: $ctype");			    
		    $header="Content-Disposition: attachment; filename=".$bookName.";";
		    header($header);
		    header("Content-Transfer-Encoding: binary");
		    
		    @readfile($cnf['path']['Temp'].$file);
					
		   // sleep(4);
		    
			unlink($cnf['path']['Temp'].$file) ;

		}

	}

?>