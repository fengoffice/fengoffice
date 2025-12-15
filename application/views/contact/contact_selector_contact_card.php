<?php
$is_multiple = (bool)array_var($post_vars, 'is_multiple', false);
$contact_id = array_var($post_vars, 'id', 0);
$hf_id = array_var($post_vars, 'hf_id', '');
$combo_id = array_var($post_vars, 'combo_id', '');
$onchange_fn_str = array_var($post_vars, 'onchange_fn_str', '');
$show_only_name = (bool)array_var($post_vars, 'show_only_name', false);

if ($is_multiple) {
	$on_remove = "og.reCalculateValue($contact_id, '$hf_id');";
} else {
	$on_remove = "document.getElementById('". $hf_id ."').value=0;";
}
$on_remove .= "$('#contact-selector-card-". $hf_id . '-' . $contact_id ."').remove();";
$on_remove .= "og.showContactCombo('$combo_id');";
$on_remove .= $onchange_fn_str;

$picture_url = $contact->getPictureUrl('small');

// reorder properties, set harcoded order to put name, email, phone, address in the top of the view
$ordered_properties = array();
$name_property = null;
$email_property = null;
$phone_property = null;
$address_property = null;
foreach ($contact_properties as $property) {
	if ($property['id'] == 'name') {
		$name_property = $property;
	} else if ($property['id'] == 'email') {
		$email_property = $property;
	} else if ($property['id'] == 'phone') {
		$phone_property = $property;
	} else if ($property['id'] == 'address') {
		$address_property = $property;
	} else {
		$ordered_properties[] = $property;
	}
}
array_unshift($ordered_properties, $address_property);
array_unshift($ordered_properties, $phone_property);
array_unshift($ordered_properties, $email_property);
array_unshift($ordered_properties, $name_property);
// end ordering properties

// get the values and class for each property
$properties_data = array();
foreach ($ordered_properties as $property) {
	if (in_array($property['id'], $properties_to_show)) {

		$property_value = '';
		$property_class = '';

		if ($property['id'] == 'name') {
			$property_value = $contact->getObjectName();
			$property_class = 'main-property name';
		} else if ($property['id'] == 'email') {
			$property_value = $contact->getEmailAddress();
			$property_class = 'main-property email';
		} else if ($property['id'] == 'phone') {
			$property_value = $contact->getAllPhonesString();
			$property_class = 'main-property phone';
		} else if ($property['id'] == 'address') {
			$addresses = $contact->getAllAddresses();
			$property_class = 'main-property address';
			foreach ($addresses as $address) {
				if (trim($address->getStreet()) != '') {
					$property_value = $address->toString();
					break;
				}
			}
		} else {
			if (str_starts_with($property['id'], 'cp_')) {
				$cp_id = str_replace('cp_', '', $property['id']);
				$property_value = $contact->getCustomPropertyValue($cp_id);
			} else {
				$property_value = $contact->getColumnValue($property['id']);
			}
			$property_class = 'other-property';
		}
		
		if (trim($property_value) != '') {
			$properties_data[] = array(
				'id' => $property['id'],
				'name' => $property['name'],
				'value' => $property_value,
				'class' => $property_class,
			);
		}
	}
}

?>

<?php if ($show_only_name) { ?>
	<div class="contact-selector-name">
		<div class="section contact-name" title="<?php echo clean($contact->getObjectName()); ?>"><?php echo clean($contact->getObjectName()); ?></div>
		<div class="section contact-actions">
			<a href="#" class="link-ico ico-delete" onclick="<?php echo $on_remove; ?>;this.parentElement.parentElement.remove();"></a>
		</div>
		<div class="clear"></div>
	</div>
<?php } else { ?>
<div class="contact-selector-card" id="contact-selector-card-<?php echo $hf_id . '-' . $contact_id; ?>">
	<div class="section photo">
		<img src="<?php echo $picture_url; ?>" alt="<?php echo clean($contact->getObjectName()); ?>">
	</div>

	<div class="section contact-info">
		<?php foreach ($properties_data as $property) { ?>
			<div class="info-content <?php echo $property['class'] ?>">
				<?php 
					if ($property['class'] == 'other-property') {
						echo $property['name'] . ': ';
					}
					echo clean($property['value']);
				?>
			</div>
			<div class="clear"></div>
		<?php } ?>
	</div>

	<div class="section contact-actions">
		<a href="#" class="link-ico ico-delete" onclick="<?php echo $on_remove; ?>"></a>
	</div>

	<div class="clear"></div>
</div>
<?php } ?>