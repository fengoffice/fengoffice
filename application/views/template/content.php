<?php
$description = array_var($variables, "description", "");
$objects = array_var($variables, "objects", "");
if ($description != "") {
?>
<span class="bold"><?php echo lang("description") ?>:</span><br><?php echo clean($description) ?><br><br>
<?php } ?>
<span class="bold"><?php echo lang("objects in template") ?>:</span><br>
<?php
if (is_array($objects) && count($objects)) {
	$isAlt = false;
	foreach ($objects as $o) {
?>
	<div class="og-add-template-object ico-<?php echo $o->getObjectTypeName() ?><?php if ($isAlt) echo " odd" ?>">
		<a class=" internalLink name" href="<?php echo $o->getViewUrl() ?>">
			<?php echo clean($o->getObjectName()) ?>
		</a>
	</div>
<?php
		$isAlt = !$isAlt;	
	}
} else {
	echo "<i>".lang("no objects in template")."</i>";
}
?><br>