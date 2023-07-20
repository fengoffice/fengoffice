<?php 
	$headers = array('name', 'type', 'default value', 'list values comma separated', 'is required', 'is multiple', 'show in main tab', 'show in lists', 'actions');
	if (!isset($id_suffix)) $id_suffix = "";
	
	Hook::fire('custom_properties_form_modify_headers', array('ot' => $object_type), $headers);
	if ($object_type->getName() == 'contact'){
		$last_column = array_pop($headers);
		$headers[] = "contact_type";
		$headers[] = $last_column;
	}
?>
<table class="custom-property-list" id="<?php echo $genid?>custom-properties-table">
	
	<tr class="header texture-n-1">
		<th></th>
	<?php foreach ($headers as $header) { ?>
		<th><?php echo lang($header) ?></th>
	<?php }?>
	</tr>
	
	<tbody id="<?php echo $genid?>-cp-container-template" class="cp-container" style="display:none;">
		<tr class="top-row">
			<td rowspan="2" class="order handle" id="order"></td>
			<td>
				<input type="hidden" id="id" name="<?php echo "custom_properties[{number}][id]"?>" value="0" />
				<input type="hidden" id="is_special" name="<?php echo "custom_properties[{number}][is_special]"?>" value="0" />
				<input type="hidden" id="is_disabled" name="<?php echo "custom_properties[{number}][is_disabled]"?>" value="0" />
				<input type="hidden" id="deleted" name="<?php echo "custom_properties[{number}][is_disabled]"?>" value="0" />
				
				<?php
					if (is_array($extra_params['additional_hidden_fields']) && count($extra_params['additional_hidden_fields']) > 0) {
						$additional_hidden_fields = $extra_params['additional_hidden_fields'];
					}
					
					if (isset($additional_hidden_fields) && count($additional_hidden_fields) > 0) {
						foreach ($additional_hidden_fields as $field_name => $field_val) {
				?>
				<input type="hidden" id="<?php echo $field_name?>" name="<?php echo "custom_properties[{number}][$field_name]"?>" value="<?php echo $field_val?>" />
				
				<?php 	}
					} ?>
				
				<input type="text" id="name" name="<?php echo "custom_properties[{number}][name]"?>" value="" placeholder="<?php echo lang('name')?>"/>
			</td>
			
			<td><?php echo get_custom_property_type_selector_html(array('id' => 'type', 'name' => 'type', 'name_prefix' => "custom_properties[{number}]")) ?>
			
			<td style="max-width:80px;">
				<input type="text" id="default_value" name="<?php echo "custom_properties[{number}][default_value]"?>" value="" style="max-width: 78px;" />
				<select	id="default_value_bool" name="<?php echo "custom_properties[{number}][default_value_bool]"?>" style="display:none;">
					<option value ="0"></option>
					<option value ="1"><?php echo lang('yes'); ?></option>
					<option value ="-1"><?php echo lang('no'); ?></option>
				</select>
			</td>
			
			<td>
				<input type="text" id="values" name="<?php echo "custom_properties[{number}][values]"?>" value="" style="display:none;" />
				<span id="values_hint" class="desc" style="margin-left:10px;"><?php echo lang('cp list values hint')?></span>
			</td>
			
			<td class="center" style="max-width:80px;"><?php echo checkbox_field("custom_properties[{number}][is_required]", false, array('id' => 'is_required'));?></td>
			
			<td class="center" style="max-width:80px;"><?php echo checkbox_field("custom_properties[{number}][is_multiple]", false, array('id' => 'is_multiple_values'));?></td>
			
			<td class="center" style="max-width:80px;"><?php echo checkbox_field("custom_properties[{number}][visible_by_default]", false, array('id' => 'visible_by_default'));?></td>
			
			<td class="center" style="max-width:80px;"><?php echo checkbox_field("custom_properties[{number}][show_in_lists]", false, array('id' => 'show_in_lists'));?></td>
			
			<?php 
				$additional_columns = array();
				Hook::fire('custom_properties_form_additional_columns', array('ot' => $object_type), $additional_columns);
				foreach ($additional_columns as $column_data) {
					?><td class="center"><?php echo $column_data['html']; ?></td><?php
				}
			?>

			<?php if ($object_type->getName() == 'contact'){
				$options = array('all', 'contact', 'user');
				$options_html = array();
				foreach($options as $opt){
					$options_html[] = option_tag(ucwords(lang($opt)), $opt, $attr);
				}
				?>
			<td class="center" style="max-width:80px;"><?php echo select_box("custom_properties[{number}][contact_type]", $options_html, array("id" => "contact_type"));?></td>
			<?php } ?>
			
			<td class="actions">
				<a class="link-ico ico-delete" id="delete_action" href="#" onclick="og.deleteCustomProperty(this)" title="<?php echo lang('delete')?>"></a>
				<a class="link-ico ico-delete" id="disable_action" href="#" onclick="og.disableSpecialCustomProperty(this)" title="<?php echo lang('disable')?>" style="display:none;"></a>
				
				<a id="undo_delete_action" href="#" onclick="og.undoDeleteCustomProperty(this)" title="<?php echo lang('enable')?>" style="display:none;"><?php echo lang('enable')?></a>
				<a id="enable_action" href="#" onclick="og.undoDisableSpecialCustomProperty(this)" title="<?php echo lang('enable')?>" style="display:none;"><?php echo lang('enable')?></a>
			</td>
		</tr>
		<tr class="bottom-row">
			<td colspan="4">
				<input id="description" style="width:97%;" type="text" placeholder="<?php echo lang('description')?>" name="<?php echo "custom_properties[{number}][description]"?>" value="" />
				<div id="numeric_options" style="display:none;"><?php 
					$num_opt_html = "";
					Hook::fire("custom_prop_numeric_options", null, $num_opt_html);
					echo $num_opt_html;
				?></div>
			</td>
			<?php $columns_count = $object_type->getName() == 'contact' ? 6 : 5; ?>
			<td colspan="<?php echo ($columns_count + count($additional_columns)) ?>">
				<span class="desc" id="disabled_message" style="display:none;"><?php echo lang('custom property is disabled')?></span>
				<span class="desc" id="deleted_message" style="display:none;"><?php echo lang('custom property deleted')?></span>
			</td>
		</tr>
	</tbody>
</table>

<div class="add-new-cp-link-container">
	<?php if (array_var($extra_params, 'add_link_text')) {
		$link_text = array_var($extra_params, 'add_link_text');
	} else {
		$link_text = lang('add new custom property', $type_name);
	} ?>
	<a href="#" class="link-ico ico-add" onclick="og.addCustomPropertyRow('<?php echo $genid?>', null, '<?php echo $id_suffix?>');return false;"><?php echo $link_text?></a>
</div>