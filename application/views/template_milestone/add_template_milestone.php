<?php
  require_javascript('og/modules/addTaskForm.js'); 
  $genid = gen_id();
  $visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($milestone->getObjectTypeId());
  $projectMilestone = new ProjectMilestone();
?>
<form class="add-milestone" style='height:100%;background-color:white' class="internalForm" action="<?php echo $milestone->isNew() ? get_url('milestone', 'add', array("copyId" => array_var($milestone_data, 'copyId'))) : $milestone->getEditUrl() ?>" method="post">
<input id="<?php echo $genid?>template_milestone" type="hidden" name="template_milestone" value="<?php echo array_var($_GET, 'template_milestone', false)?>" />
<div class="milestone">
<div class="coInputHeader">
	<div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><table style="width:535px">
	<tr><td><?php
		if ($milestone->isNew()) {
			if (array_var($milestone_data, 'is_template', false)) {
				echo lang('new milestone template');
			} else if (isset($milestone_task ) && $milestone_task instanceof ProjectTask) {
				echo lang('new milestone from template');
			} else {
				echo lang('new milestone');
			}
		} else {
			echo lang('edit milestone');
		}
	?>
	</td><td style="text-align:right"><?php echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : lang('add milestone')) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '5')) ?></td></tr></table>
	</div>
	
	</div>
	<div>
	<?php echo label_tag(lang('name'), $genid. 'milestoneFormName', true) ?>
	<?php echo text_field('milestone[name]', array_var($milestone_data, 'name'), 
		array('class' => 'title', 'id' => $genid .'milestoneFormName', 'tabindex' => '1')) ?>
	</div>
	
	<?php $categories = array(); Hook::fire('object_edit_categories', $milestone, $categories); ?>
	
	<div style="padding-top:5px">
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_select_context_div',this)" ><?php echo lang('context') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_description_div', this)"><?php echo lang('description') ?></a>  
		<?php
		//milestones should not have this options and reminders. in the future this will be solved when they get changed from object to a dimension
		
		/*<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_milestone_options_div', this)"><?php echo lang('options') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_reminders_div',this)"><?php echo lang('object reminders') ?></a>  -
		<a href="#" class="option <?php echo $visible_cps>0 ? 'bold' : ''?>" onclick="og.toggleAndBolden('<?php echo $genid ?>add_custom_properties_div', this)"><?php echo lang('custom properties') ?></a> -
		<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_subscribers_div',this)"><?php echo lang('object subscribers') ?></a>*/
		?> 
				
		<?php if($milestone->isNew() || $milestone->canLinkObject(logged_user())) { ?> 
		  -	<a href="#" class="option" onclick="og.toggleAndBolden('<?php echo $genid ?>add_linked_objects_div',this)"><?php echo lang('linked objects') ?></a>
		<?php } ?>
		<?php foreach ($categories as $category) { ?>
		 - <a href="#" class="option" <?php if ($category['visible']) echo 'style="font-weight: bold"'; ?> onclick="og.toggleAndBolden('<?php echo $genid . $category['name'] ?>', this)"><?php echo lang($category['name'])?></a>
		<?php } ?>
	</div>
</div>
<div class="coInputSeparator"></div>
<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $milestone->isNew() ? '' : $milestone->getUpdatedOn()->getTimestamp() ?>">
	
	
	<div id="<?php echo $genid ?>add_milestone_select_context_div" style="display:none">
	<fieldset>
		<legend><?php echo lang('context') ?></legend>
		<?php
			$listeners = array('on_selection_change' => 'og.reload_milestone_form_selectors()');
			if ($milestone->isNew()) {
				render_member_selectors($projectMilestone->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners));
			} else {
				render_member_selectors($projectMilestone->getObjectTypeId(), $genid, $milestone->getMemberIds(), array('listeners' => $listeners));
			} 
		?>
		
	</fieldset>
	</div>

	
	<div id="<?php echo $genid ?>add_milestone_description_div" style="display:none">
	<fieldset>
		<legend><?php echo lang('description') ?></legend>
		<?php echo textarea_field('milestone[description]', array_var($milestone_data, 'description'), array('class' => 'long', 'id' => $genid . 'milestoneFormDesc', 'tabindex' => '20')) ?>
	</fieldset>
	</div>
  
	<div id="<?php echo $genid ?>add_milestone_options_div" style="display:none">
	<fieldset>
		<legend><?php echo lang('options') ?></legend>
		<div class="objectOption">
		<div class="optionLabel"><?php echo label_tag(lang('urgent milestone'), $genid . 'milestoneFormIsUrgent') ?></div>
		<div class="optionControl"><?php echo checkbox_field('milestone[is_urgent]', array_var($milestone_data, 'is_urgent', false), array('id' => $genid . 'milestoneFormIsUrgent', 'tabindex' => '45')) ?> </div>
		</div>
	</fieldset>
	</div>

	<div id="<?php echo $genid ?>add_reminders_div" style="display:none">
		<fieldset>
		<legend><?php echo lang('object reminders') ?></legend>
		<label><?php echo lang("due date")?>:</label>
		<div id="<?php echo $genid ?>add_reminders_content">
			<?php /*echo render_add_reminders($milestone, 'due_date', array(
				'type' => 'reminder_email',
				'duration' => 1,
				'duration_type' => 1440
			)); */?>
		</div>
		</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_custom_properties_div" style="<?php echo ($visible_cps > 0 ? "" : "display:none") ?>">
	<fieldset>
		<legend><?php echo lang('custom properties') ?></legend>
		<?php echo render_object_custom_properties($milestone, false) ?>
		<?php echo render_add_custom_properties($milestone); ?>
	</fieldset>
	</div>
	
	<div id="<?php echo $genid ?>add_subscribers_div" style="display:none">
		<fieldset>
			<legend><?php echo lang('object subscribers') ?></legend>
			<?php $subscriber_ids = array();
				if (!$projectMilestone->isNew()) {
					$subscriber_ids = $projectMilestone->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<input type="hidden" id="<?php echo $genid ?>original_subscribers" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content">
				<?php //echo render_add_subscribers($projectMilestone, $genid); ?>
			</div>
		</fieldset>
	</div>
	
	<?php if($milestone->isNew() || $milestone->canLinkObject(logged_user())) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div">
	<fieldset>
		<legend><?php echo lang('linked objects') ?></legend>
		<?php echo render_object_link_form($milestone) ?>
	</fieldset>	
	</div>
	<?php } // if ?>
	
	<div>
	<?php echo label_tag(lang('due date'), null, true) ?>
	<?php echo pick_date_widget2('milestone[due_date_value]', array_var($milestone_data, 'due_date'),$genid, 90) ?>
	</div>

	<?php echo input_field("milestone[is_template]", array_var($milestone_data, 'is_template', false), array("type" => "hidden")); ?>

	<?php foreach ($categories as $category) { ?>
	<div <?php if (!$category['visible']) echo 'style="display:none"' ?> id="<?php echo $genid . $category['name'] ?>">
	<fieldset>
		<legend><?php echo lang($category['name'])?><?php if ($category['required']) echo ' <span class="label_required">*</span>'; ?></legend>
		<?php echo $category['content'] ?>
	</fieldset>
	</div>
	<?php } ?>
	
	<?php echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : lang('add milestone')) : lang('save changes'), 's', array('tabindex' => '20000')) ?>
</div>
</div>
</form>

<script>

	og.reload_milestone_form_selectors = function() {
		var dimension_members_json = Ext.util.JSON.encode(member_selector['<?php echo $genid ?>'].sel_context);
		
		var uids = App.modules.addMessageForm.getCheckedUsers('<?php echo $genid ?>');
		Ext.get('<?php echo $genid ?>add_subscribers_content').load({
			url: og.getUrl('object', 'render_add_subscribers', {
				context: dimension_members_json,
				users: uids,
				genid: '<?php echo $genid ?>',
				otype: '<?php echo $milestone->manager()->getObjectTypeId()?>'
			}),
			scripts: true
		});
	
		var combo = Ext.getCmp('<?php echo $genid ?>taskFormAssignedToCombo');
		if (combo) {
			combo.collapse();
			combo.disable();
		}
		
		var parameters = {context: dimension_members_json};
		og.openLink(og.getUrl('task', 'allowed_users_to_assign', parameters), {callback: function(success, data){
			companies = data.companies;
			if (combo) {
				combo.reset();
				combo.store.removeAll();
				combo.store.loadData(ogTasks.buildAssignedToComboStore(companies));
				combo.setValue(0);
				combo.enable();
			}
		}});
	}
	
	og.reload_milestone_form_selectors();

	Ext.get('<?php echo $genid ?>milestoneFormName').focus();
</script>