<?php
/**
 * BaseProjectChart class
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */
abstract class BaseProjectChart extends ProjectDataObject {

	// -------------------------------------------------------
	//  Access methods
	// -------------------------------------------------------

	/**
	 * Return value of 'object_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		return $this->getColumnValue('object_id');
	} // getObjectId()

	/**
	 * Set value of 'object_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setObjectId($value) {
		return $this->setColumnValue('object_id', $value);
	} // setObjectId()


	/**
	 * Return value of 'type_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getTypeId() {
		return $this->getColumnValue('type_id');
	} // getTypeId()

	/**
	 * Set value of 'type_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setTypeId($value) {
		return $this->setColumnValue('type_id', $value);
	} // setTypeId()


	/**
	 * Return value of 'display_id' field
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function getDisplayId() {
		return $this->getColumnValue('display_id');
	} // getDisplayId()

	/**
	 * Set value of 'display_id' field
	 *
	 * @access public
	 * @param integer $value
	 * @return boolean
	 */
	function setDisplayId($value) {
		return $this->setColumnValue('display_id', $value);
	} // setDisplayId()

	/**
	 * Return value of 'show_in_project' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getShowInProject() {
		return $this->getColumnValue('show_in_project');
	} // getShowInProject()

	/**
	 * Set value of 'show_in_project' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setShowInProject($value) {
		return $this->setColumnValue('show_in_project', $value);
	} // setShowInProject()

	/**
	 * Return value of 'show_in_parents' field
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function getShowInParents() {
		return $this->getColumnValue('show_in_parents');
	} // getShowInParents()

	/**
	 * Set value of 'show_in_parents' field
	 *
	 * @access public
	 * @param boolean $value
	 * @return boolean
	 */
	function setShowInParents($value) {
		return $this->setColumnValue('show_in_parents', $value);
	} // setShowInParents()

	/**
	 * Return manager instance
	 *
	 * @access protected
	 * @param void
	 * @return ProjectCharts
	 */
	function manager() {
		if(!($this->manager instanceof ProjectCharts)) $this->manager = ProjectCharts::instance();
		return $this->manager;
	} // manager

} // BaseProjectChart
?>