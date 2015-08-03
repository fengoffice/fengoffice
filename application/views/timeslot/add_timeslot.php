<?php $genid = gen_id(); ?>

<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $timeslot->getEditUrl() ?>" method="post">

<div class="timeslot">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo $timeslot->isNew() ? lang('new timeslot') : lang('edit timeslot') ?>
	</td><td style="text-align:right"><?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?></td></tr></table>
	</div>
	
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
  <div class="formAddTimeslotDescription">

    <div class="bold"><?php echo lang('description') ?>:&nbsp;</div>
    <?php echo textarea_field("timeslot[description]", array_var($timeslot_data, 'description'), array('class' => 'short', 'id' => 'addTimeslotDescription', 'tabindex' => '10')) ?>
  </div>

	<table style="margin-top:10px;">
<?php
	if (can_manage_time(logged_user())) {
		echo '<tr><td style="vertical-align:middle;"><span class="bold">' . lang("person") . ':&nbsp;</span></td>';
		
		if (logged_user()->isMemberOfOwnerCompany()) {
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
			<td align='left'><?php 
				$start_time = new DateTimeValue($timeslot->getStartTime()->getTimestamp() + logged_user()->getTimezone() * 3600) ;
				echo pick_date_widget2('timeslot[start_value]',$start_time, $genid, 20);
			?></td>
		</tr>
		
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("start time") ?>:&nbsp;</span></td>
			<td align='left'><select name="timeslot[start_hour]" size="1" tabindex="30">
			<?php
			for($i = 0; $i < 24; $i++) {
					echo "<option value=\"$i\"";
					if($start_time->getHour() == $i) echo ' selected="selected"';
					echo ">$i</option>\n";
				}
			?>
			</select> <span class="bold">:</span> <select name="timeslot[start_minute]" size="1" tabindex="40">
			<?php
			$minute = $start_time->getMinute();
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minute == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select></td>
		</tr><tr><td>&nbsp;</td></tr>
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("end date") ?>:&nbsp;</span></td>
			<td align='left'><?php 
				if ($timeslot->getEndTime() == null){
					$dt = DateTimeValueLib::now();
					$end_time = new DateTimeValue($dt->getTimestamp() + logged_user()->getTimezone() * 3600);
				} else
					$end_time = new DateTimeValue($timeslot->getEndTime()->getTimestamp() + logged_user()->getTimezone() * 3600) ;
			echo pick_date_widget2('timeslot[end_value]',$end_time, $genid, 40);
			?></td>
		</tr>
		
		<tr>
			<td style="vertical-align:middle;"><span class="bold"><?php echo lang("end time") ?>:&nbsp;</span></td>
			<td align='left'><select name="timeslot[end_hour]" size="1" tabindex="50">
			<?php
			for($i = 0; $i < 24; $i++) {
					echo "<option value=\"$i\"";
					if($end_time->getHour() == $i) echo ' selected="selected"';
					echo ">$i</option>\n";
				}
			?>
			</select> <span class="bold">:</span> <select name="timeslot[end_minute]" size="1" tabindex="60">
			<?php
			$minute = $end_time->getMinute();
			for($i = 0; $i < 60; $i++) {
				echo "<option value='$i'";
				if($minute == $i) echo ' selected="selected"';
				echo sprintf(">%02d</option>\n", $i);
			}
			?>
			</select></td>
		</tr><tr><td>&nbsp;</td></tr>
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

	<?php if ($show_billing) {?>
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
  	<?php } ?>
	<div class="clear"></div>
    <?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'), 's', array('tabindex' => '80')); ?>
</div>
</div>

</form>