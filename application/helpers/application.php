<?php

/**
 * Application helpers. This helpers are injected into the controllers
 * through ApplicationController constructions so they are available in
 * whole application
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */

/**
 * Render user box
 *
 * @param User $user
 * @return null
 */
function render_user_box(Contact $user) {
	tpl_assign('_userbox_user', $user);
	$crumbs = array(); 
	$crumbs[] = array(
		'url' => get_url('help','help_options', array('current' => 'help')),
		'text' => lang('help'),
	);
	$crumbs[] = array(
		'url' => logged_user()->getAccountUrl(), 
		'target' => 'account',
		'text' => lang('account'),
	);
	
	if (logged_user()->isExecutiveGroup()) {
		$crumbs[] = array(
			'id' => "userbox-settings",
			'url' => '#',
			'text' => lang('settings'),
			//'onclick' => "var more_panel = Ext.getCmp('more-panel'); if (more_panel) Ext.getCmp('tabs-panel').setActiveTab(more_panel); else og.openLink(og.getUrl('more','index',{more_settings_expanded:1})); return false;",
			'onclick' => "og.openLink(og.getUrl('more','index',{more_settings_expanded:1})); return false;",
		);
	}
	
	Hook::fire('render_userbox_crumbs', null, $crumbs);
	$crumbs = array_reverse($crumbs);
	tpl_assign('_userbox_crumbs', $crumbs);
	return tpl_fetch(get_template_path('user_box', 'application'));
} // render_user_box
 
/**
 * This function will render system notices for this user
 *
 * @param Contact $user
 * @return string
 */
function render_system_notices(Contact $user) {
	if(!$user->isAdministrator()) return;

	$system_notices = array();
	if (config_option('upgrade_last_check_new_version', false)) $system_notices[] = lang('new Feng Office version available', get_url('administration', 'upgrade'));

	if(count($system_notices)) {
		tpl_assign('_system_notices', $system_notices);
		return tpl_fetch(get_template_path('system_notices', 'application'));
	} // if
} // render_system_notices

/**
 * Render select company box
 *
 * @param integer $selected ID of selected company
 * @param array $attributes Additional attributes
 * @return string
 */
function select_company($name, $selected = null, $attributes = null, $allow_none = true, $check_permissions = false) {
	if (!$check_permissions) {
		$companies = Contacts::findAll(array('conditions' => 'is_company = 1 AND trashed_by_id = 0 AND archived_by_id = 0 ', 'order' => 'first_name ASC'));
	} else {
		$companies = Contacts::getVisibleCompanies(logged_user(), "`id` <> " . owner_company()->getId());
		if (logged_user()->isMemberOfOwnerCompany() || owner_company()->canAddUser(logged_user())) {
			// add the owner company
			$companies = array_merge(array(owner_company()), $companies);
		}
	}
	if ($allow_none) {
		$options = array(option_tag(lang('none'), 0));
	} else {
		$options = array();
	}
	if(is_array($companies)) {
		foreach($companies as $company) {
			$option_attributes = $company->getId() == $selected ? array('selected' => 'selected') : null;
			$company_name = $company->getObjectName();
			$options[] = option_tag($company_name, $company->getId(), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} // select_company

/**
 * Returns a control to select multiple users or groups
 *
 */
function select_users_or_groups($name = "", $selected = null, $id = null) {
	require_javascript('og/UserGroupPicker.js');
	
	if (!isset($id)) $id = gen_id();
		
	$selectedCSV = "";
	$json = array();
	
	$company_users = Contacts::getGroupedByCompany(false);
	foreach ($company_users as $company_row){
		$company = $company_row['details'];
		$users = $company_row['users'];
		
		$comp_id = $company instanceof Contact ? $company->getId() : "0";
		$comp_name = $company instanceof Contact ? $company->getObjectName() : lang('without company');
		
		if (count($users) > 0) {
			$json[] = array(
				'p' => 'users',
				't' => 'company',
				'id' => 'c' . $comp_id,
				'n' => $comp_name,
			);
			foreach ($users as $u) {
				$json[] = array(
					'p' => 'c' . $comp_id,
					't' => 'user',
					'g' => $u->isGuest() ? 1 : 0,
					'id' => $u->getPermissionGroupId(),
					'n' => $u->getObjectName(),
					'isg' => $u->isGuest()
				);	
			}
		}
	}
	
	$groups = PermissionGroups::getNonRolePermissionGroups();
	foreach ($groups as $group) {
		$json[] = array(
			'p' => 'groups',
			't' => 'group',
			'id' => $group->getId(),
			'n' => $group->getName(),
		);
	}
	$jsonUsers = json_encode($json);
	
	$output = "<div id=\"$id-user-picker\" style=\"box-shadow:2px 4px 5px 1px #CCCCCC; border-top:1px solid #ccc;\"></div>
			<input id=\"$id-field\" type=\"hidden\" value=\"$selectedCSV\" name=\"$name\"></input>
		<script>
		var userPicker = new og.UserPicker({
			renderTo: '$id-user-picker',
			field: '$id-field',
			id: '$id',
			users: $jsonUsers,
			height: 320,
			width: 240
		});
		</script>
	";
	return $output;
} // select_users_or_groups

function intersectCSVs($csv1, $csv2){
	$arr1 = explode(',', $csv1);
	$arr2 = explode(',', $csv2);
	$final = array();
	
	foreach ($arr1 as $a1) {
		foreach ($arr2 as $a2) {
			if ($a1 == $a2){
				$final[] = $a1;
				break;
			}
		}
	}
			
	return implode(',', $final);
}

function allowed_users_to_assign($context = null, $filter_by_permissions = true, $return_company_array = true, $for_task_list_filters=false) {
	if ($context == null) {
		$context = active_context();
	}

	if(!can_manage_tasks(logged_user()) && can_task_assignee(logged_user())) {
		$contacts = array(logged_user());
	} else if (can_manage_tasks(logged_user())) {
		$contacts = array();
		$tmp_contacts = array();
		// for task selectors
		if ($filter_by_permissions) {
			//check if context is empty
			$root_context = true;
			if (isset($context) && is_array($context)) {
				foreach ($context as $selection) {
					if ($selection instanceof Member && $selection->getDimension()->getDefinesPermissions() && $selection->getDimension()->getIsManageable()) {
						$root_context = false;
						break;
					}
				}
			}
			//get users with can_task_assignee permissions
			if($root_context && $for_task_list_filters){
				$tmp_contacts = get_users_with_system_permission('can_task_assignee');
			}else{
                $for_template_task_assigned_to = array_var($_GET, 'for_template_task_assigned_to');

                $task_object_type_id = ProjectTasks::instance()->getObjectTypeId();

                if($for_template_task_assigned_to){
                    $task_object_type_id = TemplateTasks::instance()->getObjectTypeId();
                }
				$tmp_contacts = allowed_users_in_context($task_object_type_id, $context, ACCESS_LEVEL_READ);
			}
		} else {
			// for template variables selectors
			$tmp_contacts = Contacts::getAllUsers();
		}
		foreach ($tmp_contacts as $c) {
			if (can_task_assignee($c)) $contacts[] = $c;
		}
	} else {
		$contacts = array();
	}

	if(!$return_company_array){
		return $contacts;
	}

	$comp_array = array();
	Hook::fire('contact_check_can_view_in_array', null, $contacts);
	foreach ($contacts as $contact) { /* @var $contact Contact */
		if (!isset($comp_array[$contact->getCompanyId()])) {
			if ($contact->getCompanyId() == 0) {
				$comp_array[0] = array('id' => "0", 'name' => lang('without company'), 'users' => array());
			} else {
				$comp = Contacts::findById($contact->getCompanyId());
				$comp_array[$contact->getCompanyId()] = array('id' => $contact->getCompanyId(), 'name' => $comp->getObjectName(), 'users' => array());
			}
		}
		$comp_array[$contact->getCompanyId()]['users'][] = array('id' => $contact->getId(), 'name' => $contact->getObjectName(), 'isCurrent' => $contact->getId() == logged_user()->getId());
	}
	
	return array_values($comp_array);
}

function allowed_users_to_assign_all_mobile($member_id = null) {
	$context = null;
	if ($member_id != null) {
		$member = Members::findById($member_id);
		if ($member instanceof Member){
			$context = array($member);
		}
	}
	return allowed_users_to_assign($context);
}


/**
 * Render assign to SELECT
 *
 * @param string $list_name Name of the select control
 * @param Project $project Selected project, if NULL active project will be used
 * @param integer $selected ID of selected user
 * @param array $attributes Array of select box attributes, if needed
 * @return null
 */
function assign_to_select_box($list_name, $context = null, $selected = null, $attributes = null, $genid = null) {
	if (!$genid) $genid = gen_id();
	ob_start(); ?>
    <input type="hidden" id="<?php echo $genid ?>taskFormAssignedTo" name="<?php echo $list_name?>"></input>
	<div id="<?php echo $genid ?>assignto_div">
		<div id="<?php echo $genid ?>assignto_container_div"></div>
	</div>
	<script>
	og.drawAssignedToSelectBoxSimple = function(companies, user, genid) {
		usersStore = ogTasks.buildAssignedToComboStore(companies);
		var assignCombo = new Ext.form.ComboBox({
			renderTo:genid + 'assignto_container_div',
			name: 'taskFormAssignedToCombo',
			id: genid + 'taskFormAssignedToCombo',
			value: user,
			store: usersStore,
			displayField:'text',
	        //typeAhead: true,
	        mode: 'local',
	        triggerAction: 'all',
	        selectOnFocus:true,
	        width:160,
	        valueField: 'value',
	        emptyText: (lang('select user or group') + '...'),
	        valueNotFoundText: ''
		});
		assignCombo.on('select', function() {
			combo = Ext.getCmp(genid + 'taskFormAssignedToCombo');
			assignedto = document.getElementById(genid + 'taskFormAssignedTo');
			if (assignedto) assignedto.value = combo.getValue();
		});
		assignedto = document.getElementById(genid + 'taskFormAssignedTo');
		if (assignedto) assignedto.value = '<?php echo ($selected ? $selected : '0') ?>';
	}
	og.drawAssignedToSelectBoxSimple(<?php echo json_encode(allowed_users_to_assign($context)) ?>, '<?php echo ($selected ? $selected : '0') ?>', '<?php echo $genid ?>');
	</script> <?php
	return ob_get_clean();
} // assign_to_select_box



function user_select_box($list_name, $selected = null, $attributes = null, $contacts = null) {
	$logged_user = logged_user();
	
	//FIXME Feng 2
	if($contacts != null){
		$users = $contacts;
	}else{
		$users = Contacts::instance()->findAll(array("conditions" => "is_company = 0 AND user_type > 0 AND disabled = 0"));
	}
		
	if(is_array($users)) {
		foreach($users as $user) {
			$option_attributes = $user->getId() == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($user->getObjectName(), $user->getId(), $option_attributes);
		}
	} 

	return select_box($list_name, $options, $attributes);
} // user_select_box



/**
 * Renders select milestone box
 *
 * @param string $name
 * @param Project $project
 * @param integer $selected ID of selected milestone
 * @param array $attributes Array of additional attributes
 * @return string
 * @throws InvalidInstanceError
 */
function select_milestone($name, $context = null, $selected = null, $attributes = null) {
	if(!isset($attributes['template_milestone'])){
		$milestones = ProjectMilestones::getActiveMilestonesByUser(logged_user(), $context);
	}else{
		//add conditions
		if(isset($attributes['template_id']) && $attributes['template_id'] != 0){
			$tmp_id = $attributes['template_id'];
			$conditions = '(`session_id` =  0 AND `template_id` = '.$tmp_id.' OR `session_id` =  '.logged_user()->getId().')';
		}else{
			$conditions = '`session_id` =  '.logged_user()->getId();
		}
		$milestones = TemplateMilestones::findAll(array('conditions' => $conditions));
	}
	
	if(is_array($attributes)) {
		if(!isset($attributes['class'])) $attributes['class'] = 'select_milestone';
	} else {
		$attributes = array('class' => 'select_milestone');
	}

	$options = array(option_tag(lang('none'), 0));
	
	
		
	if(is_array($milestones)) {

		foreach($milestones as $milestone) {
			$option_attributes = $milestone->getId() == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($milestone->getObjectName(), $milestone->getId(), $option_attributes);
		}
	}

	return select_box($name, $options, $attributes);
} // select_milestone


/**
 * Render select chart type box
 *
 * @param array $chart_types list of chart types as returned by the factory
 * @param integer $selected ID of selected chart type
 * @param array $attributes Additional attributes
 * @return string
 */
function select_chart_type($name, $chart_types, $selected = null, $attributes = null) {
	$options = array();
	if(is_array($chart_types)) {
		foreach($chart_types as $ct) {
			$option_attributes = array_search($ct,$chart_types) == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag(lang($ct), array_search($ct,$chart_types), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} // select_company


/**
 * Render Latest Activity
 *
 * @param ContentDataObject $object
 * @return null
 */
function render_object_latest_activity($object) {
	
	$latest_logs = ApplicationLogs::getObjectLogs($object, false, false, 3, 0);
	
	tpl_assign('logs', $latest_logs);
	return tpl_fetch(get_template_path('activity_log', 'latest_activity'));
	
} // render_object_latest_activity

/**
 * Show object comments block
 *
 * @param ContentDataObject $object Show comments of this object
 * @return null
 */
function render_object_comments(ContentDataObject $object) {
	if(!$object->isCommentable()) return '';
	tpl_assign('__comments_object', $object);
	return tpl_fetch(get_template_path('object_comments', 'comment'));
} // render_object_comments

function render_object_comments_for_print(ContentDataObject $object) {
	if(!$object->isCommentable()) return '';
	tpl_assign('__comments_object', $object);
	return tpl_fetch(get_template_path('object_comments_for_print', 'comment'));
} // render_object_comments

/**
 * Show object custom properties block
 *
 * @param ContentDataObject $object Show custom properties of this object
 * @return null
 */
function render_object_custom_properties($object, $required, $co_type=null, $visibility='all',$member_parent = 0) {

	$genid = gen_id();
	
	if ($object instanceof ContentDataObject) {
		
		$properties = null;
		/*$params =  array('object' => $object, 'visible_by_default' => $visibility != 'other');
		Hook::fire('override_render_properties', $params, $properties);*/
                $ot = ObjectTypes::findById($object->getObjectTypeId());
                if ($ot->getType() != 'content_object') {
                    $params =  array('object' => $object, 'visible_by_default' => $visibility != 'other');
                    Hook::fire('override_render_properties', $params, $properties);
                }

        if (is_null($properties)) {
			$properties = array();
			$ot = ObjectTypes::findById($object->getObjectTypeId());
			
			$extra_conditions = "";
			Hook::fire('object_form_custom_prop_extra_conditions', array('ot_id' => $ot, 'object' => $object), $extra_conditions, true);
			
			$cps = CustomProperties::getAllCustomPropertiesByObjectType($ot->getId(), $visibility, $extra_conditions);
			
			foreach($cps as $customProp){
				$html = get_custom_property_input_html($customProp, $object, $genid,'object_custom_properties',$member_parent);
				$properties[] = array('id' => '', 'html' => $html);
			}
		}
		
		echo '<div class="custom-properties">';
		
		foreach ($properties as $main_property){
			echo $main_property['html'];
		}
		
		echo '</div>';
	}
	
} // render_object_custom_properties


/**
 * Show object custom properties block with bootstrap style
 *
 * @param ContentDataObject $object Show custom properties of this object
 * @return null
 */
function render_object_custom_properties_bootstrap($object, $required, $co_type=null, $visibility='all',$member_parent = 0,$prefix) {

    $genid = gen_id();

    if ($object instanceof ContentDataObject) {

        $properties = null;
        $ot = ObjectTypes::findById($object->getObjectTypeId());
        if ($ot->getType() != 'content_object') {
            $params =  array('object' => $object, 'visible_by_default' => $visibility != 'other');
            Hook::fire('override_render_properties', $params, $properties);
        }

        if (is_null($properties)) {
            $properties = array();
            $ot = ObjectTypes::findById($object->getObjectTypeId());

            $extra_conditions = "";
            Hook::fire('object_form_custom_prop_extra_conditions', array('ot_id' => $ot, 'object' => $object), $extra_conditions, true);

            $cps = CustomProperties::getAllCustomPropertiesByObjectType($ot->getId(), $visibility, $extra_conditions);

            foreach($cps as $customProp){
                $html = get_custom_property_input_html($customProp, $object, $genid,$prefix,$member_parent,true);
                $properties[] = array('id' => '', 'html' => $html);
            }
        }

        echo '<div class="custom-properties col-md-12 row">';

        foreach ($properties as $main_property){
            echo $main_property['html'];
        }

        echo '</div>';
    }

} // render_object_custom_properties_bootstrap




function member_has_custom_properties($type_id) {
	if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
		return count(MemberCustomProperties::getCustomPropertyIdsByObjectType($type_id)) > 0;
	}
	return false;
}

/**
 * Show object timeslots block
 *
 * @param ContentDataObject $object Show timeslots of this object
 * @return null
 */
function render_object_timeslots(ContentDataObject $object) {
	if(!$object->allowsTimeslots()) return '';
	tpl_assign('__timeslots_object', $object);
	return tpl_fetch(get_template_path('object_timeslots', 'timeslot'));
}

/**
 * Render post comment form for specific project object
 *
 * @param ContentDataObject $object
 * @param string $redirect_to
 * @return string
 */
function render_comment_form(ContentDataObject $object) {
	$comment = new Comment();

	tpl_assign('comment_form_comment', $comment);
	tpl_assign('comment_form_object', $object);
	return tpl_fetch(get_template_path('post_comment_form', 'comment'));
} // render_post_comment_form

/**
 * Render timeslot form for specific project object
 *
 * @param ContentDataObject $object
 * @return string
 */
function render_timeslot_form(ContentDataObject $object) {
	$timeslot = new Timeslot();
	tpl_assign('timeslot_form_timeslot', $timeslot);
	tpl_assign('timeslot_form_object', $object);
	return tpl_fetch(get_template_path('post_timeslot_form', 'timeslot'));
} // render_timeslot_form

/**
 * Render open timeslot form for specific project object
 *
 * @param ContentDataObject $object
 * @return string
 */
function render_open_timeslot_form(ContentDataObject $object, Timeslot $timeslot) {
	tpl_assign('timeslot_form_timeslot', $timeslot);
	tpl_assign('timeslot_form_object', $object);
	return tpl_fetch(get_template_path('post_open_timeslot_form', 'timeslot'));
} // render_timeslot_form

/**
 * This function will render the code for objects linking section of the form. Note that
 * this need to be part of the existing form. It allows uploading of a new file to directly link to the object.
 *
 * @param string $prefix name prefix
 * @param integer $max_controls Max number of controls
 * @return string
 */
function render_linked_objects($prefix = 'linked_objects', $max_controls = 5) {
	static $ids = array();
	static $js_included = false;

	$linked_objects_id = 0;
	do {
		$linked_objects_id++;
	} while(in_array($linked_objects_id, $ids));

	$old_js_included = $js_included;
	$js_included = true;

	tpl_assign('linked_objects_js_included', $old_js_included);
	tpl_assign('linked_objects_id', $linked_objects_id);
	tpl_assign('linked_objects_prefix', $prefix);
	tpl_assign('linked_objects_max_controls', (integer) $max_controls);
	return tpl_fetch(get_template_path('linked_objects', 'object'));
} // render_linked_objects

/**
 * List all fields attached to specific object
 *
 * @param ContentDataObject $object
 * @param boolean $can_remove Logged user can remove linked objects
 * @return string
 */
function render_object_links(ContentDataObject $object, $can_remove = false, $shortDisplay = false, $enableAdding=true) {
	tpl_assign('linked_objects_object', $object);
	tpl_assign('shortDisplay', $shortDisplay);
	tpl_assign('enableAdding', $enableAdding);
	tpl_assign('linked_objects', $object->getLinkedObjects());
	return tpl_fetch(get_template_path('list_linked_objects', 'object'));
} // render_object_links

/**
 * List all fields attached to specific object, and renders them in the main view
 *
 * @param ContentDataObject $object
 * @param boolean $can_remove Logged user can remove linked objects
 * @return string
 */
function render_object_links_main(ContentDataObject $object, $can_remove = false, $shortDisplay = false, $enableAdding=true) {
	tpl_assign('linked_objects_object', $object);
	tpl_assign('shortDisplay', $shortDisplay);
	tpl_assign('enableAdding', $enableAdding);
	tpl_assign('linked_objects', $object->getLinkedObjects());
	return tpl_fetch(get_template_path('list_linked_objects_main', 'object'));
} // render_object_links

function render_object_link_form(ApplicationDataObject $object, $extra_objects = null) {
	require_javascript("og/ObjectPicker.js");
	$objects = $object->getLinkedObjects();
	if (is_array($extra_objects)) {
		$objects = array_merge($objects, $extra_objects);
	}
	tpl_assign('objects', $objects);
	return tpl_fetch(get_template_path('linked_objects', 'object'));
} // render_object_link_form

function render_object_subscribers(ContentDataObject $object) {
	tpl_assign('object', $object);
	return tpl_fetch(get_template_path('list_subscribers', 'object'));
}

function render_add_subscribers(ContentDataObject $object, $genid = null, $subscribers = null, $context = null) {
	if (!isset($genid)) {
		$genid = gen_id();
	}
	$subscriberIds = array();
	if (is_array($subscribers)) {
		foreach ($subscribers as $u) {
			$subscriberIds[] = $u->getId();
		}
	} else {
		if ($object->isNew()) {
			$subscriberIds[] = logged_user()->getId();
		} else {
			foreach ($object->getSubscribers() as $u) {
				$subscriberIds[] = $u->getId();
			}
		}
	}
	if (!isset($context)) {
		if ($object->isNew()) {
			$context = active_context();
		} else {
			$context = $object->getMembers();
		}
	}
	tpl_assign('type', get_class($object->manager()));
	tpl_assign('context', $context);
	tpl_assign('object_type_id', $object->manager()->getObjectTypeId());
	tpl_assign('subscriberIds', $subscriberIds);
	tpl_assign('genid', $genid);
	return tpl_fetch(get_template_path('add_subscribers', 'object'));
}


/**
 * Creates a button that shows an object picker to link the object given by $object with the one selected in
 * the it.
 *
 * @param ContentDataObject $object
 */
function render_link_to_object($object, $text=null, $reload=false){
	require_javascript("og/ObjectPicker.js");
	
	$id = $object->getId();
	if ($text == null) $text = lang('link object');
	$reload_param = $reload ? '&reload=1' : ''; 
	$result = '';
	$result .= '<a href="#" class="action-ico ico-add" onclick="og.ObjectPicker.show(function (data) {' .
			'if (data) {' .
				'var objects = \'\';' .
				'for (var i=0; i < data.length; i++) {' .
					'if (objects != \'\') objects += \',\';' .
					'objects += data[i].data.object_id;' .
				'}' .
				' og.openLink(\'' . get_url("object", "link_object") .
						'&object_id=' . $id . $reload_param . '&objects=\' + objects' . 
						($reload ? ',{callback: function(){og.redrawLinkedObjects('. $object->getId() .')}}' : '') . ');' .
			'}' .
		'},\'\',\'\','. $object->getId() .')" id="object_linker">';
	$result .= $text;
	$result .= '</a>';
	return $result;
}


/**
 * Creates a button that shows an object picker to link an object with an object which has not been created yet
 *
 */
function render_link_to_new_object( $text=null){
	require_javascript("og/ObjectPicker.js");
	//$id = $object->getId();
	//$manager = get_class($object->manager());
	if($text==null)
	$text=lang('link object');
	$result = '';
	$result .= '<a href="#" onclick="og.ObjectPicker.show(function (data){	if(data) {	og.addLinkedObjectRow(\'tbl_linked_objects\',data[0].data.type,data[0].data.object_id, data[0].data.name,data[0].data.manager,\''.escape_single_quotes(lang('confirm unlink object')).'\',\''.escape_single_quotes(lang('unlink')).'\'); } })">';
	$result .=  $text;
	$result .= '</a>';
	return $result;
}

/**
 * Render application logs
 *
 * This helper will render array of log entries. Options array of is array of template options and it can have this
 * fields:
 *
 * - show_project_column - When we are on project dashboard we don't actually need to display project column because
 *   all entries are related with current project. That is not the situation on dashboard so we want to have the
 *   control over this. This option is true by default
 *
 * @param array $log_entries
 * @return null
 */
function render_application_logs($log_entries, $options = null) {
	tpl_assign('application_logs_entries', $log_entries);
	tpl_assign('application_logs_show_project_column', array_var($options, 'show_project_column', true));
	return tpl_fetch(get_template_path('render_application_logs', 'application'));
} // render_application_logs



function autocomplete_member_combo($name, $dimension_id, $options, $emptyText, $attributes, $forceSelection = true, $cmp_id = '', $listeners = array()) {
	require_javascript("og/MemberCombo.js");
	
	$is_ajax = array_var($attributes, 'is_ajax');
	
	if ($is_ajax) {
		$mode = 'remote';
		$min_chars = '2';
		$store_str = 'store: new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
				method:"GET",
				url: "'.get_url('dimension', 'initial_list_dimension_members_tree', array('ajax' => 'true', 'dimension_id' => $dimension_id, 'onlyname' => '1')).'"
			}),
			reader: new Ext.data.JsonReader({
				root: "dimension_members",
				fields: [{name: "id"},{name: "name"},{name: "path"},{name: "to_show"},{name: "ico"},{name: "dim"}]
			})
		})';
	} else {
		$mode = 'local';
		$min_chars = '0';
		$jsArray = "";
		foreach ($options as $o) {
			if ($jsArray != "") $jsArray .= ",";
			$jsArray .= '['.json_encode($o[0]).','.json_encode(clean($o[1])).','.json_encode(clean($o[2])).','.json_encode(clean($o[3])).','.json_encode(clean($o[4])).','.json_encode(clean($o[5])).']';
		}
		$store_str = 'store: new Ext.data.SimpleStore({
        	fields: ["id", "name", "path", "to_show", "ico", "dim"],
        	data: ['.$jsArray.']
		})';
	}

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$attributes["autocomplete"] = "off";
	$attributes["onkeypress"] = "if (event.keyCode == 13) return false;";
	
	if (!isset($listeners['beforeselect'])) {
		$listeners['beforeselect'] = 'function(combo, record, index) {record.data.to_show = Ext.util.Format.htmlDecode(record.data.to_show);}';
	}
	$listeners_str = "";
	foreach ($listeners as $event => $function) {
		$listeners_str .= ($listeners_str == "" ? "" : ",");
		$listeners_str .= "$event: $function";
	}
	
	$html = '<div class="og-membercombo-container">' . text_field($name, array_var($attributes, 'selected_name', ''), $attributes) . '</div>
		<script>
		new og.MemberCombo({
			'.$store_str.',
			'.($cmp_id == ''?'':'id:"'.$cmp_id.'",').'
			valueField: "id",
        	displayField: "to_show",
        	searchField: "name",
        	mode: "'.$mode.'",
        	minChars: '.$min_chars.',
        	forceSelection: '.($forceSelection?'true':'false').',
        	triggerAction: "all",
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{to_show}</div></tpl>",
        	listWidth: "auto",
        	emptyText: "'.($emptyText).'",
        	applyTo: "'.$id.'",
        	enableKeyEvents: true,
        	listeners: {' . $listeners_str . '}
    	});
    	</script>
	';
	return $html;
}



/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_textfield($name, $value, $options, $emptyText, $attributes, $forceSelection = true, $cmp_id = '') {
	require_javascript("og/CSVCombo.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		if (count($o) < 2) {
			$jsArray .= '['.json_encode($o).','.json_encode(clean($o)).','.json_encode(clean($o)).']';
		} else {
			$jsArray .= '['.json_encode($o[0]).','.json_encode(clean($o[1])).','.json_encode(clean($o[1])).']';
		}
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$attributes["autocomplete"] = "off";
	$attributes["onkeypress"] = "if (event.keyCode == 13) return false;";

	$html = '<div class="og-csvcombo-container">' . text_field($name, $value, $attributes) . '</div>
		<script>
		new og.CSVCombo({
			store: new Ext.data.SimpleStore({
        		fields: ["value", "name", "clean"],
        		data: '.$jsArray.'
			}),
			'.($cmp_id == ''?'':'id:"'.$cmp_id.'",').'
			valueField: "value",
        	displayField: "name",
        	mode: "local",
        	forceSelection: '.($forceSelection?'true':'false').',
        	triggerAction: "all",
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
        	emptyText: "'.clean($emptyText).'",
        	applyTo: "'.$id.'"
    	});
    	</script>
	';
	return $html;
}

/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_emailfield($name, $value, $options, $emptyText, $attributes, $forceSelection = true) {
	require_javascript("og/CSVCombo.js");
	require_javascript("og/EmailCombo.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		if (count($o) < 2) {
			$jsArray .= '['.json_encode($o).','.json_encode($o).','.json_encode(clean($o)).']';
		} else {
			$jsArray .= '['.json_encode($o[0]).','.json_encode($o[1]).','.json_encode(clean($o[1])).']';
		}
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$attributes["autocomplete"] = "off";
	$attributes["onkeypress"] = "if (event.keyCode == 13) return false;";

	$html = '<div class="og-csvcombo-container">' . text_field($name, $value, $attributes) . '</div>
		<script>
		new og.EmailCombo({
			store: new Ext.data.SimpleStore({
        		fields: ["value", "name", "clean"],
        		data: '.$jsArray.'
			}),
			valueField: "value",
        	displayField: "name",
        	mode: "local",
        	forceSelection: '.($forceSelection?'true':'false').',
        	triggerAction: "all",
        	tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{clean}</div></tpl>",
        	emptyText: "",
        	applyTo: "'.$id.'"
    	});
    	</script>
	';
	return $html;
}


/**
 * Comma separated values from a set of options.
 *
 * @param string $name Control name
 * @param string $value Initial value
 * @param string $options
 * 		An array of arrays with the values that will be shown when autocompleting.
 * 		The first value of each array will be assumed as the value and the second as the display name.
 * @param array $attributes Other control attributes
 * @return string
 */
function autocomplete_textarea_field($name, $value, $options, $max_options, $attributes) {
	require_javascript("og/AutocompleteTextarea.js");
	$jsArray = "";
	foreach ($options as $o) {
		if ($jsArray != "") $jsArray .= ",";
		$jsArray .= json_encode($o);
	}
	$jsArray = "[$jsArray]";

	$id = array_var($attributes, "id", gen_id());
	$attributes["id"] = $id;
	$render_to = gen_id().$name;
	$max_height = array_var($attributes, 'max_height', 32);
	
	$html = '<div id="'.$render_to.'"></div>
		<script>
		og.render_autocomplete_field({
			grow_max: '.$max_height.',
			render_to: "'.$render_to.'",
			name: "'.$name.'",
			id: "'.$id.'",
			value: "'.$value.'",
			store: '.$jsArray.',
			limit: '.$max_options.'
		});
    	</script>
	';
	return $html;
}


function render_add_reminders($object, $context, $defaults = null, $genid = null, $type_object = '', $render_defaults = true) {
	if(!is_array($defaults)) $defaults = array();
	
	if($render_defaults){
		if($type_object == "event"){
			$def = explode(",", user_config_option("reminders_events"));
			$default_defaults = array(
				'type' => array_var($def, 0),
				'duration' => array_var($def, 1),
				'duration_type' => array_var($def, 2),
				'for_subscribers' => true,
			);
		} else if ($type_object == "task"){
			$def = explode(",", user_config_option("reminders_tasks"));
			$default_defaults = array(
				'type' => array_var($def, 0),
				'duration' => array_var($def, 1),
				'duration_type' => array_var($def, 2),
				'for_subscribers' => true,
			);
		} else {
			$default_defaults = array(
				'type' => 'reminder_popup',
				'duration' => '15',
				'duration_type' => '1',
				'for_subscribers' => true,
			);
		}
		
		foreach ($default_defaults as $k => $v) {
			if (!isset($defaults[$k])) $defaults[$k] = $v;
		}
	}
	   
	if (is_null($genid)) {
		$genid = gen_id();
	}
	$types = ObjectReminderTypes::findAll();
	$typecsv = "";
	foreach ($types as $type) {
		if ($typecsv != "") {
			$typecsv .= ",";
		}
		$typecsv .= '"'.$type->getName().'"';
	}
	$output = '
		<div id="'.$genid.'" class="og-add-reminders">
			<a id="'.$genid.'-link" class="action-ico ico-add" href="#" onclick="og.addReminder(this.parentNode, \''.$context.'\', \''.array_var($defaults, 'type').'\', \''.array_var($defaults, 'duration').'\', \''.array_var($defaults, 'duration_type').'\', \''.array_var($defaults, 'for_subscribers').'\', this);return false;">' . lang("add object reminder") . '</a>
		</div>
		<script>
		og.reminderTypes = ['.$typecsv.'];
		</script>
	';
	
	if ($object->isNew()) {
		if($render_defaults){
			$output .= '<script>og.addReminder(document.getElementById("'.$genid.'"), \''.$context.'\', \''.array_var($defaults, 'type').'\', \''.array_var($defaults, 'duration').'\', \''.array_var($defaults, 'duration_type').'\', \''.array_var($defaults, 'for_subscribers').'\', document.getElementById("'.$genid.'-link"));</script>';
		}
	} else {
		$reminders = ObjectReminders::getAllRemindersByObjectAndUser($object, logged_user(), $context, true);
		foreach($reminders as $reminder) {
			$mins = $reminder->getMinutesBefore();
			if ($mins % 10080 == 0) {
				$duration = $mins / 10080;
				$duration_type = "10080";
			} else if ($mins % 1440 == 0) {
				$duration = $mins / 1440;
				$duration_type = "1440";
			} else if ($mins % 60 == 0) {
				$duration = $mins / 60;
				$duration_type = "60";
			} else {
				$duration = $mins;
				$duration_type = "1";
			}
			$type = $reminder->getType();
			$forSubscribers = $reminder->getUserId() == 0 ? "true" : "false";
			$output .= '<script>og.addReminder(document.getElementById("'.$genid.'"), "'.$context.'", "'.$type.'", "'.$duration.'", "'.$duration_type.'", '.$forSubscribers.', document.getElementById(\''.$genid.'-link\'));</script>';
		} // for
	}
	return $output;
}

function render_add_reminders_config($reminder_opt) {
	$defaults = array();
	$def = explode(",", user_config_option($reminder_opt));
	$default_defaults = array(
		'type' => array_var($def, 0),
		'duration' => array_var($def, 1),
		'duration_type' => array_var($def, 2)
	);

	foreach ($default_defaults as $k => $v) {
		if (!isset($defaults[$k])) $defaults[$k] = $v;
	}
	$types = ObjectReminderTypes::findAll();
	$typecsv = array();
	foreach ($types as $type) {
		$typecsv []= $type->getName();
	}
	$durations = array(0,1,2,5,10,15,30);
	$duration_types = array("1" => "minutes","60" => "hours","1440" => "days","10080" => "weeks");

	$output = '<select name="options['.$reminder_opt.'][reminder_type]">';
	foreach ($typecsv as $type) {
		$output .= '<option value="' . $type . '"';
		if ($type == array_var($defaults, 'type')) {
			$output .= ' selected="selected"';
		}
		$output .= '>' . lang($type) . '</option>';
	}
	$output .= '</select>';

	$output .= '<select name="options['.$reminder_opt.'][reminder_duration]">';
	foreach ($durations as $duration) {
		$output .= '<option value="' . $duration . '"';
		if ($duration == array_var($defaults, 'duration')) {
			$output .= ' selected="selected"';
		}
		$output .= '>' . $duration . '</option>';
	}
	$output .= '</select>';

	$output .= '<select name="options['.$reminder_opt.'][reminder_duration_type]">';
	foreach ($duration_types as $key => $value) {
		$output .= '<option value="' . $key . '"';
		if ($key == array_var($defaults, 'duration_type')) {
			$output .= ' selected="selected"';
		}
		$output .= '>' . lang($value) . '</option>';
	}
	$output .= '</select>';
	
	return $output;
}

/**
 * Renders a form to set an object's custom properties.
 *
 * @param ContentDataObject $object
 * @return string
 */
function render_add_custom_properties(ContentDataObject $object) {
	
	if (!config_option('use_object_properties')) {
		return '';
	}
	
	$genid = gen_id();
	$output = '
        <label>'.lang('properties').'</label>
		<div id="'.$genid.'" class="og-add-custom-properties" style="float:left;">
			<table><tbody><tr>
			<th>' . lang('name') . '</th>
			<th>' . lang('value') . '</th>
			<th class="actions"></th>
			</tr></tbody></table>
			<a href="#" onclick="og.addObjectCustomProperty(this.parentNode, \'\', \'\', true);return false;">' . lang("add custom property") . '</a>
		</div>
		<div class="clear"></div>
		<script>
		var ti = 30000;
		og.addObjectCustomProperty = function(parent, name, value, focus) {
			var count = parent.getElementsByTagName("tr").length - 1;
			var tbody = parent.getElementsByTagName("tbody")[0];
			var tr = document.createElement("tr");
			var td = document.createElement("td");
			td.innerHTML = \'<input class="name" type="text" name="custom_prop_names[\' + count + \']" value="\' + name + \'" tabindex=\' + ti + \'>\';;
			if (td.children) var input = td.children[0];
			tr.appendChild(td);
			var td = document.createElement("td");
			td.innerHTML = \'<input class="value" type="text" name="custom_prop_values[\' + count + \']" value="\' + value + \'" tabindex=\' + (ti + 1) + \'>\';;
			tr.appendChild(td);
			var td = document.createElement("td");
			td.innerHTML = \'<div class="db-ico ico-delete" style="margin-left:2px;height:20px;cursor:pointer" onclick="og.removeCustomProperty(this.parentNode.parentNode);return false;">&nbsp;</div>\';
			tr.appendChild(td);
			tbody.appendChild(tr);
			if (input && focus)
				input.focus();
			ti += 2;
		}
		og.removeCustomProperty = function(tr) {
			var parent = tr.parentNode;
			parent.removeChild(tr);
			// reorder property names
			var row = parent.firstChild;
			var num = -1; // first row has no inputs
			while (row != null) {
				if (row.tagName == "TR") {
					var inputs = row.getElementsByTagName("INPUT");
					for (var i=0; i < inputs.length; i++) {
						var input = inputs[i];
						if (input.className == "name") {
							input.name = "custom_prop_names[" + num + "]";
						} else {
							input.name = "custom_prop_values[" + num + "]";
						}
					}
					num++;
				}
				row = row.nextSibling;
			}
		}
		</script>
	';
	$properties = ObjectProperties::getAllPropertiesByObject($object);
	if (is_array($properties)) {
		foreach($properties as $property) {
			$output .= '<script>og.addObjectCustomProperty(document.getElementById("'.$genid.'"), "'.clean($property->getPropertyName()).'", "'.clean($property->getPropertyValue()).'");</script>';
		} // for
	} // if
	$output .= '<script>og.addObjectCustomProperty(document.getElementById("'.$genid.'"), "", "");</script>';
	return $output;
}

/**
 * Renders an object's custom properties
 * @return string
 */
function render_custom_properties(ApplicationDataObject $object, $visibility='all') {
	tpl_assign('__properties_object', $object);
	tpl_assign('visibility', $visibility);
	return tpl_fetch(get_template_path('view', 'custom_properties'));
}

/**
 * Returns a control to select mail account
 *
 * @param string $name
 * 		Name for the control
 * @param array $mail_accounts
 * 		Array of accounts to choose from
 * @param array $selected
 * 		Index of account selected by default
 * @return string
 * 		HTML for the control
 */
function render_select_mail_account($name, $mail_accounts, $selected = null, $attributes = null) {
	$options = null;
	if(is_array($mail_accounts)) {
		foreach($mail_accounts as $mail_account) {
			$option_attributes = $mail_account->getId() == $selected ? array('selected' => 'selected') : null;
			$mail = $mail_account->getName() . " [" . $mail_account->getEmail() . "]";
			$options[] = option_tag($mail, $mail_account->getId(), $option_attributes);
		} // foreach
	} // if
	return select_box($name, $options, $attributes);
} //  render_select_mail_account


/**
 * Render select task priority box
 *
 * @param integer $selected Selected priority
 * @param array $attributes Additional attributes
 * @return string
 */
function select_task_priority($name, $selected = null, $attributes = null) {
	$options = array(
		option_tag(lang('urgent priority'), ProjectTasks::PRIORITY_URGENT, ($selected >= ProjectTasks::PRIORITY_URGENT)?array('selected' => 'selected'):null),
		option_tag(lang('high priority'), ProjectTasks::PRIORITY_HIGH, ($selected >= ProjectTasks::PRIORITY_HIGH && $selected < ProjectTasks::PRIORITY_URGENT)?array('selected' => 'selected'):null),
		option_tag(lang('normal priority'), ProjectTasks::PRIORITY_NORMAL, ($selected > ProjectTasks::PRIORITY_LOW && $selected < ProjectTasks::PRIORITY_HIGH)?array('selected' => 'selected'):null),
		option_tag(lang('low priority'), ProjectTasks::PRIORITY_LOW, ($selected <= ProjectTasks::PRIORITY_LOW)?array('selected' => 'selected'):null),
	);
	return select_box($name, $options, $attributes);
} // select_task_priority

function select_object_type($name, $types, $selected = null, $attributes = null) {
	$options = array();
	foreach ($types as $type) {
		$options[] = option_tag($type->getName(), $type->getId(), ($selected == $type->getId())?array('selected' => 'selected'):null);
	}
	return select_box($name, $options, $attributes);
} // select_task_priority


/**
 * Render assign to SELECT
 * @param string $list_name Name of the select control
 * @param Project $project Selected project, if NULL active project will be used
 * @param integer $selected ID of selected user
 * @param array $attributes Array of select box attributes, if needed
 * @return null
 */ 
function filter_assigned_to_select_box($list_name, $project = null, $selected = null, $attributes = null) {
	$grouped_users = Contacts::getGroupedByCompany(false);
	$options = array(option_tag(lang('anyone'), '0:0'),option_tag(lang('unassigned'), '-1:-1', '-1:-1' == $selected ? array('selected' => 'selected') : null));
	
	if(is_array($grouped_users) && count($grouped_users)) {
		foreach($grouped_users as $company_id => $users) {
			$company = Contacts::findById($company_id);
			if(!($company instanceof Contact)) {
				continue;
			} // if

			$options[] = option_tag('--', '0:0'); // separator

			$option_attributes = $company->getId() . ':0' == $selected ? array('selected' => 'selected') : null;
			$options[] = option_tag($company->getName(), $company_id . ':0', $option_attributes);

			if(is_array($users)) {
				foreach($users as $user) {
					$option_attributes = $company_id . ':' . $user->getId() == $selected ? array('selected' => 'selected') : null;
					$options[] = option_tag($user->getObjectName() . ' : ' . $company->getObjectName() , $company_id . ':' . $user->getId(), $option_attributes);
				} // foreach
			} // if

		} // foreach
	} // if

	return select_box($list_name, $options, $attributes);
} // assign_to_select_box


/**
 * Renders context help in a view, only if description_key is a valid lang.
 * If helpTemplate is null, default template is used
 *
 * @param $view View where the context help will be placed
 * @param string $description_key Key of the description to show, if not exists help will not be shown.
 * @param string $option_name
 * @param string $helpTemplate
 */
function render_context_help($view, $description_key, $option_name = null, $helpTemplate = null) {
	/*FIXME to show context help if ($view != null && $description_key != null && Localization::instance()->lang_exists($description_key)) {
		
		if ($option_name != null) { 
			tpl_assign('option_name' , $option_name);
		}
		
		if ($helpTemplate == null) {
			tpl_assign('helpDescription', lang($description_key));
		} else {
			tpl_assign('helpTemplate', $helpTemplate);
		}
		
		$view->includeTemplate(get_template_path('context_help', 'help'));
	}*/
}


function has_context_to_render($content_object_type_id) {
	if (is_numeric($content_object_type_id)) {
		$user_dimensions  = get_user_dimensions_ids(); // User allowed dimensions
		$dimensions = array() ;
		if ( $all_dimensions = Dimensions::getAllowedDimensions($content_object_type_id) ) { // Diemsions for this content type
			foreach ($all_dimensions as $dimension){ // A kind of intersection...
				if ( isset($user_dimensions[$dimension['dimension_id']] ) && $dimension['is_manageable'] ){
					return true;					
				}
			}
		} 
	}
	return false; 
}

function get_associated_dimensions_to_reload_json($dimension_id) {
	if (defined('JSON_NUMERIC_CHECK')) {
		$reloadDimensions = json_encode( DimensionMemberAssociations::instance()->getDimensionsToReloadByObjectType($dimension_id), JSON_NUMERIC_CHECK );
	} else {
		$reloadDimensions = json_encode( DimensionMemberAssociations::instance()->getDimensionsToReloadByObjectType($dimension_id) );
	}
	
	return $reloadDimensions;
}

/**
 * @param unknown_type $content_object_type_id
 * @param unknown_type $genid
 * @param unknown_type $selected_members
 * @param unknown_type $options
 * @param unknown_type $skipped_dimensions
 * @param unknown_type $simulate_required
 */
function render_dimension_trees($content_object_type_id, $genid = null, $selected_members = null, $options = array(), $skipped_dimensions = null, $simulate_required = null) { 
		if (is_numeric($content_object_type_id)) {
			if (is_null($genid)) $genid = gen_id();
			$user_dimensions  = get_user_dimensions_ids(); // User allowed dimensions
			$dimensions = array() ;
			if ( $all_dimensions = Dimensions::getAllowedDimensions($content_object_type_id) ) { // Diemsions for this content type
				foreach ($all_dimensions as $dimension){ // A kind of intersection...
					if ( isset($user_dimensions[$dimension['dimension_id']] ) ){
						$custom_name = DimensionOptions::getOptionValue($dimension['dimension_id'], 'custom_dimension_name');
						$dimension['name'] = $custom_name && trim($custom_name) != "" ? $custom_name : lang($dimension['code']);
						
						$dimensions[] = $dimension;
					}
				}
			}
			
			$object_is_new = is_null($selected_members);
			
			if ($dimensions!= null && count($dimensions)) {
				if (is_null($selected_members) && array_var($options, 'select_current_context')) {
					$context = active_context();
					$selected_members = array();
					foreach ($context as $selection) {
						if ($selection instanceof Member) $selected_members[] = $selection->getId(); 
					}
				}
				
				$selected_members_json = json_encode($selected_members);
				$component_id  = "$genid-member-chooser-panel-$content_object_type_id" ;
				
				if (isset($options['layout']) && in_array($options['layout'], array('horizontal', 'column'))) {
					$layout = $options['layout'];
				} else {
					//$layout = count($dimensions) > 5 ? "horizontal" : "column";
					$layout = "column";
				}
				?>
				
				<?php if (!$object_is_new) : ?> 
				<input id='<?php echo $genid; ?>trees_not_loaded' name='trees_not_loaded' type='hidden' value="1"></input>
				<?php endif;?>
				
				<input id='<?php echo $genid; ?>members' name='members' type='hidden' value="<?php echo str_replace('"', "'", $selected_members_json); ?>"></input>
				<div id='<?php echo $component_id ?>-container' class="member-chooser-container" ></div>
				
				<script>
					var memberChooserPanel = new og.MemberChooserPanel({
						renderTo: '<?php echo $component_id ?>-container',
						id: '<?php echo $component_id ?>',
						selectedMembers: <?php echo $selected_members_json?>,
						layout: '<?php echo $layout; ?>'
					}) ;
					
					<?php  
					foreach ($dimensions as $dimension) : 
						$dimension_id = $dimension['dimension_id'];
						if (is_array($skipped_dimensions) && in_array($dimension_id, $skipped_dimensions)) continue;
						
						if ( is_array(array_var($options, 'allowedDimensions')) && array_search($dimension_id, $options['allowedDimensions']) === false ){
						    //removed for PHP7 compatiblity:
						    //continue;
						}

						if (!$dimension['is_manageable']) continue;
						
						$is_required = $dimension['is_required'];				
						$dimension_name = escape_character($dimension['dimension_name']);				
						if ($is_required) $dimension_name.= " *" ;
						
						if (is_array($simulate_required) && in_array($dimension_id, $simulate_required))
							$is_required = true;
						
						if (!isset($id)) $id = gen_id();
						
						$reloadDimensions = get_associated_dimensions_to_reload_json($dimension_id);
						
					?>
					var config = {
							title: '<?php echo $dimension_name ?>',
							dimensionId: <?php echo $dimension_id ?>,
							objectTypeId: <?php echo $content_object_type_id ?>,
							required: <?php echo $is_required ?>,
							reloadDimensions: <?php echo $reloadDimensions ?>,
							isMultiple: <?php echo $dimension['is_multiple'] ?>,
							selModel: <?php echo ($dimension['is_multiple'])?
								'new Ext.tree.MultiSelectionModel()':
								'new Ext.tree.DefaultSelectionModel()'?>
													
					};

					
					<?php if( isset ($options['allowedMemberTypes'])) : ?>
						config.allowedMemberTypes = <?php  echo json_encode($options['allowedMemberTypes']) ?> ;
					<?php endif; ?>
					<?php if( isset ($options['collapsible'])) : ?>
						config.collapsible = <?php  echo (int)$options['collapsible'] ?> ;
					<?php endif; ?>
					<?php if( isset ($options['collapsed'])) : ?>
						config.collapsed = <?php  echo (int) $options['collapsed'] ?> ;
					<?php endif; ?>

					config.listeners = {
						'tree rendered': function(tree) {
							if (!tree.ownerCt.rendered_trees) tree.ownerCt.rendered_trees = 0;
							tree.ownerCt.rendered_trees++;
							if (tree.ownerCt.rendered_trees == tree.ownerCt.items.length) tree.ownerCt.fireEvent('all trees rendered', tree.ownerCt);
						}
					};

					var tree = new og.MemberChooserTree ( config );
					
					memberChooserPanel.add(tree);
					<?php endforeach; ?>

					og.can_submit_members = false;
					memberChooserPanel.on('all trees rendered', function(panel) {
						og.can_submit_members = true;
						var trees_to_reload = [];
						panel.items.each(function(item, index, length) {
							var checked = item.getLastChecked();
							if (checked != 0 && item.filterOnChange) trees_to_reload.push(item);
						});
						
						if (trees_to_reload.length > 0) {
							for (var i=0; i<trees_to_reload.length; i++) {
								trees_to_reload[i].dont_update_form = true;
								tree = trees_to_reload[i];
								setTimeout(function() { tree.dont_update_form = false; }, 2500);
							}
							
							for (var i=1; i<trees_to_reload.length; i++) {
								var next = trees_to_reload[i];
								trees_to_reload[i-1].on('all trees updated', function(){
									next.fireEvent('checkchange', next.getNodeById(next.getLastChecked()), true);
									next.expand();
								});
							}

							var t = trees_to_reload[0];
							t.fireEvent('checkchange', t.getNodeById(t.getLastChecked()), true);
							t.expand();
						}
					}); 
					
					memberChooserPanel.doLayout();

				</script>

<?php 
			}
		}
}


/**
 * 
 * Builds a tree based on generic node types 
 * @param string $parentField	- Parent Field attribute
 * @param string $childField	- Children list attribute
 * @param string $idField		- Node Identificator
 * @param string $textField 	- The field name to show
 */
function buildTree ($nodeList , $parentField = "parent", $childField = "children", $idField = "id", $textField = "name", $checkedField = "_checked") {
	$tree = array() ;
	$inserted = array();
	do {
		$insertedCount = 0 ;		
		foreach ($nodeList as $k => &$node) {
			if ($textField) $node["text"] = $node[$textField];
			$node['leaf'] = true;
			if (array_var($node, 'selectable')) $node[$checkedField] = false;
			$parentId = array_var($node, $parentField, 0);
			$id = $node[$idField];
			if ( !isset($inserted[$id])){
				if ($parentId == 0) {
					$tree[] = &$node ; 		
					$inserted[$id] = &$node ;
					$insertedCount++;
					unset ($nodeList[$k]);
				}else{					
					if (isset ($inserted[$parentId] )) {
						$inserted[$parentId][$childField][] =  &$node ;
						$inserted[$parentId]["leaf"] = false ;
						//$inserted[$parentId]["expanded"] = true ;
						$inserted[$id] = &$node ;
						$insertedCount++;
						unset ($nodeList[$k]);
					}
				}
			}
			 
		} 
	}	while ($insertedCount > 0 ) ;
	return $tree  ;
}

	function build_member_list_text_to_show_in_trees(&$memberList) {
		
		$option_values = array();
		
		foreach ($memberList as &$member_data) {
			
			if ($member_data instanceof Member) {
				$ot_id = $member_data->getObjectTypeId();
				$dim_id = $member_data->getDimensionId();
				$member_id = $member_data->getId();
				$object_id = $member_data->getObjectId();
			} else {
				$ot_id = $member_data['object_type_id'];
				$dim_id = $member_data['dimension_id'];
				$member_id = $member_data['id'];
				$object_id = $member_data['object_id'];
			}
			
			if (!isset($option_values[$ot_id])) {
				$opt_val = DimensionObjectTypeOptions::getOptionValue($dim_id, $ot_id, 'text_to_show_in_trees');
				if (!$opt_val) $opt_val = " ";
				$option_values[$ot_id] = $opt_val;
			}
			
			if ($option_values[$ot_id] && trim($option_values[$ot_id]) != '') {
				$option_decoded = json_decode($option_values[$ot_id], true);
				
				$prop_values_array = array();
				foreach ($option_decoded['properties'] as $col) {
					$is_member_column = $member_data instanceof Member ? Members::instance()->columnExists($col) : array_key_exists($col, $member_data);
					if ($is_member_column) {
						$prop_values_array[] = $member_data instanceof Member ? $member_data->getColumnValue($col) : array_var($member_data, $col);
						
					} else if (str_starts_with($col, "cp_")) {
						$cp_id = str_replace("cp_", "", $col);
						
						if ($object_id > 0) {
							// is dimension_object
							$cp_val_obj = CustomPropertyValues::getCustomPropertyValue($object_id, $cp_id);
							$cp_val = $cp_val_obj instanceof CustomPropertyValue ? $cp_val_obj->getValue() : '';
						} else {
							// is dimension_group
							if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
								$cp_val_obj = MemberCustomPropertyValues::getMemberCustomPropertyValue($member_id, $cp_id);
								$cp_val = $cp_val_obj instanceof MemberCustomPropertyValue ? $cp_val_obj->getValue() : '';
							}
						}
						if ($cp_val) {
							$prop_values_array[] = $cp_val;
						}
					}
				}
				$prop_values_array = array_filter($prop_values_array);
				
				$separator = trim(array_var($option_decoded, 'separator', '-'));
				if ($separator == "") {
					$separator = " ";
				} else {
					$separator = " $separator ";
				}
				
				if (count($prop_values_array) > 0) {
					if ($member_data instanceof Member) {
						$member_data->setName(implode($separator, $prop_values_array));
					} else {
						$member_data['name'] = implode($separator, $prop_values_array);
						$member_data['text'] = $member_data['name'];
					}
				}
				
			}
		}
		
	}
	
	
	function append_other_properties_search_conditions(Dimension $dimension, $query_string, &$search_name_cond) {
		
		$option_values = DimensionObjectTypeOptions::getOptionValuesForAllObjectTypes($dimension->getId(), 'text_to_show_in_trees');
		
		if (is_array($option_values) && count($option_values) > 0) {
			$conditions = array();
			
			foreach ($option_values as $option_value) {
				/* @var $option_value DimensionObjectTypeOption */
				$raw_val = $option_value->getValue();
				
				if (trim($raw_val) != "") {
					$option_decoded = json_decode($raw_val, true);
					
					if (isset($option_decoded['properties']) && count($option_decoded['properties']) > 0) { 
						foreach ($option_decoded['properties'] as $col) {
							if (Members::instance()->columnExists($col)) {
								$conditions[] = "$col LIKE '%".$query_string."%'";
						
							} else if (str_starts_with($col, "cp_")) {
								$cp_id = str_replace("cp_", "", $col);
								$ot = ObjectTypes::instance()->findById($option_value->getObjectTypeId());
						
								if ($ot->getType() == 'dimension_object') {
									$conditions[] = "EXISTS (
										SELECT `value` FROM ".TABLE_PREFIX."custom_property_values cpv
										WHERE cpv.custom_property_id='$cp_id' AND `value` LIKE '%".$query_string."%'
										AND cpv.object_id=".TABLE_PREFIX."members.object_id
									)
									";
								} else {
									if (Plugins::instance()->isActivePlugin('member_custom_properties')) {
										$conditions[] = "EXISTS (
											SELECT `value` FROM ".TABLE_PREFIX."member_custom_property_values cpv
											WHERE cpv.custom_property_id='$cp_id' AND `value` LIKE '%".$query_string."%'
											AND cpv.member_id=".TABLE_PREFIX."members.id
										)
										";
									}
								}
							}
						}
						
						if (count($conditions) > 0) {
							$search_name_cond = " AND (" . implode(" OR ", $conditions) . ")";
						}
					}
				}
			}
		}
	}


	function render_single_dimension_tree($dimension, $genid = null, $selected_members = array(), $options = array()) {
		if ($dimension instanceof Dimension) {
			$dimension_info = array('dimension_id' => $dimension->getId(), 'dimension_name' => $dimension->getName(), 'is_multiple' => $dimension->getAllowsMultipleSelection());
		} else {
			$dimension_info = $dimension;
		}
		
		$custom_name = DimensionOptions::getOptionValue($dimension_info['dimension_id'], 'custom_dimension_name');
		$dimension_info['dimension_name'] = ($custom_name && trim($custom_name) != "" ? $custom_name : $dimension_info['dimension_name']);
		
		$dimension_id  = $dimension_info['dimension_id'];
		if (is_null($genid)) $genid = gen_id();
		$selected_members_json = json_encode($selected_members);
		if (!isset($options['component_id'])) {
			$component_id = "$genid-member-chooser-panel-$dimension_id";
		} else {
			$component_id = $options['component_id'];
		}
		
		?>
		 
		<?php if( isset($options['use_ajax_member_tree']) && $options['use_ajax_member_tree'] ) {?>
		<?php }else{ ?>
			<input id='<?php echo $genid . array_var($options, 'pre_hf_id', '') ?>members' name='<?php echo array_var($options, 'pre_hf_id', '') ?>members' type='hidden' ></input> 
		<?php } ?>

		<div id='<?php echo $component_id ?>-container' class="<?php echo array_var($options, 'pre_class', '')?>single-tree member-chooser-container <?php echo array_var($dimension_info, 'is_multiple') ? "multiple-selection" : "single-selection"; ?>" ></div>
		
		<script>
			var memberChooserPanel = new og.MemberChooserPanel({
				renderTo: '<?php echo $component_id ?>-container',
				id: '<?php echo $component_id ?>',
				selectedMembers: <?php echo $selected_members_json?>,
				layout: 'column'
			}) ;
			
			<?php			 
				if ( is_array(array_var($options, 'allowedDimensions')) && array_search($dimension_id, $options['allowedDimensions']) === false ){
					//continue;
				    return false;
				}					
				$dimension_name = escape_character($dimension_info['dimension_name']);
				if (!isset($id)) $id = gen_id();
			?>
			var select_root = <?php echo (array_var($options, 'select_root') ? '1' : '0') ?>;
			var config = {
				id: '<?php echo $component_id ?>-tree',
				genid: '<?php echo $genid ?>',
				title: '<?php echo $dimension_name ?>',
				dimensionId: <?php echo $dimension_id ?>,
				search_placeholder: '<?php echo array_var($options, 'search_placeholder', lang('search') )?>',
				filterContentType: '<?php echo array_var($options, 'filterContentType', 1)?>',		
				collapsed: <?php echo array_var($options, 'collapsed') ? 'true' : 'false'?>,
				collapsible: <?php echo array_var($options, 'collapsible') ? 'true' : 'false'?>,
				all_members: <?php echo array_var($options, 'all_members') ? 'true' : 'false'?>,
				objectTypeId: '<?php echo array_var($options, 'object_type_id', 0) ?>',
				isMultiple: '<?php echo array_var($options, 'is_multiple', 0) ?>',
				selModel: <?php echo (array_var($dimension_info, 'is_multiple'))?
					'new Ext.tree.MultiSelectionModel()':
					'new Ext.tree.DefaultSelectionModel()'?>,
				height: <?php echo array_var($options, 'height', '270') ?>,
				width: <?php echo array_var($options, 'width', '385') ?>,
				listeners: {'tree rendered': function (t) {if (select_root) t.root.select();}}
			};
		
			<?php if( isset ($options['root_lang'])) : ?>
				config.root_lang = <?php echo json_encode($options['root_lang']) ?>;
			<?php endif; ?>
			
			<?php if( isset ($options['allowedMemberTypes'])) : ?>
				config.allowedMemberTypes = <?php echo json_encode($options['allowedMemberTypes']) ?>;
			<?php endif; ?>

			<?php if( isset ($options['checkBoxes']) && !$options['checkBoxes']) : ?>
				config.checkBoxes = false;
			<?php endif; ?>

			<?php if( isset ($options['loadUrl']) ) : ?>
				config.loadUrl = '<?php echo $options['loadUrl'] ?>';
				<?php if( isset ($options['loadAdditionalParameters']) ) : ?>
				config.loadAdditionalParameters = '<?php echo json_encode($options['loadAdditionalParameters']);?>';
				<?php endif; ?>
			<?php endif; ?>

			<?php if( array_var($options, 'enableDD')) : ?>
				config.enableDD = true;
				config.dropConfig = {
					ddGroup: '<?php echo array_var($options, 'ddGroup')?>',
					allowContainerDrop: true
				};
				config.dragConfig = {
					ddGroup: '<?php echo array_var($options, 'ddGroup')?>',
					containerScroll: true
				};
			<?php endif; ?>
			
			<?php if( isset ($options['loadUrl']) ) : ?>
				config.loadUrl = '<?php echo $options['loadUrl'] ?>';
			<?php endif; ?>

			<?php if( isset ($options['filter_by_ids'])) : ?>
				config.filter_by_ids = '<?php implode(',', $options['filter_by_ids']) ?>' ;
			<?php endif; ?>

			<?php if( isset($options['use_ajax_member_tree']) && $options['use_ajax_member_tree'] ) {?>
				<?php if( isset($options['select_function'])) {?>
					config.selectFunction = '<?php echo $options['select_function'] ?>';
				<?php }else{ ?>
					config.selectFunction = '<?php echo ""?>';
				<?php } ?>
				var tree = new og.MemberTreeAjax ( config );
			<?php }else{ ?>
				var tree = new og.MemberChooserTree ( config );
			<?php } ?>
			
			<?php if(array_var($options, 'enableDD') && array_var($options, 'enddrag_function')) : ?>
				tree.on('enddrag', <?php echo array_var($options, 'enddrag_function')?>);
			<?php endif; ?>
			<?php if(array_var($options, 'enableDD') && array_var($options, 'beforenodedrop_function')) : ?>
				tree.on('beforenodedrop', <?php echo array_var($options, 'beforenodedrop_function')?>);
			<?php endif; ?>
			<?php if(array_var($options, 'enableDD') && array_var($options, 'startdrag_function')) : ?>
				tree.on('startdrag', <?php echo array_var($options, 'startdrag_function')?>);
			<?php endif; ?>
			
			memberChooserPanel.add(tree);
			og.can_submit_members = true;
			
			<?php if (!isset($options['dont_load']) || !$options['dont_load']) : ?>
			memberChooserPanel.initialized = true;
			memberChooserPanel.doLayout();
			<?php endif; ?>
		</script>
	
<?php 
	}




function render_single_bootstrap_dimension_tree($dimension, $genid = null, $selected_members = array(), $options = array()) {
    if ($dimension instanceof Dimension) {
        $dimension_info = array('dimension_id' => $dimension->getId(), 'dimension_name' => $dimension->getName(), 'is_multiple' => $dimension->getAllowsMultipleSelection());
    } else {
        $dimension_info = $dimension;
    }

    $custom_name = DimensionOptions::getOptionValue($dimension_info['dimension_id'], 'custom_dimension_name');
    $dimension_info['dimension_name'] = ($custom_name && trim($custom_name) != "" ? $custom_name : $dimension_info['dimension_name']);

    $dimension_id  = $dimension_info['dimension_id'];
    if (is_null($genid)) $genid = gen_id();
    $selected_members_json = json_encode($selected_members);
    if (!isset($options['component_id'])) {
        $component_id = "$genid-member-chooser-panel-$dimension_id";
    } else {
        $component_id = $options['component_id'];
    }

    ?>

    <?php if( isset($options['use_ajax_member_tree']) && $options['use_ajax_member_tree'] ) {?>
    <?php }else{ ?>
        <input id='<?php echo $genid . array_var($options, 'pre_hf_id', '') ?>members' name='<?php echo array_var($options, 'pre_hf_id', '') ?>members' type='hidden' ></input>
    <?php } ?>

    <div id='<?php echo $component_id ?>-container' class="<?php echo array_var($options, 'pre_class', '')?>single-tree member-chooser-container <?php echo array_var($dimension_info, 'is_multiple') ? "multiple-selection" : "single-selection"; ?>" >

    </div>

    <script>
        var memberChooserPanel = new og.MemberChooserPanel({
            renderTo: '<?php echo $component_id ?>-container',
            id: '<?php echo $component_id ?>',
            selectedMembers: <?php echo $selected_members_json?>,
            layout: 'column'
        }) ;

        <?php
        if ( is_array(array_var($options, 'allowedDimensions')) && array_search($dimension_id, $options['allowedDimensions']) === false ){
            //continue;
            return false;
        }
        $dimension_name = escape_character($dimension_info['dimension_name']);
        if (!isset($id)) $id = gen_id();
        ?>
        var select_root = <?php echo (array_var($options, 'select_root') ? '1' : '0') ?>;
        var config = {
            id: '<?php echo $component_id ?>-tree',
            genid: '<?php echo $genid ?>',
            title: '<?php echo $dimension_name ?>',
            dimensionId: <?php echo $dimension_id ?>,
            search_placeholder: '<?php echo array_var($options, 'search_placeholder', lang('search') )?>',
            filterContentType: '<?php echo array_var($options, 'filterContentType', 1)?>',
            collapsed: <?php echo array_var($options, 'collapsed') ? 'true' : 'false'?>,
            collapsible: <?php echo array_var($options, 'collapsible') ? 'true' : 'false'?>,
            all_members: <?php echo array_var($options, 'all_members') ? 'true' : 'false'?>,
            objectTypeId: '<?php echo array_var($options, 'object_type_id', 0) ?>',
            isMultiple: '<?php echo array_var($options, 'is_multiple', 0) ?>',
            selModel: <?php echo (array_var($dimension_info, 'is_multiple'))?
                'new Ext.tree.MultiSelectionModel()':
                'new Ext.tree.DefaultSelectionModel()'?>,
            //height: <?php echo array_var($options, 'height', '270') ?>,
            width: '100%',
            listeners: {'tree rendered': function (t) {if (select_root) t.root.select();}}
        };

        <?php if( isset ($options['root_lang'])) : ?>
        config.root_lang = <?php echo json_encode($options['root_lang']) ?>;
        <?php endif; ?>

        <?php if( isset ($options['allowedMemberTypes'])) : ?>
        config.allowedMemberTypes = <?php echo json_encode($options['allowedMemberTypes']) ?>;
        <?php endif; ?>

        <?php if( isset ($options['checkBoxes']) && !$options['checkBoxes']) : ?>
        config.checkBoxes = false;
        <?php endif; ?>

        <?php if( isset ($options['loadUrl']) ) : ?>
        config.loadUrl = '<?php echo $options['loadUrl'] ?>';
        <?php endif; ?>

        <?php if( array_var($options, 'enableDD')) : ?>
        config.enableDD = true;
        config.dropConfig = {
            ddGroup: '<?php echo array_var($options, 'ddGroup')?>',
            allowContainerDrop: true
        };
        config.dragConfig = {
            ddGroup: '<?php echo array_var($options, 'ddGroup')?>',
            containerScroll: true
        };
        <?php endif; ?>

        <?php if( isset ($options['loadUrl']) ) : ?>
        config.loadUrl = '<?php echo $options['loadUrl'] ?>';
        <?php endif; ?>

        <?php if( isset ($options['filter_by_ids'])) : ?>
        config.filter_by_ids = '<?php implode(',', $options['filter_by_ids']) ?>' ;
        <?php endif; ?>

        <?php if( isset($options['use_ajax_member_tree']) && $options['use_ajax_member_tree'] ) {?>
        <?php if( isset($options['select_function'])) {?>
        config.selectFunction = '<?php echo $options['select_function'] ?>';
        <?php }else{ ?>
        config.selectFunction = '<?php echo ""?>';
        <?php } ?>
        var tree = new og.MemberTreeAjax ( config );
        <?php }else{ ?>
        var tree = new og.MemberChooserTree ( config );
        <?php } ?>

        <?php if(array_var($options, 'enableDD') && array_var($options, 'enddrag_function')) : ?>
        tree.on('enddrag', <?php echo array_var($options, 'enddrag_function')?>);
        <?php endif; ?>
        <?php if(array_var($options, 'enableDD') && array_var($options, 'beforenodedrop_function')) : ?>
        tree.on('beforenodedrop', <?php echo array_var($options, 'beforenodedrop_function')?>);
        <?php endif; ?>
        <?php if(array_var($options, 'enableDD') && array_var($options, 'startdrag_function')) : ?>
        tree.on('startdrag', <?php echo array_var($options, 'startdrag_function')?>);
        <?php endif; ?>

        memberChooserPanel.add(tree);
        og.can_submit_members = true;

        <?php if (!isset($options['dont_load']) || !$options['dont_load']) : ?>
        memberChooserPanel.initialized = true;
        memberChooserPanel.doLayout();
        <?php endif; ?>



        var selectorPadre = '#<?php echo $component_id ?>-container';
        var selectorHijoDiv = '#<?php echo $component_id ?>-tree-current-selected';

        $(selectorPadre).find('input').removeClass().addClass('form-control')
        $(selectorHijoDiv).addClass('form-control');

    </script>

    <?php
}

/**
 * @param string  $str
 * @param lenght $length
 * @param end $end
 */
function wrap_text($str, $length = 20, $end='...'){
	if (function_exists('mb_strlen')) {
		return ( mb_strlen($str) > $length ) ? mb_strcut($str,0 ,$length - mb_strlen($end) ).$end : $str;
	} else {
		return ( strlen($str) > $length ) ? substr($str, 0, $length - strlen($end)).$end : $str;
	}
}

/**
 * 
 * Renders members vinculations for the object
 * @param ContentDataObject $object
 */
function render_co_view_member_path(ContentDataObject $object) {
	tpl_assign('object', $object);
	return tpl_fetch(get_template_path('member_path', 'co'));
}

function render_add_working_days() {
        $options = explode(",",config_option("working_days"));
        
        $days = array("0" => lang('sunday'),"1" => lang('monday') , "2" => lang('tuesday'),
                                "3" => lang('wednesday'),"4" => lang('thursday'),"5" => lang('friday'),
                                "6" => lang('saturday'));
        $output = '';  
        foreach ($days as $key => $value){
            $sel = '';
            $output .= '<div style="float: left;width: 80px;"><label>' . $value . ':</label>'; 
            foreach ($options as $option) {
                if ($option == $key) {
                        $sel = 'checked="checked"';
                }                
            }
            $output .= '<input class="checkbox" type="checkbox" value="' . $key . '" name="options[working_days][]" ' . $sel . '/></div>';
        }
        
	return $output;
}


function render_widget_option_input($widget_option, $genid=null) {
	if (is_null($genid)) $genid = gen_id();
	$output = "";
	$name = 'widgets['.$widget_option['widget'].'][options]['.$widget_option['option'].']';
	switch ($widget_option['handler']) {
		case 'UserCompanyConfigHandler' :
			if ($widget_option['widget'] == 'overdue_upcoming') $ot = ObjectTypes::findByName('task');
			else break;
			
			$users = allowed_users_in_context($ot->getId(), array(), ACCESS_LEVEL_READ, '', true);
			$has_myself = false;
			foreach ($users as $u) {
				if ($u->getId() == logged_user()->getId()) $has_myself = true;
			}
			if (!$has_myself) array_unshift($users, logged_user());
			
			$output .= "<select name='$name' id='".$genid.$name."' onchange='og.on_widget_select_option_change(this);'>";
			$sel = $widget_option['value'] == 0 ? 'selected="selected"' : '';
			$output .= "<option value='0' $sel>".lang('everyone')."</option>";
			foreach ($users as $user) {
				$sel = $widget_option['value'] == $user->getId() ? 'selected="selected"' : '';
				$output .= "<option value='".$user->getId()."' $sel>".$user->getObjectName()."</option>";
			}
			$output .= "</select>";
			break;
		case 'BooleanConfigHandler' :
			$output .= yes_no_widget($name, $genid.$name, $widget_option['value'], lang('yes'), lang('no'), null, array('onchange' => 'og.on_widget_radio_option_change(this);'));
			break;
		default: break;
	}
	
	return $output;
}


function get_dates_for_date_range_config($data_saved) {
    $st = '';
    $et = '';
    $data = array();
    $now = DateTimeValueLib::now();
    //$now->advance(logged_user()->getUserTimezoneValue(), true);
    
    switch($data_saved->type){
        case "today":
            $st = DateTimeValueLib::make(0,0,0,$now->getMonth(),$now->getDay(),$now->getYear());
            $et = DateTimeValueLib::make(23,59,59,$now->getMonth(),$now->getDay(),$now->getYear());
            break;
        case "this_week":
            $monday = $now->getMondayOfWeek();
            $nextMonday = $now->getMondayOfWeek()->add('w',1)->add('d',-1);
            $st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
            $et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());
            break;
        case "last_week":
            $monday = $now->getMondayOfWeek()->add('w',-1);
            $nextMonday = $now->getMondayOfWeek()->add('d',-1);
            $st = DateTimeValueLib::make(0,0,0,$monday->getMonth(),$monday->getDay(),$monday->getYear());
            $et = DateTimeValueLib::make(23,59,59,$nextMonday->getMonth(),$nextMonday->getDay(),$nextMonday->getYear());
            break;
        case "this_month":
            $st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
            $et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);
            break;
        case "last_month":
            $now->add('M',-1);
            $st = DateTimeValueLib::make(0,0,0,$now->getMonth(),1,$now->getYear());
            $et = DateTimeValueLib::make(23,59,59,$now->getMonth(),1,$now->getYear())->add('M',1)->add('d',-1);
            break;
        case "this_quarter":
            $current_month = $now->getMonth();
            $current_year = $now->getYear();            
            switch ($current_month){
                case 1:
                case 2:
                case 3:
                    $st = DateTimeValueLib::make(0,0,0,1,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,3,31,$current_year);
                    break;
                case 4:
                case 5:
                case 6:
                    $st = DateTimeValueLib::make(0,0,0,4,1,$current_year);                    
                    $et = DateTimeValueLib::make(23,59,59,6,30,$current_year);
                    break;
                case 7:
                case 8:
                case 9:
                    $st = DateTimeValueLib::make(0,0,0,7,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,9,30,$current_year);
                    break;
                case 10:
                case 11:
                case 12:
                    $st = DateTimeValueLib::make(0,0,0,10,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,12,31,$current_year);
                    break;                    
            }
            break;
        case "last_quarter":
            $current_month = $now->getMonth();
            $current_year = $now->getYear();            
            switch ($current_month){
                case 1:
                case 2:
                case 3:
                    $st = DateTimeValueLib::make(0,0,0,10,1,$current_year)->add('y', -1);
                    $et = DateTimeValueLib::make(23,59,59,12,31,$current_year)->add('y', -1);
                    break;
                case 4:
                case 5:
                case 6:
                    $st = DateTimeValueLib::make(0,0,0,1,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,3,31,$current_year);
                    break;
                case 7:
                case 8:
                case 9:
                    $st = DateTimeValueLib::make(0,0,0,4,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,6,30,$current_year);
                    break;
                case 10:
                case 11:
                case 12:
                    $st = DateTimeValueLib::make(0,0,0,7,1,$current_year);
                    $et = DateTimeValueLib::make(23,59,59,9,30,$current_year);
                    break;
            }
            break;
        case "this_year":
            $st = DateTimeValueLib::make(0,0,0,1,1,$now->getYear());
            $et = DateTimeValueLib::make(23,59,59,1,1,$now->getYear())->add('y',1)->add('d',-1);
            break;
        case "last_year":
            $now->add('y',-1);
            $st = DateTimeValueLib::make(0,0,0,1,1,$now->getYear());
            $et = DateTimeValueLib::make(23,59,59,1,1,$now->getYear())->add('y',1)->add('d',-1);
            break;
        case "range":
            if ( trim($data_saved->range_start) != '' ) {
                $st = DateTimeValueLib::makeFromString( trim($data_saved->range_start) );
                $st = $st->beginningOfDay();
            }
            
            if ( trim($data_saved->range_end) != '' ) {
                $et = DateTimeValueLib::makeFromString( trim($data_saved->range_end) );
                $et = $et->endOfDay();
            }
            
            break;
    }
    
    if ($st instanceof DateTimeValue) {
        $st->add('s',-logged_user()->getUserTimezoneValue());
    }
    if ($et instanceof DateTimeValue) {
        $et->add('s',-logged_user()->getUserTimezoneValue());
    }
    $data["from_date"] = $st;
    $data["to_date"] = $et;
    
    return $data;
    
    
}

    function create_contact_from_data($contact_data, $members_ids) {
        $members_ids = array_unique(array_filter($members_ids));
        if (count($members_ids) > 0) {
            $members_encoded = json_encode($members_ids);
        } else {
            $members_encoded = "[]";
        }
        $_POST = array(
            'contact' => $contact_data,
            'members' => $members_encoded,
        );
        $contact_controller = new ContactController();
        return $contact_controller->add();
    }
