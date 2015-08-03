<?php

class BorderStyle extends Model{
	
	/**
	 * @var int
	 */
	var $borderStyleId;

	/**
	 * @var string
	 */
	var $color;

	/**
	 * @var int 
	 */
	var $width; 

	/**
	 * @var string
	 */
	var $style;         
	
	/**
	 * @var unknown_type
	 */
	var $bookId ;
	
	/**
	 * @return int
	 */
	public function getBorderStyleId() {
		return $this->borderStyleId;
	}
	
	/**
	 * @return string
	 */
	public function getColor() {
		return $this->color;
	}
	
	/**
	 * @return string
	 */
	public function getStyle() {
		return $this->style;
	}
	
	/**
	 * @return unknown
	 */
	public function getWidth() {
		return $this->width;
	}
	
	public function getBookId() {
		return $this->bookId ;
	}
	
	/**
	 * @param int $borderStyleId
	 */
	public function setBorderStyleId($borderStyleId) {
		$this->borderStyleId = $borderStyleId;
	}
	
	/**
	 * @param string $color
	 */
	public function setColor($color) {
		$this->color = $color;
	}
	
	/**
	 * @param string $style
	 */
	public function setStyle($style) {
		$this->style = $style;
	}
	
	/**
	 * @param int $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}
	
	/**
	 * @param int $bookId
	 */
	public function setBookId($bookId){
		$this->bookId = $bookId ;
	}
	
	
	public function save() {
		
	}
	
	
	public function load() {
		
	}
	
	/**
	 * Creates a json string from an php object 
	 *
	 */
	public function toJson(){
		return json_encode($this) ;
	}
	
	/**
	 * Creates a LayoutStyle from a json object 
	 *
	 * @param string $json
	 * 
	 */
	public function fromJson($json_obj){
		foreach (get_class_vars() as $var) {
			$this->$var  = $json_obj->$var ;  	
		}
	}
	

}
?>