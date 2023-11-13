<?php
function save_selected_objects_ids($ids_to_add, $ids_to_remove) {
	$object_controller = new ObjectController();
	$params = $object_controller->get_list_objects_params();
	
	$params['only_ids'] = true;
	$params['start'] = 0;
	$params['limit'] = 0;
	
	$select_all = array_var($_GET,'select_all', false);
	if ($select_all) {
		$listing = $object_controller->get_objects_list($params);
		save_tmp_objects_ids($listing['objects']);
	}
	
	if (count($ids_to_add) > 0) {
		save_tmp_objects_ids($ids_to_add);
	}
	
	if (count($ids_to_remove) > 0) {
		remove_tmp_objects_ids($ids_to_remove);
	}
}

function get_selector_identifier($genid = null) {
	if (is_null($genid)) {
		$genid = array_var($_GET,'genid');
	}
	$user_id = logged_user()->getId();
	$identifier = $user_id.'_'.$genid.'_'.session_id();
	
	return $identifier;
}

function save_tmp_objects_ids($obj_ids, $remove_previous_selection = false, $genid = null) {
	$user_id = logged_user()->getId();
	$identifier = get_selector_identifier($genid);
	$updated_on = DateTimeValueLib::now()->toMySQL();
		
	//get previous selection
	$prev_obj_ids = get_selected_objects_ids();
	
	//merge new ids with previous ids
	if (!$remove_previous_selection) {
		$obj_ids = array_unique(array_merge($obj_ids, $prev_obj_ids));
	}
		
	$value = implode (",", $obj_ids);
	
	$sql = "INSERT INTO ".TABLE_PREFIX."object_selector_temp_values (user_id, identifier, updated_on, value)
						VALUES ('".$user_id."', '".$identifier."', '".$updated_on."', '".$value."')
						ON DUPLICATE KEY UPDATE value='".$value."'";
	
	DB::execute($sql);
}

function remove_tmp_objects_ids($obj_ids) {
	// get current object ids
	$current_object_ids = get_selected_objects_ids();
	// remove $obj_ids from $current_object_ids
	$filtered_object_ids = array_diff($current_object_ids, $obj_ids);
	
	// save filtered array, remove previous selection
	save_tmp_objects_ids($filtered_object_ids, true);
}

function get_selected_objects_ids() {
	$identifier = get_selector_identifier();
	$row = DB::executeOne("SELECT value FROM ".TABLE_PREFIX."object_selector_temp_values WHERE identifier = '".$identifier."'");
		
	$obj_ids = array_filter(explode(',', $row['value']));
		
	return $obj_ids;
}

function get_selected_objects() {
	$listing = array();
	$object_controller = new ObjectController();
	
	$params = $object_controller->get_list_objects_params();
	$obj_ids = get_selected_objects_ids();
	
	// save original selection, so changes can be reverted when closing the selector wiothout saving
	$identifier = get_selector_identifier();
	if (!isset($_SESSION['object_selector'])) $_SESSION['object_selector'] = array();
	if (!isset($_SESSION['object_selector'][$identifier])) {
		$_SESSION['object_selector'][$identifier] = $obj_ids;
	}
	
	if(count($obj_ids) > 0){
		$params['filters']['object_ids'] = implode (",", $obj_ids);
		$listing = $object_controller->get_objects_list($params);	
	}
	return $listing;
}


function clean_old_object_selector_temp_values($days = 1) {
	$date = DateTimeValueLib::now();
	$date = $date->add('d', -1 * $days);
	
	try {
		DB::beginWork();
		DB::execute("DELETE FROM ".TABLE_PREFIX."object_selector_temp_values WHERE updated_on < '". $date->toMySQL() ."'");
		DB::commit();
	} catch (Exception $e) {
		DB::rollback();
		Logger::log("Error cleaning object selector temp values.\n".$e->getMessage()."\n".$e->getTraceAsString());
	}
}

