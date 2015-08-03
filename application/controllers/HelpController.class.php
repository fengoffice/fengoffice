<?php

/**
 * Help controller
 *
 * @version 1.0
 * @author Carlos Palma <chonwil@gmail.com>
 */
class HelpController extends ApplicationController {
 	
	/* Construct the HelpController
	 *
	 * @access public
	 * @param void
	 * @return HelpController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct
	
	function index() {
		
	}
	
	function help_options(){
		$show_context_help = user_config_option('show_context_help', 'until_close',logged_user()->getId());
		$show = true;
		if($show_context_help == 'never') {
			$show = false;
		}
		tpl_assign('show_help', $show);
		ajx_set_panel('help');
		ajx_replace(true);
	}
	
	function show_context_help(){
		$show_context_help = array_var($_GET, 'show_context_help');
		set_user_config_option('show_context_help', $show_context_help, logged_user()->getId());
		ajx_current("empty");
		if ($show_context_help == 'until_close') {
			flash_success(lang('success enable context help'));
		} else {
			flash_success(lang('success disable context help'));
		}
	}
	
	function view_message(){
		
	}
	
	function get_help_content(){
		if(!array_var($_GET, 'template')) return;
		$template = array_var($_GET, 'template');
		ajx_current("empty");
		ajx_extra_data(array("content" => load_help($template), "is_help_data" => 1));
	}
} // HelpController

?>