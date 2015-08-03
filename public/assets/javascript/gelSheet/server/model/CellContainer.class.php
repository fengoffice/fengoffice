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
abstract class CellContainer {
	public $index ; 
	public $size  ;
	public $fontStyleId ;
	public $sheetId ;
	public $layerStyleId ;
	public $layoutStyleId ;	
	
	
	public function __construct($sheetId=null, $index=null, $size=null, $fontStyleId=null, $layerStyleId=null, $layoutStyleId=null){
		$this->index = $index ; 
		$this->size  = $size;
		$this->fontStyleId = $fontStyleId ;
		$this->sheetId = $sheetId;
		$this->layerStyleId = $layerStyleId;
		$this->layoutStyleId = $layoutStyleId;	
		
	}

		/**
		 * Destructor.
		 */
	public function __destruct(){
			
	}

	
	
	/**
	 * 
	 * @return Integer
	 */
	public function getFontStyleId() {
		return $this->fontStyleId;
	}
	
	/**
	 * @return unknown
	 */
	public function getIndex() {
		return $this->index;
	}
	
	/**
	 * @return unknown
	 */
	public function getLayerStyleId() {
		return $this->layerStyleId;
	}
	
	/**
	 * @return unknown
	 */
	public function getLayoutStyleId() {
		return $this->layoutStyleId;
	}
	
	/**
	 * @return unknown
	 */
	public function getSheetId() {
		return $this->sheetId;
	}
	
	/**
	 * @return unknown
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * @param unknown_type $fontStyleId
	 */
	public function setFontStyleId($fontStyleId) {
		$this->fontStyleId = $fontStyleId;
	}
	
	/**
	 * @param unknown_type $index
	 */
	public function setIndex($index) {
		$this->index = $index;
	}
	
	/**
	 * @param unknown_type $layerStyleId
	 */
	public function setLayerStyleId($layerStyleId) {
		$this->layerStyleId = $layerStyleId;
	}
	
	/**
	 * @param unknown_type $layoutStyleId
	 */
	public function setLayoutStyleId($layoutStyleId) {
		$this->layoutStyleId = $layoutStyleId;
	}
	
	/**
	 * @param unknown_type $sheetId
	 */
	public function setSheetId($sheetId) {
		$this->sheetId = $sheetId;
	}
	
	/**
	 * @param unknown_type $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}
	
	
	
	public abstract  function load($sheetId,$index) ;

	public abstract  function save($SheetId) ;
	
	/**
	 * Enter description here...
	 *
	 * @param String $JsonObj
	 */
	public function fromJson($JsonObj){
		$this->sheetId = $JsonObj->sheetId;
		$this->index = $JsonObj->index;
		$this->size = $JsonObj->size;
		$this->fontStyleId = $JsonObj->fontStyleId;
		$this->layerStyleId = $JsonObj->layerStyleId;
		$this->layoutStyleId = $JsonObj->layoutStyleId;
	}
	
	public function toJson(){
		return json_encode($this);
	}
	
}

?>