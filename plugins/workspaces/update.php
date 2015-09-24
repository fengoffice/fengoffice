<?php 
	/**
	 * Feng2 Plugin update engine 
	 */
	function workspaces_update_1_2() {
		$workspaces = Workspaces::findAll();
		if (!is_array($workspaces)) return;
		foreach ($workspaces as $ws){
			if ($ws instanceof ContentDataObject) {
				$ws->addToSearchableObjects(1);
			}
			$ws->addToSharingTable();
		}
	}
	
	
	function workspaces_update_2_3() {
		$ws_options = '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}, "quickAdd":true,"showInPaths":true,"useLangs":true}';
		DB::executeAll("UPDATE ".TABLE_PREFIX."dimensions SET options='$ws_options' WHERE code='workspaces'");
		$tag_options = '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"},"quickAdd":true,"showInPaths":true,"useLangs":true}';
		DB::executeAll("UPDATE ".TABLE_PREFIX."dimensions SET options='$tag_options' WHERE code='tags'");
	}
	
	function workspaces_update_3_4() {
		DB::execute("
			UPDATE ".TABLE_PREFIX."dimensions SET permission_query_method='not_mandatory' WHERE code='tags';
		");
	}
	
	function workspaces_update_4_5() {
		DB::execute("
			INSERT INTO ".TABLE_PREFIX."dimension_object_type_contents (dimension_id,dimension_object_type_id,content_object_type_id,is_required,is_multiple) VALUES 
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='person' LIMIT 1), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='mail' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='feng_persons'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='company' LIMIT 1), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='mail' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='workspaces'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='workspace'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='mail' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='tags'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='tag'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='mail' LIMIT 1),0,1)
			ON DUPLICATE KEY UPDATE dimension_id=dimension_id;
		");
	}
	
	function workspaces_update_5_6() {
		// create associations
		DB::execute("
			INSERT INTO `".TABLE_PREFIX."dimension_member_associations` (`dimension_id`,`object_type_id`,`associated_dimension_id`, `associated_object_type_id`, `is_required`,`is_multiple`, `keeps_record`) VALUES
			((SELECT id from ".TABLE_PREFIX."dimensions WHERE code = 'workspaces'),(SELECT id FROM ".TABLE_PREFIX."object_types WHERE name = 'workspace'),(SELECT id from ".TABLE_PREFIX."dimensions WHERE code = 'feng_persons'),(SELECT id FROM ".TABLE_PREFIX."object_types WHERE name = 'person' LIMIT 1),0,1,0),
			((SELECT id from ".TABLE_PREFIX."dimensions WHERE code = 'workspaces'),(SELECT id FROM ".TABLE_PREFIX."object_types WHERE name = 'workspace'),(SELECT id from ".TABLE_PREFIX."dimensions WHERE code = 'feng_persons'),(SELECT id FROM ".TABLE_PREFIX."object_types WHERE name = 'company' LIMIT 1),0,1,0);
		");
		// instantiate actual associations
		$ws_dim = Dimensions::findByCode('workspaces');
		$ws_ot = ObjectTypes::findByName('workspace');
		$ws_members = Members::findAll(array('conditions' => 'dimension_id = '.$ws_dim->getId().' AND object_type_id = '.$ws_ot->getId()));
		foreach($ws_members as $ws_mem) {
			// after saving permissions the associations are instantiated by 'core_dimensions' plugin 
			save_member_permissions($ws_mem);
		}
	}
	
	function workspaces_update_6_7() {
		DB::execute("
			INSERT INTO `".TABLE_PREFIX."contact_config_options` (`category_name`, `name`, `default_value`, `config_handler_class`, `is_system`, `option_order`) VALUES
			 ('listing preferences', concat('lp_dim_workspaces_show_as_column'), '0', 'BoolConfigHandler', 0, 0),
			 ('listing preferences', concat('lp_dim_tags_show_as_column'), '0', 'BoolConfigHandler', 0, 0)
			ON DUPLICATE KEY UPDATE name=name;
		");
	}
	
	function workspaces_update_7_8() {
		DB::execute("
			UPDATE `".TABLE_PREFIX."widgets` SET `default_section`='left', `default_order`=3 WHERE `name`='workspaces';
		");
	}
	
	function workspaces_update_8_9() {
		$tag_options = '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"},"quickAdd":true,"showInPaths":true,"useLangs":true}';
		DB::executeAll("UPDATE ".TABLE_PREFIX."dimensions SET options='$tag_options' WHERE code='tags'");
		
		$dot_options = '{"defaultAjax":{"controller":"dashboard", "action": "main_dashboard"}}';
		DB::executeAll("
			UPDATE ".TABLE_PREFIX."dimension_object_types SET options='$dot_options' WHERE 
			 object_type_id=(SELECT `id` FROM `".TABLE_PREFIX."object_types` WHERE `name`='tag') AND
			 dimension_id=(SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='tags')
		");
		
		DB::executeAll("
			INSERT INTO ".TABLE_PREFIX."dimension_object_type_contents (dimension_id,dimension_object_type_id,content_object_type_id,is_required,is_multiple) VALUES
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='workspaces'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='workspace'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='template_task' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='tags'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='tag'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='template_task' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='workspaces'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='workspace'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='template_milestone' LIMIT 1),0,1),
			((SELECT id FROM ".TABLE_PREFIX."dimensions WHERE code='tags'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='tag'), (SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='template_milestone' LIMIT 1),0,1)
			ON DUPLICATE KEY UPDATE dimension_id=dimension_id;
		");
	}
	
	function workspaces_update_9_10() {
		DB::execute("
				UPDATE ".TABLE_PREFIX."contact_config_options 
					 SET default_value = concat((SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='tags'),',',(default_value)) 
					 WHERE name='quick_add_task_view_dimensions_combos';
				");
		
		DB::execute("
				UPDATE ".TABLE_PREFIX."contact_config_options
					SET default_value = concat((SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='workspaces'),',', (SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='tags'),',',(default_value))
					WHERE name='add_timeslot_view_dimensions_combos';
				");
	}
	
	function workspaces_update_10_11() {
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET default_value='1' WHERE name='lp_dim_workspaces_show_as_column';");
		DB::execute("UPDATE ".TABLE_PREFIX."contact_config_options SET default_value='1' WHERE name='lp_dim_tags_show_as_column';");
	}
	
	function workspaces_update_11_12() {
		DB::execute("
			UPDATE ".TABLE_PREFIX."members SET description=(SELECT p.description FROM ".TABLE_PREFIX."workspaces p WHERE p.object_id=".TABLE_PREFIX."members.object_id)
			WHERE object_type_id=(SELECT id FROM ".TABLE_PREFIX."object_types WHERE name='workspace');
		");
	}
	
	function workspaces_update_12_13() {
		DB::execute("
			INSERT INTO ".TABLE_PREFIX."dimension_object_type_options (`dimension_id`, `object_type_id`, `name`, `value`) VALUES
			 ((SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='workspaces'), (SELECT `id` FROM `".TABLE_PREFIX."object_types` WHERE `name`='workspace'),'select_after_creation','1'),
			 ((SELECT `id` FROM `".TABLE_PREFIX."dimensions` WHERE `code`='tags'), (SELECT `id` FROM `".TABLE_PREFIX."object_types` WHERE `name`='tag'),'select_after_creation','1')
			ON DUPLICATE KEY UPDATE `value`=`value`;
		");
	}