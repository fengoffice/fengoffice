<?php 
$show_help_option = user_config_option('show_context_help'); 
if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_emails_widget_context_help', true, logged_user()->getId()))) {
		render_context_help($this, 'chelp emails widget', 'emails_widget');
	}?>
	
<div style="padding:10px">
<table id="dashTableEmails" style="width:100%">
<?php $c = 0;
	
	$emails = array();
	if (is_array($unread_emails)) {
		$emails = $unread_emails;
	}
	foreach ($emails as $email){ 
		if (!$email->getIsDeleted()) {
			$c++;?>
			<tr class="<?php echo $c % 2 == 1? '':'dashAltRow'; echo ' ' . ($c > 5? 'dashSMUC':''); ?>" style="<?php echo $c > 5? 'display:none':'' ?>">
			<td><div class="db-ico ico-email" /></td>
			<td style="padding-left:5px">
			<?php 
				$mws = $email->getWorkspaces(logged_user()->getWorkspacesQuery());
				$projectLinks = array();
				foreach ($mws as $ws) {
					$projectLinks[] = $ws->getId();
				}
				echo  '<span class="project-replace">' . implode(',',$projectLinks) . '</span>';  //Commented as unread emails are not yet assignable to workspaces*/?>
			<a class="internalLink" style="font-weight:bold" href="<?php echo get_url('mail','view', array('id' => $email->getId()))?>"
				title="">
			<?php echo clean($email->getSubject()) ?>
			</a><br/><table width="100%" style="color:#888"><tr><td><?php echo clean($email->getFrom())?></td><td align=right><?php echo $email->getSentDate() instanceof DateTimeValue ? ($email->getSentDate()->isToday() ? format_time($email->getSentDate()) : format_date($email->getSentDate()) ) : lang("n/a")?></td></tr></table></td></tr>
	<?php } // if?>
<?php } // foreach?>
	<?php if ($c >= 10) {?>
		<tr class="dashSMUC" style="display:none"><td></td>
		<td style="text-align:right"><a href="#" onclick="Ext.getCmp('tabs-panel').activate('mails-panel');"><?php echo lang('show all') ?>...</a>
		</td></tr>
	<?php } ?>
</table>
<?php if ($c > 5) { ?>
<div id="dashSMUT" style="width:100%;text-align:right">
	<a href="#" onclick="og.hideAndShowByClass('dashSMUT', 'dashSMUC', 'dashTableEmails'); return false;"><?php echo lang("show more") ?>...</a>
</div>
<?php } // if ?>
</div>