<?php 
//$linked_object = new ProjectTask();
$icon_class = $linked_object->getObjectTypeName();
if ($linked_object->getPriority() == 300){
	$icon_class .= '-high-priority';
} else if ($linked_object->getPriority() == 100) {
	$icon_class .= '-low-priority';
}
$date_format = user_config_option('date_format');
$attr = $linked_object->isCompleted() ? 'style="text-decoration:line-through"' : '';
?>
<tr class="<?php echo $counter % 2 ? 'even' : 'odd' ?>">
	<td style="padding-left:1px;vertical-align:middle;width:22px">
		<a class="internalLink" href="<?php echo $linked_object->getObjectUrl() ?>">
		<div class="db-ico unknown ico-<?php echo clean($icon_class) ?>" title="<?php echo clean($linked_object->getObjectTypeName()) ?>"></div>
	</a></td>
	
	<td><a <?php echo $attr ?> href="<?php echo $linked_object->getObjectUrl() ?>" title="<?php echo clean($linked_object->getObjectName()) ?>">
		<?php if ($linked_object->isAssigned()){?><b><?php echo clean($linked_object->getAssignedToName()) ?></b>: <?php } // if is assigned?>
		<span><?php echo clean($linked_object->getObjectName()) ?></span>
	</a></td>
	
	<td><?php echo format_datetime($linked_object->getUpdatedOn(), $date_format, logged_user()->getTimezone());?></td>
	
	<td style="text-align:right;">
		<?php if ($linked_objects_object->canUnlinkObject(logged_user(), $linked_object)) { 
			echo '<a class="internalLink" href="' . $linked_objects_object->getUnlinkObjectUrl($linked_object) . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm unlink object')) . '\')" title="' . lang('unlink object') . '">' . lang('unlink') . '</a>';
		} ?>
	</td>
</tr>