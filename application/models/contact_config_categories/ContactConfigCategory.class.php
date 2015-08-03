<?php

/**
 * ConfigCategory class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class ContactConfigCategory extends BaseContactConfigCategory {

	/**
	 * Cached array of config options listed per user permissions
	 *
	 * @var array
	 */
	private $user_ws_config_options;

	/**
	 * Cached number of config options that current user can see
	 *
	 * @var integer
	 */
	private $count_user_ws_config_options;

	/**
	 * In DB we store uniqe name. This function will convert that name to the catetory display name in propert language
	 *
	 * @param void
	 * @return string
	 */
	function getDisplayName() {
		return lang('user ws config category name ' . $this->getName());
	} // getDisplayName

	/**
	 * Get DB description from lang based on category name
	 *
	 * @param void
	 * @return string
	 */
	function getDisplayDescription() {
		return Localization::instance()->lang('user ws config category desc ' . $this->getName(), '');
	} // getDisplayDescription

	// ---------------------------------------------------
	//  User Workspace options
	// ---------------------------------------------------

	/**
	 * Return user ws options array
	 *
	 * @param boolean $include_system_options Include system options in the result
	 * @return array
	 */
	function getContactOptions($include_system_options = false) {
		if(is_null($this->user_ws_config_options)) {
			$this->user_ws_config_options = ContactConfigOptions::getOptionsByCategory($this, $include_system_options);
		} // if
		return $this->user_ws_config_options;
	} // getContactOptions

	/**
	 * Return the number of option in category that logged user can see
	 *
	 * @param boolean $include_system_options Include system options
	 * @return integer
	 */
	function countContactOptions($include_system_options = false) {
		if(is_null($this->count_user_ws_config_options)) {
			$this->count_user_ws_config_options = ContactConfigOptions::countOptionsByCategory($this, $include_system_options);
		} // if
		return $this->count_user_ws_config_options;
	} //  countContactOptions

	/**
	 * Returns true if this category does not have any options to show to the user
	 *
	 * @param void
	 * @return boolean
	 */
	function isEmpty() {
		return $this->countContactOptions() < 1;
	} // isEmpty

	// ---------------------------------------------------
	//  Urls
	// ---------------------------------------------------

	/**
	 * View config category
	 *
	 * @param void
	 * @return null
	 */
	function getUpdateUrl() {
		return get_url('contact', 'update_user_preferences', $this->getId());
	} // getUpdateUrl
	
	function getDefaultUpdateUrl() {
		return get_url('config', 'update_default_user_preferences', $this->getId());
	}

} // ConfigCategory

?>