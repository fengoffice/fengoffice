<?php
require_javascript('og/CalendarToolbar.js');
require_javascript('og/CalendarFunctions.js');
require_javascript('og/EventPopUp.js');
require_javascript('og/CalendarPrint.js');
require_javascript('og/EventRelatedPopUp.js'); 
$genid = gen_id();
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

	echo stylesheet_tag('event/day.css');

	//today in gmt 0
	$today = DateTimeValueLib::now();
		
	//user today 
//	$today->add('h', logged_user()->getTimezone());
	
	$currentday = $today->format("j");
	$currentmonth = $today->format("n");
	$currentyear = $today->format("Y");
	$drawHourLine = ($day == $currentday && $month == $currentmonth && $year == $currentyear);

	$dtv = DateTimeValueLib::make(0,0,0,$month,$day,$year);
        
	$result = ProjectEvents::getDayProjectEvents($dtv, active_context(), $user_filter, $status_filter); 
	if(!$result) $result = array();	
	
	$alldayevents = array();
	$milestones = ProjectMilestones::getRangeMilestones($dtv, $dtv);
	if($task_filter != "hide"){
		$tasks = ProjectTasks::getRangeTasksByUser($dtv, $dtv, ($user_filter != -1 ? $user : null), $task_filter);
	}
	
	if (user_config_option('show_birthdays_in_calendar')) {
		$birthdays = Contacts::instance()->getRangeContactsByBirthday($dtv, $dtv, active_context_members(false));
	} else {
		$birthdays = array();
	}
	
	foreach ($result as $key => $event){
		if ($event->getTypeId() > 1){
			$alldayevents[] = $event;
			unset($result[$key]);
		}
	}
	
	if($milestones) {
		$alldayevents = array_merge($alldayevents,$milestones);
	}
	if(isset($tasks)) {
		$tmp_tasks = array();
		$dtv_end = new DateTimeValue($dtv->getTimestamp() + 60*60*24);
		foreach ($tasks as $task) {
			$tmp_tasks = array_merge($tmp_tasks, replicateRepetitiveTaskForCalendar($task, $dtv, $dtv_end));
		}
		foreach ($tmp_tasks as $task) {
			$added = false;
			if($task->getDueDate() instanceof DateTimeValue){
				$due_date = new DateTimeValue($task->getDueDate()->getTimestamp() + ($task->getUseDueTime() ? logged_user()->getTimezone() * 3600 : 0));
				if ($dtv->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear())) {
					if ($task->getUseDueTime() && ($task->getStartDate() instanceof DateTimeValue || $task->getTimeEstimate() > 0)) {
						$result[] = $task;
					} else {
						$alldayevents[$task->getId()] = $task;
					}
					$added = true;
				}
			}
			if($task->getStartDate() instanceof DateTimeValue){
				$start_date = new DateTimeValue($task->getStartDate()->getTimestamp() + ($task->getUseStartTime() ? logged_user()->getTimezone() * 3600 : 0));
				if (!$added && $dtv->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear())) {
					if ($task->getUseStartTime() && ($task->getDueDate() instanceof DateTimeValue|| $task->getTimeEstimate() > 0)) {
						$result[] = $task;
					} else {
						$alldayevents[$task->getId()] = $task;
					}
					$added = true;
				}
			}
		}
	}
	
	if (is_array($birthdays)) {
		$alldayevents = array_merge($alldayevents,$birthdays);
	}
	$alldaygridHeight = count($alldayevents)*PX_HEIGHT/2 + PX_HEIGHT/3;
	if($alldaygridHeight > 150){
		$alldaygridHeight = 150;
	}
	
	$loc = new Localization();
	$loc->setDateFormat(lang('view date title',$date_format));
	$view_title = $loc->formatDate($dtv);// lang(strtolower(date('l', $dtv))) . date(' j, ', $dtv) . lang('month ' . date('n', $dtv)) . date(' Y', $dtv);
	
	$users_array = array();
	$companies_array = array();
	foreach($users as $u) {
		$users_array[] = $u->getArrayInfo();
	}
	foreach($companies as $company) {
		$companies_array[] = $company->getArrayInfo();
	}
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
			<td class="coViewHeader" id='cal_coViewHeader' colspan=2  rowspan=1>
				<div class="coViewTitle">
					<table style="width:100%"><tr><td style="height:25px;vertical-align: middle;">
						<span id="chead0"><?php echo $view_title .' - '. ($user_filter == -1 ? lang('all users') : lang('calendar of', clean($user->getObjectName()))); ?></span>
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
			<div id="chrome_main2" style="width:100%; height:100%;">
					
				<div id="allDayGrid" class="inset grid"  style="height: <?php echo $alldaygridHeight ?>px; margin-bottom: 5px;background:#E8EEF7;margin-right:0px;margin-left:40px; overflow: auto"
				<?php if (!logged_user()->isGuest()) { ?>
 					onclick="og.showEventPopup(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, -1, -1, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $dtv->format($date_format) ?>', '<?php echo $genid?>',0, false);">
				<?php } else echo ">"; ?>
					<div id="allDay0" class="allDayCell" style="left: 0px; height: <?php echo $alldaygridHeight ?>px;border-left:3px double #DDDDDD !important; position:absolute;width:3px;"></div>
                                        <div id="alldayeventowner" onclick="og.disableEventPropagation(event)">
						<?php	
							$top=0;
							foreach ($alldayevents as $event){	
							
								$bold = "bold";
								if ($event instanceof Contact || $event->getIsRead(logged_user()->getId())){
									$bold = "normal";
								}
								$tipBody = '';
								$divtype = '';
								$div_prefix = '';
								$draw_div = true;
								if ($event instanceof ProjectMilestone ){
									$div_prefix = 'd_ms_div_';
									$subject = clean($event->getObjectName());
									$img_url = image_url('/16x16/milestone.png');
									$divtype = '<span class="italic">' . lang('milestone') . '</span> - ';
									$tipBody = clean($event->getDescription());
								}elseif ($event instanceof ProjectTask){
									$start_of_task = false;
									$end_of_task = false;
									if ($event->getDueDate() instanceof DateTimeValue) {
										$due_date = new DateTimeValue($event->getDueDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
										if ($dtv->getTimestamp() == mktime(0,0,0, $due_date->getMonth(), $due_date->getDay(), $due_date->getYear())){
											$end_of_task = true;
											$start_of_task = true;
										}
									}
									if ($event->getStartDate() instanceof DateTimeValue) {
										$start_date = new DateTimeValue($event->getStartDate()->getTimestamp() + logged_user()->getTimezone() * 3600);
										if ($dtv->getTimestamp() == mktime(0,0,0, $start_date->getMonth(), $start_date->getDay(), $start_date->getYear())){
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
									$div_prefix = 'd_ta_div_' . $tip_pre;
									$subject = $event->getObjectName();									
									$divtype = '<span class="italic">' . $tip_title . '</span> - ';
									$tipBody = lang('assigned to') .': '. clean($event->getAssignedToName()) . (trim(clean($event->getText())) != '' ? '<br><br>' . html_to_text($event->getText()) : '');
								}elseif ($event instanceof ProjectEvent){
									$div_prefix = 'd_ev_div_';
									$subject = clean($event->getObjectName());
									$img_url = image_url('/16x16/calendar.png');
									$divtype = '<span class="italic">' . lang('event') . '</span> - ';
									$tipBody = (trim(clean($event->getDescription())) != '' ? '<br>' . clean($event->getDescription()) : '');									
								}elseif ($event instanceof Contact ) {
									$div_prefix = 'd_bd_div_';
									$objType = 'contact';
									$subject = clean($event->getObjectName());
									$img_url = image_url('/16x16/contacts.png');
									$divtype = '<span class="italic">' . lang('birthday') . '</span> - ';
								}
								
								$tipBody = str_replace("\r", '', $tipBody);
								$tipBody = str_replace("\n", '<br>', $tipBody);
								if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';
								
								$ws_color = $event->getObjectColor($event instanceof ProjectEvent ? 1 : 12);

								cal_get_ws_color($ws_color, $ws_style, $ws_class, $txt_color, $border_color);	
						?>
						<div id="<?php echo $div_prefix . $event->getId() ?>" class="adc" style="left: 3px; top: <?php echo $top ?>px; z-index: 5;width: 99%;margin:1px;">
							<div class="t3 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-bottom:1px solid; border-color:<?php echo $border_color ?>"></div>
							<div class="noleft <?php echo  $ws_class?>" style="<?php echo  $ws_style?>; border-left:1px solid; border-right:1px solid; border-color:<?php echo $border_color ?>">							
								<div class="" style="overflow: hidden; padding-bottom: 1px;">
									<table style="width:100%"><tr><td>
									<span class="nobr" style="display: block; text-decoration: none;"><a href='<?php echo $event->getViewUrl()?>' class='internalLink' onclick="og.disableEventPropagation(event);"><img src="<?php echo $img_url?>" style="vertical-align:middle;" border='0'> <span style="font-weight:<?php echo $bold?>; color:<?php echo $txt_color ?>!important"><?php echo $subject ?></span></a></span>
									<?php if ($event instanceof ProjectEvent) { ?>
									</td><td align="right">
									<input type="checkbox" style="width:13px;height:13px;vertical-align:top;margin:2px 2px 0 0;border-color: <?php echo $border_color ?>;" id="sel_<?php echo $event->getId()?>" name="obj_selector" onclick="og.eventSelected(this.checked);og.disableEventPropagation(event);"></input>
									<?php } ?>
									</td></tr></table>
								</div>
							</div>
							<div class="t3 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"></div>
						</div>
						<script>
							addTip('<?php echo $div_prefix . $event->getId() ?>', <?php echo json_encode($divtype . $subject) ?>, <?php echo json_encode($tipBody) ?>);
						</script>
						<?php
								$top += 20;
							}
						?>
					</div>
				</div>
				<div id="gridcontainer" class="toprint" style="background-color:#fff; overflow-x:hidden; overflow-y:scroll; height:504px; position:relative;" >	
						<div id='calowner' style="display:block; width:100%;">  
							<table cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed; width: 100%;height: 1008px;">
								<tr>
									<td id="rowheadcell" style="width: 40px;">
										<div id="rowheaders" style="height: 1008px; top: 0pt; left: 0pt;">										
										<?php
											$horas = array();
											$curr_hour = date("H");
											for ($hour=0; $hour<=23; $hour++){	
												$horas[$hour]	= 0;
												$procesados[$hour] = 0;
										?>
											<div style="height: 41px; top: 0ex;border-right:3px double #DDDDDD !important;background: #E8EEF7 none repeat scroll 0%;border-top:1px solid #DDDDDD;left:0pt;width: 100%;" id="rhead<?php echo $hour?>" class="rhead">
												<?php
													$hour == $curr_hour? print("<span id='curr_hour' style='visibility:hidden;height:0px;width:0px'></span>"):print('');
												?>
												<div class="rheadtext" style="text-align:right;padding-right:2px;"><?php echo date($use_24_hours ? "G:i" : "g a", mktime($hour, 0)) ?></div>
											</div>												
										<?php
											}
										?>

										</div>
									</td>
									<td id="gridcontainercell" style="width: auto;position:relative;" >	
										<div id="grid" style="height: 100%;background-color:#fff;position:relative;" class="grid">										
											<?php
												for ($hour=0; $hour<=47; $hour++){	
													if ($hour % 2 == 0){
														$parity = "hruleeven";
														$style="border-top:1px solid #DDDDDD;";
													} else {
														$parity="hruleodd";
														$style="border-top:1px dotted #DDDDDD;";
													}
													$top = (PX_HEIGHT/2) * $hour;
													$div_id = 'h0_'.$hour;
											?>
													<div id="r<?php echo $hour?>"" class="hrule <?php echo $parity?>" style="top: <?php echo $top?>px; height:1px; z-index:1;position:absolute;left:0px;<?php echo $style?>;width:100%"></div>

													<div id="<?php echo $div_id?>" style="<?php echo $style ?>;width:100%;top: <?php echo $top?>px; z-index: 90; height:21px;position:absolute; border-left:3px double #DDDDDD;"
													<?php if (!logged_user()->isGuest()) { ?> 
														onmouseover="if (!og.selectingCells) og.overCell('<?php echo $div_id?>'); else og.paintSelectedCells('<?php echo $div_id?>');"
														onmouseout="if (!og.selectingCells) og.resetCell('<?php echo $div_id?>')";
														onmousedown="og.selectStartDateTime(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, <?php echo date("G",mktime($hour/2))?>, <?php echo ($hour % 2 ==0)?0:30 ?>); og.resetCell('<?php echo $div_id?>'); og.paintingDay=0; og.paintSelectedCells('<?php echo $div_id?>');"
														onmouseup="og.showEventPopup(<?php echo $dtv->getDay() ?>, <?php echo $dtv->getMonth()?>, <?php echo $dtv->getYear()?>, <?php echo date("G",mktime(($hour+1)/2))?>, <?php echo (($hour+1) % 2 ==0)?0:30 ?>, <?php echo ($use_24_hours ? 'true' : 'false'); ?>,'<?php echo $dtv->format($date_format) ?>', '<?php echo $genid?>', 0,false);">
													<?php } else { echo ">"; }// to close the opening div tag ?>
													</div>

													<script>
														og.ev_cell_dates[0] = {day:<?php echo $dtv->getDay() ?>, month:<?php echo $dtv->getMonth()?>, year:<?php echo $dtv->getYear()?>}
														var ev_dropzone = new Ext.dd.DropZone('<?php echo $div_id?>', {ddGroup:'ev_dropzone'});
													</script>
											<?php
												}
											?>
											<div id="eventowner" style="z-index: 102;" onclick="og.disableEventPropagation(event) ">
										<?php	
											$cells = array();
											for ($i = 0; $i < 24; $i++) {
												$cells[$i][0] = 0;
												$cells[$i][1] = 0;
											}
											foreach ($result as $event){

												getEventLimits($event, $dtv, $event_start, $event_duration, $end_modified);

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
											foreach ($result as $event){
												
												getEventLimits($event, $dtv, $event_start, $event_duration, $end_modified);

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
												if ($hr_end == 0 && $event_duration->getDay() != $dtv->getDay()) $hr_end = 24;
												$top = PX_HEIGHT * $hr_start + (PX_HEIGHT*(($min_start*100)/(60*100)));
												$bottom = PX_HEIGHT * $hr_end + (PX_HEIGHT*(($min_end*100)/(60*100)));
												$height = $bottom-$top;
												
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
												
												$width = 100 / $evs_same_time;
												$left = $width * $posHoriz + 0.25;
												$width -= 0.5;
												//provisional fix
												if($evs_same_time == 1){
													$left = 0.25;
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
												
												//if ($posHoriz+1 == $evs_same_time) $width = $width - 0.75;
												$procesados[$hr_start]++;
												
												$event_duration->add('s', 1);
												$ev_duration = DateTimeValueLib::get_time_difference($event_start->getTimestamp(), $event_duration->getTimestamp()); 

												if ($event instanceof ProjectEvent) {
													$real_start = new DateTimeValue($event->getStart()->getTimestamp() + 3600 * logged_user()->getTimezone());
													$real_duration = new DateTimeValue($event->getDuration()->getTimestamp() + 3600 * logged_user()->getTimezone());
												} else if ($event instanceof ProjectTask) {
													if ($event->getStartDate() instanceof DateTimeValue) {
														$real_start = new DateTimeValue($event->getStartDate()->getTimestamp() + 3600 * logged_user()->getTimezone());
													} else {
														$real_start = $event_start;
													}
													if ($event->getDueDate() instanceof DateTimeValue) {
														$real_duration = new DateTimeValue($event->getDueDate()->getTimestamp() + 3600 * logged_user()->getTimezone());
													} else {
														$real_duration = $event_duration;
													}
												}
												
												$pre_tf = $real_start->getDay() == $real_duration->getDay() ? '' : 'D j, ';
												$ev_hour_text = format_date($real_start, $pre_tf.$timeformat, 0) . " - " . format_date($real_duration, $pre_tf.$timeformat, 0);
												
												$assigned = "";
												if ($event instanceof ProjectTask && $event->getAssignedToContactId() > 0) {
													$assigned = "<br>" . lang('assigned to') .': '. $event->getAssignedToName();
													$tipBody = purify_html($event->getText());
												} else {
											
													$tipBody = $ev_hour_text . $assigned . (trim(clean($event->getDescription())) != '' ? '<br><br>' . clean($event->getDescription()) : '');
													$tipBody = str_replace(array("\r", "\n"), array(' ', '<br>'), $tipBody);
												}
												if (strlen_utf($tipBody) > 200) $tipBody = substr_utf($tipBody, 0, strpos($tipBody, ' ', 200)) . ' ...';
										?>
												<script>
													if (<?php echo $top; ?> < scroll_to || scroll_to == -1) {
														scroll_to = <?php echo $top;?>;
													}
													addTip('d_ev_div_' + <?php echo $event->getId() ?>, <?php echo json_encode(clean($event->getObjectName())) ?>, <?php echo json_encode($tipBody); ?>);
												</script>
												
<?php
				$all_event_colors = array_reverse($all_event_colors);
				$color_left = $left;
				$color_idx = 0;
				foreach ($all_event_colors as $color => $color_class) {
					$color_width = $width / count($all_event_colors);
					$color_left = $left + $color_width * $color_idx;
					$color_idx++;
?>
						<div id="d_ev_div_<?php echo $event->getId() . $id_suffix?>_colors_<?php echo $color_idx?>" class="chip <?php echo $color_class ?> d_ev_div_<?php echo $event->getId() . $id_suffix?>_colors"
						style="position: absolute; top: <?php echo $top?>px; left: <?php echo $color_left?>%; width: <?php echo $color_width?>%;height:<?php echo $height ?>px;z-index:100;"></div>
<?php 			} ?>
												
												<div id="d_ev_div_<?php echo $event->getId()?>" class="chip" style="position: absolute; top: <?php echo $top?>px; left: <?php echo $left?>%; width: <?php echo $width?>%;z-index:120;height: <?php echo $height ?>px;"  onclick="og.disableEventPropagation(event)">
													<div class="t1 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 2px 0px 2px;height:0px; border-bottom:1px solid;border-color:<?php echo $border_color ?>"></div>
													<div class="t2 <?php echo $ws_class ?>" style="<?php echo $ws_style ?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid;border-color:<?php echo $border_color ?>;"></div>
													<div id="inner_d_ev_div_<?php echo $event->getId()?>" class="chipbody edit" style="height: <?php echo $height ?>px;">
													<div style="overflow:hidden;height:100%;border-left: 1px solid;border-right: 1px solid;border-color:<?php echo $border_color ?>;">
														<table style="width:100%;"><tr><td>
														<?php if ($event instanceof ProjectEvent) { ?>
															<input type="checkbox" style="width:13px;height:13px;vertical-align:top;margin-top:2px 0 0 2px;border-color: <?php echo $border_color ?>;" id="sel_<?php echo $event->getId()?>" name="obj_selector" onclick="og.eventSelected(this.checked);"></input>
														<?php } ?>
															<a href='<?php echo $event->getViewUrl()."&amp;view=day&amp;user_id=".$user_filter ?>' class='internalLink' onclick="og.disableEventPropagation(event);" >
															<span name="d_ev_div_<?php echo $event->getId()?>_info" style="color:<?php echo $txt_color?>!important;padding-left:5px;"><?php echo $ev_hour_text; ?></span>
															</a>
															
															<?php
																$subject_toshow = $subject;
																if ($event instanceof ProjectTask && $event->getAssignedToContactId() > 0) {
																	$subject_toshow = '<span class="bold">'.$event->getAssignedToName().'</span>: '.$subject_toshow;
																} 
															?>
														
															<a href='<?php echo $event->getViewUrl()."&amp;view=day&amp;user_id=".$user_filter?>'
																onclick="og.disableEventPropagation(event);"
																class='internalLink'><span style="color:<?php echo $txt_color?>!important; font-weight: <?php  if (isset($bold))echo $bold; ?>;"><?php echo $subject_toshow;?></span></a>
															
														</td><td align="right">
														<div align="right" style="padding-right:4px;<?php echo ($ev_duration['hours'] == 0 ? 'height:'.$height.'px;' : '') ?>">
														<?php
														if ($event instanceof ProjectEvent) { 
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
														}?>
														</div>
														</td></tr>
														<tr><td>
															
														</td></tr>
														<tr style="height:100%;">
															<td style="width:100%;" colspan="2"><div style="height: <?php echo $height - PX_HEIGHT ?>px;"></div></td>
														</tr>
														</table>
													</div>
													</div>
  													<div class="b2 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 1px 0px 1px;height:1px; border-left:1px solid;border-right:1px solid; border-color:<?php echo $border_color ?>"> </div>
													<div class="b1 <?php echo  $ws_class?>" style="<?php echo  $ws_style?>;margin:0px 2px 0px 2px;height:0px; border-top:1px solid; border-color:<?php echo $border_color ?>"> </div>
												</div>
												<?php if ($event instanceof ProjectEvent) { ?>
												<script>
													<?php if (!$end_modified) { ?>
													og.setResizableEvent('d_ev_div_<?php echo $event->getId()?>', '<?php echo $event->getId()?>'); //Resize
													<?php } ?>
													<?php $is_repetitive = $event->isRepetitive() ? 'true' : 'false'; ?>
													<?php if (!logged_user()->isGuest()) { ?>
													og.createEventDrag('d_ev_div_<?php echo $event->getId()?>', '<?php echo $event->getId()?>', <?php echo $is_repetitive ?>, '<?php echo $event_start->format('Y-m-d H:i:s') ?>', 'event', false, 'ev_dropzone'); // Drag
													<?php }?>													
												</script>
												<?php } ?>
										<?php
											}
										?>
											</div>
										</div>
									</td>
									<td id="ie_scrollbar_adjust" style="width:0px;"></td>
								</tr>
							</table>
						</div><!--calowner -->															 
				</div><!--gridcontainer -->
			</div>
			</td>
			</tr>
		</table>
	</td>
</tr></table>
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
	var ogCalTT = new og.CalendarTopToolbar({
		renderTo:'calendarPanelTopToolbar'
	});	
	var ogCalSecTT = new og.CalendarSecondTopToolbar({
		usersHfId:'hfCalUsers',
		companiesHfId:'hfCalCompanies',
		renderTo: 'calendarPanelSecondTopToolbar'
	});

	// Mantain the actual values after refresh by clicking Calendar tab.
	var dtv = new Date('<?php echo $dtv->getMonth().'/'.$dtv->getDay().'/'.$dtv->getYear() ?>');
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
	og.drawCurrentHourLine(0, 'd_');
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