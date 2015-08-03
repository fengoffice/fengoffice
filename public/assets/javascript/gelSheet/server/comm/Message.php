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
include_once 'MessageHandler.php';

class ContentList {
	private $contents;	
	public function __construct($name=null, $value=null) {
		$this->contents = array();
		if(isset($name))
			$this->contents[$name] = $value;
	
	}
	
	public function getContents() {
		return $this->contents;
	}
	
	public function addContent($name, $value) {
		$this->contents[$name] = $value;
	}
	
	public function addContentList($list) {
		if(isset($list))
			$this->contents = array_merge($this->contents,$list->getContents());
	}
	
	public function toJSON(){		
		$json = "";	
		foreach ($this->contents as $key => $value){
			if($key !="data")					//TODO: cambiar a campo de objetos
				$json .= "{'$key':'".addslashes($value)."'},";
			else 
				$json .= "$value,";
			 	
		}
		
		$json[strlen($json)-1] = " ";
		
//		if(count($this->contents)>1)
			$json = "[$json]";
		
		return $json;
	}

}

class Message extends Exception {
	private $type;
	private $contentList;
	private $debugging;
	private $data;
	
	public function __construct($type, $number, $description = null, $contentList = null) {
		parent::__construct ($description, $number );
		global $debugging;
		$this->type = $type;
		$this->contentList = $contentList;
		$this->next = null;
		$this->debugging = $debugging;		
	}
	
	public function __destruct(){
		if($this->debugging && ($this->type == "ERROR" || $this->type == "WARNING")){
			$this->addContentElement("File",$this->file);
			$this->addContentElement("Line",$this->line);
		}
//		MessageHandler::addMessage($this);
		echo $this->toJSON();
	}
	
	public function setData($data){
		$this->data = $data;
	}
	
	public function getData(){
		return $this->data;
	}
	
	public function getType(){
		return $this->type;
	}
	public function isDebugging(){
		return $this->debugging;
	}
	
	public function addMessage($message) {
			$this->next = $message;
	}
	
	public function getContentElement() {
		return $this->contentList;
	}
	
	public function setContentElement($contentElement) {
		$this->contentList = $contentElement;
	}
	
	public function addContentElement($name,$value) {
		if(isset($this->contentList))
			$this->contentList->addContent($name,$value);
		else
			$this->contentList = new ContentList($name,$value);
	}
	
	public function addContentList($list) {
		if(isset($this->contentList))
			$this->contentList->addContentList($list);
		else
			$this->contentList = $list;
	}

	public function getErrorType() {
		return $this->type;
	}
	
	public function toJSON() {
		if ($this->contentList)
			$list = $this->contentList->toJSON ();
		else
			$list = "null";
		
		if (isset($this->next))
			$next = $this->next->toJSON ();
		else
			$next = "null";
		
		if(isset($this->data))
			$data = $this->data;
		else
			$data = "null"; 
			
		if($this->type == "SUCCESS")
			$success = 'true';
		else 
			$success = 'false';
			
		return "({'success':$success,'type':'$this->type','number':$this->code,'description':'".addslashes($this->message)."','data':$data,'contents':$list,'next':$next})";
	}
}

class GsError extends Message {
	public function __construct($number = null, $description = null, $contentList = null) {
		parent::__construct ( "ERROR", $number, $description, $contentList );
	}
}

class Warning extends Message {
	public function __construct($number = null, $description = null, $contentList = null) {
		parent::__construct ( "WARNING", $number, $description, $contentList );
	}
}

class Success extends Message {
	public function __construct($description = null, $data = null) {
		parent::__construct ( "SUCCESS", 0, $description );
		$this->setData($data);
	}
}

class Notice extends Message {
	public function __construct($number = null, $description = null, $contentList = null) {
		parent::__construct ( "NOTICE", $number, $description, $contentList );
	}

}
?>
