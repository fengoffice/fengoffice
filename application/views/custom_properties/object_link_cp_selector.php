<?php
	if (!isset($genid)) $genid = gen_id();
	
	$hf_value = "[]";
	$selected_objects = array();
	$selected_object_ids = array();

	if (is_array($cp_value) && count($cp_value) > 0) {
		if ($cp_value[0] && str_starts_with($cp_value[0]->getValue(), '[')) {
			$selected_object_ids = json_decode($cp_value[0]->getValue(), true);
		} else if ($cp_value[0] instanceof CustomPropertyValue || $cp_value[0] instanceof MemberCustomPropertyValue) {
			foreach ($cp_value as $cpv) {
				if ($cpv) {
					$selected_object_ids[] = (int)trim($cpv->getValue());
				}
			}
		}
	} else if (is_string($cp_value)) {
		$selected_object_ids = json_decode($cp_value, true);
	}

	if (is_array($selected_object_ids)) {

		foreach ($selected_object_ids as $selected_object_id) {
			$selected_object = Objects::findObject($selected_object_id);
			if ($selected_object instanceof ContentDataObject) {
				$selected_objects[] = $selected_object;
			}
		}

		$hf_value = json_encode($selected_object_ids);
	}


	$filter_ot_id = $cp->getFilterValuesBy();
	$filter_ot_name = '';
	$filter_ot_label = lang('object');
	if ($filter_ot_id > 0) {
		$filter_ot = ObjectTypes::instance()->findById($filter_ot_id);
		if ($filter_ot instanceof ObjectType) {
			$filter_ot_name = $filter_ot->getName();
			$filter_ot_label = $filter_ot->getObjectTypeName();
		}
	}

	$is_multiple = $cp->getIsMultipleValues();

	$hf_input_id = $genid.'cp'.$cp->getId();
?>

<div id="<?php echo $genid ?>cp-object-link-selector-container<?php echo $cp->getId() ?>" class="cp-object-link-selector-container">
	
	<div id="<?php echo $genid ?>cp-object-link-selector<?php echo $cp->getId() ?>" class="cp-object-link-selector">
		<div id="<?php echo $genid ?>selected-values<?php echo $cp->getId() ?>" class="cp-object-link-selected-values">
		</div>

		<div id="<?php echo $genid ?>cp-object-link-action<?php echo $cp->getId() ?>" class="cp-object-link-action">
			<?php if (!$disabled) { ?>
			<a href="#" class="link-ico ico-add" onclick="og.select_object_link_custom_property_value('<?php echo $genid ?>', '<?php echo $cp->getId() ?>', <?php echo $is_multiple ? '1' : '0' ?>, '<?php echo $filter_ot_name ?>', '<?php echo $cp->getLinkedTo() ?>'); return false;"><?php
				echo lang('select') . ' ' . strtolower($filter_ot_label);
			?></a>
			<?php } ?>
		</div>
	</div>
	
	<input type="hidden" name="<?php echo $input_name ?>" 
		id="<?php echo $hf_input_id?>" value="<?php echo $hf_value ?>" />
	
</div>
<div class="clear"></div>

<script>
$(document).ready(function() {
	
	<?php
	foreach ($selected_objects as $selected_object) {
		$sel_ot = ObjectTypes::instance()->findById($selected_object->getObjectTypeId());
		?>
		var sel_object = {
			object_id: <?php echo $selected_object->getId() ?>,
			name: '<?php echo escape_character($selected_object->getName()) ?>',
			icon: '<?php echo $sel_ot->getIconClass() ?>'
		};
		og.add_object_link_custom_property_value('<?php echo $genid?>', '<?php echo $cp->getId() ?>', <?php echo $is_multiple ? '1' : '0' ?>, sel_object);
		<?php
	}
	?>
	
});
</script>