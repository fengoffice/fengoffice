
<?php if (can_manage_security(logged_user())) { ?>
<table style="width:100%;"><tr><td style="padding-right:10px;width:50%;">
<fieldset class=""><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>userSystemPermissions',this)"><?php echo lang("system permissions") ?></legend>
	<div id="<?php echo $genid ?>userSystemPermissions" style="display:block" class="user-system-permissions">
	
		<?php
			$columns = SystemPermissions::instance()->getColumns();
			$hidden_cols = array('permission_group_id', 'can_view_billing', 'can_task_assignee');
			foreach ($columns as $column_name) :
				if (in_array($column_name, $hidden_cols)) continue;
		?>
				<div id="<?php echo $genid ?>div_<?php echo $column_name ?>">
				<?php
					$attributes = array('id' => $genid . 'sys_perm['.$column_name.']');
					if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
						$attributes['onclick'] = 'return false;';
						$attributes['class'] = 'disabled';
					}
					echo checkbox_field('sys_perm['.$column_name.']', $system_permissions instanceof SystemPermission ? $system_permissions->getColumnValue($column_name) : false, $attributes) ?> 
			      <label for="<?php echo $genid . 'sys_perm['.$column_name.']' ?>" class="checkbox"><?php echo lang($column_name) ?></label>
			      <a class="help-sign" href="javascript:og.toggle('<?php echo $genid . $column_name ?>_help')">?</a>
			      <div id="<?php echo $genid . $column_name ?>_help" class="permissions-help" style="display:none"><?php echo lang($column_name . ' description') ?></div>
			    </div>
		<?php 
			endforeach;
		?>
	    
		<?php
			$other_permissions = array();
			if (isset($user) && !is_null($user)) {
				Hook::fire('add_user_permissions', $user, $other_permissions);
			}
			foreach ($other_permissions as $perm => $perm_val) {?>
				<div id="<?php echo $genid ?>div_<?php echo $perm ?>">
			      <?php  
			        $attributes = array('id' => $genid . "sys_perm[$perm]");
					if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
						$attributes['onclick'] = 'return false;';
						$attributes['class'] = 'disabled';
					}
					echo checkbox_field("sys_perm[$perm]", array_var($more_permissions, $perm), $attributes) ?> 
			      <label for="<?php echo $genid . "sys_perm[$perm]" ?>" class="checkbox"><?php echo lang($perm) ?></label>
			      <a class="help-sign" href="javascript:og.toggle('<?php echo $genid ?><?php echo $perm ?>_help')">?</a>
			      <div id="<?php echo $genid ?><?php echo $perm ?>_help" class="permissions-help" style="display:none"><?php echo lang($perm.' description') ?></div>
				</div>
			<?php }
		?>
		<?php if (!isset($disable_sysperm_inputs) || !$disable_sysperm_inputs) : ?>
		<div style="height:10px;"></div>
		<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) { if (!$(checks[i]).prop('disabled')) checks[i].checked = true;}"><?php echo lang('check all')?></a>
		<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = false;"><?php echo lang('uncheck all')?></a>
		<?php endif; ?>
	</div>
</fieldset>


<?php 	if (is_array($all_modules_info) && count($all_modules_info) > 0) {?>
</td><td style="padding-left:10px;width:50%;">
<fieldset class=""><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>userModulePermissions',this)"><?php echo lang("module permissions") ?></legend>
	<div id="<?php echo $genid ?>userModulePermissions" style="display:block" class="user-module-permissions">	
	<?php foreach ($all_modules_info as $mod_info) { ?>
	
		<div id="<?php echo $genid . array_var($mod_info, 'id')?>">
	      <?php  
			$attributes = array('id' => $genid . 'mod_perm['.array_var($mod_info, 'ot').']');
			if (isset($disable_sysperm_inputs) && $disable_sysperm_inputs) {
				$attributes['onclick'] = 'return false;';
				$attributes['class'] = 'disabled';
			}
			echo checkbox_field('mod_perm['.array_var($mod_info, 'id').']', array_var($module_permissions_info, array_var($mod_info, 'id')), $attributes) ?> 
	      <label for="<?php echo $genid . 'mod_perm['.array_var($mod_info, 'ot').']' ?>" class="checkbox"><?php echo array_var($mod_info, 'name') ?></label>
		</div>
	
	<?php } ?>
	
	<?php if (!isset($disable_sysperm_inputs) || !$disable_sysperm_inputs) : ?>
		<div style="height:10px;"></div>
		<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) {if (!$(checks[i]).prop('disabled')) checks[i].checked = true;}"><?php echo lang('check all')?></a>
		<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = false;"><?php echo lang('uncheck all')?></a>
	<?php endif; ?>
	</div>
</fieldset>
<?php 	} ?>

</td></tr></table>
<?php } ?>



<?php 
	tpl_assign('genid', $genid);
	
	tpl_assign('member_types', $permission_parameters['member_types']);
	tpl_assign('allowed_object_types_by_member_type', $permission_parameters['allowed_object_types_by_member_type']);
	tpl_assign('allowed_object_types', $permission_parameters['allowed_object_types']);
	tpl_assign('all_object_types', $permission_parameters['all_object_types']);
	tpl_assign('member_permissions', $permission_parameters['member_permissions']);
	tpl_assign('dimensions', $permission_parameters['dimensions']);
	
	$this->includeTemplate(get_template_path('user_permissions_control', 'account'));
?>

<?php if (config_option('let_users_create_objects_in_root') && (isset($user) && $user instanceof Contact && ($user->isAdminGroup() || $user->isExecutive() || $user->isManager())) ){ ?>
<div id="<?php echo $genid?>_root_permissions" class="root-permissions" style="<?php echo (isset($is_new_user) && $is_new_user ? "display:none;" : "")?>">

<fieldset><legend><span class="og-task-expander toggle_expanded" style="padding-left:20px;" 
	onclick="og.toggle('<?php echo $genid ?>root_permissions'); if ($(this).hasClass('toggle_expanded')){$(this).removeClass('toggle_expanded');$(this).addClass('toggle_collapsed');} else {$(this).removeClass('toggle_collapsed');$(this).addClass('toggle_expanded');}">
	<?php echo lang('permissions for unclassified objects');?></span></legend>
	 
  <div id="<?php echo $genid ?>root_permissions" style="width:600px;">
  <table style="width:100%;">
  <tr style="border-bottom:1px solid #888;margin-bottom:5px"><td></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:120px;"><a href="#" class="internalLink radio-title-3" onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 3);return false;"><?php echo lang('read write and delete') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:120px;"><a href="#" class="internalLink radio-title-2" onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 2);return false;"><?php echo lang('read and write') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:120px;"><a href="#" class="internalLink radio-title-1" onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 1);return false;"><?php echo lang('read only') ?></a></td>
  	<td align=center style="padding-left:10px;padding-right:10px;width:120px;"><a href="#" class="internalLink radio-title-0" onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 0);return false;"><?php echo lang('none no bars') ?></a></td>
  </tr>
<?php 
	$all_object_types = ObjectTypes::instance()->findAll(array('conditions' => "type IN ('content_object', 'located') AND type NOT IN ('comment') AND name <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'template' AND 
		(plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))"));
	$row_cls = "";
	$root_object_types = array();
	
	$root_permissions = $permission_parameters['root_permissions'];
	foreach ($all_object_types as $ot) {
		if ($ot->getName() == 'mail' || $ot->getName() == 'template') continue;
		$row_cls = $row_cls == "" ? "altRow" : "";
		$id_suffix = "root_" . $ot->getId();
		$root_object_types[] = $ot->getId();
		$root_perm_actual_info = array_var($root_permissions, $ot->getId());
		
		$can_delete = false; $can_write = false; $can_read = false; $none = false;
		if (array_var($root_perm_actual_info, 'd') == 1) {
			$can_delete = true;
		} else if (array_var($root_perm_actual_info, 'w') == 1) {
			$can_write = true;
		} else if (array_var($root_perm_actual_info, 'r') == 1) {
			$can_read = true;
		} else {
			$none = true;
		}
		$can_delete = array_var($root_perm_actual_info, 'd') == 1;
		$can_write = array_var($root_perm_actual_info, 'd') == 0 && array_var($root_perm_actual_info, 'w') == 1;
		$can_read = array_var($root_perm_actual_info, 'd') == 0 && array_var($root_perm_actual_info, 'w') == 0 && array_var($root_perm_actual_info, 'r') == 1;

?><tr class="<?php echo $row_cls?>">
  	<td style="padding:0 20px"><span id="<?php echo $genid.'obj_type_label'.$id_suffix?>"><?php echo lang($ot->getName()) ?></span></td>
  	<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, $can_delete, array('value' => '3')) ?></td>
  	<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, $can_write, array('value' => '2')) ?></td>
  	<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, $can_read, array('value' => '1')) ?></td>
  	<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, $none, array('value' => '0')) ?></td>
  </tr>
<?php } ?>
  </table>
  <input type="hidden" name="root_perm_genid" value="<?php echo $genid?>" />
  </div>
</fieldset>
</div>
<script>
var genid = '<?php echo $genid?>';

og.perm_root_object_type_ids = Ext.util.JSON.decode('<?php echo json_encode($root_object_types)?>');
og.ogRootPermSetLevel = function (genid, level) {
	for (i=0; i<og.perm_root_object_type_ids.length; i++) {
		var ot = og.perm_root_object_type_ids[i];
		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_root_" + ot), level);
	}
}
</script>
<?php }?>

<?php $role_id = isset($user) && $user instanceof Contact ? $user->getUserType() : $pg_id;
if ($role_id > 0 && !(isset($user_group_abm) && $user_group_abm)) { ?>
<script>
$(function() {
	var type = '<?php echo $role_id ?>';
	og.userPermissions.enableDisableSystemPermissionsByRole(genid, type);
});
</script>
<?php } ?>