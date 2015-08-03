<?php
define('CONSOLE_MODE', true);
require_once('WebServicesBase.php');

class WorkspacesServices extends WebServicesBase {
	
	function WorkspacesServices() {
		
		$this->__dispatch_map['listWorkspaces'] = array(
            "in"  => array("username" => "string", "password" => "string"),
			"out" => array("list" => "string")
		);
		
		$this->WebServicesBase();
	}
	
	function zip_and_encode64($content) {
		$zip = new ZipArchive();
		$tmp_xml_path = "".rand();
		$tmp_zip_path = $tmp_xml_path . "xml";
		
		$xml_tmp = fopen($tmp_xml_path, "w");
		fwrite($xml_tmp, $content);
		fclose($xml_tmp);
		
		$zip->open($tmp_zip_path, ZipArchive::OVERWRITE);
		$zip->addFile($tmp_xml_path, "workspaces.xml");
		$zip->close();

		$zipped_content = file_get_contents($tmp_zip_path);

		unlink($tmp_zip_path);
		unlink($tmp_xml_path);
		
		return base64_encode($zipped_content);
	}

	function listWorkspaces($username, $password) {
		$result = '';
		if ($this->loginUser($username, $password)) {
			$wspaces = logged_user() != null ? logged_user()->getActiveProjects() : array('No Logged User');
			if (isset($wspaces) && is_array($wspaces)) {
				$activeProjects = array();
				foreach($wspaces as $p) $activeProjects[] = $p->getId();
				$this->initXml('workspaces');
				foreach ($wspaces as $ws) {
					$this->workspace_toxml($ws, $activeProjects);
				}
				$result = $this->endXml();
			}
		}
		return $result;
	}
	
	private function workspace_toxml(Project $ws, $activeProjects) {
        $parentIds = '';
        $i = 1;
        $pid = $ws->getPID($i);
        while ($pid != $ws->getId() && $pid != 0 && $i <= 10) {
        	$coma = $parentIds == '' ? '' : ',';
            if (in_array($pid, $activeProjects)) $parentIds .= $coma . $pid;
            $i++;
            $pid = $ws->getPID($i);
        }
		
		$this->instance->startElement('workspace');
		
		$this->instance->startElement('id');
		$this->instance->text($ws->getId());
		$this->instance->endElement();
		
		$this->instance->startElement('name');
		$this->instance->text($ws->getName());
		$this->instance->endElement();
		
		$this->instance->startElement('description');
		$this->instance->text($ws->getDescription());
		$this->instance->endElement();
		
		$this->instance->startElement('parentids');
		$this->instance->text($parentIds);
		$this->instance->endElement();
		
		$this->instance->endElement();
	}
	
}

$server = new SOAP_Server();
$webservice = new WorkspacesServices();

$server->addObjectMap($webservice, 'http://schemas.xmlsoap.org/soap/envelope/');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
     $server->service($HTTP_RAW_POST_DATA);
} else {
     $disco = new SOAP_DISCO_Server($server, 'FengWebServices_workspaces');
     header("Content-type: text/xml");

     if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'], 'wsdl') == 0) {
         echo $disco->getWSDL(); // show only the WSDL/XML output if ?wsdl is set in the address bar
     } else {
         echo $disco->getDISCO();
     }
}
?>