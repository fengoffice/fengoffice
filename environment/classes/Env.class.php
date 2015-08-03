<?php

class Env {

	/**
	 * Check if environment is in debug mode
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	static function isDebugging() {
		return defined('DEBUG') && DEBUG;
	} // isDebugging
	
	static function isDebuggingDB() {
		return defined('DEBUG') && DEBUG && defined('DEBUG_DB') && DEBUG_DB;
	}
	
	static function isDebuggingTime() {
		return defined('DEBUG') && DEBUG && defined('DEBUG_TIME') && DEBUG_TIME;
	}

	/**
	 * Use specific library. This function will look in application directory
	 * first and then in enviroment library folder. If it doesn't finds requested
	 * class in it LibraryDnxError will be raised
	 *
	 * @access public
	 * @param string $library Library name
	 * @return null
	 * @throws LibraryDnxError
	 */
	static function useLibrary($library) {
		static $included = array();
		if(isset($included[$library]) && $included[$library]) return;

		$library_path = ENVIRONMENT_PATH . "/library/$library/";
		if(!file_exists($library_path)) $library_path = ROOT . "/library/$library/";

		if(!is_dir($library_path)) throw new LibraryDnxError($library);

		// Call init library file if it exists
		$library_init_file = $library_path . $library . '.php';
		if(is_file($library_init_file)) include_once $library_init_file;

		$included[$library] = true;
	} // useLibrary

	/**
	 * Include library error class if class is not already included
	 *
	 * @access public
	 * @param string $error_class
	 * @param string $library Library name
	 * @return boolean
	 */
	static function useLibraryError($error_class, $library) {
		if(class_exists($error_class)) return true;

		$expected_path = ENVIRONMENT_PATH . "/library/$library/errors/$error_class.class.php";
		if(is_file($expected_path)) {
			include_once $expected_path;
			return true;
		} else {
			throw new FileDnxError($expected_path);
		} // if
	} // useLibraryError

	/**
	 * Show nice error output.
	 *
	 * @access public
	 * @param Error $error
	 * @param boolean $die Die when done, default value is true
	 * @return null
	 */
	static function dumpError($error, $die = true) {
		static $css_rendered = false;

		// Check error instance...
		if(!instance_of($error, 'Error')) {
			print '$error is not a valid <i>Error</i> instance!' . $error;
			return;
		} // if

		// OK, include template...
		include ENVIRONMENT_PATH . '/templates/dump_error.php';

		// Die?
		if($die) {
			die();
		} // if
	} // dumpError

	/**
	 * Contruct controller and execute specific action
	 *
	 * @access public
	 * @param string $controller_name
	 * @param string $action
	 * @return null
	 */
	static function executeAction($controller_name, $action) {
   		$max_users = config_option('max_users');
		if ($max_users && Contacts::count() > $max_users) {
	        echo lang("error").": ".lang("maximum number of users exceeded error");
	        return;
    	}
		ajx_check_login();
		
		Env::useController($controller_name);

		$controller_class = Env::getControllerClass($controller_name);
		if(!class_exists($controller_class, false)) {
			throw new ControllerDnxError($controller_name);
		} // if

		$controller = new $controller_class();
		if(!instance_of($controller, 'Controller')) {
			throw new ControllerDnxError($controller_name);
		} // if

		if (is_ajax_request()) {
			// if request is an ajax request return a json response
			
			// execute the action
			$controller->setAutoRender(false);
			$controller->execute($action);
			
			// fill the response
			$response = AjaxResponse::instance();
			if (!$response->hasCurrent()) {
				// set the current content
				$response->setCurrentContent("html", $controller->getContent(), page_actions(), ajx_get_panel());
			}
			$response->setEvents(evt_pop());
			$error = flash_pop('error');
			$success = flash_pop('success');
			if (!is_null($error)) {
				$response->setError(1, clean($error));
			} else if (!is_null($success)) {
				$response->setError(0, clean($success));
			}
			
			// display the object as json

			tpl_assign("object", $response);
			$content = tpl_fetch(Env::getTemplatePath("json"));
			tpl_assign("content_for_layout", $content);
			TimeIt::start("Transfer");
			if (is_iframe_request()) {
				tpl_display(Env::getLayoutPath("iframe"));
			} else {
				tpl_display(Env::getLayoutPath("json"));
			}
			TimeIt::stop();
		} else {
			return $controller->execute($action);
		}
	} // executeAction

	/**
	 * Find and include specific controller based on controller name
	 *
	 * @access public
	 * @param string $controller_name
	 * @return boolean
	 * @throws FileDnxError if controller file does not exists
	 * 
	 */
	static function useController($controller_name) {
		$controller_class = Env::getControllerClass($controller_name);
		
		// Search For Plugin. Execute it if found
		$plugins = Plugins::instance()->getActive() ;
		$pluginName = null ; 
		foreach ($plugins as $plugin) {
			/* @var $plugin Plugin  */
			$systemName = $plugin->getSystemName() ;
			//$controller_file = ROOT."/plugins/$pluginName/application/controllers/$controller_class.class.php";
			$controller_file = $plugin->getControllerPath()."$controller_class.class.php";
			//echo $controller_file ."<br/>";
			if (is_file($controller_file)){
				$pluginName = $plugin ;
				Plugins::instance()->setCurrent($plugin) ;
				include_once $controller_file;
				return true ;
			}
		}
		
		
		// Plugin not found - Search for core controller
		
		
		if(class_exists($controller_class, false)) return true;

		$controller_file = APPLICATION_PATH . "/controllers/$controller_class.class.php";
		if(is_file($controller_file)) {
			include_once $controller_file;
			return true;
		} else {
			throw new FileDnxError($controller_file, "Controller '$controller_name' does not exists (expected location '$controller_file')");
		} // if
	} // useController

	/**
	 * Use specific helper
	 *
	 * @access public
	 * @param string $helper Helper name
	 * @return boolean
	 * @throws FileDnxError
	 */
	static function useHelper($helper, $plugin = null) {
		$helper_file = Env::getHelperPath($helper, $plugin);

		// If we have it include, else throw exception
		if(is_file($helper_file)) {
			include_once $helper_file;
			return true;
		} else {
			throw new FileDnxError($helper_file, "Helper '$helper' does not exists (expected location '$helper_file')");
		} // if
	} // useHelper

	/**
	 * Check if specific helper exists
	 *
	 * @access public
	 * @param string $helper
	 * @return boolean
	 */
	static function helperExists($helper, $plugin = null) {
		return is_file(self::getHelperPath($helper, $plugin));
	} // helperExists

	/**
	 * Return controller name based on controller class
	 *
	 * @access public
	 * @param string $controller_class
	 * @return string
	 */
	static function getControllerName($controller_class) {
		return Inflector::underscore( substr($controller_class, 0, strlen($controller_class) - 10) );
	} // getControllerName

	/**
	 * Return controller class based on controller name
	 *
	 * @access public
	 * @param string $controller_name
	 * @return string
	 */
	static function getControllerClass($controller_name) {
		return Inflector::camelize($controller_name) . 'Controller';
	} // getControllerClass

	/**
	 * Return path of specific template
	 *
	 * @access public
	 * @param string $template
	 * @param string $controller_name
	 * @return string
	 */
	static function getTemplatePath($template, $controller_name = null, $plugin = null ) {
		if ($plugin) {
			$template_path = ROOT."/plugins/$plugin/application/views/$controller_name/$template.php";
			return $template_path;
		}
		
		if ($plugin = Plugins::instance()->getCurrent()){
			//$template_path = ROOT."/plugins/$plugin/application/views/$controller_name/$template.php";
			$template_path = $plugin->getViewPath()."$controller_name/$template.php";
			if ( is_file($template_path) ) {
				
				return $template_path ;
			}
		}
		
		if($controller_name) {
			return APPLICATION_PATH . "/views/$controller_name/$template.php";
		} else {
			return APPLICATION_PATH . "/views/$template.php";
		} // if
	} // getTemplatePath

	/**
	 * Return layout
	 *
	 * @access public
	 * @param string $layout
	 * @return string
	 */
	static function getLayoutPath($layout) {
		return APPLICATION_PATH . "/layouts/$layout.php";
	} // getLayoutPath

	/**
	 * Return path of specific helper
	 *
	 * @access public
	 * @param string $helper
	 * @return string
	 */
	static function getHelperPath($helper, $plugin = null) {
		if (is_null($plugin))
			return APPLICATION_PATH . "/helpers/$helper.php";
		else
			return ROOT . "/plugins/$plugin/application/helpers/$helper.php";
	} // getHelperPath

	/**
	 * Return default base URL based on owner company status
	 *
	 * @access private
	 * @param void
	 * @return string
	 */
	private function getDefaultBase() {
		return ROOT_URL;
	} // getDefaultBase

} // Env

// ---------------------------------------------------
//  This routines are used a lot in controllers and
//  templates so here are shortcut methods
// ---------------------------------------------------

/**
 * Interface to Env::getTemplatePath() function
 *
 * @access public
 * @param string $template Template name
 * @param string $controller_name
 * @return string
 */
function get_template_path($template, $controller_name = null, $plugin = null) {
	return Env::getTemplatePath($template, $controller_name, $plugin);
} // get_template_path

?>