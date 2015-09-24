<?php

$highlight_first_obj_link = false;
$highlight_more_settings_link = false;

$highlighted_step = config_option('getting_started_step');
if (!$highlighted_step) $highlighted_step = 1;

$links = array();
if (can_manage_configuration(logged_user())) {
	$links[] = array(
		'id' => 'system_modules',
		'ico' => 'ico-large-tabs',
		'url' => get_url('more', 'system_modules'),
		'name' => lang('system modules'),
		'extra' => '',
	);
}
if (can_manage_security(logged_user())) {
	$links[] = array(
		'id' => 'users_and_groups',
		'ico' => 'ico-large-group',
		'url' => get_url('more', 'users_and_groups', array('only_full_users' => (config_option('getting_started_step') < 99 ? '1' : '0'))),
		'name' => lang('users groups and permissions'),
		'extra' => '',
	);
}

$min_steps = 3;
$more_settings_expanded = array_var($_REQUEST, 'more_settings_expanded');

if (config_option('getting_started_step') < 99 && !$more_settings_expanded) {

	// dimension links
	if (can_manage_dimension_members(logged_user())) {
		Hook::fire('more_panel_dimension_links', null, $links);
		$min_steps = count($links);
	}
	
	// add your first object link
	if (config_option('getting_started_step') < 98) {
		
		$object_count_rows = DB::executeAll("SELECT count(o.id) as cant, ot.id as ot_id, ot.name FROM ".TABLE_PREFIX."objects o INNER JOIN ".TABLE_PREFIX."object_types ot ON o.object_type_id=ot.id 
			WHERE ot.name IN ('task','message','weblink','file','expense','objective','event') AND o.trashed_by_id=0 AND o.archived_by_id=0 GROUP BY ot_id");
		$object_count = array();
		foreach ($object_count_rows as $row) {
			$object_count[$row['name']] = $row['cant'];
		}
		
		$first_tab_panel = TabPanels::findOne(array('conditions' => "object_type_id > 0 AND enabled=1 AND id NOT IN ('more-panel', 'reporting-panel', 'mails-panel') AND 
				(plugin_id is NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))", 'order' => 'ordering'));
		
		if ($first_tab_panel instanceof TabPanel) {
			$ot = ObjectTypes::findById($first_tab_panel->getObjectTypeId());
			if ($ot instanceof ObjectType) {
				
				switch ($ot->getName()) {
					case "task":
						$selector = '.task-list-row-template .btn.btn-xs.btn-primary'; break;
					case "message": 
					case "weblink": 
					case "file":
					case "expense":
					case "objective":
					case "mail":
					case "contact":
					case "event":
						$selector = '#'.$first_tab_panel->getId().' .new_button'; break;
					default: break;
				}
				
				$skip_this_step = false;
				switch ($ot->getName()) {
					case "task":
					case "message":
					case "weblink":
					case "file":
					case "expense":
					case "objective":
					case "event":
						$skip_this_step = ($object_count[$ot->getName()] > 0); break;
					default: break;
				}
				
				if ($skip_this_step) {
					
					$step = 99;
					
				} else {
				
					$step = 98;
					$hint_text = lang('click here to add a new', strtolower(lang($ot->getName())));
					$acitvate_tab_js = "var panel = Ext.getCmp('".$first_tab_panel->getId()."'); Ext.getCmp('tabs-panel').setActiveTab(panel); og.highlight_link({selector:'$selector', step:$step, time_active:30000, timeout:500, hint_text:'$hint_text', hint_pos:'right', animate_opacity:10, reload_panel:true})";
					$add_first_obj_url = "javascript:$acitvate_tab_js";
					
					$icon_class = $ot->getName() == 'file' ? 'ico-large-text-html' : 'ico-large-' . $ot->getName();
					$add_first_obj = array(
							'id' => 'add_first_object',
							'ico' => $icon_class,
							'url' => $add_first_obj_url,
							'name' => lang('add your first', strtolower(lang($ot->getName()))),
							'extra' => '',
					);
					if ($add_first_obj_onclick != null) {
						$add_first_obj['onclick'] = $add_first_obj_onclick;
					}
					$links[] = $add_first_obj;
				}
			}
			
			$highlight_first_obj_link = true;
		}
		
	} else {
		
		$highlight_more_settings_link = true;
	}
	
	// more settings link
	$selector = "#userbox-settings";
	$click_on_settings_js = "javascript:showUserOptionsPanel(); og.highlight_link({selector:'$selector', step:99, time_active:30000, timeout:500, animate_opacity:10, hint_text:'".lang('click here')."', hint_pos:'left'});";
	
	$links[] = array(
		'id' => 'more_settings',
		'ico' => 'ico-large-config',
		'url' => $click_on_settings_js,
		'name' => lang('more settings'),
		'extra' => '',
	);
	
	
} else {
	
	
	Hook::fire('render_administration_dimension_icons', null, $links);
	
	if (can_manage_configuration(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-company',
				'url' => get_url('administration', 'company'),
				'name' => lang('organization data'),
				'extra' => '',
		);
		$links[] = array(
				'ico' => 'ico-large-configuration',
				'url' => get_url('administration', 'configuration'),
				'name' => lang('configuration'),
				'extra' => '',
		);
	}
	
	if (can_manage_templates(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-template',
				'url' => get_url('template', 'index'),
				'name' => lang('templates'),
				'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('template','add') . '">' . lang('add template') . '</a>',
		);
	}
	
	if (can_manage_billing(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-billing',
				'url' => get_url('billing', 'index'),
				'name' => lang('billing'),
				'extra' => '<a class="internalLink coViewAction ico-add" href="' . get_url('billing', 'add') . '">' . lang('add billing category') . '</a>',
		);
	}
	
	if (can_manage_configuration(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-custom-properties',
				'url' => get_url('administration', 'custom_properties'),
				'name' => lang('custom properties'),
				'extra' => '',
		);
	}
	
	if (can_manage_dimensions(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-config2',
				'url' => get_url('administration', 'dimension_options'),
				'name' => lang('dimension options'),
				'extra' => '',
		);
	}
	
	Hook::fire('render_administration_icons', null, $links);
	
	if (can_manage_security(logged_user()) && Plugins::instance()->isActivePlugin('income')) {
		$links[] = array(
				'ico' => 'ico-large-income',
				'url' => get_url('income', 'administration'),
				'name' => lang('income'),
				'extra' => '',
		);
	}
	
	
	if (defined("PLUGIN_MANAGER") && PLUGIN_MANAGER && can_manage_plugins(logged_user())) {
		$links[] = array(
				'ico' => 'ico-large-plugins',
				'url' => get_url('plugin', 'index'),
				'name' => lang('plugins'),
				'extra' => '',
		);
	}
	
}

$count = 0;
foreach ($links as $link) {
	$count++;
	if (!$more_settings_expanded) {
		if (array_var($link, 'id') == 'add_first_object' && $highlight_first_obj_link && $count > $min_steps + 1) {
			$highlighted_step = $count;
		}
		if (array_var($link, 'id') == 'more_settings' && $highlight_more_settings_link && $count > $min_steps) {
			$highlighted_step = $count;
		}
	} else {
		$highlighted_step = 0;
	}
?>
<a <?php echo (isset($link['onclick']) ? 'onclick="'.$link['onclick'].'"' : '') ?> class="internalLink" href="<?php echo $link['url'] ?>" <?php echo isset($link['target']) ? 'target="'.$link['target'].'"' : '' ?>>
	<div class="link highlighted <?php echo ($highlighted_step == $count ? 'on' : 'off')?>" id="<?php echo $genid?>_link_<?php echo $count?>">
		
		<div class="highlighted-number"><?php echo $count?></div>
		<div class="coViewIconImage <?php echo $link['ico']?>"></div>
    	
		<?php echo $link['name'] ?>
		<div class="clear"></div>
	</div>
</a>
<?php
}
?>
<div class="clear"></div>