<?php

/**
 *  TemplateObjectProperties class
 *
 * @author Feng Office
 */
class TemplateObjectProperties extends BaseTemplateObjectProperties {
	
	/**
	 * Returns all Properties of a template object
	 *
	 * @param integer $template_id
	 * @param integer $object_id
	 * @return array
	 */
	static function getPropertiesByTemplateObject($template_id, $object_id) {
		return self::instance()->findAll(
			array('conditions' => array('`template_id` = ? AND `object_id` = ?', $template_id, $object_id), 'order' => 'property' )
		);
		
		//@todo - sort by name - and the name depends on whether it is a custom property or a standard property 
	}
	
	/**
	 * Returns template object property value
	 *
	 * @param integer $template_id
	 * @param integer $object_id
	 * @param string $property
	 * @return array
	 */
	static function getTemplateObjectPropertyValue($template_id, $object_id, $property) {
		return self::instance()->findAll(
			array('conditions' => array('`template_id` = ? AND `object_id` = ? AND `property` = ?', $template_id, $object_id, $property) )
		);
	}
	
	 /**
	 * Deletes all object properties from a template
	 *
	 * @param integer $template_id
	 * @return array
	 */
	static function deletePropertiesByTemplate($template_id) {
		return self::instance()->delete(array('`template_id` = ?', $template_id));
	}
	

} // TemplateObjectProperties

?>