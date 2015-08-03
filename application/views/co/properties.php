<?php $date_format = user_config_option('date_format'); ?>
<!-- Properties Panel -->
<table style="width:240px">
	<col width=12/><col width=216/><col width=12/>
	<tr>
		<td class="coViewHeader coViewSmallHeader" style="border:1px solid #ccc;" colspan=2 rowspan=2><div class="coViewPropertiesHeader"><?php echo lang("properties") ?></div></td>
		<td class="coViewTopRight"></td>
	</tr>
		
	<tr><td class="coViewRight" rowspan=2></td></tr>
	
	<tr>
		<td class="coViewBody" style="border:1px solid #ccc;" colspan=2>
			<div class="prop-col-div" style="width:200;">
				<span style="color:#333333;font-weight:bolder;"><?php echo lang('unique id') ?>:&nbsp;</span><?php echo $object->getUniqueObjectId() ?>
			</div>
			
	<?php if(false && $object->isLinkableObject() && !$object->isTrashed()) {?>
		<div id="linked_objects_in_prop_panel" class="prop-col-div" style="width:200;"><?php echo render_object_links($object, $object->canEdit(logged_user()))?></div>
	<?php } ?>
	
    <?php if ($object instanceof ContentDataObject && !isset($is_user)) { ?>

	<div class="prop-col-div" style="width:200;">
		<div id="<?php echo $genid ?>subscribers_in_prop_panel">
			<?php  echo render_object_subscribers($object)?>
		</div>
		<?php if ($object->canEdit(logged_user())) {
				$onclick_fn = "og.show_hide_subscribers_list('". $object->getId() ."', '". $genid ."');";
		?>
			<a id="<?php echo $genid.'add_subscribers_link' ?>" onclick="<?php echo $onclick_fn ?> return false;" href="#" class="ico-add internalLink" style="background-repeat: no-repeat; padding-left: 18px; padding-bottom: 3px;"><?php echo lang('modify object subscribers')?></a>
		<?php } ?>
	</div>
		
	<?php } ?>
	<div class="prop-col-div" style="width:200;">
    	<?php if($object->getCreatedBy() instanceof Contact && $object->getCreatedBy()->isUser()) { ?>
    		<div ><span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('created by') ?>:
			</span>
			<?php 
			if ($object->getCreatedBy() instanceof Contact && $object->getCreatedBy()->isUser()){
				if (logged_user()->getId() == $object->getCreatedBy()->getId())
					$username = lang('me');
				else
					$username = clean($object->getCreatedBy()->getObjectName());
					
				if ($object->getObjectCreationTime() && $object->getCreatedOn()->isToday()){
					$datetime = format_time($object->getCreatedOn());
					echo lang('user date today at bold', $object->getCreatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getCreatedBy()->getObjectName()));
				} else {
					$datetime = format_datetime($object->getCreatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date bold', $object->getCreatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getCreatedBy()->getObjectName()));
				}
			} ?></div>
    	<?php } // if ?>
    	
    	<?php
		/**
		 * This section displays more information of the task if object is a task. 
		 */
		?>    	
    	<?php if($object instanceof ProjectTask) { ?>
    	
    		<?php
    		//Last updated by 	
    		if($object->getUpdatedBy() instanceof Contact && $object->getUpdatedBy()->isUser()) { ?>
    		<div ><span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('last updated by') ?>:
			</span>
			<?php 
				if ($object->getUpdatedBy() instanceof Contact && $object->getUpdatedBy()->isUser()){
					if (logged_user()->getId() == $object->getUpdatedBy()->getId())
						$username = lang('me');
					else
						$username = clean($object->getUpdatedBy()->getObjectName());
						
					if ($object->getUpdatedOn() && $object->getUpdatedOn()->isToday()){
						$datetime = format_time($object->getUpdatedOn());
						echo lang('user date today at bold', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedBy()->getObjectName()));
					} else {
						$datetime = format_datetime($object->getUpdatedOn(), $date_format, logged_user()->getTimezone());
						echo lang('user date bold', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedBy()->getObjectName()));
					}
				} ?></div>
    		<?php } // if ?>  
    		
    		<?php
    		//archived by
		if ($object instanceof ContentDataObject && $object->isArchivable() && $object->isArchived()) { ?>
    		<div>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('archived by') ?>:
			</span>
			<?php
			$archive_user = Contacts::findById($object->getArchivedById());
			if ($archive_user instanceof Contact && $archive_user->isUser()) {
				if (logged_user()->getId() == $archive_user->getId()) {
					$username = lang('me');
				} else {
					$username = clean($archive_user->getObjectName());
				}

				if ($object->getArchivedOn()->isToday()) {
					$datetime = format_time($object->getArchivedOn());
					echo lang('user date today at bold', $archive_user->getCardUserUrl(), $username, $datetime, clean($archive_user->getObjectName()));
				} else {
					$datetime = format_datetime($object->getArchivedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date bold', $archive_user->getCardUserUrl(), $username, $datetime, clean($archive_user->getObjectName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		//deleted by
		if ($object instanceof ContentDataObject  && $object->isTrashable() && $object->getTrashedById() != 0) { ?>
    		<div>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('deleted by') ?>:
			</span>
			<?php
			$trash_user = Contacts::findById($object->getTrashedById());
			if ($trash_user instanceof Contact && $trash_user->isUser()){
				if (logged_user()->getId() == $trash_user->getId())
					$username = lang('me');
				else
					$username = clean($trash_user->getObjectName());

				if ($object->getTrashedOn()->isToday()){
					$datetime = format_time($object->getTrashedOn());
					echo lang('user date today at bold', $trash_user->getCardUserUrl(), $username, $datetime, clean($trash_user->getObjectName()));
				} else {
					$datetime = format_datetime($object->getTrashedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date bold', $trash_user->getCardUserUrl(), $username, $datetime, clean($trash_user->getObjectName()));
				}
			}
			 ?></div>
		<?php } // if ?>
		
		<?php
		//mime type
		if ($object instanceof ProjectFile && $object->getLastRevision() instanceof ProjectFileRevision) { ?>
			<div title="<?php echo  $mime ?>">
			<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('mime type') ?>:
    			<?php $mime = $object->getLastRevision()->getTypeString(); ?>
			</span>
				<?php if (strlen($mime) > 30) {
					echo substr_utf($mime, 0, 15) . '&hellip;' . substr_utf($mime, -15);
				} else {
					echo $object->getLastRevision()->getTypeString();
				}?>
			</div>
		<?php if ($object->isCheckedOut()) { ?>
	    		<span style="color:#333333;font-weight:bolder;">
	    			<?php echo lang('checked out by') ?>:
				</span><br/><div style="padding-left:10px">
				<?php
				$checkout_user = Contacts::findById($object->getCheckedOutById());
				if ($checkout_user instanceof Contact && $checkout_user->isUser()){
					if (logged_user()->getId() == $checkout_user->getId())
						$username = lang('me');
					else
						$username = clean($checkout_user->getObjectName());
	
					if ($object->getCheckedOutOn()->isToday()){
						$datetime = format_time($object->getCheckedOutOn());
						echo lang('user date today at bold', $checkout_user->getCardUserUrl(), $username, $datetime, clean($checkout_user->getObjectName()));
					} else {
						$datetime = format_datetime($object->getCheckedOutOn(), $date_format, logged_user()->getTimezone());
						echo lang('user date bold', $checkout_user->getCardUserUrl(), $username, $datetime, clean($checkout_user->getObjectName()));
					}
				}
			 ?></div>
		<?php }
			} // if ?>
			
    	</div><div class="prop-col-div" style="width:200;">
    	<?php
    	//Assigned By    		
    	if($object->getAssignedBy() instanceof Contact && $object->getAssignedBy()->isUser()) { ?>
    	<div ><span style="color:#333333;font-weight:bolder;">
    		<?php echo lang('assigned by') ?>:
		</span>
		<?php 
			if ($object->getAssignedBy() instanceof Contact && $object->getAssignedBy()->isUser()){
				if (logged_user()->getId() == $object->getAssignedBy()->getId())
					$username = lang('me');
				else
					$username = clean($object->getAssignedBy()->getObjectName());
						
				if ($object->getAssignedOn() && $object->getAssignedOn()->isToday()){
					$datetime = format_time($object->getAssignedOn());
					echo lang('user date today at bold', $object->getAssignedBy()->getCardUserUrl(), $username, $datetime, clean($object->getAssignedBy()->getObjectName()));
				} else {
					$datetime = format_datetime($object->getAssignedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date bold', $object->getAssignedBy()->getCardUserUrl(), $username, $datetime, clean($object->getAssignedBy()->getObjectName()));
				}
			} ?></div>
    	<?php } // if ?>  
    		
    		
    	<?php
    	//Start Date
    	if($object->getStartDate() instanceof DateTimeValue) { ?>
    	<div ><span style="color:#333333;font-weight:bolder;">
    		<?php echo lang('to start') ?>:
		</span>
		<?php 
													
				if ($object->getStartDate() && $object->getStartDate()->isToday()){
					$datetime = format_time($object->getStartDate());
					echo $datetime;
				} else {
					echo format_date($object->getStartDate(), null, logged_user()->getTimezone()).' <b>'.lang('at').'</b> '.format_time($object->getStartDate(), null, logged_user()->getTimezone());
				}
			 ?></div>
    	<?php } // if ?>  
    		
    		
    	<?php } // if ?>
    	
    	
    	
    	
    	<?php if($object->getObjectUpdateTime() && $object->getUpdatedBy() instanceof Contact && $object->getCreatedBy()->isUser() && $object->getCreatedOn() != $object->getUpdatedOn()) { ?>
    		<div>
    		<span style="color:#333333;font-weight:bolder;">
    			<?php echo lang('modified by') ?>:
			</span>
			<?php 
			if ($object->getUpdatedBy() instanceof Contact && $object->getUpdatedBy()->isUser()){
					
				if (logged_user()->getId() == $object->getUpdatedBy()->getId())
					$username = lang('me');
				else
					$username = clean($object->getUpdatedByDisplayName());

				if ($object->getUpdatedOn()->isToday()){
					$datetime = format_time($object->getUpdatedOn());
					echo lang('user date today at bold', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				} else {
					$datetime = format_datetime($object->getUpdatedOn(), $date_format, logged_user()->getTimezone());
					echo lang('user date bold', $object->getUpdatedBy()->getCardUserUrl(), $username, $datetime, clean($object->getUpdatedByDisplayName()));
				}
			}?></div>
		<?php } // if ?>
		
				
		
	</div>
	
	<?php Hook::fire("render_object_properties", $object, $ret = 0);?>
		</td>
	</tr>
	
	<tr>
		<td class="coViewBottomLeft" style="width:12px;">&nbsp;&nbsp;</td>
		<td class="coViewBottom" style="width:216px;"></td>
		<td class="coViewBottomRight" style="width:12px;">&nbsp;&nbsp;</td>
	</tr>
	</table>
