<?php
	$show_help_option = user_config_option('show_context_help'); 
	if ($show_help_option == 'always' || ($show_help_option == 'until_close' && user_config_option('show_dashboard_info_widget_context_help', true, logged_user()->getId()))) {
		render_context_help($this, 'chelp dashboard info widget', 'dashboard_info_widget');
	}
	$date_format = user_config_option('date_format');
?>

<div style="padding:10px">
<?php 


	$project = active_project();
	$contacts = ProjectContacts::getContactsByProject($project);
	//if (can_manage_contacts(logged_user())){
		if (count($contacts) > 0){
			?><div class='endSeparatorDiv'>
			<b><?php echo lang('workspace contacts') ?>:</b>
				<div style='padding-left:15px'><?php
			$c = 0;
			foreach ($contacts as $contact){
				if ($c != 0)
					echo '<br/>';
				$c++;
				if ($contact->canView(logged_user())) {
				?><span><a href="<?php echo $contact->getCardUrl()?>" class="internalLink coViewAction ico-contact"><?php echo $contact->getObjectName() ?></a> - <span class="desc"><?php echo $contact->getRole(active_project())->getRole() ?></span></span><?php
				}
			}
			?></div>
			</div>
<?php }//}

	if (logged_user()->isMemberOfOwnerCompany()){
		$users = $project->getUsers(false); 
		if (count($users) > 1){
			?><div class='endSeparatorDiv' id='workspaceUsersDiv'>
			<b><?php echo lang('shared with') ?>:</b>
			<div style='padding-left:15px'><?php
			$c = 0;
			//echo var_dump($users);
			foreach ($users as $user){
				if ($user instanceof User && $user->getId() != logged_user()->getId()){
					$c++;
					?><div class="dashSMDIU" style="white-space:nowrap;<?php echo ($c > 3 && count($users) > 5)? 'display:none':''?>"><a href="<?php echo $user->getCardUrl()?>" class="internalLink coViewAction ico-user"><?php echo clean($user->getObjectName()) ?></a></div><?php
				}
			}
			if (count($users) > 5) {?>
			<div id="dashSMDIUT" style="width:100%;text-align:left">
				<a href="#" onclick="og.hideAndShowByClass('dashSMDIUT', 'dashSMDIU', 'workspaceUsersDiv'); return false;"><?php echo lang("show all amount", count($users) -4) ?>...</a>
			</div>
			<?php } ?>
			</div>
		</div>
<?php }} ?>	

<table><?php if ($project->getCreatedBy() instanceof User){ ?>
		<tr><td><?php echo lang('created by') ?>:</td>
		<td style="padding-left:10px"><?php 
				if (logged_user()->getId() == $project->getCreatedById())
					$username = lang('you');
				else
					$username = clean($project->getCreatedByDisplayName());

				if ($project->getCreatedOn()->isToday()){
					$datetime = format_time($project->getCreatedOn());
					echo lang('user date today at', $project->getCreatedByCardUrl(), $username, $datetime, clean($project->getCreatedByDisplayName()));
				} else {
					$datetime = format_datetime($project->getCreatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $project->getCreatedByCardUrl(), $username, $datetime, clean($project->getCreatedByDisplayName()));
				}
			 ?></td></tr>
	<?php } 
		if ($project->getUpdatedBy() instanceof User){ ?>
		<tr><td><?php echo lang('modified by') ?>:</td>
		<td style="padding-left:10px"><?php 
				if (logged_user()->getId() == $project->getUpdatedById())
					$username = lang('you');
				else
					$username = clean($project->getUpdatedByDisplayName());

				if ($project->getUpdatedOn()->isToday()){
					$datetime = format_time($project->getUpdatedOn());
					echo lang('user date today at', $project->getUpdatedByCardUrl(), $username, $datetime, clean($project->getUpdatedByDisplayName()));
				} else {
					$datetime = format_datetime($project->getUpdatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date', $project->getUpdatedByCardUrl(), $username, $datetime, clean($project->getUpdatedByDisplayName()));
				}
			 ?></td></tr>
	<?php } ?>
	<tr><td colspan="2">
		<?php echo render_custom_properties($project); ?><br/>
	</td></tr>
	<?php if (config_option("show_feed_links")) { ?>
		<tr><td colspan="2"><a target="_blank" class="link-ico ico-rss" href="<?php echo get_url('feed', 'project_activities', array('id' => logged_user()->getId(), 'token' => logged_user()->getTwistedToken(), 'project' => $project->getId())) ?>"><?php echo lang("recent project activities feed", clean($project->getName()))?></a></td></tr>
	<?php } ?>
</table>
</div>