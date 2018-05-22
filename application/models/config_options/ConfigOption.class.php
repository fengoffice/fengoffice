<?php

  /**
  * ConfigOption class
  * Generated on Mon, 27 Feb 2006 14:00:37 +0100 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class ConfigOption extends BaseConfigOption {
    
    /**
    * Config handler instance
    *
    * @var ConfigHandler
    */
    private $config_handler;
    
    /**
    * Return display name
    *
    * @param void
    * @return string
    */
    function getDisplayName() {
      return lang('config option name ' . $this->getName());
    } // getDisplayName
    
    /**
    * Return display description
    *
    * @param void
    * @return string
    */
    function getDisplayDescription() {
      return Localization::instance()->lang('config option desc ' . $this->getName(), '');
    } // getDisplayDescription
    
    /**
    * Return config handler instance
    *
    * @param void
    * @return ConfigHandler
    */
    function getConfigHandler() {
      if($this->config_handler instanceof ConfigHandler) return $this->config_handler;
      
      $handler_class = trim($this->getConfigHandlerClass());
      if(!$handler_class) throw new Error('Handler class is not set for "' . $this->getName() . '" config option');
      
      $handler = new $handler_class();
      if(!($handler instanceof ConfigHandler)) throw new Error('Handler class for "' . $this->getName() . '" config option is not valid');
      
      $handler->setConfigOption($this);
      $handler->setRawValue(parent::getValue());
      $this->config_handler = $handler;
      return $this->config_handler;
    } // getConfigHandler
  
    /**
    * Return config value
    *
    * @access public
    * @param void
    * @return mixed
    */
    function getValue() {
      $handler = $this->getConfigHandler();
      $handler->setRawValue(parent::getValue());
      return $handler->getValue();
    } // getValue
    
    /**
    * Set option value
    *
    * @access public
    * @param mixed $value
    * @return boolean
    */
    function setValue($value) {
      $handler = $this->getConfigHandler();
      $handler->setValue($value);
      return parent::setValue($handler->getRawValue());
    } // setValue
    
    /**
    * Render this control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
    	if ($this->getConfigHandlerClass() == '') {
    		return '';
    	}
    	$handler = $this->getConfigHandler();
    	return $handler->render($control_name);
    } // render
    
    function save(){
    	parent::save();
    	ConfigOptions::instance()->updateConfigOptionCache($this);
    }
    
  } // ConfigOption 

?>