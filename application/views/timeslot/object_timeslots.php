<?php
	$timeslots = $__timeslots_object->getTimeslots();
	$countTimeslots = 0;
	if (is_array($timeslots) && count($timeslots))
		$countTimeslots = count($timeslots);
	$random = rand();
	$open_timeslot = null;
?>


    <div class="commentsTitle">
    	<table style="width:100%"><tr><td><?php echo lang('work performed')?></td>
    		<?php if($__timeslots_object instanceof ProjectTask){ ?>
    			<td align=right><a style="font-weight:normal;font-size:80%" class="coViewAction ico-print" href="<?php echo get_url('reporting','total_task_times_by_task_print',array("id" => $__timeslots_object->getId())) ?>" target="_blank"><?php echo lang('print') ?></a>  </td>
    		<?php } ?>
    	</tr></table> 
    </div>
		<table style="width:100%;max-width:800px;margin-bottom:10px;" class="objectTimeslots" id="<?php echo $random ?>objectTimeslots" style="<?php echo $countTimeslots > 0? '':'display:none'?>">

<?php $counter = 0;
		foreach($timeslots as $timeslot) {
			$counter++;
			$options = array();
			if (!$__timeslots_object->isTrashed() && $timeslot->canEdit(logged_user())) {
				$options[] = '<a class="internalLink coViewAction ico-edit" href="' . $timeslot->getEditUrl() . '">' . lang('edit') . '</a>';
			}
			if (!$__timeslots_object->isTrashed() && $timeslot->canDelete(logged_user())) 
				$options[] = '<a class="internalLink coViewAction ico-delete" href="' . $timeslot->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete timeslot')) . '\')">' . lang('delete') . '</a>';
				
			if (!$__timeslots_object->isTrashed() && $timeslot->isOpen() && $timeslot->getContactId() == logged_user()->getId() && $timeslot->canEdit(logged_user())){
				$open_timeslot = $timeslot;
				$counter --;
			} else {
?>
			<tr class="timeslot <?php echo $counter % 2 ? 'even' : 'odd'; echo $timeslot->isOpen() ? ' openTimeslot' : '' ?>" id="timeslot<?php echo $timeslot->getId() ?>">
			<td style="padding-right:10px"><b><?php echo $counter ?>.</b></td>
			<?php if ($timeslot->getUser() instanceof Contact) { ?>
				<td style="padding-right:10px"><b><a class="internalLink" href="<?php echo $timeslot->getUser()->getCardUserUrl()?>" title=" <?php echo lang('user card of', clean($timeslot->getUser()->getObjectName())) ?>"><?php echo clean($timeslot->getUser()->getObjectName()) ?></a></b></td>
			<?php } else {?>
				<td style="padding-right:10px"><b><?php echo lang("n/a") ?></b></td>
			<?php } ?>
			<td style="padding-right:10px"><?php echo format_datetime($timeslot->getStartTime())?>
				&nbsp;-&nbsp;<?php echo $timeslot->isOpen() ? ('<b>' . lang('work in progress') . '</b>') : 
				( (format_date($timeslot->getEndTime()) != format_date($timeslot->getStartTime()))?  format_datetime($timeslot->getEndTime()): format_time($timeslot->getEndTime())) ?></td>
			<td style="padding-right:10px">
				<?php 
					echo DateTimeValue::FormatTimeDiff($timeslot->getStartTime(), $timeslot->getEndTime(), "hm", 60, $timeslot->getSubtract());
					if ($timeslot->getSubtract() > 0) {
						$now = DateTimeValueLib::now();
						echo " <span class='desc'>(" . lang('paused time') . ": " . DateTimeValue::FormatTimeDiff($now, $now, "hm", 60, $timeslot->getSubtract()) .")</span>";
					}
				?>
			</td>
			<td align="right" <?php if(count($options)) echo 'style="min-width:120px;"' ?>>
			<?php if(count($options)) { ?>
					<?php echo implode(' | ', $options) ?>
			<?php } // if ?>
			</td>
			</tr>
			
			<?php if ($timeslot->getDescription() != '') {?>
				<tr class="timeslot <?php echo $counter % 2 ? 'even' : 'odd'; echo $timeslot->isOpen() ? ' openTimeslot' : '' ?>" ><td></td>
				<td colspan=6 style="color:#666666"><?php echo nl2br(clean($timeslot->getDescription())) ?></td></tr>
			<?php } //if ?>
		<?php } //if 
		} // foreach ?>
		</table>



<?php if (!$__timeslots_object->isTrashed()){
	if ($open_timeslot) {
		echo render_open_timeslot_form($__timeslots_object, $open_timeslot);
	} else { 
		if($__timeslots_object->canAddTimeslot(logged_user())) { 
			echo render_timeslot_form($__timeslots_object);
		} // if
	} // if
	} // if ?>
<br/>