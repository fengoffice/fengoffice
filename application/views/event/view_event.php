<?php

$duration = $variables["duration"];
$desc = $variables["desc"];
$attendance = isset($variables["attendance"]) ? $variables["attendance"] : null;
$otherInvitationsTable = isset($variables["other_invitations"]) ? $variables["other_invitations"] : null;

if ($attendance != null) {
	echo '<br>' . $attendance;
}
?>
<br><b><?php echo lang('CAL_DURATION')?>:</b> <?php echo $duration?><br>
<?php if ($desc) { ?>
<fieldset>
<legend><?php echo lang('CAL_DESCRIPTION')?></legend>
<?php echo $desc; ?>
</fieldset>
<?php } ?>
<?php if ($otherInvitationsTable != null) { ?>
<fieldset>
<legend><?php echo lang('invitations') ?></legend>
<?php echo $otherInvitationsTable; ?>
</fieldset>
<?php } ?>