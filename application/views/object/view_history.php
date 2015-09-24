<?php 
	$genid = gen_id();
?>
<div class="history" style="height:100%;background-color:white">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php echo lang('view history for') . ' ' . clean($object->getObjectName()); ?></div>
	</div>
</div>

<div class="coInputMainBlock adminMainBlock">


<div id="<?php echo $genid?>tabs" class="edit-form-tabs" style="display:none;">
	
	<ul id="<?php echo $genid?>tab_titles">
		<li><a id="<?php echo $genid?>mods-tab" href="#<?php echo $genid?>modifications"><?php echo lang('modifications tab') ?></a></li>
		<li><a id="<?php echo $genid?>views-tab" href="#<?php echo $genid?>views"><?php echo lang('views tab') ?></a></li>
	</ul>

	<div id="<?php echo $genid?>modifications" class="form-tab">
		<table style="min-width:400px; width:100%;">
			<tr>
				<th><?php echo lang('date')?></th>
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
		
		<div class="pagination">
			<?php echo render_view_history_pagination($object->getViewHistoryUrl(), $mod_logs_pagination, 'mod', array_var($view_logs_pagination, 'current_page')); ?>
		</div>
		
	</div>
	
	<div id="<?php echo $genid?>views" class="form-tab">
		<table style="min-width:400px; width:100%;">
			<tr>
				<th><?php echo lang('date')?></th>
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
		
		<div class="pagination">
			<?php echo render_view_history_pagination($object->getViewHistoryUrl(), $view_logs_pagination, 'view', array_var($mod_logs_pagination, 'current_page')); ?>
		</div>
		
	</div>
</div>

</div>
</div>

<script>
var curtab = '<?php echo $curtab?>';
$(function() {
	$("#<?php echo $genid?>tabs").tabs().show();
	
	if (curtab == 'view') {
		var hist_tab_int = setInterval(function() {
			if ($("#<?php echo $genid?>views-tab").length > 0) {
				$("#<?php echo $genid?>views-tab").click();
				clearInterval(hist_tab_int);
			}
		}, 100);
	}
});
</script>