<?php

/**
 *   ReportColumns class
 *
 * 
 */

class ReportColumns extends BaseReportColumns {

	/**
	 * Return all report columns
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportColumns($report_id) {
		return self::findAll(array(
			'conditions' => array("`report_id` = ?", $report_id)
		)); // findAll
	} //  getAllReportColumns
	
	/**
	 * Return all report column names for fields
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportColumnNamesForFields($report_id) {
		$colNames = array();
		$columns = self::findAll(array(
			'conditions' => array("`report_id` = ? AND `field_name` != '' AND `custom_property_id` = 0", $report_id),
			'order' => '`id` asc'
		)); // findAll
		foreach($columns as $col){
			$colNames[] = $col->getFieldName();
		}
		return $colNames;
	} //  getAllReportColumnNamesForFields
	
	/**
	 * Return all report columns for custom properties
	 *
	 * @param report_id
	 * @return array
	 */
	static function getAllReportColumnsForCustomProperties($report_id) {
		$colCp = array();
		$columns = self::findAll(array(
			'conditions' => array("`report_id` = ? AND `custom_property_id` > 0", $report_id)
		)); // findAll
		foreach($columns as $col){
			$colCp[] = $col->getCustomPropertyId();
		}
		return $colCp;
	} //  getAllReportColumnsForCustomProperties

	/**
	 * Return all report column names
	 *
	 * @param report_id
	 * @return array
	 */
	static function getReportColumnNames($report_id){
		$colNames = array();
		$columns = self::findAll(array(
			'conditions' => array("`report_id` = ?", $report_id)
		)); // findAll
		foreach($columns as $col){
			if($col->getCustomPropertyId() > 0){
				$cp = CustomProperties::getCustomProperty($col->getCustomPropertyId());
				if($cp instanceof CustomProperty){
					$colNames[] = $cp->getName();
				}
			}else{
				$colNames[] = $col->getFieldName();
			}
		}
		return $colNames;
	}//getReportColumnNames

} // ReportColumns

?>