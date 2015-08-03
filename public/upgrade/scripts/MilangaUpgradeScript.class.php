<?php

  /**
  * Milanga upgrade script will upgrade Feng Office 0.9 to Feng Office 1.0
  *
  * @package ScriptUpgrader.scripts
  * @version 1.0
  * @author Marcos Saiz <marcos.saiz@fengoffice.com>
  */
  class MilangaUpgradeScript extends ScriptUpgraderScript {
        
    /**
    * Array of files and folders that need to be writable
    *
    * @var array
    */
    private $check_is_writable = array(
      '/config/config.php',
    	'/config',
      '/public/files',
      '/cache',
      '/tmp',
      '/upload'
    ); // array
      
    /**
    * Array of extensions taht need to be loaded
    *
    * @var array
    */
    private $check_extensions = array(
      'mysql', 'gd', 'simplexml'
    ); // array
  
    /**
    * Construct the MilangaUpgradeScript
    *
    * @param Output $output
    * @return MilangaUpgradeScript
    */
    function __construct(Output $output) {
      parent::__construct($output);
      $this->setVersionFrom('0.9');
      $this->setVersionTo('1.0');
    } // __construct
    
  	function getCheckIsWritable() {
		return $this->check_is_writable;
	}

	function getCheckExtensions() {
		return $this->check_extensions;
	}
    
    /**
    * Execute the script
    *
    * @param void
    * @return boolean
    */
    function execute() {
      // ---------------------------------------------------
      //  Check MySQL version
      // ---------------------------------------------------
      
      $mysql_version = mysql_get_server_info($this->database_connection);
      if($mysql_version && version_compare($mysql_version, '4.1', '>=')) {
        $constants['DB_CHARSET'] = 'utf8';
        @mysql_query("SET NAMES 'utf8'", $this->database_connection);
        tpl_assign('default_collation', $default_collation = 'collate utf8_unicode_ci');
        tpl_assign('default_charset', $default_charset = 'DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
      } else {
        tpl_assign('default_collation', $default_collation = '');
        tpl_assign('default_charset', $default_charset = '');
      } // if
      
      tpl_assign('table_prefix', TABLE_PREFIX);
      
      // ---------------------------------------------------
      //  Execute migration
      // ---------------------------------------------------
      
      $total_queries = 0;
      $executed_queries = 0;
      $upgrade_script = tpl_fetch(get_template_path('db_migration/1_0_milanga'));
      
      if($this->executeMultipleQueries($upgrade_script, $total_queries, $executed_queries, $this->database_connection)) {
        $this->printMessage("Database schema transformations executed (total queries: $total_queries)");
      } else {
        $this->printMessage('Failed to execute DB schema transformations. MySQL said: ' . mysql_error(), true);
        return false;
      } // if

      $this->printMessage('Feng Office has been upgraded. You are now running Feng Office '.$this->getVersionTo().' Enjoy!');
    } // execute
  } // MilangaUpgradeScript

?>