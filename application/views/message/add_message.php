<?php
	require_javascript('og/modules/addMessageForm.js'); 
	$genid = gen_id();
	$object = $message;

	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories);
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.setDescription(); og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "og.setDescription();";
	}
	
	$main_cp_count = CustomProperties::countVisibleCustomPropertiesByObjectType($object->getObjectTypeId());
	$other_cp_count = CustomProperties::countHiddenCustomPropertiesByObjectType($object->getObjectTypeId());
?>
<form onsubmit="<?php echo $on_submit?>" class="add-message" id="<?php echo $genid ?>submit-edit-form" style='height:100%;background-color:white' action="<?php echo $message->isNew() ? get_url('message', 'add') : $message->getEditUrl() ?>" method="post" enctype="multipart/form-data" >
<div class="message">
	<div class="coInputHeader">

	<div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
				
			<?php echo $object->getAddEditFormTitle(); ?>
		</div>
	</div>

	<div>
		<div class="coInputName">
		<?php echo text_field('message[name]', array_var($message_data, 'name'), 
			array('id' => $genid . 'messageFormTitle', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
		</div>
			
		<div class="coInputButtons">
			<?php echo submit_button($object->getSubmitButtonFormTitle(),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
		</div>
		<div class="clear"></div>
	</div>
	</div>

	<div class="feng-forms">
		<div class="coInputMainBlock edit-member">
	
			<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="">
			<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>">
			<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo !$message->isNew() ? $message->getUpdatedOn()->getTimestamp() : '' ?>">
			
			<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
				<?php 
					$properties_html = null;
					Hook::fire('override_render_properties', [
						'object' => $message,
						'genid' => $genid,
						'visible_by_default' => true
					], $properties_html);
				?>
			
				<ul id="<?php echo $genid?>tab_titles">
				
					<li><a href="#<?php echo $genid?>add_message_text"><?php echo lang('details') ?></a></li>
					
					<?php if (is_null($properties_html)) {
						if ($other_cp_count || config_option('use_object_properties')) { ?>
					<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
					<?php } 
					} ?>
					
					<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
					
					<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
					<li><a href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
					<?php } ?>
					
					<?php foreach ($categories as $category) {
							if (array_var($category, 'hidden')) continue;
						?>
					<li><a href="#<?php echo $genid . $category['id'] ?>"><?php echo $category['name'] ?></a></li>
					<?php } ?>
				</ul>
				
				<div id="<?php echo $genid ?>add_message_text" class="editor-container form-tab">

					<?php
					if (!is_null($properties_html)) {
						echo_custom_properties_html($properties_html);

					} else {
					?>
					<div class="container">
						<div class="row">
							<div class="col">
								<?php
								$available_columns = $object->manager()->getColumnsAvailableInForms();

								// Set context variable for fallback rendering
								$is_hook_rendering = false;

								foreach ($available_columns as $column) {									
									$input_file = ROOT . '/application/views/message/form_inputs/' . $column . '.php';
									if (file_exists($input_file)) {
										echo '<div class="form-group">';
										include $input_file;
										echo '</div>';
									}
								}
								?>

								<?php $null = null; Hook::fire('before_render_main_custom_properties', array('object' => $object), $null);?>

								<div class="form-group">
									<div class="main-custom-properties-div">
										<?php
										if ($main_cp_count) {
											echo render_object_custom_properties($object, false, null, 'visible_by_default');
										}
										?>
									</div>
								</div>
							</div>
							<div class="col">
								<div id="<?php echo $genid ?>add_form_select_context_div">
									<?php
									$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
									if ($message->isNew()) {
										render_member_selectors($message->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
									} else {
										render_member_selectors($message->manager()->getObjectTypeId(), $genid, $message->getMemberIds(), array('listeners' => $listeners, 'object' => $object), null, null, false);
									}
									?>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
					<?php } // endif ?>
					<div class="clear"></div>
				</div>
				
				<?php if (is_null($properties_html)) {
					if ($other_cp_count || config_option('use_object_properties')) { ?>
				<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab other-custom-properties-div">
					<?php  echo render_object_custom_properties($object, false, null, 'other') ?>
					<?php  echo render_add_custom_properties($object); ?>
				</div>
				<?php }
				} ?>

				<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
					<div id="<?php echo $genid ?>add_subscribers_content">
						<?php 
						$subscriber_ids = array();
						if (!$object->isNew()) {
							$subscriber_ids = $object->getSubscriberIds();
						} else {
							$subscriber_ids[] = logged_user()->getId();
						}
						echo render_add_subscribers($object, $genid);
						?>
					</div>
					<input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
					<input type="hidden" id="<?php echo $genid ?>original_subscribers" value="<?php echo implode(',',$subscriber_ids)?>"/>
				</div>
				
				<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
				<div id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
					<?php echo render_object_link_form($object) ?>
				</div>
				<?php } // if ?>
				
				<?php foreach ($categories as $category) { ?>
				<div id="<?php echo $genid . $category['id'] ?>" class="form-tab">
					<?php echo $category['content'] ?>
				</div>
				<?php } ?>
			</div>

			
			<?php if (!array_var($_REQUEST, 'modal')) {
				echo submit_button($message->isNew() ? lang('add message') : lang('save changes'),'s', array('style'=>'margin-top:0px')); 
			}?>
		</div>
	</div>
</div>
</form>
<script>
$(function() {
	$("#<?php echo $genid?>tabs").tabs();
});
</script>