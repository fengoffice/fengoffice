<?php
	require_javascript('og/modules/addMessageForm.js');
	set_page_title($webpage->isNew() ? lang('add webpage') : lang('edit webpage'));
	$genid = gen_id();
	$object = $webpage;

	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
	
	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories);
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
?>
<form id="<?php echo $genid ?>submit-edit-form" onsubmit="<?php echo $on_submit?>" class="internalForm"
	action="<?php echo $webpage->isNew() ? get_url('webpage', 'add') : $webpage->getEditUrl() ?>" method="post">

<div class="webpage">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow" id="<?php echo $genid?>_title_label">
	<div class="coInputTitle">
		<?php echo $webpage->isNew() ? lang('new webpage') : lang('edit webpage') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
		<?php echo text_field('webpage[name]', array_var($webpage_data, 'name'), array('class' => 'title', 'id' => $genid.'webpageFormTitle')) ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
  
  <div class="coInputHeaderUpperRow" id="<?php echo $genid?>_url_label">
	<div class="coInputTitle"><?php echo lang('url').' *' ?></div>
  </div>
  <div class="coInputName"><?php
  	echo text_field('webpage[url]', array_var($webpage_data, 'url'), array('class' => 'title', 'id' => 'webpageFormURL'));
?></div>
  <div class="clear"></div>
  
  
</div>

<div class="coInputMainBlock">

	<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $webpage->isNew()? '' : $webpage->getUpdatedOn()->getTimestamp() ?>">
	<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
	<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >

  <div id="<?php echo $genid?>tabs" class="edit-form-tabs">

	<ul id="<?php echo $genid?>tab_titles">
	
		<li><a href="#<?php echo $genid?>add_webpage_data_div"><?php echo lang('text') ?></a></li>
		
		<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
		<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
		<?php } ?>
		
		<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
		
		<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
		<li><a href="#<?php echo $genid?>add_linked_objects_div"><?php echo lang('linked objects') ?></a></li>
		<?php } ?>
		
		<?php foreach ($categories as $category) { ?>
		<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
		<?php } ?>
	</ul>

	<div id="<?php echo $genid?>add_webpage_data_div" class="form-tab">
		<div id="<?php echo $genid ?>add_webpage_select_context_div">
		<?php 
		$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$webpage->manager()->getObjectTypeId().')');
		if ($webpage->isNew()) {
			render_member_selectors($webpage->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false); 
		} else {
			render_member_selectors($webpage->manager()->getObjectTypeId(), $genid, $webpage->getMemberIds(), array('listeners' => $listeners), null, null, false); 
		} ?>
		</div>
		
		<div class="dataBlock">
		<?php echo label_tag(lang('description'), 'webpageFormDesc') ?>
		<?php echo textarea_field('webpage[description]', array_var($webpage_data, 'description'), array('class' => 'long', 'id' => 'webpageFormDesc')) ?>
		</div>
	</div>
        
	<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab">
		<?php echo render_object_custom_properties($webpage, false) ?>
		<?php echo render_add_custom_properties($webpage); ?>
	</div>
        
	<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
		<?php $subscriber_ids = array();
			if (!$webpage->isNew()) {
				$subscriber_ids = $webpage->getSubscriberIds();
			} else {
				$subscriber_ids[] = logged_user()->getId();
			}
		?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
		<div id="<?php echo $genid ?>add_subscribers_content">
			<?php //echo render_add_subscribers($webpage, $genid); ?>
		</div>
	</div>
	
	<?php if($webpage->isNew() || $webpage->canLinkObject(logged_user())) { ?>
	<div id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
		<?php echo render_object_link_form($webpage) ?>
	</div>
	<?php } ?>
	
	<?php foreach ($categories as $category) { ?>
	<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
		<?php echo $category['content'] ?>
	</div>
	<?php } ?>
  </div>
	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($webpage->isNew() ? lang('add webpage') : lang('save changes')); 
	}?>

</div>

</div>
</form>

<script>
	$(function() {
		$("#<?php echo $genid?>tabs").tabs();
		$('#<?php echo $genid ?>webpageFormTitle').focus();

		setTimeout(function() {
            var w = $(".simplemodal-data .coInputHeader .coInputName input.title").width();
        	$(".simplemodal-data .coInputHeader .coInputName input.title").css('width', (2*w)+'px');

        	var wl1 = $("#<?php echo $genid?>_title_label .coInputTitle").width();
        	var wl2 = $("#<?php echo $genid?>_url_label .coInputTitle").width();
        	var wl3 = wl1 > wl2 ? wl1 : wl2;
        	$("#<?php echo $genid?>_title_label .coInputTitle").css('width', wl3+'px');
        	$("#<?php echo $genid?>_url_label .coInputTitle").css('width', wl3+'px');
        	
        	$(".simplemodal-data .coInputHeader .coInputName").css('white-space', 'nowrap');
        }, 500);
	});
</script>