<?php
	require_javascript("og/DateField.js");
	if (!isset($genid)) $genid = gen_id();
	
	/** @var ContentDataObject $object */
	if (!isset($object_type)) {
		$object_type = ObjectTypes::instance()->findById($object->getObjectTypeId());
	}
	$object_type_name = $object_type instanceof ObjectType ? $object_type->getObjectTypeName() : null;
	
	$form_title = $object_type_name;
	$new_object_text = $object->isNew() ? lang('save') : ($object_type_name ? lang('edit') . strtolower(" $object_type_name") : lang('new object'));

	$form_action = $object->isNew() ? $object->getAddObjectUrl() : $object->getEditUrl();
	
	// on submit functions
	$on_submit = "";
	if (array_var($_REQUEST, 'modal')) {
		$on_submit .= " og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit .= " return true;";
	}

	$main_properties = array();
    Hook::fire('render_member_properties', array('object' => $object, 'visible_by_default' => true, 'genid' => $genid), $main_properties);
?>
<style>

</style>
<form 
	id="<?php echo $genid ?>submit-edit-form" 
	class="edit-object" 
	method="post" enctype="multipart/form-data"  
	action="<?php echo $form_action ?>"
	onsubmit="<?php echo $on_submit ?>"
>
	<input type="hidden" name="genid" value="<?php echo $genid?>" id="genid" />
	
	<div class="coInputHeader">
	
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $form_title ?></div>
	  </div>
	
	  <div>
		<div class="coInputName">
			<?php echo text_field('object[name]', $object->getName(), array('id' => $genid . '-name', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
		</div>
			
		<div class="coInputButtons">
			<?php echo submit_button($object == null || $object->isNew() ? $new_object_text : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
	
	
	<div class="coInputMainBlock">
	
		<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
			<ul id="<?php echo $genid?>tab_titles">
			
				<li><a href="#<?php echo $genid?>object_data"><?php echo lang('details') ?></a></li>
				
			</ul>
			
			<div id="<?php echo $genid?>object_data" class="form-tab">
			<?php 

				// Render the custom properties using groups if available
				echo_custom_properties_html($main_properties);
				
			?>
			</div>
			
			<div class="x-clear"></div>
		
		</div>

	</div>

	<?php 
	if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($object == null || $object->isNew() ? $new_object_text : lang('save changes'),'s',array('style'=>'margin-top:0px;'));
	}
	?>
</form>

<script>
	var genid = '<?php echo $genid?>';
	$(function() {
		$("#<?php echo $genid?>tabs").tabs();
		Ext.get('<?php echo $genid ?>-name').focus();
	});
</script>
