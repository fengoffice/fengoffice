<?php
	/* @var $timeslot Timeslot */
	$genid = gen_id();
	$object = $timeslot;
	
	$categories = array();
	Hook::fire('object_edit_categories', $object, $categories); // se genera el tab de billings
	
	// on submit functions
	if (array_var($_REQUEST, 'modal')) {
		$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit = "return true;";
	}
	
	if (!isset($pre_selected_member_ids)) $pre_selected_member_ids = null;
	
    $has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;
?>

<form onsubmit="<?php echo $on_submit?>" class="add-timeslot" id="<?php echo $genid ?>submit-edit-form" action="<?php echo $timeslot->isNew() ? get_url('time', 'add') : get_url('time', 'edit_timeslot', array('id' => $timeslot->getId())); ?>" method="post" enctype="multipart/form-data">
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
                    <?php echo submit_button($timeslot->isNew() ? lang('save') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="feng-forms">
            <div class="coInputMainBlock edit-member">
                <input type="hidden" name="genid" value="<?php echo $genid?>" id="genid"/>
                <input type="hidden" name="object_id" value="<?php echo $timeslot->getRelObjectId()?>" />
                <input type="hidden" name="dont_reload" value="<?php echo isset($dont_reload) ? $dont_reload : '0'?>" />
                <input type="hidden" name="req_channel" value="<?php echo array_var($_REQUEST, 'req_channel', 'modal form') ?>" />

                <div id="<?php echo $genid?>tabs" class="edit-form-tabs">
                    <ul id="<?php echo $genid?>tab_titles">
                        <li><a href="#<?php echo $genid?>add_timeslot_basic_div"><?php echo lang('details') ?></a></li>

                        <?php if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
                        <li><a href="#<?php echo $genid?>add_timeslot_billing"><?php echo lang('billing') ?></a></li>
                        <?php } ?>
                        <?php foreach ($categories as $category) {
                            if (array_var($category, 'hidden')) continue;
                        ?>
                        <li><a href="#<?php echo $genid . array_var($category, 'id', $category['name']) ?>" id="<?php echo array_var($category, 'id', $category['name'])."_li"?>"><?php echo $category['name'] ?></a></li>
                        <?php } ?>
                    </ul>

                    <div id="<?php echo $genid ?>add_timeslot_basic_div" class="editor-container form-tab">
                        <?php
                        $properties_html = null;
                        Hook::fire('override_render_properties', [
                            'object' => $timeslot,
                            'genid' => $genid,
                            'visible_by_default' => true
                        ], $properties_html);

                        if (!is_null($properties_html)) {
							
							echo '<div class="main-custom-properties-div">';
                            echo_custom_properties_html($properties_html);
							echo '</div>';

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
                                        $input_file = ROOT . '/application/views/timeslot/form_inputs/' . $column . '.php';
                                        if (file_exists($input_file)) {
                                            echo '<div class="form-group">';
                                            include $input_file;
                                            echo '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="col">
                                    <?php
                                        $listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().'); og.set_time_is_billable_using_labor_wrapper("'.$genid.'");');
                                        
                                        // allow to add more listeners to this component
                                        Hook::fire('timeslot_form_member_selector_listeners', array('genid' => $genid, 'object' => $object), $listeners);

                                        if ($timeslot->isNew()) {
                                            render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $pre_selected_member_ids, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
                                        } else {
                                            render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $timeslot->getMemberIds(), array('listeners' => $listeners, 'object' => $object), null, null, false);
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <?php } // endif ?>
                    </div>

                    <?php 
                    $can_see_billing_info = true;
                    Hook::fire('get_can_see_billing_information', array('user'=>logged_user()), $can_see_billing_info);
                    if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing') && $can_see_billing_info) { ?>
                        <div id="<?php echo $genid ?>add_timeslot_billing" class="editor-container form-tab">	
                            <div class="dataBlock">
                                <?php echo label_tag(lang('type')) ?>
                                <?php echo radio_field('timeslot[is_fixed_billing]',
                                                        !$timeslot->getColumnValue('is_fixed_billing'),
                                                        array('onchange' => 'og.showAndHide("' . $genid. 'hbilling",["' . $genid. 'fbilling"])', 
                                                        'value' => '0',
                                                        'style' => 'width:16px')) . lang('hourly billing'); ?>

                                <?php echo radio_field('timeslot[is_fixed_billing]',
                                                        $timeslot->getColumnValue('is_fixed_billing'),
                                                        array('onchange' => 'og.showAndHide("' . $genid. 'fbilling",["' . $genid. 'hbilling"])', 
                                                        'value' => '1',
                                                        'style' => 'width:16px')) . lang('fixed billing');?>
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
                    <?php foreach ($categories as $category) { ?>
                        <div id="<?php echo $genid . array_var($category, 'id', $category['name']) ?>" class="form-tab">
                            <?php echo $category['content'] ?>
                        </div>
                    <?php } ?>
                </div>


                <?php if (!array_var($_REQUEST, 'modal')) {
                    echo submit_button($timeslot->isNew() ? lang('save') : lang('save changes'), 's', array('style'=>'margin-top:0px')); 
                }?>
            </div>
        </div>
    </div>
</form>

<div id="modal-config-more" style="display: none;">
    <h2><?php echo lang('How should we adjust'); ?></h2>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="start" checked="" id="{genid}user_config_start_sooner"/>
        <label class="pull-left" for="{genid}user_config_start_sooner"><?php echo lang('Did you start sooner'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="end" id="{genid}user_config_end_later"/>
        <label class="pull-left" for="{genid}user_config_end_later"> <?php echo lang('Did you end later'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div>
         <div class="pull-left" style="line-height: 13px;margin-top: 33px;">
            <input class="pull-left" type="checkbox" id="remember"/>
            <label class="pull-left" style="margin-left: 10px"><?php echo lang('Remember my selection'); ?></label>
            <div class="clearfix"></div>
        </div>
        <button class="pull-right submit" onclick="og.rollbackInputs();" style="font-size: 16px;"><?php echo lang('cancel'); ?></button>
        <button class="pull-right submit add" onclick="og.applyTimeAction();" style="font-size: 16px;margin-right: 5px;"><?php echo lang('accept'); ?></button>
        <div class="clearfix"></div>
    </div>
</div>


<div id="modal-config-less" style="display: none;">
    <h2><?php echo lang('How should we adjust'); ?></h2>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="start" checked="" id="{genid}user_config_start_later"/>
        <label for="{genid}user_config_start_later"><?php echo lang('Did you start later'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="end" id="{genid}user_config_end_sooner"/>
        <label for="{genid}user_config_end_sooner"><?php echo lang('Did you end sooner'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div>
        <div class="pull-left" style="line-height: 13px;margin-top: 33px;">
            <input class="pull-left" type="checkbox" id="remember"/>
            <label class="pull-left" style="margin-left: 10px"><?php echo lang('Remember my selection'); ?></label>
            <div class="clearfix"></div>
        </div>
        <button class="pull-right submit" onclick="og.rollbackInputs();" style="font-size: 16px;"><?php echo lang('cancel'); ?></button>
        <button class="pull-right submit add" onclick="og.applyTimeAction();" style="font-size: 16px;margin-right: 5px;"><?php echo lang('accept'); ?></button>
        <div class="clearfix"></div>
    </div>
</div>

<div id="modal-config-hours" style="display: none;">
    <h2><?php echo lang('What do you prefer'); ?></h2>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="dates" checked="" id="{genid}user_config_change_date"/>
        <label for="{genid}user_config_change_date"><?php echo lang('Did you want change the date value'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="times" id="{genid}user_config_change_time"/>
        <label for="{genid}user_config_change_time"><?php echo lang('Did you want change the time value'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div>
        <div class="pull-left" style="line-height: 13px;margin-top: 33px;">
            <input class="pull-left" type="checkbox" id="remember"/>
            <label class="pull-left" style="margin-left: 10px"><?php echo lang('Remember my selection'); ?></label>
            <div class="clearfix"></div>
        </div>
        <button class="pull-right submit" onclick="og.rollbackDates();" style="font-size: 16px;"><?php echo lang('cancel'); ?></button>
        <button class="pull-right submit add" onclick="og.applyDatesAction();" style="font-size: 16px;margin-right: 5px;"><?php echo lang('accept'); ?></button>
        <div class="clearfix"></div>
    </div>
</div>

<script>   
    var edit_mode = "<?php echo isset($edit_mode) ? $edit_mode : "";  //this variable is undefined  *** LC 2023-10-03 ?>";

    var edit_hours_mode = "<?php echo 0; ?>";
    var gen_id = "<?php echo $genid; ?>";
    var time_preferences =JSON.parse('<?php echo json_encode($time_preferences); ?>');
    var div;

    og.reclassify_time_when_linking_task = <?php echo (config_option('reclassify_time_when_linking_task') ? '1' : '0') ?>;
    
    og.toggle_specify_end_time = function(input, genid) {
        $(input).hide();
        $("#"+genid+"end_date_container").slideToggle(100);
    }
    
    og.toggle_specify_paused_time = function(input, genid) {
        $(input).hide();
        $('#' + genid + "paused_time_container").slideToggle(100);
    }
    // Save current selected member ids
    var json_sel_ids = "";
    var current_selected_member_ids = [];

    if (typeof member_selector !== 'undefined' && member_selector[gen_id] && member_selector[gen_id].hiddenFieldName) {
        var hidden_field = document.getElementById(gen_id + member_selector[gen_id].hiddenFieldName);
        if (hidden_field) {
            json_sel_ids = hidden_field.value;
            current_selected_member_ids = json_sel_ids == "" ? [] : Ext.util.JSON.decode(json_sel_ids);
        }
    }
    
	//[Conrado 8/2019] Would this work?
	//The idea here is to know the initial values, to see if/how we need to edit them 
    og._original_worked_time = $('#worked_time').val();
    og._original_worked_minute = $('#worked_minute').val();
        
    og._original_paused_time = $('#paused_time').val();
    og._original_paused_minute = $('#paused_minute').val();
    

	og._original_start_date = $('#date_input');
    og._original_start_time = $('#start_time_input');
    
    og._original_end_date = $('#date_end_input');
    og._original_end_time = $('#end_time_input');
    
    //The idea here is to keep track of the things that were edited in this session, and in what order.
    og._start_time_edited_in_session = 0;
    og._end_time_edited_in_session = 0;
    og._worked_or_paused_time_edited_in_session = 0;    

    
    //These are the multipliers used to calculate the number of miliseconds that make up a minute, an hour, a day, and a year
    og._minutes = 1000 * 60;
    og._hours = og._minutes * 60;
    og._days = og._hours * 24;
    og._years = og._days * 365;
    
    og._lastTimeValue = 0;
    og._newTimeValue = 0;
    
    og._lastHoursInputValue = 0;
    og._lastMinutesInputValue = 0;
    og._lastPausedHoursInputValue = 0;
    og._lastPausedMinutesInputValue = 0;

	og._dims_to_keep_when_relinkng_task = [];
	<?php
	$status_dim = Dimensions::findByCode('status_timesheet');
	if ($status_dim instanceof Dimension) {
	?>
		og._dims_to_keep_when_relinkng_task.push(<?php echo $status_dim->getId() ?>);
	<?php
	}
	?>
    
    /**
     * Process triggered when the user changes the value in the (worked) time input
     * 
     */
    og.onchangeTimesInputs = function (self){

    	//This didn't seem necessary so far.
        	
        //Step 1) Capture the "new" work (and pause) values entered by the user
        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();
        
        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();
        
        //Step 2) Convert them into a timeValue (total number of milliseconds)
        //@ToDo - review: is this necessary?
        og._newTimeValue = timeslots.turn_into_total_milliseconds(worked_time, worked_minute, paused_time, paused_minute);
        		
        //Step 3) I still haven't checked why this is needed 
        if(og._newTimeValue > og._lastTimeValue){
            div= $('#modal-config-more').html().replace(/\{genid\}/ig, Ext.id());
        }else{
            div= $('#modal-config-less').html().replace(/\{genid\}/ig, Ext.id());
        }

        //Step 3.2) Update the variables
        og._lastTimeValue = og._newTimeValue;
        og._worked_or_paused_time_edited_in_session = Math.max(og._start_time_edited_in_session, og._end_time_edited_in_session) + 1;
        
        //Step 4) Determine whether the start date or the end date should be modified
        //or if it needs to be asked to the user
        if (time_preferences.automatic_calculation_time == timeslots.PREF_DURATION_ASK_USER) {
            $modal = og.ExtModal.show({
                title:'<?php echo lang("You changed the length of your time record"); ?>',
                basecls: 'user-config-timeslots',
                html: '<div id="user-config" class="user-config-timeslots-container">'+div+'</div>'
                });
            $modal.on('close', og.rollbackInputs);
        } else {
            // Use the shared preference-dispatch helper (also used by the weekly view)
            var startKnown = $('#start_time_input').val() !== 'hh:mm' && $('#date_input').val() !== og.preferences.date_format_tip;
            var endKnown   = $('#end_time_input').val() !== 'hh:mm' && $('#date_end_input').val() !== og.preferences.date_format_tip;
            // Honour the session-edit order: if pref==RECALC_END but end was edited after start, fall back to RECALC_START
            var effectivePref = (time_preferences.automatic_calculation_time == timeslots.PREF_DURATION_RECALC_END && og._end_time_edited_in_session > og._start_time_edited_in_session)
                ? timeslots.PREF_DURATION_RECALC_START
                : time_preferences.automatic_calculation_time;
            var action = timeslots.whatChangesOnDurationChange(startKnown, endKnown, effectivePref);
            if (action === 'end')   { og.changeEndDate(); }
            else                    { og.changeStartDate(); }
        }
        
        /* old version        
        if(edit_mode){
            switch(time_preferences.automatic_calculation_time){
                case "1":
                    og.changeStartDate();
                    //og.changeStartDate() and og._start_time_edited_in_session == 
                    break;
                case "2":
                    og.changeEndDate();
                    break;
                default :
                    $modal = og.ExtModal.show({
                        title:'<?php echo lang("You changed the length of your time record"); ?>',
                        basecls: 'user-config-timeslots',
                        html: '<div id="user-config" class="user-config-timeslots-container">'+div+'</div>'
                    });
                    $modal.on('close', og.rollbackInputs);
                    break;
            }
        }else{
            og.changeStartDate();
        }
        */

    }

    /**
     * Apply the change the the user selected on the pop-up questions
     * 
     */
    og.applyTimeAction = function (){
        action = $('#user-config').find('input:radio[name="user_config"]:checked').val();
        var actionValue = "0";
        if(action == 'start'){
            actionValue = "1";
            og.changeStartDate();
        }
        if(action == 'end'){
            actionValue = "2";
            og.changeEndDate();
        }
        remember = $('#user-config').find('#remember').is(":checked")? 1 : 0;
        if(remember){
            // use ajax to remember the user's selection
            time_preferences.automatic_calculation_time = actionValue;
            var url = og.getUrl('account', 'update_user_preference', {name: 'automatic_calculation_time', value:actionValue});
            og.openLink(url,{hideLoading:true});
        }
        og.updateLastTimeValue();
        og.ExtModal.hide();
    };

    /**
     * Apply the change (start time, end time and/or worked time) that the user chose on the pop-up question
     * 
     */
    og.applyDatesAction = function (){
        action = $('#user-config').find('input:radio[name="user_config"]:checked').val();
       // debugger;
        var actionValue = "3";
        if(action == 'dates'){
            actionValue = "1";
            if(og.whichFieldMustChange == 2){
                og.changeEndDate();
            }else{
                og.changeStartDate();
            };
        }
        if(action == 'times'){
            actionValue = "2";
            og.changeTimesInputs();
        }
        remember = $('#user-config').find('#remember').is(":checked")? 1 : 0;
        if(remember){
            // use ajax to remember the user's selection
            time_preferences.automatic_calculation_start_time = actionValue;
            var url = og.getUrl('account', 'update_user_preference', {name: 'automatic_calculation_start_time', value:actionValue});
            og.openLink(url,{hideLoading:true});
        }
        og.updateLastTimeValue();
        og.ExtModal.hide();

    };

    
    /**
     * 
     * Automatically change the start date.
     *
	 * When the user changes the worked time (or paused time) and/or the end date+time, the start date might change. 
     * This will depend on what settings the user has in its configuration, and/or whether it is configured to prompt a question to the user.
     */
    og.changeStartDate = function () {

        //Step 1) get the current values
        start_date = $('#date_input');
        start_time = $('#start_time_input');
        
        end_date = $('#date_end_input');
        end_time = $('#end_time_input');

        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();
        
        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();
		
        //If the end_time or end_date are empty, it should be set as "now" by default.
		var now = new Date(); //Current time
        if(end_time.val() =="hh:mm" ) {
            end_time.val(timeslots.format_hours_and_minutes(now));
        }
        if(end_date.val() == og.preferences.date_format_tip) {
            end_date.val(timeslots.safe_date_format(now, og.preferences.date_format));
        }

        //Step 2) We turn the end_date into a Date object
        var end_date_aux = og.getDateArray(end_date.val(), end_time.val());
        var end_date_and_time = new Date(end_date_aux[0], end_date_aux[1], end_date_aux[2], end_date_aux[3], end_date_aux[4]);
        
        //Step 3) We sum up the worked time and paused time as milliseconds
        var total_milliseconds = timeslots.turn_into_total_milliseconds(worked_time, worked_minute, paused_time, paused_minute);
        //console.log("Total miliseconds: "+total_milliseconds);
        
        //Step 4) We deduct the worked hours + paused hours from the end date, to get the start date
        var start_date_and_time = end_date_and_time.getTime() - total_milliseconds;
        var startDate = new Date(start_date_and_time);
        
        start_date.val(timeslots.safe_date_format(startDate, og.preferences.date_format));
        start_time.val(timeslots.format_hours_and_minutes(startDate));
          
    };

    /**
     * Automatically change the end date
	 * When the user changes the worked time (or paused time) and/or the start date+time, the start date might change 
     * This will depend on what settings the user has in its configuration, and/or whether it is configured to prompt a question to the user.     
     */
    og.changeEndDate = function () {
        
        //Step 1) get the current values
        start_date = $('#date_input');
        start_time = $('#start_time_input');
        
        end_date = $('#date_end_input');
        end_time = $('#end_time_input');

        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();
        
        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();
		

        //Step 2) Check if the start_time or start_date are empty. If either is empty we use the changeStartDate function
        //(It doesn make sense to "change the End Date", because we don't have a firm start date).
        if ( 
             //(end_time.val() =="hh:mm" ) 							||
             //(end_date.val() == og.preferences.date_format_tip) 	||
             (start_time.val() =="hh:mm" ) 							||
             (start_date.val() == og.preferences.date_format_tip) ) {
        	og.changeStartDate();
        } else {

            //Step 3) We turn the start_date into a Date object
            var start_date_aux = og.getDateArray(start_date.val(), start_time.val());
            var start_date_and_time = new Date(start_date_aux[0], start_date_aux[1], start_date_aux[2], start_date_aux[3], start_date_aux[4]);
            
            //Step 4) We sum up the worked time and paused time as milliseconds
            var total_milliseconds = timeslots.turn_into_total_milliseconds(worked_time, worked_minute, paused_time, paused_minute);
            //console.log("In changeEndDate() - Total miliseconds: "+total_milliseconds);
            
            //Step 5) We deduct the worked hours + paused hours from the end date, to get the start date
            var end_date_and_time = start_date_and_time.getTime() + total_milliseconds;
            var endDate = new Date(end_date_and_time);

            //Step 6) We input the new values on the fields
            end_date.val(timeslots.safe_date_format(endDate, og.preferences.date_format));
            end_time.val(timeslots.format_hours_and_minutes(endDate));
        } 

    };

    /**
     * Automatically change the length of worked time
     */
    og.changeTimesInputs = function(){
        //Step 1) get the current values
        start_date = $('#date_input');
        start_time = $('#start_time_input');
        
        end_date = $('#date_end_input');
        end_time = $('#end_time_input');

        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();
        
        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();

        //Step 2) Check if both start time and end time are entered
        if ( (end_time.val()   != "hh:mm" ) 						&&
             (end_date.val()   != og.preferences.date_format_tip) 	&&
             (start_time.val() != "hh:mm" ) 						&&
             (start_date.val() != og.preferences.date_format_tip) ) {

			//Step 3) We turn the start and end times into Date objects
            var start_aux = og.getDateArray(start_date.val(),start_time.val());
            var end_aux = og.getDateArray(end_date.val(),end_time.val());
            DateOne = new Date(start_aux[0],start_aux[1],start_aux[2],start_aux[3],start_aux[4]);
            DateTwo = new Date(end_aux[0],end_aux[1],end_aux[2],end_aux[3],end_aux[4]);

            //Step 4) We calculate the difference between start and end date, and turn it into hours and minutes 
            var time_diff = DateTwo.getTime() - DateOne.getTime();

            if(paused_time != ""){
                time_diff -= (parseInt(paused_time) * timeslots._minutes_multiplier);
            }
            if(paused_minute !=""){
                time_diff -= (parseInt(paused_minute) * timeslots._hours_multiplier);
            }

            var final_minutes = (time_diff/timeslots._minutes_multiplier) % 60;
            var final_hours = parseInt((time_diff/timeslots._minutes_multiplier) / 60);

            $('#worked_time').val(final_hours);
            $('#worked_minute').val(final_minutes);
    			
        } else {
            //If any of those is empty, we can't "change the worked time", because we don't have enough elements
        	og.changeStartDate();
        }

    };

    og.onchangeStartDate = function () {
        div = $('#modal-config-hours').html().replace(/\{genid\}/ig, Ext.id());
        if (time_preferences.automatic_calculation_start_time == timeslots.PREF_DATE_ASK_USER) {
            var end_date_visible = $("#"+gen_id+"end_date_container").is(":visible");
            if (end_date_visible) {
                $modal = og.ExtModal.show({
                    title: '<?php echo lang("You changed a date in your time record"); ?>',
                    basecls: 'user-config-timeslots',
                    html: '<div id="user-config" class="user-config-timeslots-container">' + div + '</div>'
                });
                $modal.on('close', og.rollbackInputs);
            }
        } else {
            var endKnown      = $('#end_time_input').val() !== 'hh:mm' && $('#date_end_input').val() !== og.preferences.date_format_tip;
            var durationKnown = $('#worked_time').val() !== '' || $('#worked_minute').val() !== '';
            var action = timeslots.whatChangesOnStartChange(endKnown, durationKnown, time_preferences.automatic_calculation_start_time);
            if (action === 'duration') { og.changeTimesInputs(); }
            else                       { og.changeEndDate(); }
        }
    }

    og.onchangeEndDate = function () {
        div = $('#modal-config-hours').html().replace(/\{genid\}/ig, Ext.id());
        if (time_preferences.automatic_calculation_start_time == timeslots.PREF_DATE_ASK_USER) {
            var end_date_visible = $("#"+gen_id+"end_date_container").is(":visible");
            if (end_date_visible) {
                $modal = og.ExtModal.show({
                    title: '<?php echo lang("You changed a date in your time record"); ?>',
                    basecls: 'user-config-timeslots',
                    html: '<div id="user-config" class="user-config-timeslots-container">' + div + '</div>'
                });
                $modal.on('close', og.rollbackInputs);
            }
        } else {
            var startKnown    = $('#start_time_input').val() !== 'hh:mm' && $('#date_input').val() !== og.preferences.date_format_tip;
            var durationKnown = $('#worked_time').val() !== '' || $('#worked_minute').val() !== '';
            var action = timeslots.whatChangesOnEndChange(startKnown, durationKnown, time_preferences.automatic_calculation_start_time);
            if (action === 'duration') { og.changeTimesInputs(); }
            else                       { og.changeStartDate(); }
        }
    }

    //This global variable seems necessary to carry the user selection across functions
    og.whichFieldMustChange = 0;
    
    og.onchangeDatesInputs = function (whichField) {

        if(whichField == 'timeslot[end_date]'){
            og.whichFieldMustChange = 1;
        }else{
            og.whichFieldMustChange = 2;
        }

        div = $('#modal-config-hours').html().replace(/\{genid\}/ig, Ext.id());

        var action = 0;
        if(time_preferences.automatic_calculation_start_time != 0){
            if(time_preferences.automatic_calculation_start_time == 1){
                action = 2;
                if(whichField == 'timeslot[end_date]'){
                    action = 1;
                }
            }
            if(time_preferences.automatic_calculation_start_time == 2){
                action = 3;
            }
        }

        switch (action) {
            case 1:
                og.changeStartDate();
                break;
            case 2:
                og.changeEndDate();
                break;
            case 3:
                og.changeTimesInputs();
                break;
            default :
            	var end_date_visible = $("#"+gen_id+"end_date_container").is(":visible");
        		if (end_date_visible) {
	                $modal = og.ExtModal.show({
	                    title: '<?php echo lang("You changed the length of your time record"); ?>',
	                    basecls: 'user-config-timeslots',
	                    html: '<div id="user-config" class="user-config-timeslots-container">' + div + '</div>'
	                });
	                $modal.on('close', og.rollbackInputs);
        		}
                break;
        }


    };
    
        
    og.updateLastTimeValue = function (){
        
        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();
        
        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();
        
            
        var horas_restar = 0;
        if(worked_time > 0 && worked_time != ''){
            horas_restar = og._hours * worked_time;
        }
        if(paused_time > 0 && paused_time != ''){
            horas_restar += og._hours * paused_time;
        }
        var minutos_restar = 0;
        if(worked_minute > 0 && worked_minute != ''){
            minutos_restar = og._minutes * worked_minute;
        }
        if(paused_minute > 0 && paused_minute != ''){
            minutos_restar += og._minutes * paused_minute;
        }
            
        og._lastTimeValue = horas_restar+minutos_restar;
        og._lastHoursInputValue = worked_time;
        og._lastMinutesInputValue = worked_minute;
        og._lastPausedHoursInputValue = paused_time;
        og._lastPausedMinutesInputValue = paused_minute;
        
    };

    /**
     * Get back time values when cancel the automatic changes
     */
    og.rollbackInputs = function (){
        worked_time = $('#worked_time').val(og._lastHoursInputValue);
        worked_minute = $('#worked_minute').val(og._lastMinutesInputValue);
        paused_time = $('#paused_time').val(og._lastPausedHoursInputValue);
        paused_minute = $('#paused_minute').val(og._lastPausedMinutesInputValue);

        og.ExtModal.hide();
    };

    og.rollbackDates = function(){

    }

    
    og.openObjectTaskPicker = function (genid) { 
        og.ObjectPicker.show(function (objs) {
            if (objs && objs.length > 0) {
                var obj = objs[0].data;
                if (obj.type != 'task') {
                    og.msg(lang("error"), lang("object type not supported"), 4, "err");
                } else {
                    og.addObjectTask(genid, obj.object_id, obj.name);
                }
            }
        },'',{
            types: ['task'],
            selected_type: 'task',
			ignore_context: false,
			context: og.getMembersToFilterObjectPicker('<?php echo $genid?>', 'task'),
        });
    };
    
    og.addObjectTask = function (genid, object_id, object_name) {

        
        var Link = $('.add-linked-object:first');
        var Object = $('.og-add-template-object:first');
        Object.find('#object_id').val(object_id);
        Object.find('.name').text(object_name);

        if (og.reclassify_time_when_linking_task) {
            og.set_timeslot_members_by_task(object_id, og._dims_to_keep_when_relinkng_task);
        } else {
            og.setTimeslotIsBillableUsingTaskWrapper(object_id);
        }

		// if advanced billing plugin is active => if task is not billable then disable is_billable input and set it to no
		if (og.advanced_billing) {
			// get task details
			og.openLink(og.getUrl('task','get_task_data',{id: object_id, task_info: true}),{
				callback: function(success, data) {
					// process response
					if (data && data.task) {
						// Store task data for future use
						og.related_task_data[data.task.id] = data.task;


						var task_is_fixed_fee = og.check_task_or_parent_is_fixed_fee(data.task);
						var task_non_billable = !data.task.is_billable;

						if (task_is_fixed_fee) {
							// if task or parent is fixed fee, toggle to show is_billable_work field
							if (og.income && og.income.toggle_billable_fields) {
								og.income.toggle_billable_fields(true, genid);
							}
                             if(og.advanced_billing && og.advanced_billing.disable_form_billing_section) {
                                og.advanced_billing.disable_form_billing_section();
                            }

							$("#" + genid + "is_billableNo.yes_no").attr('disabled', 'disabled');
							$("#" + genid + "is_billableYes.yes_no").attr('disabled', 'disabled');

						} else if (task_non_billable) {
							// if task is non billable then disable is_billable input and set it to no
							if (og.income && og.income.toggle_billable_fields) {
								og.income.toggle_billable_fields(false, genid);
							}
                            if(og.advanced_billing && og.advanced_billing.disable_form_billing_section) {
                                og.advanced_billing.disable_form_billing_section();
                            }
							$("#" + genid + "is_billableNo").click();
							$("#" + genid + "is_billableNo.yes_no").attr('disabled', 'disabled');
							$("#" + genid + "is_billableYes.yes_no").attr('disabled', 'disabled');

						} else {
							// if billable then let the user change the billable input
							if (og.income && og.income.toggle_billable_fields) {
								og.income.toggle_billable_fields(false, genid);
							}
                            if(og.advanced_billing && og.advanced_billing.enable_form_billing_section) {
                                og.advanced_billing.enable_form_billing_section();
                            }
							$("#" + genid + "is_billableNo.yes_no").removeAttr('disabled');
							$("#" + genid + "is_billableYes.yes_no").removeAttr('disabled');
						}
					}
				}
			});
		}
		
        Link.hide()
        Object.show();
        
    };

	og.disable_time_form_is_billable_input = function() {
		$("#" + gen_id + "is_billableNo").click();
		$("#" + gen_id + "is_billableNo").attr('disabled','disabled');
		$("#" + gen_id + "is_billableYes").attr('disabled','disabled');
	}
	og.enable_time_form_is_billable_input = function() {
		$("#" + gen_id + "is_billableNo").removeAttr('disabled');
		$("#" + gen_id + "is_billableYes").removeAttr('disabled');
	}

	og.check_task_or_parent_is_fixed_fee = function(task) {
		return task.is_fixed_fee || task.has_fixed_fee_parent;
	}

	og.related_task_data = {};

	og.enabled_disable_is_billable_from_fixed_fee_task = function(task) { 
		var task_is_fixed_fee = og.check_task_or_parent_is_fixed_fee(task);
		var task_non_billable = !task.is_billable;

		if (task_is_fixed_fee) {
            og.disable_time_form_is_billable_input();
            if (og.income && og.income.toggle_billable_fields) {
                og.income.toggle_billable_fields(true, gen_id);
            }
		} else if (task_non_billable) {
			// A non-billable task is authoritative: force "No" and lock the input. We must not stop
			// at toggle_billable_fields(false), which derives billable from is_billable_work (defaults
			// to "Yes" on a new entry) and would silently re-check "Yes". Call it for the field layout,
			// then override with disable_time_form_is_billable_input() so "No" always wins.
			if (og.income && og.income.toggle_billable_fields) {
				og.income.toggle_billable_fields(false, gen_id);
			}
			og.disable_time_form_is_billable_input();
		} else {
			og.enable_time_form_is_billable_input();
			if (og.income && og.income.toggle_billable_fields) {
				og.income.toggle_billable_fields(false, gen_id);
			}
		}
	}

	og.setTimeslotIsBillableUsingTaskWrapper = function(task_id) {
        let task = og.related_task_data[task_id];
        if (!task) {
            og.openLink(og.getUrl('task', 'get_task_data', {id: task_id, task_info: true}), {
                hideLoading: true,
                callback: function(success, data) {
                    let task = data.task;
                    if (task) {
                        og.related_task_data[task.id] = task;
                        og.enabled_disable_is_billable_from_fixed_fee_task(task);
                        if (task.is_calculated_estimated_price) {
                            og.setTimeslotIsBillableUsingTask(task_id);
                        }
                    }
                }
            });
        } else {
            og.enabled_disable_is_billable_from_fixed_fee_task(task);
            if (task.is_calculated_estimated_price) {
                og.setTimeslotIsBillableUsingTask(task_id);
            }
        }
	}

    og.setTimeslotIsBillableUsingTask = function(task_id){
        // Change billable if hour_types, advanced_billing and income plugins are activated
		var hour_type_active = <?php echo Plugins::instance()->isActivePlugin('hour_types') ? '1' : '0'; ?>;
		var advanced_billing_active = <?php echo Plugins::instance()->isActivePlugin('advanced_billing') ? '1' : '0'; ?>;
        var income_active = <?php echo Plugins::instance()->isActivePlugin('income') ? '1' : '0'; ?>;
        var genid = gen_id;
		if(hour_type_active && advanced_billing_active && income_active){
            var is_invoiced = $('#'+genid+'invoicing_status').val() == 'invoiced';
            if(!is_invoiced){
                var current_billable = $('#'+genid+'is_billableYes').attr('checked') == 'checked' ? 1 : 0;
                var params = {task_id: task_id, current_billable: current_billable};
                og.openLink(og.getUrl('billing_definition','get_task_billable_for_time_form', params), {
                    hideLoading: true,
                    callback: function(success, data) {
                        if(data.has_value){
                            if(data.is_billable){
                                $('#'+genid+'is_billableNo').removeAttr('checked');
                                $('#'+genid+'is_billableYes').attr('checked','checked');
                                $('#'+genid+'invoicing_status').val('pending');
                            } else {
                                $('#'+genid+'is_billableYes').removeAttr('checked');
                                $('#'+genid+'is_billableNo').attr('checked','checked');
                                $('#'+genid+'invoicing_status').val('non_billable');
                                og.disable_time_form_is_billable_input();
                            }
                        }
                    }
                });
            }
        }	
	};

	og.set_time_is_billable_using_labor_wrapper = function(genid) {
		let task_id = $("#object_id").val();
		if (task_id > 0) {
			let task = og.related_task_data[task_id];
			if (!task) {
				og.openLink(og.getUrl('task', 'get_task_data', {id: task_id, task_info: true}), {
					hideLoading: true,
					callback: function(success, data) {
						let task = data.task;
						if (task) {
                            var ask_confirmation = false;
							og.related_task_data[task.id] = task;
							og.enabled_disable_is_billable_from_fixed_fee_task(task);
							if (task.is_calculated_estimated_price) {
								og.set_time_is_billable_using_labor(genid, ask_confirmation);
							}
						}
					}
				});
			} else {
                var ask_confirmation = false;
				og.enabled_disable_is_billable_from_fixed_fee_task(task);
				if (task.is_calculated_estimated_price) {
					og.set_time_is_billable_using_labor(genid, ask_confirmation);
				}
			}

		} else {
			og.set_time_is_billable_using_labor(genid);
		}
	}

    og.set_time_is_billable_using_labor = function (genid, ask_confirmation = true) { 
        // Change billable if hour_types, advanced_billing and income plugins are activated
		var hour_type_active = <?php echo Plugins::instance()->isActivePlugin('hour_types') ? '1' : '0'; ?>;
		var advanced_billing_active = <?php echo Plugins::instance()->isActivePlugin('advanced_billing') ? '1' : '0'; ?>;
        var income_active = <?php echo Plugins::instance()->isActivePlugin('income') ? '1' : '0'; ?>;
        var json_sel_ids = og.get_selected_members_in_form(genid);
        var selected_member_ids = json_sel_ids == "" ? [] : Ext.util.JSON.decode(json_sel_ids);

        // The linked task's non-billable status is authoritative: a billable labor category must
        // not flip a non-billable task's time entry back to "billable". Without this guard, linking
        // a non-billable task while the user has a billable default hour type (auto-added during
        // reclassification) races the task-level "No" and silently overrides it with "Yes".
        var linked_task_id = $("#object_id").val();
        var linked_task = linked_task_id > 0 ? og.related_task_data[linked_task_id] : null;
        var linked_task_non_billable = !!(linked_task && !linked_task.is_billable);

        if(hour_type_active && advanced_billing_active && income_active){
            var is_invoiced = $('#'+genid+'invoicing_status').val() == 'invoiced';
            if(!is_invoiced){
                var current_billable = $('#'+genid+'is_billableYes').attr('checked') == 'checked' ? 1 : 0;
                var params = {member_ids: selected_member_ids, current_member_ids: current_selected_member_ids, current_billable: current_billable};
                og.openLink(og.getUrl('billing_definition','get_labor_cat_billable_for_time_form', params), {
                    hideLoading: true,
                    callback: function(success, data) {
                        if(data.has_value){
                            if(data.is_billable){
                                if(!linked_task_non_billable && current_billable != data.is_billable){
                                    if(ask_confirmation) {
                                        if(confirm(lang('You are changing from a non-billable labor category to a billable one. This will set the \'Billable\' property for this task to \'Yes\''))){
                                            $('#'+genid+'is_billableNo').removeAttr('checked');
                                            $('#'+genid+'is_billableYes').attr('checked','checked');
                                            $('#'+genid+'invoicing_status').val('pending');
                                        }
                                    } else {
                                        $('#'+genid+'is_billableNo').removeAttr('checked');
                                        $('#'+genid+'is_billableYes').attr('checked','checked');
                                        $('#'+genid+'invoicing_status').val('pending');
                                    }
                                }
                            } else {
                                if(current_billable != data.is_billable){
                                    if(ask_confirmation) {
                                        if(confirm(lang('You are changing from a billable labor category to a non-billable one. This will set the \'Billable\' property for this task to \'No\''))){
                                            $('#'+genid+'is_billableYes').removeAttr('checked');
                                            $('#'+genid+'is_billableNo').attr('checked','checked');
                                            $('#'+genid+'invoicing_status').val('non_billable');
                                        }
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
        current_selected_member_ids = selected_member_ids;
    }

    og.removeObjectTask = function (element) {

        var Link = $('.add-linked-object:first');
        var Object = $('.og-add-template-object:first');

        Object.hide();
        Link.show();

        Object.find('#object_id').val(0);
        Object.find('.name').text('');

		og.enable_time_form_is_billable_input();
         
		// Reset to show regular billable field when task is removed
		if (og.income && og.income.toggle_billable_fields) {
			og.income.toggle_billable_fields(false, gen_id);
		}
    }

    og.getTimeslotFormLaborMemberId = function(form_genid) {
		var hour_type_dim_id = og.enabled_dimensions_by_code && og.enabled_dimensions_by_code['hour_types']
			? og.enabled_dimensions_by_code['hour_types'] : 0;
		if (!hour_type_dim_id) return 0;

		var candidates = [form_genid, form_genid + '-' + hour_type_dim_id];
		for (var i = 0; i < candidates.length; i++) {
			var sid = candidates[i];
			if (!member_selector[sid] || !member_selector[sid].sel_context) continue;
			var ctx = member_selector[sid].sel_context[hour_type_dim_id]
				|| member_selector[sid].sel_context[String(hour_type_dim_id)]
				|| member_selector[sid].sel_context[parseInt(hour_type_dim_id, 10)];
			if (ctx && ctx.length > 0 && ctx[0]) {
				return ctx[0];
			}
		}
		return 0;
	}

    og.set_timeslot_members_by_task =  function(task_id, excluded_dim_ids) {
		if (task_id) {
			og.disable_time_form_submit_button(); // don't allow to submit form until next request returns

			var hour_type_dim_id = og.enabled_dimensions_by_code && og.enabled_dimensions_by_code['hour_types']
				? parseInt(og.enabled_dimensions_by_code['hour_types'], 10) : 0;
			var current_labor_member_id = og.getTimeslotFormLaborMemberId(gen_id);

			og.openLink(og.getUrl('task', 'get_task_data', {id: task_id, task_info: true}), {
				hideLoading: true,
				callback: function(success, data) {
					if (!success) {
						og.enable_time_form_submit_button(); // enable submit button
						return;
					}
					if (data && data.task) {
						og.related_task_data[data.task.id] = data.task;
                        // parse the mem path string
                        var mempath = Ext.util.JSON.decode(data.task.memPath);
                        var task_members_json = {};
						var task_labor_member_id = 0;
                        // iterate the mempath object, key = dimension_id, value = member ids grouped by member type id

                        for (var dim_id in mempath) {
							// Detect task labor category from memPath regardless of selector presence
							if (hour_type_dim_id && String(dim_id) == String(hour_type_dim_id)) {
								var labor_ots = mempath[dim_id];
								for (var labor_ot_id in labor_ots) {
									if (!isNaN(labor_ot_id) && labor_ots[labor_ot_id] && labor_ots[labor_ot_id].length > 0) {
										for (var lx in labor_ots[labor_ot_id]) {
											if (typeof labor_ots[labor_ot_id][lx] != 'function' && labor_ots[labor_ot_id][lx]) {
												task_labor_member_id = labor_ots[labor_ot_id][lx];
												break;
											}
										}
									}
									if (task_labor_member_id) break;
								}
							}

							// only process the members that can be edited in the form, the other task members that user can't edit will be managed by the controller
							let form_has_selctor = $("#"+ gen_id +"-member-chooser-panel-" + dim_id).length > 0;
							if (!form_has_selctor) {
								// check if we have a separated selector for this dimension
								form_has_selctor = $("#"+ gen_id + '-'+ dim_id +"-member-chooser-panel-" + dim_id).length > 0;
							}
							if (form_has_selctor) {

								task_members_json[dim_id] = [];
								// get the members grouped by type
								ots_data = mempath[dim_id];
								// foreach member type, process the members
								for (var ot_id in ots_data) {
									if (!isNaN(ot_id) && ots_data[ot_id] && ots_data[ot_id].length > 0) {
										// process the members of the current member tpye
										for (var x in ots_data[ot_id]) {
											// get the member id
											var m = ots_data[ot_id][x];
											// add the member id to the result
											task_members_json[dim_id].push(m);
										}
									}
								}
							}
                        }

						var keep_current_labor = false;
						if (hour_type_dim_id && current_labor_member_id > 0 && task_labor_member_id > 0
								&& String(current_labor_member_id) != String(task_labor_member_id)) {
							keep_current_labor = !confirm(lang('The selected task has a different labor category. Do you want to update it to the task\'s labor category?'));
						} else if (hour_type_dim_id && current_labor_member_id > 0 && !(task_labor_member_id > 0)) {
							// Task has no labor category: keep the one already selected in the form
							keep_current_labor = true;
						}

						var dims_to_exclude = excluded_dim_ids ? excluded_dim_ids.slice() : [];
						// remove_all_selections compares with parseInt(dim_id), so exclude list must be numeric
						if (keep_current_labor && hour_type_dim_id) {
							dims_to_exclude.push(hour_type_dim_id);
						}

                        // remove all members from timeslot
                        member_selector.remove_all_selections(gen_id, dims_to_exclude, true);

                        for(var dim_id in task_members_json){
							if (keep_current_labor && hour_type_dim_id && String(dim_id) == String(hour_type_dim_id)) {
								continue;
							}
                            var member_ids = task_members_json[dim_id];
                            for (var i=0; i<member_ids.length; i++) {
                                if(typeof member_ids[i] != 'function'){
                                    var mem_id = member_ids[i];
                                    member_selector.add_relation(dim_id, gen_id, mem_id, false, true);
                                }
                            }     
                        }

						// Ensure current labor remains selected when we chose to keep it
						if (keep_current_labor && hour_type_dim_id && current_labor_member_id > 0) {
							member_selector.add_relation(hour_type_dim_id, gen_id, current_labor_member_id, false, true);
						}

						og.enable_time_form_submit_button(); // enable submit button

                        if(hour_type_dim_id && !keep_current_labor) {
                            var contact_id = document.getElementById(gen_id+'timeslot_contact_id').value;
							var selector_genid = gen_id;
							if (!member_selector[selector_genid]) {
								// check if we have a separated selector for this dimension
								selector_genid = gen_id + '-' + hour_type_dim_id;
							}
							
                            if (member_selector[selector_genid] && member_selector[selector_genid].sel_context[hour_type_dim_id]
									&& member_selector[selector_genid].sel_context[hour_type_dim_id].length == 0){
								og.disable_time_form_submit_button(); // don't allow to submit form until next request returns

                                og.openLink(og.getUrl('hour_type', 'get_user_default_hour_type', {user_id: contact_id}), {
                                    hideLoading: true,
                                    callback: function(success, data) {
                                        if (data.member_id) {
                                            member_selector.add_relation(hour_type_dim_id, selector_genid, data.member_id);
                                        }
										og.enable_time_form_submit_button(); // enable submit button
                                    }
                                });
                            }

                        }
                        
                        og.setTimeslotIsBillableUsingTaskWrapper(task_id);
					} else {
						og.enable_time_form_submit_button(); // enable submit button
					}
				}
			});
		}
	}
    
    og.disable_time_form_submit_button = function() {
		$("#"+gen_id+"submit-edit-form button.submit").attr("disabled", "disabled").addClass("disabled");
	}
    
    og.enable_time_form_submit_button = function() {
		$("#"+gen_id+"submit-edit-form button.submit").removeClass("disabled").removeAttr("disabled");
	}
    
    
    var users_store = [];
<?php if( isset($users)){ foreach ($users as $u) { ?>
                users_store.push(['<?php echo $u->getId() ?>', '<?php echo clean(escape_character($u->getObjectName())) ?>']);
<?php }} ?>

    $(function() {
        $("#<?php echo $genid ?>tabs").tabs();
<?php if (can_manage_time(logged_user())) { ?>
                    var tsContactCombo = new Ext.form.ComboBox({
                        renderTo:'<?php echo $genid ?>timeslot_contact_combo_container',
                        name: 'ts_contact_id_combo',
                        id: '<?php echo $genid ?>timeslot_contact_id_combo',
                        value: '<?php echo $object->getContactId() ?>',
                        store: users_store,
                        displayField:'text',
                        mode: 'local',
                        cls: 'assigned-to-combo',
                        triggerAction: 'all',
                        selectOnFocus:true,
                        width: 244,
                        listWidth: 244,
                        listClass: 'assigned-to-combo-list',
                        valueField: 'value',
                        emptyText: (lang('select user') + '...'),
                        valueNotFoundText: ''
                    });
                    tsContactCombo.on('select', function(combo, selected, idx) {
                        combo = Ext.getCmp('<?php echo $genid ?>timeslot_contact_id_combo');
                        assignedto = document.getElementById('<?php echo $genid ?>timeslot_contact_id');
                        if (assignedto) assignedto.value = combo.getValue();
                        assigned_user = combo.getValue();

                        if (og.on_ts_contact_combo_select && og.on_ts_contact_combo_select.length > 0) {
                            var params = {genid: '<?php echo $genid?>', selected_user_id: combo.getValue(), combo: combo, task_id:'<?php echo $timeslot->getRelObjectId()?>'};
                            for (x in og.on_ts_contact_combo_select) {
                                if(x == 'remove') continue;
                                var fn = og.on_ts_contact_combo_select[x];
                            	if (typeof(fn) == 'function') {
                                    fn.call(null, params);
		                        }
                            }
                        }
                    });
<?php } ?>
    });
    
    $(document).ready(function (){
        og.updateLastTimeValue();
    })
    
    <?php 
    $is_calculated_estimated_price = $timeslot->getRelObject() ? $timeslot->getRelObject()->getColumnValue('is_calculated_estimated_price') : true;
    $is_fixed_fee = $timeslot->getRelObject() ? $timeslot->getRelObject()->getColumnValue('is_fixed_fee') : false;
    $task_non_billable = false;
    if(Plugins::instance()->isActivePlugin('advanced_billing')){
        $task = $timeslot->getRelObject();
        $task_non_billable = $task instanceof ProjectTask && $task->getColumnValue('is_billable') == 0;
    }
   
        
    if ((!$is_calculated_estimated_price && $is_fixed_fee) || $task_non_billable) { ?>
		og.disable_time_form_is_billable_input();
	<?php } ?>
            
</script>
