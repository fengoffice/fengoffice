<?php

/**
 * Administration logs manager class
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class AdministrationLogs extends BaseAdministrationLogs {

	const ADM_LOG_CATEGORY_SYSTEM = 'system';
	const ADM_LOG_CATEGORY_SECURITY = 'security';
	
	
	static function createLog($title, $log_data, $category = null) {
		if(is_null($category)) {
			$category = self::ADM_LOG_CATEGORY_SYSTEM;
		} // if
		if(!self::isValidCategory($category)) {
			throw new Error("'$category' is not valid administration log category");
		} // if

		$log = new AdministrationLog();
		try {
			DB::beginWork();
	
			$log->setTitle($title);
			$log->setLogData($log_data);
			$log->setCategory($category);
			$log->save();
		
			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
		}
		return $log;
	} // createLog

	static function getLastLogs($category = '', $title = '', $log_data = '', $limit = 10, $additional_conds = '') {
		$cat_cond = $category == '' ? "" : " AND `category` = ".DB::escape($category);
		$title_cond = $title == '' ? "" : " AND `title` = ".DB::escape($title);
		$data_cond = $log_data == '' ? "" : " AND `log_data` = ".DB::escape($log_data);
		$conditions = "1=1 $cat_cond $title_cond $data_cond";
		if ($additional_conds != '') $conditions .= " AND $additional_conds";
		
		return self::findAll(array('conditions' => $conditions, 'limit' => $limit, 'order' => '`created_on` DESC'));
	}

	/**
	 * Check if specific category is valid
	 *
	 * @param string $action
	 * @return boolean
	 */
	static function isValidCategory($action) {
		static $valid_actions = null;

		if(!is_array($valid_actions)) {
			$valid_actions = array(
			self::ADM_LOG_CATEGORY_SYSTEM,
			self::ADM_LOG_CATEGORY_SECURITY
			); // array
		} // if

		return in_array($action, $valid_actions);
	} // isValidCategory

} // AdministrationLogs

?>