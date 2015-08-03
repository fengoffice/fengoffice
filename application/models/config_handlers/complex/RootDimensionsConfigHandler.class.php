<?php

  /**
  * Class that handles integer config values
  *
  * @version 1.0
  */
  class RootDimensionsConfigHandler extends ConfigHandler {
    
   /**
    * Render form control
    *
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
       $value =  $this->getValue();
       $dimensions  = Dimensions::instance()->findAll();
       $permission_group_ids = ContactPermissionGroups::getPermissionGroupIdsByContactCSV(logged_user()->getId(),false);
       $out = '' ;
       foreach ($dimensions as $dim) { /* @var $dim Dimension */
			if ( $dim->getOptions(1) && isset($dim->getOptions(1)->hidden) && $dim->getOptions(1)->hidden ) {
				continue ;
			}
       		if (!$dim->getDefinesPermissions() || !$dim->deniesAllForContact($permission_group_ids)) {
	       		if  (array_search($dim->getId(), $value) !== false ){
	       			$checked = 1 ; 	
	       		}else{
	       			$checked = 0 ;
	       		}
	       		$out.='<div class="dimension" >';
	       		$out.=label_tag($dim->getName(), null, false, array('style' => 'display:inline;margin:10px;vertical-align:super;'));
	       		$out.=checkbox_field($control_name.'['.$dim->getId().']',$checked );
	       		$out.='</div >';
       		}
       }
       $out.='<input type="hidden" name="'.$control_name.'[0]" value=" ">';
	   return $out ;	 
    }
    

    
    /**
    * Convert raw value to php
    *
    * @param string $value
    * @return mixed
    */
    function rawToPhp($value) {
      $tmp = explode(",", $value);
      $res = array();
      foreach ($tmp as $val) {
      	if (trim($val) != "") $res[] = $val;
      }
      return $res;
    } // rawToPhp
    
	function phpToRaw($value) {
		if (is_array($value) && count($value)) {
			unset($value[0]);
			return implode(',', array_keys($value));
		}else{
			return $value;
		}
	}

  } 
  
  
  
  