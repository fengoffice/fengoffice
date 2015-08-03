<?php


class LayoutStyle extends Model {
	
	/**
	 * @var int
	 */
	var $layoutStyleId ;         

	/**
	 * @var int
	 */
	var $bookId; 
	
	/**
	 * @var string
	 */
	var $backgroundColor ;         
	
	/**
	 * @var int
	 */	
	var $borderLeftStyleId ;         

	/**
	 * @var int
	 */
	var $borderRightStyleId ;         

	/**
	 * @var int
	 */
	var $borderTopStyleId ;         

	/**
	 * @var int
	 */
	var $borderBottomStyleId ;
	
	
/*	
	public function save(){
		parent::save();
	}
*/	

	/**
	 * Creates a json string from an php object 
	 *
	 */
	public function toJson(){
		parent::toJson();
	}
	
	/**
	 * Creates a LayoutStyle from a json object 
	 *
	 * @param string $json
	 * 
	 */
	public function fromJson($json_obj){
		parent::fromJson();
	}
	
		
}


/*
$layout = new LayoutStyle(array("BookId" => 3)) ;

$layout->setBackgroundColor("#000000") ;
$layout->setBookId (1) ;
$layout->setBorderBottomStyleId(2);
$layout->setBorderLeftStyleId(3) ;
$layout->setBorderRightStyleId(4) ;
$layout->setBorderTopStyleId(5) ;

$layout->save() ;

exit ; 
$layout = new LayoutStyle() ;

$layout->load(1) ;
$layout->setLayoutStyleId(null) ;
$layout->save() ;
*/

?>