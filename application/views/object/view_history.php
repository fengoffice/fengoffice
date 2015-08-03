<?php 
	$genid = gen_id(); 
	$key = $genid.'mod';
?>
<div class="history" style="height:100%;background-color:white">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php echo lang('view history for') . ' ' . clean($object->getObjectName()); ?></div>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock adminMainBlock">

<div id="bt<?php echo $genid?>">
<a href="#" style="<?php echo $key == $genid.'mod' ? 'font-weight:bold' : 'font-weight:normal'?>;margin-right:10px" onclick="og.toggleSimpleTab('<?php echo $genid.'mod'; ?>LO', 'hp<?php echo $genid; ?>', 'bt<?php echo $genid; ?>', this)">
<?php echo lang("modifications tab") ?><span style="font-size:80%"></span></a>

<a href="#" style="<?php echo $key == $genid.'view' ? 'font-weight:bold' : 'font-weight:normal'?>;margin-right:10px" onclick="og.toggleSimpleTab('<?php echo $genid.'view' ?>LO', 'hp<?php echo $genid ?>', 'bt<?php echo $genid ?>', this)">
<?php echo lang("views tab") ?><span style="font-size:80%"></span></a>
<div id="hp<?php echo $genid?>">

<div id="<?php echo $genid.'mod' ?>LO" style="<?php echo $key == $genid.'mod' ? '' : 'display:none'?>">
<table style="min-width:400px;margin-top:10px;">
<tr><th><?php echo lang('date')?></th>
<th><?php echo lang('user')?></th>
<th><?php echo lang('details')?></th>
</tr>
<?php
$isAlt = true;
if (is_array($logs)) {
	foreach ($logs as $log) {
		$isAlt = !$isAlt;
		echo '<tr' . ($isAlt? ' class="altRow"' : '') . '><td  style="padding:5px;padding-right:15px;">';
		if ($log->getCreatedOn()->getYear() != DateTimeValueLib::now()->getYear())
			$date = format_time($log->getCreatedOn(), "M d Y, H:i");
		else{
			if ($log->isToday())
				$date = lang('today') . format_time($log->getCreatedOn(), ", H:i:s");
			else
				$date = format_time($log->getCreatedOn(), "M d, H:i");
		}
		if($log->getAction()==ApplicationLogs::ACTION_LOGIN  /*FIXME || ($log->getRelObjectManager() == 'Timeslots' && ($log->getAction()==ApplicationLogs::ACTION_OPEN || $log->getAction()==ApplicationLogs::ACTION_CLOSE))*/) {
			echo $date . ' </td><td style="padding:5px;padding-right:15px;"><a class="internalLink" href="' . ($log->getTakenBy() instanceof Contact ? $log->getTakenBy()->getCardUserUrl() : '#') . '">'  . clean($log->getTakenByDisplayName()) . '</a></td><td style="padding:5px;padding-right:15px;"> ' . $log->getText();
		} else {
			$output = null;
			Hook::fire('override_view_history_log', array('object' => $object, 'log' => $log), $output);
			if ($output == null) {
				$activity_data = $log->getActivityData();
			} else {
				$activity_data = $output;
			}
			echo $date . ' </td><td style="padding:5px;padding-right:15px;"><a class="internalLink" href="' . ($log->getTakenBy() instanceof Contact ? $log->getTakenBy()->getCardUserUrl() : '#') . '">'  . clean($log->getTakenByDisplayName()) . '</a></td><td style="padding:5px;padding-right:15px;"> ' . $activity_data;
		}
		echo '</td></tr>';
	}
}

?>
</table>
</div>

<div id="<?php echo $genid.'view' ?>LO" style="<?php echo $key == $genid.'view' ? '' : 'display:none'?>">
<table style="min-width:400px;margin-top:10px;">
<tr><th><?php echo lang('date')?></th>
<th><?php echo lang('user')?></th>
<th><?php echo lang('details')?></th>
</tr>
<?php
$isAlt = true;
if (is_array($logs_read) && count($logs_read)) {
	foreach ($logs_read as $log) {
		$isAlt = !$isAlt;
		echo '<tr' . ($isAlt? ' class="altRow"' : '') . '><td  style="padding:5px;padding-right:15px;">';
		if ($log->getCreatedOn()->getYear() != DateTimeValueLib::now()->getYear())
			$date = format_time($log->getCreatedOn(), "M d Y, H:i");
		else{
			if ($log->isToday())
				$date = lang('today') . format_time($log->getCreatedOn(), ", H:i:s");
			else
				$date = format_time($log->getCreatedOn(), "M d, H:i");
		}
		echo $date . ' </td><td style="padding:5px;padding-right:15px;"><a class="internalLink" href="' . ($log->getTakenBy() instanceof Contact ? $log->getTakenBy()->getCardUserUrl() : '#') . '">'  . clean($log->getTakenByDisplayName()) . '</a></td><td style="padding:5px;padding-right:15px;"> ' . $log->getText();
		echo '</td></tr>';
	}
}

?>
</table>
</div>
</div>
</div>
</div>
</div>
