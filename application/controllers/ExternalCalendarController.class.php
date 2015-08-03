<?php
require_once ROOT.'/environment/classes/event/CalFormatUtilities.php';
require_once LIBRARY_PATH.'/google/autoload.php';
require_once LIBRARY_PATH.'/google/Client.php';
require_once LIBRARY_PATH.'/google/Service/Calendar.php';
/**
* Controller that is responsible for handling events sync
*/

class ExternalCalendarController extends ApplicationController {

	/**
	* Construct the EventController
	*
	* @access public
	* @param void
	* @return EventController
	*/
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		$this->addHelper('calendar');
	} // __construct
     
	
	function init() {
		require_javascript("og/CalendarManager.js");
		ajx_current("panel", "events", null, null, true);
		ajx_replace(true);
	}
		
	private function update_sync_cron_events() {
		$count = ExternalCalendarUsers::count();
		$events = CronEvents::findAll(array('conditions' => "name IN ('import_google_calendar','export_google_calendar')"));
		foreach ($events as $event) {
			if ($count > 0) {
				if (!$event->getEnabled()) {
					$event->setEnabled(true);
					$event->save();
				}
			} else {
				if ($event->getEnabled()) {
					$event->setEnabled(false);
					$event->save();
				}
			}
		}
	}
	
	/**
	 * This function connect with google and return the service
	 * @param ExternalCalendarUser $user
	 * @return Google_Service_Calendar
	 */
	private function connect_with_google_calendar($user) {
		$client = new Google_Client();
		$client->setAccessToken($user->getAuthPass());
		
		//if isAccessTokenExpired then refresh the token and store it
		if($client->isAccessTokenExpired()) {
			$credentials = json_decode($user->getAuthPass());
			$client->refreshToken($credentials->refresh_token);
			$user->setAuthPass($client->getAccessToken());
			$user->save();
		}
			
		$service = new Google_Service_Calendar($client);
		
		return $service;
	}
	
	// get member selectors for add to the view
	function get_rendered_member_selectors() {
		$members = array();
		$objectId = 0;
		if(array_var($_GET, 'id')){
			$objectId = array_var($_GET, 'id');
			$user = ExternalCalendarUsers::findByContactId();
			$calendar = ExternalCalendars::findOne(array('conditions' => array("original_calendar_id=? AND ext_cal_user_id=?", $objectId, $user->getId())));
			$object_type_id = ProjectEvents::instance()->getObjectTypeId();
			$members_ids = explode(",", $calendar->getRelatedTo());
			foreach($members_ids as $members_id){
				$members[] = $members_id;
			}			
		}
		
		$genid = array_var($_GET, 'genid');
		$listeners = array();
	
		//ob_start — Turn on output buffering
		//no output is sent from the script (other than headers), instead the output is stored in an internal buffer.
		ob_start();
			
		
		if (count($members) > 0) {
			render_member_selectors(ProjectEvents::instance()->getObjectTypeId(), $genid, $members, array('listeners' => $listeners),null,null,false);
		} else {
			render_member_selectors(ProjectEvents::instance()->getObjectTypeId(), $genid,null, array('select_current_context' => true, 'listeners' => $listeners),null,null,false);
		}
	
		ajx_current("empty");
	
		//Gets the current buffer contents and delete current output buffer.
		//ob_get_clean() essentially executes both ob_get_contents() and ob_end_clean().
		ajx_extra_data(array("htmlToAdd" => ob_get_clean()));
		ajx_extra_data(array("objectId" => $objectId));
	
	
	} // get_rendered_member_selectors
	
	function classify_calendar() {
		ajx_current("empty");
	
		if (!array_var($_POST, 'original_calendar_id')) {
			flash_error(lang('must enter a calendar name'));
			ajx_current("empty");
			return;
		}
		$original_calendar_id = array_var($_POST, 'original_calendar_id');
	
		$users = ExternalCalendarUsers::findByContactId();
	
		$calendar = ExternalCalendars::findOne(array('conditions' => array("original_calendar_id=? AND ext_cal_user_id=?", $original_calendar_id, $users->getId())));
					 
		if($calendar){
			$member_ids = json_decode(array_var($_POST, 'members'));
            $members = "";
            foreach($member_ids as $member_id){
            	$members .= $member_id.",";
            }
            $members = rtrim($members, ",");
            $calendar->setRelatedTo($members);
            $calendar->save();
			flash_success(lang('success edit calendar'));
			ajx_current("reload");
		}
	}

    function calendar_sinchronization() {
        $user = ExternalCalendarUsers::findByContactId();
        $user_data = array();
                                               
        $client = new Google_Client();     

        // Step 2: The user accepted your access now you need to exchange it.
        $google_code = array_var($_GET, 'google_code',false);
        if ($google_code) {
        	try{
        	
        		$credentials = $client->authenticate(urldecode(array_var($_GET, 'google_code')));
        	
	        	$google_acces_token = $client->getAccessToken();
	                
	            $service = new Google_Service_Oauth2($client);
	            $user_info = $service->userinfo->get();
	                
	            $user_email = ExternalCalendarUsers::findByEmail($user_info['email']);
	            if(!$user_email) {
	            	$user_email = new ExternalCalendarUser();
	                $user_email->setAuthUser($user_info['email']);
	                $user_email->setAuthPass($credentials);
	                $user_email->setContactId(logged_user()->getId());
	                $user_email->setType("google");
	                $user_email->save();
	                		
	                $this->update_sync_cron_events();
	            }else{
	            	$user_email->setAuthPass($credentials);
	            	$user_email->save();
	            }
            
            }catch(Exception $e){
            	Logger::log("ERROR Google Sync Step 2");
            	Logger::log($e->getMessage());
            }
		}
                
        $service_is_working = true;
        if($user){   
        	//calendars actions
            $calendars_actions = array();
            //1 = start sync
            $calendars_actions[1] = array();
            $calendars_actions[1]['text'] = lang('start sync');
            $calendars_actions[1]['function'] = 'og.google_calendar_start_sync_calendar';
            //2 = stop sync
            $calendars_actions[2] = array();
            $calendars_actions[2]['text'] = lang('stop sync');
            $calendars_actions[2]['function'] = 'og.google_calendar_stop_sync_calendar';
            //3 = restart sync
            $calendars_actions[3] = array();
            $calendars_actions[3]['text'] = lang('restart sync');
            $calendars_actions[3]['function'] = 'og.google_calendar_start_sync_calendar';
            //4 = delete calendar
            $calendars_actions[4] = array();
            $calendars_actions[4]['text'] = lang('delete calendar');
            $calendars_actions[4]['function'] = 'og.google_calendar_delete_calendar';
            //5 = delete calendar
            $calendars_actions[5] = array();
            $calendars_actions[5]['text'] = lang('classify');
            $calendars_actions[5]['function'] = 'og.google_calendar_classify';
            tpl_assign('calendars_actions',$calendars_actions);
              	
            //calendars status
            //1 = never sync
            //2 = in sync
            //3 = not in sync
            //4 = deleted from google
            $calendars_status = array();
            //1 = never sync
            $calendars_status[1]  = array();
            $calendars_status[1]['text'] = lang('never sync');
            $calendars_status[1]['actions'] = array(1);
            //2 = in sync
            $calendars_status[2]  = array();
            $calendars_status[2]['text'] = lang('in sync');
            $calendars_status[2]['actions'] = array(2,4,5);
            //3 = not in sync
            $calendars_status[3]  = array();
            $calendars_status[3]['text'] = lang('not in sync');
            $calendars_status[3]['actions'] = array(3,4);
            //4 = deleted from google
            $calendars_status[4]  = array();
            $calendars_status[4]['text'] = lang('deleted from google');
            $calendars_status[4]['actions'] = array(4);
            //5 = delete calendar
            $calendars_actions[5] = array();
            $calendars_actions[5]['text'] = lang('classify');
            $calendars_actions[5]['function'] = 'og.google_calendar_classify';
            tpl_assign('calendars_status',$calendars_status);
              	
	        $user_data['id'] = $user->getId();
	        $user_data['auth_user'] = $user->getAuthUser();
	        $user_data['sync'] = $user->getSync();
	        tpl_assign('user',$user_data);
	            	  	
	        // Step 3: We have access we can now create our service	            	
	        try{
	        	$service = $this->connect_with_google_calendar($user);
		            	            	  	
		        $calendarList  = $service->calendarList->listCalendarList();
		        
		        $instalation = explode("/", ROOT_URL);
		        $instalation_name = end($instalation);
		        $feng_calendar_name = lang('feng calendar',$instalation_name);
		            		
		        while(true) {
			    	foreach ($calendarList->getItems() as $calendarListEntry) {
			    		//is feng calendar
			    		if ($calendarListEntry->getSummary() == $feng_calendar_name){
			    			continue;			    			
			    		}
			    		 
			        	$external_calendars[$calendarListEntry->getId()] = array('original_calendar_id' => $calendarListEntry->getId(), 'title' => $calendarListEntry->getSummary() , 'calendar_status' => 1);
			        }
			            	  	
			        $pageToken = $calendarList->getNextPageToken();
			        if ($pageToken) {
			        	$optParams = array('pageToken' => $pageToken);
			            $calendarList = $service->calendarList->listCalendarList($optParams);
			        } else {
			        	break;
			        }
			    }
			}catch(Exception $e){
		    	$service_is_working = false;
		    }

		    //Calendars status
	        $view_calendars = array();
	        $calendars = ExternalCalendars::findByExtCalUserId($user->getId(),true);	       
            foreach ($calendars as $ext_calendar){
            	$view_calendar = array();
            	$view_calendar['original_calendar_id'] = $ext_calendar->getOriginalCalendarId();
            	$view_calendar['title'] = $ext_calendar->getCalendarName();
            	
            	
            	$members = array();
            	$member_ids = explode(",",$ext_calendar->getRelatedTo());
            	foreach ($member_ids as $member_id){
            		$members[$member_id] = $member_id;
            	}
            	if(count($members)){
            		$view_calendar['members'] = json_encode(array(1=>$members));
            	}  
            	$view_calendar['calendar_id'] = $ext_calendar->getId();
            	
            	//deleted on google
            	$view_calendar['calendar_status'] = 4;
	            if(array_key_exists($ext_calendar->getOriginalCalendarId(), $external_calendars)){
	            	//not in sync
	            	$view_calendar['calendar_status'] = 3;
	            	if($ext_calendar->getSync()){
	            		//in sync
	            		$view_calendar['calendar_status'] = 2;
	            	}
	            	unset($external_calendars[$ext_calendar->getOriginalCalendarId()]);
	            }            

	            $view_calendars[$ext_calendar->getOriginalCalendarId()] = $view_calendar;
	        }
	        
	        $all_calendars = array_merge($external_calendars,$view_calendars);
	        ksort($all_calendars);
	        tpl_assign('external_calendars',$all_calendars); 

	        $sync_from_feng = false;
	        if($user->getSync() == 1){
	        	$calendar_feng = ExternalCalendars::findFengCalendarByExtCalUserIdValue($user->getId());
	        	if($calendar_feng instanceof ExternalCalendar && $calendar_feng->getSync() == 0){
	        		$sync_from_feng = false;
	        	}else{
	        		$sync_from_feng = true;
	        	}
	        }
	        
	        if($sync_from_feng){
	        	$sync_from_feng_action = "og.google_calendar_stop_sync_feng_calendar";
	        	$sync_from_feng_color = "2";
	        	$sync_from_feng_text = lang('stop sync');	        	
	        }else{
	        	$sync_from_feng_action = "og.google_calendar_start_sync_feng_calendar";
	        	$sync_from_feng_color = "3";
	        	$sync_from_feng_text = lang('start sync');
	        }
	        tpl_assign('sync_from_feng_action',$sync_from_feng_action);
	        tpl_assign('sync_from_feng_color',$sync_from_feng_color);
	        tpl_assign('sync_from_feng_text',$sync_from_feng_text);
		}
            	
        if(!$user || !$service_is_working){
	    	// Step 1:  The user has not authenticated we give them a link to login
	        if (!$google_code) {
	        	$client->setScopes(array('https://www.googleapis.com/auth/calendar','https://www.googleapis.com/auth/userinfo.email','https://www.googleapis.com/auth/userinfo.profile'));
	            $client->setState(ROOT_URL.'/index.php?c=external_calendar&a=calendar_sinchronization');
	            $authUrl = $client->createAuthUrl();
	            tpl_assign('auth_url',$authUrl);
	        }
        }
	}
	
	function start_sync_feng_calendar() {
		ajx_current("empty");
		if($_POST){				
			$user = ExternalCalendarUsers::findByContactId();	
			if($user){
				try{
					$calendar_feng = ExternalCalendars::findFengCalendarByExtCalUserIdValue($user->getId());
					if($calendar_feng instanceof ExternalCalendar){						
						$calendar_feng->setSync(1);
						$calendar->save();					
					}
					$user->setSync(1);
					$user->save();
					
					ajx_current("reload");
				}catch(Exception $e){
					$service_is_working = false;
				}
			}
		}
	}
	
	function stop_sync_feng_calendar() {
		ajx_current("empty");
		if($_POST){				
			$user = ExternalCalendarUsers::findByContactId();	
			if($user){
				try{
					$calendar_feng = ExternalCalendars::findFengCalendarByExtCalUserIdValue($user->getId());
					if($calendar_feng instanceof ExternalCalendar){						
						$calendar_feng->setSync(0);
						$calendar->save();					
					}
					$user->setSync(0);
					$user->save();
					
					ajx_current("reload");
				}catch(Exception $e){
					$service_is_working = false;
				}
			}
		}
	}
        
    function start_sync_calendar() {
    	ajx_current("empty");
        if($_POST){
        	if (!array_var($_POST, 'original_calendar_id')) {
            	flash_error(lang('must enter a calendar name'));
                ajx_current("empty");
                return;
            }
            $original_calendar_id = array_var($_POST, 'original_calendar_id');
            
            $user = ExternalCalendarUsers::findByContactId();
                       
            if($user){
            	try{
            		$service = $this->connect_with_google_calendar($user);
            		 
            		$google_calendar = $service->calendars->get($original_calendar_id);
            		
            		$calendar = ExternalCalendars::findOne(array('conditions' => array("original_calendar_id=? AND ext_cal_user_id=?", $original_calendar_id, $user->getId())));
            		if($calendar){
            			$calendar->setOriginalCalendarId($google_calendar->getId());            			
            			$calendar->setCalendarName($google_calendar->getSummary());
            			$calendar->setSync(1);
            			$calendar->save();
            		
            			flash_success(lang('success edit calendar'));
            		}else{
            			$calendar = new ExternalCalendar();
            			$calendar->setOriginalCalendarId($google_calendar->getId());            			
            			$calendar->setCalendarName($google_calendar->getSummary());
            			$calendar->setExtCalUserId($user->getId());
            			$calendar->setSync(1);
            			$calendar->save();
            			 
            			flash_success(lang('success add calendar'));
            		}
            		ajx_current("reload");
            	}catch(Exception $e){
            		$service_is_working = false;
            	}            	
            }             
		}         
	}
	
	function stop_sync_calendar() {
		ajx_current("empty");
		if($_POST){
			if (!array_var($_POST, 'original_calendar_id')) {
				flash_error(lang('must enter a calendar name'));
				ajx_current("empty");
				return;
			}
			$original_calendar_id = array_var($_POST, 'original_calendar_id');
	
			$user = ExternalCalendarUsers::findByContactId();
				
			if($user){
				try{
					$calendar = ExternalCalendars::findOne(array('conditions' => array("original_calendar_id=? AND ext_cal_user_id=?", $original_calendar_id, $user->getId())));
					if($calendar){
						$calendar->setSync(0);
						$calendar->save();

						ajx_current("reload");
					}
					
				}catch(Exception $e){
					$service_is_working = false;
					Logger::log($e->getMessage());
				}
			}
		}
	}
        
    function delete_calendar_user() {
    	ajx_current("empty");
                
        try{
        	$cal_users = ExternalCalendarUsers::findByContactId();
            $calendars = ExternalCalendars::findByExtCalUserId($cal_users->getId());                
            foreach ($calendars as $calendar){
            	$events = ProjectEvents::findByExtCalId($calendar->getId());
                foreach ($events as $event){
                	if($calendar->getCalendarFeng() == 0){
                    	$event->trash();
                    }
                    $event->setSpecialID("");
                    $event->setExtCalId(0);
                    $event->save();
                }
                $calendar->delete();
            }
                    
            $cal_users->delete();  

            $this->update_sync_cron_events();

            flash_success(lang('success delete calendar'));
            ajx_current("reload");
        }
        catch(Exception $e)
        {
            flash_error($e->getMessage());
            ajx_current("empty");
        }
	}

    function delete_calendar() {
    	ajx_current("empty");
                                    
        if (!array_var($_POST, 'original_calendar_id')) {
			flash_error(lang('must enter a calendar name'));
			ajx_current("empty");
			return;
		}
		$original_calendar_id = array_var($_POST, 'original_calendar_id');
                
        $users = ExternalCalendarUsers::findByContactId();
                
       	$calendar = ExternalCalendars::findOne(array('conditions' => array("original_calendar_id=? AND ext_cal_user_id=?", $original_calendar_id, $users->getId())));
        
        $events = ProjectEvents::findByExtCalId($calendar->getId());
               
        if($calendar){
        	if($calendar->delete()){
            	if($events){
                	foreach($events as $event){                            
                    	$event->delete();                                                               
                    }
                }
                        
                flash_success(lang('success delete calendar'));
                ajx_current("reload");
            }         
    	} 
	} 

	function delete_event_calendar_extern($event, $ext_user){
		if($event->getExtCalId() > 0 && $event->getSpecialID() != ""){
			$calendar = ExternalCalendars::findById($event->getExtCalId());
			if($calendar instanceof ExternalCalendar && $calendar->getSync() > 0){
				try
				{
					//delete event on google
					$service = $this->connect_with_google_calendar($ext_user);
					$service->events->delete($calendar->getOriginalCalendarId(), $event->getSpecialID());
				}
				catch(Exception $e)
				{
					Logger::log("Fail to delete event: ". $event->getId());
					Logger::log($e->getMessage());
				}
			}				
		}
	}
   	
	function import_google_calendar() {
		$users = ExternalCalendarUsers::findAll();
		foreach ($users as $user){
			// log user in
			$contact = Contacts::findById($user->getContactId());
			CompanyWebsite::instance()->setLoggedUser($contact, false, false, false);
			ExternalCalendarController::import_google_calendar_for_user($user);			
		}
	}
	
	function import_google_calendar_for_user($user) {
		if($user instanceof ExternalCalendarUser){
			$calendars = ExternalCalendars::findByExtCalUserId($user->getId());
			
			$service = $this->connect_with_google_calendar($user);
			
			$contact = Contacts::findById($user->getContactId());		
			
			try
			{
				//update or insert events for calendars
				foreach ($calendars as $calendar){
					if($calendar->getSync() == 0){
						continue;
					}
					
					$optParams = array();
					$syncToken = $calendar->getExternalCalendarPropertyValue("syncToken");
					//if syncToken is not present we have to make a full sync
					if($syncToken){
						//incremental sync get events created or updated from las check
						$optParams['syncToken'] = $syncToken;
					}else{
						//full sync get events starting from past 2 weeks 
						$previous_week = strtotime("-2 week");
						$time_min = date(DATE_RFC3339,$previous_week);					
						$optParams['timeMin'] = $time_min;							
					}
					
					//Try to get events for this calendar
					try{
						$events = $service->events->listEvents($calendar->getOriginalCalendarId(),$optParams);
					}catch(Exception $e){
						Logger::log("Fail to get events from external calendar: ". $calendar->getId());
						Logger::log($e->getMessage());
						
						//remove the syncToken for this calendar so the next time we do a full sync
						$syncTokenProp = $calendar->getExternalCalendarProperty("syncToken");
						if($syncTokenProp){
							$syncTokenProp->delete();
						}	
											
						//go to the next calendar
						continue;
					}
					
					//Working with events
					while(true) {
						foreach ($events->getItems() as $event) {						
							//check if is a cancelled event
							if($event->getStatus() == "cancelled"){
								$cancelled_event = ProjectEvents::findBySpecialId($event->getId(),$calendar->getId());
								//delete ProjectEvent
								if($cancelled_event instanceof ProjectEvent){
									$cancelled_event->delete();
								}								
								continue;
							}
							
							//check if is a recurrent event
							if(is_array($event->getRecurrence())){
								continue;
							}
							
							//check if is a recurrent event instance
							if(!is_null($event->getRecurringEventId()) && $event->getRecurringEventId() != ''){
								continue;
							}
														
							//get all the data that we need from google event
							$event_id = $event->getId();
							$event_name = $event->getSummary();
							$event_desc = $event->getDescription();
							$event_start_date = ExternalCalendarController::date_google_to_sql($event->getStart());
							$event_end_date = ExternalCalendarController::date_google_to_sql($event->getEnd());
							$event_type = 1;
							if($event->getStart()->getDate()){
								$event_type = 2;
								//set this times because we have a bug with all day events times
								$event_start_date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $event_start_date);
								$event_start_date->advance(12 * 3600);
								$event_start_date = $event_start_date->toMySQL();
								
								$event_end_date = DateTimeValueLib::dateFromFormatAndString(DATE_MYSQL, $event_start_date);
								$event_end_date->advance(1 * 3600);
								$event_end_date = $event_end_date->toMySQL();
							}	
							
							$event_updated_date = EMPTY_DATETIME;
							if(!is_null($event->getUpdated()) && $event->getUpdated() != ''){
								$event_updated_date_str = strtotime($event->getUpdated());
								$event_updated_date = date(DATE_MYSQL,$event_updated_date_str);
							}
							//Save event							
							try{
								DB::beginWork();
								//if event exist update it
								$new_event = ProjectEvents::findBySpecialId($event_id,$calendar->getId());
								if(!$new_event instanceof ProjectEvent){
									//Create ProjectEvent from google event
									$new_event = new ProjectEvent();
								}
																
								$new_event->setSpecialID($event_id);
								$new_event->setStart($event_start_date);
								$new_event->setDuration($event_end_date);
								$new_event->setTypeId($event_type);
								$new_event->setObjectName($event_name);
								$new_event->setDescription($event_desc);
								$new_event->setUpdateSync($event_updated_date);
								$new_event->setExtCalId($calendar->getId());
								$new_event->save();		
														
								//Invitation insert only if not exists
								$conditions = array('event_id' => $new_event->getId(), 'contact_id' => $user->getContactId());
								if (EventInvitations::findById($conditions) == null) {
										$invitation = new EventInvitation();
										$invitation->setEventId($new_event->getId());
										$invitation->setContactId($user->getContactId());
										$invitation->setInvitationState(1);
										$invitation->setUpdateSync();
										$invitation->setSpecialId($event_id);
										$invitation->save();
								}
		
								//Subscription insert only if not exists
								if (ObjectSubscriptions::findBySubscriptions($new_event->getId(), $contact) == null) {
										$subscription = new ObjectSubscription();
										$subscription->setObjectId($new_event->getId());
										$subscription->setContactId($user->getContactId());
										$subscription->save();
								}
		
								$member = array();
								if($calendar->getRelatedTo()){
										$member_ids = explode(",",$calendar->getRelatedTo());
										foreach ($member_ids as $member_id){
											$member[] = $member_id;
										}									
								}
								$object_controller = new ObjectController();
								$object_controller->add_to_members($new_event, $member,$contact);
								
								DB::commit();
							}
							catch(Exception $e)
							{
								DB::rollback();
								Logger::log("Fail to save event for external calendar user: ". $contact->getId());
								Logger::log($e->getMessage());
							}
						}
						 
						 //getNextSyncToken
						$pageToken = $events->getNextPageToken();
						if ($pageToken) {
							$optParams = array('pageToken' => $pageToken);
							$events = $service->events->listEvents($calendar->getOriginalCalendarId(), $optParams);
						} else {
							$nextSyncToken = $events->getNextSyncToken();
							if ($nextSyncToken) {
								$calendar->setExternalCalendarPropertyValue("syncToken", $nextSyncToken);
							}							
							break;
						}
					}
				}//foreach calendars
			}
			catch(Exception $e)
			{
				Logger::log("Fail to get events for external calendar user: ". $user->getId());
				Logger::log($e->getMessage());
			}
		}
	}
	
	/**
	 *@param $date Google_Service_Calendar_EventDateTime	
	 *@return string a formatted date string
	 */
	private function date_google_to_sql($date){
		if($date instanceof Google_Service_Calendar_EventDateTime){
			$str = $date->getDateTime();
			
			//if not have time
			if($date->getDate()){
				$str = $date->getDate();			
			}
			
			$str = strtotime($str);
			
			return (date(DATE_MYSQL,$str));	
		}else{
			return EMPTY_DATETIME;
		}	
	}
	
	private function update_event_on_google_calendar($event, $ext_calendar, $ext_user, $service){
		$insert_event = false;
		//update event
		if($event->getSpecialID() != ""){
			//First retrieve the event from the google API.
			try{
				$newEvent = $service->events->get($ext_calendar->getOriginalCalendarId(), $event->getSpecialID());	
			}catch(Exception $e){
				Logger::log("Fail to get event from google: ". $event->getId());
				Logger::log($e->getMessage());
				throw $e;
			}
		}
	
		//insert event
		if(!$newEvent instanceof Google_Service_Calendar_Event){
			//create google event
			$newEvent = new Google_Service_Calendar_Event();
			$insert_event = true;
		}
	
		$newEvent->setSummary($event->getObjectName());
		$newEvent->setDescription($event->getDescription());
	
		$start = new Google_Service_Calendar_EventDateTime();
		$end = new Google_Service_Calendar_EventDateTime();
	
		//All day event
		if($event->getTypeId() == 2){
			$star_time = date("Y-m-d",$event->getStart()->getTimestamp());
			$end_time = date("Y-m-d",$event->getDuration()->getTimestamp());
	
			$start->setDate($star_time);
			$end->setDate($end_time);
		}else{
			$star_time = date(DATE_RFC3339,$event->getStart()->getTimestamp());
			$end_time = date(DATE_RFC3339,$event->getDuration()->getTimestamp());
	
			$start->setDateTime($star_time);
			$end->setDateTime($end_time);
		}
		$newEvent->setStart($start);
		$newEvent->setEnd($end);
	
		try{
			if($insert_event){
				// insert event
				$createdEvent = $service->events->insert($ext_calendar->getOriginalCalendarId(), $newEvent);
			}else{
				// update event
				$createdEvent = $service->events->update($ext_calendar->getOriginalCalendarId(), $newEvent->getId(), $newEvent);
			}
		}catch(Exception $e){
			Logger::log("Fail to add event: ". $event->getId());
			Logger::log($e->getMessage());
			throw $e;
		}
	
		$event->setSpecialID($createdEvent->getId());
		$event->setUpdateSync(ExternalCalendarController::date_google_to_sql($createdEvent->getUpdated()));
		$event->setExtCalId($ext_calendar->getId());
		$event->save();
	
		$invitation = EventInvitations::findOne(array('conditions' => array('contact_id = '.$ext_user->getContactId().' AND event_id ='.$event->getId())));
		if($invitation){
			$invitation->setUpdateSync(ExternalCalendarController::date_google_to_sql($createdEvent->getUpdated()));
			$invitation->setSpecialId($createdEvent->getId());
			$invitation->save();
		}
	}
	
	function sync_event_on_extern_calendar($event, $user = null, $calendar = null){				
		//check external user
		if(is_null($user)){
			$user = ExternalCalendarUsers::findByContactId();
		}		
		if (!$user instanceof ExternalCalendarUser) return;
		
		//check external calendar
		if(is_null($calendar)){
			$calendar = ExternalCalendars::findById($event->getExtCalId());
		}		
		if (!$calendar instanceof ExternalCalendar) return;

		//check if calendar sync is activated
		if($calendar->getSync() == 0){
			return;
		}
		
		//connect with google
		$service = $this->connect_with_google_calendar($user);
		
		$this->update_event_on_google_calendar($event,$calendar,$user,$service);
			
	}    
   
	function export_google_calendar() {
		$users = ExternalCalendarUsers::findAll(array('conditions' => "sync = 1"));
		foreach ($users as $user){
			// log user in
			$contact = Contacts::findById($user->getContactId());
			CompanyWebsite::instance()->logUserIn($contact);
			ExternalCalendarController::export_google_calendar_for_user($user);
			CompanyWebsite::instance()->logUserOut();
		}
	}
		
	function export_google_calendar_for_user($user) {		           
        $service = $this->connect_with_google_calendar($user);     
        
        if($user->getSync() == 1){
        	$calendar_feng = ExternalCalendars::findFengCalendarByExtCalUserIdValue($user->getId());
        	
        	//get events starting from past 2 weeks
        	$previous_week = strtotime("-2 week");
        	$time_min = date(DATE_MYSQL,$previous_week);
        	
            $events = ProjectEvents::findNoSync($user->getContactId(), $time_min, 100);
            $events_inv = ProjectEvents::findNoSyncInvitations($user->getContactId(), $time_min, 100);
                    
            try{
            	if ($calendar_feng instanceof ExternalCalendar){  
            		$events_and_inv = array_merge($events, $events_inv);          		
                	foreach ($events_and_inv as $event){
                		$this->update_event_on_google_calendar($event,$calendar_feng,$user,$service);                		
                    }

            		//we ask for events in this calendar in order to prevent checking the uploaded events on the import
                    try{
                    	$now = strtotime("now");
						$time_min = date(DATE_RFC3339,$now);					
						$optParams['timeMin'] = $time_min;	                    	
                    	$events = $service->events->listEvents($calendar_feng->getOriginalCalendarId(),$optParams);
                    }catch(Exception $e){
                    	Logger::log("Fail to get events from feng external calendar: ". $calendar->getId());
                    	Logger::log($e->getMessage());
                    }
                    
                    //update the calendar token 
                    $nextSyncToken = $events->getNextSyncToken();                   
                    if ($nextSyncToken) {
                    	$calendar_feng->setExternalCalendarPropertyValue("syncToken", $nextSyncToken);
                    }      
                }else{
                	//create feng calendar on google if not exists and save it on feng
                   	$instalation = explode("/", ROOT_URL);
                    $instalation_name = end($instalation);
                    $calendar_name = lang('feng calendar',$instalation_name);
                    $calendar_exists = false;
                    //check if calendar exists
                    try{                    	        	 
                    	$calendarList  = $service->calendarList->listCalendarList();                    
                    	while(true) {
                    		foreach ($calendarList->getItems() as $calendarListEntry) {
                    			if($calendarListEntry->getSummary() == $calendar_name){
                    				$calendar_exists = true;
                    				$external_calendar = array('original_calendar_id' => $calendarListEntry->getId(), 'title' => $calendarListEntry->getSummary() , 'calendar_status' => 1);
 									break;                   			
                    			}
                    		}
                    		 
                    		$pageToken = $calendarList->getNextPageToken();
                    		if ($pageToken) {
                    			$optParams = array('pageToken' => $pageToken);
                    			$calendarList = $service->calendarList->listCalendarList($optParams);
                    		} else {
                    			break;
                    		}
                    	}
                    }catch(Exception $e){
                    	Logger::log("Fail to get calendars list from google: ". $user->getContactId());
						throw $e;
                    }
                    
                    if(!$calendar_exists){
                    	$new_calendar = new Google_Service_Calendar_Calendar();
                    	$new_calendar->setSummary($calendar_name);
                    	//$calendar->setTimeZone('America/Los_Angeles');
                    	
                    	$createdCalendar = $service->calendars->insert($new_calendar);
                    	$external_calendar = array('original_calendar_id' => $createdCalendar->getId(), 'title' => $createdCalendar->getSummary() , 'calendar_status' => 1);
                    }
                    
                    $calendar = new ExternalCalendar();
                    $calendar->setOriginalCalendarId($external_calendar['original_calendar_id']);
                    //$calendar->setCalendarVisibility($calendar_visibility);
                    $calendar->setCalendarName($external_calendar['title']);
                    $calendar->setExtCalUserId($user->getId());
                    $calendar->setCalendarFeng(1);
                    $calendar->setSync(1);
                    $calendar->save();                    
              	}
                        
                flash_success(lang('success add sync'));
                ajx_current("reload");
           	}
            catch(Exception $e)
            {
            	Logger::log($e->getMessage());                
            }
     	}
	}	
} // EventController


?>
