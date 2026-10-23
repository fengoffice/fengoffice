<?php

  /**
  * Class that handles single dimension selection config values
  *
  * @version 1.0 
  */
  class DimensionsSelectorConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
  	function render($control_name) {
  		return $this->do_render($control_name);
  	}
  	
    function do_render($control_name, $additional_params=array()) {
        $is_default = array_var($additional_params, 'is_default');
        $value = $this->getValue();
        
        $dimensions = Dimensions::instance()->findAll(array('conditions' => "`code` != 'feng_persons'"));
        $enabled_dimension_ids = config_option('enabled_dimensions');
        
        $onchange_fn = array_var($additional_params, 'onchange_fn');
        
        $options = array();
        $options[] = option_tag(lang('none'), 0, $value == 0 ? array('selected' => 'selected') : null);

        // Sort dimensions by name
        usort($dimensions, function($a, $b) {
            return strcasecmp($a->getName(), $b->getName());
        });
       
        foreach ($dimensions as $dim) {
            /* @var $dim Dimension */
            if (!in_array($dim->getId(), $enabled_dimension_ids)) continue;
            $option_attributes = $value == $dim->getId() ? array('selected' => 'selected') : null;
            $options[] = option_tag($dim->getName(), $dim->getId(), $option_attributes);
        }
       
        return select_box($control_name, $options, $attributes);
    }
    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    function rawToPhp($value) {
      return $value;
    }
    
    function phpToRaw($value) {
      if (is_numeric($value) || $value === 0) {
        return $value;
      } else {
        return 0;
      }
    }
  }
