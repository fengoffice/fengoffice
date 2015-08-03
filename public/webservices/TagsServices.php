<?php
define('CONSOLE_MODE', true);
require_once('WebServicesBase.php');

class TagsServices extends WebServicesBase {
	
	function TagsServices() {
		
		$this->__dispatch_map['listTags'] = array(
            "in"  => array("username" => "string", "password" => "string"),
            "out" => array("list" => "string")
		);
		
		$this->__typedef['ArrayOfString'] = array(array('data' => 'string'));
		
		$this->WebServicesBase();
	}
	
	function listTags($username, $password) {
		if ($this->loginUser($username, $password)) {
			$tags = Tags::getTagNames();
			$this->initXml('tags');
			if (is_array($tags)) {
				foreach ($tags as $tag) {
					$this->instance->startElement('tag');
					$this->instance->text($tag['name']);
					$this->instance->endElement();
				}
			}
			$result = $this->endXml();
		} else $result = '';
		
		return $result;
	}
	
}

$server = new SOAP_Server();
$webservice = new TagsServices();

$server->addObjectMap($webservice, 'http://schemas.xmlsoap.org/soap/envelope/');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
     $server->service($HTTP_RAW_POST_DATA);
} else {
     $disco = new SOAP_DISCO_Server($server, 'FengWebServices_tags');
     header("Content-type: text/xml");

     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'wsdl') == 0) {
         echo $disco->getWSDL(); // show only the WSDL/XML output if ?wsdl is set in the address bar
     } else {
         echo $disco->getDISCO();
     }
}

?>