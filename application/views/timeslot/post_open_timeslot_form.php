<?php $genid = gen_id();
	
?>

<form class="internalForm" action="<?php echo $timeslot_form_timeslot->getCloseUrl() ?>" method="post" enctype="multipart/form-data">

<?php if ($timeslot_form_timeslot->isPaused()){ ?>
<div class="og-timeslot-work-paused" style="margin-top:6px;">
<?php echo lang('paused timeslot message', DateTimeValue::FormatTimeDiff($timeslot_form_timeslot->getStartTime(), $timeslot_form_timeslot->getPausedOn(), "hm", 60, $timeslot_form_timeslot->getSubtract())) ?>
<span style="padding-left:15px;"><?php echo lang('time since pause') ?>:&nbsp;<span id="<?php echo $genid ?>timespan"></span></span></div>
<script>
	og.startClock('<?php echo $genid ?>', <?php echo $timeslot_form_timeslot->getSecondsSincePause() ?>);
</script>
<?php } else { ?>
<div class="og-timeslot-work-started" style="margin-top:6px;"><?php echo lang('open timeslot message') ?>&nbsp;<span id="<?php echo $genid ?>timespan"></span></div>
<script>
	og.startClock('<?php echo $genid ?>', <?php echo $timeslot_form_timeslot->getSeconds() ?>);
</script>

<?php } ?>

  <div class="formAddCommentText">
  <?php echo label_tag(lang("end work description"),"closeTimeslotDescription",false) ?>
    <?php echo textarea_field("timeslot[description]", '', array('class' => 'short', 'id' => 'closeTimeslotDescription')) ?>
  </div>
<?php
	if ($timeslot_form_timeslot->isPaused())
		echo submit_button(lang('resume work'), '',array('style' => 'margin-right:15px', 'onclick' => 'javascript:this.form.action = "' . $timeslot_form_timeslot->getResumeUrl() . '"'));
	else
		echo submit_button(lang('pause work'), '',array('style' => 'margin-right:15px', 'onclick' => 'javascript:this.form.action = "' . $timeslot_form_timeslot->getPauseUrl() . '"')); 
	 echo submit_button(lang('end work'));?>
<?php echo submit_button(lang('cancel'),'',
	array('style' => 'margin-left:15px', 'onclick' => 'javascript:if(confirm("' . lang('confirm cancel work timeslot') . '")) {this.form.action += "&cancel=true"} else return false;')) ?>
</form>