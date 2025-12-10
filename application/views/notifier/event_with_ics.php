<style>
	body {
		margin: 20px 0;
		padding:0;
	}
</style>
<?php
$msg_css = 'padding: 10px 20px; border-radius: 8px; line-height: 20px;';
if ($notification == 'modified') {
	$msg_css .= ' background-color: #e6f4ea; color: #0d5327;';
	$color_css = 'color: #0d5327;';
} else if ($notification == 'deleted') {
	$msg_css .= ' background-color: #ffdada; color: #550000;';
	$color_css = 'color: #550000;';
}
?>
<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php if (in_array($notification, ['modified', 'deleted'])): ?>
		<div style="<?php echo $msg_css; ?>">
			<div style="<?php echo $color_css; ?>"><?php echo $notification_msg ?></div>
			<div style="<?php echo $color_css; ?>"><?php echo $notification_details ?></div>
		</div>
	<?php endif; ?>
	

	<div id="invitation-from" style="margin: 20px 10px;"><?php echo lang('invitation from feng_evx calendar', product_name()); ?></div>
</div>

