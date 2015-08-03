<?php
//include_once("./config/error.php");

class MessageHandler {
	private static $instancia;
	private $messageList;
	private $lastMessage;

	private function __construct() {
		$this->messageList = null;
		$this->lastMessage = null;
	}
	
	public function __destruct(){
		if(isset($this->messageList))
			echo $this->messageList->toJson();
	}

	/** Devuelve la instancia.
	 * @return La instancia del controlador.
	 */
	private static function getInstance() {
		if (! isset ( self::$instancia ))
			self::$instancia = new MessageHandler ( );

		return self::$instancia;
	}
	
	public static function addMessage($message){
		$sys = MessageHandler::getInstance();
		if (isset($sys->messageList))
			$sys->lastMessage->addMessage ($message );
		else 
			$sys->messageList = $message;
		
		$sys->lastMessage = $message;
		if($message->getType()=="ERROR")
			exit;
	}
	
}
?>
