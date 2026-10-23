<?php
$dummy = new Contact();

$member_id = array_var($post_vars, 'member_id');
$combo_id = array_var($post_vars, 'combo_id');
$render_to = array_var($post_vars, 'render_to');
$multiple = array_var($post_vars, 'multiple');
$genid = array_var($post_vars, 'genid', gen_id());
$on_submit = "og.addQuickContactFromModal('$member_id', '$combo_id', '$render_to', '$genid', '$multiple'); return false;";

$properties_to_show = config_option('contact_quickadd_inputs');
$contact_properties = ObjectTypes::findByName('contact')->getObjectTypeProperties(false, true, true);

// reorder properties, set harcoded order to put first name, surname, email, phone, address in the top of the form
$ordered_properties = array();
$first_name_property = null;
$surname_property = null;
$email_property = null;
$phone_property = null;
$address_property = null;
foreach ($contact_properties as $property) {
	if ($property['id'] == 'first_name') {
		$first_name_property = $property;
	} else if ($property['id'] == 'surname') {
		$surname = $property;
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
array_unshift($ordered_properties, $surname);
array_unshift($ordered_properties, $first_name_property);
// end ordering inputs

?>

<div style='display:none;'><?php echo select_country_widget('template_country', '', array('id' => 'template_select_country')) ?></div>

<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo lang('add new contact') ?></div>
	</div>
	<div class="clear"></div>
</div>

<div class="coInputMainBlock contact-quick-add">

	<form class="internalForm" method="post" enctype="multipart/form-data"
		action="<?php echo get_url('contact', 'add') ?>" 
		id="<?php echo $genid ?>submit-edit-form"
		onsubmit="<?php echo $on_submit ?>">

	<?php
		foreach ($ordered_properties as $property) {
			if (in_array($property['id'], $properties_to_show)) {
				// foreach property to show -> render the input
				?>
			<div class="dataBlock">

			<?php if (!str_starts_with($property['id'], 'cp_')) { ?>
				<label for="<?php echo $genid . $property['id'] ?>"><?php echo $property['name'] ?></label>
			<?php } ?>

			<?php
				if (in_array($property['id'], array('email', 'phone', 'address', 'website'))) {
					// render special inputs (email, phone, address, website)

					if ($property['id'] == 'email') {
						//email input
						echo text_field('contact[email]', '', array('id' => $genid . $property['id'], 'class' => 'cp-text'));

					} else if ($property['id'] == 'phone') {
						//phone input
						echo text_field('contact[phone][0][number]', '', array('id' => $genid . $property['id'], 'class' => 'cp-text'));
						?>
						<input type="hidden" name="contact[phone][0][type]'" value="<?php echo config_option('default_type_phone') ?>"/>
						<input type="hidden" name="contact[phone][0][name]'" value=""/>
						<?php

					} else if ($property['id'] == 'address') {
						//address input
						?>
						<div class="address-custom-properties-parent" style="height: 153px;">
							<div id="<?php echo $genid ?>address-container" class="address-input-container"></div>
						</div>
						<?php
					} else if ($property['id'] == 'website') {
						//website input
						echo text_field('contact[website]', '', array('id' => $genid . $property['id'], 'class' => 'cp-text'));
					}
					
				} else if (str_starts_with($property['id'], 'cp_')) {
					// render custom property inputs

					$cp_id = str_replace('cp_', '', $property['id']);
					$cp = CustomProperties::instance()->findById($cp_id);

					echo get_custom_property_input_html($cp, $dummy, $genid, 'object_custom_properties', null);

				} else {
					// render default inputs (columns in contact object type)
					echo text_field('contact[' . $property['id'] . ']', '', array('id' => $genid . $property['id'], 'class' => 'cp-'.$property['type']));
				} 
			?>
			</div>
	<?php
			}
		}
	?>

		<div class="button-container">
			<?php echo submit_button(lang('add contact')); ?>
		</div>
		<div class="clear"></div>

	</form>
</div>

<script>
	let genid = '<?php echo $genid ?>';
	
	
	$(function() {
		// render the address input
		og.renderAddressInput(genid + 'address', 'contact[address][0]', genid + 'address-container', 2, {});

		// set all the inputs as tabbable
		$('.contact-quick-add input:visible, .contact-quick-add textarea:visible, .contact-quick-add select:visible, .contact-quick-add button.submit:visible').each(function(index) {
			$(this).addClass('tabbable').addClass('tab-' + index).data('tab', index);
		});

		// get the amount of tabbables
		var tabbable_count = $('.contact-quick-add .tabbable').length;

		// handle tabbing
		$('.contact-quick-add .tabbable').each(function(index) {
			$(this).on('keydown', function(e) {
				if (e.which == 9) {
					e.preventDefault();
					e.stopPropagation();
	
					// get current index and calculate next index
					let current_index = $(this).data('tab');
					let next_index = $(this).data('tab') + 1;
					if (e.shiftKey) {
						next_index = $(this).data('tab') - 1;
					}
					
					// if we're on the last tabbable, and not on shift, go back to first
					if (index == tabbable_count - 1 && !e.shiftKey) {
						next_index = 0;
					}
					// if we're on the first tabbable, and on shift, go to last
					if (index == 0 && e.shiftKey) {
						next_index = tabbable_count - 1;
					}
	
					// focus on the next tabbable
					$('.contact-quick-add .tabbable.tab-' + next_index).focus();
				}
			})
		});

		// initialize focus in first tabbable input
		$('.contact-quick-add .tabbable.tab-0').focus();
		
	});
</script>