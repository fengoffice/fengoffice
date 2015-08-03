<?php 
$show_help_option = user_config_option('show_context_help'); 
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_messages_widget_context_help', true, logged_user()->getId()))) {
	render_context_help($this, 'chelp messages widget', 'messages_widget');
} ?>
  
<div style="padding:10px">
<table id="dashTableMessages" style="width:100%">
<?php $c = 0;
	foreach ($messages as $message){ $c++;?>
	<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'dashSMMC':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
	<td><div class="db-ico ico-message"></div></td>
	<td style="padding-left:5px">
	<?php 
		$mws = $message->getWorkspaces(logged_user()->getWorkspacesQuery());
		$projectLinks = array();
		foreach ($mws as $ws) {
			$projectLinks[] =  $ws->getId();
		}
		echo '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';?>
	<a class="internalLink" href="<?php echo get_url('message','view', array('id' => $message->getId()))?>"
		title="<?php echo lang('message posted on by linktitle', format_datetime($message->getCreatedOn()), clean($message->getCreatedByDisplayName())) ?>">
	<?php echo clean($message->getTitle()) ?>
	</a></td></tr>
<?php } // foreach?>
	<?php if ($c >= 10) {?>
		<tr class="dashSMMC" style="display:none"><td></td>
		<td style="text-align:right"><a href="#" onclick="Ext.getCmp('tabs-panel').activate('messages-panel');"><?php echo lang('show all') ?>...</a>
		</td></tr>
	<?php } ?>
</table>
<?php if ($c > 5) { ?>
<div id="dashSMMT" style="width:100%;text-align:right">
	<a href="#" onclick="og.hideAndShowByClass('dashSMMT', 'dashSMMC', 'dashTableMessages'); return false;"><?php echo lang("show more") ?>...</a>
</div>
<?php } // if ?>
</div>