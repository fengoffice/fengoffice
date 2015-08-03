<?php 
$show_help_option = user_config_option('show_context_help'); 
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_comments_widget_context_help', true, logged_user()->getId()))) { 
	render_context_help($this, 'chelp comments widget', 'comments_widget');
} ?>

<div style="padding:10px">
<table id="dashTableComments" style="width:100%">

<?php $c = 0;
	foreach ($comments as $comment){ $c++;?>
	<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'dashSMCoC':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
	<td><div class="db-ico ico-comment"></div></td>
	<td style="padding-left:5px">
	<a class="internalLink" href="<?php echo $comment->getViewUrl()?>"
		title="<?php echo lang('comment posted on by linktitle', format_datetime($comment->getCreatedOn()), clean($comment->getCreatedByDisplayName())) ?>">
	<?php echo clean($comment->getObject()->getObjectName()) ?>
	</a>
	<span class="previewText"><?php echo clean($comment->getPreviewText(100)) ?></span>
	</td></tr>
<?php } // foreach?>
	<?php /*if ($c >= 10) {?>
		<tr class="dashSMCoC" style="display:none"><td></td>
		<td style="text-align:right"><a href="#" onclick="Ext.getCmp('tabs-panel').activate('messages-panel');"><?php echo lang('show all') ?>...</a>
		</td></tr>
	<?php } */ ?>
</table>
<?php if ($c > 5) { ?>
<div id="dashSMCoT" style="width:100%;text-align:right">
	<a href="#" onclick="og.hideAndShowByClass('dashSMCoT', 'dashSMCoC', 'dashTableComments'); return false;"><?php echo lang("show more") ?>...</a>
</div>
<?php } // if ?>
</div>