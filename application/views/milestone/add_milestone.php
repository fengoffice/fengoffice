<?php
  require_javascript('og/modules/addTaskForm.js'); 
  $genid = gen_id();
  $visible_cps = CustomProperties::countVisibleCustomPropertiesByObjectType($milestone->getObjectTypeId());
  $object = $milestone;
  $categories = array();
  Hook::fire('object_edit_categories', $object, $categories);
  
  // on submit functions
  if (array_var($_REQUEST, 'modal')) {
  	$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
  } else {
  	$on_submit = "return true";
  }
  
  $main_cp_count = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
  $other_cp_count = CustomProperties::countHiddenCustomPropertiesByObjectType($object->getObjectTypeId());
?>
<form class="add-milestone" id="<?php echo $genid?>submit-edit-form" onsubmit="<?php echo $on_submit?>" class="internalForm" action="<?php echo $milestone->isNew() ? get_url('milestone', 'add', array("copyId" => array_var($milestone_data, 'copyId'))) : $milestone->getEditUrl() ?>" method="post">

<div class="milestone">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle"><?php
		if ($milestone->isNew()) {
			if (array_var($milestone_data, 'is_template', false)) {
				echo lang('new milestone template');
			} else if (isset($milestone_task ) && $milestone_task instanceof ProjectTask) {
				echo lang('new milestone from template');
			} else {
				echo $object->getAddEditFormTitle();
			}
		} else {
			echo $object->getAddEditFormTitle();
		}
	?></div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('milestone[name]', array_var($milestone_data, 'name'), 
		array('class' => 'title', 'id' => $genid .'milestoneFormName', 'placeholder' => lang('type name here'))) ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : $object->getSubmitButtonFormTitle()) : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">
	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $milestone->isNew() ? '' : $milestone->getUpdatedOn()->getTimestamp() ?>">
	
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>add_milestone_data"><?php echo lang('details') ?></a></li>
			
			<?php if ($other_cp_count || config_option('use_object_properties')) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
			<li><a href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) {
					if (array_var($category, 'hidden')) continue;
				?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
	
		<div id="<?php echo $genid ?>add_milestone_data" class="editor-container form-tab">
		
			<div id="<?php echo $genid ?>add_milestone_select_context_div">
			<?php
				$listeners = array('on_selection_change' => 'og.reload_milestone_form_selectors()');
				if ($milestone->isNew()) {
					render_member_selectors($milestone->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
				} else {
					render_member_selectors($milestone->manager()->getObjectTypeId(), $genid, $milestone->getMemberIds(), array('listeners' => $listeners, 'object' => $object), null, null, false);
				} 
			?>
			<div class="clear"></div>
			</div>
			
			<div class="dataBlock">
			<?php echo label_tag(lang('due date'), null, true) ?>
			<?php echo pick_date_widget2('milestone[due_date_value]', array_var($milestone_data, 'due_date'),$genid, 90) ?>
			</div>

			<div id="<?php echo $genid ?>add_milestone_description_div" class="dataBlock">
				<label><?php echo lang("description")?>:</label>
				<?php echo textarea_field('milestone[description]', array_var($milestone_data, 'description'), array('class' => 'long', 'id' => $genid . 'milestoneFormDesc', 'tabindex' => '20')) ?>
			</div>
			
			<?php $null = null; Hook::fire('before_render_main_custom_properties', array('object' => $object), $null);?>
			
			<div class="main-custom-properties-div"><?php
				if ($main_cp_count) {
					echo render_object_custom_properties($object, false, null, 'visible_by_default');
				}
			?></div>
		</div>
	
  
	
		<div id="<?php echo $genid ?>add_reminders_div" class="form-tab" style="display:none;">
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
	
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab other-custom-properties-div">
			<?php echo render_object_custom_properties($milestone, false, null, 'other') ?>
			<?php echo render_add_custom_properties($milestone); ?>
		</div>
	
		<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
			<?php $subscriber_ids = array();
				if (!$milestone->isNew()) {
					$subscriber_ids = $milestone->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<input type="hidden" id="<?php echo $genid ?>original_subscribers" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content"><?php
				foreach ($subscriber_ids as $subid) {
					echo '<input type="hidden" name="subscribers[user_'.$subid.']" value="1"/>';
				} 
			?></div>
		</div>
	
	<?php if($milestone->isNew() || $milestone->canLinkObject(logged_user())) { ?>
		<div id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
			<?php echo render_object_link_form($milestone) ?>
		</div>
	<?php } // if ?>
	
	

	<?php echo input_field("milestone[is_template]", array_var($milestone_data, 'is_template', false), array("type" => "hidden")); ?>

		
		<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
			<?php echo $category['content'] ?>
		</div>
		<?php } ?>
		
	</div>
	<?php if (!array_var($_REQUEST, 'modal')) {
			echo submit_button($milestone->isNew() ? (array_var($milestone_data, 'is_template', false) ? lang('save template') : $object->getSubmitButtonFormTitle()) : lang('save changes'), 's', array('tabindex' => '20000'));
		} ?>
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

$(function() {
	og.reload_milestone_form_selectors();

	$("#<?php echo $genid?>tabs").tabs();
	
	Ext.get('<?php echo $genid ?>milestoneFormName').focus();
});
</script>