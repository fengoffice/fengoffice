<?php

if (is_array($logs) && count($logs)> 0) {	
	//FIXME $is_user = $logs[0]->getRelObjectManager() == 'Users' ? true : false;
	$is_user = false;
	if(!isset ($no_permissions)) $no_permissions = '';
	if ($is_user)$no_permissions = (logged_user()->getId()!=$user_id && !logged_user()->isAdministrator()) ? true : false;			
	if (!$is_user || ($is_user && !$no_permissions)){	

?>

<div class="commentsTitle"><?php echo lang('latest activity'); ?> </div>

<table style="min-width:400px;margin-top:10px;" class='dashActivity'>
<tbody>
<?php
$isAlt = true;
if (is_array($logs)) {
	foreach ($logs as $log) {		
		//FIXME if ($log->getRelObjectManager() == 'Users' && $no_permissions) break;		
		$isAlt = !$isAlt;
		echo '<tr' . ($isAlt? ' class="dashAltRow"' : '') . '><td  style="padding:5px;padding-right:15px;">';
		if ($log->getCreatedOn()->getYear() != DateTimeValueLib::now()->getYear())
			$date = format_time($log->getCreatedOn(), "M d Y, H:i");
		else{
			if ($log->isToday())
				$date = lang('today') . format_time($log->getCreatedOn(), ", H:i:s");
			else
				$date = format_time($log->getCreatedOn(), "M d, H:i");
		}
		/*FIXME if($log->getRelObjectManager() == 'Timeslots' && ($log->getAction()==ApplicationLogs::ACTION_OPEN || $log->getAction()==ApplicationLogs::ACTION_CLOSE))
			echo $date . ' </td><td style="padding:5px;padding-right:15px;"> ' . $log->getText();
		else*/
			echo $date . ' </td><td style="padding:5px;padding-right:15px;"> ' . $log->getActivityData();
		echo '</td></tr>';
	}
}

?>
</tbody>
</table>

<a style="display:block" class="internalLink" href='<?php echo $object->getViewHistoryUrl() ?>' ><?php echo lang('view all activity'); ?></a>
<?php  	}
}
?>

<br>
