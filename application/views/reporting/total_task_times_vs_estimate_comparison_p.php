<?php
	$genid = gen_id();
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('reporting', 'total_task_times_vs_estimate_comparison') ?>" method="post" enctype="multipart/form-data">

<div class="reportTotalTimeParams">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo lang('task time report') ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">

	<table>
		<tr>
			<td><b><?php echo lang("start date") ?>:&nbsp;</b></td>
			<td align='left'><?php 
				echo pick_date_widget2('report[start]',DateTimeValueLib::now(), $genid);
			?></td>
		</tr>
		<tr style='height:30px;'>
			<td ><b><?php echo lang("end date") ?>:&nbsp;</b></td>
			<td align='left'><?php 
				echo pick_date_widget2('report[end]',DateTimeValueLib::now(), $genid);
			?></td>
		</tr>
		<tr style='height:30px;'>
			<td><b><?php echo lang("user") ?>:&nbsp;</b></td>
			<td align='left'><?php 
				$options = array();
				$options[] = option_tag('-- ' . lang('anyone') . ' --', 0, array('selected' => 'selected'));
				foreach($users as $user){
					$options[] = option_tag($user->getObjectName(),$user->getId());
				}
				echo select_box('report[user]', $options);
			?></td>
		</tr>
		<tr style='height:30px;'>
			<td><b><?php echo lang("workspace") ?>:&nbsp;</b></td>
			<td align='left'>
				<?php echo select_project('report[project_id]', $workspaces,null,null,true);
					echo checkbox_field('report[include_subworkspaces]', true, array('id' => 'report[include_subworkspaces]' )) ?> 
	      <label for="<?php echo 'report[include_subworkspaces]' ?>" class="checkbox"><?php echo lang('include subworkspaces') ?></label>
			</td>
		</tr>
		<tr style='height:30px;'>
			<td colspan=2 align='left'>
				<?php echo checkbox_field('report[show_details]', false, array('id' => 'report[show_details]' )) ?> 
	      <label for="<?php echo 'report[show_details]' ?>" class="checkbox"><?php echo lang('show details') ?></label>
			</td>
		</tr>
	</table>
	
<br/>
<?php echo submit_button(lang('generate report'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
</div>
</div>

</form>