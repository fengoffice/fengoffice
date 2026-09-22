<style>
	body {
		margin: 20px 0;
		padding:0;
	}
</style>
<?php
$msg_css = 'padding: 10px 20px; border-radius: 8px; line-height: 20px;';
$color_css = '';
if ($notification == 'modified') {
	$msg_css .= ' background-color: #e6f4ea; color: #0d5327;';
	$color_css = 'color: #0d5327;';
} else if ($notification == 'deleted') {
	$msg_css .= ' background-color: #ffdada; color: #550000;';
	$color_css = 'color: #550000;';
}
$event_title = isset($title) ? $title : ($object instanceof ProjectEvent ? $object->getObjectName() : '');
?>
<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php if (in_array($notification, ['modified', 'deleted'])): ?>
		<div style="<?php echo $msg_css; ?>">
			<div style="<?php echo $color_css; ?>"><?php echo $notification_msg ?></div>
			<div style="<?php echo $color_css; ?>"><?php echo $notification_details ?></div>
		</div>
	<?php endif; ?>

	<div id="invitation-from" style="margin: 20px 10px;"><?php echo lang('invitation from feng_evx calendar', product_name()); ?></div>

	<div style="margin: 10px; padding: 14px; border: 1px solid #ccc; border-radius: 6px;">
		<div style="font-size: 16px; font-weight: bold; margin-bottom: 12px;">
			<?php if ($object instanceof ProjectEvent && method_exists($object, 'getViewUrl') && isset($recipient_is_user) && $recipient_is_user): ?>
				<a href="<?php echo $object->getViewUrl() ?>" target="_blank" style="font-size: 16px;"><?php echo clean($event_title) ?></a>
			<?php else: ?>
				<?php echo clean($event_title) ?>
			<?php endif; ?>
		</div>

		<?php if (!empty($start)): ?>
		<div style="line-height: 20px; margin-bottom: 4px;">
			<?php echo lang('date') ?>: <b><?php echo $start ?></b>
		</div>
		<?php endif; ?>

		<?php if (!empty($time)): ?>
		<div style="line-height: 20px; margin-bottom: 4px;">
			<?php echo lang('time') ?>: <b><?php echo $time ?></b>
		</div>
		<?php endif; ?>

		<?php if (!empty($duration)): ?>
		<div style="line-height: 20px; margin-bottom: 4px;">
			<?php echo lang('CAL_DURATION') ?>: <b><?php echo $duration ?></b>
		</div>
		<?php endif; ?>

		<?php if (!empty($description)): ?>
		<div style="line-height: 20px; margin-top: 10px;">
			<strong><?php echo lang('description') ?>:</strong>
			<div style="margin-top: 4px;"><?php echo nl2br(clean($description)) ?></div>
		</div>
		<?php endif; ?>

		<?php if (!empty($guests)): ?>
		<div style="line-height: 20px; margin-top: 12px;">
			<strong><?php echo lang('event invitations') ?>:</strong>
			<div style="margin-top: 6px;"><?php echo $guests ?></div>
		</div>
		<?php endif; ?>
	</div>
</div>
