<?php

/**
 * Plugin class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Plugin extends BasePlugin {
	var $systemName = null;
	
	var $metadata = null;
	
	function isActive() {
		return $this->getIsActivated ();
	}
	
	function isInstalled() {
		return $this->getIsInstalled ();
	}
	
	function activate() {
		$this->setIsActivated ( 1 );
		$this->save ();
	}
	
	function deactivate() {
		$null = null;
		Hook::fire('on_plugin_deactivate', array('plugin' => $this->getSystemName()), $null);

		$this->setIsActivated ( 0 );
		$this->save ();
	}
	
	function update() {
		foreach ( $this->getUpdateFunctions () as $updateFunction ) {
			if (function_exists($updateFunction)) {
				call_user_func($updateFunction);
				$tmp_ver = substr($updateFunction, strrpos($updateFunction, "_") + 1);
				$this->setVersion($tmp_ver);
				$this->save();
			}
		}
		// If we need to do data modifications we need to do it in a separate step to ensure that we have the db structure in the latest version
		// do it here, process another file called update_data.php
	}

	/**
	 * Executes the data change functions for the plugin. These functions are stored in an array and are executed in the order they are defined.
	 *
	 * @param array $dataChangeFunctions An array of functions that will be executed. Each function should be named in the format "update_data_<version>"
	 */
	function executeDataChanges($dataChangeFunctions = null) {
		if ($dataChangeFunctions == null) {
			$dataChangeFunctions = $this->getDataChangesFunctions();
		}
		foreach ($dataChangeFunctions as $dataChangeFunction) {
			try {
				call_user_func($dataChangeFunction);
			} catch (Throwable $e) {
				// update() already bumped the version to the latest, which would make this data change
				// look as already executed and it would never be retried. Roll the version back to this
				// step's starting version so the next update run retries it (update functions are guarded
				// with check_column_exists / IF NOT EXISTS so re-running them is safe).
				$parts = explode("_", $dataChangeFunction);
				array_pop($parts); // target version
				$from_version = array_pop($parts);
				if (is_numeric($from_version) && intval($from_version) < intval($this->getVersion())) {
					$this->setVersion($from_version);
					$this->save();
				}
				throw $e;
			}

			$tmpVer = substr($dataChangeFunction, strrpos($dataChangeFunction, "_") + 1);
			if ($tmpVer > $this->getVersion()) {
				$this->setVersion($tmpVer);
				$this->save();
			}
		}
	}
	
	function getSystemName() {
		if (! $this->systemName) {
			$this->systemName = str_replace ( array (' ', '-', '�', '�', '�', '�', '�', '�', '.' ), array ('_' . '_', 'n', 'a', 'e', 'i', 'o', 'u', '' ), strtolower ( $this->getName () ) );
		}
		return $this->systemName;
	}
	
	/**
	 * Returns the path of the controller folder 
	 */
	function getControllerPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/application/controllers/";
	}
	
	function getMetadata() {
		if ($this->metadata === null) {
			$this->scanMetadata ();
		}
		
		return $this->metadata;
	}
	
	/**
	 * Returns the path of the plugin folder  
	 */
	function getHooksPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/hooks/";
	}
	
	/**
	 * Returns the path to the view folder 
	 */
	function getViewPath() {
		return ROOT . "/plugins/" . $this->getSystemName () . "/application/views/";
	}
	
	function getLanguagePath() {
		return PLUGIN_PATH . "/" . $this->getSystemName () . "/language";
	}
	
	function scanMetadata() {
		$metadata = include PLUGIN_PATH . "/" . $this->getSystemName () . "/info.php";
		$this->metadata = $metadata;
	}
	
	/**
	 * @return mixed - false if not update avalable - update function otherwise 	
	 */
	function updateAvailable() {
		$meta = $this->getMetadata ();
		$name = $this->getSystemName ();
		$installedVersion = $this->getVersion ();
		$nextVersion = array_var ( $meta, 'version' );
		return ($installedVersion && ($installedVersion < $nextVersion));
	}
	
	function getUpdateFunctions() {
		$functions = array ();
		$meta = $this->getMetadata ();
		$name = $this->getSystemName ();
		$path = ROOT . "/plugins/$name/update.php";
		$installedVersion = $this->getVersion ();
		$nextVersion = array_var ( $meta, 'version' );
		if ($installedVersion && ($installedVersion < $nextVersion)) {
			if (file_exists ( $path )) {
				include_once $path;
				for($v = $installedVersion; $v < $nextVersion; $v++) {
					$function_name = $this->getSystemName () . "_update_" . $v . "_" . ($v + 1);
					if (function_exists ( $function_name )) {
						$functions[] = $function_name;						
					}
				}
			}
		}
		return $functions;
	}

	function getDataChangesFunctions() {
		$functions = array ();
		$meta = $this->getMetadata ();
		$name = $this->getSystemName ();
		$path = ROOT . "/plugins/$name/data_changes.php";
		$installedVersion = $this->getVersion ();
		$nextVersion = array_var ( $meta, 'version' );
		if ($installedVersion && ($installedVersion < $nextVersion)) {
			if (file_exists ( $path )) {
				include_once $path;
				for($v = $installedVersion; $v < $nextVersion; $v++) {
					$function_name = $this->getSystemName () . "_data_changes_" . $v . "_" . ($v + 1);
					if (function_exists ( $function_name )) {
						$functions[] = $function_name;						
					}
				}
			}
		}
		return $functions;
	}
}