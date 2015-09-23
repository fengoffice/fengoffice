<?php
Hook::register ( "workspaces" );

function workspaces_total_task_timeslots_group_by_criterias($args, &$ret) {
	$wdimension = Dimensions::findByCode ( 'workspaces' );
	$tdimension = Dimensions::findByCode ( 'tags' );
	$ret[] = array('val' => 'dim_'.$wdimension->getId(), 'name' => $wdimension->getName());
	$ret[] = array('val' => 'dim_'.$tdimension->getId(), 'name' => $tdimension->getName());
}


function workspaces_custom_reports_additional_columns($args, &$ret) {
	
	$ot = array_var($args, 'object_type');
	if ($ot instanceof ObjectType && in_array($ot->getType(), array('dimension_object','dimension_group'))) return;
	
	$dimensions = Dimensions::findAll ( array("conditions" => "code IN ('workspaces','tags')") );
	foreach ($dimensions as $dimension) {
		if (in_array($dimension->getId(), config_option('enabled_dimensions'))) {
			
			$name = $dimension->getName();
			
			$ret[] =  array('id' => 'dim_'.$dimension->getId(), 'name' => $name, 'type' => DATA_TYPE_STRING);
		}
	}
}

function workspaces_total_tasks_times_csv_columns($cols, &$cols) {
	$dimension = Dimensions::findByCode('workspaces');
	$cols[] = $dimension->getName();
}

function workspaces_total_tasks_times_csv_column_values($ts, &$new_values) {
	if (!is_array($new_values)) $new_values = array();
	$dimension = Dimensions::findByCode('workspaces');
	if ($ts instanceof Timeslot) {
		$members = $ts->getMembers();
		$str = "";
		foreach ($members as $m) {
			if ($m->getDimensionId() == $dimension->getId()) {
				$str .= ($str == "" ? "" : " - "). $m->getName();
			}
		}
		$new_values[] = $str;
	}
}

function workspaces_include_tasks_template($ignored, &$more_content_templates) {
	$more_content_templates[] = array(
		'template' => 'groupby',
		'controller' => 'task',
		'plugin' => 'workspaces'
	);
}


function workspaces_quickadd_extra_fields($parameters) {
	if (array_var($parameters, 'dimension_id') == Dimensions::findByCode("workspaces")->getId()) {
		$parent_member = Members::findById(array_var($parameters, 'parent_id'));
		if ($parent_member instanceof Member && $parent_member->getObjectId() > 0) {
			$dimension_object = Objects::findObject($parent_member->getObjectId());
			
			$fields = $dimension_object->manager()->getPublicColumns();
			$color_columns = array();
			foreach ($fields as $f) {
				if ($f['type'] == DATA_TYPE_WSCOLOR) {
					$color_columns[] = $f['col'];
				}
			}
			foreach ($color_columns as $col) {
				foreach ($fields as &$f) {
					if ($f['col'] == $col && $dimension_object->columnExists($col)) {
						$color_code = $dimension_object->getColumnValue($col);
						echo '<input type="hidden" name="dim_obj['.$col.']" value="'.$color_code.'" />';
					}
				}
			}
		}
	}
}

function workspaces_render_widget_member_information(Member $member, &$prop_html="") {
	$ws_ot = ObjectTypes::findByName('workspace');
	if ($member->getObjectTypeId() == $ws_ot->getId()) {
		
		$ws = Workspaces::getWorkspaceById($member->getObjectId());
		if ($ws instanceof Workspace && trim($member->getDescription()) != "" && $ws->getColumnValue('show_description_in_overview')) {
			$prop_html .= '<div style="margin-bottom:5px;">'.escape_html_whitespace(convert_to_links(clean($member->getDescription()))).'</div>';
		}
		
	}
}



function workspaces_more_panel_dimension_links($ignored, &$links) {
	$dimension = Dimensions::findByCode('workspaces');
	$enabled_dimensions = config_option("enabled_dimensions");
	if (!in_array($dimension->getId(), $enabled_dimensions)) return;
	
	$name = $dimension->getName();
	
	$step = Plugins::instance()->isActivePlugin('crpm') ? 5 : 4;
	
	$selector = '#dimension-panel-'.$dimension->getId().' .x-tool.x-tool-options';
	$onclick = 'Ext.getCmp(\'menu-panel\').expand(true); og.highlight_link({selector:\''.$selector.'\', step:'.$step.', time_active:30000, timeout:500, animate_opacity:10, hint_text:\''.lang('click here').'\'}); return false;';
	
	$links[] = array(
			'id' => 'dim_workspaces',
			'ico' => 'ico-large-workspace',
			'url' => get_url('dimension', 'list_members', array('dim' => $dimension->getId())),
			'name' => $name,
			'onclick' => $onclick,
			'extra' => '',
	);
}


function workspaces_add_member_by_type_info($data, &$info) {
	if (array_var($data, 'dim_code') == 'workspaces') {
		$w_ot = ObjectTypes::findByName('workspace');

		if (array_var($data, 'mem_type') == $w_ot->getId()) {
			$dimension = Dimensions::findByCode('workspaces');
			$info = array('name' => lang('add new workspace'), 'url' => get_url('member', 'add', array('dim_id' => $dimension->getId(), 'type' => $w_ot->getId())));
		}
	} else if (array_var($data, 'dim_code') == 'tags') {
		$t_ot = ObjectTypes::findByName('tag');
		
		if (array_var($data, 'mem_type') == $t_ot->getId()) {
			$dimension = Dimensions::findByCode('tags');
			$info = array('name' => lang('add new tag'), 'url' => get_url('member', 'add', array('dim_id' => $dimension->getId(), 'type' => $t_ot->getId())));
		}
	}
}


function workspaces_page_rendered() {
	if (!Plugins::instance()->isActivePlugin('crpm')) {
		$did = Dimensions::instance()->findByCode('workspaces')->getId();
		
		$one_member = Members::findOne(array('conditions' => 'dimension_id = '.$did));
		if (!$one_member instanceof Member) {
			echo "<script>";
			echo "og.menuPanelCollapsed = true;";
			echo "</script>";
		}
	}

}

function workspaces_after_user_add($object, $ignored) {
	/* @var $object Contact */
	$workspaces_dim = Dimensions::findOne(array("conditions" => "`code` = 'workspaces'"));

	if ($workspaces_dim instanceof Dimension) {

		$sql = "INSERT INTO `".TABLE_PREFIX."contact_dimension_permissions` (`permission_group_id`, `dimension_id`, `permission_type`)
				 SELECT `c`.`permission_group_id`, ".$workspaces_dim->getId().", 'check'
				 FROM `".TABLE_PREFIX."contacts` `c`
				 WHERE `c`.`is_company`=0 AND `c`.`user_type`!=0 AND `c`.`disabled`=0 AND `c`.`object_id`=".$object->getId()."
				 ON DUPLICATE KEY UPDATE `dimension_id`=`dimension_id`;";
		DB::execute($sql);
	}
}


function workspaces_custom_reports_object_types($parameters, &$object_types) {
	$ws_dim = Dimensions::findByCode('workspaces');
	if (in_array($ws_dim->getId(), config_option('enabled_dimensions'))) {
		$object_types[] = ObjectTypes::findByName('workspace');
	}
	/*
	$tags_dim = Dimensions::findByCode('tags');
	if (in_array($tags_dim->getId(), config_option('enabled_dimensions'))) {
		$object_types[] = ObjectTypes::findByName('tag');
	}*/
}


function workspaces_additional_dashboard_actions($ignored, &$actions) {
	
	$ws_dim = Dimensions::findByCode('workspaces');
	$wot = ObjectTypes::findByName('workspace');
	$wdot = DimensionObjectTypes::findOne(array('conditions' => 'dimension_id='.$ws_dim->getId().' AND object_type_id='.$wot->getId()));
	if ($wdot instanceof DimensionObjectType && $wdot->getEnabled()) {
		if (active_context_can_contain_member_type($ws_dim->getId(), $wot->getId())) {
			$actions[] = array(
				'id' => 'workspaces-list',
				'assoc_ot' => $wot->getId(),
				'assoc_dim' => $ws_dim->getId(),
				'name' => lang('workspaces list'),
				'class' => 'link-ico ico-workspace',
				'onclick' => "og.openLink(og.getUrl('member', 'init', {dim_id:".$ws_dim->getId().", type_id:".$wot->getId()."}), {caller:'".$ws_dim->getCode()."'}); return false;",
			);
		}
	}
	
	$tags_dim = Dimensions::findByCode('tags');
	$tot = ObjectTypes::findByName('tag');
	$tdot = DimensionObjectTypes::findOne(array('conditions' => 'dimension_id='.$tags_dim->getId().' AND object_type_id='.$tot->getId()));
	if ($tdot instanceof DimensionObjectType && $tdot->getEnabled()) {
		if (active_context_can_contain_member_type($tags_dim->getId(), $tot->getId())) {
			$actions[] = array(
				'id' => 'tags-list',
				'assoc_ot' => $tot->getId(),
				'assoc_dim' => $tags_dim->getId(),
				'name' => lang('tags list'),
				'class' => 'link-ico ico-tag',
				'onclick' => "og.openLink(og.getUrl('member', 'init', {dim_id:".$tags_dim->getId().", type_id:".$tot->getId()."}), {caller:'".$tags_dim->getCode()."'}); return false;",
			);
		}
	}
}
