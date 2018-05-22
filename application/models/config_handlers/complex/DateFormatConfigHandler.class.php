<?php

  /**
  * Date format
  *
  * @version 1.0
  * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
  */
  class DateFormatConfigHandler extends ConfigHandler {
  
    /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $option_attributes = $this->getValue() == 'd/m/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd/mm/yyyy', 'd/m/Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm/d/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm/dd/yyyy', 'm/d/Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y/m/d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy/mm/dd', 'Y/m/d', $option_attributes);
      
      $option_attributes = $this->getValue() == 'd-m-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd-mm-yyyy', 'd-m-Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm-d-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm-dd-yyyy', 'm-d-Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y-m-d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy-mm-dd', 'Y-m-d', $option_attributes);
      
      $option_attributes = $this->getValue() == 'd.m.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd.mm.yyyy', 'd.m.Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'm.d.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm.dd.yyyy', 'm.d.Y', $option_attributes);
      
      $option_attributes = $this->getValue() == 'Y.m.d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy.mm.dd', 'Y.m.d', $option_attributes);
      
      return select_box($control_name, $options);
    } // render
    
    /**
     * 
     * @param type $control_name
     * @return type
     */
    public static function renderExtended($control_name,$selected,$attr) {
      $options = array();
      
      $options[] = option_tag(lang('Select Format'), '');
      
      $option_attributes = $selected == 'd/m/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd/mm/yyyy', 'd/m/Y', $option_attributes);
      
      $option_attributes = $selected == 'm/d/Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm/dd/yyyy', 'm/d/Y', $option_attributes);
      
      $option_attributes = $selected == 'Y/m/d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy/mm/dd', 'Y/m/d', $option_attributes);
      
      $option_attributes = $selected == 'd-m-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd-mm-yyyy', 'd-m-Y', $option_attributes);
      
      $option_attributes = $selected == 'm-d-Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm-dd-yyyy', 'm-d-Y', $option_attributes);
      
      $option_attributes = $selected == 'Y-m-d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy-mm-dd', 'Y-m-d', $option_attributes);
      
      $option_attributes = $selected == 'd.m.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd.mm.yyyy', 'd.m.Y', $option_attributes);
      
      $option_attributes = $selected == 'm.d.Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm.dd.yyyy', 'm.d.Y', $option_attributes);
      
      $option_attributes = $selected == 'Y.m.d' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy.mm.dd', 'Y.m.d', $option_attributes);
      
      $option_attributes = $selected == 'Y' ? array('selected' => 'selected') : null;
      $options[] = option_tag('yyyy', 'Y', $option_attributes);
      
      $option_attributes = $selected == 'd' ? array('selected' => 'selected') : null;
      $options[] = option_tag('dd', 'd', $option_attributes);
      
      $option_attributes = $selected == 'm' ? array('selected' => 'selected') : null;
      $options[] = option_tag('mm', 'm', $option_attributes);
      
      
      return select_box($control_name, $options,$attr);
    } // render
    
  
  } // DateFormatConfigHandler

?>