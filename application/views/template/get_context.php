<?php
	require_javascript("og/ObjectPicker.js");
	require_javascript("og/modules/addTemplate.js");
	require_javascript("og/DateField.js");
	
	
	$workspaces = active_projects();
	$genid = gen_id();
	$object = $cotemplate;
?>
<form  style='height:100%;background-color:white' class="internalForm" action="<?php echo get_url('template', 'instantiate', array('id' => $id))?>" method="post" enctype="multipart/form-data">

<div class="template">
<div class="coInputHeader">
<div class="coInputMainBlock">	
		
	<?php if (isset ($workspaces) && count($workspaces) > 0) { ?>
	<div id="<?php echo $genid ?>add_template_select_workspace_div">
	<fieldset>
		<legend><?php echo lang('template context')?></legend>
		<?php
			$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
			if ($cotemplate->isNew()) {
				render_member_selectors($cotemplate->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners)); 
			}else {
				render_member_selectors($cotemplate->manager()->getObjectTypeId(), $genid, $cotemplate->getMemberIds(), array('listeners' => $listeners)); 
			} 
		?>		
	</fieldset>
	</div>
	<?php } ?>
	
	<?php echo submit_button(lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '3')) ?>
</div>
</div>
</div>
</form>
