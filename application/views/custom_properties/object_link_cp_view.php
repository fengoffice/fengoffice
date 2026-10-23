<?php
	if (!isset($genid)) $genid = gen_id();
	
	if (is_array($cp_value) && count($cp_value) > 0) {
		if ($cp_value[0]) {
			$selected_object_ids = json_decode($cp_value[0]->getValue(), true);
		}
	} else if (is_numeric($cp_value)) {
		$selected_object_ids = array($cp_value);
	} else if (is_string($cp_value)) {
		$selected_object_ids = json_decode($cp_value, true);
	}

	$selected_objects = array();
	if (is_array($selected_object_ids)) {

		foreach ($selected_object_ids as $selected_object_id) {
			$selected_object = Objects::findObject($selected_object_id);
			if ($selected_object instanceof ContentDataObject) {
				$selected_objects[] = $selected_object;
			}
		}
	}
?>
<div class="cp-object-link-view <?php echo $add_class ?>">
	<?php foreach ($selected_objects as $selected_object) { 
		$sel_ot = ObjectTypes::instance()->findById($selected_object->getObjectTypeId());
		?>
		<div id="<?php echo $genid ?>selected-value<?php echo $cp->getId() ?>-<?php echo $selected_object->getId() ?>" class="cp-object-link-selected-value">
			<div class="name">
				<a href="<?php echo $selected_object->getObjectUrl() ?>" target="_blank" class="link-ico <?php echo $sel_ot->getIconClass() ?>"><?php echo escape_character($selected_object->getName()) ?></a>
			</div>
		</div>
	<?php } ?>
</div>
