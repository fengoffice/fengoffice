<?php 
	require_javascript('og/modules/addMessageForm.js');
	$genid = gen_id();
	$object = $company;
	if($company->isNew()) { 
		$form_action = get_url('contact', 'add_company'); 
	} else {
		$form_action = $company->getEditUrl();
	}
	$renderContext = has_context_to_render($company->manager()->getObjectTypeId());
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
	$categories = array(); Hook::fire('object_edit_categories', $object, $categories);
?>
<form style="height:100%;background-color:white" class="internalForm" action="<?php echo $form_action ?>" method="post">


<div class="adminAddCompany">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $company->isNew() ? lang('new company') : lang('edit company') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('company[first_name]',  array_var($company_data, 'first_name'), array('class' => 'title', 'id' => $genid . 'clientFormName', 'placeholder' => lang('type name here'))) ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($company->isNew() ? lang('add company') : lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">
  <div id="<?php echo $genid?>tabs" class="edit-form-tabs">

	<ul id="<?php echo $genid?>tab_titles">
	
		<li><a href="#<?php echo $genid?>company_data"><?php echo lang('company data') ?></a></li>
		
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
	
	<?php 
	//company data tab
	render_company_data_tab($genid, $company, $renderContext, $company_data);
	?>
	
	<div id='<?php echo $genid ?>add_custom_properties_div' class="form-tab">
		<?php echo render_object_custom_properties($object, false) ?>
		<?php echo render_add_custom_properties($object); ?>
	</div>
	
	<div id="<?php echo $genid ?>add_subscribers_div" class="form-tab">
		<?php $subscriber_ids = array();
			if (!$object->isNew()) {
				$subscriber_ids = $object->getSubscriberIds();
			} else {
				$subscriber_ids[] = logged_user()->getId();
			} 
		?><input type="hidden" id="<?php echo $genid ?>subscribers_ids_hidden" value="<?php echo implode(',',$subscriber_ids)?>"/>
		<div id="<?php echo $genid ?>add_subscribers_content">
		<?php //echo render_add_subscribers($object, $genid); ?>
		</div>
	</div>
	
	

	<?php if($object->isNew() || $object->canLinkObject(logged_user())) { ?>
	<div style="display:none" id="<?php echo $genid ?>add_linked_objects_div" class="form-tab">
		<?php echo render_object_link_form($object) ?>
	</div>
	<?php } // if ?>
		
	
	<?php foreach ($categories as $category) { ?>
	<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
		<?php echo $category['content'] ?>
	</div>
	<?php } ?>
  </div>
<?php 
	if(!$company->isNew() && $company->isOwnerCompany()) { 
		echo submit_button(lang('save changes'));
	} else {
		echo submit_button($company->isNew() ? lang('add company') : lang('save changes'));
	}
?>
</div>
	
	
	
	
  
</div>

</form>

<script>
$(document).ready(function() {	
	Ext.get('<?php echo $genid ?>clientFormName').focus();

	$("#<?php echo $genid?>tabs").tabs();
});
</script>
