<?php
	$submit_url = get_url('contact', 'import_from_csv_file');
	$genid = gen_id();
?>

<script>
og.submitCsv = function(genid) {
	fname = document.getElementById(genid + 'filenamefield');
	ok = true;
	
	if (fname.value.lastIndexOf('.csv') == -1 || fname.value.lastIndexOf('.csv') != fname.value.length - 4 ) {
		ok = confirm(lang('not csv file continue'));
	}
	if (ok) {
		if (fname.value != '') {
			form = document.getElementById(genid + 'csvimport');
			form.action = form.action += "&context="+og.contextManager.plainContext();
			og.submit(form, {
				callback: function(result) {
					if (result.errorCode == 0) {
						og.getUrl('contact', 'import_from_csv_file', {calling_back: 1});
					}
				}
			});
		}
	}
}
</script>

<form style="height:100%;background-color:white" id="<?php echo $genid ?>csvimport" name="<?php echo $genid ?>csvimport" class="internalForm" action="<?php echo $submit_url ?>" method="post" enctype="multipart/form-data">

<div class="file">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
	<table style="width:535px"><tr><td><?php echo isset($import_result) ? lang('import result') : ($import_type == 'contact' ? lang('import contacts from csv') : lang('import companies from csv'));?></td>
<?php if (isset($titles)) { ?>
	<td style="text-align:right"><?php echo submit_button(lang('import'), 's', array('style'=>'margin-top:0px;margin-left:10px','id' => $genid.'csv_import_submit1', 'tabindex' => 40)) ?></td>
<?php } ?>
	</tr></table>
</div>
</div>

<?php if (!isset($titles) && !isset($import_result)) { ?>
	<div id="<?php echo $genid ?>selectFileControlDiv">
        <?php echo label_tag(lang('file'), $genid . 'filenamefield', true) ?>
        <?php echo file_field('csv_file', null, array('id' => $genid . 'filenamefield', 'class' => 'title', 'tabindex' => 10, 'size' => '88', "onchange" => 'javascript:og.submitCsv(\'' . $genid .'\')')) ?>
    </div>
    <div id="<?php echo $genid ?>first_record_has_names_div">
    	<table><tr><td><?php echo label_tag(lang('first record contains field names'), $genid . 'first_record_has_names') ?></td>
    	<td style="padding-left:10px;padding-top:5px;">
    	<?php echo yes_no_widget('first_record_has_names', $genid.'first_record_has_names', true, lang('yes'), lang('no'), 20) ?></td></tr></table>
    </div>
    <div id="<?php echo $genid ?>delimiter_div">
    	<table><tr><td><?php echo label_tag(lang('field delimiter'), $genid . 'delimiter') ?></td>
    	<td style="padding-left:10px;">
    	<?php echo text_field('delimiter', '', array('id' => $genid.'delimiter', 'style' => 'width:10px;', 'tabindex' => 30)) ?></td></tr></table>
    </div>
   	<?php } //if ?>
<?php if (isset($titles)) { ?>
	<div>
	<p><b><?php echo lang('you must match the database fields with file fields before executing the import process') ?></b></p>
	</div>
<?php } //if ?>

</div>
<?php if (isset($titles)) { ?>
	
	<div class="coInputMainBlock adminMainBlock">
	
	<table><tr><th></th><th><?php echo ($import_type == 'contact' ? lang('contact fields') : lang('company fields')); ?></th><th><?php echo lang('fields from file'); ?></th></tr>
	
	<?php
		if ($import_type == 'contact') 
			$contact_fields = Contacts::getContactFieldNames();
		else $contact_fields = Contacts::getCompanyFieldNames();
		
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType(Contacts::instance()->getObjectTypeId());+
		
		$isAlt = false;
		$i = 0; $label_w = $label_h = $label_o = false;
		foreach ($contact_fields as $c_field => $c_label) {
			if (str_starts_with($c_field, 'contact[w') && !$label_w) {
				?><tr><td colspan="3" style="text-align:center;"><b><?php echo lang('work')?></b></td></tr> <?php
				$label_w = true;
			} else if (str_starts_with($c_field, 'contact[h') && !$label_h) {
				?><tr><td colspan="3" style="text-align:center;"><b><?php echo lang('home')?></b></td></tr> <?php
				$label_h = true;
			} else if (str_starts_with($c_field, 'contact[o') && !$label_o) {
				?><tr><td colspan="3" style="text-align:center;"><b><?php echo lang('other')?></b></td></tr> <?php
				$label_o = true;
			}
			
			$isAlt = !$isAlt;
			$options = array(option_tag('', -1));
			foreach ($titles as $k => $t) $options[] = option_tag($t, $k, $k == $i ? array('selected' => 'selected') : null);
			$i++;
	?>	
				<tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
				<td><?php echo checkbox_field('check_'.$c_field, true,array( 'tabindex' => 50+$i)) ?></td><td><?php echo $c_label ?></td><td><?php echo select_box('select_'.$c_field, $options); ?></td></tr>	
	<?php	
		} //foreach	?>
		
	<?php if (is_array($custom_properties) && count($custom_properties)>0) { ?>
		<tr><td colspan="3" style="text-align:center;"><b><?php echo lang('custom properties')?></b></td></tr>
	<?php
			foreach ($custom_properties as $cp) {/* @var $cp CustomProperty */
				$isAlt = !$isAlt;
				$options = array(option_tag('', -1));
				
				foreach ($titles as $k => $t) $options[] = option_tag($t, $k, $t == $cp->getName() ? array('selected' => 'selected') : null);
				$i++;
				
				?><tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
					<td><?php echo checkbox_field('check_custom_properties['.$cp->getId().']', true,array( 'tabindex' => 50+$i)) ?></td><td><?php echo $cp->getName() ?></td>
					<td><?php echo select_box('select_custom_properties['.$cp->getId().']', $options); ?></td>
				</tr>
	<?php
			} 
	?>
	<?php } //if?>
	</table>
	
	<div><table style="width:535px">
		<tr><td><?php echo submit_button(isset($titles) ? lang('import') : lang('read file'), 's', array('style'=>'margin-top:0px;margin-left:10px','id' => $genid.'csv_import_submit1', 'tabindex' => 100)) ?></td></tr></table>
	</div>
	
	</div>
<?php } //if?>
	<div class="coInputMainBlock adminMainBlock">
<?php
	if (!isset($titles) && !isset($import_result)) { ?>
		<p><b><?php echo lang('select a file in order to load its data') ?></b></p>
<?php	}
	if (isset($import_result)) {
		if (count($import_result['import_ok'])) {
			$isAlt = false;
?>
	<br><table><tr><th colspan="2" style="text-align:center"><?php echo ($import_type == 'contact' ? lang('contacts succesfully imported') : lang('companies succesfully imported')) ?></th>
				   <th style="text-align:center"><?php echo lang('status') ?></th></tr>
<?php 		foreach ($import_result['import_ok'] as $reg) { ?>
				<tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
				<td style="padding-left:10px;"><?php echo $import_type == 'contact' ? array_var($reg, 'firstname') . ' ' . array_var($reg, 'lastname') : array_var($reg, 'name')?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
				<td style="padding-left:10px;"><span class="desc"><?php echo array_var($reg, 'import_status') ?></span></td></tr>
<?php 			$isAlt = !$isAlt;
			} ?>
	</table>
<?php 	} //if
		if (count($import_result['import_fail'])) {
			$isAlt = false;
?>
	<br><table><tr><th colspan="2" style="text-align:center"><?php echo ($import_type == 'contact' ? lang('contacts import fail') : lang('companies import fail')) ?></th>
				   <th style="text-align:center"><?php echo lang('import fail reason') ?></th></tr>
<?php 		foreach ($import_result['import_fail'] as $reg) { ?>
				<tr<?php echo ($isAlt ? ' class="altRow"': '') ?>>
				<td style="padding-left:10px;"><?php echo $import_type == 'contact' ? array_var($reg, 'firstname') . ' ' . array_var($reg, 'lastname') : array_var($reg, 'name')?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'email') ?></td>
				<td style="padding-left:10px;"><?php echo array_var($reg, 'fail_message') ?></td></tr>
<?php 			$isAlt = !$isAlt;
			} ?>
	</table>
<?php 	}
	} //if?>
	</div>
</div>
</form>

<script>
	btn = Ext.get('<?php echo $genid ?>filenamefield');
	if (btn != null) btn.focus();
</script>