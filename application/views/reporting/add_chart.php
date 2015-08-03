<?php
	$project = active_or_personal_project();
	$genid = gen_id();
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'add_chart')  ?>" method="post">

<script>
function setSave(){
	document.getElementById('hfSaveChart').value = 1;	
}
</script>
<input id="hfSaveChart" name="chart[save]" type="hidden" value="0"/>

<div class="chart">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo isset($chart) && !$chart->isNew() ? lang('edit chart') : lang('add chart') ?>
	</td><td style="text-align:right"><?php echo submit_button(isset($chart) && !$chart->isNew() ? lang('edit chart') : lang('add chart'),'s',
		array('style'=>'margin-top:0px;margin-left:10px', 'onclick' => 'javascript:setSave();return true;')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
	
	<table style="width:535px"><tr><td>
	<?php if (is_array($chart_list)){
		echo label_tag(lang('chart'), 'chartFormTypeId', false);
		echo select_chart_type('chart[type_id]', $chart_list, array_var($chart_data, 'type_id'), array('id' => 'chartFormTypeId'));
	}?>
	</td><td>
	<?php if (is_array($chart_displays)){
		echo label_tag(lang('chart display'), 'chartFormDisplayId', false);
		echo select_chart_type('chart[display_id]', $chart_displays, array_var($chart_data, 'display_id'), array('id' => 'chartFormDisplayId'));
	} ?></td>
	<td style="text-align:right"><?php echo submit_button(lang('draw chart')) ?></td></tr></table>
	
	<?php echo label_tag(lang('title'), 'chartFormTitle', true) ?>
	<?php echo text_field('chart[title]', array_var($chart_data, 'title'), 
		array('id' => 'chartFormTitle', 'class' => 'title', 'tabindex' => '1')) ?>
	</div>
	
	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('add_chart_select_context_div',this)"><?php echo lang('context') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_chart_add_tags_div', this)"><?php echo lang('tags') ?></a> - 
		<a href="#" class="option" onclick="og.toggleAndBolden('add_chart_display_div', this)"><?php echo lang('display') ?></a>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<div id="add_chart_select_context_div" style="display:none">
	<fieldset><legend><?php echo lang('workspace')?></legend>
		<?php echo select_project2('chart[project_id]', ($project instanceof Project)? $project->getId():0, $genid) ?>
	</fieldset>
	</div>
	
	<div id="add_chart_add_tags_div" style="display:none">
	<fieldset><legend><?php echo lang('tags')?></legend>
		<?php echo autocomplete_tags_field("chart[tags]", array_var($chart_data, 'tags')); ?>
	</fieldset>
	</div>
	
	<div id="add_chart_display_div" style="display:none">
	<fieldset><legend><?php echo lang('display')?></legend>
		<div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('show in project') ?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('chart[show_in_project]', 'chartFormShowInProject', array_var($chart_data, 'show_in_project'), lang('yes'), lang('no')) ?></div>
			<div class="optionDesc"><?php echo lang('show in project desc') ?></div>
		</div>
		
		<div class="objectOption">
			<div class="optionLabel"><label><?php echo lang('show in parents') ?>:</label></div>
			<div class="optionControl"><?php echo yes_no_widget('chart[show_in_parents]', 'chartFormShowInParents', array_var($chart_data, 'show_in_parents'), lang('yes'), lang('no')) ?></div>
			<div class="optionDesc"><?php echo lang('show in parents desc') ?></div>
		</div>
		
	</fieldset>
	</div>
	
	<?php if (isset($chart)){
		echo $chart->Draw();		
}?>

</div>

</div>
</form>