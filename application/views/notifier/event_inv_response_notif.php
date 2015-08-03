<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">

	<?php echo "<a href='".$event->getViewUrl()."' target='_blank' style='font-size: 18px;'>".lang('what').': ' . $event->getSubject(). "</a>" ?> 
	<br><br> <?php echo lang('date') . ': ' . $date?><br>
	<br />
	<?php
	
		echo lang('who').': '; ?><br><?php 
		echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		if ($invitation->getInvitationState() == 1)
			echo lang('user will attend to event', $from_user->getObjectName());
		else if ($invitation->getInvitationState() == 2)
			echo lang('user will not attend to event', $from_user->getObjectName());
		?>
		<br><br>
		<?php
		if(!count($assist)<1){
			echo lang('also confirmed attendance').':';	?><br><?php
			foreach ($assist as $k1=>$name1){
				echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');			
				echo ($name1);	?><br><?php	
			}		
			?>
			<br><br>
			<?php
		}
		if(!count($pending)<1){
			echo lang('awaiting confirmation').':';	?><br><?php		
			foreach ($pending as $k2=>$name2){
				echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
				echo ($name2);	?><br><?php
			}
			?>
			<br><br>
			<?php
		}
		if(!count($not_assist)<1){
			echo lang('rejected invitation').':';	?><br><?php
			foreach ($not_assist as $k3=>$name3){
				echo ('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
				echo ($name3);	?><br><?php
			}
			?>
			<br><br>
			<?php
		}
		
	?>
	<br><br>
	
	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>

