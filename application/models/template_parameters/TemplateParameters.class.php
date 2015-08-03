<?php

/**
 *  TemplateParameters class
 *
 * @author Pablo Kamil
 */
class TemplateParameters extends BaseTemplateParameters {
	
	/**
	 * Returns all Parameters of a Template
	 *
	 * @param integer $template_id
	 * @return array
	 */
	static function getParametersByTemplate($template_id) {
		return self::findAll(array('conditions' => array('`template_id` = ?', $template_id) ));
	}
	
	/**
	 * Deletes all Parameters of a Template
	 *
	 * @param integer $template_id
	 * @return array
	 */
	static function deleteParametersByTemplate($template_id) {
		return self::delete(array('`template_id` = ?', $template_id));
	}

} // TemplateParameters

?>