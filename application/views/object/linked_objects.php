<?php 
require_javascript('og/modules/linkToObjectForm.js');
if (!isset($genid)) $genid = gen_id();

if ((!is_array($objects) || count($objects) == 0))  {
	echo '<div class="desc no_linked_objects_desc">' . lang('there are no linked objects yet') . '</div><br />';
}
?>
<div id="contene" class="linked-objects-container">
	<a id="<?php echo $genid ?>before" class="add-linked-object" href="#" onclick="App.modules.linkToObjectForm.pickObject(this)"><span class="action-ico ico-open-link"><?php echo lang('link object') ?></span></a>
</div>

<style>
.linked-objects-container{
	max-height:250px;
	overflow-y: auto;
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

<script>
<?php
if (is_array($objects)) {
	foreach ($objects as $o) {
		if (!$o instanceof ContentDataObject) continue;
?>
App.modules.linkToObjectForm.addObject(document.getElementById('<?php echo $genid ?>before'), {
	'object_id': <?php echo $o->getId() ?>,
	'type': '<?php echo $o->getObjectTypeName() ?>',
	'name': <?php echo json_encode($o->getObjectName()) ?>
});
<?php
	}
}
?>
</script>