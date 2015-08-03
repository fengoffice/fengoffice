<?php

class GUIController extends ApplicationController {

	/**
	 * Construct the GUIController
	 *
	 * @access public
	 * @param void
	 * @return GUIController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'dialog');
	} // __construct

	function save_state() {
		$this->setLayout("json");
		$this->setTemplate(get_template_path("json"));
		
		if (!isset($_POST['data'])) {
			$object = array("success" => true);
			tpl_assign("object", $object);
			return;
		}
		$data = $_POST['data'];
		$array = json_decode($data);
		if (!is_array($array) || count($array) <= 0) {
			$object = array("success" => true);
			tpl_assign("object", $object);
			return;
		}
		$query = "INSERT INTO `" . TABLE_PREFIX . "guistate` (`contact_id`, `name`, `value`) VALUES ";
		$queryd = "DELETE FROM `" . TABLE_PREFIX . "guistate` WHERE `contact_id` = " . logged_user()->getId() . " AND `name` IN (";
		$values = "";
		$names = "";
		$id = logged_user()->getId();
		foreach ($array as $a) {
			if ($values != "") {
				$values .= ",";
				$names .= ",";
			}
			$values .= "(" . $id . "," . DB::escape($a->name) . "," . DB::escape($a->value) . ")";
			$names .= DB::escape($a->name);
		}
		$query .= $values;
		if ($names == "") $names = "0";
		$queryd .= $names . ")";
		try {
			DB::execute($queryd);
			DB::execute($query);
			$object = array("success" => true);
			tpl_assign("object", $object);
		} catch (Exception $e) {
			$object = array(
				"success" => false,
				"message" => $e->getMessage()
			);
			tpl_assign("object", $object);
		}
	}
	
	function read_state() {
		$this->setLayout("json");
		$this->setTemplate(get_template_path("json"));
		
		try {
			$data = self::getState();
			$object = array(
				"success" => true,
				"data" => json_encode($data)
			);
			tpl_assign("object", $object);
		} catch (Exception $e) {
			$object = array(
				"success" => false,
				"message" => $e->getMessage()
			);
			tpl_assign("object", $object);
		}
	}
	
	function delete_state() {
		$this->setTemplate(get_template_path('back'));
		ajx_current("empty");
		try {
			$query = "DELETE FROM `" . TABLE_PREFIX . "guistate` WHERE `contact_id` = " . DB::escape(logged_user()->getId());
			DB::executeAll($query);
			flash_success(lang("success reset gui state"));
		} catch (Exception $e) {
			flash_error($e->getMessage());
		}
	}
	
	static function getState() {
		$query = "SELECT `name`, `value` FROM `" . TABLE_PREFIX . "guistate` WHERE `contact_id` = " . DB::escape(logged_user()->getId());
		$rows = DB::executeAll($query);
		$data = array();
		if ($rows) {
			foreach ($rows as $r) {
				$data[] = array(
					"name" => $r["name"],
					"value" => $r["value"]
				);
			}
		}
		return $data;
	}

} // GUIController

?>