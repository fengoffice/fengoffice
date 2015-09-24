<script>
	var cant_tips = 0;
	var tips_array = [];
	
	function addTip(div_id, title, bdy) {
		tips_array[cant_tips++] = new Ext.ToolTip({
			target: div_id,
	        html: bdy,
	        title: title,
	        hideDelay: 1500,
	        closable: true
		});
	}
</script>


<?php
$calendar_panel = TabPanels::instance()->findById('calendar-panel');
if ($calendar_panel instanceof TabPanel && $calendar_panel->getEnabled()) {

	$genid = gen_id();
	
	require_javascript('og/EventPopUp.js');
	require_javascript('og/CalendarFunctions.js');
	
	//$startday = date("d",mktime()) - (date("N", mktime()) %7);
	if (user_config_option("start_monday")) {
		$startday = date("j") - date("N")+1; // beginning of the week, monday
	} else {
		$startday = date("j") - date("w"); // beginning of the week, sunday
	}
	
	//user_config_option('show_two_weeks_calendar',null,logged_user()->getId())? $my_weeks = 2 : $my_weeks = 1 ;
	$my_weeks = 2;
	$endday = $startday + (7 * $my_weeks);
	$today = DateTimeValueLib::now()->add('h', logged_user()->getTimezone());
	$currentday = $today->getDay();
	$currentmonth = $today->getMonth();
	$currentyear = $today->getYear();
	
	$user_filter_myself = true;
	$user_filter = null;
	$w_option_user_filter = ContactWidgetOptions::instance()->getContactOption('calendar', logged_user()->getId(), 'filter_by_myself');
	if (array_var($w_option_user_filter, 'option')) {
		$user_filter_myself = array_var($w_option_user_filter, 'value');
	}
	if ($user_filter_myself) $user_filter = logged_user();
	
	$date_start = new DateTimeValue(mktime(0, 0, 0, $currentmonth, $startday, $currentyear));
	$date_end = new DateTimeValue(mktime(0, 0, 0, $currentmonth, $endday, $currentyear));

	$tmp_tasks = ProjectTasks::instance()->getRangeTasksByUser($date_start, $date_end, $user_filter, null, false, true);
	$birthdays = Contacts::instance()->getRangeContactsByBirthday($date_start, $date_end);
	
	$milestones = ProjectMilestones::getRangeMilestones($date_start, $date_end);
	
	$tasks = array();
	if($tmp_tasks) {
		foreach ($tmp_tasks as $task) {
			$tasks = array_merge($tasks, replicateRepetitiveTaskForCalendarRawTask($task, $date_start, $date_end));
		}
	}
	$use_24_hours = user_config_option('time_format_use_24');
	if($use_24_hours) $timeformat = 'G:i';
	else $timeformat = 'g:i A';
  
	// load the day we are currently viewing in the calendar

	$output ='';
	if (user_config_option("start_monday")) $firstday = (date("w", mktime(0,0,0,$currentmonth,1,$currentyear))-1) % 7;
	else $firstday = (date("w", mktime(0, 0, 0, $currentmonth, 1, $currentyear))) % 7; // Numeric representation of day of week.
	$lastday = date("t", mktime(0, 0, 0, $currentmonth, 1, $currentyear)); // # of days in the month
	
	$output .= "<table id=\"calendar\" border='0' style='width:100%;border-collapse:collapse' cellspacing='0' cellpadding='0'>\n";
	$day = date("d");
	$month = date("m");
	$year = date("Y");
	// Loop to render the calendar
	
	$can_add_event = ProjectEvent::canAdd(logged_user(), active_context());
	$output .= "<tr>";
	
	if(!user_config_option("start_monday")) {
		$output .= "    <th width='12.5%' align='center'>" .  lang('sunday short') . '</th>' . "\n";
	}
	$output .= '
	<th width="15%">' . lang('monday short') . '</th>
	<th width="15%">' . lang('tuesday short') . '</th>
	<th width="15%">' . lang('wednesday short') . '</th>
	<th width="15%">' . lang('thursday short') . '</th>
	<th width="15%">' . lang('friday short') . '</th>
	<th width="12.5%">' . lang('saturday short') . '</th>';
	
	if(user_config_option("start_monday")) {
		$output .= '<th width="12.5%">' . lang('sunday short') . '</th>';
	}
	$output .= '</tr>';
	

	$result = ProjectEvents::getRangeProjectEvents($date_start, $date_end, ($user_filter instanceof Contact ? $user_filter->getId() : -1),' 0 1 3');
	foreach ($result as $ev) {
		$result = array_merge($result, $ev->getRepetitiveInstances($date_start, $date_end));
	}
	
	if(!$result) $result = array();
	if(!empty($milestones)) $result = array_merge($result,$milestones );
	if(!empty($tasks)) $result = array_merge($result,$tasks );
	if(!empty($birthdays)) $result = array_merge($result, $birthdays );
	

    for ($week_index = 0;$week_index<$my_weeks; $week_index++) {
		$output .= '  <tr>' . "\n";
		for ($day_of_week = 0; $day_of_week < 7; $day_of_week++) {
			$i = $week_index * 7 + $day_of_week;
			$day_of_month = $i + $startday;
			// see what type of day it is
			if($currentyear == $year && $currentmonth == $month && $currentday == $day_of_month){
				$daytitle = 'todaylink';
			}else $daytitle = 'daylink';
			
			// if weekends override do this
			if( !user_config_option("start_monday") AND ($day_of_week==0 OR $day_of_week==6) AND $day_of_month <= $lastday AND $day_of_month >= 1){
				$daytype = "weekend";
			}elseif( user_config_option("start_monday") AND ($day_of_week==5 OR $day_of_week==6) AND $day_of_month <= $lastday AND $day_of_month >= 1){
				$daytype = "weekend";
			}elseif($day_of_month <= $lastday AND $day_of_month >= 1){
				$daytype = "weekday";
			}else{
				$daytype = "weekday_future";
			}

			// see what type of day it is
			if($currentyear == $year && $currentmonth == $month && $currentday == $day_of_month){
			  $daytitle = 'todaylink';
			  $daytype = "today";
			}elseif($day_of_month > $lastday OR $day_of_month < 1){
				$daytitle = 'extralink';
			}else $daytitle = 'daylink';
			// writes the cell info (color changes) and day of the month in the cell.
			$output .= "<td valign=\"top\" class=\"$daytype\" ";
			if ($day_of_month <= $lastday AND $day_of_month >= 1) { 
				$p = get_url('event', 'viewdate', array(
					'day' => $day_of_month,
					'month' => $month,
					'year' => $year,
					'view_type' => 'viewdate'
				));
				$t = get_url('event', 'add', array(
					'day' => $day_of_month,
					'month' => $month,
					'year' => $year
				));
				$w = $day_of_month;
				$dtv = DateTimeValueLib::make(0, 0, 0, $month, $day_of_month, $year);
			} elseif($day_of_month < 1) {
				$p = get_url('event', 'viewdate', array(
					'day' => $day_of_month,
					'month' => $month,
					'year' => $year,
					'view_type' => 'viewdate'
				));
				$t = get_url('event', 'day', array(
					'day' => $day_of_month,
					'month' => $month,
					'year' => $year
				));
				//$w = "&nbsp;";
				$w = idate('d', mktime(0, 0, 0, $month, 0, $year)) + $day_of_month;
				$dtv = DateTimeValueLib::make(0,0,0,$month,$day_of_month,$year);  
			} else {
				if ($day_of_month==$lastday+1) {
					$month++;
					if($month==13){
						$month = 1;
						$year++;
					}
				}
				$p = get_url('event', 'viewdate', array(
					'day' => $day_of_month - $lastday,
					'month' => $month,
					'year' => $year,
					'view_type' => 'viewdate'
				));
				$t = get_url('event', 'add', array(
					'day' => $day_of_month - $lastday,
					'month' => $month,
					'year' => $year
				));
				$w = $day_of_month - $lastday;
				$dtv = DateTimeValueLib::make(0,0,0,$month,$w,$year);
			}
			$loc = Localization::instance();
			
			$start_value = $dtv->format(user_config_option('date_format'));
			$popupTitle = lang('add event');
			$output .= "><div style='z-index:0; min-height:100px; height:100%;cursor:pointer' onclick=\"og.showEventPopup('".$dtv->getDay()."','".$dtv->getMonth()."','".$dtv->getYear()."',9,0,true,'".$start_value."', '".$genid."',1,true);\") >
			<div class='$daytitle' style='text-align:right'>";
			//if($day_of_month >= 1){
				$output .= "<a class='internalLink' href=\"$p\" onclick=\"og.disableEventPropagation(event);\"  style='color:#5B5B5B' >$w</a>";				
				// only display this link if the user has permission to add an event
				if(ProjectEvent::canAdd(logged_user(),active_context())){
					// if single digit, add a zero
					$dom = $day_of_month;
					if($dom < 10) $dom = "0".$dom;
					// make sure user is allowed to edit the past
						
				}
				
			//}else $output .= "&nbsp;";
			$output .= "</div>";
			// This loop writes the events for the day in the cell
			if (is_numeric($w)){
				
					
				if(count($result)<1) $output .= "&nbsp;";
				else{
					$count = 0;
					$to_show_len = 25;
					foreach($result as $event){
						
						if($event instanceof ProjectEvent ){
							
							$event_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
							$event_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
							
							if ($dtv->getTimestamp() == mktime(0,0,0, $event_start->getMonth(), $event_start->getDay(), $event_start->getYear()) ||
								$dtv->getTimestamp() == mktime(0,0,0, $event_duration->getMonth(), $event_duration->getDay(), $event_duration->getYear())) {
									
								
								$count++;
								// make the event subjects links or not according to the variable $whole_day in gatekeeper.php
								if($count <= 3){
									$ws_color = $event->getObjectColor($event instanceof ProjectEvent ? 1 : 12);
									cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
									
									$subject = clean($event->getSubject());
									$typeofevent = $event->getTypeId();
									$eventid = $event->getId();
							
									$output .= "<div class='nobr og-wsname-color-$ws_color' style='border-radius:4px;height:17px;margin:1px;padding:1px;z-index:1000;border: 1px solid;border-color:$border_color'>";
									if($subject=="") $subject = "[".lang('CAL_NO_SUBJECT')."]";
									if (function_exists('mb_strlen')) {
										$subject_toshow = mb_strlen($subject) < $to_show_len ? $subject : mb_substr($subject, 0, $to_show_len-3)."...";
									} else {
										$subject_toshow = strlen($subject) < $to_show_len ? $subject : substr($subject, 0, $to_show_len-3)."...";
									}
									$output .= "<span id='o_ev_div_" . $event->getId() . "'>";			
									$output .= "<a class=\"internalLink link-ico ico-event\" style='vertical-align:bottom;' href='" . get_url('event', 'view', array('id' => $event->getId())) . "' onclick=\"og.disableEventPropagation(event);\" >";
									$output .= $subject_toshow."</a>";
									$output .= '</span>';
									$output .= "</div>";
									
									$tip_text = str_replace("\r", '', $event->getTypeId() == 2 ? lang('CAL_FULL_DAY') : $event_start->format($use_24_hours ? 'G:i' : 'g:i A') .' - '. $event_duration->format($use_24_hours ? 'G:i' : 'g:i A') . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : ''));
									$tip_text = str_replace("\n", '<br>', $tip_text);
									if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
									?>
									<script >
										addTip('o_ev_div_<?php echo $event->getId() ?>', '<i>' + lang('event') + '</i> - '+<?php echo json_encode(clean($event->getSubject())) ?>, <?php echo json_encode($tip_text);?>);
									</script>
									<?php
								}
							}
						} elseif($event instanceof ProjectMilestone ){
							$milestone = $event;
							$due_date = new DateTimeValue($milestone->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
							if ($dtv->getTimestamp() == mktime(0,0,0,$due_date->getMonth(),$due_date->getDay(),$due_date->getYear())) {	
								$count++;
								if ($count <= 3){
									
									$ws_color = $event->getObjectColor($event instanceof ProjectEvent ? 1 : 12);
									cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
									
									$cal_text = clean($milestone->getName());
									if (function_exists('mb_strlen')) {
										$cal_text = mb_strlen($cal_text) < $to_show_len ? $cal_text : mb_substr($cal_text, 0, $to_show_len-3)."...";
									} else {
										$cal_text = strlen($cal_text) < $to_show_len ? $cal_text : substr($cal_text, 0, $to_show_len-3)."...";
									}
									$output .= "<div class='nobr og-wsname-color-$ws_color' style='border-radius:4px;height:17px;margin:1px;padding:1px;z-index:1000;border: 1px solid;border-color:$border_color'>";
									$output .= "<span id='o_ms_div_" . $milestone->getId() . "'>";
									$output .= "<a class=\"internalLink link-ico ico-milestone\" style='vertical-align:bottom;' href='".$milestone->getViewUrl()."' onclick=\"og.disableEventPropagation(event);\" >";
									$output .= $cal_text."</a>";
									$output .= '</span>';
									$output .= "</div>";
									
									$tip_text = str_replace("\r", '', (trim(clean($milestone->getDescription())) == '' ? '' : '<br><br>'. clean($milestone->getDescription())));
									$tip_text = str_replace("\n", '<br>', $tip_text);
									if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
									?>
									<script>
										addTip('o_ms_div_<?php echo $milestone->getId() ?>', '<i>' + lang('milestone') + '</i> - '+<?php echo json_encode(clean($milestone->getTitle())) ?>, <?php echo json_encode($tip_text)?>);
									</script>
									<?php
								}//if count
							}
							
						}//endif milestone
						//elseif($event instanceof ProjectTask){
						elseif(is_array($event)){
							//$task = $event;
							
							$start_of_task = false;
							$end_of_task = false;
							if ($event['due_date'] != EMPTY_DATETIME){
								$due_date = new DateTimeValue(strtotime($event['due_date']) + logged_user()->getTimezone() * 3600);
								if ($dtv->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear())) $end_of_task = true;
							}
							if ($event['start_date'] != EMPTY_DATETIME){
								$start_date = new DateTimeValue(strtotime($event['start_date']) + logged_user()->getTimezone() * 3600);
								if ($dtv->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear())) $start_of_task = true;
							}
							
							if ($start_of_task || $end_of_task) {
								$task = new ProjectTask();
								$task->setFromAttributes($event);
								$task->setId($event['id']);
								$task->setNew(false);
								
								if ($start_of_task && $end_of_task) {
									$tip_title = lang('task');
									$ico = "ico-task";
									$tip_pre = '';
								} else if ($end_of_task) {
									$tip_title = lang('end of task');
									$ico = "ico-task-end";
									$tip_pre = 'end_';
								} else {
									$tip_title = lang('start of task');
									$ico = "ico-task-start";
									$tip_pre = 'st_';
								}
								
								$count++;
								if ($count <= 3){
									$ws_color = $task->getObjectColor($task instanceof ProjectEvent ? 1 : 12);
									cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
									
									$cal_text = clean($task->getTitle());
									if (function_exists('mb_strlen')) {
										$cal_text = mb_strlen($cal_text) < $to_show_len ? $cal_text : mb_substr($cal_text, 0, $to_show_len-3)."...";
									} else {
										$cal_text = strlen($cal_text) < $to_show_len ? $cal_text : substr($cal_text, 0, $to_show_len-3)."...";
									}
									$output .= "<div class='nobr og-wsname-color-$ws_color' style='border-radius:4px;height:17px;margin:1px;padding:1px;z-index:1000;border: 1px solid;border-color:$border_color'>";
									$output .= "<span id='o_ta_div_$tip_pre" . $task->getId() . "'>";
									$output .= "<a class=\"internalLink link-ico $ico\" style='vertical-align:bottom;' href='".$task->getViewUrl()."' onclick=\"og.disableEventPropagation(event);\" >";
									$output .= $cal_text."</a>";
									$output .= '</span>';
									$output .= "</div>";
									
									$tip_text = str_replace("\r", '', lang('assigned to') .': '. clean($task->getAssignedToName()) . (trim(clean($task->getText())) == '' ? '' : '<br><br>'. clean(html_to_text($task->getText()))));
									$tip_text = str_replace("\n", '<br>', $tip_text);													
									if (strlen_utf($tip_text) > 200) $tip_text = substr_utf($tip_text, 0, strpos($tip_text, ' ', 200)) . ' ...';
									
									?>
									<script>
										addTip('o_ta_div_<?php echo $tip_pre ?>' + <?php echo $task->getId() ?>, '<i>' + '<?php echo $tip_title ?>' + '</i> - ' + <?php echo json_encode(clean($task->getTitle()))?>, <?php echo json_encode($tip_text);?>);
									</script>
									<?php
								}//if count
							}
						}//endif task
						elseif($event instanceof Contact){
							
							$contact = $event;
							$bday = $contact->getBirthday();
	
							$now = mktime(0, 0, 0, $dtv->getMonth(), $dtv->getDay(), $dtv->getYear());
							if ($bday){
								if ($now == mktime(0, 0, 0, $bday->getMonth(), $bday->getDay(), $dtv->getYear())) {
									$count++;
									if ($count <= 3){
		
										$output .= '<div class="nobr event_block"  id="m_bd_div_'.$contact->getId().'" style="border-left-color: #B1BFAC;">';
										$output .= "<a style='vertical-align:bottom;' href='".$contact->getViewUrl()."' onclick=\"og.disableEventPropagation(event);\" >";
										$output .= "<img src='".image_url('/16x16/contacts.png')."' style='vertical-align: middle;'>";
										$output .= "<span>".$contact->getDisplayName()."</span></a>";
										$output .= "</div>";
	
									?>
									<script>
										addTip('m_bd_div_<?php echo $contact->getId() ?>', '<i>' + '<?php echo escape_single_quotes(lang('birthday')) ?>' + '</i> - ' + <?php echo json_encode(clean($contact->getDisplayName()))?>, '');
									</script>
									<?php
									}//if count
								}
							}
						}//endif birthdays
					} // end foreach event writing loop
					if ($count > 3) {
						$output .= '<div style="witdh:100%;text-align:center;font-size:9px" ><a href="'.$p.'" class="internalLink"  onclick="og.disableEventPropagation(event);">'.($count-3) . ' ' . lang('more') .'</a></div>';
					}
				}
				
				$output .= '</div></td>';
			} //if is_numeric($w) 
		} // end weekly loop
		$output .= "\n  </tr>\n";
		// If it's the last day, we're done
		if($day_of_month >= $lastday+7) {
			break;
		}
	} // end main loop
	$output .= '</table>';
	
	$active_members = array();
	$context = active_context();
	foreach ($context as $selection) {
		if ($selection instanceof Member) $active_members[] = $selection;
	}
	if (count($active_members) > 0) {
		$mnames = array();
		$allowed_contact_ids = array();
		foreach ($active_members as $member) {
			$mnames[] = clean($member->getName());
		}
		$widget_title = lang('upcoming events milestones and tasks'). ' '. lang('in').' '. implode(", ", $mnames);
	}
	include_once 'template.php';

}