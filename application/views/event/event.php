<?php
require_javascript('og/modules/addMessageForm.js'); 
require_javascript('og/EventRelatedPopUp.js');
$genid = gen_id(); 
?>
<script>
	var genid = '<?php echo $genid ?>';

	og.cal_hide = function(id) {
		document.getElementById(id).style.display = "none";
	}

	og.cal_show = function(id) {
		document.getElementById(id).style.display = "block";
	}
	
	og.toggleDiv = function(div_id){
		var theDiv = document.getElementById(div_id);
		dis = !theDiv.disabled;
	    var theFields = theDiv.getElementsByTagName('*');
	    for (var i=0; i < theFields.length;i++) theFields[i].disabled=dis;
	    theDiv.disabled=dis;
	}
	
	og.changeRepeat = function() {
		og.cal_hide("cal_extra1");
		og.cal_hide("cal_extra2");
		og.cal_hide("cal_extra3");
		og.cal_hide("<?php echo $genid?>add_reminders_warning");
		if(document.getElementById("daily").selected){
			document.getElementById("word").innerHTML = '<?php echo escape_single_quotes(lang("days"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("weekly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("weeks"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("monthly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("months"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("yearly").selected){
			document.getElementById("word").innerHTML =  '<?php echo escape_single_quotes(lang("years"))?>';
			og.cal_show("cal_extra1");
			og.cal_show("cal_extra2");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		} else if(document.getElementById("holiday").selected){
			og.cal_show("cal_extra3");
			og.cal_show("<?php echo $genid?>add_reminders_warning");
		}
	}
	
	og.confirmEditRepEvent = function(ev_id, is_repetitive) {
		if (is_repetitive) {
			return confirm(lang('confirm repeating event edition'));
		}
		return true;
	}
	
	og.updateEventStartDate = function() {
		var picker = Ext.getCmp(genid + 'event[start_value]Cmp');
		var old_date = picker.getValue();
		var r_dow = Ext.get(genid + 'event[repeat_dow]').getValue();
		var r_wnum = Ext.get(genid + 'event[repeat_wnum]').getValue();
		
		var date = new Date();
		date.setMonth(old_date.getMonth());
		date.setFullYear(old_date.getFullYear());
		for (i=1; i<=7; i++) {
			date.setDate(i);
			if (date.getDay() + 1 == r_dow) break;
		}
		for (i = 1; i < r_wnum; i++) date.setDate(date.getDate() + 7);
		picker.setValue(date);
	}
	
	og.updateRepeatHParams = function() {
		var picker = Ext.getCmp(genid + 'event[start_value]Cmp');
		var orig_date = picker.getValue();
		var r_dow = document.getElementById(genid + 'event[repeat_dow]');
		var r_wnum = document.getElementById(genid + 'event[repeat_wnum]');
		if (r_dow && r_wnum) {
			var date = new Date();
			date.setMonth(orig_date.getMonth());
			date.setFullYear(orig_date.getFullYear());
			date.setDate(1);
			var first_dow = date.getDay();
			var wnum = date.getDay() == 0 ? -1 : 0;
			for (i=1; i<=orig_date.getDate(); i++) {
				date.setDate(i);
				if (date.getDay() == first_dow) wnum++;
			}
			if (wnum > 4) wnum = 4;
			
			r_dow.selectedIndex = orig_date.getDay();
			r_wnum.selectedIndex = wnum - 1;
		}
	}
	
</script>


<?php
/*
	Copyright (c) Reece Pegues
	sitetheory.com

    Reece PHP Calendar is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or 
	any later version if you wish.

    You should have received a copy of the GNU General Public License
    along with this file; if not, write to the Free Software
    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
$object = $event;

// on submit functions
if (array_var($_REQUEST, 'modal')) {
	$on_submit = "og.submit_modal_form('".$genid."submit-edit-form'); return false;";
} else {
	$on_submit = "return true;";
}


$day =  array_var($event_data, 'day');
$month =  array_var($event_data, 'month');
$year =  array_var($event_data, 'year');
$all = true;
if (active_project()!= null)
	$all = false;	
$filter_user = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();

$use_24_hours = user_config_option('time_format_use_24');

// get dates
$setlastweek='';
$pm = 0;
if($event->isNew()) { 
	$username = '';
	$desc = '';
	
	// if adding event to today, make the time current time.  Else just make it 6PM (you can change that)
	if( "$year-$month-$day" == date("Y-m-d") ) $hour = date('G') + 1;
	else $hour = 18;
	// organize time by 24-hour or 12-hour clock.
	$pm = 0;
	if(!$use_24_hours) {
		if($hour >= 12) {
			$hour = $hour - 12;
			$pm = 1;
		}
	}
	// set default minute and duration times.
	$minute = 0;
	$durhr = 1;
	$durday = 0;
	$durmin = 0;
	// set other defaults
	$rjump = 1;
	// set type of event to default of 1 (nothing)
	$typeofevent = 1;
}


if($event->isNew()) {
	$form_view_url = get_url('event', 'add')."&view=". array_var($_GET, 'view','month');
} else {
	$form_view_url = $event->getEditUrl()."&view=". array_var($_GET, 'view','month');
} 

$categories = array();
Hook::fire('object_edit_categories', $object, $categories);

$has_custom_properties = CustomProperties::countAllCustomPropertiesByObjectType($object->getObjectTypeId()) > 0;

?>
	<form id="<?php echo $genid ?>submit-edit-form" class="add-event" onsubmit="<?php echo $on_submit?>" class="internalForm" action="<?php echo $form_view_url; ?>" method="post">
	<input type="hidden" id="event[pm]" name="event[pm]" value="<?php echo $pm?>">
	<input id="<?php echo $genid?>view_related" type="hidden" name="view_related" value="<?php echo isset($event_related) ? $event_related : ""; ?>" />
	<input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
	<div class="event">
	<div class="coInputHeader">
	
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo $event->isNew() ? lang('new event') : lang('edit event') ?>
		</div>
	  </div>
	
	  <div>
		<div class="coInputName">
		<?php echo text_field('event[name]', array_var($event_data, 'name'), 
	    		array('class' => 'title', 'id' => 'eventSubject', 'maxlength' => '100', 'placeholder' => lang('type name here'))); ?>
		</div>
			
		<div class="coInputButtons">
		<?php
			$is_repetitive = $event->isRepetitive() ? 'true' : 'false'; 
			echo submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('style'=>'margin-top:0px;margin-left:10px', 'onclick' => (!$event->isNew() ? "javascript:if(!og.confirmEditRepEvent('".$event->getId()."',$is_repetitive)) return false;" : '')));
		?>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
	
		
	<div class="coInputMainBlock">	
		<input id="<?php echo $genid?>updated-on-hidden" type="hidden" name="updatedon" value="<?php echo $event->isNew() ? '' : $event->getUpdatedOn()->getTimestamp() ?>">
		<input id="<?php echo $genid?>merge-changes-hidden" type="hidden" name="merge-changes" value="" >
		<input id="<?php echo $genid?>genid" type="hidden" name="genid" value="<?php echo $genid ?>" >
		
		<div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>time_and_duration"><?php echo lang('basic data') ?></a></li>
			<li><a href="#<?php echo $genid?>add_more_details_div"><?php echo lang('more details') ?></a></li>
			
			<li><a href="#<?php echo $genid?>add_event_invitation_div"><?php echo lang('event invitations') ?></a></li>
			
			<li><a href="#<?php echo $genid?>add_subscribers_div"><?php echo lang('object subscribers') ?></a></li>
			
			<?php foreach ($categories as $category) { ?>
			<li><a href="#<?php echo $genid . $category['name'] ?>"><?php echo $category['name'] ?></a></li>
			<?php } ?>
		</ul>
		
		<div id="<?php echo $genid?>time_and_duration" class="form-tab">
		
		  <div id="<?php echo $genid ?>add_event_select_context_div">
			<?php
			$listeners = array('on_selection_change' => 'og.reload_subscribers("'.$genid.'",'.$object->manager()->getObjectTypeId().'); og.redrawPeopleList("'.$genid.'");');
			if ($event->isNew()) {
				render_member_selectors($event->manager()->getObjectTypeId(), $genid, null, array('select_current_context' => true, 'listeners' => $listeners), null, null, false);
			} else {
				render_member_selectors($event->manager()->getObjectTypeId(), $genid, $event->getMemberIds(), array('listeners' => $listeners), null, null, false);
			} 
			?>
		  </div>
		  
		  
		  <div class="dataBlock" style="clear: both;">
			<?php echo label_tag(lang('CAL_DATE')) ?>
			<?php
				$tmph = array_var($event_data, 'hour') == -1 ? 0 : array_var($event_data, 'hour');
				$tmpm = array_var($event_data, 'minute') == -1 ? 0 : array_var($event_data, 'minute');
				$dv_start = DateTimeValueLib::make($tmph, $tmpm, 0, $month, $day, $year);
				$event->setStart($dv_start);
				echo pick_date_widget2('event[start_value]', $event->getStart(), $genid, 120); ?>
		  </div>
		  <div class="clear"></div>
		  
		  <div class="dataBlock">
			<?php echo label_tag(lang('CAL_TIME')) ?>
			<?php
				$hr = array_var($event_data, 'hour');
			 	$minute = array_var($event_data, 'minute');
				$is_pm = array_var($event_data, 'pm');
				$time_val = "$hr:" . str_pad($minute, 2, '0') . ($use_24_hours ? '' : ' '.($is_pm ? 'PM' : 'AM'));
				echo pick_time_widget2('event[start_time]', $time_val, $genid, 130);
			?>
		  </div>
		  <div class="clear"></div>
		  
		  <div class="dataBlock">
			<?php echo label_tag(lang('CAL_DURATION')) ?>
			<div id="<?php echo $genid ?>ev_duration_div">
				<select name="event_durationhour" size="1" onchange="document.getElementById('<?php echo $genid?>hf_dhour').value=this.options[this.selectedIndex].value;"><?php
				for($i = 0; $i < 24; $i++) {
					echo "<option value='$i'";
					if(array_var($event_data, 'durationhour')== $i) echo ' selected="selected"';
					echo ">$i</option>\n";
				}
				
				?></select> <?php echo lang('CAL_HOURS') ?> <select name="event_durationmin" size="1" onchange="document.getElementById('<?php echo $genid?>hf_dmin').value=this.options[this.selectedIndex].value;"><?php
				
					// print out the duration minutes drop down
					$durmin = array_var($event_data, 'durationmin');
					for($i = 0; $i <= 59; $i = $i + 15) {
						echo "<option value='$i'";
						if($durmin >= $i && $i > $durmin - 15) echo ' selected="selected"';
						echo sprintf(">%02d</option>\n", $i);
					}
					?>
				</select> 
			</div>
		  </div>
		  <input type="hidden" name="event[durationhour]" id="<?php echo $genid?>hf_dhour" value="<?php echo array_var($event_data, 'durationhour') ?>" />
		  <input type="hidden" name="event[durationmin]" id="<?php echo $genid?>hf_dmin" value="<?php echo array_var($event_data, 'durationmin') ?>" />
		  <div class="clear"></div>
		  
		  <div class="dataBlock">
			<?php echo label_tag(lang('CAL_FULL_DAY')) ?>
			<input type="checkbox" name="event_type_id" <?php echo (array_var($event_data, 'typeofevent', 1) == 2 ? 'checked="checked"' : '');?> 
				onchange="og.toggleDiv('<?php echo $genid?>event[start_time]'); og.toggleDiv('<?php echo $genid?>ev_duration_div'); document.getElementById('<?php echo $genid?>hf_type').value=(this.checked ? 2 : 1);" />
			<input type="hidden" name="event[type_id]" id="<?php echo $genid?>hf_type" value="<?php echo array_var($event_data, 'typeofevent', 1) ?>" />
		  </div>
		  <div class="clear"></div>
		  
		  <div id="<?php echo $genid ?>add_event_description_div" class="dataBlock">
		    <?php echo label_tag(lang('description')) ?>
			<?php echo textarea_field('event[description]',array_var($event_data, 'description'), array('id' => 'descriptionFormText', 'rows' => '5', 'style' => "width:500px;"));?>
		  </div>
		  <div class="clear"></div>
		  
		<?php if ($has_custom_properties || config_option('use_object_properties')) { ?>
		  <div id="<?php echo $genid ?>add_custom_properties_div">
			<?php echo render_object_custom_properties($object, false) ?>
			<?php echo render_add_custom_properties($object);?>
		  </div>
		<?php } ?>		  
		  
		</div>
		
		<div id="<?php echo $genid ?>add_more_details_div"  class="form-tab">
		
		  <div class="reminders-div sub-section-div" style="border-top:0px none;">
			<h2><?php echo lang('object reminders')?></h2>
			<div id="<?php echo $genid ?>add_reminders_content">
				<div id="<?php echo $genid ?>add_reminders_warning" class="desc" style="display:none;">
					<?php echo lang('reminders will not apply to repeating events') ?>
				</div>
				<?php echo render_add_reminders($object, "start", null, null, "event");?>
			</div>
		  </div>
		
		  <div class="repeat-options-div sub-section-div">
			<h2><?php echo lang('CAL_REPEATING_EVENT')?></h2>
			<div id="<?php echo $genid ?>event_repeat_options_div">
<?php 
			$occ = array_var($event_data, 'occ'); 
			$rsel1 = array_var($event_data, 'rsel1'); 
			$rsel2 = array_var($event_data, 'rsel2'); 
			$rsel3 = array_var($event_data, 'rsel3'); 
			$rnum = array_var($event_data, 'rnum'); 
			$rend = array_var($event_data, 'rend');
			
			// calculate what is visible given the repeating options
			$hide = '';
			$hide2 = (isset($occ) && $occ == 6)? '' : "display: none;";
			if((!isset($occ)) OR $occ == 1 OR $occ=="6" OR $occ=="") $hide = "display: none;";
			// print out repeating options for daily/weekly/monthly/yearly repeating.
			if(!isset($rsel1)) $rsel1=true;
			if(!isset($rsel2)) $rsel2="";
			if(!isset($rsel3)) $rsel3="";
			if(!isset($rnum) || $rsel2=='') $rnum="";
			if(!isset($rend) || $rsel3=='') $rend="";
			if(!isset($hide2) ) $hide2="";?>
			
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top" style="padding-bottom:6px">
						<table>
							<tr>
								<td align="left" valign="top" style="padding-bottom:6px">
									<?php echo lang('CAL_REPEAT')?> 
										<select name="event[occurance]" onChange="og.changeRepeat()">
											<option value="1" id="today"<?php if(isset($occ) && $occ == 1) echo ' selected="selected"'?>><?php echo lang('CAL_ONLY_TODAY')?></option>
											<option value="2" id="daily"<?php if(isset($occ) && $occ == 2) echo ' selected="selected"'?>><?php echo lang('CAL_DAILY_EVENT')?></option>
											<option value="3" id="weekly"<?php if(isset($occ) && $occ == 3) echo ' selected="selected"'?>><?php echo lang('CAL_WEEKLY_EVENT')?></option>
											<option value="4" id="monthly"<?php if(isset($occ) && $occ == 4) echo ' selected="selected"'?>><?php echo lang('CAL_MONTHLY_EVENT') ?></option>
											<option value="5" id="yearly"<?php if(isset($occ) && $occ == 5) echo  ' selected="selected"'?>><?php echo lang('CAL_YEARLY_EVENT') ?></option>
											<option value="6" id="holiday"<?php if(isset($occ) && $occ == 6)  echo ' selected="selected"'?>><?php echo lang('CAL_HOLIDAY_EVENT') ?></option>
										</select>
									<?php if (isset($occ) && $occ > 1 && $occ < 6){ ?>
									<script>
										og.changeRepeat();
									</script>
									<?php } ?>
								</td>
							</tr>
						</table>
					</td>
					</tr><tr>
					<td>
						<div id="cal_extra2" style="width: 400px; align: center; text-align: left; <?php echo $hide ?>">
							<div id="cal_extra1" style="<?php echo $hide ?>">
								<?php echo lang('CAL_EVERY') ."&nbsp;". text_field('event[occurance_jump]',array_var($event_data, 'rjump', '1'), array('class' => 'title','size' => '2', 'maxlength' => '100', 'style'=>'width:25px')) ?>
								<span id="word"></span>
							</div>
							<table>
							<script type="text/javascript">
								og.selectRepeatMode = function(mode) {
									var id = '';
									if (mode == 1) id = 'repeat_opt_forever';
									else if (mode == 2) id = 'repeat_opt_times';
									else if (mode == 3) id = 'repeat_opt_until';
									if (id != '') {
										el = document.getElementById('<?php echo $genid ?>'+id);
										if (el) el.checked = true;
									} 
								}
								og.viewDays = function(view) {
									var btn = Ext.get('<?php echo $genid ?>repeat_days');
									if(view){
										btn.dom.style.display = 'block';
									}else{
										btn.dom.style.display = 'none';
									}
								}
							</script>
							
								<tr><td colspan="2" style="vertical-align:middle; height: 22px;">
									<?php echo radio_field('event[repeat_option]',$rsel1,array('id' => $genid.'repeat_opt_forever','value' => '1', 'onclick' => 'og.viewDays(false)')) ."&nbsp;". lang('CAL_REPEAT_FOREVER')?>
								</td></tr>
								<tr><td colspan="2" style="vertical-align:middle">
									<?php echo radio_field('event[repeat_option]',$rsel2,array('id' => $genid.'repeat_opt_times','value' => '2', 'onclick' => 'og.viewDays(true)')) ."&nbsp;". lang('CAL_REPEAT');
									echo "&nbsp;" . text_field('event[repeat_num]', $rnum, array('size' => '3', 'id' => 'repeat_num', 'maxlength' => '3', 'style'=>'width:25px', 'onchange' => 'og.selectRepeatMode(2);')) ."&nbsp;" . lang('CAL_TIMES') ?>
								</td></tr>
								<tr><td style="vertical-align:middle">
									<?php echo radio_field('event[repeat_option]',$rsel3,array('id' => $genid.'repeat_opt_until','value' => '3', 'onclick' => 'og.viewDays(false)')) ."&nbsp;". lang('CAL_REPEAT_UNTIL');?>
								</td><td style="padding-left:8px;">
									<?php echo pick_date_widget2('event[repeat_end]', $rend, $genid, 95);?>
								</td></tr>
							</table>
						</div>
						<div id="cal_extra3" style="width: 400px; align: center; text-align: left; <?php echo $hide2 ?>'">
							<?php
								echo lang('CAL_REPEAT') . "&nbsp;";
								$options = array(
									option_tag(lang('1st'), 1, array_var($event_data, 'repeat_wnum') == 1 ? array("selected" => "selected") : null),
									option_tag(lang('2nd'), 2, array_var($event_data, 'repeat_wnum') == 2 ? array("selected" => "selected") : null),
									option_tag(lang('3rd'), 3, array_var($event_data, 'repeat_wnum') == 3 ? array("selected" => "selected") : null),
									option_tag(lang('4th'), 4, array_var($event_data, 'repeat_wnum') == 4 ? array("selected" => "selected") : null),
								);
								echo select_box('event[repeat_wnum]', $options, array("id" => $genid."event[repeat_wnum]", "onchange" => "og.updateEventStartDate();"));
								
								$options = array(
									option_tag(lang('sunday'), 1, array_var($event_data, 'repeat_dow') == 1 ? array("selected" => "selected") : null),
									option_tag(lang('monday'), 2, array_var($event_data, 'repeat_dow') == 2 ? array("selected" => "selected") : null),
									option_tag(lang('tuesday'), 3, array_var($event_data, 'repeat_dow') == 3 ? array("selected" => "selected") : null),
									option_tag(lang('wednesday'), 4, array_var($event_data, 'repeat_dow') == 4 ? array("selected" => "selected") : null),
									option_tag(lang('thursday'), 5, array_var($event_data, 'repeat_dow') == 5 ? array("selected" => "selected") : null),
									option_tag(lang('friday'), 6, array_var($event_data, 'repeat_dow') == 6 ? array("selected" => "selected") : null),
									option_tag(lang('saturday'), 7, array_var($event_data, 'repeat_dow') == 7 ? array("selected" => "selected") : null),
								);
								echo select_box('event[repeat_dow]', $options, array("id" => $genid."event[repeat_dow]", "onchange" => "og.updateEventStartDate();"));
								echo "&nbsp;" . lang('every') . "&nbsp;";
								$options = array();
								for ($i=1; $i<=12; $i++) {
									$options[] = option_tag("$i", $i, array_var($event_data, 'repeat_mjump') == $i ? array("selected" => "selected") : null);
								}
								echo select_box('event[repeat_mjump]', $options, array("id" => $genid."event[repeat_mjump]"));
								echo "&nbsp;" . lang('months');
							?>
						</div>
					</td>
				</tr>
                                <tr id="<?php echo $genid ?>repeat_days" style="display: none;">
                                    <td>
                                        <table>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[repeat_saturdays]"/>
                                                    <?php echo lang('repeat on saturdays')?>                                                
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[repeat_sundays]"/>
                                                    <?php echo lang('repeat on sundays')?>                                                
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input class="checkbox" type="checkbox" value="1" name="event[working_days]"/>
                                                    <?php echo lang('repeat working days')?>                                                
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
			</table>
		</div>
	  </div>
	  
	  <div class="linked-objects-div sub-section-div">
		<h2><?php echo lang('linked objects')?></h2>
		<div id="<?php echo $genid ?>add_linked_objects_div">
		  <?php echo render_object_link_form($object) ?>
  		</div>
	  </div>
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
	

	<div id="<?php echo $genid ?>add_event_invitation_div" class="og-add-subscribers form-tab">
	
		<div class="dataBlock">
			<p><?php echo lang('event invitations desc') ?></p>
			<p><?php $event_send_invitations = (user_config_option("event_send_invitations") && $event->isNew()) ? true : false;
					 $event_subscribe_invited = (user_config_option("event_subscribe_invited") && $event->isNew()) ? true : false;
					 echo checkbox_field('event[send_notification]', array_var($event_data, 'send_notification', $event_send_invitations), array('id' => $genid . 'eventFormSendNotification')) ?>
			<label for="<?php echo $genid ?>eventFormSendNotification" class="checkbox"><?php echo lang('send new event notification') ?></label></p>
			
			<div class="clear"></div>
			<p><?php echo checkbox_field('event[subscribe_invited]', array_var($event_data, 'subscribe_invited', $event_subscribe_invited), array('id' => $genid . 'eventFormSubscribeInvited')) ?>
			<label for="<?php echo $genid ?>eventFormSubscribeInvited" class="checkbox"><?php echo lang('subscribe invited users') ?></label></p>
		</div>
		<div class="clear"></div>
		
		<div id="emailNotification">
		<?php // ComboBox for Assistance confirmation 
			if (!$event->isNew()) {
				$event_invs = $event->getInvitations();
				if (isset($event_invs[$filter_user])) {
					$event_inv_state = $event_invs[$filter_user]->getInvitationState();
				} else {
					$event_inv_state = -1;
				}
				
				if ($event_inv_state != -1) {
					$options = array(
						option_tag(lang('yes'), 1, ($event_inv_state == 1)?array('selected' => 'selected'):null),
						option_tag(lang('no'), 2, ($event_inv_state == 2)?array('selected' => 'selected'):null),
						option_tag(lang('maybe'), 3, ($event_inv_state == 3)?array('selected' => 'selected'):null)
					);
					if ($event_inv_state == 0) {
						$options[] = option_tag(lang('decide later'), 0, ($event_inv_state == 0) ? array('selected' => 'selected'):null);
					}
					?>
					<table><tr><td style="padding-right: 6px;"><label for="eventFormComboAttendance" class="combobox"><?php echo lang('confirm attendance') ?></label></td><td>
					<?php echo select_box('event[confirmAttendance]', $options, array('id' => 'eventFormComboAttendance'));?>
					</td></tr></table>	
			<?php	} //if			
			} // if ?>
		</div>
		<div class="clear"></div>
		
	</div>	
	



	<?php foreach ($categories as $category) { ?>
	<div id="<?php echo $genid . $category['name'] ?>" class="form-tab">
		<?php echo $category['content'] ?>
	</div>
	<?php } ?>

	<input type="hidden" name="cal_origday" value="<?php echo $day?>">
	<input type="hidden" name="cal_origmonth" value="<?php echo $month?>">
	<input type="hidden" name="cal_origyear" value="<?php echo $year?>">


	</div>
	<?php if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($event->isNew() ? lang('add event') : lang('save changes'),'e',array('onclick' => (!$event->isNew() ? "javascript:if(!og.confirmEditRepEvent('".$event->getId()."',$is_repetitive)) return false;" : ''))); 
	}?>
  </div>
</form>

<script>
var is_new_event = <?php echo $event->isNew() ? '1' : '0'?>;
og.eventInvitationsUserFilter = '<?php echo $filter_user ?>';

og.drawInnerHtml = function(companies) {
	var htmlStr = '';
	var script = "";
	var genid = Ext.id();
	htmlStr += '<div id="' + genid + 'invite_companies"></div>';
	htmlStr += '&nbsp;';
	script += 'var div = Ext.getDom(genid + \'invite_companies\');';
	script += 'div.invite_companies = {};';
	script += 'var cos = div.invite_companies;';
	htmlStr += '<div class="company-users">';
	if (companies != null) {
		var calendar_user_filter = <?php echo user_config_option('calendar user filter'); ?>;
		for (i = 0; i < companies.length; i++) {
			comp_id = companies[i].object_id;
			comp_name = companies[i].name;
			comp_img = companies[i].logo_url;			
			script += 'cos.company_' + comp_id + ' = {id:\'' + genid + 'inviteCompany' + comp_id + '\', checkbox_id : \'inviteCompany' + comp_id + '\',users : []};';
			
			htmlStr += '<div onclick="App.modules.addMessageForm.emailNotifyClickCompany('+comp_id+',\'' + genid + '\',\'invite_companies\', \'invitation\')" class="company-name container-div" onmouseover="og.rollOver(this)" onmouseout="og.rollOut(this,true ,true)" >';
			
			htmlStr += '<div class="contact-picture-container" style="float:left;padding-top:3px;">' +
				(comp_id > 0 ? '<img class="commentUserAvatar" src="'+comp_img+'" alt="'+og.clean(comp_name)+'" />' : '') +'</div>' +
				'<label style="float:left;padding-left:5px;" for="'+comp_id+'">' +
				'<span class="ico-company link-ico">'+og.clean(comp_name)+'</span>' + '</label><div class="clear"></div>';
			
			htmlStr += '<input type="checkbox" style="display:none;" name="event[invite_company_'+comp_id+']" id="' + genid + 'inviteCompany'+comp_id+'" ></input>';
			
			htmlStr += '</div>';
			
			htmlStr += '<div class="company-users" style="padding-left:10px;">';
			for (j = 0; j < companies[i].users.length; j++) {
				usr = companies[i].users[j];
				var cls = (usr.invited || (is_new_event && usr.id == calendar_user_filter) ? 'checked-user' : 'user-name');
				htmlStr += '<div id="div' + genid + 'inviteUser'+usr.id+'" class="container-div '+cls+'" style="margin-left:5px;" onmouseover="og.rollOver(this)" onmouseout="og.rollOut(this,false ,true)" onclick="og.checkUser(this)">'

				htmlStr += '<input id="'+genid+'inviteUser'+usr.id+'" type="hidden" name="event[invite_user_'+usr.id+']" value="'+(usr.invited || (is_new_event && usr.id == calendar_user_filter)?'1':'0')+'" />';

				htmlStr += '<div class="contact-picture-container" style="float:left;padding-top:3px;">' +
					'<img class="commentUserAvatar" src="'+ og.allUsers[usr.id].img_url +'" alt="'+og.clean(usr.name)+'" /></div>';
				
				htmlStr += '<label for="' + genid + 'notifyUser' + usr.id + '" style="float:left; width: 125px; min-width:0px; overflow:hidden; padding-left: 5px;>' +
					'<span class="ico-user link-ico">'+og.clean(usr.name)+'</span><br>' +
					'<span style="color:#888888;font-size:90%;font-weight:normal;">'+ usr.mail+ '</span></label>';
				
				script += 'cos.company_' + comp_id + '.users.push({ id:'+usr.id+', checkbox_id : \'inviteUser' + usr.id + '\'});';
				htmlStr += '</div>';
			}
			htmlStr += '</div>';
		}
		htmlStr += '</div>';
	}
	Ext.lib.Event.onAvailable(genid + 'invite_companies', function() {
		eval(script);
	});
	return htmlStr;
};

og.drawUserList = function(success, data) {
	var companies = data.companies;

	var inv_div = Ext.get('<?php echo $genid ?>inv_companies_div');
	if (inv_div != null) inv_div.remove();
	inv_div = Ext.get('emailNotification');
	
	if (inv_div != null) {
		inv_div.insertHtml('beforeEnd', '<div id="<?php echo $genid ?>inv_companies_div">' + og.drawInnerHtml(companies) + '</div>');	
		if (Ext.isIE) inv_div.update(Ext.getDom("emailNotification").innerHTML, true);
	}
};

og.redrawPeopleList = function(genid){
	var dimension_members_json = Ext.util.JSON.encode(member_selector[genid].sel_context);
	og.openLink(og.getUrl('event', 'allowed_users_view_events', {context:dimension_members_json, user:og.eventInvitationsUserFilter, evid:<?php echo $event->isNew() ? 0 : $event->getId()?>}), {callback:og.drawUserList});
};


Ext.getCmp(genid + 'event[start_value]Cmp').on({
	change: og.updateRepeatHParams
});


Ext.get('eventSubject').focus();
<?php if (array_var($event_data, 'typeofevent') == 2) echo 'og.toggleDiv(\''.$genid.'event[start_time]\'); og.toggleDiv(\''.$genid.'ev_duration_div\');'; ?>

Ext.extend(og.EventRelatedPopUp, Ext.Window, {
	accept: function() {
		this.close();
	}
});

$(document).ready(function() {
    if($("#<?php echo $genid?>view_related").val()){
        this.dialog = new og.EventRelatedPopUp();
        this.dialog.setTitle(lang('events related'));
        this.dialog.show();      
    }
});

function selectEventRelated(val){
    $("#<?php echo $genid?>type_related").val(val);
}

$(function() {
	$("#<?php echo $genid?>tabs").tabs();
	og.redrawPeopleList('<?php echo $genid?>');
});
</script>