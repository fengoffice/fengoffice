<?php
	require_javascript("og/ReportingFunctions.js");
	$genid = gen_id();
	
	if (count($report_data) == 0) {
		$report_data['show_billing'] = $has_billing;
	}
	if (!array_var($report_data, 'date_type')) {
		$report_data['date_type'] = 4;
	}
	if (!isset($conditions)) $conditions = array();
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'total_task_times') ?>" method="post" enctype="multipart/form-data">

<div class="reportTotalTimeParams">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo lang('task time report') ?>
		<?php //echo submit_button(lang('generate report'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?></div>
	</div>
	<div style="padding:5px 0"><?php echo lang('task time report description') ?></div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
	
	<?php if (count(active_context_members(false)) > 0) : ?>
	<div style="margin-bottom: 15px; padding-bottom: 5px; border-bottom:1px dotted #aaa;">
		<h5><?php echo lang('showing information for')?>:</h5>
		<ul>
		<?php
			$context = active_context();
			foreach ($context as $selection) :
				if ($selection instanceof Member) : ?>
					<li><span class="coViewAction <?php echo $selection->getIconClass()?>"><?php echo $selection->getName()?></span></li>	
		<?php 	endif;
			endforeach;
		?>
		</ul>
	</div>
	<?php else: ?>
	<div style="padding:5px;"></div>
	<?php endif; ?>

	<table>
		<tr style='height:30px;'>
			<td><span class="bold"><?php echo lang("date") ?>:&nbsp;</span></td>
			<td align='left'><?php 
				echo select_box('report[date_type]', array(
					option_tag(lang('today'), 1, array_var($report_data, "date_type") == 1 ? array('selected' => 'selected'):null),
					option_tag(lang('this week'), 2, array_var($report_data, "date_type") == 2 ? array('selected' => 'selected'):null),
					option_tag(lang('last week'), 3, array_var($report_data, "date_type") == 3 ? array('selected' => 'selected'):null),
					option_tag(lang('this month'), 4, array_var($report_data, "date_type") == 4 ? array('selected' => 'selected'):null),
					option_tag(lang('last month'), 5, array_var($report_data, "date_type") == 5 ? array('selected' => 'selected'):null),
					option_tag(lang('select dates...'), 6, array_var($report_data, "date_type") == 6 ? array('selected' => 'selected'):null)
				), array('onchange' => 'og.dateselectchange(this)'));
			?></td>
		</tr>
		<?php
			if (array_var($report_data, "date_type") == 6) {
				$style = "";
		       	$st = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, array_var($report_data, 'start_value'));
		       	$et = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, array_var($report_data, 'end_value'));
			} else {
				$style = 'display:none;';
				$st = DateTimeValueLib::now();
				$et = $st;
			} 
		?>
		<tr class="dateTr"  style="<?php echo $style ?>">
			<td><span class="bold"><?php echo lang("start date") ?>:&nbsp;</span></td>
			<td align='left'><?php echo pick_date_widget2('report[start_value]', $st, $genid);?></td>
		</tr>
		<tr class="dateTr"  style="<?php echo $style ?>">
			<td style="padding-bottom:18px"><span class="bold"><?php echo lang("end date") ?>:&nbsp;</span></td>
			<td align='left'><?php echo pick_date_widget2('report[end_value]', $et, $genid);?></td>
		</tr>
		<tr style='height:30px;'>
			<td><span class="bold"><?php echo lang("timeslots") ?>:&nbsp;</span></td>
			<td align='left'><?php 
				echo select_box('report[timeslot_type]', array(
					option_tag(lang('task timeslots'), 0, array_var($report_data, "timeslot_type") == '0' ? array('selected' => 'selected') : null),
					option_tag(lang('time timeslots'), 1, array_var($report_data, "timeslot_type") == '1' ? array('selected' => 'selected') : null),
					option_tag(lang('all timeslots'), 2, array_var($report_data, "timeslot_type") == '2' ? array('selected' => 'selected') : null)
				), array('onchange' => 'og.timeslotTypeSelectChange(this, \'' . $genid . '\')'));
			?>
				<span id="<?php echo $genid?>task_ts_desc" class="desc" style="display:<?php echo  array_var($report_data, "timeslot_type") == '0' ? '' : 'none'?>"><?php echo lang('task timeslots report desc')?></span>
				<span id="<?php echo $genid?>general_ts_desc" class="desc" style="display:<?php echo  array_var($report_data, "timeslot_type") == '1' ? '' : 'none'?>"><?php echo lang('general timeslots report desc')?></span>
			</td>
		</tr>
		<tr style='height:30px;'>
			<td><span class="bold"><?php echo lang("person") ?>:&nbsp;</span></td>
			<td align='left'><?php 
				$options = array();
				$options[] = option_tag('-- ' . lang('anyone') . ' --', 0, array_var($report_data, "user") == null?array('selected' => 'selected'):null);
				foreach($users as $user){
					$options[] = option_tag($user->getObjectName(),$user->getId(), array_var($report_data, "user") == $user->getId()?array('selected' => 'selected'):null);
				}
				echo select_box('report[user]', $options);
			?></td>
		</tr>
		
		
		<tr style='height:30px;' id="<?php echo $genid ?>repGroupBy">
			<td><span class="bold"><?php echo lang("group by") ?>:&nbsp;</span></td>
			<td align='left'>
				<span id="<?php echo $genid ?>gbspan" style="<?php echo array_var($report_data, "timeslot_type") == 0 ? 'display:inline':'display:none' ?>">
					<?php for ($i = 1; $i <= 3; $i++){ 
						$gbVal = array_var($report_data, "group_by_$i");
						?>
					<select id="<?php echo $genid ?>group_by_<?php echo $i ?>" name="report[group_by_<?php echo $i ?>]" )">
						<option value="0"<?php if ($gbVal == null) echo ' selected="selected"' ?>><?php echo lang('none') ?></option>
						<option value="rel_object_id"<?php if ($gbVal == "rel_object_id") echo ' selected="selected"' ?>><?php echo lang('task')?></option>
						<option value="contact_id"<?php if ($gbVal == "contact_id") echo ' selected="selected"' ?>><?php echo lang('person')?></option>
						<option value="priority"<?php if ($gbVal == "priority") echo ' selected="selected"' ?>><?php echo lang('priority')?></option>
						<option value="milestone_id"<?php if ($gbVal == "milestone_id") echo ' selected="selected"' ?>><?php echo lang('milestone')?></option>
						<?php
							$gbs = array();
							Hook::fire('total_task_timeslots_group_by_criterias', null, $gbs);
							foreach($gbs as $gb) { ?>
								<option value="<?php echo $gb['val']?>"<?php if ($gbVal == $gb['val']) echo ' selected="selected"' ?>><?php echo $gb['name']?></option>
						<?php } 
						?>
					</select>
					<?php } // for ?>
				</span>
				<span id="<?php echo $genid ?>altgbspan" style="<?php echo array_var($report_data, "timeslot_type") == 0 ? 'display:none':'display:inline' ?>">
					<?php for ($i = 1; $i <= 3; $i++){ 
						$gbVal = array_var($report_data, "alt_group_by_$i");
						?>
					<select id="<?php echo $genid ?>alt_group_by_<?php echo $i ?>" name="report[alt_group_by_<?php echo $i ?>]" )">
						<option value="0"<?php if ($gbVal == null) echo ' selected="selected"' ?>><?php echo lang('none') ?></option>
						<option value="contact_id"<?php if ($gbVal == "contact_id") echo ' selected="selected"' ?>><?php echo lang('user')?></option>
						<option value="rel_object_id"<?php if ($gbVal == "rel_object_id") echo ' selected="selected"' ?>><?php echo lang('task')?></option>
						<?php
							$gbs = array();
							Hook::fire('total_task_timeslots_group_by_criterias', null, $gbs);
							foreach($gbs as $gb) { ?>
								<option value="<?php echo $gb['val']?>"<?php if ($gbVal == $gb['val']) echo ' selected="selected"' ?>><?php echo $gb['name']?></option>
						<?php } 
						?>
					</select>
					<?php } // for ?>
				</span>
			</td>
		</tr>
		
		<?php if (false) { //FIXME ?>
		<tr style='height:30px;'>
			<td>&nbsp;</td>
			<td align='left'>
				<?php echo checkbox_field('report[include_unworked]', array_var($report_data, 'include_unworked', false), array("id" => "report[include_unworked]")); ?> 
	      		<label for="<?php echo 'report[include_unworked]' ?>" class="checkbox"><?php echo lang('include unworked pending tasks') ?></label>
			</td>
		</tr>
		<?php } ?>
		
		<?php if ($has_billing && can_manage_billing(logged_user())) {?>
		<tr style='height:30px;'>
			<td><span class="bold"><?php echo lang('show billing information') ?></span></td>
			<td align='left' style="padding-left:10px;">
				<?php echo checkbox_field('report[show_billing]', array_var($report_data, 'show_billing', false), array("id" => "report[show_billing]")); ?>
			</td>
		</tr>
		<?php } ?>
		<tr style='height:30px;'>
			<td><span class="bold"><?php echo lang('show estimated time column') ?></span></td>
			<td align='left' style="padding-left:10px;">
				<?php echo checkbox_field('report[show_estimated_time]', array_var($report_data, 'show_estimated_time', true), array("id" => "report[show_estimated_time]")); ?> 
			</td>
		</tr>
		
		<?php if (isset($has_custom_properties) && $has_custom_properties) {?>
		<tr>
			<td style="padding-top:10px;padding-right:10px;"><span class="bold"><?php echo lang('custom properties') ?>:&nbsp;</span></td>
			<td><div style="border:1px dotted #aaa; border-radius:5px; padding:5px 5px 7px;">
				<div id="<?php echo $genid ?>" style="margin-bottom:5px;"></div>
				<a href="#" class="link-ico ico-add" onclick="og.addCondition('<?php echo $genid ?>', 0, 0, '', '', '', false, true)"><?php echo lang('add condition')?></a>
			</div></td>
		</tr>
		<?php } ?>
		
	</table>
	
<br/>
<?php echo submit_button(lang('generate report'),'s',array('style'=>'margin-top:0px;')) ?>
</div>
</div>

</form>

<script>
	og.loadReportingFlags();	
	og.reportTask('<?php echo $genid?>', '<?php echo array_var($report_data, 'order_by') ?>', '<?php echo array_var($report_data, 'order_by_asc') ?>', '');	

	var first = document.getElementById('<?php echo $genid ?>reportFormName');
	if (first) first.focus();
	
</script>