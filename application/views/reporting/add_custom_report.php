<?php
require_javascript("og/DateField.js");
require_javascript("og/ReportingFunctions.js");
require_javascript('og/modules/doubleListSelCtrl.js');
require_javascript('og/CSVCombo.js');
$genid = gen_id();
if (!isset($conditions)) $conditions = array();
?>
<form style='height: 100%; background-color: white' class="internalForm report"
	action="<?php echo $url  ?>" method="post"
	onsubmit="return og.validateReport('<?php echo $genid ?>');"><input
	type="hidden" name="report[report_object_type_id]" id="report[report_object_type_id]"
	value="<?php echo array_var($report_data, 'report_object_type_id', '') ?>" />

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo (isset($id) ? lang('edit custom report') : lang('new custom report')) ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('report[name]', array_var($report_data, 'name'), array('id' => $genid . 'reportFormName', 'class' => 'title', 'placeholder' => lang('type name here'))); ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>


<div class="coInputMainBlock">
	<div class="dataBlock">
	<?php
	echo label_tag(lang('description'), $genid . 'reportFormDescription', false);
	echo text_field('report[description]', array_var($report_data, 'description'), array('id' => $genid . 'reportFormDescription', 'tabindex' => '2', 'class' => 'title'));
	?>
	</div>
	<div class="clear"></div>
	
	<div class="dataBlock">
<?php 

echo label_tag(lang('object type'), $genid . 'reportFormObjectType', true); 

$selected_option=null;
$options = array();
foreach ($object_types as $type) {
	if ($selected_type == $type[0]) {
		$selected = 'selected="selected"';
		$selected_option = $type[1];
	} else {
		$selected = '';
	}
	$options[] = '<option value="'.$type[0].'" '.$selected.'>'.$type[1].'</option>';
}

$context_menu_style = "font-weight:normal";
$context_div_display ="display:none;";
$strDisabled = count($options) > 1 ? '' : 'disabled';
echo select_box('objectTypeSel', $options, array('id' => 'objectTypeSel' ,'onchange' => 'og.reportObjectTypeChanged("'.$genid.'", "", 1, "")', 'style' => 'width:200px;', $strDisabled => '', 'tabindex' => '10'));
?>
	</div>
	<div class="clear"></div>
	
	<div class="dataBlock">
	  <span style="margin-left:30px;">
		<?php echo checkbox_field("report[ignore_context]", array_var($report_data, 'ignore_context', true), array('id' => $genid.'ignore_context',
				'onchange' => 'document.getElementById("'.$genid.'add_report_select_context_div").style.display = (this.checked ? "none" : "")')); ?>
		<label class="checkbox" for="<?php echo $genid.'ignore_context'?>"><?php echo lang('show always')?></label>
	  </span>

	  <div class="clear"></div>
	</div>
	<div class="dataBlock" id="<?php echo $genid ?>add_report_select_context_div" style="margin:0;<?php echo $context_div_display ?>">
	  <div> 
	<?php
		$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
		if ($object->isNew()) {
			render_member_selectors($object->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
		} else {
			render_member_selectors($object->manager()->getObjectTypeId(), $genid, $object->getMemberIds(), array('listeners' => $listeners), null, null, false);
		}
	?>
	  </div>
	  <div class="clear"></div>
	</div>
</div>

<div id="<?php echo $genid ?>MainDiv" class="coInputMainBlock">
	<fieldset><legend><?php echo lang('conditions') ?></legend>
		<div id="<?php echo $genid ?>" class="report-conditions"></div>
		<div style="margin-top:10px;">
			<a href="#" class="link-ico ico-add" onclick="og.addCondition('<?php echo $genid ?>', 0, 0, '', '', '', false)"><?php echo lang('add condition')?></a>
		</div>
	</fieldset>

	<fieldset><legend><?php echo lang('columns and order') ?></legend>
		<div id="columnListContainer"></div>
	</fieldset>
	
	<?php echo submit_button((isset($id) ? lang('save changes') : lang('add report')), 's', array('tabindex' => '20000'))?>

</div>

</form>

<script>
	og.loadReportingFlags();
	og.reportObjectTypeChanged('<?php echo $genid?>', '<?php echo array_var($report_data, 'order_by') ?>', '<?php echo array_var($report_data, 'order_by_asc') ?>', '<?php echo (isset($columns) ? implode(',', $columns) : '') ?>');
	<?php if(isset($conditions)){ ?>
		<?php foreach($conditions as $condition){ ?>
		    og.addCondition('<?php echo $genid?>',<?php echo $condition->getId() ?>, <?php echo $condition->getCustomPropertyId() ?> , '<?php echo $condition->getFieldName() ?>', '<?php echo $condition->getCondition() ?>', '<?php echo $condition->getValue() ?>', '<?php echo $condition->getIsParametrizable() ?>');		
		<?php 
		}//foreach ?>
	<?php }//if ?>
	
	var first = document.getElementById('<?php echo $genid ?>reportFormName');
	if (first) first.focus();
</script>
