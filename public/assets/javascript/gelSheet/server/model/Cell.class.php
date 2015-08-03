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


class Cell {
	/**
	 * @var Integer
	 */
	public $sheetId;
	
	/**
	 * @var Integer
	 */
	public $dataColumn;
	
	/**
	 * Enter description here...
	 * @var Integer
	 */
	public $dataRow;
	
	/**
	 * Store the data? + the formula definition
	 * @var unknown_type
	 */
	public $cellFormula;
	
	/**
	 * Stores the result for the cellFormula
	 * @var String
	 */
	public $cellValue;
	
	/**
	 * @var Integer
	 */
	public $fontStyleId;
	
	/**
	 * @var Integer
	 */
	public $layoutStyleId;




	/**
	 * Constructor.
	 */
	public function __construct($sheetId=null,$dataColumn=null,$dataRow=null,$cellFormula=null,$fontStyleId=null, $layoutStyleId=null, $cellValue=null ){
			$this->sheetId = $sheetId ;
			$this->dataColumn = $dataColumn;
			$this->dataRow = $dataRow;
			$this->cellFormula =$cellFormula ;
			$this->fontStyleId = $fontStyleId;
			$this->layoutStyleId = $layoutStyleId;
			$this->cellValue = $cellValue ;
	}

	/**
	 * Destructor.
	 */
	public function __destruct(){

	}



	/** Setters **/

	public function setSheetId($sheet){
		$this->sheetId = $sheet;
	}

	public function setId($id)  {
		$this->sheetId = $id;
	}

	public function setDataColumn($dc){
		$this->dataColumn = $dc;
	}

	public function setDataRow($dr){
		$this->dataRow = $dr;
	}

	public function setFormula($formula){
		$this->cellFormula = $formula;
	}
	
	public function setCellValue($cellValue) {
		$this->cellValue = $cellValue ;
	}

	public function setFontStyleId($font_style){
		$this->fontStyleId=$font_style;
	}

	public function setLayoutStyleId($layout_id){
		$this->layoutStyleId=$layout_id;
	}

	/** Getters **/

	/**
	 * Enter description here...
	 *
	 * @return Integer
	 */
	public function getSheetId(){
		return $this->sheetId;
	}

	/**
	 * Enter description here...
	 *
	 * @return Integer
	 */
	public function getId()  {
		return $this->sheetId;
	}

	/**
	 * Enter description here...
	 *
	 * @return Integer
	 */
	public function getDataColumn(){
		return $this->dataColumn;
	}

	/**
	 * Enter description here...
	 *
	 * @return String
	 */
	public function getDataRow(){
		return $this->dataRow;
	}

	/**
	 * Enter description here...
	 *
	 * @return String
	 */
	public function getFormula(){
		return $this->cellFormula;
	}

	public function getCellValue() {
		return $this->cellValue ;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Integer
	 */
	public function getFontStyleId(){
		return $this->fontStyleId;
	}

	/**
	 * Enter description here...
	 *
	 * @return Integer
	 */
	public function getLayoutStyleId(){
		return $this->layoutStyleId;
	}



	/**
	 * Enter description here...
	 *
	 * @param unknown_type $SheetId
	 * @param unknown_type $DataColum
	 * @param unknown_type $DataRow
	 */
	public function load($SheetId, $DataColum, $DataRow){
	/*

		$sql = "select * from cell where SheetId=$SheetId ";
		$connection  = new Connection();
		$result =  mysql_query($sql);
		if ($cell = mysql_fetch_object($result) )	{
			this->SheetId = $cell->SheetId ;
			this->DataColumn = $cell->SheetId;
			this->CellFormula = $cell->CellFormula ;
			this->FormatId = $cell->FormatId;
			return true;
		}
		return false;
	*/
	}

	/**
	 * Enter description here...
	 *
	 * @return QueryResult
	 */
	public function save(){
		$sql = sprintf("INSERT INTO ".table('cells'). " (SheetId, DataColumn,DataRow,CellFormula,FontStyleId,LayoutStyleId, CellValue) VALUES (%d,%d,%d,'%s',%d,%d,'%s')",
							$this->sheetId,
							$this->dataColumn,
							$this->dataRow,
							addslashes($this->cellFormula),
							$this->fontStyleId,
							$this->layoutStyleId,
							addslashes($this->cellValue)
						);
		$result = mysql_query($sql) ;				
		if (!$result) {
			$error = new  GsError(345, "Error saving") ;
			if ($error->isDebugging()){
				$error->addContentElement("descrption","Saving cell" ); 
				$error->addContentElement("MySqlError",mysql_error() ); 
				$error->addContentElement("MySqlQuery",$sql ); 
			}
			throw $error ; 
		}
		
	}

	/**
	 * Enter description here...
	 *
	 * @param JsonDatatype
	 */
	public function fromJson($obj){
		$this->sheetId = $obj->sheetId;
		$this->dataColumn=$obj->dataColumn;
		$this->dataRow=$obj->dataRow;
		$this->cellFormula=$obj->cellFormula;
		//$this->formatId=$obj->formatId;
		$this->setFontStyleId($obj->fontStyleId);
		$this->setLayoutStyleId($obj->layoutStyleId);
		$this->setCellValue($obj->cellValue);

	}

	public function toJson() {
		$cellFormula = addslashes($this->cellFormula) ;
		$cellValue = addslashes($this->cellFormula) ; 
		return "{r:$this->dataRow,c:$this->dataColumn,f:'$cellFormula', v:'$this->cellValue', fs:'$this->fontStyleId'}";
	}

}
?>