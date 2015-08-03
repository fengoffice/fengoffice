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
	include_once("model/Book.class.php");	
	include_once("model/Cell.class.php");
	include_once("model/Sheet.class.php");
	
	/**
	 * Book Controller
	 * 
	 *
	 */
	class FormatController {
	
		/**
		 * Enter description here...
		 *
		 * @param Sheet $sheet
		 */
		public static function maxWrittenRow($sheet) {
			$max = 0;			 
			foreach ($sheet->cells as $cell) {
				if ($cell->getDataRow() > $max )
					$max = $cell->getDataRow() ;
			}
			return $max;
		}

		
		/**
		 * Enter description here...
		 *
		 * @param Sheet $sheet
		 */
		public static function maxWrittenColumn($sheet) {
			$max = 0;			 
			foreach ($sheet->cells as $cell) {
				if ($cell->getDataColumn() > $max )
					$max = $cell->getDataColumn() ;
			}
			return $max;
		}
		
		/**
		 * Enter description here...
		 *
		 * @param FontStyle $fstyle
		 * @return String
		 */
		public function fontStyleToCss($fstyle){
			$css = ".c".$fstyle->getId()."{";
			//$css .= "font-family:". $fstyle->getFontId().";";
			$css .= "font-size:". $fstyle->getFontSize().";";
			if($fstyle->getFontBold())
				$css .= "font-weight:bold;";
			
			if($fstyle->getFontItalic())
				$css .= "font-style:italic;";
			
			if($fstyle->getFontUnderline())
				$css .= "text-decoration:underline;";
			
			$css .= "color:". $fstyle->getFontColor();
			
			return $css."}";	
		}
		
		/**
		 * Export a sheet to Html table
		 *
		 * @param Sheet $sheet
		 * @return String
		 */
		public static function toHTML($sheet) {
			$maxCol = FormatController::maxWrittenColumn($sheet) ;
			$maxRow  = FormatController::maxWrittenRow($sheet)   ;
			
			$book = new Book();
			$book->load($sheet->getBookId());
			
			$cells = $sheet->cells;
	
			unset($output);
			$output.='<table border=1 border-collapse="collapse">';

			for ($i = 0 ; $i <= $maxRow ; $i++) {
				$output.='<tr>';
				for ($j = 0 ; $j <= $maxCol ; $j++) {
					$cell = $sheet->getCell($i,$j);
					$fsId = 0;
					if ($cell<> null)	
						$fsId = $cell->getFontStyleId();
						
					$output.='<td class="c'.$fsId.'">';
					if ($cell<> null)	
						//$output.= $sheet->getData($i,$j);
						$output.=$cell->getFormula();
					$output.='</td>';
				}
				$output.='</tr>';
			}	
			$output.='</table>';
			$style  = '<style type="text/css" media="screen">';
			$style .= "table{border-collapse:collapse;}";
	    	$style .= "td{border:1px solid #CCC;height:18px;width:80px;position:relative;}";

	    	
	    	foreach($book->getFontStyles() as $fstyle){

	    		$style .= FormatController::fontStyleToCss($fstyle)."\n";
	    	}
	    	
    		$style .='</style>';
			
			$output='<html><head>'.$style.'</head><body>'.$output.'</body></html>';
			return $output; 
		}
		
		
		
		
		
	}	
	

?>