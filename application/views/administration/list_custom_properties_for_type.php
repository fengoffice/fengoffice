<?php
  if (!isset($genid)) {
  	$genid = gen_id();
  }
  if (!isset($extra_params)) {
  	$extra_params = array();
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

<?php Env::useHelper('custom_properties'); ?>

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

	$cps_data = array();
	foreach ($custom_properties as $cp) {/* @var $cp CustomProperty */
		$cp_info = $cp->getArrayInfo();
		$cp_info['original_name'] = $cp->getName();
		$cps_data[] = $cp_info;
	}
	
	Hook::fire("list_custom_properties_for_type_modify_cps", array('ot' => $object_type, 'cps' => $custom_properties), $cps_data);
	
	// Ensure that the resulting set of properties is sorted using the order attribute
	usort($cps_data, function($a, $b) {
		return $a['property_order'] - $b['property_order'];
	});
?>
	// Shared callback: initialises sortable and fires the post-load event.
	// Called immediately when there are no properties, or from the onComplete
	// callback of og.addCustomPropertyRows (which is async) when there are.
	var initAfterLoad_<?php echo $genid?> = function() {
		$( "#<?php echo $genid?>custom-properties-table" ).sortable({
			stop: function(event, object) {
				og.refreshTableRowsOrder(genid);
			},
			handle: ".handle",
			cursor: "move",
			cancel: "tr.header"
		});
		og.eventManager.fireEvent('after list custom properties', {genid: '<?php echo $genid?>'});
	};

<?php
	if (count($cps_data) == 0) { // add one empty row
?>
		og.addCustomPropertyRow('<?php echo $genid?>');
		initAfterLoad_<?php echo $genid?>();
<?php
	} else {

		$props_js = array();
		foreach ($cps_data as $cp) {

			$prop = array(
				'id' => array_var($cp, 'id'),
				'code' => array_var($cp, 'code'),
				'name' => array_var($cp, 'name'),
				'original_name' => array_var($cp, 'original_name',''),
				'type' => array_var($cp, 'type'),
				'order' => array_var($cp, 'property_order'),
				'description' => array_var($cp, 'description'),
				'values' => array_var($cp, 'values'),
				'default_value' => array_var($cp, 'default_value'),
				'boolean_allow_not_specified' => cp_boolean_allows_not_specified($cp) ? 1 : 0,
				'is_special' => array_var($cp, 'is_special') ? '1' : '',
				'is_disabled' => array_var($cp, 'is_disabled') ? '1' : '',
				'visible_by_default' => array_var($cp, 'visible_by_default') ? '1' : '',
				'show_in_lists' => array_var($cp, 'show_in_lists') ? '1' : '',
				'is_required' => array_var($cp, 'is_required') ? '1' : '',
				'is_multiple_values' => array_var($cp, 'is_multiple_values') ? '1' : '',
				'override_is_required' => array_var($cp, 'override_is_required') ? '1' : '',
				'override_is_multiple_values' => array_var($cp, 'override_is_multiple_values') ? '1' : '',
				'filter_values_by' => array_var($cp, 'filter_values_by'),
				'linked_to' => array_var($cp, 'linked_to'),
				'position' => array_var($cp, 'position'),
			);
			if ($object_type->getName() == 'contact') {
				$prop['contact_type'] = $cp['contact_type'];
			}

			Hook::fire('additional_custom_property_fields', array('cp' => $cp, 'ot' => $object_type), $prop);
			$props_js[] = $prop;
		}
		$json_flags = defined('JSON_HEX_APOS') ? JSON_HEX_APOS | JSON_HEX_TAG : JSON_HEX_TAG;
?>
		og.addCustomPropertyRows('<?php echo $genid?>', <?php echo json_encode($props_js, $json_flags) ?>, '', initAfterLoad_<?php echo $genid?>);
<?php
	} ?>
});
</script>



