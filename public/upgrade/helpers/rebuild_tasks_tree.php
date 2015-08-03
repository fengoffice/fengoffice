<?php

  /**
   * Info: if you want to rebuild all depths and parents paths you have to reset them before call this function
  * @access public
  * @param $table_name the table that you want to use (only project_tasks and template_tasks tables can be used)
  * @return string with sql querys to execute
  */
  function rebuild_tasks_depth_and_parents_path($table_name, $database_connection) {
  		$upgrade_script = '';
	  	//START calculte the depth and the parents_path for each task
	  	$tasks_with_parents_sql = "SELECT e.object_id, e.parent_id FROM ".$table_name." e WHERE `parent_id` > 0";
	  	$res = mysql_query($tasks_with_parents_sql , $database_connection);
	  	$tasks_with_parents_rows = array();
	  	while($row = mysql_fetch_assoc($res)){
	  		$tasks_with_parents_rows[] = $row;
	  	}
	  		
	  	$total_tasks_with_parents = count($tasks_with_parents_rows);
	  	
	  	$tasks_with_parents_ids = array();
	  	foreach ($tasks_with_parents_rows as $task){
	  		$tasks_with_parents_ids[] = $task['object_id'];
	  	}
	  	
	  	foreach($tasks_with_parents_rows as  $key => &$task){
	  		$stop = false;
	  		$loop_count = 0;
	  		$parent_id = $task['parent_id'];
	  		$task['parents_ids'] = array();
	  		while (!$stop) {
	  			if(!in_array($parent_id, $tasks_with_parents_ids)){
	  				$stop = true;
	  				$task['parents_ids'][] = $parent_id;
	  			}else{
	  				foreach($tasks_with_parents_rows as $parent){
	  					if($parent_id == $parent['object_id']){
	  						$parent_id = $parent['parent_id'];
	  						$task['parents_ids'][] = $parent['object_id'];
	  						break;
	  					}
	  				}
	  			}
	  				
	  			//prevent infinit loop
	  			$loop_count++;
	  			if($loop_count > $total_tasks_with_parents){
	  				$stop = true;
	  			}
	  		}
	  		$task['depth'] = count($task['parents_ids']);
	  	
	  		$depth = $task['depth'];
	  		$task_id = $task['object_id'];
	  		$parents_path = implode(',', $task['parents_ids']);
	  	
	  		$upgrade_script .= "
						UPDATE `".$table_name."` SET `depth`='$depth', `parents_path`='$parents_path' WHERE `object_id`='$task_id';
	  						";
	  	}
	  	//END calculte the depth and the parents_path for each task
	    
	   return $upgrade_script; 
  } // rebuild_tasks_depth_and_parents_path 

?>