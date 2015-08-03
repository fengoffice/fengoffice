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

	/**
 	* Class FontStyle.
 	* @author Pepe
 	*/
class FontStyle extends Model  {

	public $fontStyleId ;
	public $bookId;
	public $fontId;
	public $fontSize ;
	public $fontBold ;
	public $fontItalic;
	public $fontUnderline;
	public $fontColor;
	public $fontVAlign;
	public $fontHAlign;



	public function getFontName(){

		$sql= "SELECT * FROM ". table('fonts') ." WHERE FontId= $this->fontId";
		$result= mysql_query($sql);

		if ($row = mysql_fetch_object($result)) {
			return $row->FontName;
		}
		else{
			return "Calibri";
		}
	}


	public function toJson() {
		return parent::toJson() ;
	}

	public function fromJson($json_obj){
		return parent::fromJson($json_obj); 
	}

	public function delete($recursive = false) {
		$sql = "DELETE  FROM ".table('fontStyles'). " where fontStyleId=$this->fontStyleId";
		return mysql_query($sql);
	}


	/**
	* Constructor.
 	*/
	public function __construct($fontStyleId = null,$bookId=null, $fontId =null, $fontSize =null ,  $fontBold =null, $fontItalic=null, $fontUnderline = null,$fontColor = null, $fontVAlign = null, $fontHAlign = null ){
		$this->fontStyleId= $fontStyleId;
		$this->bookId= $bookId;
		$this->fontId= $fontId;
		$this->fontSize = $fontSize;
		$this->fontBold= $fontBold ;
		$this->fontItalic= $fontItalic;
		$this->fontUnderline= $fontUnderline;
		$this->fontColor= $fontColor;
		$this->fontVAlign = $fontVAlign ;
		$this->fontHAlign = $fontHAlign ;
	}

	/**
	 * Destructor.
	 */
	public function __destruct(){
	}

	public function save() {
		return parent::save();
	}
}
