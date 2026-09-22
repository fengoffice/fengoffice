<?php
	require_javascript("og/ObjectPicker.js");
	require_javascript("og/modules/addTemplate.js");
	require_javascript("og/DateField.js");
	
	$genid = gen_id();
	$object = $cotemplate;
	
	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories);

	if (!isset($template_data) || !is_array($template_data)) {
		$template_data = array();
	}
	if (!isset($task_template_category_select_options) || !is_array($task_template_category_select_options)) {
		$task_template_category_select_options = array(option_tag(lang('task templates uncategorized'), 0, array('selected' => 'selected')));
	}
?>
<form id="templateForm" style='height:100%;background-color:white' class="internalForm" action="<?php echo $cotemplate->isNew() ? get_url('template', 'add') : $cotemplate->getEditUrl() ?>" method="post" enctype="multipart/form-data" 
onsubmit="return og.submitTemplateForm();">

<div id = "templateConteiner" class="template">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px"><tr><td><?php echo $cotemplate->isNew() ? lang('new template') : lang('edit template') ?>
	</td><td style="text-align:right"><?php echo submit_button($cotemplate->isNew() ? lang('add template') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'class'=>'blue')) ?></td></tr></table>
	</div>
</div>
	<div>
	<?php echo label_tag(lang('name'), $genid . 'templateFormName', true) ?>
	<?php echo text_field('template[name]', array_var($template_data, 'name'), 
		array('id' => $genid . 'templateFormName', 'class' => 'name long', 'tabindex' => '1')) ?>
	</div>
	<div class="dataBlock" style="margin-top:8px;">
		<?php echo label_tag(lang('task template category'), $genid . 'task_template_category_id') ?>
		<?php echo select_box('template[task_template_category_id]', $task_template_category_select_options, array('id' => $genid . 'task_template_category_id', 'tabindex' => '2')) ?>
		<span style="margin-left:12px;"><a class="internalLink" href="<?php echo get_url('template', 'manage_task_template_categories') ?>"><?php echo lang('manage task template categories') ?></a></span>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">	
	

	<div>
		<fieldset>
		<legend><?php echo label_tag(lang('description'), 'templateFormDescription', false) ?></legend>
		
		<?php echo editor_widget('template[description]', array_var($template_data, 'description'), 
			array('id' => $genid . 'templateFormDescription', 'class' => 'long', 'tabindex' => '3')) ?>
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_template_objects_div">
		<fieldset>
			<legend><?php echo lang('tasks')?></legend>
			<br/>
			<div id="<?php echo $genid ?>template_tasks_div">
				
			</div>
			<br/>
			<div class="db-ico ico-task" style="float: left;"></div>
			
			<?php $add_task_link_js = "og.render_modal_form('', {c:'task', a:'add_task', params: {template_task:1, template_id:". ($cotemplate->getId()? $cotemplate->getId():0) ."}});"?>
			<a id="<?php echo $genid ?>add_template_task" class='internalLink dashboard-link' href="#" onclick="<?php echo $add_task_link_js ?>">
		<?php echo lang('add a new task to this template') ?></a>
		 
		 <?php if (config_option('use_milestones')){ ?>	
			<br/>
		
	 		<div class="db-ico ico-milestone" style="float: left;"></div>
	 		
	 		<?php $add_milestone_link_js = "og.render_modal_form('', {c:'milestone', a:'add', params: {template_milestone:1, template_id:". ($cotemplate->getId()? $cotemplate->getId():0) .", use_ajx:1}});"?>
			<a id="<?php echo $genid ?>add_template_milestone" class='internalLink dashboard-link' href="#" onclick="<?php echo $add_milestone_link_js ?>">
			<?php echo lang('add a new milestone to this template') ?></a>
			
				<a style="display:none;" id="<?php echo $genid ?>add_template_milestone" class='internalLink dashboard-link' href="#" onmousedown="og.openLink(og.getUrl('milestone', 'add', {template_milestone:1, template_id:<?php echo $cotemplate->getId()? $cotemplate->getId():0 ?>}), {caller:'new_task_template'});" onclick="Ext.getCmp('tabs-panel').activate('new_task_template');">
		 	<?php echo lang('add a new milestone to this template') ?></a>
		 <?php }?>
		
		
			
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_template_parameters_div">
		<fieldset><legend><?php echo lang("parameters")?></legend>
			<a id="<?php echo $genid ?>params" href="#" onclick="og.promptAddParameter(this, 0)"><?php echo lang('add a variable to this template') ?></a>
		</fieldset>
	</div>
	<?php
		if (isset($add_to) && $add_to) {
			echo input_field("add_to", "true", array("type"=>"hidden"));
		}
		if (!$cotemplate->isNew()) {
			$template_updated_on = $cotemplate->getUpdatedOn();
	?>
	<input type="hidden" id="<?php echo $genid ?>template-loaded-updated-on" name="template_loaded_updated_on" value="<?php echo $template_updated_on instanceof DateTimeValue ? $template_updated_on->getTimestamp() : '' ?>" />
	<input type="hidden" name="template_loaded_object_count" value="<?php echo count($cotemplate->getObjects()) ?>" />
	<?php } ?>
	
	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['id'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php echo submit_button($cotemplate->isNew() ? lang('add template') : lang('save changes'),'s',
		array('style'=>'margin-top:0px', 'tabindex' => '4')) ?>
</div>
</div>
</form>

<script>
		og.resetTemplateFormSubmitState();
		og.actual_template_id = <?php echo $cotemplate->getId()? $cotemplate->getId():'0' ?>;
		og.loadTemplateVars();
		Ext.get('<?php echo $genid ?>templateFormName').focus();
	<?php
	
	
	if (isset($parameters) && is_array($parameters)) {
		foreach ($parameters as $param) {
			$param_def_val = str_replace(array('{{','}}'), '', $param->getDefaultValue());
	?>
		og.addParameterToTemplate(document.getElementById('<?php echo $genid ?>params'), '<?php echo escape_character($param->getName()) ?>','<?php echo $param->getType() ?>','<?php echo $param_def_val ?>'); 
	<?php }
	}?>

	og.add_template_input_divs = [];
	var inputs = document.getElementById('<?php echo $genid ?>add_template_objects_div').getElementsByTagName('input');
	for (var i=0; i < inputs.length; i++) {
		if(inputs[i].className == 'objectID') {
			og.add_template_input_divs[inputs[i].value] = inputs[i].parentNode.parentNode.id;
		}
	}

	for (x=0; x<og.templateObjects.length; x++) {
		var tobj = og.templateObjects[x];
		if (tobj.type == 'task' && tobj.object_id) og.drawTemplateObjectMilestonesCombo(Ext.get(og.add_template_input_divs[tobj.object_id]).dom, tobj);
	}

	og.redrawTemplateObjectsLists = function(data){
		obj = data.object ? data.object : data;
		if(obj.type == "template_task"){
			og.redrawTemplateTaskList(obj);
		}else if(data.type == "template_milestone"){
			og.redrawTemplateMilestoneList(obj);
		}
		
	}

	og.redrawTemplateTaskList = function(data){
		if(data.action == "edit"){
			//refresh name			
			$('#template-object-name'+data.id).text(data.name);
			$('#template-object-name'+data.id).parent().children(".ico-recurrent").remove();
			if (parseInt(data.is_repetitive) > 0) {
				$('#template-object-name'+data.id).parent().append('&nbsp;<span class="link-ico ico-recurrent"></span>');
			}

			//refresh milestone
			if($('#subTasksDiv'+data.milestone_id).length && $('#subTasksDiv'+data.milestone_id).has($('#objectDiv'+data.id)).length == 0){
				var div = $('#objectDiv'+data.id);
				$('#objectDiv'+data.id).remove();
				$('#subTasksDiv'+data.milestone_id).append(div);
				$('#subtasksExpander'+data.milestone_id).show();
			}

			//refresh parent
			if($('#subTasksDiv'+data.parent_id).length && $('#subTasksDiv'+data.parent_id).has($('#objectDiv'+data.id)).length == 0){
				var div = $('#objectDiv'+data.id);
				$('#objectDiv'+data.id).remove();
				$('#subTasksDiv'+data.parent_id).append(div);
				$('#subtasksExpander'+data.parent_id).show();
			}

			//if not have parent or milestone
			if(data.milestone_id == 0 && data.parent_id == 0){
				var div = $('#objectDiv'+data.id);
				// remove and add again only if it is not already in the root of the list
				if (!$(div).hasClass('root')) {
					$('#objectDiv'+data.id).remove();
					$('#<?php echo $genid ?>template_tasks_div').append(div);				
				}
			}
					
		}else{
			if(data.milestone_id && !data.parent_id){
				og.addObjectToTemplate(('subTasksDiv'+data.milestone_id), data, true);
				$('#subtasksExpander'+data.milestone_id).show();
			}else if(data.parent_id){
				og.addObjectToTemplate(('subTasksDiv'+data.parent_id), data, true);
				$('#subtasksExpander'+data.parent_id).show();
			}else{
				og.addObjectToTemplate(('<?php echo $genid ?>template_tasks_div'), data, true);
			}
		}
		
// Ordenar tareas root
const rootContainer = document.getElementById('<?php echo $genid ?>template_tasks_div');
if (rootContainer) {
	const rootItems = Array.from(rootContainer.children)
		.filter(child => child.classList.contains('template-object-div'));

	rootItems.sort((a, b) => {
		const linkA = a.querySelector('a.internalLink');
		const linkB = b.querySelector('a.internalLink');
		if (!linkA || !linkB) return 0;
		return linkA.textContent.trim().localeCompare(linkB.textContent.trim(), 'es', { sensitivity: 'base' });
	});

	rootItems.forEach(item => rootContainer.appendChild(item));
}

// Ordenar cada bloque de subtasks
document.querySelectorAll('.template-subtasks-div').forEach(container => {

	const items = Array.from(container.children)
		.filter(child => child.classList.contains('template-object-div'));

	items.sort((a, b) => {
		const linkA = a.querySelector('a.internalLink');
		const linkB = b.querySelector('a.internalLink');
		if (!linkA || !linkB) return 0;
		return linkA.textContent.trim().localeCompare(linkB.textContent.trim(), 'es', { sensitivity: 'base' });
	});

	items.forEach(item => container.appendChild(item));
});

	}

	og.redrawTemplateMilestoneList = function(data){
		if(data.action == "edit"){
			//refresh name			
			$('#template-object-name'+data.id).text(data.name);					
		}else{
			og.addObjectToTemplate(('<?php echo $genid ?>template_tasks_div'), data, true);
		}
	}

	
	og.template_obj_properties = {};
	<?php // load template object properties
		$template_task_props = TemplateTasks::instance()->getTemplateObjectProperties();
		$template_mile_props = TemplateMilestones::instance()->getTemplateObjectProperties();
		
		foreach ($template_task_props as $prop) {
			?>
			var tt_ot = '<?php echo TemplateTasks::instance()->getObjectTypeId() ?>';
			if (!og.template_obj_properties[tt_ot]) og.template_obj_properties[tt_ot] = {properties: []};

 			og.template_obj_properties[tt_ot].properties.push({
				id: '<?php echo $prop['id'] ?>',
				name: '<?php echo $prop['name'] ?>',
				type: '<?php echo $prop['type'] ?>'
 			});
			
			<?php 
		}
				
		foreach ($template_mile_props as $prop) {
			?>
			var tm_ot = '<?php echo TemplateMilestones::instance()->getObjectTypeId() ?>';
			if (!og.template_obj_properties[tm_ot]) og.template_obj_properties[tm_ot] = {properties: []};

			og.template_obj_properties[tm_ot].properties.push({
				id: '<?php echo $prop['id'] ?>',
				name: '<?php echo lang('field ProjectTasks '.$prop['id']) ?>',
				type: '<?php echo $prop['type'] ?>'
			});
			<?php 
		}
	?>

	og.template_allowed_users_to_assign = null;
	<?php
		$task_controller = new TaskController();
		$_GET['for_template_var'] = 1;
		$companies_to_assign = $task_controller->allowed_users_to_assign();
		
		if ($companies_to_assign && array_var($companies_to_assign, 'companies')) { ?>
			og.template_allowed_users_to_assign = Ext.util.JSON.decode('<?php echo escape_character(json_encode($companies_to_assign['companies']))?>');
	<?php 
		} 
		
	if (is_array($objects)) {	
		foreach ($objects as $o) {	?>			
			og.redrawTemplateObjectsLists(<?php echo json_encode($o)?>);			
			<?php 
			if(isset($object_properties) && is_array($object_properties)){
				
				$oid = $o["object_id"];
				if(isset($object_properties[$oid])){
					foreach($object_properties[$oid] as $objProp){
						$property = $objProp->getProperty();
						
						$value =  str_replace("\n","\\n",$objProp->getValue());
						$value =  escape_character($value);
					?>
					og.addTemplateObjectProperty(<?php echo $oid ?>, <?php echo $oid ?>, '<?php echo $property ?>', '<?php echo $value ?>', '<?php echo $o['object_type_id']?>');
			  <?php }
				}
			}
		}
	}
	?>
			
	var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>templateFormName'));
	
	$( "#<?php echo $genid ?>templateFormName" ).change(function() {
		Ext.getCmp(p.id).setPreventClose(true);
	});
	$( "#<?php echo $genid ?>templateFormDescription" ).change(function() {
		Ext.getCmp(p.id).setPreventClose(true);
	});
	$('#<?php echo $genid ?>template_tasks_div').bind("DOMSubtreeModified",function(){
		  Ext.getCmp(p.id).setPreventClose(true);
		});
	$('#<?php echo $genid ?>add_template_parameters_div').bind("DOMSubtreeModified",function(){
		  Ext.getCmp(p.id).setPreventClose(true);
	});
	$("#templateForm" ).submit(function( event ) {
		Ext.getCmp(p.id).setPreventClose(false);
	});
			
	og.editTempObj = function(id, type){
		if(type == "template_task"){
			og.render_modal_form('', {c:'task', a:'edit_task', params: {id: id, template_task:1, template_id:<?php echo $cotemplate->getId() ? $cotemplate->getId():0 ?>}});
		}else if(type == "template_milestone"){
			og.render_modal_form('', {c:'milestone', a:'edit', params: {id: id, template_milestone:1, template_id:<?php echo $cotemplate->getId() ? $cotemplate->getId():0 ?>, use_ajx:1}});
			//og.openLink(og.getUrl('milestone', 'edit', {id: id, template_milestone:1}), {caller:'new_task_template'});
		}
	}

	og.viewTempObj = function(id, type){
		if(type == "template_task"){
			og.openLink(og.getUrl('task', 'view', {id: id, template_task:1}), {caller:'new_task_template'});
		}else if(type == "template_milestone"){
			og.openLink(og.getUrl('milestone', 'edit', {id: id, template_milestone:1}), {caller:'new_task_template'});
		}
	}

	// Serializes property/object inputs into a single JSON field for POST.
	// Inputs are kept in the DOM so a second save on the same tab cannot lose objects[].
	og.submitTemplateForm = function() {
		if (og._templateFormSubmitting) {
			return false;
		}

		if ($("#<?php echo $genid; ?>templateFormName").val() == '') {
			og.err(lang('template name required'));
			return false;
		}

		if (!og.templateConfirmSubmit('<?php echo $genid ?>') || !og.handleMemberChooserSubmit('<?php echo $genid; ?>', <?php echo $cotemplate->manager()->getObjectTypeId() ?>)) {
			return false;
		}

		og._templateFormSubmitting = true;
		var $form = $('#templateForm');
		$form.find('input[type=submit], button[type=submit]').prop('disabled', true);

		var all_prop_inputs = {};
		var $serializedInputs = $form.find('[name^="prop"], [name^="objectProperties"], [name^="objects"]');
		
		$serializedInputs.each(function(){
			all_prop_inputs[$(this).attr('name')] = $(this).val();
		});

		og.ensureTemplateObjectsInPropInputs(all_prop_inputs);

		// Disable (don't remove) individual inputs so they stay in the DOM for a second save
		// but are excluded from the POST — same intent as the old .remove(), without breaking the form.
		$serializedInputs.prop('disabled', true);

		// Ensure that the properties added by advanced_templates plugin are sent with the id not the text
		if (og.advanced_templates) {
			for (var key in all_prop_inputs) {
				let value = all_prop_inputs[key];
				let text_value = og.advanced_templates.replace_string_param_text_with_id(value);
				
				if (text_value != value) {
					all_prop_inputs[key] = text_value;
				}
			}
		}
		
		$form.find('#all_prop_inputs').remove();
		$('<input>').attr({
		    type: 'hidden',
		    id: 'all_prop_inputs',
		    name: 'all_prop_inputs',
		    value: encodeURIComponent(JSON.stringify(all_prop_inputs)),
		}).appendTo($form);

		if (og._templateFormSubmitTimeout) {
			clearTimeout(og._templateFormSubmitTimeout);
		}
		og._templateFormSubmitTimeout = setTimeout(function() {
			og.resetTemplateFormSubmitState();
		}, 60000);
		
		return true;
	}

	if (!og._templateFormSubmitBound) {
		og._templateFormSubmitBound = true;
		$(document).ajaxComplete(function(event, xhr, settings) {
			var url = (settings && settings.url) ? settings.url : '';
			if (url.indexOf('c=template') === -1) {
				return;
			}
			if (url.indexOf('a=edit') === -1 && url.indexOf('a=add') === -1) {
				return;
			}
			og.resetTemplateFormSubmitState();
		});
	}
</script>