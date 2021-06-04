<?php
  if (!isset($genid)) {
  	$genid = gen_id();
  }

  if (!isset($save_js_function)) {
  	$save_js_function = "og.saveObjectTypeCustomProperties('$genid');";
  }
  
  $type_name = $object_type->getPluralObjectTypeName();

  $null = null;
  Hook::fire("custom_properties_form_page_actions", array("ot" => $object_type), $null);
  
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
		tpl_assign('object_type', $object_type);
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
		$cps_data = array();
		foreach ($custom_properties as $cp) {/* @var $cp CustomProperty */
			$cps_data[] = $cp->getArrayInfo();
		}
		
		Hook::fire("list_custom_properties_for_type_modify_cps", array('ot' => $object_type), $cps_data);
		
		foreach ($cps_data as $cp) {
			$cp_name = escape_character(array_var($cp, 'name'));

			if (array_var($cp, 'is_special') && trim(array_var($cp, 'code', '')) != "") {
				$label_code = str_replace("_special", "", $cp['code']);
				if (trim($label_code) != "") {
					$label_value = Localization::instance()->lang($label_code);
					if (!is_null($label_value)) $cp_name = $label_value;
				}
			}

			$prop = array(
				'id' => array_var($cp, 'id'),
				'name' => $cp_name,
				'type' => array_var($cp, 'type'),
				'order' => array_var($cp, 'property_order'),
				'description' => str_replace('"', '\\"', escape_character(array_var($cp, 'description'))),
				'values' => escape_character(array_var($cp, 'values')),
				'default_value' => escape_character(array_var($cp, 'default_value')),
				'is_special' => array_var($cp, 'is_special') ? '1' : '',
				'is_disabled' => array_var($cp, 'is_disabled') ? '1' : '',
				'visible_by_default' => array_var($cp, 'visible_by_default') ? '1' : '',
				'show_in_lists' => array_var($cp, 'show_in_lists') ? '1' : '',
				'is_required' => array_var($cp, 'is_required') ? '1' : '',
				'is_multiple_values' => array_var($cp, 'is_multiple_values') ? '1' : '',
			);
		    if ($object_type->getName() == 'contact') {
				$prop['contact_type'] = $cp['contact_type'];
			}
			
			Hook::fire('additional_custom_property_fields', array('cp' => $cp, 'ot' => $object_type), $prop);
?>
		var prop = Ext.util.JSON.decode('<?php echo (defined('JSON_HEX_APOS') ? json_encode($prop, JSON_HEX_APOS) : json_encode($prop)) ?>');
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



