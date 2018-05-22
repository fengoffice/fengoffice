<?php 
require_javascript('og/modules/linkToObjectForm.js');
if (!isset($genid)) $genid = gen_id();

if ((!is_array($objects) || count($objects) == 0))  {
	echo '<div class="desc no_linked_objects_desc">' . lang('there are no linked objects yet') . '</div><br />';
}
?>
<style>
.linked-objects-container{
	max-height:250px;
	overflow-y: auto;
	padding: 5px 0;
}

.linked-objects-container .ico-contact{
	width: 500px !important;
}

.linked-objects-container .add-linked-object{
	display: block;
    margin-bottom: 15px;
}

.linked-objects-container .og-add-template-object{
	margin-top: 5px;
}

.linked-objects-container .odd{
	background-color: #f8f8e8 !important;
}

</style>

<div id="contene" class="linked-objects-container">
	<a id="<?php echo $genid ?>before" class="add-linked-object" href="#" onclick="App.modules.linkToObjectForm.pickObject(this)"><span class="action-ico ico-open-link"><?php echo lang('link object') ?></span></a>
<?php 
if (is_array($objects)) {
	$cls = "";
	$count = 0;
	foreach ($objects as $o) {
		if (!$o instanceof ContentDataObject || !$o->canLinkObject(logged_user())) continue;
		$cls = $cls == "" ? " odd" : "";
		$count++;
?>
	<div class="og-add-template-object ico-<?php echo $o->getObjectTypeName() . $cls ?>">
		<input type="hidden" name="linked_objects[<?php echo $count?>]" value="<?php echo $o->getId()?>" />
		<span class="name"><?php echo $o->getObjectName()?></span>
		<a href="#" onclick="App.modules.linkToObjectForm.removeObject(this.parentNode)" class="removeDiv" style="display:block"><?php echo lang('remove') ?></a>
	</div>
<?php
	}
}
?>
</div>
