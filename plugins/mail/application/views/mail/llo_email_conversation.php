
<?php if (is_array($emails_info)) { ?>
<table style="width:100%;">
<?php foreach($emails_info as $count => $email) { ?>

<tr class="<?php echo $count % 2 ? 'odd' : 'even' ?>">
	<td style="width:10px;text-align:center;"><?php echo ($email['id'] == $source_email_id ? "*" : "") ?></td>
	
	<td style="width:20px;">
		<?php if ($email['has_att']) { ?>
		<div class="db-ico ico-attachment"></div>
		<?php } ?>
	</td>
	<td style="width:20px;">
		<?php if ($email['show_ico']) { ?>
		<div class="db-ico ico-user"></div>
		<?php } ?>
	</td>
	<?php $view_url = get_url('mail', 'view', array('id' => $email['id'])) ?>
	<td style="<?php echo $email['read'] ? "" : "font-weight: bold;" ?>">
		<a class="internalLink" href="<?php echo $view_url ?>" onclick="og.openLink('<?php echo $view_url ?>');return false;" title="<?php echo clean($email['from']); ?>">
			<?php echo $email['from_name']?>
		</a><span class="desc">- <?php echo $email['text'] ?></span>
	</td>
	
	
	<td style="text-align:right;"><span class="desc"><?php echo lang('date')?>: </span>
		<?php echo $email['date'] ?></td>	
	
</tr>
<?php } //foreach ?>
</table>
<?php } //if ?>