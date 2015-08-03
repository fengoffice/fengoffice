<?php

/**
 * Abstract upgrade script. Single script is used to upgrade product from $version_from
 * to $verion_to or to execute some code changes regardles of the version
 *
 * @package ScriptUpgrader
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class ScriptUpgraderScript {

	/**
	 * Output object
	 *
	 * @var Output
	 */
	private $output;

	/**
	 * Upgrader object that constructed this upgrade script
	 *
	 * @var ScriptUpgrader
	 */
	private $upgrader;

	/**
	 * Upgrade from version
	 *
	 * @var string
	 */
	private $version_from;

	/**
	 * Upgrader to version
	 *
	 * @var string
	 */
	private $version_to;
	
	/**
	 * Database connection link
	 *
	 * @var resource
	 */
	protected $database_connection = null;

	
	/**
	 * Construct upgrade script
	 *
	 * @param Output $output
	 * @return ScriptUpgraderScript
	 */
	function __construct(Output $output) {
		$this->setOutput($output);
	} // __construct

	/**
	 * Return an array of files and folders to be checked if is writable
	 * 
	 * @return array
	 */
	abstract function getCheckIsWritable();
	
	/**
	 * Return an array of extensions to be checked if are loaded
	 *
	 * @return array
	 */
	abstract function getCheckExtensions();
	
	/**
	 * Execute this script
	 *
	 * @param void
	 * @return boolean
	 */
	abstract function execute();

	/**
	 * Sets the scripts database connection.
	 *
	 * @param resource $dbc
	 */
	function setDatabaseConnection($dbc) {
		$this->database_connection = $dbc;
	}
	
	/**
	 * Return script name. This can be overriden by the single step
	 *
	 * @param void
	 * @return string
	 */
	function getScriptName() {
		return 'Upgrade ' . $this->getVersionFrom() . ' -> ' . $this->getVersionTo();
	} // getName

	// ---------------------------------------------------
	//  Utils
	// ---------------------------------------------------

	/**
	 * Execute multiple queries
	 *
	 * This one is really quick and dirty because I want to finish this and catch
	 * the bus. Need to be redone ASAP
	 *
	 * This function returns true if all queries are executed successfully
	 *
	 * @todo Make a better implementation
	 * @param string $sql
	 * @param integer $total_queries Total number of queries in SQL
	 * @param integer $executed_queries Total number of successfully executed queries
	 * @param resource $connection MySQL connection link
	 * @return boolean
	 */
	function executeMultipleQueries($sql, &$total_queries, &$executed_queries, $connection) {
		if(!trim($sql)) {
			$total_queries = 0;
			$executed_queries = 0;
			return true;
		} // if

		// Make it work on PHP 5.0.4
		$sql = str_replace(array("\r\n", "\r"), array("\n", "\n"), $sql);

		$queries = explode(";\n", $sql);
		if(!is_array($queries) || !count($queries)) {
			$total_queries = 0;
			$executed_queries = 0;
			return true;
		} // if

		$total_queries = count($queries);
		foreach($queries as $query) {
			if(trim($query)) {
				if(mysql_query(trim($query), $connection)) {
					$executed_queries++;
				} else {
					return false;
				} // if
			} // if
		} // if

		return true;
	} // executeMultipleQueries
	
	/**
	 * Checks if a column exists in a table
	 *
	 *  This function returns true if the column exists
	 *
	 * @param string $table_name Name of the table
	 * @param string $col_name Name of the column
	 * @return boolean
	 */
	function checkColumnExists($table_name, $col_name, $connection) {
		$res = mysql_query("DESCRIBE `$table_name`", $connection);
		while($row = mysql_fetch_array($res)) {
			if ($row['Field'] == $col_name) return true;
		}
		return false;
	} // checkColumnExists
	
	/**
	 * Checks if a keys exists in a table
	 *
	 *  This function returns true if the key exists
	 *
	 * @param string $table_name Name of the table
	 * @param string $col_name Name of the column
	 * @return boolean
	 */
	function checkKeyExists($table_name, $key_name, $connection) {
		$res = mysql_query("SHOW KEYS FROM `$table_name`");
		while($row = mysql_fetch_array($res)) {
			if ($row['Key_name'] == $key_name) return true;
		}
		return false;
	} // checkKeyExists
	
	/**
	 * Checks if a table exists
	 *
	 *  This function returns true if the table exists
	 *
	 * @param string $table_name Name of the table
	 * @return boolean
	 */
	function checkTableExists($table_name, $connection) {
		$res = mysql_query("SHOW TABLES", $connection);
		while ($row = mysql_fetch_array($res)) {
			if ($row[0] == $table_name) return true;
		}
		return false;
	}
	
	/**
	 * Checks if a value exists in a column in a table
	 *
	 *  This function returns true if the value exists
	 *
	 * @param string $table_name Name of the table
	 * @param string $col_name Name of the column
	 * @param string $value Value in question
	 * @return boolean
	 */
	function checkValueExists($table_name, $col_name, $value, $connection) {
		$res = mysql_query("SELECT * FROM `$table_name` WHERE `$table_name`.`$col_name` = '$value' LIMIT 1", $connection);
		while($row = mysql_fetch_array($res)) {
			return true;
		}
		return false;
	} // checkValueExists
	
	// ---------------------------------------------------
	//  Getters and setters
	// ---------------------------------------------------

	/**
	 * Get upgrader
	 *
	 * @param null
	 * @return ScriptUpgrader
	 */
	function getUpgrader() {
		return $this->upgrader;
	} // getUpgrader

	/**
	 * Set upgrader value
	 *
	 * @param ScriptUpgrader $value
	 * @return null
	 */
	function setUpgrader(ScriptUpgrader $value) {
		$this->upgrader = $value;
	} // setUpgrader

	/**
	 * Get version_from
	 *
	 * @param null
	 * @return string
	 */
	function getVersionFrom() {
		return $this->version_from;
	} // getVersionFrom

	/**
	 * Set version_from value
	 *
	 * @param string $value
	 * @return null
	 */
	protected function setVersionFrom($value) {
		$this->version_from = $value;
	} // setVersionFrom

	/**
	 * Get version_to
	 *
	 * @param null
	 * @return string
	 */
	function getVersionTo() {
		return $this->version_to;
	} // getVersionTo

	/**
	 * Set version_to value
	 *
	 * @param string $value
	 * @return null
	 */
	protected function setVersionTo($value) {
		$this->version_to = $value;
	} // setVersionTo

	/**
	 * Return output instance
	 *
	 * @param void
	 * @return Output
	 */
	function getOutput() {
		return $this->output;
	} // getOutput

	/**
	 * Set output object
	 *
	 * @param Output $output
	 * @return Output
	 */
	function setOutput(Output $output) {
		$this->output = $output;
		return $output;
	} // setOutput

	/**
	 * Returns whether this script can upgrade from $version
	 *
	 * @param string $version
	 */
	function worksFor($version) {
		return version_compare($version, $this->getVersionFrom()) >= 0 && version_compare($version, $this->getVersionTo()) < 0;
	}
	/**
	 * Print message to the output
	 *
	 * @param string $message
	 * @param boolean $is_error
	 * @return null
	 */
	function printMessage($message, $is_error = false) {
		if($this->output instanceof Output) {
			$this->output->printMessage($message, $is_error);
		} // if
	} // printMessage

} // ScriptUpgraderScript

?>