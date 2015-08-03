<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><br><br>

<?php echo lang("$context $type reminder desc", clean($object->getObjectName()), $date) ?><br><br>

<?php echo lang("view $type") ?>: <?php echo str_replace('&amp;', '&', $object->getViewUrl()) ?><br><br>

<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>