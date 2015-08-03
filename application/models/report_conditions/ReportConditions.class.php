<?php

/**
 *   ReportConditions class
 *
 * 
 */

class ReportConditions extends BaseReportConditions {

	/**
	 * Return specific condition
	 *
	 * @param $id
	 * @return ReportCondition
	 */
	static function getCondition($id) {
		return self::findOne(array(
			'conditions' => array("`id` = ?", $id)
		)); // findOne
	} //  getCondition
	
	/**
	 * Return all report conditions
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditions($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ?", $report_id)
		)); // findAll
	} //  getAllReportConditions
	
	/**
	 * Return all report conditions for fields
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditionsForFields($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ? AND field_name != '' AND custom_property_id = 0", $report_id)
		)); // findAll
	} //  getAllReportConditionsForFields
	
	/**
	 * Return all report conditions for fields
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportConditionsForCustomProperties($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ? AND custom_property_id > 0", $report_id)
		)); // findAll
	} //  getAllReportConditionsForFields
	
	/**
	 * Return specific condition with that field name for the given report
	 *
	 * @param $reportid
	 * @param $field
	 * @return ReportCondition
	 */
	static function getReportConditionField($reportid,$field) {
		return self::findOne(array(
			'conditions' => array('field_name = \'' . $field.'\'' ,'report_id = \'' . $reportid . '\'')
		)); // findOne
	} //  getReportConditionField
	
} // ReportConditions

?>