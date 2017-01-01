<?php
  if (!isset($genid)) {
  	$genid = gen_id();
  }

  if (!isset($save_js_function)) {
  	$save_js_function = "og.saveObjectTypeCustomProperties('$genid');";
  }

  $type_name = (lang($object_type->getName().'s'));

?>

<script>
	var genid = '<?php echo $genid?>';
	og.admin_cp_count = {};
	og.admin_cp_count['<?php echo $genid?>'] = 0;

	og.custom_props_table_genids = [];
	og.custom_props_table_genids = ['<?php echo $genid?>'];

</script>

<div class="custom-properties-admin object-type <?php echo $object_type->getName() ?>">

<div class="coInputHeader">
<table><tr><td>
  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php if (array_var($extra_params, 'form_title')) {
			echo array_var($extra_params, 'form_title');
		} else {
			echo lang('custom properties for', $type_name);
		} ?>
	</div>
	<div class="desc">
		<?php echo lang('custom properties reorder help') ?>
	</div>
  </div>
</td><td style="min-width:160px;">
  <div class="coInputHeaderUpperRow">
  	<div class="coInputButtons">
  		<?php echo submit_button(lang('save changes'), null, array('onclick' => $save_js_function, 'style' => 'margin-top:0; margin-left:20px;')) ?>
  	</div>
  </div>
</td></tr></table>
</div>

<div class="coInputMainBlock adminMainBlock">
	<input type="hidden" id="<?php echo $genid?>_ot_id" value="<?php echo $object_type->getId() ?>"/>

	<?php
		tpl_assign('genid', $genid);
		tpl_assign('type_name', $type_name);
		tpl_assign('extra_params', $extra_params);
		tpl_display(get_template_path('cp_table_template', 'administration'));
	?>

	<?php
	$fire_sections_hook = !isset($dont_fire_hook) || !$dont_fire_hook;
	if ($fire_sections_hook) {
		$null = null;
		Hook::fire('custom_property_form_sections', array('ot' => $object_type, 'genid' => $genid), $null);
	}
	?>

	<?php echo submit_button(lang('save changes'), null, array('onclick' => $save_js_function)) ?>
</div>

</div>

<script>


$(function() {

<?php
	if (count($custom_properties) == 0) { // add one empty row

		?>og.addCustomPropertyRow('<?php echo $genid?>');<?php

	} else {
		foreach ($custom_properties as $cp) { /* @var $cp CustomProperty */
			$cp_name = escape_character($cp->getName());

			if ($cp->getIsSpecial() && trim($cp->getCode()) != "") {
				$label_code = str_replace("_special", "", $cp->getCode());
				if (trim($label_code) != "") {
					$label_value = Localization::instance()->lang($label_code);
					if (!is_null($label_value)) $cp_name = $label_value;
				}
			}

			$prop = array(
				'id' => $cp->getId(),
				'name' => $cp_name,
				'type' => $cp->getType(),
				'description' => escape_character($cp->getDescription()),
				'values' => escape_character($cp->getValues()),
				'default_value' => escape_character($cp->getDefaultValue()),
				'is_special' => $cp->getColumnValue('is_special') ? '1' : '',
				'is_disabled' => $cp->getColumnValue('is_disabled') ? '1' : '',
				'visible_by_default' => $cp->getVisibleByDefault() ? '1' : '',
				'is_required' => $cp->getIsRequired() ? '1' : '',
				'is_multiple_values' => $cp->getIsMultipleValues() ? '1' : '',
			);
			Hook::fire('additional_custom_property_fields', $cp, $prop);
?>
		var prop = Ext.util.JSON.decode('<?php echo json_encode($prop)?>');
		og.addCustomPropertyRow('<?php echo $genid?>', prop);

	<?php }
	} ?>

	$( "#<?php echo $genid?>custom-properties-table" ).sortable({
		stop: function(event, object) {
			og.refreshTableRowsOrder(genid);
		},
		handle: ".handle",
		cursor: "move",
		cancel: "tr.header"
	});
});
</script>



