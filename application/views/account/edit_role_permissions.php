<?php
require_javascript("og/Permissions.js");
ajx_current('users_and_groups');
set_page_title(lang('role permissions'));

$genid = gen_id();
$root_object_types = array();

/**
 * Variables expected:
 * - $role
 * - $permission_group_id
 * - $system_permissions
 * - $all_modules_info
 * - $module_permissions_info
 * - $root_permissions
 * - $max_root_permissions
 * - $role_type
 */
?>

<script>
og.maxRolePermissions = <?php echo json_encode($max_root_permissions); ?>;
og.currentRoleId = <?php echo (int)$permission_group_id ?>;
</script>

<div class="adminConfiguration">

<form class="internalForm"
      action="<?php echo get_url('administration', 'default_role_permissions_save') ?>"
      method="post"
      target="more-panel"
      onsubmit="if (typeof og.enableRootPermissionRadiosForSubmit === 'function') og.enableRootPermissionRadiosForSubmit(this); return true;">

	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('Default permissions for role') ?>
				<?php if ($role instanceof PermissionGroup) { ?>
					<span class="role-name">
						– <?php echo clean($role->getName()) ?>
					</span>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="coInputMainBlock adminMainBlock">

	<div class="role-permissions-block" id="role_<?php echo $genid ?>">

	<table class="permissions-main-table">
	<tr>

	<!-- ================= SYSTEM PERMISSIONS ================= -->
	<td class="permissions-col-left">
		<fieldset>
			<legend class="toggle_expanded"
				onclick="og.toggle('<?php echo $genid ?>userSystemPermissions',this)">
				<?php echo lang("system permissions") ?>
			</legend>

			<input type="hidden" name="role_id" value="<?php echo $permission_group_id ?>">

			<div id="<?php echo $genid ?>userSystemPermissions" class="user-system-permissions">
				<?php
				$columns = SystemPermissions::instance()->getColumns();
				$hidden_cols = array('permission_group_id','can_view_billing','can_task_assignee');

				foreach ($columns as $column_name):
					if (in_array($column_name, $hidden_cols)) continue;
				?>
<div class="perm-row">

	<?php
	echo checkbox_field(
		'sys_perm['.$column_name.']',
		$system_permissions instanceof SystemPermission
			? $system_permissions->getColumnValue($column_name)
			: false,
		array('id' => $genid.'sys_perm_'.$column_name)
	);
	?>

	<div class="perm-label">
		<label for="<?php echo $genid.'sys_perm_'.$column_name ?>" class="checkbox">
			<?php echo lang($column_name) ?>
		</label>

		<span class="perm-help-toggle"
		      data-target="<?php echo $genid . $column_name ?>_help"
		      title="<?php echo lang('help') ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
			     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
			     aria-hidden="true">
				<circle cx="12" cy="12" r="10"></circle>
				<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
				<path d="M12 17h.01"></path>
			</svg>
		</span>
	</div>

	<div id="<?php echo $genid . $column_name ?>_help"
	     class="permissions-help"
	     style="display:none">
		<?php echo lang($column_name . ' description') ?>
	</div>

</div>

				<?php endforeach; ?>
				<div style="height:10px;"></div>
				<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) { if (!$(checks[i]).prop('disabled')) checks[i].checked = true;}"><?php echo lang('check all')?></a>
				<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) { if (!$(checks[i]).prop('disabled')) checks[i].checked = false;}"><?php echo lang('uncheck all')?></a>
			</div>
		</fieldset>
	</td>

	<!-- ================= MODULE PERMISSIONS ================= -->
	<?php if (is_array($all_modules_info) && count($all_modules_info) > 0) { ?>
	<td class="permissions-col-right">
		<fieldset>
			<legend class="toggle_expanded"
				onclick="og.toggle('<?php echo $genid ?>userModulePermissions',this)">
				<?php echo lang("module permissions") ?>
			</legend>

			<div id="<?php echo $genid ?>userModulePermissions" class="user-module-permissions">
				<?php foreach ($all_modules_info as $mod_info) { ?>
					<div class="perm-row">
						<?php
						echo checkbox_field(
							'mod_perm['.$mod_info['id'].']',
							array_var($module_permissions_info, $mod_info['id']),
							array('id' => $genid.'mod_perm_'.$mod_info['id'])
						);
						?>
						<label for="<?php echo $genid.'mod_perm_'.$mod_info['id'] ?>" class="checkbox">
							<?php echo $mod_info['name'] ?>
						</label>
					</div>
				<?php } ?>
				<div style="height:10px;"></div>
				<a href="#" class="internalLink ogTasksGroupAction ico-complete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) { if (!$(checks[i]).prop('disabled')) checks[i].checked = true;}"><?php echo lang('check all')?></a>
				<a href="#" class="internalLink ogTasksGroupAction ico-delete" onclick="checks=this.parentNode.getElementsByTagName('input'); for(i=0;i<checks.length;i++) checks[i].checked = false;"><?php echo lang('uncheck all')?></a>
			</div>
		</fieldset>
	</td>
	<?php } ?>

	</tr>
	</table>

	<!-- ================= ROOT OBJECT TYPE PERMISSIONS ================= -->
	<?php if (config_option('let_users_create_objects_in_root')) { ?>

	<div class="root-permissions">

	<fieldset>
	<legend><?php echo lang('Default permissions by object type') ?></legend>

	<table class="permissions-root-table">

	<tr class="permissions-title-row">
		<th></th>
		<th><span class="permission-level-title"><?php echo str_replace(' & ', ' &<br>', lang('read write and delete')) ?></span></th>
		<th><span class="permission-level-title"><?php echo lang('read and write') ?></span></th>
		<th><span class="permission-level-title"><?php echo lang('read only') ?></span></th>
		<th><span class="permission-level-title"><?php echo lang('none no bars') ?></span></th>
	</tr>

	<tr class="permissions-checkall-row">
		<td><?php echo lang('check all').":" ?></td>
		<td><input type="checkbox" class="all-radio-sel-chk-root" id="chk-3"
		           title="<?php echo lang('set rwd permissions for all object types')?>"
		           onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 3, og.maxRolePermissions[og.currentRoleId], og.currentRoleId);" /></td>
		<td><input type="checkbox" class="all-radio-sel-chk-root" id="chk-2"
		           title="<?php echo lang('set rw permissions for all object types')?>"
		           onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 2, og.maxRolePermissions[og.currentRoleId], og.currentRoleId);" /></td>
		<td><input type="checkbox" class="all-radio-sel-chk-root" id="chk-1"
		           title="<?php echo lang('set r permissions for all object types')?>"
		           onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 1, og.maxRolePermissions[og.currentRoleId], og.currentRoleId);" /></td>
		<td><input type="checkbox" class="all-radio-sel-chk-root" id="chk-0"
		           title="<?php echo lang('set none permissions for all object types')?>"
		           onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 0, og.maxRolePermissions[og.currentRoleId], og.currentRoleId);" /></td>
	</tr>

	<?php
	$is_alt = false;
	$all_object_types = ObjectTypes::instance()->findAll(array(
		'conditions' => "type IN ('content_object', 'located') AND type NOT IN ('comment') AND name <> 'file revision' AND name <> 'template_task' AND name <> 'template_milestone' AND `name` <> 'template' AND 
		(plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (SELECT id FROM ".TABLE_PREFIX."plugins WHERE is_activated > 0 AND is_installed > 0))"
	));

	foreach ($all_object_types as $ot):
		if ($ot->getName() == 'mail' || $ot->getName() == 'template') continue;

		$is_alt = !$is_alt;
		$row_cls = $is_alt ? 'altRow' : '';

		$ot_id = $ot->getId();
		$root_object_types[] = $ot_id;
		$current = array_var($root_permissions, $ot_id, array());

		$can_delete = array_var($current, 'd') == 1;
		$can_write = !$can_delete && array_var($current, 'w') == 1;
		$can_read = !$can_delete && !$can_write && array_var($current, 'r') == 1;
		$none = !$can_delete && !$can_write && !$can_read;
	?>
	<tr class="<?php echo $row_cls ?>">
		<td><?php echo $ot->getObjectTypeName() ?></td>
		<td><?php echo radio_field($genid.'root_'.$ot_id, $can_delete, array('value'=>'3','id'=>$genid.'rg_3_'.$ot_id, 'onchange' => "og.ogRootPermValueChanged('".$genid."')")); ?></td>
		<td><?php echo radio_field($genid.'root_'.$ot_id, $can_write, array('value'=>'2','id'=>$genid.'rg_2_'.$ot_id, 'onchange' => "og.ogRootPermValueChanged('".$genid."')")); ?></td>
		<td><?php echo radio_field($genid.'root_'.$ot_id, $can_read, array('value'=>'1','id'=>$genid.'rg_1_'.$ot_id, 'onchange' => "og.ogRootPermValueChanged('".$genid."')")); ?></td>
		<td><?php echo radio_field($genid.'root_'.$ot_id, $none, array('value'=>'0','id'=>$genid.'rg_0_'.$ot_id, 'onchange' => "og.ogRootPermValueChanged('".$genid."')")); ?></td>
	</tr>
	<?php endforeach; ?>

	</table>
	<input type="hidden" name="root_perm_genid" value="<?php echo $genid?>" />
	</fieldset>

	<button class="submit"
	        type="submit"
	        accesskey="s">
	    Save
	</button>

	</div>
	<?php } ?>

	</div>
	</div>
</form>
</div>

<?php if (isset($role_type) && $role_type > 0) { ?>
<script>
var genid = '<?php echo $genid?>';

og.perm_root_object_type_ids = Ext.util.JSON.decode('<?php echo json_encode($root_object_types)?>');

$(function() {

	if (typeof og.ogLoadPermissions === 'function') {
		og.ogLoadPermissions('<?php echo $genid ?>');
	}

	if (og.userPermissions && typeof og.userPermissions.enableDisableSystemPermissionsByRole === 'function') {
		og.userPermissions.enableDisableSystemPermissionsByRole(
			'<?php echo $genid ?>',
			'<?php echo $role_type ?>'
		);
	}
	
	if (og.uncheckDisabledSystemPermissions && typeof og.uncheckDisabledSystemPermissions === 'function') {
		og.uncheckDisabledSystemPermissions('<?php echo $genid ?>');
	}

	if (og.applyRootPermissionsMaxUI && typeof og.applyRootPermissionsMaxUI === 'function' && og.maxRolePermissions && og.maxRolePermissions[og.currentRoleId]) {
		og.applyRootPermissionsMaxUI('<?php echo $genid ?>', og.currentRoleId, og.maxRolePermissions[og.currentRoleId], true);
	}

	if (og.clampRootRadiosToMax && typeof og.clampRootRadiosToMax === 'function' && og.maxRolePermissions && og.maxRolePermissions[og.currentRoleId]) {
		og.clampRootRadiosToMax(
			'<?php echo $genid ?>',
			og.currentRoleId,
			og.maxRolePermissions[og.currentRoleId]
		);
	}

	if (typeof og.ogRootPermValueChanged === 'function') {
		og.ogRootPermValueChanged('<?php echo $genid ?>');
	}

	$('.role-permissions-block .perm-help-toggle').on('click', function () {
		var target = $(this).data('target');
		$('#' + target).slideToggle(150);
	});

});
</script>
<?php } ?>
