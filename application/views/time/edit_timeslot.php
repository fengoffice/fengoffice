<?php
	/* @var $timeslot Timeslot */
	$genid = gen_id();
	$object = $timeslot;
	
	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories);
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
	
	$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
?>
<form onsubmit="<?php echo $on_submit?>" class="add-timeslot" id="<?php echo $genid ?>submit-edit-form" action="<?php echo $timeslot->isNew() ? get_url('time', 'add_timeslot') : get_url('time', 'edit_timeslot', array('id' => $timeslot->getId())); ?>" method="post" enctype="multipart/form-data">
<div class="timeslot">

<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo $timeslot->isNew() ? lang('new timeslot') : lang('edit timeslot') ?>
	</div>
  </div>

  <div>
	<div class="coInputName">
	<?php //echo text_field('timeslot[name]', $object->getObjectName(), array('id' => $genid . 'timeslotFormTitle', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
	</div>
		
	<div class="coInputButtons" style="float:right;">
		<?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
	</div>
	<div class="clear"></div>
  </div>
</div>

<div class="coInputMainBlock">
	
	<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>add_timeslot_details"><?php echo lang('details') ?></a></li>
			<li><a href="#<?php echo $genid?>add_timeslot_related_to"><?php echo lang('related to') ?></a></li>
			
			<?php if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
			<li><a href="#<?php echo $genid?>add_timeslot_billing"><?php echo lang('billing') ?></a></li>
			<?php } ?>
			
			<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
			<li><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('custom properties') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) {
					if (array_var($category, 'hidden')) continue;
				?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>

		
		<div id="<?php echo $genid ?>add_timeslot_details" class="editor-container form-tab">
		
			<?php if (logged_user()->isAdminGroup()) { ?>
			<div class="dataBlock" style="<?php echo (can_manage_time(logged_user())) ? '':'display: none;'?>">
				<?php echo label_tag(lang('user')) ?>
				<?php
					$options = array();
					foreach ($users as $user) {
						$options[] = option_tag($user->getObjectName(), $user->getId(), $timeslot->getContactId() == $user->getId() ? array("selected" => "selected") : null);
					}
					echo select_box("timeslot[contact_id]", $options); 
				?>
			</div>
			<?php } ?>
			
			<div class="dataBlock" >
				<?php echo label_tag(lang('date')) ?>
				<?php $date = $timeslot->isNew() ? DateTimeValueLib::now() : new DateTimeValue($timeslot->getStartTime()->getTimestamp() + logged_user()->getTimezone()*3600); ?>
				<?php echo pick_date_widget2('timeslot[date]', $date, $genid, 1000, false) ?>
			</div>
			
			<div class="dataBlock" >
				<?php echo label_tag(lang('time')) ?>
				<?php echo text_field('timeslot[hours]', floor($timeslot->getMinutes() / 60), array('type' => 'number', 'class' => 'short')) ?>
				<span style="margin:0 10px 0 0;"><?php echo lang('hours')?></span>
				
				<select name="timeslot[minutes]">
				<?php
					$minuteOptions = array(0,5,10,15,20,25,30,35,40,45,50,55);
					for($i = 0; $i < 12; $i++) {
						$sel = ($timeslot->getMinutes() % 60) == $minuteOptions[$i] ? 'selected="selected"' : '';
						echo "<option value=\"" . $minuteOptions[$i] . "\" $sel>" . $minuteOptions[$i] . "</option>\n";
					}
				?>
				</select>
				<span style="margin:0 10px 0 0;"><?php echo lang('minutes')?></span>
			</div>
			
			<div class="dataBlock">
				<?php echo label_tag(lang('description')) ?>
				<?php echo textarea_field('timeslot[description]', $timeslot->getDescription(), array('class' => 'long'))?>
			</div>
		
			<?php 
				echo render_object_custom_properties($timeslot, null, null, 'visible_by_default');
			?> 
		</div>
		
		
		<div id="<?php echo $genid ?>add_timeslot_related_to" class="editor-container form-tab">
		<?php 
			$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
			if ($timeslot->isNew()) {
				render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
			} else {
				render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $timeslot->getMemberIds(), array('listeners' => $listeners), null, null, false);
			} 
			
		?>
			<div class="clear"></div>
		</div>
		
		<?php if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
		<div id="<?php echo $genid ?>add_timeslot_billing" class="editor-container form-tab">
			
			<div class="dataBlock">
				<?php echo label_tag(lang('type')) ?>
				<?php echo radio_field('timeslot[is_fixed_billing]', !$timeslot->getColumnValue('is_fixed_billing'), array('onchange' => 'og.showAndHide("' . $genid. 'hbilling",["' . $genid. 'fbilling"])', 
					'value' => '0', 'style' => 'width:16px')) . lang('hourly billing'); ?>
					
				<?php echo radio_field('timeslot[is_fixed_billing]', $timeslot->getColumnValue('is_fixed_billing'), array('onchange' => 'og.showAndHide("' . $genid. 'fbilling",["' . $genid. 'hbilling"])', 
					'value' => '1', 'style' => 'width:16px')) . lang('fixed billing');?>
			</div>
			
		  	<div id="<?php echo $genid ?>hbilling" class="dataBlock" style="<?php echo $timeslot->getColumnValue('is_fixed_billing') ? 'display:none':'' ?>">
		    	<?php echo label_tag(lang('hourly rates'), 'addTimeslotHourlyBilling') ?>
		    	<?php echo config_option('currency_code', '$') ?>&nbsp;
		  		<?php echo text_field('timeslot[hourly_billing]', $timeslot->getColumnValue('hourly_billing'), array('id' => 'addTimeslotHourlyBilling', 'readonly' => 'readonly', 'style' => 'border:0;')) ?>
		  	</div>
		  	
		  	<div id="<?php echo $genid ?>fbilling" class="dataBlock" style="<?php echo $timeslot->getColumnValue('is_fixed_billing') ? '' : 'display:none' ?>">
		    	<?php echo label_tag(lang('billing amount'), 'addTimeslotFixedBilling') ?>
		    	<?php echo config_option('currency_code', '$') ?>&nbsp;
		  		<?php echo text_field('timeslot[fixed_billing]', $timeslot->getColumnValue('fixed_billing'), array('id' => 'addTimeslotFixedBilling', 'type' => 'number')) ?>
			</div>
		  	
		</div>
		<?php } ?>
		
		<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab other-custom-properties-div">
			<?php  echo render_object_custom_properties($timeslot, null, null, 'other') ?>
			<?php  echo render_add_custom_properties($object); ?>
		</div>
		<?php } ?>
		
		<?php foreach ($categories as $category) { ?>
		<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
			<?php echo $category['content'] ?>
		</div>
		<?php } ?>
	</div>

	
	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s', array('style'=>'margin-top:0px')); 
	}?>
</div>
</div>
</form>
<script>
$(function() {
	$("#<?php echo $genid?>tabs").tabs();
});
</script>