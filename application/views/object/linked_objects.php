<?php 
require_javascript('og/modules/linkToObjectForm.js');
if (!isset($genid)) $genid = gen_id();

if ((!is_array($objects) || count($objects) == 0))  {
	echo '<div class="desc no_linked_objects_desc">' . lang('there are no linked objects yet') . '</div><br />';
}
?>

<div id="contene" class="linked-objects-container">
	<a id="<?php echo $genid ?>before" 
	   class="btn btn-primary-50 btn-sm" 
	   href="#" 
	   onclick="App.modules.linkToObjectForm.pickObject(this)">
	   <i class="icon-link"></i> <?php echo lang('link object') ?>
	</a>
<?php 
if (is_array($objects)) {
	$count = 0;
	foreach ($objects as $o) {
		if (!$o instanceof ContentDataObject || !$o->canLinkObject(logged_user())) continue;
		$count++;
?>
    <div class="selected-object-wrapper og-add-template-object">
        <input type="hidden" name="linked_objects[<?php echo $count-1?>]" value="<?php echo $o->getId()?>" />
        
        <div class="object-badge">
            <i class="icon-<?php echo $o->getObjectTypeName() ?>"></i>
            
            <span class="name"><?php echo $o->getObjectName()?></span>
            
            <a href="#" 
               onclick="App.modules.linkToObjectForm.removeObject(this.parentNode.parentNode)" 
               class="object-remove-btn" 
               title="<?php echo lang('remove') ?>">
                <i class="icon-circle-x"></i>
            </a>
        </div>
    </div>
<?php
	}
}
?>
</div>
