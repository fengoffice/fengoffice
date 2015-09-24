<?php 
require_javascript('og/CalendarToolbar.js');
require_javascript('og/CalendarFunctions.js');
require_javascript('og/EventPopUp.js');
require_javascript('og/CalendarPrint.js');
require_javascript('og/EventRelatedPopUp.js');
$genid = gen_id();

$max_events_to_show = user_config_option('displayed events amount');
if (!$max_events_to_show) $max_events_to_show = 3;
?>

<script>
	scroll_to = -1;
	og.ev_cell_dates = [];
	og.events_selected = 0;
	og.eventSelected(0);
        og.config.genid = '<?php echo $genid ?>';
</script>

<?php
	
	define('PX_HEIGHT',42);
	$year = isset($_GET['year']) ? $_GET['year'] : (isset($_SESSION['year']) ? $_SESSION['year'] : date('Y'));
	$month = isset($_GET['month']) ? $_GET['month'] : (isset($_SESSION['month']) ? $_SESSION['month'] : date('n'));
	$day = isset($_GET['day']) ? $_GET['day'] : (isset($_SESSION['day']) ? $_SESSION['day'] : date('j'));
	
	$_SESSION['year'] = $year;
	$_SESSION['month'] = $month;
	$_SESSION['day'] = $day;
	
	$user_filter = $userPreferences['user_filter'];
	$status_filter = $userPreferences['status_filter'];
        $task_filter = $userPreferences['task_filter'];
	
	$user = Contacts::findById(array('id' => $user_filter));
	if ($user == null) $user = logged_user();
	
	$use_24_hours = user_config_option('time_format_use_24');
	$date_format = user_config_option('date_format');
	if($use_24_hours) $timeformat = 'G:i';
	else $timeformat = 'g:i A';

	echo stylesheet_tag('event/week.css');

	$startday = $day - date("N", mktime(0, 0, 0, $month, $day, $year)) + 1; // beginning of the week, monday

	$endday = $startday + 7; // end of week
	
	$today = DateTimeValueLib::now();
	$today->add('h', logged_user()->getTimezone());
	$currentday = $today->format("j");
	$currentmonth = $today->format("n");
	$currentyear = $today->format("Y");
	$drawHourLine = false;
	
	$lastday = date("t", mktime(0, 0, 0, $month, 1, $year)); // # of days in the month
	
	$date_start = new DateTimeValue(mktime(0, 0, 0, $month, $startday, $year)); 
	$date_end = new DateTimeValue(mktime(0, 0, 0, $month, $endday, $year)); 
//	$date_start->add('h', logged_user()->getTimezone());
//	$date_end->add('h', logged_user()->getTimezone());
	
	$tasks = array();
	$milestones = ProjectMilestones::getRangeMilestones($date_start, $date_end);
    if($task_filter != "hide"){
    	$tasks = ProjectTasks::getRangeTasksByUser($date_start, $date_end, ($user_filter != -1 ? $user : null), $task_filter);
    }
    
    if (user_config_option('show_birthdays_in_calendar')) {
    	$birthdays = Contacts::instance()->getRangeContactsByBirthday($date_start, $date_end, active_context_members(false));
    } else {
    	$birthdays = array();
    }

	$tmp_tasks = array();
	foreach ($tasks as $task) {
		$tmp_tasks = array_merge($tmp_tasks, replicateRepetitiveTaskForCalendar($task, $date_start, $date_end));
	}
        
	$dates = array(); //datetimevalue for each day of week
	$results = array();
	$allday_events_count = array();
	$alldayevents = array();
	$today_style = array();
	
	$task_starts = array();
	$task_ends = array();
	
	$month_aux = $month;
	$year_aux = $year;
	
	for ($day_of_week = 0; $day_of_week < 7; $day_of_week++) {	
		
		$day_of_month = $day_of_week + $startday;
		if($day_of_month <= $lastday AND $day_of_month >= 1){ 								
			$w = $day_of_month;
		} elseif($day_of_month < 1) {								
			$w = $day_of_month;
		} else {
			if($day_of_month > $lastday) {
				if ($month_aux == $month) $month_aux++;
				if($month_aux == 13){
					$month_aux = 1;
					$year_aux++;
				}
			}
			$w = $day_of_month - $lastday;
		}	
		
		$day_tmp = (isset($w) && is_numeric($w)) ? $w : 0;
	
		$dates[$day_of_week] = new DateTimeValue(mktime(0, 0, 0, $month_aux, $day_tmp, $year_aux)); 

		$today_style[$day_of_week] = '';
		if($currentyear == $dates[$day_of_week]->getYear() && $currentmonth == $dates[$day_of_week]->getMonth() && $currentday == $dates[$day_of_week]->getday()) { // Today
			$drawHourLine = true;
			$today_style[$day_of_week] = 'background-color:#FFFF88;opacity:0.4;filter: alpha(opacity = 40);z-index=0;';
		} else if($year == $year_aux && $month == $month_aux && $day == $day_of_month) { // Selected day
			$today_style[$day_of_week] = 'background-color:#E4EEEE;opacity:0.4;filter: alpha(opacity = 40);z-index=0;';
		}

		
		$results[$day_of_week] = ProjectEvents::getDayProjectEvents($dates[$day_of_week], active_context(), $user_filter, $status_filter); 
		if(!$results[$day_of_week]) $results[$day_of_week]=array();
		foreach ($results[$day_of_week] as $key => $event){
			if ($event->getTypeId()> 1){
				$alldayevents[$day_of_week][] = $event;
				unset($results[$day_of_week][$key]);
			}
		}
		if(is_array($milestones)){
			foreach ($milestones as $milestone){
				$due_date = new DateTimeValue($milestone->getDueDate()->getTimestamp());
				if ($dates[$day_of_week]->getTimestamp() == mktime(0,0,0,$due_date->getMonth(),$due_date->getDay(),$due_date->getYear())) {	
					$alldayevents[$day_of_week][] = $milestone;
				}			
			}
		}
		
		if(isset($tasks) && is_array($tasks)){
			$task_starts[$day_of_week] = array();
			$task_ends[$day_of_week] = array();
			
			foreach ($tmp_tasks as $task) {
				$added = false;
				if ($task->getDueDate() instanceof DateTimeValue){
					$due_date = new DateTimeValue($task->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
					if ($dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear())) {
						if ($task->getUseDueTime() && ($task->getStartDate() instanceof DateTimeValue || $task->getTimeEstimate() > 0)) {
							$results[$day_of_week][] = $task;
							$task_ends[$day_of_week][$task->getId()] = true;
						} else {
							$alldayevents[$day_of_week][] = $task;
						}
						$added = true;
					}
				}
				if ($task->getStartDate() instanceof DateTimeValue){
					$start_date = new DateTimeValue($task->getStartDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
					if (!$added && $dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear())) {
						if ($task->getUseStartTime() && ($task->getDueDate() instanceof DateTimeValue|| $task->getTimeEstimate() > 0)) {
							$results[$day_of_week][] = $task;
							$task_starts[$day_of_week][$task->getId()] = true;
						} else {
							$alldayevents[$day_of_week][] = $task;
						}
						$added = true;
					}
				}
			}
		}

		if (is_array($birthdays)) {
			foreach($birthdays as $c) {
				if (!$c->getBirthday() instanceof DateTimeValue) continue;
				if ($dates[$day_of_week]->getTimestamp() == mktime(0,0,0,$c->getBirthday()->getMonth(),$c->getBirthday()->getDay(),$dates[$day_of_week]->getYear())) {
					$alldayevents[$day_of_week][] = $c;
				}
			}
		}
		$allday_events_count[$day_of_week] = count(array_var($alldayevents, $day_of_week, array()));
	}
	
	if(is_array($tmp_tasks)){
		foreach ($tmp_tasks as $task) {
			if (!$task->getUseDueTime() || !$task->getUseStartTime()) continue;
			$starts_this_week = false;
			for ($day_of_week = 0; $day_of_week < 7; $day_of_week++) {
				if (array_var($task_starts[$day_of_week], $task->getId())) {
					$starts_this_week = true;
					$ends_this_week = false;
					for ($dow = $day_of_week+1; $dow < 7; $dow++) {
						if (array_var($task_ends[$dow], $task->getId())) {
							$ends_this_week = true;
							for ($d = $day_of_week+1; $d < $dow; $d++) {
								$results[$d][$task->getId()] = $task;
							}
						}
					}
					if (!$ends_this_week && $task->getDueDate() instanceof DateTimeValue) {
						for ($d = $day_of_week+1; $d < 7; $d++) {
							$results[$d][$task->getId()] = $task;
						}
					}
				}
			}
			if (!$starts_this_week && $task->getDueDate() instanceof DateTimeValue) {
				$due_date = new DateTimeValue($task->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
				$due_dow = $due_date->format('w') + (user_config_option("start_monday") ? -1 : 0);
				for ($dow = 0; $dow < $due_dow; $dow++) {
					$dow_ts = mktime(0, 0, 0, $month, $startday + $dow, $year);
					if ($task->getStartDate() instanceof DateTimeValue && $task->getStartDate()->getTimestamp() < $dow_ts) {
						$results[$dow][$task->getId()] = $task;
					}
				}
			}
		}
	}
	
	$max_events = max($allday_events_count) == 0 ? 1 : max($allday_events_count);
	$alldaygridHeight = $max_events * PX_HEIGHT / 2 + PX_HEIGHT / 2;//Day events container height= all the events plus an extra free space
        if($alldaygridHeight > 100){
            $alldaygridHeight = 100;
        }

	$users_array = array();
	$companies_array = array();
	foreach($users as $u)
		$users_array[] = $u->getArrayInfo();
	foreach($companies as $company)
		$companies_array[] = $company->getArrayInfo();	
?>
<div id="calHiddenFields">
	<input type="hidden" id="hfCalUsers" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($users_array)))) ?>"/>
	<input type="hidden" id="hfCalCompanies" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($companies_array)))) ?>"/>
	<input type="hidden" id="hfCalUserPreferences" value="<?php echo clean(str_replace('"',"'", escape_character(json_encode($userPreferences)))) ?>"/>
        <input id="<?php echo $genid?>type_related" type="hidden" name="type_related" value="only" />
</div>


<div class="calendar" style="padding:0px;height:100%;overflow:hidden;" id="cal_main_div" onmouseup="og.clearPaintedCells();">
<div id="calendarPanelTopToolbar" class="x-panel-tbar" style="width:100%;display:block;background-color:#F0F0F0;"></div>
<div id="calendarPanelSecondTopToolbar" class="x-panel-tbar" style="width:100%;padding-top:0;display:block;background-color:#F0F0F0;"></div>
<div id="<?php echo $genid."view_calendar"?>">  
<table style="width:100%;height:100%;">
<tr>
<td>
	<table style="width:100%;height:100%;">
	<tr>
	<td class="coViewHeader" colspan=2 rowspan=1>
	<div class="coViewTitle">
		<table style="width:100%;"><tr><td style="height:25px;vertical-align: middle;">
			<span id="chead0">
			<?php if (user_config_option("show_week_numbers")) {
				$weeknumber = date("W", mktime(0, 0, 0, $month, $startday, $year));
				echo lang("week number x", $weeknumber) . " - "; 
			}?>
			<?php echo date($date_format, mktime(0, 0, 0, $month, $startday, $year)) ." - ". date($date_format, mktime(0, 0, 0, $month, $endday-1, $year))
		 	.' - '. ($user_filter == -1 ? lang('all users') : lang('calendar of', clean($user->getObjectName())));?></span>
		</td><td style="height:25px; vertical-align:middle; padding-right:10px;"><?php 
		if (config_option("show_feed_links")) {
			renderCalendarFeedLink();
		}
		?></td></tr></table>
	</div>		
	</td>
	</tr>
	<tr>
		<td class="coViewBody" style="padding:0px;height:100%;" colspan=2>
		<div id="chrome_main2" style="width:100%; height:100%">
		<div id="allDayGrid" class="inset grid"  style="height: <?php echo $alldaygridHeight ?>px; margin-bottom: 5px;background:#E8EEF7;margin-right:15px;margin-left:40px;position:relative;">
		<?php					
									
			$width_percent = 100/5;
			$width = 0;
			for ($day_of_week = 0; $day_of_week < 5; $day_of_week++) {	
				
				$day_of_month = $day_of_week + $startday;
				// see what type of day it is
				$today_text = "";			
				if($currentyear == $year && $currentmonth == $month && $currentday == $day_of_month){
					$daytitle = 'todaylink';
					$today_text = "Today ";
				}else $daytitle = 'daylink';
				
				// if weekends override do this
				if ($day_of_month <= $lastday AND $day_of_month >= 1) {
					$daytype = "weekday";
				} else {
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
				
										
				$dtv_temp = $dates[$day_of_week];
				$p = get_url('event', 'viewdate', array(
					'day' => $dtv_temp->getDay(),
					'month' => $dtv_temp->getMonth(),
					'year' => $dtv_temp->getYear(),
					'view_type' => 'viewdate'
				));
				$t = get_url('event', 'add', array(
					'day' => $dtv_temp->getDay(),
					'month' => $dtv_temp->getMonth(),
					'year' => $dtv_temp->getYear()
				));							

				$format_d_m = str_replace('d', 'j', $date_format);
				$format_d_m = str_replace(array('Y','y','o'), '', $format_d_m);
				$format_d_m = str_replace('n', 'm', $format_d_m);
				if (strpos($format_d_m, 'm') === FALSE) $format_d_m = str_replace('F', 'M', $format_d_m);
				else $format_d_m = str_replace(array('F','M'), '', $format_d_m);
				$format_d_m = trim($format_d_m);
				while (!(str_starts_with($format_d_m, 'j') || str_starts_with($format_d_m, 'm'))) 
					$format_d_m = substr($format_d_m, 1);
				while (!(str_ends_with($format_d_m, 'j') || str_ends_with($format_d_m, 'm'))) 
					$format_d_m = substr($format_d_m, 0, strlen($format_d_m) - 1);

		?>
				<div id="alldaycelltitle_<?php echo $day_of_week ?>" class="chead cheadNotToday" style="width: <?php echo $width_percent ?>%; left: <?php echo $width ?>%;text-align:center;position:absolute;top:0%;">
					<span id="chead<?php echo $day_of_week ?>">
						<a class="internalLink" href="<?php echo $p; ?>"  onclick="og.disableEventPropagation(event) "><?php $dtime = mktime(0, 0, 0, $dtv_temp->getMonth(), $dtv_temp->getDay(), $dtv_temp->getYear()); echo lang(strtolower(date("l", $dtime)) . ' short') . date(' '. $format_d_m, $dtime); ?></a>
					</span>
				</div>
				<div id="allDay<?php echo $day_of_week ?>" class="allDayCell" style="left: <?php echo $width ?>%; height: 100%;border-left:3px double #DDDDDD !important; position:absolute;width:3px;z-index:110;background:#E8EEF7;top:0%;"></div>

				<div id="alldayeventowner_<?php echo $day_of_week ?>" style="width: <?php echo $width_percent ?>%;position:absolute;left: <?php echo $width ?>%; top: 12px;height: <?php echo $alldaygridHeight ?>px;"
				<?php if (!logged_user()->isGuest()) { ?>
					onclick="og.showEventPopup(<?php echo $dtv_temp->getDay() ?>, <?php echo $dtv_temp->getMonth()?>, <?php echo $dtv_temp->getYear()?>, -1, -1, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $dtv_temp->format($date_format) ?>', '<?php echo $genid?>', 0,false);">
				<?php } else echo ">"; ?>
					<?php	
						$top=5;
						$count = 0;
						if(is_array(array_var($alldayevents,$day_of_week))){
							foreach ($alldayevents[$day_of_week] as $event){
								$count++;
								if($count <= $max_events_to_show){
									$tipBody = '';
									$divtype = '';
									$div_prefix = '';
									if ($event instanceof ProjectMilestone ){
										$div_prefix = 'w5_ms_div_';
										$objType = 'milestone';
										$subject = clean($event->getObjectName());
										$img_url = image_url('/16x16/milestone.png');
										$due_date=$event->getDueDate();
										$divtype = '<span class="italic">' . lang('milestone') . '</span> - ';
										$tipBody = trim(clean($event->getDescription()));
									}elseif ($event instanceof ProjectTask){
										$start_of_task = false;
										$end_of_task = false;
										$is_repe_task = $event->isRepetitive();
										if ($event->getDueDate() instanceof DateTimeValue) {
											$due_date = new DateTimeValue($event->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
											if ($dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear())){
												$end_of_task = true;
												$start_of_task = true;
											}
										}
										if ($event->getStartDate() instanceof DateTimeValue) {
											$start_date = new DateTimeValue($event->getStartDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
											if ($dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear())) {
												$start_of_task = true;
												$end_of_task = true;
											}
										}
										if ($start_of_task && $end_of_task) {
											$tip_title = lang('task');
											$img_url = image_url('/16x16/tasks.png');
											$tip_pre = '';
										} else if ($end_of_task) {
											$tip_title = lang('end of task');
											$img_url = image_url('/16x16/task_end.png');
											$tip_pre = 'end_';
										} else {
											$tip_title = lang('start of task');
											$img_url = image_url('/16x16/task_start.png');
											$tip_pre = 'st_';
										}
										$tip_pre .= gen_id()."_";
										$div_prefix = 'w5_ta_div_' . $tip_pre;
										$objType = 'task';
										$subject = clean($event->getObjectName());
										$divtype = '<span class="italic">' . $tip_title . '</span> - ';
										$task_desc = html_to_text($event->getText());
										$tipBody = lang('assigned to') .': '. clean($event->getAssignedToName()) . (trim(clean($event->getText())) != '' ? '<br><br>' . trim($task_desc) : '');
									}elseif ($event instanceof ProjectEvent){
										$div_prefix = 'w5_ev_div_';
										$objType = 'event';
										$subject = clean($event->getObjectName());
										$img_url = image_url('/16x16/calendar.png'); /* @var $event ProjectEvent */
										$divtype = '<span class="italic">' . lang('event') . '</span> - ';
										$tipBody = (trim(clean($event->getDescription())) != '' ? '<br>' . clean($event->getDescription()) : '');
									}elseif ($event instanceof Contact ) {
										$div_prefix = 'w5_bd_div_';
										$objType = 'contact';
										$subject = clean($event->getObjectName());
										$img_url = image_url('/16x16/contacts.png');
										$due_date = new DateTimeValue(mktime(0,0,0, $event->getBirthday()->getMonth(), $event->getBirthday()->getDay(), $dates[$day_of_week]->getYear()));
										$divtype = '<span class="italic">' . lang('birthday') . '</span> - ';
									}
									$tipBody = str_replace("\r", '', $tipBody);
									$tipBody = str_replace("\n", '<br>', $tipBody);
									if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';

									if ($event instanceof ProjectMilestone || $event instanceof ProjectEvent || ($due_date instanceof DateTimeValue && $dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear()))
									|| ($start_date instanceof DateTimeValue && $dates[$day_of_week]->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear()))) {

										$ws_color = $event->getObjectColor($event instanceof ProjectEvent ? 1 : 12);

										cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
					?>
					<div id="<?php echo $div_prefix . $event->getId() ?>" class="adc" style="left: 4%; top: <?php echo $top ?>px; z-index: 5;width: 92%;margin:1px;position:absolute;">
						<div class="t3 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-bottom:1px solid; border-color:<?php echo $border_color ?>"></div>
						<div class="noleft <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;border-left:1px solid; border-right:1px solid; border-color:<?php echo $border_color ?>">							
							<div class="" style="overflow: hidden; padding-bottom: 1px;">
								<table style="width:100%"><tr><td>
								<span class="nobr" style="display: block; text-decoration: none;"><a href='<?php echo $event->getViewUrl()."&amp;view=week"?>' class='internalLink'" onclick="og.disableEventPropagation(event);"><img src="<?php echo $img_url?>" style="vertical-align:middle," border='0'> <span style="color:<?php echo $txt_color ?>!important"><?php echo $subject ?></span> </a></span>
								<?php if ($objType == 'event') { ?>
								</td><td align="right">
								<input type="checkbox" style="width:13px;height:13px;vertical-align:top;margin:2px 2px 0 0;border-color: <?php echo $border_color ?>;" id="sel_<?php echo $event->getId()?>" name="obj_selector" onclick="og.eventSelected(this.checked);og.disableEventPropagation(event);"></input>
								<?php } ?>
								</td></tr></table>
							</div>
						</div>
						<div class="t3 <?php echo  $ws_class;?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"></div>
					</div>
					<script>
						addTip('<?php echo $div_prefix . $event->getId() ?>', <?php echo json_encode($divtype . $subject) ?>, <?php echo json_encode($tipBody) ?>);
						<?php if (!($event instanceof Contact || (isset($is_repe_task) && $is_repe_task))) { ?>
						<?php $is_repetitive = $event instanceof ProjectEvent && $event->isRepetitive() ? 'true' : 'false'; ?>
						<?php if (!logged_user()->isGuest()) { ?>
						og.createEventDrag('<?php echo $div_prefix . $event->getId() ?>', '<?php echo $event->getId()?>', <?php echo $is_repetitive ?>, '<?php echo $dates[$day_of_week]->format('Y-m-d')." 00:00:00" ?>', '<?php echo $objType ?>', true, 'ev_dropzone_allday');
						<?php } ?>
						<?php } ?>
					</script>
					<?php
										$top += 21;
									}
								}
							}
							if ($count > $max_events_to_show) {
                                        ?>

                                        <div id="<?php echo $div_prefix . $event->getId() ?>" class="adc" style="left: 4%; top: <?php echo $top ?>px; z-index: 5;width: 92%;margin:1px;position:absolute; text-align: center;">
                                                <a href="<?php echo $p?>" class="internalLink"  onclick="og.disableEventPropagation(event);return true;"><?php echo ($count-$max_events_to_show) . ' ' . lang('more');?> </a>
                                        </div>
<?php
							}
						}
					?>
				</div>
			<script>
				var ev_dropzone_allday = new Ext.dd.DropZone('alldayeventowner_<?php echo $day_of_week ?>', {ddGroup:'ev_dropzone_allday'});
				var ev_dropzone_allday = new Ext.dd.DropZone('alldaycelltitle_<?php echo $day_of_week ?>', {ddGroup:'ev_dropzone_allday'});
			</script>
		<?php
				$width += $width_percent;
			}
		?>
	</div>
	
	<div id="gridcontainer" class="toprint" style="background-color:#fff; position:relative; overflow-x:hidden; overflow-y:scroll; height:504px;">	
			<div id='calowner' style="display:block; width:100%;">  
				<table cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed; width: 100%;height: 100%;">
					<tr>
						<td id="rowheadcell" style="width: 40px;">
							<div id="rowheaders" style="top: 0pt; left: 0pt;">										
							<?php
								for ($hour=0; $hour<=23; $hour++){	
							?>
<div style="height: <?php echo PX_HEIGHT-1?>px; top: 0ex;background: #E8EEF7 none repeat scroll 0%;border-top:1px solid #DDDDDD;left:0pt;width: 100%;" id="rhead<?php echo $hour?>" class="rhead">
<div class="rheadtext" style="text-align:right;padding-right:2px;"><?php echo date($use_24_hours ? "G:i" : "g a", mktime($hour, 0)) ?></div>
</div>												
										<?php
								}
							?>

							</div>
						</td>
						<td id="gridcontainercell" style="width:100%;position:relative" >	
							<div id="grid" style="height: 100%;background-color:#fff;position:relative;" class="grid">										
								
							<?php
								for ($hour=0; $hour<=47; $hour++){	
									$curr_hour = date("g");
									if ($hour % 2 == 0){
										$parity = "hruleeven";
										$style="border-top:1px solid #DDDDDD;";
									} else {
										$parity="hruleodd";
										$style="border-top:1px dotted #DDDDDD;";
									}
									$top = (PX_HEIGHT/2) * $hour;
							?>
<div id="r<?php echo $hour?>" class="hrule <?php echo $parity?>" onmousedown="" onmouseup="" style="top: <?php echo $top?>px; height:0px; z-index:1; position:absolute; left:0px;<?php echo $style?>;width:100%">
<?php $hour == $curr_hour? print("<span id='curr_hour' style='visibility:hidden;height:0px;width:0px'></span>"):print('');?>
</div>
<?php
								}
?>
<div id="eventowner" class="eventowner" style="z-index: 102;" onclick="og.disableEventPropagation(event) ">
<?php
								for ($day_of_week = 0; $day_of_week < 5; $day_of_week++) {
									$date = $dates[$day_of_week];
									$left = (100/5)*$day_of_week;
									
									for ($hour=0; $hour<=47; $hour++){
										$top = (PX_HEIGHT/2) * $hour;
								
									$div_id = 'h' . $day_of_week . "_" . $hour; 

?>

<div id="<?php echo $div_id?>" style="left:<?php echo $left ?>%;width:<?php echo $width_percent ?>%;top:<?php echo $top?>px;height:21px;position:absolute;z-index: 90;<?php echo $today_style[$day_of_week]?>"
<?php if (!logged_user()->isGuest()) { ?>
onmouseover="if (!og.selectingCells) og.overCell('<?php echo $div_id?>'); else og.paintSelectedCells('<?php echo $div_id?>');"
onmouseout="if (!og.selectingCells) og.resetCell('<?php echo $div_id?>');"
onmousedown="og.selectStartDateTime(<?php echo $date->getDay() ?>, <?php echo $date->getMonth()?>, <?php echo $date->getYear()?>, <?php echo date("G",mktime($hour/2))?>, <?php echo ($hour % 2 == 0) ? 0 : 30 ?>); og.resetCell('<?php echo $div_id?>'); og.paintingDay = <?php echo $day_of_week ?>; og.paintSelectedCells('<?php echo $div_id?>');"
onmouseup="og.showEventPopup(<?php echo $date->getDay() ?>, <?php echo $date->getMonth()?>, <?php echo $date->getYear()?>, <?php echo date("G",mktime(($hour+1)/2))?>, <?php echo (($hour+1) % 2 == 0) ? 0 : 30 ?>, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $date->format($date_format) ?>', '<?php echo $genid?>',0, false);">
<?php } else echo ">"; ?>
</div>

<script>
	og.ev_cell_dates[<?php echo $day_of_week?>] = {day:<?php echo $date->getDay() ?>, month:<?php echo $date->getMonth()?>, year:<?php echo $date->getYear()?>}
	var ev_dropzone = new Ext.dd.DropZone('<?php echo $div_id?>', {ddGroup:'ev_dropzone'});
</script>

<?php								} ?>

									<div id="vd<?php echo $day_of_week ?>" style="left: <?php echo $left ?>%; height: <?php echo (PX_HEIGHT)*24 ?>px;border-left:3px double #DDDDDD !important; position:absolute;width:3px;z-index:110;"></div>
<?php
										$cells = array();
										for ($i = 0; $i < 24; $i++) {
											$cells[$i][0] = 0;
											$cells[$i][1] = 0;
										}
										foreach ($results[$day_of_week] as $event){
											
											getEventLimits($event, $dates[$day_of_week], $event_start, $event_duration, $end_modified);

											$event_duration->add('s', -1);
											if ($event_start->getMinute() < 30) {
												$cells[$event_start->getHour()][0]++;
												$cells[$event_start->getHour()][1]++;
											} else $cells[$event_start->getHour()][1]++;
											for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++){
												$cells[$i][0]++;
												$cells[$i][1]++;
											}
											if ($event_duration->getMinute() > 0) {
												if ($event_duration->getHour() != $event_start->getHour()) {
													$cells[$event_duration->getHour()][0]++;
													if ($event_duration->getMinute() > 30) $cells[$event_duration->getHour()][1]++;
												}
											}
										}
										$occup = array(); //keys: hora - pos
										foreach ($results[$day_of_week] as $event){
											
											getEventLimits($event, $dates[$day_of_week], $event_start, $event_duration, $end_modified);
											
											$event_id = $event->getId();
											$subject = clean($event->getObjectName());

											$ws_colors = $event->getObjectColors($event instanceof ProjectEvent ? 1 : 12);
											$all_event_colors = array();
											foreach ($ws_colors as $ws_color) {
												cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);
												$all_event_colors[$ws_color] = $ws_class;
												if (!user_config_option('show_multiple_color_events')) break;
											}
											
											$hr_start = $event_start->getHour();
											$min_start = $event_start->getMinute();
											$hr_end = $event_duration->getHour();
											$min_end = $event_duration->getMinute();
											
											if ($event_start == $event_duration){
												$hr_end++;
											}
											if ($hr_end == 0 && $event_duration->getDay() != $dates[$day_of_week]->getDay()) $hr_end = 24;
											$top = PX_HEIGHT * $hr_start + (PX_HEIGHT*(($min_start*100)/(60*100)));
											$bottom = PX_HEIGHT * $hr_end + (PX_HEIGHT*(($min_end*100)/(60*100)));
											$height = $bottom - $top;
											
											$evs_same_time = 0;
											$i = $event_start->getHour();
											if ($event_start->getMinute() < 30) {
												if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
												if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
											} else if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
											
											for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++){
												if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
												if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
											}
											$i = $event_duration->getHour();
											if ($event_duration->getMinute() > 0) {
												if ($cells[$i][0] > $evs_same_time) $evs_same_time = $cells[$i][0];
												if ($event_duration->getMinute() > 30) {
													if ($cells[$i][1] > $evs_same_time) $evs_same_time = $cells[$i][1];
												}
											}
											
											$posHoriz = 0;
											$canPaint = false;
											while (!$canPaint) {
												$canPaint = true;
												if ($event_start->getMinute() < 30) {
													$canPaint = !(isset($occup[$event_start->getHour()][0][$posHoriz]) && $occup[$event_start->getHour()][0][$posHoriz]
															 || isset($occup[$event_start->getHour()][1][$posHoriz]) && $occup[$event_start->getHour()][1][$posHoriz]);
												} else {
													$canPaint = !(isset($occup[$event_start->getHour()][1][$posHoriz]) && $occup[$event_start->getHour()][1][$posHoriz]);
												}
												for($i = $event_start->getHour()+1; $canPaint && $i < $event_duration->getHour(); $i++) {
													if (isset($occup[$i][0][$posHoriz]) && $occup[$i][0][$posHoriz] || isset($occup[$i][1][$posHoriz]) && $occup[$i][1][$posHoriz]) {
														$canPaint = false;
													}
												}
												if ($canPaint) {
													if ($event_duration->getMinute() > 30) {
														$canPaint = !(isset($occup[$event_duration->getHour()][0][$posHoriz]) && $occup[$event_duration->getHour()][0][$posHoriz]
														|| isset($occup[$event_duration->getHour()][1][$posHoriz]) && $occup[$event_duration->getHour()][1][$posHoriz]);
													} else {
														$htmp = $event_duration->getHour() - ($event_duration->getMinute() > 0 ? 0 : 1);
														$postmp = $event_duration->getMinute() == 30 ? 0 : 1;
														$canPaint = !(isset($occup[$htmp][$postmp][$posHoriz]) && $occup[$htmp][$postmp][$posHoriz] && $event_duration->getDay() == $event_start->getDay()); 
													}
												}
												
												if (!$canPaint) $posHoriz++;
											}

											$width = (100/5) / $evs_same_time;
											$left = $width * $posHoriz + ((100/5) * $day_of_week) + 0.25;
											$width -= 0.5;
											
											//provisional fix
											if($evs_same_time == 1){
												$left = ((100/5) * $day_of_week) + 0.25;
											}
											//End provisional fix
											
											if ($event_start->getMinute() < 30) {
												$occup[$event_start->getHour()][0][$posHoriz] = true;
												$occup[$event_start->getHour()][1][$posHoriz] = true;
											} else {
												$occup[$event_start->getHour()][1][$posHoriz] = true;
											}
											for($i = $event_start->getHour()+1; $i < $event_duration->getHour(); $i++) {
												$occup[$i][0][$posHoriz] = true;
												$occup[$i][1][$posHoriz] = true;
											}
											if ($event_duration->getMinute() > 0) {
												$occup[$event_duration->getHour()][0][$posHoriz] = true;
												if ($event_duration->getMinute() > 30) {
													$occup[$event_duration->getHour()][1][$posHoriz] = true;
												}
											}
											$event_duration->add('s', 1);
											
											if ($event instanceof ProjectEvent) {
												$real_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
												$real_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
											} else if ($event instanceof ProjectTask) {
												if ($event->getStartDate() instanceof DateTimeValue) {
													$real_start = new DateTimeValue($event->getStartDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
												} else {
													$real_start = $event_start;
												}
												if ($event->getDueDate() instanceof DateTimeValue) {
													$real_duration = new DateTimeValue($event->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
												} else {
													$real_duration = $event_duration;
												}
											}
											
											$pre_tf = $real_start->getDay() == $real_duration->getDay() ? '' : 'D j, ';
											$ev_hour_text = (!$event->isRepetitive() && $real_start->getDay() != $event_start->getDay()) ? "... ".format_date($real_duration, $timeformat, 0) : format_date($real_start, $timeformat, 0);
											
											$assigned = "";
											if ($event instanceof ProjectTask && $event->getAssignedToContactId() > 0) {
												$assigned = "<br>" . lang('assigned to') .': '. $event->getAssignedToName();
												$task_desc = purify_html($event->getText());
												$tipBody = lang('assigned to') .': '. clean($event->getAssignedToName()) . (trim(clean($event->getText())) != '' ? '<br><br>' . trim($task_desc) : '');
											} else {
												
												$tipBody = format_date($real_start, $pre_tf.$timeformat, 0) .' - '. format_date($real_duration, $pre_tf.$timeformat, 0) . $assigned . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : '');
												$tipBody = str_replace("\r", '', $tipBody);
												$tipBody = str_replace("\n", '<br>', $tipBody);
											}
											if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';
											
											$ev_duration = DateTimeValueLib::get_time_difference($event_start->getTimestamp(), $event_duration->getTimestamp());
											$id_suffix = "_$day_of_week"; 
?>
											<script>
												if (<?php echo $top; ?> < scroll_to || scroll_to == -1) {
													scroll_to = <?php echo $top;?>;
												}
												addTip('w5_ev_div_' + '<?php echo $event->getId() . $id_suffix ?>', <?php echo json_encode(clean($event->getObjectName())) ?>, <?php echo json_encode($tipBody);?>);
											</script>
<?php
											$bold = "bold";
											if ($event instanceof Contact || $event->getIsRead(logged_user()->getId())){
												$bold = "normal";
											}
?>

<?php
				$all_event_colors = array_reverse($all_event_colors);
				$color_left = $left;
				$color_idx = 0;
				foreach ($all_event_colors as $color => $color_class) {
					$color_width = $width / count($all_event_colors);
					$color_left = $left + $color_width * $color_idx;
					$color_idx++;
?>
						<div id="w5_ev_div_<?php echo $event->getId() . $id_suffix?>_colors_<?php echo $color_idx?>" class="chip <?php echo $color_class ?> w5_ev_div_<?php echo $event->getId() . $id_suffix?>_colors"
						style="position: absolute; top: <?php echo $top?>px; left: <?php echo $color_left?>%; width: <?php echo $color_width?>%;height:<?php echo $height ?>px;z-index:100;"></div>
<?php 			} ?>

						<div id="w5_ev_div_<?php echo $event->getId() . $id_suffix?>" class="chip" style="position: absolute; top: <?php echo $top?>px; left: <?php echo $left?>%; width: <?php echo $width?>%;height:<?php echo $height ?>px;z-index:120;" onclick="og.disableEventPropagation(event)" onmouseup="og.clearPaintedCells()">
						<div class="t1 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 2px 0px 2px;height:0px; border-bottom:1px solid;border-color:<?php echo $border_color ?>"></div>
						<div class="t2 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid;border-color:<?php echo $border_color ?>"></div>
						<div id="inner_w5_ev_div_<?php echo $event->getId() . $id_suffix?>" class="chipbody edit" style="height:<?php echo $height ?>px;">
						
						<div style="overflow:hidden;height:100%;border-left: 1px solid;border-right: 1px solid;border-color:<?php echo $border_color ?>;">
							<table style="width:100%;"><tr><td>
							<?php if ($event instanceof ProjectEvent) { ?>
								<input type="checkbox" style="width:13px;height:13px;vertical-align:top;margin:2px 0 0 2px;border-color: <?php echo $border_color ?>;" id="sel_<?php echo $event->getId()?>" name="obj_selector" onclick="og.eventSelected(this.checked);"></input>
							<?php } ?>
								<a href='<?php echo get_url($event instanceof ProjectEvent ? 'event' : 'task', 'view', array(
										'view' => 'viewweek5days',
										'id' => $event->getId(),
										'user_id' => $user_filter
									)); ?>'
								onclick="og.disableEventPropagation(event);"
								class='internalLink'>
									<span name="w5_ev_div_<?php echo $event->getId() . $id_suffix?>_info" style="color:<?php echo $txt_color?>!important;padding-left:5px;font-weight:"<?php echo $bold ?>";"><?php echo "$ev_hour_text"?></span>																				
								</a>
							</td><td align="right">
								<div align="right" style="padding-right:4px;<?php echo ($ev_duration['hours'] == 0 ? 'height:'.$height.'px;' : '') ?>">
								<?php
								if ($event instanceof ProjectEvent && $user_filter != -1) { 
									$invitations = $event->getInvitations();
									if ($invitations != null && is_array($invitations) && isset($invitations[$user_filter])) {
										$inv = $invitations[$user_filter];
										
										if ($inv->getInvitationState() == 0) { // Not answered
											echo '<img src="' . image_url('/16x16/mail_mark_unread.png') . '"/>';
										} else if ($inv->getInvitationState() == 1) { // Assist = Yes
											echo '<img src="' . image_url('/16x16/complete.png') . '"/>';
										} else if ($inv->getInvitationState() == 2) { // Assist = No
											echo '<img src="' . image_url('/16x16/del.png') . '"/>';
										} else if ($inv->getInvitationState() == 3) { // Assist = Maybe
											echo '<img src="' . image_url('/16x16/help.png') . '"/>';
										} else {
											//echo "Not Invited";
										}
									}
								} else if ($event instanceof ProjectTask) {
									echo '<img src="' . image_url('/16x16/tasks.png') . '"/>';
								} ?>
								</div>
							</td></tr>
							<tr><td>
								<?php
									$subject_toshow = $subject;
									if ($event instanceof ProjectTask && $event->getAssignedToContactId() > 0) {
										$subject_toshow = '<span class="bold">'.$event->getAssignedToName().'</span><br />'.$subject_toshow;
									} 
								?>
								<div><a href='<?php echo get_url($event instanceof ProjectEvent ? 'event' : 'task', 'view', array('view' => 'week', 'id' => $event->getId(), 'user_id' => $user_filter)); ?>'
									onclick="og.disableEventPropagation(event);"
									class='internalLink'><span style="color:<?php echo $txt_color?>!important;padding-left:5px;font-weight: <?php echo $bold;?>"><?php echo $subject_toshow;?></span></a>
								</div>
							</td></tr>
							<tr style="height:100%;">
								<td style="width:100%;" colspan="2"><div style="height: <?php echo $height - PX_HEIGHT ?>px;"></div></td>
							</tr>
							</table>
						</div>
						</div>
						<div class="b2 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid; border-color:<?php echo $border_color ?>"></div>
						<div class="b1 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 2px 0px 2px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"></div>
						</div>
						<?php if ($event instanceof ProjectEvent) { ?>
						<script>
							<?php if (!$end_modified) { ?>
							og.setResizableEvent('w5_ev_div_<?php echo $event->getId() . $id_suffix?>', '<?php echo $event->getId()?>'); // Resize
							<?php } ?>
							<?php $is_repetitive = $event->isRepetitive() ? 'true' : 'false'; ?>
							<?php if (!logged_user()->isGuest()) { ?>
							og.createEventDrag('w5_ev_div_<?php echo $event->getId() . $id_suffix?>', '<?php echo $event->getId()?>', <?php echo $is_repetitive ?>, '<?php echo $event_start->format('Y-m-d H:i:s') ?>', 'event', false, 'ev_dropzone'); // Drag
							<?php } ?>
						</script>
						<?php } ?>

<?php												}//foreach ?>
											
<?php										}//day of week ?>
</div><!-- eventowner -->
										</div><!-- grid -->
									</td><td id="ie_scrollbar_adjust" style="width:0px;"></td>
								</tr>
							</table>
						</div><!-- calowner -->
				</div><!-- gridcontainer -->
				
			</div>		
			
			</td>
			</tr>
		</table>
	</td>
</tr>
</table>
</div>
</div>

<?php
	$wdst = user_config_option('work_day_start_time');
	$h_m = explode(':', $wdst);
	if (str_ends_with($wdst, 'PM')) {
		$h_m[0] = ($h_m[0] + 12) % 24;
		$h_m[1] = substr($h_m[1], 0 , strpos(' ', $h_m[1]));
	}
	$defaultScrollTo = PX_HEIGHT * ($h_m[0] + ($h_m[1] / 60));
 ?>


<script>
	// Top Toolbar	
	ogCalendarUserPreferences = Ext.util.JSON.decode(document.getElementById('hfCalUserPreferences').value);
	og.ogCalTT = new og.CalendarTopToolbar({
		renderTo:'calendarPanelTopToolbar'
	});	
	og.ogCalSecTT = new og.CalendarSecondTopToolbar({
		usersHfId:'hfCalUsers',
		companiesHfId:'hfCalCompanies',
		renderTo: 'calendarPanelSecondTopToolbar'
	});

	// Mantain the actual values after refresh by clicking Calendar tab.
	var dtv = new Date('<?php echo $month.'/'.$day.'/'.$year ?>');
	og.calToolbarDateMenu.picker.setValue(dtv);

	// scroll to first event
	var scroll_pos = (scroll_to == -1 ? <?php echo $defaultScrollTo ?> : scroll_to);
	Ext.get('gridcontainer').scrollTo('top', scroll_pos, true);
	
	if (Ext.isIE) document.getElementById('ie_scrollbar_adjust').style.width = '15px';
	
	// resize grid
	function resizeGridContainer(e, id) {
		maindiv = document.getElementById('cal_main_div');
		if (maindiv == null) {
			og.removeDomEventHandler(window, 'resize', id);
		} else {
			var divHeight = maindiv.offsetHeight;
			var tbarsh = Ext.get('calendarPanelSecondTopToolbar').getHeight() + Ext.get('calendarPanelTopToolbar').getHeight();
			divHeight = divHeight - tbarsh - <?php echo (PX_HEIGHT + $alldaygridHeight); ?>;
			document.getElementById('gridcontainer').style.height = divHeight + 'px';
		}
	}
	resizeGridContainer();
	if (Ext.isIE) {
		og.addDomEventHandler(document.getElementById('cal_main_div'), 'resize', resizeGridContainer);
	} else {
		og.addDomEventHandler(window, 'resize', resizeGridContainer);
	}
	
<?php if ($drawHourLine) { ?>
	og.startLocaleTime = new Date('<?php echo $today->format('m/d/Y H:i:s') ?>');
	og.startLineTime = null;
	var today_d = og.startLocaleTime.format('N') - 1;
	og.drawCurrentHourLine(today_d, 'w5_');
<?php } ?>
	// init tooltips
	Ext.QuickTips.init();
        
        Ext.extend(og.EventRelatedPopUp, Ext.Window, {
                accept: function() {
                        var action = $("#action_related").val();
                        var opt = $("#<?php echo $genid?>type_related").val();
                        og.openLink(og.getUrl('event', action, {ids: og.getSelectedEventsCsv(), options:opt}));
                        this.close();
                }
        });
        
        function selectEventRelated(val){
            $("#<?php echo $genid?>type_related").val(val);
        }
</script>
