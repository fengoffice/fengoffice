<?php

/**
 * Config controller is responsible for handling all config related operations
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>, Marcos Saiz <marcos.saiz@fengoffice.com>
 */
class ConfigController extends ApplicationController {

	/**
	 * Construct the ApplicationController
	 *
	 * @param void
	 * @return ApplicationController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');

		// Access permissios
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
	} // __construct

	/**
	 * Show and process config category form
	 *
	 * @param void
	 * @return null
	 */
	function update_category() {
		// Access permissios
		if(!can_manage_configuration(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return ;
		} // if
		$category = ConfigCategories::findById(get_id());
		if(!($category instanceof ConfigCategory)) {
			flash_error(lang('config category dnx'));
			$this->redirectToReferer(get_url('administration'));
		} // if

		if($category->isEmpty()) {
			flash_error(lang('config category is empty'));
			$this->redirectToReferer(get_url('administration'));
		} // if

		$options = $category->getOptions(false);
		$categories = ConfigCategories::getAll(false);

		tpl_assign('category', $category);
		tpl_assign('options', $options);
		tpl_assign('config_categories', $categories);

		$submited_values = array_var($_POST, 'options');
		if(is_array($submited_values)) {
			foreach($options as $option) {
                                //update global cache if available
                                if (GlobalCache::isAvailable() && GlobalCache::key_exists('config_option_'.$option->getName())) {					
                                        GlobalCache::delete('config_option_'.$option->getName());					
                                }
                                
                                if($option->getName() == "working_days"){
                                    $new_value = "";
                                    foreach (array_var($submited_values, $option->getName()) as $value){
                                        $new_value .= $value.",";
                                    }
                                    $new_value = substr ($new_value, 0, -1);                                    
                                }else{             
                                    $new_value = array_var($submited_values, $option->getName());
                                    if(is_null($new_value) || ($new_value == $option->getValue())) continue;
                                }
                                
                                $option->setValue($new_value);
                                $option->save();
                                evt_add("config option changed", array('name' => $option->getName(), 'value' => $new_value));
				
			} // foreach
			flash_success(lang('success update config category', $category->getDisplayName()));
			ajx_current("back");
		} // if

	} // update_category

	/**
	 * Default user preferences
	 *
	 */
	function default_user_preferences() {
		tpl_assign('config_categories', ContactConfigCategories::getAll());
	} //list_preferences
	
	
	function configure_widgets_default() {
		$widgets = Widgets::instance()->findAll(array(
			"conditions" => " plugin_id = 0 OR plugin_id IS NULL OR plugin_id IN ( SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0 )",
			"order" => "default_order",
			"order_dir" => "ASC",
		));
		
		$widgets_info = array();
		foreach ($widgets as $widget) {
			$widgets_info[] = $widget->getDefaultWidgetSettings(logged_user());
		}
		
		tpl_assign('widgets_info', $widgets_info);
		tpl_assign('default_configuration', true);
		$this->setTemplate(get_template_path('configure_widgets', 'contact'));
	}
	
	function configure_widgets_default_submit() {
		ajx_current("empty");
		
		$widgets_data = array_var($_POST, 'widgets');
		try {
			DB::beginWork();
			foreach ($widgets_data as $name => $data) {
				$widget = Widgets::instance()->findOne(array('conditions' => array('name = ?', $name)));
				if (!$widget instanceof Widget) continue;
				
				$widget->setDefaultOrder($data['order']);
				$widget->setDefaultSection($data['section']);
				$widget->save();
				
				if (isset($data['options']) && is_array($data['options'])) {
					foreach ($data['options'] as $opt_name => $opt_val) {
						$contact_widget_option = ContactWidgetOptions::instance()->findOne(array('conditions' => array('contact_id=0 AND widget_name=? AND `option`=?',$name,$opt_name)));
						if (!$contact_widget_option instanceof ContactWidgetOption) continue;
						$contact_widget_option->setValue($opt_val);
						$contact_widget_option->save();
					}
				}
			}
			DB::commit();
			evt_add('reload tab panel', 'overview-panel');
			ajx_current("back");
		} catch (Exception $e) {
			flash_error($e->getMessage());
			DB::rollback();
		}
	}
	

	/**
	 * Update default user preferences
	 *
	 */
	function update_default_user_preferences(){
		$category = ContactConfigCategories::findById(get_id());
		if(!($category instanceof ContactConfigCategory)) {
			flash_error(lang('config category dnx'));
			$this->redirectToReferer(get_url('user','card'));
		} // if

		if($category->isEmpty()) {
			flash_error(lang('config category is empty'));
			$this->redirectToReferer(get_url('user','card'));
		} // if

		$options = $category->getContactOptions(false);
		$categories = ContactConfigCategories::getAll(false);

		tpl_assign('category', $category);
		tpl_assign('options', $options);
		tpl_assign('config_categories', $categories);

		$submited_values = array_var($_POST, 'options');
		if (is_array($submited_values)) {
			try {
				DB::beginWork();
				foreach ($options as $option) {
				// update global cache if available					
					if (GlobalCache::isAvailable()) {							
						GlobalCache::delete('user_config_option_def_'.$option->getName());
					}
					
					$new_value = array_var($submited_values, $option->getName());
					if (is_null($new_value) || ($new_value == $option->getValue())) continue;

					$option->setValue($new_value);
					$option->save();
					
					if (!user_has_config_option($option->getName())) {
						evt_add('user preference changed', array('name' => $option->getName(), 'value' => $new_value));
					}
				} // foreach
				DB::commit();
				flash_success(lang('success update config value', $category->getDisplayName()));
				ajx_current("back");
			} catch (Exception $ex) {
				DB::rollback();
				flash_success(lang('error update config value', $category->getDisplayName()));
			}
		} // if
	} // update_default_user _preferences

	/**
	 * Remove Getting Started widget from dashboard
	 *
	 */
	function remove_getting_started_widget(){
		try{
			DB::beginWork();
			$option = ContactConfigOptions::getByName('show getting started widget');
			$option->setUserValue(0, logged_user()->getId());
			$option->save();
			DB::commit();
			ajx_current('reload');
		}catch (Exception $ex){
			DB::rollback();
		}
	}
} // ConfigController

?>