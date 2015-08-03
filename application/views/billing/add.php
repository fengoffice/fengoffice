<?php
	$genid = gen_id();
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-billing-form'); return false;";
	} else {
		$on_submit = "";
	}
?>
<form style='height:100%;background-color:white' class="internalForm" action="<?php echo $billing->isNew() ? get_url('billing', 'add') : $billing->getEditUrl() ?>" 
	method="post" enctype="multipart/form-data" id="<?php echo $genid."submit-billing-form"?>" onsubmit="<?php echo $on_submit?>">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $billing->isNew() ? lang('new billing category') : lang('edit billing category') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php echo text_field('billing[name]', array_var($billing_data, 'name'), 
		array('id' => $genid . 'billingFormName', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
	</div>
		
	<div class="coInputButtons">
		<?php echo submit_button($billing->isNew() ? lang('add billing category') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">

	<?php if (false) { ?>
	<div class="dataBlock">
	<?php echo label_tag(lang('report name'), $genid . 'billingFormReportName', false) ?>
	<?php echo text_field('billing[report_name]', array_var($billing_data, 'report_name'), 
		array('id' => $genid . 'billingFormReportName', 'tabindex' => '2')) ?>
	</div>
	<?php } ?>
	
	<div class="dataBlock">	
	<?php echo label_tag(lang('default hourly rates'), $genid . 'billingFormValue', true) ?>
	<?php echo text_field('billing[default_value]', array_var($billing_data, 'default_value'), 
		array('id' => $genid . 'billingFormValue', 'tabindex' => '3')) ?>
	</div>
	
	<div class="dataBlock">
	<?php echo label_tag(lang('description'), $genid . 'billingFormDescription', false) ?>
	<?php echo textarea_field('billing[description]', array_var($billing_data, 'description'), 
		array('id' => $genid . 'billingFormDescription', 'class' => 'comment', 'tabindex' => '4')) ?>
	</div>
	
	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($billing->isNew() ? lang('add billing category') : lang('save changes'),'s', array('tabindex' => '5')); 
	}?>
</div>

</form>

<script>
	Ext.get('<?php echo $genid ?>billingFormName').focus();
</script>
