<?php 
	$headers = array('name', 'type', 'type filters', 'default value', 'list values comma separated', 'is required', 'is multiple', 'show in main tab', 'show in lists', 'actions');
	if (!isset($id_suffix)) $id_suffix = "";
	
	Hook::fire('custom_properties_form_modify_headers', array('ot' => $object_type), $headers);
	if ($object_type->getName() == 'contact'){
		$last_column = array_pop($headers);
		$headers[] = "contact_type";
		$headers[] = $last_column;
	}
	$additional_2nd_row_colspan = 0;
?>
<div class="cp-table-container">
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
				<input type="hidden" id="cp_number" name="<?php echo "custom_properties[{number}][cp_number]"?>" value="{number}" />

				<?php
					if (isset($extra_params['additional_hidden_fields']) && count($extra_params['additional_hidden_fields']) > 0) {
						$additional_hidden_fields = $extra_params['additional_hidden_fields'];
					}
					
					if (isset($additional_hidden_fields) && count($additional_hidden_fields) > 0) {
						foreach ($additional_hidden_fields as $field_name => $field_val) {
				?>
				<input type="hidden" id="<?php echo $field_name?>" name="<?php echo "custom_properties[{number}][$field_name]"?>" value="<?php echo $field_val?>" />
				
				<?php 	}
					} ?>
				
				<input type="text" id="name" style="min-width: 200px;" name="<?php echo "custom_properties[{number}][name]"?>" value="" placeholder="<?php echo lang('name')?> "/>
			</td>
			
			<td><?php echo get_custom_property_type_selector_html(array(
				'id' => 'type',
				'name' => 'type',
				'name_prefix' => "custom_properties[{number}]",
			), $object_type) ?></td>
			
			<td>
				<?php
					// Contact type filters. Any combination of the three disjoint contact groups
					// can be selected; all of them checked, or none, means "do not filter".
					$cp_contact_type_filter_options = array(
						'companies' => lang('companies and groups'),
						'contacts'  => lang('individual contacts'),
						'users'     => lang('users'),
					);
				?>
				<div id="filter_values_contact_cp" class="filter-values contact cp-type-filter" style="display:none;">
					<button type="button" class="cp-type-filter-toggle" title="<?php echo lang('filter contact cp dropdown by')?>" onclick="og.toggleCpContactTypeFilterPanel(this); return false;">
						<span class="cp-type-filter-summary"><?php echo lang('all contact types')?></span>
					</button>
					<div class="cp-type-filter-panel" style="display:none;">
					<?php foreach ($cp_contact_type_filter_options as $cp_filter_value => $cp_filter_label) { ?>
						<label class="cp-type-filter-option">
							<input type="checkbox" class="cp-type-filter-check checkbox" value="<?php echo $cp_filter_value?>" checked="checked" onchange="og.cpContactTypeFilterChanged(this);" />
							<span class="cp-type-filter-label"><?php echo $cp_filter_label?></span>
						</label>
					<?php } ?>
					</div>
				</div>
				<select	id="filter_values_object_link_cp" class="filter-values contact" name="custom_properties[{number}][filter_values_by]" title="<?php echo lang('select object type')?>" style="display:none;">
					<option value =""><?php echo lang('no filter'); ?></option>
				<?php
					$allowed_object_types = ObjectTypes::getAvailableObjectTypes();
					foreach ($allowed_object_types as $allowed_object_type) {
						?><option value="<?php echo $allowed_object_type->getId()?>"><?php echo $allowed_object_type->getObjectTypeName()?></option><?php
					}
				?>
				</select>

				<?php 
					if (Plugins::instance()->isActivePlugin('member_fields_in_objects')) { 
						Env::useHelper('functions', 'member_fields_in_objects');
				?>
					<div id="member_fields_in_objects" class="member-fields-in-objects">
						<?php
							echo get_external_property_selector_html($object_type);
						?>
						<select	id="external_property_editable" class="external-property-editable-selector" name="custom_properties[{number}][is_editable]" title="<?php echo lang('allow property editing or only display')?>" style="display:none;">
							<option value ="1"><?php echo lang('allow edition')?></option>
							<option value ="0"><?php echo lang('display only')?></option>	
						</select>
					</div>
				<?php } ?>

			</td>

			<?php
				if (Plugins::instance()->isActivePlugin('advanced_core')) {
					$additional_2nd_row_colspan += 1;
					?>
					<td>
						<select	id="linked_to" name="custom_properties[{number}][linked_to]" title="<?php echo lang('contact cp linked to info')?>" style="display:none;">
						</select>
						<input type="hidden" id="linked_to_pivot" name="custom_properties[{number}][linked_to_pivot]" value="" />
					</td>
					<?php
				}
			?>
			
			<td>
				<input type="text" id="default_value" name="<?php echo "custom_properties[{number}][default_value]"?>" value="" style="max-width: 78px;" />
				<div id="boolean_default_config" style="display:none;">
					<select id="default_value_bool" name="<?php echo "custom_properties[{number}][default_value_bool]"?>" style="max-width:110px;">
						<option value="1"><?php echo lang('yes'); ?></option>
						<option value="-1"><?php echo lang('no'); ?></option>
						<option value="0" class="cp-boolean-default-not-specified-option"><?php echo lang('cp boolean not specified'); ?></option>
					</select>
					<label class="cp-boolean-allow-ns-label">
						<input type="checkbox" id="boolean_allow_not_specified" name="<?php echo "custom_properties[{number}][boolean_allow_not_specified]"?>" value="1" checked="checked" class="checkbox" />
						<?php echo lang('cp boolean allow not specified'); ?>
					</label>
				</div>
			</td>
			
			<td>
				<input type="text" id="values" name="<?php echo "custom_properties[{number}][values]"?>" value="" style="display:none;" />
				<span id="values_hint" class="desc"><?php echo lang('cp list values hint')?></span>
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
					$options_html[] = option_tag(ucwords(lang($opt)), $opt);
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
			<td colspan="<?php echo (5 + $additional_2nd_row_colspan)?>">
				<input id="description" type="text" placeholder="<?php echo lang('description')?>" name="<?php echo "custom_properties[{number}][description]"?>" value="" />
				<div id="numeric_options" class="numeric-options-section" style="display:none;"><?php 
					$num_opt_html = "";
					Hook::fire("custom_prop_numeric_options", null, $num_opt_html);
					echo $num_opt_html;
				?></div>
			</td>
			<?php $columns_count = $object_type->getName() == 'contact' ? 6 : 5; ?>
			<td colspan="<?php echo ($columns_count + count($additional_columns)) ?>">
				<div class="desc" id="original_name" style="display:none;"></div>
				<span class="desc" id="disabled_message" style="display:none;"><?php echo lang('custom property is disabled')?></span>
				<span class="desc" id="deleted_message" style="display:none;"><?php echo lang('custom property deleted')?></span>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div class="add-new-cp-link-container">
	<?php if (array_var($extra_params, 'add_link_text')) {
		$link_text = array_var($extra_params, 'add_link_text');
	} else {
		$link_text = lang('add new custom property', $type_name);
	} ?>
	<span class="cp-add-link-container">
		<a href="#" class="link-ico ico-add" onclick="og.addCustomPropertyRow('<?php echo $genid?>', null, '<?php echo $id_suffix?>');return false;"><?php echo $link_text?></a>
	</span>
	<?php
		$more_links = [];
		Hook::fire('custom_properties_form_add_link', array('object_type' => $object_type, 'genid' => $genid, 'id_suffix' => $id_suffix), $more_links);
		foreach ($more_links as $link) {
			?>
			<span class="cp-add-link-container">
				<a href="#" class="link-ico <?php echo $link['icon']?>" onclick="<?php echo $link['onclick']?>"><?php echo $link['text']?></a>
			</span>
			<?php
		}
	?>
	<!-- Preview form link, hidden for now, only for develop purposes -->
	<span class="cp-add-link-container right" style="float:right; display:none;">
		<a href="#" class="link-ico ico-form" onclick="og.render_modal_form('', {c:'object', a:'test_add_form', params: {ot_id:<?php echo $object_type->getId() ?>}});">Preview form</a>
	</span>
	<div class="clear"></div>
</div>
