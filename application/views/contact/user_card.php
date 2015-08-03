<?php 

	$isUserAccount = isset($object) && $object instanceof Contact;
	if(isset($user) && ($user instanceof Contact)) { 
?>
<div class="card" style="padding:0px;">

  <div class="cardIcon"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> avatar" /></div>
  <div class="cardData">
    
    <div class="cardBlock">
    	<div><span><?php echo lang('username') ?>: <?php echo clean($user->getUsername()) ?></span></div>
    	<div><span><?php echo lang('user title') ?>:</span> <?php echo $user->getJobTitle() ? clean($user->getJobTitle()) : lang('n/a') ?></div>
		<div><span><?php echo lang('company') ?>:</span> <a class="internalLink" href="<?php echo $user->getCompany()->getCardUrl() ?>"><?php echo clean($user->getCompany()->getObjectName()) ?></a></div>
      <div><span><?php echo lang('email address') ?>:</span> <a <?php echo logged_user()->hasMailAccounts() ? 'href="' . get_url('mail', 'add_mail', array('to' => clean($user->getEmailAddress()))) . '"' : 'target="_self" href="mailto:' . clean($user->getEmailAddress()) . '"' ?>><?php echo clean($user->getEmailAddress()) ?></a></div>
    </div>
  </div>
</div>

<?php if (false && isset($logs)){ 
	$genid = gen_id();
	?>
	<fieldset><legend class="toggle_expanded" onclick="og.toggle('<?php echo $genid ?>user_activity',this)"><?php echo lang('latest user activity') ?></legend>
		<div id="<?php echo $genid ?>user_activity"><table><col/><col style="padding-left:10px;"/><col style="padding-left:10px"/>
		<?php foreach ($logs as $log) {
			$log_object = $log->getObject();
			if ($log_object instanceof ApplicationDataObject){?>
			<tr><td><?php 
				if ($log->getCreatedOn()->isToday()){
					$datetime = format_time($log->getCreatedOn());
					echo lang('today at', $datetime);
				} else {
					echo format_date($log->getCreatedOn());
				}?></td>
			<td><div class="db-ico ico-<?php echo $log_object->getObjectTypeName() ?>"></div></td>
			<td><a class='internalLink' href='<?php echo $log_object->getObjectUrl() ?>'><?php echo clean($log_object->getObjectName()) ?></a></td>
			<td><?php echo $log->getText() ?></td>
			</tr>
			
		<?php } // if
			} //foreach ?>
		</table></div>
	</fieldset><br/>
<?php } //if ?>
<?php } // if ?>