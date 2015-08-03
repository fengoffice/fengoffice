<?php

  /**
  * ContactConfigOption class
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class ContactConfigOption extends BaseContactConfigOption {
    
    /**
    * Config handler instance
    *
    * @var ConfigHandler
    */
    private $config_handler;
    
    /**
     * @var array Cached values for the option. Order: USER / WORKSPACE / Config option value
     */
    protected $option_values_cache = array();
    
    /**
    * Return display name
    *
    * @param void
    * @return string
    */
    function getDisplayName() {
      return lang('user config option name ' . $this->getName());
    } // getDisplayName
    
    /**
    * Return display description
    *
    * @param void
    * @return string
    */
    function getDisplayDescription() {
      return Localization::instance()->lang('user config option desc ' . $this->getName(), '');
    } // getDisplayDescription
    
    function useDefaultValue() {
    	$this->getConfigHandler()->setRawValue($this->getDefaultValue());
    }
    
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
      $handler->setRawValue($this->getContactValue(logged_user() instanceof Contact ? logged_user()->getId() : 0));
      $this->config_handler = $handler;
      return $this->config_handler;
    } // getConfigHandler
  
    /**
     * Returns user value for the config option, or else default is returned
     *
     */
    function getContactValue($user_id = 0, $default = null, $member = 0){
    	//Return value if found
    	if (!is_null($this->getContactValueCached($user_id))) {
       		return $this->getContactValueCached($user_id);
       	} else {
    		if (!$this->getContactValueNotFoundCache($user_id)){
	    		$val = ContactConfigOptionValues::findById(array('option_id' => $this->getId(), 'contact_id'=>$user_id, 'member_id' => $member));
	    		if ($val instanceof ContactConfigOptionValue){
	    			$this->updateContactValueCache($user_id,$val->getValue());
	    			return $val->getValue();
	    		} else $this->updateContactValueCache($user_id, null);
    		}
    	}
    	
    	//Value not found, return default if searching for default user
    	if ($user_id == 0){
    		//Return default settings
    		if (!is_null($default))
    			return $default;
    		else
    			return $this->getDefaultValue();
    	} 
    	
    	//Search global preferences
    	if (!is_null($this->getContactValueCached(0))) 
    		return $this->getContactValueCached(0);
    	else {
    		if (!$this->getContactValueNotFoundCache(0)){
	    		$val = ContactConfigOptionValues::findById(array('option_id' => $this->getId(), 'contact_id'=> 0));
	    		if ($val instanceof ContactConfigOptionValue){
					$this->updateContactValueCache(0,$val->getValue());
	    			return $val->getValue();
	    		} else $this->updateContactValueCache(0, null);
    		}
    	}
    	
    	//Nothing found, return default settings
    	if (!is_null($default))
    		return $default;
    	else
    		return $this->getDefaultValue();
    }
    
    private function updateContactValueCache($user_id, $value){
    	if (!array_key_exists($user_id, $this->option_values_cache))
    		$this->option_values_cache[$user_id] = array();
    	$this->option_values_cache[$user_id] = $value;
    }
    
    function getContactValueCached($user_id = 0){
    	if (array_key_exists($user_id, $this->option_values_cache))
    		return $this->option_values_cache[$user_id];
    	return null;
    }
    
    /**
     * Returns true if the value was already searched in the database but not found.
     * 
     * @param $user_id
     * @param $workspace_id
     * @return unknown_type
     */
    function getContactValueNotFoundCache($user_id = 0){
    	if (array_key_exists($user_id, $this->option_values_cache))
    		return true;
    	return false;
    }
    
    /**
     * Set value  
     *
     */
    function setContactValue($new_value, $user_id = 0){
    	$val = ContactConfigOptionValues::findById(array('option_id' => $this->getId(), 'contact_id' => $user_id));
        if(!$val){
                // if value was not found, create it
                $val = new ContactConfigOptionValue();
                $val->setOptionId($this->getId());
                $val->setContactId($user_id);
        }
        $val->setValue($new_value);
        $val->save();
        $this->updateContactValueCache($user_id,$val->getValue());
    }
    
    /**
    * Return config default value
    *
    * @access public
    * @param void
    * @return mixed
    */
    function getValue() {
      $handler = $this->getConfigHandler();
      $handler->setRawValue(parent::getDefaultValue());
      return $handler->getValue();
    } // getDefaultValue
    
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
      return parent::setDefaultValue($handler->getRawValue());
    } //  setDefaultValue
    
    /**
    * Render this control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $handler = $this->getConfigHandler();
      return $handler->render($control_name);
    } // render
    
    function save(){
    	parent::save();
    	ContactConfigOptions::instance()->updateConfigOptionCache($this);
    }
    
  } // ContactConfigOption 

?>