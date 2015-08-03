<?php
	require_javascript('og/modules/addMessageForm.js');
	set_page_title($file->isNew() ? lang('add webpage') : lang('edit webpage'));
	$genid = gen_id();
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
	
	$categories = array();
	Hook::fire('object_edit_categories', $file, $categories);
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($file->getObjectTypeId()) > 0;
?>
<form id="<?php echo $genid ?>submit-edit-form" onsubmit="<?php echo $on_submit; ?>" class="internalForm" action="<?php echo $file->isNew() ? get_url('files', 'add_weblink') : $file->getEditUrl() ?>" method="post">


<div class="webpage">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $file->isNew() ? lang('new webpage') : lang('edit webpage') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('webpage[name]', array_var($file_data, 'name'), array('class' => 'title', 'tabindex' => '1', 'id' => $genid.'webpageFormTitle', 'placeholder' => lang('type name here'))); ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($file->isNew() ? lang('add webpage') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">

	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $file->isNew()? '' : $file->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >


	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>add_webpage_description_div"><?php echo lang('details') ?></a></li>
			
			<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php if($file->isNew() || $file->canLinkObject(logged_user())) { ?>
			<li><a href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) { ?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
		
	
	
		<div id="<?php echo $genid?>add_webpage_description_div" class="form-tab">
		
			<div id="<?php echo $genid ?>add_webpage_select_context_div">
				
				<?php 
				$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$file->manager()->getObjectTypeId().')');
				if ($file->isNew()) {
					render_member_selectors($file->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false); 
				} else {
					render_member_selectors($file->manager()->getObjectTypeId(), $genid, $file->getMemberIds(), array('listeners' => $listeners), null, null, false); 
				} ?>
			
			</div>
		
			<div class="dataBlock">
				<?php echo label_tag(lang('url'), 'webpageFormURL', true) ?>
				<?php echo text_field('webpage[url]', array_var($file_data, 'url'), array('class' => 'title', 'tabindex' => '50', 'id' => 'webpageFormURL')) ?>
			</div>
		
			<div class="dataBlock">
				<?php echo label_tag(lang('description'), 'webpageFormDesc') ?>
				<?php echo textarea_field('webpage[description]', array_var($file_data, 'description'), array('class' => 'long', 'id' => 'webpageFormDesc', 'tabindex' => '40')) ?>
			</div>
		</div>
	        
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab">
			<?php echo render_object_custom_properties($file, false) ?>
			<?php echo render_add_custom_properties($file); ?>
	    </div>
	        
		<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
			<?php $subscriber_ids = array();
				if (!$file->isNew()) {
					$subscriber_ids = $file->getSubscriberIds();
				} else {
					$subscriber_ids[] = logged_user()->getId();
				}
			?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
			<div id="<?php echo $genid ?>add_subscribers_content">
			</div>
		</div>
		
		<?php if($file->isNew() || $file->canLinkObject(logged_user())) { ?>
		<div style="display: none" id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
			<?php echo lang('linked objects') ?>
			<?php echo render_object_link_form($file) ?>
		</div>
		<?php } ?>
		
		<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
			<?php echo $category['content'] ?>
		</div>
		<?php } ?>
	
	</div>
	
	<?php echo submit_button($file->isNew() ? lang('add webpage') : lang('save changes'), 's', array('tabindex' => '20000')) ?>

</div>

</form>

<script>
$(function() {
	$("#<?php echo $genid?>tabs").tabs();
	$('#<?php echo $genid ?>webpageFormTitle').focus();
});
</script>
