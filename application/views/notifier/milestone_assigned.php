<div style="font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<a href="<?php echo str_replace('&amp;', '&', $milestone_assigned->getViewUrl()) ?>" target="_blank" style="font-size: 18px;"><?php echo lang('milestone assigned', $milestone_assigned->getObjectName()) ?></a><br><br>

	<?php echo lang('members') ?>:
	<?php
		$members = $milestone->getMembers();
		foreach ($members as $member) { 
			echo $member->getName(); 
		}
	?><br><br>

	<?php if (isset($date)) {
		 	echo "<br>";
		 	echo lang('date') ?>: <?php echo $date ?><?php echo "<br>";
		}
	?>
	<br><br>

	<div style="color: #818283; font-style: italic; border-top: 2px solid #818283; padding-top: 2px; font-family: Verdana, Arial, sans-serif; font-size: 12px;">
	<?php echo lang('system notification email'); ?><br>
	<a href="<?php echo ROOT_URL; ?>" target="_blank" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;"><?php echo ROOT_URL; ?></a>
	</div>

</div>