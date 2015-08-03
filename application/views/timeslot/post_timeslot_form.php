<?php $genid = gen_id(); ?>
<table><tr><td style="padding-right:15px" id="<?php echo $genid?>tdstartwork">
<form class="internalForm" action="<?php echo Timeslot::getOpenUrl($timeslot_form_object) ?>" method="post" enctype="multipart/form-data">
<?php echo submit_button(lang('start work')) ?>
</form>
</td><td>
<form class="internalForm" action="<?php echo Timeslot::getAddTimespanUrl($timeslot_form_object) ?>" method="post" enctype="multipart/form-data">
<button id="<?php echo $genid?>buttonAddWork" type="button" class="submit" onclick="addWork('<?php echo $genid?>')"><?php echo lang('add work') ?></button>

<div id="<?php echo $genid?>addwork" style="display:none;">

<div style="float:left;margin-left:10px;">
<?php
	if (can_manage_time(logged_user())) {
		echo label_tag(lang("person"), $genid . "closeTimeslotDescription", false);
		
		if (logged_user()->isMemberOfOwnerCompany()) {
			$users = Contacts::getAllUsers();
		} else {
			$users = logged_user()->getCompanyId() > 0 ? Contacts::getAllUsers(" AND `company_id` = ". logged_user()->getCompanyId()) : array(logged_user());
		}
		$tmp_users = array();
		foreach ($users as $user) {
			$is_assigned = ($timeslot_form_object instanceof ProjectTask && $timeslot_form_object->getAssignedToContactId() == $user->getId());
			if ($is_assigned || can_add($user, $timeslot_form_object->getMembers(), Timeslots::instance()->getObjectTypeId())) {
				$tmp_users[] = $user;
			}
		}
		$users = $tmp_users;
		
		$user_options = array();
		foreach ($users as $user) {
			$user_options[] = option_tag($user->getObjectName(), $user->getId(), logged_user()->getId() == $user->getId() ? array("selected" => "selected") : null);
		}
		echo select_box("timeslot[contact_id]", $user_options, array('id' => $genid . 'tsUser', 'tabindex' => '60'));
	}
?>
</div>

<div style="float:left;margin-left:10px;">
	<?php echo label_tag(lang("end work description"), $genid . "closeTimeslotDescription", false) ?>
	<?php echo textarea_field("timeslot[description]", '', array('class' => 'short', 'id' => $genid . 'closeTimeslotDescription', 'tabindex' => '70')) ?>
</div>

<div style="float:left;margin-left:10px;">
	<?php echo label_tag(lang('total time'), "closeTimeslotTotalTime", false) ?>
	<div style="float:left;">
		<span><?php echo lang("hours") ?>:&nbsp;</span>
		<?php echo text_field("timeslot[hours]", '', array('style' => 'width:30px', 'tabindex' => '80')) ?>
	</div>
	<div style="float:left;margin-left:10px;">
		<span><?php echo lang("minutes") ?>:&nbsp;</span>
		<select name="timeslot[minutes]" size="1" tabindex="85">
		<?php
			$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
			for($i = 0; $i < 12; $i++) {
				echo "<option value=\"" . $minuteOptions[$i] . "\">" . $minuteOptions[$i] . "</option>\n";
			}
		?></select>
	</div>
</div>

<div class="clear"></div>

<?php echo submit_button(lang('add work'), null, array('tabindex' => '200')) ?>
<button class="submit" style="margin-left:15px" id="buttonAddWorkCancel" type="button" tabindex="201" onclick="cancleWork('<?php echo $genid?>')"><?php echo lang('cancel') ?></button>
</div>

</form>
</td></tr></table>

<script>
    function addWork(genid){
        document.getElementById(genid + 'addwork').style.display = 'inline';
        document.getElementById(genid + 'buttonAddWork').style.display = 'none';
        document.getElementById(genid + 'tdstartwork').style.display = 'none';
        document.getElementById(genid + 'closeTimeslotDescription').focus();
        return false;
    }
    
    function cancleWork(genid){
        document.getElementById(genid + 'addwork').style.display = 'none';
        document.getElementById(genid + 'buttonAddWork').style.display = 'inline';
        document.getElementById(genid + 'tdstartwork').style.display = '';
        return false;
    }
    
</script>