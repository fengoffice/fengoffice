<?php

  /**
  * Template class
  *
  * This class is template wrapper, responsible for forwarding variables to the
  * templates and including them.
  * 
  * @version 1.0
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class Template {
    
    /**
    * Array of template variables
    *
    * @var array
    */
    private $vars = array();
    
    /**
    * Assign specific variable to the template
    *
    * @param string $name Variable name
    * @param mixed $value Variable value
    * @return boolean
    * @throws InvalidParamError
    */
    function assign($name, $value) {
      if(!$trimmed = trim($name)) throw new InvalidParamError('$name', $name, "Variable name can't be empty");
      $this->vars[$trimmed] = $value;
      return true;
    } // assign
    
    /**
     * Return the value of a variable.
     * @param string $name
     * @return mixed
     */
    function getVar($name) {
    	return array_var($this->vars, trim($name));
    }
    
    /**
    * Display template and retur output as string
    *
    * @param string $template Template path (absolute path or path relative to 
    *   the templates dir)
    * @return string
    * @throws FileDnxError
    */
    function fetch($template) {
      ob_start();
      try {
      	TimeIt::start("Template");
        $this->includeTemplate($template);
        TimeIt::stop();
      } catch(Exception $e) {
        ob_end_clean();
        throw $e;
      } // try
      return ob_get_clean();
    } // fetch
    
    /**
    * Display template
    *
    * @param string $template Template path or path relative to templates dir
    * @return boolean
    * @throws FileDnxError
    */
    function display($template) {
      return $this->includeTemplate($template);
    } // display
    
    /**
    * Include specific template
    *
    * @param string $template Template name or path relative to templates dir
    * @return null
    */
    function includeTemplate($__template) {
      if(file_exists($__template)) {
        extract($this->vars, EXTR_SKIP);
        include $__template;
        return true;
      } else {
        throw new FileDnxError($__template, "Template '$__template' doesn't exists");
      } // if
    } // includeTemplate
    
    /**
    * Return template service instance
    *
    * @param void
    * @return Template
    */
    function instance() {
      static $instance;
      if(!instance_of($instance, 'Template')) $instance = new Template();
      return $instance;
    } // instance
  
  } // Template

?>