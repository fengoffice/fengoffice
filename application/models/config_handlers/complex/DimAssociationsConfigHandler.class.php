<?php

  /**
  * Let user select where he wants to store uploaded files
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class DimAssociationsConfigHandler extends ConfigHandler {
  
    /**
    * @param string $control_name
    * @return string
    */
    function render($control_name) {
      $options = array();
      
      $config = json_decode($this->getConfigOption()->getOptions());
      $main_dim_id = $config->dim_id;
      $main_mem_type_id = $config->ot_id;
      
      if (!isset($config->no_empty_value) || !$config->no_empty_value) {
      	$options[] = option_tag("", "");
      }
      
      if (isset($config->assoc_dim_id)) {
      	$associations = DimensionMemberAssociations::findAll(array(
      			'conditions' => "dimension_id=$main_dim_id AND object_type_id=$main_mem_type_id
  					AND associated_dimension_id='".$config->assoc_dim_id."' AND associated_object_type_id='".$config->assoc_ot_id."'"));
      } else {
      	$associations = DimensionMemberAssociations::getAllAssociatationsForObjectType($main_dim_id, $main_mem_type_id);
      }
      
      foreach ($associations as $association) {/* @var $association DimensionMemberAssociation */
          
      	$dim_reference = Dimensions::getDimensionById($main_dim_id);
      	$assoc_info = $association->getArrayInfo($dim_reference);
      	
		$option_attributes = $this->getValue() == $association->getId() ? array('selected' => 'selected') : null;
		
		$option_text = $assoc_info['name'];
		
		$options[] = option_tag($option_text, $association->getId(), $option_attributes);
      }
      
      $attributes = array('id' => 'list_' . $this->getConfigOption()->getName());
      
      return select_box($control_name, $options, $attributes);
    } // render
    
  
  } // DimAssociationsConfigHandler


