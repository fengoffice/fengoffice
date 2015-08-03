<?php
$mysql_version = mysql_get_server_info($this->database_connection);
if ($mysql_version && version_compare($mysql_version, '5.6', '>=')) {
	
	// create searchable_objects as InnoDB
	$sql_string = "
		CREATE TABLE `".$database_prefix."searchable_objects_new` (
			`rel_object_id` int(10) unsigned NOT NULL default '0',
			`column_name` varchar(50) collate utf8_unicode_ci NOT NULL default '',
			`content` text collate utf8_unicode_ci NOT NULL,
			`contact_id` int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (`rel_object_id`,`column_name`),
			FULLTEXT KEY `content` (`content`),
			KEY `rel_obj_id` (`rel_object_id`)
		) ENGINE=$database_engine DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	";
	
	if(!$this->executeMultipleQueries($sql_string, $total_queries, $executed_queries, $this->database_connection)) {
		return $this->breakExecution('Failed to import database construction. MySQL said: ' . mysql_error($this->database_connection));
	}
	
	// switch table names and delete old table. 
	$sql_string = "
		RENAME TABLE `".$database_prefix."searchable_objects` TO `".$database_prefix."searchable_objects_old`;
		RENAME TABLE `".$database_prefix."searchable_objects_new` TO `".$database_prefix."searchable_objects`;
		DROP TABLE `".$database_prefix."searchable_objects_old`;
	";
	if(!$this->executeMultipleQueries($sql_string, $total_queries, $executed_queries, $this->database_connection)) {
		return $this->breakExecution('Failed to import database construction. MySQL said: ' . mysql_error($this->database_connection));
	}
	
}