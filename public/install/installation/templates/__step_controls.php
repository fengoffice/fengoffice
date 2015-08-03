<div id="installerControls">
<?php if($current_step->hasNextStep()) { ?>
	<?php if($current_step->hasPreviousStep()) { ?>
		<button type="button" onclick="location.href = '<?php echo $current_step->getPreviousStepUrl() ?>'; return true;">&laquo; Back</button>&nbsp;
	<?php } // if ?>
	<button type="submit" <?php if($current_step->getNextDisabled()) { ?>disabled="disabled"<?php } // if ?>>Next &raquo;</button>
<?php } else { ?>
	<?php if (!$all_ok) { ?>
		<button type="button" onclick="location.href = '<?php echo $current_step->getPreviousStepUrl() ?>'; return true;">&laquo; Back</button>&nbsp;
	<?php } ?>
	<?php if(isset($absolute_url)) { ?>
		<button type="button" onclick="location.href = '<?php echo $absolute_url ?>'">Finish</button>
	<?php } else {?>
		<button type="button" onclick="location.href = '../../index.php'">Finish</button>
	<?php } // if ?>
<?php } // if ?>
<?php if ($current_step instanceof ChecksStep) { ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<button type="button" onclick="location.href = 'index.php?step=2'">Try Again</button>
<?php } ?>
</div>