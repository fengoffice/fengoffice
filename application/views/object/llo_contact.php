<?php 
//$linked_object = new Contact();
$icon_class = $linked_object->getIconClass();
if (!isset($attr)) $attr = "";
?>
<tr class="<?php echo $counter % 2 ? 'even' : 'odd' ?>">
	<td style="padding-left:1px;vertical-align:middle;width:22px">
		<a class="internalLink" href="<?php echo $linked_object->getObjectUrl() ?>">
		<div class="db-ico unknown <?php echo clean($icon_class) ?>" title="<?php echo clean($linked_object->getObjectTypeName()) ?>"></div>
	</a></td>
	
	<td><a <?php echo $attr ?> href="<?php echo $linked_object->getObjectUrl() ?>" title="<?php echo clean($linked_object->getObjectName()) ?>">
		<?php echo clean($linked_object->getObjectName()) ?>
	</a></td>
	
	<td><?php echo clean($linked_object->getJobTitle())?></td>
	
	<td>
		<?php 
		$email_attrs = array('to' => clean($linked_object->getEmailAddress()));
		if ($linked_objects_object instanceof ProjectTask) {
			$email_attrs['link_to_objects'] = 'ProjectTasks-' . $linked_objects_object->getId();
		}?>
		<a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', $email_attrs) . '"' : 'target="_self" href="mailto:' . clean($linked_object->getEmailAddress()) . '"' ?>><?php echo clean($linked_object->getEmailAddress());?></a></td>
	
	<td style="text-align:right;">
		<?php if ($linked_objects_object->canUnlinkObject(logged_user(), $linked_object)) { 
			echo '<a class="internalLink" href="' . $linked_objects_object->getUnlinkObjectUrl($linked_object) . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm unlink object')) . '\')" title="' . lang('unlink object') . '">' . lang('unlink') . '</a>';
		} ?>
	</td>
</tr>