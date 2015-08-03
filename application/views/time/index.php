<?php
	require_javascript('og/time/main.js');
	require_javascript('og/time/drawing.js');

	// FIXME: para cuando se haga el billing habilitar $show_billing
	$show_billing = false;//can_manage_billing(logged_user());
	
	$genid = gen_id();
	$tasks_array = array();
	$timeslots_array = array();
	$users_array = array();
	$all_users_array = array();
	$companies_array = array();
	if (isset($tasks)) {
		foreach($tasks as $task) {
			$tasks_array[] = $task->getArrayInfo();
		}
	}
	if (isset($timeslots)) {
		foreach($timeslots as $timeslot) {
			/* @var $timeslot Timeslot */
			$timeslots_array[] = $timeslot->getArrayInfo($show_billing);
		}
	}
	if (isset($users)) {
		foreach($users as $user) {
			$info = $user->getArrayInfo();
			if ($user->getId() == logged_user()->getId()) $info['isCurrent'] = true;
			$users_array[] = $info;
		}
	}
	if (isset($all_users)) {
		foreach($all_users as $user) {
			$info = $user->getArrayInfo();
			if ($user->getId() == logged_user()->getId()) $info['isCurrent'] = true;
			$users_array[] = $info;
		}
	}
	if (isset($companies)) {
		foreach($companies as $company) {
			$companies_array[] = $company->getArrayInfo();
		}
	}
	
	$display_members = true;
	$context = active_context();
	foreach ($context as $selection) {
		if ($selection instanceof Member) {
			$display_members = false;
			break;
		}
	}

?>

<style>

</style>

<div id="timePanel" class="ogContentPanel" style="height:100%;">
<div style="padding:7px;">
<input type="hidden" id="<?php echo $genid ?>hfTasks" value="<?php echo clean(json_encode($tasks_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfTimeslots" value="<?php echo clean(json_encode($timeslots_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfUsers" value="<?php echo clean(json_encode($users_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfAllUsers" value="<?php echo clean(json_encode($all_users_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfCompanies" value="<?php echo clean(json_encode($companies_array)) ?>"/>
<input type="hidden" id="<?php echo $genid ?>hfDrawInputs" value="<?php echo $draw_inputs ? "1" : "0" ?>"/>

<table style="width:100%; display:none;" id="<?php echo $genid ?>active_tasks_table">
	<tr>
		<td colspan=2 class="TMActiveTasksHeader">
			<?php echo lang('all active tasks') ?>
		</td>
		<td class="coViewTopRight">&nbsp;</td>
	</tr>
	<tr>
		<td colspan=2 class="coViewBody" style="background-color:white;">
			<div id="<?php echo $genid ?>TMActiveTasksContents" class="TMActiveTasksContents">
			
			</div>
		</td>
		<td class="coViewRight"></td>
	</tr>
	<tr>
		<td class="coViewBottomLeft"></td>
		<td class="coViewBottom">&nbsp;</td>
		<td class="coViewBottomRight"></td>
	</tr>
</table>



<table style="width:100%;" class="general-timeslots">

<tr>
	<td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td>
	<td style="height:0px;overflow:hidden;line-height:0px;">&nbsp;</td>
	<td style="width:12px;height:1px;overflow:hidden;line-height:0px;"></td>
</tr>
<tr>
	<td colspan="2" rowspan="2">
	<div id="<?php echo $genid ?>TMTimespanHeader" class="TMTimespanHeader" style="width:100%;">
		<div style="padding:3px 7px">
		<table style="width:100%;"><tr>
			<td>
				<?php echo lang('time timeslots') ?>
			</td>
			<td align="right" style="font-size:80%;font-weight:normal">
				<a href="<?php echo get_url("reporting",'total_task_times_p', array('type' => '1', 'ws' => active_project() instanceof Project ? active_project()->getId() : 0)) ?>" class="internalLink coViewAction ico-print" style="color:white;font-weight:bold"><?php echo lang('generate report') ?></a>
			</td>
		</tr></table>
		</div>
	</div>
	
	<script type="text/javascript">
	//submit the form when the user press enter
	og.checkEnterPress = function (e,genid)
	{
		var characterCode;
		if(e && e.which){
			characterCode = e.which;
		}
		else{
			e = event;
			characterCode = e.keyCode;
		}
		if(characterCode == 13){
			ogTimeManager.SubmitNewTimeslot(genid);
			return false;
		}
	}
	</script>
	<div id="<?php echo $genid ?>TMTimespanAddNew" class="TMTimespanAddNew" <?php echo ($draw_inputs ? "" : 'style="display:none;"') ?>>
		<input type="hidden" id="<?php echo $genid ?>tsId" name="timeslot[id]" value=""/>
		<div style="padding:7px;">
			
			<div class="context-body" style="float: left; margin-bottom: 5px;">
				<?php
					//get skipped dimensions for this view
					$dimensions_to_show = explode(",",user_config_option("add_timeslot_view_dimensions_combos"));
					$dimensions_to_show = is_array($dimensions_to_show)? array_filter($dimensions_to_show) : array();
					$dimensions_to_skip = array_diff(get_user_dimensions_ids(), $dimensions_to_show);
					$listeners = array();
					$listeners = array('on_selection_change' => 'ogTimeManager.renderUserCombo("'.$genid.'")');
					if(!empty($dimensions_to_show)){
						render_member_selectors(Timeslots::instance()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'horizontal' => true, 'width' => '270'), $dimensions_to_skip, null, true);	
					}
					?>
					
			</div>
						
			<div class="small-member-selector TMTimespanSelectorHeight" style="<?php echo (can_manage_time(logged_user())) ? '':'display: none;'?>">
				<?php echo label_tag(lang('user')) ?>
				<?php
					$options = array();
					foreach ($users as $user) {
						$options[] = option_tag($user->getObjectName(), $user->getId(), $selected_user == $user->getId() ? array("selected" => "selected") : null);
					}
					echo select_box("timeslot[contact_id]", $options, array('id' => $genid . 'tsUser', 'style' => 'max-width:100px','tabIndex' => 1000,'class' => 'TMTimespanRealSelector')); 
				?>
			</div>
			
			<div class="small-member-selector TMTimespanSelectorHeight" >
				<?php echo label_tag(lang('date')) ?>
				<?php echo pick_date_widget2('timeslot[date]', DateTimeValueLib::now(), $genid, 1000, false) ?>
			</div>
			
			<div class="small-member-selector TMTimespanSelectorHeight" >
				<?php echo label_tag(lang('hours')) ?>
				<?php echo text_field('timeslot[hours]', 0, 
							array('style' => 'width:45px', 'id' => $genid . 'tsHours', 'tabIndex'=>1000,'onkeypress'=>'og.checkEnterPress(event,\''.$genid.'\')')) ?>
			</div>
			
			<div class="small-member-selector TMTimespanSelectorHeight" >
				<?php echo label_tag(lang('minutes')) ?>
				<select name="timeslot[minutes]" class="TMTimespanRealSelector" tabIndex=1000 style="width: 63px" size="1" id="<?php echo $genid . 'tsMinutes'?>">
								<?php
									$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
									for($i = 0; $i < 12; $i++) {
										echo "<option value=\"" . $minuteOptions[$i] . "\"";
										echo ">" . $minuteOptions[$i] . "</option>\n";
									}
								?>
				</select>
			</div>
			
			<div class="small-member-selector" style="width: 280px; ">
				<?php echo label_tag(lang('description'), '', '') ?>
				<div id="<?php echo $genid .'tsDesc'?>" tabIndex=1000 contentEditable="true" class="TMTimespanDesc"></div>
			</div>
			
			<div class="small-member-selector submit-btns" style="margin-top: 20px;">
				<div id="<?php echo $genid ?>TMTimespanSubmitAdd"><?php echo submit_button(lang('add'),'s',array('class'=>'blue', 'style'=>'margin-top:0px;margin-left:0px;', 'tabIndex'=>'1000','onclick' => 'ogTimeManager.SubmitNewTimeslot(\'' .$genid . '\');return false;')) ?></div>
				<div id="<?php echo $genid ?>TMTimespanSubmitEdit" style="display:none">
					<?php echo submit_button(lang('save'),'s',array('style'=>'margin-top:0px;margin-left:0px', 'class'=>'blue',
								'onclick' => 'ogTimeManager.SubmitNewTimeslot(\'' .$genid . '\');return false;')) ?>
					<?php echo submit_button(lang('cancel'),'c',array('style'=>'margin-top:0px;margin-left:0px', 
								'onclick' => 'ogTimeManager.CancelEdit();return false;')) ?>
				</div>
			</div>			
			
					
		</div>
	</div>
	<div id="<?php echo $genid ?>TMTimespanAddNew" class="TMTimespanAddNew" style="padding: 6px 0;<?php echo (!$draw_inputs ? "" : 'display:none;') ?>">
		<?php
			$names = array();
			$context = active_context();
			foreach ($context as $dimension) {
				$names[] = $dimension->getName();
			} 
		?>
		<span class="desc" style="padding: 0 12px;">* <?php echo lang('select member to add timeslots', implode(", ", $names))?></span>
	</div>

	</td>
	<td class="coViewTopRight">&nbsp;</td>
</tr>
<tr><td colspan="2" class="coViewRight"></td></tr>
<tr style="border-left: 1px solid #ddd;">
	<td colspan="2" class="coViewBody">
		<div id="<?php echo $genid ?>TMTimespanContents" style="width:100%" class="TMTimespanContents">
		<div style="padding:7px">
			<table style="width:100%" id="<?php echo $genid ?>TMTimespanTable">
			<tr>
				<td width='20%'><span class="bold"><?php echo lang('related to') ?></span></td>
				<td width='15%'><span class="bold"><?php echo lang('user') ?></span></td>
				<td width='70px'><span class="bold"><?php echo lang('date') ?></span></td>
				<td width='60px'><span class="bold"><?php echo lang('time') ?></span></td>
				<td><span class="bold"><?php echo lang('description') ?></span></td>
				<?php if ($show_billing) { ?>
					<td width="100px"><span class="bold"><?php echo lang('billing') ?></span></td>
				<?php } ?>
				<td width='220px'><span class="bold"><?php echo lang('last updated by') ?></span></td>
				<td></td>
			</tr>
			</table>
		</div>
		</div>
	</td>
	<td class="coViewRight"></td>
</tr>
<tr style="border-left: 1px solid #ddd;">
	<td colspan="2" class="coViewBody">
	<?php if ($total > 0) {
		$page = intval($start / $limit);
		$totalPages = ceil($total / $limit);
		if ($totalPages > 1) {
			$a_nav = array(
				'<span class="x-tbar-page-first" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-prev" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-next" style="padding-left:16px"/>', 
				'<span class="x-tbar-page-last" style="padding-left:16px"/>'
			);
			$nav = '';
			if ($page != 0) { ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => '0', 'limit' => $limit)) ?>"><span class="x-tbar-page-first db-ico" style="padding-left:16px">&nbsp;</span></a>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $start - $limit, 'limit' => $limit)) ?>"><span class="x-tbar-page-prev db-ico" style="padding-left:16px">&nbsp;</span></a>&nbsp;
			<?php } else { ?>
				<span class="og-disabled x-tbar-page-first db-ico" style="padding-left:16px">&nbsp;</span>
				<span class="og-disabled x-tbar-page-prev db-ico" style="padding-left:16px">&nbsp;</span>&nbsp;
			<?php }
			for ($i = 1; $i < $totalPages + 1; $i++) {
				$off = $limit * ($i - 1);
				if(($i != $page + 1) && abs($i - 1 - $page) <= 2 ) { ?>
					<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><?php echo $i ?></a>&nbsp;
				<?php } else if($i == $page + 1) { ?>
					<span class="bold"><?php echo $i ?></span>&nbsp;
				<?php }
			}
			if ($page < $totalPages - 1) {
				$off = $start + $limit; ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><span class="x-tbar-page-next db-ico" style="padding-left:16px">&nbsp;</span></a>
				<?php $off = $limit * ($totalPages - 1); ?>
				<a class="internalLink" href="<?php echo get_url('time', 'index', array('start' => $off, 'limit' => $limit)) ?>"><span class="x-tbar-page-last db-ico" style="padding-left:16px">&nbsp;</span></a>
			<?php } else { ?>
				<span class="og-disabled x-tbar-page-next db-ico" style="padding-left:16px">&nbsp;</span>
				<span class="og-disabled x-tbar-page-last db-ico" style="padding-left:16px">&nbsp;</span>
			<?php } ?>
			<br/><span class='desc'>&nbsp;<?php echo lang('total') . ": " . $totalPages . " " . lang('pages') ?></span>
		<?php }
	} ?>
	</td>
	<td class="coViewRight"></td>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom">&nbsp;</td>
	<td class="coViewBottomRight"></td>
</tr>
</table>
</div>

<script>
	ogTimeManager.loadDataFromHF('<?php echo $genid ?>');
	ogTimeManager.drawTasks('<?php echo $genid ?>');
	ogTimeManager.drawTimespans('<?php echo $genid ?>');	
	Ext.getCmp("<?php echo $genid ?>timeslot[date]Cmp").focus();
	$('.context-header').click(function(){
		$('.context-body').slideToggle();
	});
	
	$(function() {
		og.eventManager.fireEvent('replace all empty breadcrumb', null);
	});
</script>
</div>
