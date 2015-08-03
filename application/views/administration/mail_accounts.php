<?php
set_page_title(lang('mail accounts'));

if (can_add_mail_accounts(logged_user())) {
	add_page_action(lang('add mail account'), get_url('mail', 'add_account'), 'ico-add');
} // if

$genid = gen_id();
?>

<div id="<?php echo $genid ?>adminContainer" class="adminMailAccounts">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('mail accounts') ?>
	</div>
  </div>

</div>

<div class="coInputMainBlock adminMainBlock">
<?php if(isset($all_accounts) && is_array($all_accounts) && count($all_accounts)) { ?>
<table class="adminListing" style="min-width: 400px;">
	<tr>
		<th><?php echo lang('name') ?></th>
		<th><?php echo lang('email address') ?></th>
		<th><?php echo lang('owned by') ?></th>
		<th><?php echo lang('incoming server') ?></th>
		<th><?php echo lang('outgoing server') ?></th>
		<th><?php echo lang('users') ?></th>
		<th><?php echo lang('options') ?></th>
	</tr>
	<?php
	$isAlt = true;
	foreach($all_accounts as $account) {
		$isAlt = !$isAlt;
		?>
	<tr class="<?php echo $isAlt? 'altRow' : ''?>">
		<td><?php echo clean($account->getName()) ?></td>
		<td><?php echo $account->getEmailAddress() ?></td>
		<td><?php echo $account->getOwner() instanceof Contact ? $account->getOwner()->getObjectName() : lang("n/a") ?></td>
		<td><?php echo $account->getServer() ?></td>
		<td><?php echo $account->getSmtpServer() ?></td>
		<td><?php echo MailAccountContacts::countByAccount($account) ?></td>
		<?php
		$options = array();
		if (($account->canDelete(logged_user()) && $account->getContactId() == logged_user()->getId()) || $account->canEdit(logged_user())) {
			$options[] = '<a class="internalLink" href="'.get_url('mail', 'edit_account', array('id' => $account->getId())).'">' . lang('edit') . '</a>';
		}
		if ($account->canDelete(logged_user())) {
			$options[] = '<a class="internalLink" href="javascript:og.promptDeleteAccount(' . $account->getId() . ', true)">' . lang('delete') . '</a>';
		} // if
		if ($account->canDelete(logged_user()) && config_option("sent_mails_sync")) {		
			$options[] = '<a class="internalLink" href="'.get_url('mail', 'sync_old_sent_mails', array('id' => $account->getId())).'">' . lang('sync') . '</a>';
		}		
		?>
		<td><?php echo implode(' | ', $options) ?></td>
	</tr>
	<?php } // foreach ?>
</table>
	<?php } else { ?> <?php echo lang('no email accounts') ?> <?php } // if ?>
	
	<div class="add" style="margin: 10px">
		<a href="<?php echo get_url("mail", "add_account") ?>" class="internalLink ico-add link-ico"><?php echo lang("add mail account") ?></a>
	</div>
	
</div>

</div>
