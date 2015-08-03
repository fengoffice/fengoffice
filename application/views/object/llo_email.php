<?php 
$icon_class = $linked_object->getObjectTypeName();

$belongs_to_conversation = MailContents::countMailsInConversation($linked_object) > 1;

?>
<tr class="<?php echo $counter % 2 ? 'even' : 'odd' ?>">
	<td style="padding-left:1px;vertical-align:middle;width:22px">
		<a class="internalLink" href="<?php echo $linked_object->getObjectUrl() ?>">
		<div class="db-ico unknown ico-<?php echo clean($icon_class) ?>" title="<?php echo clean($linked_object->getObjectTypeName()) ?>"></div>
	</a></td>
	
	<td><a class="internalLink" href="<?php echo $linked_object->getObjectUrl() ?>" title="<?php echo clean($linked_object->getObjectName()) ?>">
	<span><?php if ($linked_object->getState() == 2) {?><span style="color:red;font-weight:bold"><?php echo lang('draft') ?></span> - <?php } ?><?php echo clean($linked_object->getObjectName()) ?></span></a></td>
	
	<td><span class="desc"><?php echo lang('from')?>: </span><?php echo $linked_object->getFrom()?></td>
	
	<?php $date_str = $linked_object->getSentDate() instanceof DateTimeValue ? ($linked_object->getSentDate()->isToday() ? format_time($linked_object->getSentDate(), null, logged_user()->getTimezone()) : format_datetime($linked_object->getSentDate(), $date_format, logged_user()->getTimezone())) : lang('n/a') ?>
	<td><span class="desc"><?php echo lang('date')?>: </span><?php echo $date_str ?></td>
	
	<td>
	<?php if ($belongs_to_conversation) { ?>
		<div onclick="og.loadConversation('<?php echo $genid ?>', <?php echo $linked_object->getId()?>);" class="db-ico ico-comment" style="cursor: pointer;"></div>
	<?php } ?>
	</td>
	
	<td style="text-align:right;">
	<?php
	if ($linked_objects_object->canUnlinkObject(logged_user(), $linked_object)) { 
		echo '<a class="internalLink" href="' . $linked_objects_object->getUnlinkObjectUrl($linked_object) . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm unlink object')) . '\')" title="' . lang('unlink object') . '">' . lang('unlink') . '</a>';
	} ?>
	</td>
</tr>
<?php if ($belongs_to_conversation) { ?>
<tr>
	<td colspan=6><div id="<?php echo $genid ?>conversationdiv<?php echo $linked_object->getId()?>" style="display:none"></div></td>
</tr>
<?php } ?>

<script>
	og.loadConversation = function(genid, mail_id) {
		//alert(genid + 'conversationdiv' + mail_id);
		var conversationLoaded = document.getElementById(genid + 'conversationdiv' + mail_id).innerHTML != '';
		og.showHide(genid + 'conversationdiv' + mail_id);
		if (!conversationLoaded) {
			Ext.get(genid + 'conversationdiv' + mail_id).load({
				url: og.getUrl('mail', 'get_conversation_info', {id: mail_id}), scripts: true
			});
		}
	}
</script>