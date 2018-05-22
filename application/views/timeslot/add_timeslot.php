<?php $genid = gen_id(); ?>
<style>
.timeslot .coInputMainBlock .dimension-selector-container label {
	margin-right: 10px;
	min-width: 0px;
}
.timeslot .left-section {
	max-width:550px;
}
.timeslot .right-section {
	margin-left: 5px;
	margin-top: 7px;
}
</style>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $timeslot->getEditUrl() ?>" method="post">

<div class="timeslot">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:1000px"><tr><td><?php echo $timeslot->isNew() ? lang('new timeslot') : lang('edit timeslot') ?>
	</td><td style="text-align:right"><?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'class'=>'blue')) ?></td></tr></table>
	</div>
	
	</div>
</div>


<div class="coInputMainBlock task-data">
  <div class="left-section">
	<table style="margin-top:10px;width:100%;">
		<tr>
		  <td style="width:150px">
			<span class="bold" style="vertical-align: top;"><?php echo lang('description') ?>:&nbsp;</span>
		  </td>
		  <td>
			<?php echo textarea_field("timeslot[description]", array_var($timeslot_data, 'description'), array('class' => 'long', 'id' => 'addTimeslotDescription', 'style' => 'width:300px;')) ?>
		  </td>
		</tr>
<?php

	$tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);

	if (can_manage_time(logged_user())) {
		echo '<tr><td style="vertical-align:middle;"><span class="bold">' . lang("person") . ':&nbsp;</span></td>';
		
		if (logged_user()->isAdministrator() || logged_user()->isMemberOfOwnerCompany()) {
			$users = Contacts::getAllUsers();
		} else {
			$users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = ". logged_user()->getCompanyId()) : array(logged_user());
		}
		$tmp_users = array();
		foreach ($users as $user) {
			$rel_object = $timeslot->getRelObject();
			$is_assigned = ($rel_object instanceof ProjectTask && $rel_object->getAssignedToContactId() == $user->getId());
			if ($is_assigned || can_add($user, $rel_object->getMembers(), Timeslots::instance()->getObjectTypeId())) {
				$tmp_users[] = $user;
			}
		}
		$users = $tmp_users;
		
		$user_options = array();
		foreach ($users as $user) {
			$user_options[] = option_tag($user->getObjectName(), $user->getId(), array_var($timeslot_data, 'contact_id') == $user->getId() ? array("selected" => "selected") : null);
		}
		echo '<td>' . select_box("timeslot[contact_id]", $user_options, array('id' => $genid . 'tsUser', 'tabindex' => '15')) . '</td></tr>';
		echo '<tr><td>&nbsp;</td></tr>';
	}
?>
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("start date") ?>:&nbsp;</span></td>
			<td align='left'>
				<table><tr><td>
				<?php 
					$start_time = new DateTimeValue($timeslot->getStartTime()->getTimestamp() + $tz_offset) ;
					echo pick_date_widget2('timeslot[start_value]',$start_time, $genid, 20);
				?>
				</td><td>
				<?php 
					echo pick_time_widget2('timeslot[start_time]', $start_time->getHour().":".str_pad($start_time->getMinute(), 2, 0, STR_PAD_LEFT), $genid, null, false);
				?>
				</td></tr></table>
			</td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("end date") ?>:&nbsp;</span></td>
			<td align='left'>
				<table><tr><td>
				<?php 
					if ($timeslot->getEndTime() == null){
						$dt = DateTimeValueLib::now();
						$end_time = new DateTimeValue($dt->getTimestamp() + $tz_offset);
					} else {
						$end_time = new DateTimeValue($timeslot->getEndTime()->getTimestamp() + $tz_offset) ;
					}
					echo pick_date_widget2('timeslot[end_value]',$end_time, $genid, 40);
				?>
				</td><td>
				<?php 
					echo pick_time_widget2('timeslot[end_time]', $end_time->getHour().":".str_pad($end_time->getMinute(), 2, 0, STR_PAD_LEFT), $genid, 41, false);
				?>
				</td></tr></table>
			</td>
		</tr>
		
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("total pause time") ?>:&nbsp;</span></td>
			<td align='left'><span><?php 
				$totalSeconds = $timeslot->getSubtract();
				$seconds = $totalSeconds % 60;
				$minutes = (($totalSeconds - $seconds) / 60) % 60;
				$hours = (($totalSeconds - $seconds - ($minutes * 60)) / 3600);
				
			?><input type="text" style="width:40px;margin-right:3px" name="timeslot[subtract_hours]" value="<?php echo($hours); ?>" tabindex="60"/><?php echo lang('hours') ?>,&nbsp;
			</span><select name="timeslot[subtract_minutes]" size="1" tabindex="70">
			<?php
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minutes == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select><span><?php echo lang('minutes') ?>,&nbsp;</span>
			<select name="timeslot[subtract_seconds]" size="1" tabindex="80">
			<?php
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($seconds == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select><span><?php echo lang('seconds') ?></span></td>
		</tr>
	</table>
	
	<?php if ($show_billing && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
		<br/>
		<?php echo radio_field('timeslot[is_fixed_billing]',!$timeslot_data['is_fixed_billing'],array('onchange' => 'og.showAndHide("' . $genid. 'hbilling",["' . $genid. 'fbilling"])', 
			'value' => '0', 'style' => 'width:16px')); echo '<span class="bold">' . lang('hourly billing') . '</span>'; ?>
		<?php echo radio_field('timeslot[is_fixed_billing]',$timeslot_data['is_fixed_billing'],array('onchange' => 'og.showAndHide("' . $genid. 'fbilling",["' . $genid. 'hbilling"])', 
		'value' => '1', 'style' => 'width:16px')); echo '<span class="bold">' . lang('fixed billing') . '</span>'; ?>
		<br /><br />
	  	<div id="<?php echo $genid ?>hbilling" style="<?php echo $timeslot_data['is_fixed_billing']?'display:none':'' ?>">
	    	<?php echo label_tag(lang('hourly rates'), 'addTimeslotHourlyBilling', false, array('style'=>'min-width:110px;')) ?>
	  		<?php echo text_field('timeslot[hourly_billing]',array_var($timeslot_data, 'hourly_billing'), array('id' => 'addTimeslotHourlyBilling', 'readonly' => 'readonly')) ?>
	  	</div>
	  	<div id="<?php echo $genid ?>fbilling" style="<?php echo $timeslot_data['is_fixed_billing']?'':'display:none' ?>">
	    	<?php echo label_tag(lang('billing amount'), 'addTimeslotFixedBilling', false, array('style'=>'min-width:110px;')) ?>
	  		<?php echo text_field('timeslot[fixed_billing]',array_var($timeslot_data, 'fixed_billing'), array('id' => 'addTimeslotFixedBilling')) ?>
	  	</div>
  	<?php } 
  	
  		$ret = null;
  		Hook::fire("edit_timeslot_form_additional_fields", $timeslot, $ret);
  	//style="float:left; width:auto; margin:10px; padding-left:10px; border-left:1px dotted #ccc;"
  	?>
	<div class="clear"></div>
  </div>
  <div class="right-section">
  	<div class="dimension-selector-container">
	<?php
		$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$timeslot->manager()->getObjectTypeId().')');
		if ($timeslot->isNew()) {
			render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $timeslot), null, null, false);
		} else {
			render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $timeslot->getMemberIds(), array('listeners' => $listeners, 'object' => $timeslot), null, null, false);
		} 
	?>
	</div>
  </div>
  <div class="clear"></div>
    <?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'), 's', array('tabindex' => '80')); ?>
</div>
</div>


</form>