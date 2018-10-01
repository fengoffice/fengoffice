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
                    <?php echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
                </div>
                <div class="clear"></div>
            </div>
        </div>

        <div class="coInputMainBlock">
            <input type="hidden" name="object_id" value="<?php echo $timeslot->getRelObjectId()?>" />
            <input type="hidden" name="dont_reload" value="<?php echo isset($dont_reload) ? $dont_reload : '0'?>" />

            <div id="<?php echo $genid?>tabs" class="edit-form-tabs">
                <ul id="<?php echo $genid?>tab_titles">
                    <li><a href="#<?php echo $genid?>add_timeslot_details"><?php echo lang('details') ?></a></li>
                    <li><a href="#<?php echo $genid?>add_timeslot_related_to"><?php echo lang('related to') ?></a></li>

                    <?php if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
                    <li><a href="#<?php echo $genid?>add_timeslot_billing"><?php echo lang('billing') ?></a></li>
                    <?php } ?>
                    <?php foreach ($categories as $category) {
                        if (array_var($category, 'hidden')) continue;
                    ?>
                    <li><a href="#<?php echo $genid . array_var($category, 'id', $category['name']) ?>"><?php echo $category['name'] ?></a></li>
                    <?php } ?>
                </ul>

                <div id="<?php echo $genid ?>add_timeslot_details" class="editor-container form-tab">
                    <?php if (can_manage_time(logged_user())) { ?>
                        <div class="dataBlock">
                            <?php echo label_tag(lang('user')) ?>
                            <div id="<?php echo $genid?>timeslot_contact_combo_container" style="float:left;"></div>
                            <input type="hidden" id="<?php echo $genid?>timeslot_contact_id" name="timeslot[contact_id]" value="<?php echo $object->getContactId() ?>" />
                            <div class="clear"></div>
                        </div>
                    <?php } ?>

                    <div class="dataBlock" id="<?php echo $genid?>worked_time_container">
                        <div class="pull-left">
                        <?php echo label_tag(lang('worked time')) ?>
                        
                        <?php echo hour_field('timeslot[hours]', floor($timeslot->getMinutes() / 60),array("maxlength" => 4,
                                    "id" => "worked_time",
                                    "class" => "inputHours",
                                    "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')",
                                    "onchange" => "og.onchangeTimesInputs(this);",
                                    "placeholder" => "Hs")) ?>
                        <span style="margin: 0px 5px;">:</span>
                        <?php echo minute_field('timeslot[minutes]', $timeslot->getMinutes() % 60,array("maxlength" => 2,
                                    "id" => "worked_minute",
                                    "class" => "inputMinutes",
                                    "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, ''); if (event.target.value > 59) event.target.value = 59;",
                                    "onchange" => "og.onchangeTimesInputs(this);",
                                    "placeholder" => "Min")) ?>
                        
                        </div>
                        <a class="specify-link pull-left" style="line-height: 25px;<?php echo $time_preferences['show_paused_time'] ? '':'display:none;' ?>" onclick="og.toggle_specify_paused_time(this, '<?php echo $genid ?>')" href="#"><?php echo lang('specify paused time') ?></a>
                        <div class="dataBlock dataBlockRight" 
                             style="<?php if ($timeslot->getSubtract() == 0) echo 'display:none;' ?>"
                             id="<?php echo $genid?>paused_time_container">

                            <?php echo label_tag(lang('paused time')) ?>

                            <?php echo hour_field('timeslot[subtract_hours]', floor($timeslot->getSubtract() / 3600),array("maxlength" => 4,
                                        "id" => "paused_time",
                                        "class" => "inputHours",
                                        "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, '')",
                                        "onchange" => "og.onchangeTimesInputs(this);",
                                        "placeholder" => "Hs")) ?>
                            <span style="margin: 0px 5px;">:</span>
                            <?php echo minute_field('timeslot[subtract_minutes]', ($timeslot->getSubtract() / 60) % 60,array("maxlength" => 2,
                                        "id" => "paused_minute",
                                        "class" => "inputMinutes",
                                        "onkeyup" => "event.target.value = event.target.value.replace(/[^0-9]/g, ''); if (event.target.value > 59) event.target.value = 59;",
                                        "onchange" => "og.onchangeTimesInputs(this);",
                                        "placeholder" => "Min")) ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="dataBlock">
                        <div class="pull-left">
                            <?php 
                                echo label_tag(lang('start date'));
                                $tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);
                                $date = $timeslot->isNew() ? DateTimeValueLib::now() : new DateTimeValue($timeslot->getStartTime()->getTimestamp() + $tz_offset);		
                            ?>
                            <table>
                                <tr>
                                    <td>
                                        <?php 
                                            echo pick_date_widget_timeslot('timeslot[date]', $date, $genid, null, false,'date_input');
                                        ?>
                                    </td>
                                    <td style="padding-left: 5px">
                                        <?php 
                                            echo pick_time_widget_timeslot('timeslot[start_time]', $timeslot->isNew() ? null : $date, $genid,null,false,'start_time_input');
                                        ?>
                                    </td>
                                    <td style="padding-left: 5px">
                                        <a class="specify-link" onclick="og.toggle_specify_end_time(this, '<?php echo $genid ?>')" href="#"><?php echo lang('specify end date') ?></a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                    <div class="dataBlock dataBlockRight" style="display:none;" id="<?php echo $genid?>end_date_container">
                        <?php 
                            echo label_tag(lang('end date'));
                            $tz_offset = Timezones::getTimezoneOffsetToApply($timeslot);
                            $end_date = $timeslot->isNew() || !$timeslot->getEndTime() instanceof DateTimeValue ? null : 
                                    new DateTimeValue($timeslot->getEndTime()->getTimestamp() + $tz_offset);
                        ?>
                        <table>
                            <tr>
                                <td>
                                    <?php 
                                        echo pick_date_widget_timeslot('timeslot[end_date]', $end_date, $genid, null, false,'date_end_input');
                                    ?>
                                </td>
                                <td style="padding-left: 5px">
                                    <?php 
                                        echo pick_time_widget_timeslot('timeslot[end_time]', $timeslot->isNew() ? null : $end_date, $genid,null,false,'end_time_input');
                                    ?>
                                </td>
                            </tr>
                        </table>		
                    </div>
                        <div class="clearfix"></div>
                    </div>

                  
                    
                    <div class="dataBlock">
                        <?php echo label_tag(lang('description')) ?>
                        <?php echo textarea_field('timeslot[description]', $timeslot->getDescription(), array('class' => 'long'))?>
                    </div>
                  
                    <?php 
                        echo render_object_custom_properties($timeslot, null, null, 'visible_by_default');
                    ?>



                    <div class="dataBlock">
                        <?php echo label_tag(lang('task')) ?>
                    

                    <div id="contene" class="linked-objects-container">
                        <?php
                            $object_id = $timeslot->getRelObjectId();
                            if ($object_id){
                                $showLinkObjectTask = 'display:none;';
                                
                                $object_id = $timeslot->getRelObject()->getObjectId();
                                $object_name = $timeslot->getRelObject()->getObjectName();
                            }else{
                                $showObjectTask = 'display:none;';
                            }
                        ?>
                        
                        <a id="<?php echo $genid ?>before" class="add-linked-object " href="#" onclick="og.openObjectTaskPicker()" style="<?php echo $showLinkObjectTask ?>"><span class="action-ico ico-task"><?php echo lang('link task') ?></span></a>

                            
                            <div class="template-object-actions og-add-template-object ico-task" style="<?php echo $showObjectTask ?>">
                                <input id="object_id" type="hidden" name="object_id" value="<?php echo $object_id ?>" />
                                <input type="hidden" name="old_object_id" value="<?php echo $object_id ?>" />
                                <span class="name"><?php echo $object_name ?></span>
                                <a href="#" onclick="og.removeObjectTask(this.parentNode)" class="internalLink coViewAction ico-delete"><?php echo lang('remove') ?></a>
                            </div>
                                    
                    </div>

                    </div>




                </div>

                <div id="<?php echo $genid ?>add_timeslot_related_to" class="editor-container form-tab">
                    <?php 
                        $listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().')');
                        if ($timeslot->isNew()) {
                            render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $pre_selected_member_ids, array('select_current_context' => true, 'listeners' => $listeners, 'object' => $object), null, null, false);
                        } else {
                            render_member_selectors($timeslot->manager()->getObjectTypeId(), $genid, $timeslot->getMemberIds(), array('listeners' => $listeners, 'object' => $object), null, null, false);
                        } 
                    ?>
                    <div class="clear"></div>
                </div>

                <?php if (can_manage_billing(logged_user()) && !Plugins::instance()->isActivePlugin('advanced_billing')) { ?>
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
                echo submit_button($timeslot->isNew() ? lang('add timeslot') : lang('save changes'), 's', array('style'=>'margin-top:0px')); 
            }?>
        </div>
    </div>
</form>

<div id="modal-config-more" style="display: none;">
    <h2><?php echo lang('How should we adjust'); ?></h2>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="start" checked=""/>
        <label class="pull-left"><?php echo lang('Did you start sooner'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="end"/>
        <label class="pull-left"> <?php echo lang('Did you end later'); ?></label>
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
        <input class="pull-left" type="radio" name="user_config" value="start" checked=""/>
        <label><?php echo lang('Did you start later'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="end"/>
        <label><?php echo lang('Did you end sooner'); ?></label>
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
    <h2><?php echo lang('How do you prefer'); ?></h2>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="dates" checked=""/>
        <label><?php echo lang('Did you want change the date value'); ?></label>
        <div class="clearfix"></div>
    </div>
    <div class="row">
        <input class="pull-left" type="radio" name="user_config" value="times"/>
        <label><?php echo lang('Did you want change the time value'); ?></label>
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
    var edit_mode = "<?php echo $edit_mode; ?>";
    var edit_hours_mode = "<?php echo 0; ?>";
    var gen_id = "<?php echo $genid; ?>";
    var time_preferences =JSON.parse('<?php echo json_encode($time_preferences); ?>');
    var div;
    
    og.toggle_specify_end_time = function(input, genid) {
        $(input).hide();
        $("#"+genid+"end_date_container").slideToggle(100);
    }
    
    og.toggle_specify_paused_time = function(input, genid) {
        $(input).hide();
        $('#' + genid + "paused_time_container").slideToggle(100);
    }
    
    og._minutes = 1000 * 60;
    og._hours = og._minutes * 60;
    og._days = og._hours * 24;
    og._years = og._days * 365;
    og._lastTimeValue = 0;
    og._actualTimeValue = 0;
    
    og._lastHoursInputValue = 0;
    og._lastMinutesInputValue = 0;
    og._lastPausedHoursInputValue = 0;
    og._lastPausedMinutesInputValue = 0;
    
    /**
     * Automatic calculation when the time input change yours values
     */
    og.onchangeTimesInputs = function (self){
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
        
        og._actualTimeValue = horas_restar+minutos_restar;
        
        if(og._actualTimeValue > og._lastTimeValue){
            div= $('#modal-config-more').html();
        }else{
            div= $('#modal-config-less').html();
        }
        
        if(edit_mode){
            switch(time_preferences.automatic_calculation_time){
                case "1":
                    og.changeStartDate();
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
    }

    /**
     * Apply the change who the user decided
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
     * Apply the change who the user decided
     */
    og.applyDatesAction = function (){
        action = $('#user-config').find('input:radio[name="user_config"]:checked').val();
       // debugger;
        var actionValue = "3";
        if(action == 'dates'){
            actionValue = "1";
            if(og.whoNeedChange == 2){
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
     * Change automatic the start date
     */
    og.changeStartDate = function () {
        
        start_date = $('#date_input');
        start_time = $('#start_time_input');
        
        end_date = $('#date_end_input');
        end_time = $('#end_time_input');
        
        var now;
        if(end_date.val() == og.preferences.date_format_tip || end_time.val() =="hh:mm"){
            now = new Date();
        }else{
            var end_date_aux = og.getDateArray(end_date.val(),end_time.val());
            now = new Date(end_date_aux[0],end_date_aux[1],end_date_aux[2],end_date_aux[3],end_date_aux[4]);
        }
        hours = now.getHours();
        minutes = now.getMinutes(); 
        
        var d = new Date(now.getTime()-(og._actualTimeValue));
        var final_minutes = d.getMinutes();
        var final_end_minutes = now.getMinutes();
        if(d.getMinutes() < 10){
            final_minutes = '0'+final_minutes
        }
        if(now.getMinutes() < 10){
            final_end_minutes = '0'+final_end_minutes
        }
        if(og.preferences.time_format_use_24 == "0"){
            start_time.val(d.getHours()+':'+final_minutes+' AM');
            end_time.val(now.getHours()+':'+final_end_minutes+' AM');
            if(d.getHours() > 12){
                start_time.val((d.getHours()-12)+':'+final_minutes+' PM');
            }
            if(now.getHours() > 12){
                end_time.val((now.getHours()-12)+':'+final_end_minutes+' PM');
            }
        }else{
            start_time.val(d.getHours()+':'+final_minutes);
            end_time.val(now.getHours()+':'+final_end_minutes);
        }
        start_date.val(d.dateFormat(og.preferences.date_format));
        end_date.val(now.dateFormat(og.preferences.date_format));
        
    };

    /**
     * Change automatic the end date
     */
    og.changeEndDate = function () {
        
        start_date = $('#date_input');
        start_time = $('#start_time_input');
        
        end_date = $('#date_end_input');
        end_time = $('#end_time_input');

        var now;
        if(end_date.val() == og.preferences.date_format_tip || end_time.val() =="hh:mm"){
            now = new Date();
        }else{
            var start_date_aux = og.getDateArray(start_date.val(),start_time.val());
            now = new Date(start_date_aux[0],start_date_aux[1],start_date_aux[2],start_date_aux[3],start_date_aux[4]);
        }
        hours = now.getHours();
        minutes = now.getMinutes(); 
        
        var d = new Date(now.getTime()+(og._actualTimeValue));
        var final_minutes = d.getMinutes();
        var final_end_minutes = now.getMinutes();
        if(d.getMinutes() < 10){
            final_minutes = '0'+final_minutes
        }
        if(now.getMinutes() < 10){
            final_end_minutes = '0'+final_end_minutes
        }
        if(og.preferences.time_format_use_24 == "0"){
            end_time.val(d.getHours()+':'+final_minutes+' AM');
            start_time.val(now.getHours()+':'+final_end_minutes+' AM');
            if(d.getHours() > 12){
                end_time.val((d.getHours()-12)+':'+final_minutes+' PM');
            }
            if(now.getHours() > 12){
                start_time.val((now.getHours()-12)+':'+final_end_minutes+' PM');
            }
        }else{
            end_time.val(d.getHours()+':'+final_minutes);
            start_time.val(now.getHours()+':'+final_end_minutes);
        }
        end_date.val(d.dateFormat(og.preferences.date_format));
        start_date.val(now.dateFormat(og.preferences.date_format));

    };


    og.changeTimesInputs = function(){
        worked_time = $('#worked_time').val();
        worked_minute = $('#worked_minute').val();

        paused_time = $('#paused_time').val();
        paused_minute = $('#paused_minute').val();

        start_date = $('#date_input');
        start_time = $('#start_time_input');

        end_date = $('#date_end_input');
        end_time = $('#end_time_input');

        if(start_time.val() == 'hh:mm'){
            return true;
        }
        if(end_time.val() == 'hh:mm'){
            return true;
        }

        var start_aux = og.getDateArray(start_date.val(),start_time.val());
        var end_aux = og.getDateArray(end_date.val(),end_time.val());
        DateOne = new Date(start_aux[0],start_aux[1],start_aux[2],start_aux[3],start_aux[4]);
        DateTwo = new Date(end_aux[0],end_aux[1],end_aux[2],end_aux[3],end_aux[4]);

        var time_diff = DateTwo.getTime() - DateOne.getTime();

        if(paused_time != ""){
            time_diff -= (parseInt(paused_time) * og._minutes);
        }
        if(paused_minute !=""){
            time_diff -= (parseInt(paused_minute) * og._hours);
        }

        var final_minutes = (time_diff/og._minutes) % 60;
        var final_hours = parseInt((time_diff/og._minutes) / 60);

        $('#worked_time').val(final_hours);
        $('#worked_minute').val(final_minutes);
    };

    og.whoNeedChange = 0;
    og.onchangeDatesInputs = function (whoShow) {   //---------------------------------------------------------------------------------------------------------------------------------

        if(whoShow == 'timeslot[end_date]'){
            og.whoNeedChange = 1;
        }else{
            og.whoNeedChange = 2;
        }

        div = $('#modal-config-hours').html();

        var action = 0;
        if(time_preferences.automatic_calculation_start_time != 0){
            if(time_preferences.automatic_calculation_start_time == 1){
                action = 2;
                if(whoShow == 'timeslot[end_date]'){
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
                $modal = og.ExtModal.show({
                    title: '<?php echo lang("You changed the length of your time record"); ?>',
                    basecls: 'user-config-timeslots',
                    html: '<div id="user-config" class="user-config-timeslots-container">' + div + '</div>'
                });
                $modal.on('close', og.rollbackInputs);
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

    
    og.openObjectTaskPicker = function () {
        og.ObjectPicker.show(function (objs) {
            if (objs && objs.length > 0) {
                var obj = objs[0].data;
                if (obj.type != 'task') {
                    og.msg(lang("error"), lang("object type not supported"), 4, "err");
                } else {
                    og.addObjectTask(obj.object_id,obj.name);
                }
            }
        },'',{
            types: ['task'],
            selected_type: 'task'
        });
    };
    
    og.addObjectTask = function (object_id, object_name) {

        
        var Link = $('.add-linked-object:first');
        var Object = $('.og-add-template-object:first');
        
        Object.find('#object_id').val(object_id);
        Object.find('.name').text(object_name);
        
        Link.hide()
        Object.show();
        
    };

    og.removeObjectTask = function (element) {

        var Link = $('.add-linked-object:first');
        var Object = $('.og-add-template-object:first');
        
        Object.hide();
        Link.show();
        
        Object.find('#object_id').val(0);
        Object.find('.name').text('');
        
    }
    
    
    
    
    
    var users_store = [];
<?php foreach ($users as $u) { ?>
                users_store.push(['<?php echo $u->getId() ?>', '<?php echo clean(escape_character($u->getObjectName())) ?>']);
<?php } ?>

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
                    });
<?php } ?>
    });
    
    $(document).ready(function (){
        og.updateLastTimeValue();
    })
    
    
            
</script>
