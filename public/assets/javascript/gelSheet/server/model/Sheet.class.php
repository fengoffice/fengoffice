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
	include_once("config/settings.php");


	class Sheet {
		public $sheetId;
		public $bookId;
		public $sheetName;
		public $sheetIndex;
		public $cells=array();
		public $rows=array();
		public $cols=array();


		/**
		* Constructor.
	 	*/
		public function __construct($sheetId=null,$bookId=null, $sheetName=null, $sheetIndex=null ){
			$this->sheetId = $sheetId ;
			$this->bookId=$bookId;
			$this->sheetName=$sheetName;
			$this->sheetIndex=$sheetIndex;
		}

		/**
		 * Destructor.
		 */
		public function __destruct(){

		}

		/** Setters **/

		public function setId($id){
			$this->sheetId=$id;
		}

		public function setBookId($bid){
			$this->bookId=$bid;
		}

		public function setName($name){
			$this->sheetName=$name;
		}

		public function setIndex($sindex){
			$this->sheetIndex=$sindex;
		}


		/** Getters **/

		public function getId (){
			return $this->sheetId;
		}

		public function getBookId(){
			return $this->bookId;
		}

		public function getName(){
			return $this->sheetName;
		}

		public function getIndex(){
			return $this->sheetIndex;
		}



		/**
		 * Enter description here...
		 *
		 * @param unknown_type $row
		 * @param unknown_type $column
		 * @return Cell
		 *
		 *
		 */
		public function getCell($row, $column){
			//	Ineficient !
			foreach ($this->cells as $cell)
				if ( ($row == $cell->getDataRow() ) && ($column == $cell->getDataColumn() ) )
					return $cell;
			return null;
		}


		/**
		 * Returns the value of a specific cell.
		 * If empty, returns an empty String.
		 *
		 * @param Integer $row
		 * @param Integer $column
		 * @return String
		 */
		public function getData($row, $column){
			//	Ineficient !
			foreach ($this->cells as $cell)
				if ( ($row == $cell->getDataRow() ) && ($column == $cell->getDataColumn() ) )
					return $cell->getFormula();
			return '';
		}

		/************/


		/** Others **/


		/**
		 * Returns all the cells in a given column
		 *
		 * @param Integer col_num - The number of the column
		 * @return array of cells
		 *
		 */
		public function getCol($col_num){
			$rows = array();
			foreach ($this->cells as $cell){
				if ($cell->dataColumn == $col_num) {
					$rows[]=$cell;
				}
			}
			return $rows;
		}

		/**
		 * Returns all the cells in a given row
		 *
		 * @param Integer row_num - The number of the row
		 * @return array of cells
		 *
		 */
		public function getRow($row_num){
			$cols = array();
			foreach ($this->cells as $cell){
				if ($cell->dataRow == $row_num) {
					$cols[]=$cell;
				}
			}
			return $cols;
		}

		/**
		 * Returns all cells from the sheet
		 *
		 *
		 *
		 * @param none
		 * @return array of cells
		 *
		 */

		public function getCells(){

			return $this->cells;

		}


		/**
		 * Enter description here...
		 *
		 * @param Cell $cell
		 * @return unknown
		 */
		public function addCell($cell){
			//$this->cells[]=$cell;

			$this->cells["".$cell->dataRow]["".$cell->dataColumn] = $cell;

		}

		public function addRow($row){
			$this->rows[$row->getIndex()] = $row;
		}

		public function addColumn($col){


			$this->cols[$col->getIndex()] = $col;
		}

		public function delete($recursive=false){

			if ($recursive){
				$sql="delete from ".table('cells'). " where SheetId = $this->sheetId";
				mysql_query($sql);
			}

			$sql="delete from ".table('sheets'). " where SheetId = $this->sheetId";
			mysql_query($sql);

		}

		private function loadColumns(){
			$sql = "select * from ".table('columns'). " where SheetId=$this->sheetId" ;
			$result =  mysql_query($sql);
			while ($row = mysql_fetch_object($result)){
				$col = new Column($row->SheetId, $row->ColumnIndex,$row->ColumnSize,$row->FontStyleId, $row->LayerStyleId,$row->LayoutStyleId);

				$this->addColumn($col);
			}

		}

		private function loadRows(){
			$sql = "select * from ".table('rows'). " where SheetId=$this->sheetId" ;
			$result =  mysql_query($sql);
			while ($row = mysql_fetch_object($result)){
				$nrow = new Row($row->SheetId, $row->RowIndex,$row->RowSize,$row->FontStyleId, $row->LayerStyleId,$row->LayoutStyleId);

				$this->addRow($nrow);
			}

		}

		private function loadCells(){

			global $cnf;
			$cnf['path']['Cell'] 	= "model/Cell.class.php";

			$sql = "select * from ".table('cells'). " where SheetId=$this->sheetId" ;
			$result =  mysql_query($sql);
			while ($row = mysql_fetch_object($result)){
				$cell = new Cell($row->SheetId,$row->DataColumn,$row->DataRow,$row->CellFormula,$row->FontStyleId, $row->LayoutStyleId, $row->CellValue);
				$this->addCell($cell);
			}
		}

		public function load($SheetId) {

			$sql = "select * from ".table('sheets'). " where SheetId=$SheetId ";
			//$connection  = new Connection();
			$result =  mysql_query($sql);
			if($sheet = mysql_fetch_object($result)) {
				$this->sheetId = $sheet->SheetId;
				$this->bookId = $sheet->BookId;
				$this->sheetName = $sheet->SheetName ;
				$this->sheetIndex = $sheet->SheetIndex;
			}
			$this->loadRows();
			$this->loadColumns();
			$this->loadCells();

		}

		public function save(){
			//echo print_r($this);
			$hasErrors = false;
			//$this->delete(false); Ver que hacemos
			if (isset($this->sheetId)) {

				$sql = "INSERT INTO ".table('sheets'). " (SheetId, BookId, SheetName, SheetIndex) VALUES (%d,%d,'%s',%d)";
				$sql = sprintf($sql,$this->sheetId,$this->bookId,$this->sheetName,$this->sheetIndex);
				if (!mysql_query($sql))
					return true; //has Errors
			} else {
				$sql = "INSERT INTO ".table('sheets'). " (BookId, SheetName, SheetIndex) VALUES (%d,'%s',%d)";
				$sql = sprintf($sql , $this->bookId , $this->sheetName , $this->sheetIndex);

				$result = mysql_query($sql);
				if(!$result)
					return true;
				$this->sheetId = mysql_insert_id();
			}

			foreach ($this->cells as $row => $col_array) {
				foreach ($col_array as $col_num => $cell) {
					$cell->sheetId = $this->sheetId ;
					/* @var $cell Cell  */
					$cell->save();
				}
			}
			return $hasErrors;
		}

		public function toJson(){
			//return json_encode($this);
			$json = "{id:$this->sheetId,cells:[";
			$temp = "";
			foreach($this->cells as $row){
				foreach($row as $cell){
					$temp.= ",".$cell->toJson();
				}
			}
			$json.= substr($temp,1)."]}";
			return $json;
		}


		public function fromJson($obj){
			//print print_r($obj);

			$this->sheetId = $obj->sheetId;
			//$this->bookId=$obj->bookId;
			$this->sheetName = $obj->sheetName;
//			$this->sheetIndex = $obj->sheetIndex;
			$this->sheetIndex = 0; //TODO: get correct index when implemented
		
			foreach ($obj->cells as $jsonCell){
				$cell = new Cell();
				$jsonCell->sheetId = $this->sheetId;
				$cell->fromJson($jsonCell);
				$this->addCell($cell);
			}
		}
	}
?>