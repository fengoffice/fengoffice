<?php

  /**
  * Base application controller
  *
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  abstract class ApplicationController extends PageController {
  
    /**
    * Add application level constroller
    *
    * @param void
    * @return null
    */
    function __construct() {
      parent::__construct();
      $this->addHelper('application');
    } // __construct
        
    /**
    * Set help
    *
    * @access public
    * @param string $template Path of help template
    * @return null
    * @throws FileDnxError if $template file does not exists
    */
    protected function setHelp($template) {
    	/*FIXME $content = array("type" => "html", "data" => load_help($template), "template" => $template);    	
        ajx_extra_data(array('help_content' => $content));*/
    } // setHelp
  
  } // ApplicationController

?>