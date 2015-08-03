<?php

/**
 * DB access for PDO MySQL
 *
 * @version 1.0
 * @author Ignacio de Soto <ignacio.desoto@fengoffice.com>
 */
class PdoMysqlDBAdapter extends AbstractDBAdapter {

	protected $lastAffectedRows;
	
	protected $connected = false;
	
	/**
	 * Connect to the database based on the params array
	 *
	 * @access protected
	 * @param array $params
	 * @return null
	 * @throws DBConnectError
	 */
	protected function connect($params) {

		$host     = array_var($params, 'host', '');
		$user     = array_var($params, 'user', '');
		$pass     = array_var($params, 'pass', '');
		$database = array_var($params, 'name', '');
		$persist  = array_var($params, 'persist', false);

		try {
			$link = new PDO("mysql:host=$host;dbname=$database", $user, $pass, ($persist ? array(PDO::ATTR_PERSISTENT => true) : array()));
		} catch (PDOException $e) {
			throw new DBConnectError($host, $user, $pass, $database);
		} // if
		
		$this->setLink($link);
		$this->setParams($params);
		$this->setDatabaseName($database);
		return true;

	} // connect

	function close() {
		unset($this->link);
	}
	
	function isResource($resource) {
    	return $resource instanceof PDO || $resource instanceof PDOStatement;
    }
	
	
	/**
	 * Basic query execution
	 *
	 * @access protected
	 * @param string $sql
	 * @return mixed
	 */
	protected function executeQuery($sql) {
		$sth = $this->link->prepare($sql);
		$sth->execute();
		if ($sth->columnCount() == 0) {
			$this->lastAffectedRows = $sth->rowCount();
			return true;
   		} else {
   			return $sth;
   		}
	} // executeQuery

	/**
	 * Get begin work SQL (start transaction)
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getBeginWorkCommand() {
		return 'BEGIN WORK';
	} // getBeginWorkCommand

	/**
	 * Get comming SQL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCommitCommand() {
		return 'COMMIT';
	} // getCommitCommand

	/**
	 * Get rollback SQL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getRollbackCommand() {
		return 'ROLLBACK';
	} // getRollbackCommand

	/**
	 * Return number of affected rows
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function affectedRows() {
		return $this->lastAffectedRows;
	} // affectedRows

	/**
	 * Return last insert ID
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function lastInsertId() {
		return $this->link->lastInsertId();
	} // lastInsertId

	/**
	 * Returns last error message that server thrown
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function lastError() {
		$errorInfo = $this->link->errorInfo();
		return $errorInfo[2];
	} // lastError

	/**
	 * Returns code of the last error
	 *
	 * @access public
	 * @param void
	 * @return integer
	 */
	function lastErrorCode() {
		$errorInfo = $this->link->errorInfo();
		return $errorInfo[1];
	} // lastErrorCode

	/**
	 * Return array of tables that exists in database. This function will return NULL if there are
	 * no tables in database
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function listTables() {
		$extracted_table_names = $this->executeAll('SHOW TABLES');
		$table_names = array();
		if(count($extracted_table_names)) {
			foreach($extracted_table_names as $extracted_table_name) {
				$table_names[] = array_var($extracted_table_name, 'Tables_in_' . $this->getDatabaseName());
			} // foreach
		} // if
		return count($table_names) ? $table_names : null;
	} // listTables

	/**
	 * Drop one or more tables. If $table_names is string only that table will be droped, else script will drop
	 *
	 * @access public
	 * @param mixed $table_names Array of table names or single table name
	 * @return boolean
	 */
	function dropTables($table_names) {

		if(empty($table_names)) return true;
		if(!is_array($table_names)) $table_names = array($table_names);

		$escaped_table_names = array();
		foreach($table_names as $table_name) $escaped_table_names[] = $this->escapeField($table_name);
		return count($escaped_table_names) ?
		$this->execute('DROP TABLE ' .  implode(', ', $escaped_table_names)) :
		true;

	} // dropTables

	/**
	 * Remove all data from specific tables
	 *
	 * @access public
	 * @param mixed $table_names Single table name or array of table names
	 * @return boolean
	 */
	function emptyTables($table_names) {

		if(empty($table_names)) return true;
		if(!is_array($table_names)) $table_names = array($table_names);

		foreach($table_names as $table_name) {
			$this->execute('TRUNCATE ' . $this->escapeField($table_name));
		} // foreach

		return true;

	} // emptyTables

	/**
	 * This function will return array of table names and their CREATE TABLE commands
	 *
	 * @access public
	 * @param void
	 * @return array or NULL if there are no tables in database
	 */
	function exportDatabaseStructure() {
		$tables = $this->listTables();
		if(!is_array($tables) || !count($tables)) return null;
		$create_commands = array();
		foreach($tables as $table) {
			$create_command = $this->exportTableStructure($table);
			if(trim($create_command) <> '') $create_commands[$table] = $create_command;
		} // foreach
		return count($create_commands) ? $create_commands : null;
	} // exportDatabaseStructure

	/**
	 * This function is able to import database construction from any connected adapter
	 *
	 * @access public
	 * @param AbstractDBAdapter $adapter
	 * @param boolean $clear Clean up the database before execution
	 * @return boolean
	 */
	function importDatabaseStructure(AbstractDBAdapter $adapter, $clear = false) {
		if($clear) $this->clearDatabase();
		$structure = $adapter->exportDatabaseStructure();
		if(is_array($structure)) {
			foreach($structure as $table_name => $table_construction) $this->execute($table_construction);
		} // if
	} // importDatabaseStructure

	/**
	 * Return CREATE TABLE sql for specific table
	 *
	 * @access public
	 * @param string $table_name
	 * @return string or NULL if table does not exists
	 */
	function exportTableStructure($table_name) {
		$result = $this->executeOne('SHOW CREATE TABLE ' . $this->escapeField($table_name));
		return array_var($result, 'Create Table');
	} // exportTableStructure

	/**
	 * Escape name of table field or name of the table
	 *
	 * @access public
	 * @param string $field
	 * @return string
	 */
	function escapeField($field) {
		return '`' . trim($field) . '`';
	} // escapeField

	/**
	 * Escape value before use it in query. This function makes difference between NULL, scalar
	 * and DateTime values
	 *
	 * @access public
	 * @param mixed $unescaped Value that need to be escaped
	 * @return string
	 */
	function escapeValue($unescaped) {
		if(is_null($unescaped)) {
			return 'NULL';
		} // if

		if(is_bool($unescaped)) {
			return $unescaped ? "'1'" : "'0'";
		} // if

		if(is_array($unescaped)) {
			$escaped_array = array();
			foreach($unescaped as $unescaped_value) $escaped_array[] = self::escapeValue($unescaped_value);
			return implode(', ', $escaped_array);
		} // if

		if(is_object($unescaped) && ($unescaped instanceof DateTimeValue)) {
			return $this->link->quote($unescaped->toMySQL());
		} // if

		return $this->link->quote($unescaped);
	} // escapeValue

	/**
	 * Fetch row from query result
	 *
	 * @access public
	 * @param resource $resource
	 * @return array
	 */
	function fetchRow($resource) {
		return $resource->fetch(PDO::FETCH_ASSOC);
	} // fetchRow

	/**
	 * Return number of rows in specific query result
	 *
	 * @access public
	 * @param resource $resource
	 * @return integer
	 */
	function numRows($resource) {
		$sql = $resource->queryString;
		$sql = preg_replace("/SELECT(.*?)FROM/i", "SELECT count(*) FROM", $sql);
		$res = $this->link->query($sql);
		$count = $res->fetchColumn();
		return $count;
	} // numRows

	/**
	 * Free database result
	 *
	 * @access public
	 * @param resource $resource
	 * @return boolean
	 */
	function freeResult($resource) {
		return $resource->closeCursor();
	} // freeResult

} // MysqlDBAdapter

?>