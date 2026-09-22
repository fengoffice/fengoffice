<?php
$root_permissions = array_var($permission_parameters, 'root_permissions', array());
?>

<div id="<?php echo $genid?>_root_permissions"
	class="root-permissions"
	style="<?php echo (isset($is_new_user) && $is_new_user ? "display:none;" : "")?>">

<fieldset>
	<legend>
		<span class="og-task-expander toggle_expanded" style="padding-left:20px;"
			onclick="og.toggle('<?php echo $genid ?>root_permissions');
				if ($(this).hasClass('toggle_expanded')) {
					$(this).removeClass('toggle_expanded').addClass('toggle_collapsed');
				} else {
					$(this).removeClass('toggle_collapsed').addClass('toggle_expanded');
				}">
			<?php echo lang('permissions for unclassified objects'); ?>
		</span>
	</legend>

	<div id="<?php echo $genid ?>root_permissions" style="width:600px;">
	<table style="width:100%;">

	<tr class="permissions-title-row">
		<td></td>
		<td align="center" style="width:120px;">
			<a href="#" class="internalLink all-radio-sel radio-title-3"
			   onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 3);return false;">
				<?php echo lang('read write and delete') ?>
			</a>
		</td>
		<td align="center" style="width:120px;">
			<a href="#" class="internalLink all-radio-sel radio-title-2"
			   onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 2);return false;">
				<?php echo lang('read and write') ?>
			</a>
		</td>
		<td align="center" style="width:120px;">
			<a href="#" class="internalLink all-radio-sel radio-title-1"
			   onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 1);return false;">
				<?php echo lang('read only') ?>
			</a>
		</td>
		<td align="center" style="width:120px;">
			<a href="#" class="internalLink all-radio-sel radio-title-0"
			   onclick="og.ogRootPermSetLevel('<?php echo $genid ?>', 0);return false;">
				<?php echo lang('none no bars') ?>
			</a>
		</td>
	</tr>

	<tr class="permissions-checkall-row">
		<td style="padding-left:20px;"><?php echo lang('check all').":"?></td>
		<td align="center">
			<input type="checkbox" class="all-radio-sel-chk-root" id="chk-3"
				onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 3);" />
		</td>
		<td align="center">
			<input type="checkbox" class="all-radio-sel-chk-root" id="chk-2"
				onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 2);" />
		</td>
		<td align="center">
			<input type="checkbox" class="all-radio-sel-chk-root" id="chk-1"
				onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 1);" />
		</td>
		<td align="center">
			<input type="checkbox" class="all-radio-sel-chk-root" id="chk-0"
				onchange="og.ogRootPermSetLevelCheckbox(this, '<?php echo $genid ?>', 0);" />
		</td>
	</tr>

<?php
$all_object_types = ObjectTypes::instance()->findAll(array(
	'conditions' => "type IN ('content_object', 'located')
		AND type NOT IN ('comment')
		AND name <> 'file revision'
		AND name <> 'template_task'
		AND name <> 'template_milestone'
		AND name <> 'template'
		AND (plugin_id IS NULL OR plugin_id = 0 OR plugin_id IN (
			SELECT id FROM ".TABLE_PREFIX."plugins
			WHERE is_activated > 0 AND is_installed > 0
		))"
));

$row_cls = "";
$root_object_types = array();

foreach ($all_object_types as $ot) {
	if ($ot->getName() == 'mail') continue;
	if ($ot->getName() == 'expense_item') continue;

	$row_cls = $row_cls == "" ? "altRow" : "";
	$id_suffix = "root_" . $ot->getId();
	$root_object_types[] = $ot->getId();

	$info = array_var($root_permissions, $ot->getId(), array());

	$can_delete = array_var($info, 'd') == 1;
	$can_write  = !$can_delete && array_var($info, 'w') == 1;
	$can_read   = !$can_delete && !$can_write && array_var($info, 'r') == 1;
	$none       = !$can_delete && !$can_write && !$can_read;
?>
	<tr class="<?php echo $row_cls ?>">
		<td style="padding-left:20px;"><?php echo $ot->getObjectTypeName() ?></td>
		<td align="center"><?php echo radio_field($genid.'rg_'.$id_suffix, $can_delete, array('value'=>'3','onchange'=>"og.ogRootPermValueChanged('".$genid."')")) ?></td>
		<td align="center"><?php echo radio_field($genid.'rg_'.$id_suffix, $can_write,  array('value'=>'2','onchange'=>"og.ogRootPermValueChanged('".$genid."')")) ?></td>
		<td align="center"><?php echo radio_field($genid.'rg_'.$id_suffix, $can_read,   array('value'=>'1','onchange'=>"og.ogRootPermValueChanged('".$genid."')")) ?></td>
		<td align="center"><?php echo radio_field($genid.'rg_'.$id_suffix, $none,       array('value'=>'0','onchange'=>"og.ogRootPermValueChanged('".$genid."')")) ?></td>
	</tr>
<?php } ?>

	</table>
	<input type="hidden" name="root_perm_genid" value="<?php echo $genid ?>" />
	</div>
</fieldset>
</div>

<script>
var genid = '<?php echo $genid ?>';

og.perm_root_object_type_ids = Ext.util.JSON.decode('<?php echo json_encode($root_object_types) ?>');
</script>
