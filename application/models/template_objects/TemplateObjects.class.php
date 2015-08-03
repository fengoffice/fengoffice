<?php

/**
 *  TemplateObjects class
 *
 * @author Ignacio de Soto
 */
class TemplateObjects extends BaseTemplateObjects {
	
	
	/**
	 * Returns all Objects of a Template
	 *
	 * @param integer $template_id
	 * @return array
	 */
	static function getObjectsByTemplate($template_id) {
		$all = self::findAll(array('conditions' => array('`template_id` = ?', $template_id) ));
		if (!is_array($all)) return array();
		$objs = array();
		foreach ($all as $obj) {
			$o = Objects::findObject($obj->getObjectId());
			if ($o instanceof ContentDataObject) {
				$objs[] = $o;
			}
		
		}
		
		return $objs;
	}
	
	
	static function removeObjectFromTemplates($object) {
		self::delete(array("`object_id` = ?", $object->getId()));
	}
	
	
	/**
	 * Returns all Objects of a Template
	 *
	 * @param integer $template_id
	 * @return array
	 */
	static function deleteObjectsByTemplate($template_id) {
		return self::delete(array('`template_id` = ?', $template_id));
	}
	
	
	static function templateHasObject($template, $object) {
		return self::count(array("`template_id` = ? AND `object_id` = ?",
			$template->getId(), $object->getId())) > 0;
	}
	
} 
