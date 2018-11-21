<?php

/**
 * Notifier class has purpose of sending various notification to users. Primary
 * notification method is email
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Notifier {

	/** Supported transports **/
	const MAIL_TRANSPORT_MAIL = 'mail()';
	const MAIL_TRANSPORT_SMTP = 'smtp';

	/** Secure connection values **/
	const SMTP_SECURE_CONNECTION_NO  = 'no';
	const SMTP_SECURE_CONNECTION_SSL = 'ssl';
	const SMTP_SECURE_CONNECTION_TLS = 'tls';

	/**
	 * Cached value of echange compatible config option
	 *
	 * @var boolean
	 */
	static public $exchange_compatible = null;
	
	function notifyAction($object, $action, $log_data, $exclude_contacts_ids = null) {

		//Check disabled object types notificactions.
		if(in_array($object->getObjectTypeId(),config_option("disable_notifications_for_object_type"))){
			return;
		}

		if (!$object instanceof ContentDataObject) {
			return;
		}
		if ($object instanceof Comment) {
			$subscribers = $object->getRelObject()->getSubscribers();
		} else {
			$subscribers = $object->getSubscribers();
		}
		if ($object instanceof ProjectEvent && $action == ApplicationLogs::ACTION_ADD) { //remove invited people from subscribers to avoid repeated notifications
			$tmp_subs = array();
			foreach ($subscribers as $person) {
				$inv = EventInvitations::findById(array('event_id' => $object->getId(), 'contact_id' => $person->getId()));
				if (!($inv instanceof EventInvitation)) $tmp_subs[] = $person;
			}
			$subscribers = $tmp_subs;
		}

		//Remove contacts from $exclude_contacts_ids
		if(is_array($exclude_contacts_ids)){
			foreach ($subscribers as $person) {
				if (!in_array($person->getId(), $exclude_contacts_ids)) $tmp_subs[] = $person;
			}
			$subscribers = $tmp_subs;
		}
		
		if ($object instanceof ProjectTask && $action == ApplicationLogs::ACTION_CLOSE) {
			
			// notify users assigned to tasks depending on this tasks that this task has been completed
			self::notifyDependantTaskAssignedUsersOfTaskCompletion($object);
			
		}
		
		if (!is_array($subscribers) || count($subscribers) == 0) return;
		if ($action == ApplicationLogs::ACTION_ADD) {
			self::objectNotification($object, $subscribers, logged_user(), 'new');
		} else if ($action == ApplicationLogs::ACTION_EDIT) {
			self::objectNotification($object, $subscribers, logged_user(), 'modified');
		} else if ($action == ApplicationLogs::ACTION_TRASH) {
			self::objectNotification($object, $subscribers, logged_user(), 'deleted');
		} else if ($action == ApplicationLogs::ACTION_CLOSE) {
			self::objectNotification($object, $subscribers, logged_user(), 'closed');
		} else if ($action == ApplicationLogs::ACTION_OPEN) {
			self::objectNotification($object, $subscribers, logged_user(), 'open');
		} else if ($action == ApplicationLogs::ACTION_SUBSCRIBE) {
			$contactIds = $log_data;
			$contactIds = explode(',', $contactIds);
			foreach ($contactIds as $k => &$contactId) {
				if (!is_numeric($contactId)) unset($contactIds[$k]);
			}
			if (count($contactIds)) {
				$contactIdsStr = implode(',', $contactIds);
				$contacts = Contacts::instance()->findAll(array("conditions"=>" o.id IN (".$contactIdsStr.")"));
			}else {
				$contacts = array();
			}
		
			self::objectNotification($object, $contacts, logged_user(), 'subscribed');
		} else if ($action == ApplicationLogs::ACTION_COMMENT) {
			self::newObjectComment($object, $subscribers);
		} else if ($action == ApplicationLogs::ACTION_UPLOAD) {
			self::objectNotification($object, $subscribers, logged_user(), ApplicationLogs::ACTION_UPLOAD);
		}
		
	}
	
	/**
	 * @return For each localization and timezone will return an array of user groups, with a maximum of 20 users per group.
	 * @param $people array of users to separate in groups
	 */
	static function buildPeopleGroups($people, $object, $ignore_lang_and_timezone=false) {
		$max_users_per_group = 20;
		$groups = array();
		
		if ($ignore_lang_and_timezone) {
			// only group by amount
			$lang_groups = array();
			foreach ($people as $user) {
				$key = "en_us|0";
				if (!isset($lang_groups[$key])) $lang_groups[$key] = array();
				$lang_groups[$key][] = $user;
			}
		} else {
			// group by lang and timezone
			$lang_groups = array();
			foreach ($people as $user) {
				if ($user instanceof Contact && !$user->getDisabled()) {
					$tz_offset = Timezones::getTimezoneOffsetToApply($object, $user);
					$tz = $tz_offset/3600;
					$key = $user->getLocale() ."|". $tz;
					
					if (!isset($lang_groups[$key])) $lang_groups[$key] = array();
					$lang_groups[$key][] = $user;
				}
			}
		}
		
		// set max group size = $max_users_per_group
		foreach ($lang_groups as $key => $users) {
			$exp = explode('|', $key);
			$lang = $exp[0];
			$timezone = $exp[1];
			
			$lang_group = array('lang' => $lang, 'tz' => $timezone, 'groups' => array());
			$group_count = 0;
			$count = 0;
			foreach ($users as $u) {
				if (!isset($lang_group['groups'][$group_count])) $lang_group['groups'][$group_count] = array();
				
				$lang_group['groups'][$group_count][] = $u;
				$count++;
				if ($count >= $max_users_per_group) {
					$count = 0;
					$group_count++;
				}
			}
			$groups[] = $lang_group;
		}
		
		return $groups;
	}

	static function getContext($object,$notification=null) {	    
	    $result = array();
	    $contexts = array();
	    $members =  $object instanceof Comment ? $object->getRelObject()->getMembers() : $object->getMembers();
	    $contexts_names = array();
	    $dimensions_to_add_in_subject = config_option('notifications_add_members_in_subject');
	    
	    // Do not send context when edit a user
	    if(!($object instanceof Contact && $notification == 'modified' && $object->getUserType() > 0)){
	       if(count($members)>0){
        	    foreach ($members as $member){
                    $dim = $member->getDimension();	                
                    if($dim->getIsManageable()){
                        /* @var $member Member */
                        $parent_members = $member->getAllParentMembersInHierarchy();
                        $parents_str = '';
                        $obj_type = ObjectTypes::findById($member->getObjectTypeId());
                        
                        foreach ($parent_members as $pm) {
                            /* @var $pm Member */
                            if (!$pm instanceof Member) continue;
                               $parents_str .= '<span style="'.get_workspace_css_properties($pm->getMemberColor()).'">'. $pm->getName() .'</span>';
                        }
                        $result['parents_str'] = $parents_str;
                        
                        
                        if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
                            if ($obj_type instanceof ObjectType) {
                                $contexts[$dim->getCode()][$obj_type->getName()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
                            }
                        }else{
                            $contexts[$dim->getCode()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
                        }
                        
                        if ($obj_type instanceof ObjectType && in_array($dim->getId(), $dimensions_to_add_in_subject)) {
                           $contexts_names[$dim->getCode()][$obj_type->getName()][] = $member->getName();
                        }
                    }
        	    }
	        }
	    }

        $context_subject = '';
	    foreach ($contexts_names as $contexts_name_dim){
	        foreach ($contexts_name_dim as $context_name_mem_type){
	            foreach ($context_name_mem_type as $context_name_mem){
	                if($context_subject != ''){
	                    $context_subject = '; '.$context_subject;
	                }
	                $context_subject = $context_name_mem.$context_subject;
	            }
	        }
	    }
	    $result['contexts'] = $contexts;
	    $result['context_subject'] = $context_subject;
	    return $result;
	    
	}
	
	static function objectNotification($object, $people, $sender, $notification, $description = null, $descArgs = null, $properties = array(), $links = array()) {

		if (in_array($object->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			return;
		}
		
		Hook::fire('filter_object_notification_people', array('object' => $object, 'notification' => $notification) , $people);
		
		if (!is_array($people) || !count($people)) {
			return;
		}
		if ($sender instanceof Contact) {
			$sendername = $sender->getObjectName();
			$senderemail = $sender->getEmailAddress();
			$senderid = $sender->getId();
		} else {
			$sendername = owner_company()->getObjectName();
			$senderemail = owner_company()->getEmailAddress();
			
			if (!is_valid_email($senderemail)) {
				$administrator = owner_company()->getCreatedBy();
				if (!$administrator instanceof Contact) {
					$administrator = Contacts::findOne(array("conditions" => "user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE name IN ('Super Administrator','Administrator'))", "order" => "user_type"));
				}
				if ($administrator instanceof Contact) {
					$senderemail = $administrator->getEmailAddress();
				}
				
				if (!is_valid_email($senderemail)) {
					$senderemail = 'noreply@example.com';
				}
			}
			$senderid = 0;
		}
		
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());
		$name = $object instanceof Comment ? $object->getRelObject()->getObjectName() : $object->getObjectName();

		$assigned_to = "";
		$assigned_by = "";
		if($object instanceof ProjectTask){
			if($object->getAssignedTo() instanceof Contact){
				$assigned_to = $object->getAssignedToName();
				if($object->getAssignedBy() instanceof Contact) $assigned_by = $object->getAssignedBy()->getObjectName();
			}
		}
		
		$text = "";
		//text, descripction or revision comment
		if ($object->columnExists('text') && trim($object->getColumnValue('text'))) {
			if($object->getObjectTypeId() == "3" || $object->getObjectTypeId() == "5"){
				if(config_option("wysiwyg_tasks") || config_option("wysiwyg_messages")){
					$text = purify_html(nl2br($object->getColumnValue('text')));
				}else{
					$text = escape_html_whitespace("\n" . $object->getColumnValue('text'));
				}
			}else{
				$text = escape_html_whitespace("\n" . $object->getColumnValue('text'));
			}
		}
		if ($object->columnExists('description') && trim($object->getColumnValue('description'))) {
			if($object->getObjectTypeId() == "3" || $object->getObjectTypeId() == "5"){
				if(config_option("wysiwyg_tasks") || config_option("wysiwyg_messages")){
					$text = purify_html(nl2br($object->getColumnValue('description')));
				}else{
					$text = escape_html_whitespace("\n" . $object->getColumnValue('description'));
				}
			}else{
				$text = escape_html_whitespace("\n" . $object->getColumnValue('description'));
			}
		}
		
		$text_comment = "";
		if ($object instanceof ProjectFile && $object->getType() == ProjectFiles::TYPE_DOCUMENT) {
			$revision = $object->getLastRevision();
			if (trim($revision->getComment())) {
				$text_comment = escape_html_whitespace("\n" . $revision->getComment());
			}
		}

		$result = self::getContext($object,$notification);
	    $contexts = $result['contexts'];

		
		$attachments = array();
		try {
			if ($object instanceof ProjectFile && ($object->getAttachToNotification() || $object->getFileType() && $object->getFileType()->getIsImage() && config_option('show images in document notifications') 
					&& in_array($object->getTypeString(), ProjectFiles::$image_types))) {
				
				if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
					$file_path = FileRepository::getBackend()->getFilePath($object->getLastRevision()->getRepositoryId());
				} else {
					$file_path = ROOT . "/tmp/" . $object->getFilename();
					$handle = fopen($file_path, 'wb');
					fwrite($handle, $object->getLastRevision()->getFileContent(), $object->getLastRevision()->getFilesize());
					fclose($handle);
				}
				$att_disposition = 'attachment';
				if (config_option('show images in document notifications') && in_array($object->getTypeString(), ProjectFiles::$image_types)) {
					$att_disposition = 'inline';
				}
				$attachments[] = array(
					'cid' => gen_id() . substr($senderemail, strpos($senderemail, '@')),
					'path' => $file_path,
					'type' => $object->getTypeString(),
					'disposition' => $att_disposition,
					'name' => $object->getFilename(),
				);
			}
		} catch (FileNotInRepositoryError $e) {
			// don't interrupt notifications.
		}
		
		if (trim($name) == "") {
			$name = lang($object->getObjectTypeName()) . " (".lang('id').": " . $object->getId() . ")";
		}
		
		tpl_assign('object', $object);
		tpl_assign('title', $name);//title
		tpl_assign('by', $assigned_by);//by
		tpl_assign('asigned', $assigned_to);//assigned to
		tpl_assign('description', $text);//descripction
		tpl_assign('revision_comment', $text_comment);//revision_comment
		tpl_assign('contexts', $contexts);//contexts
		
		$emails = array();
		
		$grouped_people = self::buildPeopleGroups($people, $object);
		foreach ($grouped_people as $pgroup) {
			$lang = array_var($pgroup, 'lang');
			$timezone = array_var($pgroup, 'tz');
			$group_users = array_var($pgroup, 'groups'); // contains arrays of users, with max size = 20 each one, a single email is sent foreach user group
			
			foreach ($group_users as $users) {
				
				$to_addresses = array();
				foreach ($users as $user) {
					if (logged_user() instanceof Contact && logged_user()->getId() == $user->getId()) {
						$user->notify_myself = logged_user()->notify_myself;
					}
					if ( ($user->getId() != $senderid || $user->notify_myself) && ($object->canView($user) || $user->ignore_permissions_for_notifications)) {
						$email_address = trim($user->getEmailAddress());
						if ($email_address != '') {
							$to_addresses[$user->getId()] = self::prepareEmailAddress($email_address, $user->getObjectName());
						}
					}
				}
				
				// build notification
				if (count($to_addresses) > 0) {
					
					if ($object instanceof Comment) {
						$subscribers = $object->getRelObject()->getSubscribers();
					} else {
						$subscribers = $object->getSubscribers();
					}
					//ALL SUBSCRIBERS
					if(count($subscribers) > 0){
						$string_subscriber = '';
						$total_s = count($subscribers);
						$c = 0;
						foreach ($subscribers as $subscriber){
							$c++;
							if($c == $total_s && $total_s > 1){
								$string_subscriber .= " " . lang('and') . " ";
							}else if($c > 1){
								$string_subscriber .= ", ";
							}
					
							$string_subscriber .= $subscriber->getFirstName();
							if($subscriber->getSurname() != "")
								$string_subscriber .=" " . $subscriber->getSurname();
					
						}
						tpl_assign('subscribers', $string_subscriber);// subscribers
					}
					
					// send notification on user's locale and with user info
					Localization::instance()->loadSettings($lang, ROOT . '/language');
					
					if ($object instanceof Comment) {
						$object_comment = Objects::findObject($object->getRelObjectId());
						$object_type_name = $object_comment->getObjectTypeName();
					} else {
						$object_type_name = '';
					}
					
					$object_type = strtolower(lang($object_type_name));
					if($object_type_name != ""){
						tpl_assign('object_comment_name',lang("the " . strtolower($object_type_name) . " notification"));//object_comment_name
					}
					
					if (!isset($description)) {
						$descArgs = array(clean($name), $sendername, $object_type, $object->getCreatedByDisplayName());
						$description = "$notification notification $type desc";
					}else{//reminders
						$date = "";
						//due
						if ($object->columnExists('due_date') && $object->getColumnValue('due_date')) {
							if ($object->getColumnValue('due_date') instanceof DateTimeValue) {
								$date = Localization::instance()->formatDescriptiveDate($object->getColumnValue('due_date'), $timezone);
								$time = Localization::instance()->formatTime($object->getColumnValue('due_date'), $timezone);
								if($time > 0) {
									$date .= " " . $time;
								}
							}
						}
						//start
						if ($object->columnExists('start') && $object->getColumnValue('start')) {
							if ($object->getColumnValue('start') instanceof DateTimeValue) {
								$date = Localization::instance()->formatDescriptiveDate($object->getColumnValue('start'), $timezone);
								$time = Localization::instance()->formatTime($object->getColumnValue('start'), $timezone);
								if($time > 0) {
									$date .= " " . $time;
								}
							}
						}
						$descArgs = array(clean($name), ($date!="" ? $date : $sendername), $object_type, $object->getCreatedByDisplayName(),$date);
					}
					tpl_assign('description_title', langA($description, $descArgs));//description_title
					
					tpl_assign('priority', '');//priority
					if ($object->columnExists('priority') && trim($object->getColumnValue('priority'))) {
						if ($object->getColumnValue('priority') >= ProjectTasks::PRIORITY_URGENT) {
							$priorityColor = "#FF0000";
							$priority = lang('urgent priority');
						}else if ($object->getColumnValue('priority') >= ProjectTasks::PRIORITY_HIGH) {
							$priorityColor = "#FF9088";
							$priority = lang('high priority');
						} else if ($object->getColumnValue('priority') <= ProjectTasks::PRIORITY_LOW) {
							$priorityColor = "white";
							$priority = lang('low priority');
						}else{
							$priorityColor = "#DAE3F0";
							$priority = lang('normal priority');
						}
						tpl_assign('priority', array($priority,$priorityColor));//priority
					}
					
					//ESPECIAL ASSIGNED FOR EVENTS
					tpl_assign('start', '');//start
					tpl_assign('time', '');//time
					tpl_assign('duration', '');//duration
					tpl_assign('guests', '');// invitations
					tpl_assign('start_date', '');//start_date
					tpl_assign('due_date', '');//due_date
					
					$event_ot = ObjectTypes::findByName('event');
					if ($object->getObjectTypeId() == $event_ot->getId()) {
						//start
						if ($object->getStart() instanceof DateTimeValue) {
							$date = Localization::instance()->formatDescriptiveDate($object->getStart(), $timezone);
							$time = Localization::instance()->formatTime($object->getStart(), $timezone);
							tpl_assign('start', $date);//start
							if ($object->getTypeId() != 2) {
								tpl_assign('time', $time);//time
							}
						}
					
						if ($object->getTypeId() != 2) {
							//duration
							if ($object->getDuration() instanceof DateTimeValue) {
								$durtime = $object->getDuration()->getTimestamp() - $object->getStart()->getTimestamp();
								$durhr  = ($durtime / 3600) % 24;   //seconds per hour
								tpl_assign('duration', $durhr." hs");//duration
							}
						}else{
							tpl_assign('duration', lang('all day event'));//duration
						}
					
						//invitations
						$guests = "";
						$send_link = array();
						$invitations = EventInvitations::findAll(array ('conditions' => 'event_id = ' . $object->getId()));
						if (isset($invitations) && is_array($invitations)) {
							foreach ($invitations as $inv) {
								$inv_user = Contacts::findById($inv->getContactId());
								if ($inv_user instanceof Contact) {
									if (can_access($inv_user, $object->getMembers(),ProjectEvents::instance()->getObjectTypeId(), ACCESS_LEVEL_READ)) {
										$state_desc = lang('pending response');
										if ($inv->getInvitationState() == 1) $state_desc = lang('yes');
										else if ($inv->getInvitationState() == 2) $state_desc = lang('no');
										else if ($inv->getInvitationState() == 3) $state_desc = lang('maybe');
										$guests .= '<div style="line-height: 20px; clear:both;">';
										$guests .= '<div style="width: 35%;line-height: 20px; float: left;">' . clean($inv_user->getObjectName()) . '</div>';
										$guests .= '<div style="line-height: 20px; float: left;">' . $state_desc . '</div></div>';
									}
									if($inv->getInvitationState() == 0){
										$send_link[] = $inv_user->getId();
									}
								}
							}
						}
						tpl_assign('guests', $guests);// invitations
					}else{//start date, due date or start
						if ($object->columnExists('start_date') && $object->getColumnValue('start_date')) {
							if ($object->getColumnValue('start_date') instanceof DateTimeValue) {
								$date = Localization::instance()->formatDescriptiveDate($object->getColumnValue('start_date'), $timezone);
								$time = Localization::instance()->formatTime($object->getColumnValue('start_date'), $timezone);
								if($time > 0)
									$date .= " " . $time;
							}
							tpl_assign('start_date', $date);//start_date
						}
						if ($object->columnExists('due_date') && $object->getColumnValue('due_date')) {
							if ($object->getColumnValue('due_date') instanceof DateTimeValue) {
								$date = Localization::instance()->formatDescriptiveDate($object->getColumnValue('due_date'), $timezone);
								$time = Localization::instance()->formatTime($object->getColumnValue('due_date'), $timezone);
								if($time > 0)
									$date .= " " . $time;
							}
							tpl_assign('due_date', $date);//due_date
						}
					}
					
					$toemail = $user->getEmailAddress();
					try {
						$content = FileRepository::getBackend()->getFileContent(owner_company()->getPictureFile());
						if ($content != "" && config_option('show company logo in notifications')) {
							$file_path = ROOT . "/tmp/logo_empresa.png";
							$handle = fopen($file_path, 'wb');
							if ($handle) {
								fwrite($handle, $content);
								fclose($handle);
								$attachments['logo'] = array(
										'cid' => gen_id() . substr($toemail, strpos($toemail, '@')),
										'path' => $file_path,
										'type' => 'image/png',
										'disposition' => 'inline',
										'name' => 'logo_empresa.png',
								);
							}
						}
					} catch (FileNotInRepositoryError $e) {
						unset($attachments['logo']);
					}
					tpl_assign('attachments', $attachments);// attachments
					$from = self::prepareEmailAddress($senderemail, $sendername);
					if (!$toemail) continue;
					
					//this is not used. Why is it here?
					//$from = self::prepareEmailAddress($senderemail, $sendername);
					//PHP7 fix:
					//Previously: if (!$toemail) continue;
					if (!$toemail) return;
					
					$subject = htmlspecialchars_decode(langA("$notification notification $type", $descArgs));

					if ($object instanceof ProjectFile && $object->getDefaultSubject() != ""){
					    $subject = $object->getDefaultSubject();
						tpl_assign('description_title', $subject);
					}
					$recipients_field = config_option('notification_recipients_field', 'to');
					$emails[] = array(
							"object_id" => $object->getId(),
							"$recipients_field" => $to_addresses,
							"from" => self::prepareEmailAddress($senderemail, $sendername),
							"subject" => $subject,
							"body" => tpl_fetch(get_template_path('general', 'notifier')),
							"attachments" => $attachments,
					);
				
				}
			}
		}
		self::queueEmails($emails);
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	}
		
	/**
	 * Send new comment notification to message subscribers
	 *
	 * @param Comment $comment
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newObjectComment(Comment $comment, $all_subscribers) {
		$subscribers = array();
		foreach($all_subscribers as $subscriber) {
			$subscribers[] = $subscriber;
		}
		
		$object = $comment->getRelObject();
		if (!$object instanceof ContentDataObject) return;
		
		if (!in_array($object->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			self::objectNotification($comment, $subscribers, logged_user(), 'new');
		}
		
	} // newObjectComment
	
	/**
	 * Reset password and send forgot password email to the user
	 *
	 * @param Contact $user
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function forgotPassword(Contact $user, $token = null) {
		if (! $user instanceof Contact) return;
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $user), $quit);
		if ($quit) return;
		
		//$new_password = $user->resetPassword(true);
		tpl_assign('user', $user);
		//tpl_assign('new_password', $new_password);
		tpl_assign('token',$token);
		
		$administrator = owner_company()->getCreatedBy();
		if (!$administrator instanceof Contact) {
			$administrator = Contacts::findOne(array("conditions" => "user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE name IN ('Super Administrator','Administrator'))", "order" => "user_type"));
		}
		if (!$administrator instanceof Contact) return;
		
		$from_name = trim($administrator->getObjectName());
		if (!$from_name) $from_name = owner_company()->getObjectName();
		
		$from_email = trim($administrator->getEmailAddress());
		if (!$from_email) $from_email = owner_company()->getEmailAddress();

		// send email in user's language
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		$toemail = $user->getEmailAddress();
		//PHP7 fix:
		//Previously: if (!$toemail) continue;
		if (!$toemail) return;
		
		self::queueEmail(
			null,
			array(self::prepareEmailAddress($toemail, $user->getObjectName())),
			null,
			null,
			self::prepareEmailAddress($from_email, $from_name),
			lang('reset password'),
			tpl_fetch(get_template_path('forgot_password', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // forgotPassword
	
	/**
	 * Send password expiration notification email to user 
	 *
	 * @param User $user
	 * @param string $expiration_days
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function passwordExpiration(Contact $user, $expiration_days) {
		tpl_assign('user', $user);
		tpl_assign('exp_days', $expiration_days);

		if (! $user instanceof Contact) return;
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $user), $quit);
		if ($quit) return;
		
		$administrator = owner_company()->getCreatedBy();
		if (!$administrator instanceof Contact) {
			$administrator = Contacts::findOne(array("conditions" => "user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE name IN ('Super Administrator','Administrator'))", "order" => "user_type"));
		}
		if (!$administrator instanceof Contact) return;
		
		$from_name = trim($administrator->getObjectName());
		if (!$from_name) $from_name = owner_company()->getObjectName();
		
		$from_email = trim($administrator->getEmailAddress());
		if (!$from_email) $from_email = owner_company()->getEmailAddress();
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		
		$toemail = $user->getEmailAddress();
		//PHP7 fix:
		//Previously: if (!$toemail) continue;
		if (!$toemail) return;
		
		self::queueEmail(
			null,
			array(self::prepareEmailAddress($toemail, $user->getObjectName())),
			null,
			null,
			self::prepareEmailAddress($from_email, $from_name),
			lang('password expiration reminder'),
			tpl_fetch(get_template_path('password_expiration_reminder', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // passwordExpiration

	/**
	 * Send new account notification email to the user whose accout has been created
	 * (welcome message)
	 *
	 * @param Contact $user
	 * @param string $raw_password
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function newUserAccount(Contact $user, $raw_password) {
		tpl_assign('new_account', $user);
		tpl_assign('raw_password', $raw_password);
		tpl_assign('type_notifier',"specify_pass");
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $user), $quit);
		if ($quit) return;

		$sender = $user->getCreatedBy() instanceof Contact ? $user->getCreatedBy() : owner_company()->getCreatedBy();
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		$toemail = $user->getEmailAddress();
		//PHP7 fix:
		//Previously: if (!$toemail) continue;		
		if (!$toemail) return;
		
		self::queueEmail(
			null,
			array(self::prepareEmailAddress($toemail, $user->getObjectName())),
			null,
			null,
			self::prepareEmailAddress($sender->getEmailAddress(), $sender->getObjectName()),
			lang('your account created'),
			tpl_fetch(get_template_path('new_account', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // newUserAccount
        
	
    static function newUserAccountLinkPassword(Contact $user, $raw_password, $token = null) {
		tpl_assign('new_account', $user);
		tpl_assign('raw_password', $raw_password);
		tpl_assign('type_notifier',"link_pass");
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $user), $quit);
		if ($quit) return;
		
		//generate password
		$new_password = $user->resetPassword(true);
		tpl_assign('token',$token);                

		$sender = $user->getCreatedBy() instanceof Contact ? $user->getCreatedBy() : owner_company()->getCreatedBy();
		
		$locale = $user->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		$toemail = $user->getEmailAddress();
		//PHP7 fix:
		//Previously: if (!$toemail) continue;
		if (!$toemail) return;
		
		self::queueEmail(
			null,
			array(self::prepareEmailAddress($toemail, $user->getObjectName())),
			null,
			null,
			self::prepareEmailAddress($sender->getEmailAddress(), $sender->getObjectName()),
			lang('your account created'),
			tpl_fetch(get_template_path('new_account', 'notifier'))
		); // send
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // newUserAccount


	/**
	 * Send task due notification to the list of users ($people)
	 *
	 * @param ProjectTask $task Due task
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function objectReminder(ObjectReminder $reminder) {
		$object = $reminder->getObject();
		$context = $reminder->getContext();
		$type = $object->getObjectTypeName();
		$date = $object->getColumnValue($context);
		$several_event_subscribers = false;
		Env::useHelper("format");
		$isEvent = ($object instanceof ProjectEvent) ? true : false;			
			
		if ($reminder->getUserId() == 0) {
			$people = $object->getSubscribers();
			if ($isEvent){
				$several_event_subscribers = true;
				$aux = array();
				foreach ($people as $person){        //grouping people by different timezone
					if ($object instanceof ContentDataObject) {
						$tz_offset = Timezones::getTimezoneOffsetToApply($object, $person);
						$time = $tz_offset/3600;
					} else {
						$time = $person->getUserTimezoneValue()/3600;
					}
					if (isset ($aux["$time"])){
						$aux["$time"][] = $person;
					}else{
						$aux["$time"] = array($person);
					}
				}
				foreach ($aux as $tz => $group){
					$string_date = format_datetime($date, 0, $tz);
					self::objectNotification($object, $group, null, "$context reminder", "$context $type reminder desc");
				}
			}
		} else {
			$people = array();
			$rem_user = $reminder->getUser();
			
			if ($rem_user instanceof Contact && $object->isSubscriber($rem_user)) {
				$people = array($reminder->getUser());
				if ($isEvent){
					$tz_offset = Timezones::getTimezoneOffsetToApply($object, $reminder->getUser());
					$time = $tz_offset/3600;
					
					$string_date = format_datetime($date, 0, $time);
				}else{
					$string_date = $date->format("Y/m/d H:i:s");
				}
			}
		}
		
		if(!$several_event_subscribers && count($people) > 0) {
			if (!isset($string_date)) $string_date = format_datetime($date);
			self::objectNotification($object, $people, null, "$context reminder", "$context $type reminder desc");
		}
	} // taskDue
	
	/**
	 * Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEvent(ProjectEvent $object, $people, $notification, $sender) {
		
		if (in_array($object->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			return;
		}
		
		Hook::fire('filter_object_notification_people', array('object' => $object, 'notification' => $notification) , $people);
		
		if(!is_array($people) || !count($people) || !$sender instanceof Contact) {
			return; // nothing here...
		} // if

		$name = $object->getObjectName();
		$type = $object->getObjectTypeName();
		$typename = lang($object->getObjectTypeName());

		tpl_assign('object', $object);
		tpl_assign('title', $name);
		tpl_assign('description', escape_html_whitespace(convert_to_links(clean($object->getDescription()))));//descripction

		//context
		$contexts = array();
		$members = $object->getMembers();
				
		if(count($members)>0){
			foreach ($members as $member){
				$dim = $member->getDimension();
				if($dim->getIsManageable()){
					if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
						$obj_type = ObjectTypes::findById($member->getObjectTypeId());
						if ($obj_type instanceof ObjectType) {
							$contexts[$dim->getCode()][$obj_type->getName()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
						}
					}else{
						$contexts[$dim->getCode()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
					}
				}
			}
		}
		tpl_assign('contexts', $contexts);//folders

		$attachments = array();
		try {
			$content = FileRepository::getBackend()->getFileContent(owner_company()->getPictureFile());
			if ($content && config_option('show company logo in notifications')) {
				$file_path = ROOT . "/tmp/logo_empresa.png";
				$handle = fopen($file_path, 'wb');
				if ($handle) {
					fwrite($handle, $content);
					fclose($handle);
					$attachments['logo'] = array(
						'cid' => gen_id() . substr($sender->getEmailAddress(), strpos($sender->getEmailAddress(), '@')),
						'path' => $file_path,
						'type' => 'image/png',
						'disposition' => 'inline',
						'name' => 'logo_empresa.png',
					);
				}
			}
		} catch (FileNotInRepositoryError $e) {
			unset($attachments['logo']);
		}
		tpl_assign('attachments', $attachments);// attachments
                //invitations
                $invitations = EventInvitations::findAll(array ('conditions' => 'event_id = ' . $object->getId()));
                if (isset($invitations) && is_array($invitations)) {
                    $guests = "";
                    $send_link = array();
                    foreach ($invitations as $inv) {
                        $inv_user = Contacts::findById($inv->getContactId());
                        if ($inv_user instanceof Contact) {
                            if (can_access($inv_user, $object->getMembers(),ProjectEvents::instance()->getObjectTypeId(), ACCESS_LEVEL_READ)) {
                                $state_desc = lang('pending response');
                                if ($inv->getInvitationState() == 1) $state_desc = lang('yes');
                                else if ($inv->getInvitationState() == 2) $state_desc = lang('no');
                                else if ($inv->getInvitationState() == 3) $state_desc = lang('maybe');
                                $guests .= '<div style="line-height: 20px; clear:both;">';
								$guests .= '<div style="width: 35%;line-height: 20px; float: left;">' . clean($inv_user->getObjectName()) . '</div>';            
								$guests .= '<div style="line-height: 20px; float: left;">' . $state_desc . '</div></div>';
                            }
                            if($inv->getInvitationState() == 0){
                                $send_link[] = $inv_user->getId();
                            }
                        }
                    }
                }
                tpl_assign('guests', $guests);// invitations
		
		$emails = array();
		foreach($people as $user) {
			if ($user->getId() != $sender->getId() && !$user->getDisabled()) {
				// send notification on user's locale and with user info
				$locale = $user->getLocale();
				Localization::instance()->loadSettings($locale, ROOT . '/language');
                                
                                //ALL SUBSCRIBERS
                                if($object->getSubscribers()){
                                    $subscribers = $object->getSubscribers();
                                    $string_subscriber = '';
                                    $total_s = count($subscribers);
                                    $c = 0;
                                    foreach ($subscribers as $subscriber){
                                        $c++;
                                        if($c == $total_s && $total_s > 1){
                                            $string_subscriber .= " " . lang('and') . " ";
                                        }else if($c > 1){
                                            $string_subscriber .= ", ";
                                        }

                                        $string_subscriber .= $subscriber->getFirstName();
                                        if($subscriber->getSurname() != "")
                                            $string_subscriber .=" " . $subscriber->getSurname();

                                    }
                                    tpl_assign('subscribers', $string_subscriber);// subscribers
                                }
                                
                                $tz_offset = Timezones::getTimezoneOffsetToApply($object, $user);
                                $tz = $tz_offset/3600;
                                
                                //start
                                if ($object->getStart() instanceof DateTimeValue) {
                                    $date = Localization::instance()->formatDescriptiveDate($object->getStart(), $tz);
                                    $time = Localization::instance()->formatTime($object->getStart(), $tz);
                                    tpl_assign('start', $date);//start
                                    if ($object->getTypeId() != 2) {
                                        tpl_assign('time', $time);//time   
                                    }
                                }
                                
                                if ($object->getTypeId() != 2) {
                                    //duration
                                    if ($object->getDuration() instanceof DateTimeValue) {
                                        $durtime = $object->getDuration()->getTimestamp() - $object->getStart()->getTimestamp();
                                        $durhr  = ($durtime / 3600) % 24;   //seconds per hour
                                        tpl_assign('duration', $durhr." hs");//duration                                  
                                    }
                                }else{
                                    tpl_assign('duration', lang('all day event'));//duration
                                } 
                                
                                $links = array();
                                if(in_array($user->getId(), $send_link)){
                                    $links = array(
                                                array('img' => get_image_url("/16x16/complete.png"),'text' => lang('accept invitation'), 'url' => get_url('event', 'change_invitation_state', array('at' => 1, 'e' => $object->getId(), 'u' => $user->getId()))),
                                                array('img' => get_image_url("/16x16/del.png"),'text' => lang('reject invitation'), 'url' => get_url('event', 'change_invitation_state', array('at' => 2, 'e' => $object->getId(), 'u' => $user->getId()))),
                                            );
                                    $description_title = lang("new notification event invitation", $object->getObjectName(), $sender->getObjectName());
                                    $subject_mail = lang("new notification event", $name, $sender->getObjectName());
                                }else{
                                    $description_title = lang("$notification notification event desc", $object->getObjectName(), $sender->getObjectName());
                                    $subject_mail = lang("$notification notification $type", $name, $typename);
                                }
                                tpl_assign('links', $links);                                
                                tpl_assign('description_title', $description_title);//description_title
                                
				$toemail = $user->getEmailAddress();
				//PHP7 fix:
				//Previously: if (!$toemail) continue;
				if (!$toemail) return;
				
				$emails[] = array(
					"object_id" => $object->getId(),
					"to" => array(self::prepareEmailAddress($toemail, $user->getObjectName())),
					"from" => self::prepareEmailAddress($sender->getEmailAddress(), $sender->getObjectName()),
					"subject" => $subject = $subject_mail,
					"body" => tpl_fetch(get_template_path('general', 'notifier')),
                                        "attachments" => $attachments
				);
			}
		}// foreach
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		self::queueEmails($emails);
	} // notifEvent
	
	 /** Send event notification to the list of users ($people)
	 *
	 * @param ProjectEvent $event Event
	 * @param array $people
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	static function notifEventAssistance(ProjectEvent $event, EventInvitation $invitation, $from_user, $invs = null) {
		if ((!$event instanceof ProjectEvent) || (!$invitation instanceof EventInvitation) 
			|| (!$event->getCreatedBy() instanceof Contacts) || (!$from_user instanceof Contact)) {
			return;
		}
		
		tpl_assign('event', $event);
		tpl_assign('invitation', $invitation);
		tpl_assign('from_user', $from_user);

		$assist = array();
		$not_assist = array();
		$pending = array();
		
		if (isset ($invs)){
			foreach ($invs as $inv){
				if ($inv->getUserId() == ($from_user->getId())) continue;
				$decision = $inv->getInvitationState();
				$user_name = Contacts::findById($inv->getUserId())->getObjectName();
				if ($decision == 1){
					$assist[] = ($user_name);
				}else if ($decision == 2){
					$not_assist[] = ($user_name);
				}else{
					$pending[] = ($user_name);
				}
			}
		}

		tpl_assign('assist', $assist);
		tpl_assign('not_assist', $not_assist);
		tpl_assign('pending', $pending);
		
		$people = array($event->getCreatedBy());
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $event->getCreatedBy()), $quit);
		if ($quit) return;
		
		$recepients = array();
		foreach($people as $user) {
			$tz_offset = Timezones::getTimezoneOffsetToApply($event, $user);
			$tz_offset = $tz_offset/3600;
			
			$locale = $user->getLocale();
			Localization::instance()->loadSettings($locale, ROOT . '/language');
			$date = Localization::instance()->formatDescriptiveDate($event->getStart(), $tz_offset);
			if ($event->getTypeId() != 2) $date .= " " . Localization::instance()->formatTime($event->getStart(), $tz_offset);

			tpl_assign('date', $date);
			$toemail = $user->getEmailAddress();
			//PHP7 fix:
			//Previously: if (!$toemail) continue;
			if (!$toemail) return;
			
			self::queueEmail(
				$event->getId(),
				array(self::prepareEmailAddress($toemail, $user->getObjectName())),
				null,
				null,
				self::prepareEmailAddress($from_user->getEmailAddress(), $from_user->getObjectName()),
				lang('event invitation response') . ': ' . $event->getSubject(),
				tpl_fetch(get_template_path('event_inv_response_notif', 'notifier'))
			); // send
		} // foreach
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // notifEvent

	// ---------------------------------------------------
	//  Milestone
	// ---------------------------------------------------

	/**
	 * Milestone has been assigned to the user
	 *
	 * @param ProjectMilestone $milestone
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function milestoneAssigned(ProjectMilestone $milestone) {
		if($milestone->isCompleted()) {
			return true; // milestone has been already completed...
		} // if
		if(!($milestone->getAssignedTo() instanceof Contact)) {
			return true; // not assigned to user
		} // if

		tpl_assign('milestone_assigned', $milestone);
		
		if (! $milestone->getCreatedBy() instanceof Contact) return;
		
		$locale = $milestone->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');
		if ($milestone->getDueDate() instanceof DateTimeValue) {
			$tz_offset = Timezones::getTimezoneOffsetToApply($milestone, $milestone->getAssignedTo());
			$tz = $tz_offset/3600;
			$date = Localization::instance()->formatDescriptiveDate($milestone->getDueDate(), $tz);
			tpl_assign('date', $date);
		}
		
		$assigned_to = $milestone->getAssignedTo();
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $assigned_to), $quit);
		if ($quit) return;
		
		if ($assigned_to instanceof Contact) {
			$email_address = trim($assigned_to->getEmailAddress());
			if ($email_address != '') {
				return self::queueEmail(
					$milestone->getId(),
					array(self::prepareEmailAddress($email_address, $assigned_to->getObjectName())),
					null,
					null,
					self::prepareEmailAddress($milestone->getCreatedBy()->getEmailAddress(), $milestone->getCreatedByDisplayName()),
					lang('milestone assigned to you', $milestone->getObjectName()),
					tpl_fetch(get_template_path('milestone_assigned', 'notifier'))
				); // send
			}
		}
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} // milestoneAssigned

	/**
	 * Task has been assigned to the user
	 *
	 * @param ProjectTask $task
	 * @return boolean
	 * @throws NotifierConnectionError
	 */
	function taskAssigned(ProjectTask $task) {

		if (in_array($task->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			return;
		}
		
		if($task->isCompleted()) {
			return true; // task has been already completed...
		}
		if(!($task->getAssignedTo() instanceof Contact)) {
			return true; // not assigned to user
		}
		if (!is_valid_email($task->getAssignedTo()->getEmailAddress())) {
			return true;
		}		
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $task->getAssignedTo()), $quit);
		if ($quit) return;
		
		tpl_assign('task_assigned', $task);

		$locale = $task->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');

		tpl_assign('title', $task->getObjectName());
		tpl_assign('by', $task->getAssignedBy()->getObjectName());
		tpl_assign('asigned', $task->getAssignedTo()->getObjectName());
		$text = "";
		if(config_option("wysiwyg_tasks")){
			$text = purify_html(nl2br($task->getDescription()));
		}else{
			$text = escape_html_whitespace($task->getDescription());
		}
		tpl_assign('description', $text);//descripction
		tpl_assign('description_title', lang("new task assigned to you desc", $task->getObjectName(),$task->getAssignedBy()->getObjectName()));//description_title

		//priority
		if ($task->getPriority()) {
			if ($task->getPriority() >= ProjectTasks::PRIORITY_URGENT) {
				$priorityColor = "#FF0000";
				$priority = lang('urgent priority');
			}else if ($task->getPriority() >= ProjectTasks::PRIORITY_HIGH) {
				$priorityColor = "#FF9088";
				$priority = lang('high priority');
			} else if ($task->getPriority() <= ProjectTasks::PRIORITY_LOW) {
				$priorityColor = "white";
				$priority = lang('low priority');
			}else{
				$priorityColor = "#DAE3F0";
				$priority = lang('normal priority');
			}
			tpl_assign('priority', array($priority,$priorityColor));
		}

		//ALL SUBSCRIBERS
		if($task->getSubscribers()){
			$subscribers = $task->getSubscribers();
			$string_subscriber = '';
			$total_s = count($subscribers);
			$c = 0;
			foreach ($subscribers as $subscriber){
				$c++;
				if($c == $total_s && $total_s > 1){
					$string_subscriber .= " " . lang('and') . " ";
				}else if($c > 1){
					$string_subscriber .= ", ";
				}

				$string_subscriber .= $subscriber->getFirstName();
				if($subscriber->getSurname() != "")
				$string_subscriber .=" " . $subscriber->getSurname();

			}
			tpl_assign('subscribers', $string_subscriber);// subscribers
		}

		//context
		$contexts = array();
		$members = $task->getMembers();
		if(count($members)>0){
			foreach ($members as $member){
				$dim = $member->getDimension();
				if($dim->getIsManageable()){
					if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
						$obj_type = ObjectTypes::findById($member->getObjectTypeId());
						if ($obj_type instanceof ObjectType) {
							$contexts[$dim->getCode()][$obj_type->getName()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
						}
					}else{
						$contexts[$dim->getCode()][]= '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
					}
				}
			}
		}

		$tz_offset = Timezones::getTimezoneOffsetToApply($task, $task->getAssignedTo());
		$tz = $tz_offset/3600;
		
		tpl_assign('contexts', $contexts);//workspaces
		//start date, due date or start
		if ($task->getStartDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getStartDate(), $tz);
			$time = Localization::instance()->formatTime($task->getStartDate(), $tz);
			if($time > 0) $date .= " " . $time;
			tpl_assign('start_date', $date);//start_date
		}

		if ($task->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getDueDate(), $tz);
			$time = Localization::instance()->formatTime($task->getDueDate(), $tz);
			if($time > 0) $date .= " " . $time;
			tpl_assign('due_date', $date);//due_date
		}
		
		$attachments = array();
		try {
			$content = FileRepository::getBackend()->getFileContent(owner_company()->getPictureFile());
			if ($content && config_option('show company logo in notifications')) {
				$file_path = ROOT . "/tmp/logo_empresa.png";
				$handle = fopen($file_path, 'wb');
				if ($handle) {
					fwrite($handle, $content);
					fclose($handle);
					$attachments['logo'] = array(
						'cid' => gen_id() . substr($task->getAssignedTo()->getEmailAddress(), strpos($task->getAssignedTo()->getEmailAddress(), '@')),
						'path' => $file_path,
						'type' => 'image/png',
						'disposition' => 'inline',
						'name' => 'logo_empresa.png',
					);
					tpl_assign('attachments', $attachments);// attachments
				}
			}
		} catch (FileNotInRepositoryError $e) {
			unset($attachments['logo']);
		}
		tpl_assign('attachments', $attachments);// attachments
		
		$assigned_to = $task->getAssignedTo();
		if ($assigned_to instanceof Contact) {
			$assigned_to_email = trim($assigned_to->getEmailAddress());
			if ($assigned_to_email != '') {
				self::queueEmail(
					$task->getId(),
					array(self::prepareEmailAddress($assigned_to_email, $assigned_to->getObjectName())),
					null,
					null,
					self::prepareEmailAddress($task->getUpdatedBy()->getEmailAddress(), $task->getUpdatedByDisplayName()),
					lang('new task assigned to you',$task->getObjectName()),
					tpl_fetch(get_template_path('task_assigned', 'notifier')),
					'text/html',
					'8bit',
					$attachments
				);
			}
		}
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	} 

        
	function workEstimate(ProjectTask $task) {

		if (in_array($task->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			return;
		}
		
		tpl_assign('task_assigned', $task);
		
		if(!($task->getAssignedTo() instanceof Contact)) {
			return true; // not assigned to user
		}
		if (!is_valid_email($task->getAssignedTo()->getEmailAddress())) {
			return true;
		}
		
		$quit = false;
		Hook::fire('filter_object_notification_single_user', array('user' => $task->getAssignedTo()), $quit);
		if ($quit) return;

		$locale = $task->getAssignedTo()->getLocale();
		Localization::instance()->loadSettings($locale, ROOT . '/language');

		tpl_assign('title', $task->getObjectName());
		tpl_assign('by', $task->getAssignedBy()->getObjectName());
		tpl_assign('asigned', $task->getAssignedTo()->getObjectName());
		$text = "";
		if(config_option("wysiwyg_tasks")){
			$text = purify_html(nl2br($task->getDescription()));
		}else{
			$text = escape_html_whitespace($task->getDescription());
		}
		tpl_assign('description', $text);//descripction
		tpl_assign('description_title', lang("new task work estimate to you desc", $task->getObjectName(),$task->getAssignedBy()->getObjectName()));//description_title

		//priority
		if ($task->getPriority()) {
			if ($task->getPriority() >= ProjectTasks::PRIORITY_URGENT) {
				$priorityColor = "#FF0000";
				$priority = lang('urgent priority');
			}else if ($task->getPriority() >= ProjectTasks::PRIORITY_HIGH) {
				$priorityColor = "#FF9088";
				$priority = lang('high priority');
			} else if ($task->getPriority() <= ProjectTasks::PRIORITY_LOW) {
				$priorityColor = "white";
				$priority = lang('low priority');
			}else{
				$priorityColor = "#DAE3F0";
				$priority = lang('normal priority');
			}
			tpl_assign('priority', array($priority,$priorityColor));
		}
		
		//context		
		$contexts = array();
		$members = $task->getMembers();
		if(count($members)>0){
			foreach ($members as $member){
				$dim = $member->getDimension();
				if($dim->getIsManageable()){
					/* @var $member Member */
					$parent_members = $member->getAllParentMembersInHierarchy();
					$parents_str = '';
					foreach ($parent_members as $pm) {
						/* @var $pm Member */
						if (!$pm instanceof Member) continue;
						$parents_str .= '<span style="'.get_workspace_css_properties($pm->getMemberColor()).'">'. $pm->getName() .'</span>';
					}
					if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
						$obj_type = ObjectTypes::findById($member->getObjectTypeId());
						if ($obj_type instanceof ObjectType) {
							$contexts[$dim->getCode()][$obj_type->getName()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
						}
					}else{
						$contexts[$dim->getCode()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
					}
				}
			}
		}
		tpl_assign('contexts', $contexts);//workspaces
		
		$tz_offset = Timezones::getTimezoneOffsetToApply($task, $task->getAssignedTo());
		$tz = $tz_offset/3600;

		//start date, due date or start
		if ($task->getStartDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getStartDate(), $tz);
			$time = Localization::instance()->formatTime($task->getStartDate(), $tz);
			if($time > 0) $date .= " " . $time;
			tpl_assign('start_date', $date);//start_date
		}

		if ($task->getDueDate() instanceof DateTimeValue) {
			$date = Localization::instance()->formatDescriptiveDate($task->getDueDate(), $tz);
			$time = Localization::instance()->formatTime($task->getDueDate(), $tz);
			if($time > 0) $date .= " " . $time;
			tpl_assign('due_date', $date);//due_date
		}

		$attachments = array();
		try {
			$content = FileRepository::getBackend()->getFileContent(owner_company()->getPictureFile());
			if ($content && config_option('show company logo in notifications')) {
				$file_path = ROOT . "/tmp/logo_empresa.png";
				$handle = fopen($file_path, 'wb');
				if ($handle) {
					fwrite($handle, $content);
					fclose($handle);
					$attachments['logo'] = array(
						'cid' => gen_id() . substr($task->getAssignedBy()->getEmailAddress(), strpos($task->getAssignedBy()->getEmailAddress(), '@')),
						'path' => $file_path,
						'type' => 'image/png',
						'disposition' => 'inline',
						'name' => 'logo_empresa.png',
					);
				}
			}
		} catch (FileNotInRepositoryError $e) {
			unset($attachments['logo']);
		}
		tpl_assign('attachments', $attachments);// attachments

		//ALL SUBSCRIBERS
		if($task->getSubscribers()){
			$subscribers = $task->getSubscribers();
			$string_subscriber = '';
			$total_s = count($subscribers);
			$c = 0;
			foreach ($subscribers as $subscriber){
				$c++;
				if($c == $total_s && $total_s > 1){
					$string_subscriber .= " " . lang('and') . " ";
				}else if($c > 1){
					$string_subscriber .= ", ";
				}

				$string_subscriber .= $subscriber->getFirstName();
				if($subscriber->getSurname() != "")
				$string_subscriber .=" " . $subscriber->getSurname();

			}
			tpl_assign('subscribers', $string_subscriber);// subscribers
		}

		if($task->getAssignedById() == $task->getAssignedToContactId()){
			if (!$task->getAssignedBy()->getDisabled()) {
				$emails[] = array(
                            "to" => array(self::prepareEmailAddress($task->getAssignedBy()->getEmailAddress(), $task->getAssignedBy()->getObjectName())),
                            "from" => self::prepareEmailAddress($task->getUpdatedBy()->getEmailAddress(), $task->getUpdatedByDisplayName()),
                            "subject" => lang('work estimate title'),
                            "body" => tpl_fetch(get_template_path('work_estimate', 'notifier')),
                            "attachments" => $attachments
                        ); 
			}
		}else{
			if (!$task->getAssignedBy()->getDisabled()) {
				$emails[] = array(
							"object_id" => $task->getId(),
                            "to" => array(self::prepareEmailAddress($task->getAssignedBy()->getEmailAddress(), $task->getAssignedBy()->getObjectName())),
                            "from" => self::prepareEmailAddress($task->getUpdatedBy()->getEmailAddress(), $task->getUpdatedByDisplayName()),
                            "subject" => lang('work estimate title'),
                            "body" => tpl_fetch(get_template_path('work_estimate', 'notifier')),
                            "attachments" => $attachments
                        );
			}
			if (!$task->getAssignedTo()->getDisabled()) {
				$emails[] = array(
							"object_id" => $task->getId(),
                            "to" => array(self::prepareEmailAddress($task->getAssignedTo()->getEmailAddress(), $task->getAssignedTo()->getObjectName())),
                            "from" => self::prepareEmailAddress($task->getUpdatedBy()->getEmailAddress(), $task->getUpdatedByDisplayName()),
                            "subject" => lang('work estimate title'),
                            "body" => tpl_fetch(get_template_path('work_estimate', 'notifier')),
                            "attachments" => $attachments
				);
			}
		}
		self::queueEmails($emails);
		
		$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
		Localization::instance()->loadSettings($locale, ROOT . '/language');
	}


	// ---------------------------------------------------
	//  Util functions
	// ---------------------------------------------------

	/**
	 * This function will prepare email address. It will return $name <$email> if both
	 * params are presend and we are not in exchange compatibility mode. In other case
	 * it will just return email
	 *
	 * @param string $email
	 * @param string $name
	 * @return string
	 */
	static function prepareEmailAddress($email, $name = null) {
		if(trim($name)) {
			return trim($name) . ' <' . trim($email) . '>';
		} else {
			return trim($email);
		} // if
	} // prepareEmailAddress

	/**
	 * Returns true if exchange compatible config option is set to true
	 *
	 * @param void
	 * @return boolean
	 */
	static function getExchangeCompatible() {
		if(is_null(self::$exchange_compatible)) {
			self::$exchange_compatible = config_option('exchange_compatible', false);
		} // if
		return self::$exchange_compatible;
	} // getExchangeCompatible

	/**
	 * Send an email using Swift (send commands)
	 *
	 * @param string to_address
	 * @param string from_address
	 * @param string subject
	 * @param string body, optional
	 * @param string content-type,optional
	 * @param string content-transfer-encoding,optional
	 * @return bool successful
	 */
	static function sendEmail($to, $from, $subject, $body = false, $type = 'text/plain', $encoding = '8bit', $attachments = array(), $object_id = 0) {
		$ret = false;
		if (config_option('notification_from_address')) {
			$from = config_option('notification_from_address');
		}
		Hook::fire('notifier_email_body', $body, $body);
		Hook::fire('notifier_email_subject', $subject, $subject);
		Hook::fire('notifier_send_email', array(
			'to' => $to,
			'from' => $from,
			'subject' => $subject,
			'body' => $body,
			'type' => $type,
			'encoding' => $encoding,
		), $ret);
		if ($ret) return true;
		
		Env::useLibrary('swift');

		$mailer = self::getMailer();
		if(!($mailer instanceof Swift_Mailer)) {
			throw new NotifierConnectionError();
		} // if

		// init Swift logger
		if (defined('LOG_SWIFT') && LOG_SWIFT > 0) {
			$swift_logger = new Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($swift_logger));
			$swift_logger_level = LOG_SWIFT; // 0: no log, 1: log only errors, 2: log everything
		} else {
			$swift_logger_level = 0;
		}

		$smtp_address = config_option("smtp_address");
		if (config_option("mail_transport") == self::MAIL_TRANSPORT_SMTP && $smtp_address) {
			$pos = strrpos($from, "<");
			if ($pos !== false) {
				//$sender_address = trim(substr($from, $pos + 1), "> ");
				$sender_name = trim(substr($from, 0, $pos));
				$from_address = str_replace(array("<",">"),array("",""), trim(substr($from, $pos, strlen($from)-1)));
			} else {
				$sender_name = "";
				$from_address = $from;
			}
			
			$from_address = config_option("smtp_address");
			
		} else {
			$pos = strrpos($from, "<");
			if ($pos !== false) {
				$sender_name = trim(substr($from, 0, $pos));
				$sender_address = str_replace(array("<",">"),array("",""), trim(substr($from, $pos, strlen($from)-1)));
			} else {
				$sender_name = "";
				$sender_address = $from;
			}
			
			$from_address = $sender_address;
		}
		
		if (trim($sender_name) == "") {
			$sender_name = owner_company()->getObjectName();
		}

		$from_name = config_option("notification_from_name");
		if (trim($from_name) != "") {
			$sender_name = $from_name;
		}

		$from = array($from_address => $sender_name);

		if (Plugins::instance()->isActivePlugin('mail') && config_option('use_mail_accounts_to_send_nots')) {
			$ma = MailAccounts::instance()->findOne(array("conditions" => "email_addr = '$from_address'"));
			if ($ma instanceof MailAccount) {
				
				$mu = new MailUtilities();
				$from = array($ma->getEmailAddress() => ($sender_name != "" ? $sender_name : $ma->getFromName()));
			
				$mailer = self::getMailer(array(
						'smtp_server' => $ma->getSmtpServer(),
						'smtp_port' => $ma->getSmtpPort(),
						'smtp_secure_connection' => $ma->getOutgoingTrasnportType(),
						'smtp_username' => $ma->smtpUsername(),
						'smtp_password' => $mu->ENCRYPT_DECRYPT($ma->smtpPassword()),
				));
			} else {
				$mailer = $default_mailer;
			}
		}

		//Create the message
		$message = Swift_Message::newInstance($subject)
		  ->setFrom($from)
		  ->setBody($body)
		  ->setContentType($type)
		;
		
		foreach ($attachments as $a) {
			$attach = Swift_Attachment::fromPath(array_var($a, 'path'), array_var($a, 'type'));
			$attach->setDisposition(array_Var($a, 'disposition', 'attachment'));
			if (array_var($a, 'cid')) $attach->setId(array_var($a, 'cid'));
			if (array_var($a, 'name')) $attach->setFilename(array_var($a, 'name'));
			$message->attach($attach);
		}
		
		$message->setContentType($type);
		$to = prepare_email_addresses(implode(",", $to));
		foreach ($to as $address) {
			$message->addTo(array_var($address, 0), array_var($address, 1));
		}
		$result = $mailer->send($message);

		if ($swift_logger_level >= 2 || ($swift_logger_level > 0 && !$result)) {
			file_put_contents(CACHE_DIR."/swift_log_notifier.txt", "\n".gmdate("Y-m-d H:i:s")." DEBUG:\n" . $swift_logger->dump() . "----------------------------------------------------------------------------", FILE_APPEND);
			$swift_logger->clear();
		}

		if ($result) {
			// save notification history when notifications are not sent by cron
			self::saveNotificationHistory(array(
				'to' => json_encode($to),
				'cc' => '',
				'bcc' => '',
				'from' => json_encode($from),
				'subject' => $subject,
				'body' => $body,
				'attachments' => json_encode($attachments),
				'timestamp' => DateTimeValueLib::now()->toMySQL(),
				'object_id' => $object_id,
			));
		}
		
		return $result;
	} // sendEmail
	
	static function queueEmail($object_id, $to, $cc, $bcc, $from, $subject, $body = false, $type = 'text/html', $encoding = '8bit', $attachments = array()) {
		
		$queue_this_email = true;
		Hook::fire('put_email_in_queue', $email_data, $queue_this_email);
		if (!$queue_this_email) {
			return;
		}

		$object = Objects::findObject($object_id);
		if ($object instanceof ContentDataObject) {
		    $result = self::getContext($object,'modified');
		    if($result['context_subject'] != ''){
		        $subject = '['.$result['context_subject'].'] '.$subject;
		    }
		}

		$cron = CronEvents::getByName('send_notifications_through_cron');
		if ($cron instanceof CronEvent && $cron->getEnabled()) {
			$qm = new QueuedEmail();
			// set To
			if (!is_array($to)) {
				$to = array($to);
			}
			$qm->setTo(implode(";", $to));
			// set CC
			if ($cc != null) {
				if (!is_array($cc)) {
					$cc = array($cc);
				}
				$qm->setCc(implode(";", $cc));
			}
			// set BCC
			if ($bcc != null) {
				if (!is_array($bcc)) {
					$bcc = array($bcc);
				}
				$qm->setBcc(implode(";", $bcc));
			}
			// set from
			$qm->setFrom($from);
			// set subject
			$qm->setSubject($subject);
			// set body
			$qm->setBody($body);
			// set attachments
			if ($qm->columnExists('attachments')) {
				$qm->setColumnValue('attachments', json_encode($attachments));
			}
			// related object id
			$qm->setObjectId($object_id);
			
			$qm->save();
		} else {
			// not using cron
			try {
				$sent_ok = self::sendEmail($to, $from, $subject, $body, $type, $encoding, $attachments, $object_id);
			} catch (Exception $e) {
				logger::log_r("Error Sending Notification: " . $e->getMessage());
				logger::log_r($e->getTraceAsString());
				// save log in server
				if (defined('EMAIL_ERRORS_LOGDIR') && file_exists(EMAIL_ERRORS_LOGDIR) && is_dir(EMAIL_ERRORS_LOGDIR)) {
					$err_msg = ROOT_URL."\nError sending notification (subject=$subject) using account $from\n\nError detail:\n".$e->getMessage()."\n".$e->getTraceAsString();
					file_put_contents(EMAIL_ERRORS_LOGDIR . basename(ROOT), $err_msg, FILE_APPEND);
				}
			}
		}
	}
	
	static function queueEmails($emails) {
		foreach ($emails as $email) {
			self::queueEmail(
				array_var($email, 'object_id'),
				array_var($email, 'to'),
				array_var($email, 'cc'),
				array_var($email, 'bcc'),
				array_var($email, 'from'),
				array_var($email, 'subject'),
				array_var($email, 'body'),
				array_var($email, 'type', 'text/html'),
				array_var($email, 'encoding', '8bit'),
				array_var($email, 'attachments')
			);
		}
	}
	
	static function sendQueuedEmails() {
		$date = DateTimeValueLib::now();
		$date->add("d", -2);
		
		$emails = QueuedEmails::getQueuedEmails($date);
		if (count($emails) <= 0) return 0;
		
		Env::useLibrary('swift');
		
		$default_mailer = self::getMailer();
		$mailer = $default_mailer;
		if(!($mailer instanceof Swift_Mailer)) {
			throw new NotifierConnectionError();
		}
		
		$fromSMTP = config_option("mail_transport", self::MAIL_TRANSPORT_MAIL) == self::MAIL_TRANSPORT_SMTP && config_option("smtp_authenticate", false);
		$count = 0;
		foreach ($emails as $email) {
			try {
				if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "Start queued_email_id=".$email->getId()."\n", FILE_APPEND);
				
				$body = $email->getBody();
				$subject = $email->getSubject();
				Hook::fire('notifier_email_body', $body, $body);
				Hook::fire('notifier_email_subject', $subject, $subject);
				
				if ($fromSMTP && config_option("smtp_address")) {
					$pos = strrpos($email->getFrom(), "<");
					if ($pos !== false) {
						$sender_name = trim(substr($email->getFrom(), 0, $pos));
						$from_address = str_replace(array("<",">"),array("",""), trim(substr($email->getFrom(), $pos, strlen($email->getFrom())-1)));
					} else {
						$sender_name = "";
						$from_address = $email->getFrom();
					}
					
					$from_address = config_option("smtp_address");
					if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "using config smtp address\n", FILE_APPEND);
					
				} else {
					$pos = strrpos($email->getFrom(), "<");
					if ($pos !== false) {
						$sender_name = trim(substr($email->getFrom(), 0, $pos));
						$sender_address = str_replace(array("<",">"),array("",""), trim(substr($email->getFrom(), $pos, strlen($email->getFrom())-1)));
					} else {
						$sender_name = "";
						$sender_address = $email->getFrom();
					}
					
					$from_address = $sender_address;
					if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "using user email in from address\n", FILE_APPEND);
				}
				
				if (trim($sender_name) == "") {
					$sender_name = owner_company()->getObjectName();
				}

				$from_name = config_option("notification_from_name");
				if (trim($from_name) != "") {
					$sender_name = $from_name;
				}

				$from = array($from_address => $sender_name);
				
				
				// if exists an email account defined for the sender => use it to send the notification
				if (Plugins::instance()->isActivePlugin('mail') && config_option('use_mail_accounts_to_send_nots')) {
					$ma = MailAccounts::instance()->findOne(array("conditions" => "email_addr = '$from_address'"));
					if ($ma instanceof MailAccount) {
						
						$mu = new MailUtilities();
						$from = array($ma->getEmailAddress() => ($sender_name != "" ? $sender_name : $ma->getFromName()));
						if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "using mail account in from address\n", FILE_APPEND);
						
						$mailer = self::getMailer(array(
								'smtp_server' => $ma->getSmtpServer(),
								'smtp_port' => $ma->getSmtpPort(),
								'smtp_secure_connection' => $ma->getOutgoingTrasnportType(),
								'smtp_username' => $ma->smtpUsername(),
								'smtp_password' => $mu->ENCRYPT_DECRYPT($ma->smtpPassword()),
						));
					} else {
						$mailer = $default_mailer;
					}
				}
				
				if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", print_r(array('from'=>$from,'subject'=>$subject),1)."\n", FILE_APPEND);

				$message = Swift_Message::newInstance($subject)
				  ->setFrom($from)
				  ->setBody($body)
				  ->setContentType('text/html')
				;
				
				if ($email->columnExists('attachments')) {
					$attachments = json_decode($email->getColumnValue('attachments'));
					foreach ($attachments as $a) {
						// if file does not exists or its size is greater than 20 MB then don't process the atachments
						if (!file_exists($a->path) || filesize($a->path) / (1024 * 1024) > 20) continue;
						
						$attach = Swift_Attachment::fromPath($a->path, $a->type);
						$attach->setDisposition($a->disposition);
						if ($a->cid) $attach->setId($a->cid);
						if ($a->name) $attach->setFilename($a->name);
						$message->attach($attach);
					}
				}
				
				$to = prepare_email_addresses(implode(",", explode(";", $email->getTo())));
				foreach ($to as $address) {
					$message->addTo(array_var($address, 0), array_var($address, 1));
				}
				$cc = prepare_email_addresses(implode(",", explode(";", $email->getCc())));
				foreach ($cc as $address) {
					$message->addCc(array_var($address, 0), array_var($address, 1));
				}
				$bcc = prepare_email_addresses(implode(",", explode(";", $email->getBcc())));
				foreach ($bcc as $address) {
					$message->addBcc(array_var($address, 0), array_var($address, 1));
				}
				$result = $mailer->send($message);

				// set the real from in the email object
				foreach ($from as $f_add => $f_name) {
					$fr = $f_name == '' ? $f_add : "$f_name <$f_add>";
					$email->setFrom($fr);
					if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "Real from: $fr\n", FILE_APPEND);
				}
				
				if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", print_r(array('to'=>$to, 'cc'=>$cc, 'bcc'=>$bcc),1)."\n", FILE_APPEND);
				
				if ($result) {
					DB::beginWork();
					// save notification history after cron sent the email
					self::saveNotificationHistory(array(
						'to' => json_encode($to),
						'cc' => json_encode($cc),
						'bcc' => json_encode($bcc),
						'from' => json_encode($from),
						'subject' => $subject,
						'body' => $body,
						'attachments' => json_encode($attachments),
						'timestamp' => DateTimeValueLib::now()->toMySQL(),
						'object_id' => DB::escape($email->getObjectId()),
					));

					// delte from queued_emails
					$email->delete();
					DB::commit();
				}
				$count++;
				
				if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "End sending queued email ".$email->getId()."\n", FILE_APPEND);
				
			} catch (Exception $e) {
				if (defined('DEBUG_NOTIFICATIONS') && DEBUG_NOTIFICATIONS) file_put_contents(CACHE_DIR."/debug_notifications", "Error sending queued_email ".$email->getId()."\n".$e->getMessage()."\n", FILE_APPEND);
				if ($result) {
					DB::rollback();
				}
				
				// save log in server
				if (defined('EMAIL_ERRORS_LOGDIR') && file_exists(EMAIL_ERRORS_LOGDIR) && is_dir(EMAIL_ERRORS_LOGDIR)) {
					$err_msg = ROOT_URL."\nError sending notification (queued_email_id=".$email->getId().") using account ".print_r($from, 1)."\n\nError detail:\n".$e->getMessage()."\n".$e->getTraceAsString();
					file_put_contents(EMAIL_ERRORS_LOGDIR . basename(ROOT), $err_msg, FILE_APPEND);
				}
				
				Logger::log("There has been a problem when sending the Queued emails.\nError Message: ". $e->getMessage(). "\nTrace: ". $e->getTraceAsString());
				$msg = $e->getMessage();
				if (strpos($msg, 'Failed to authenticate') !== false) {
					$from_k = array_keys($from);
					$usu = Contacts::getByEmail($from_k[0], null, true);
					
					$rem = ObjectReminders::instance()->findOne(array('conditions' => "context='eauthfail ". $from_k[0]."'"));
					if (!$rem instanceof ObjectReminder && $usu instanceof Contact) {
						$reminder = new ObjectReminder();
						$reminder->setMinutesBefore(0);
						$reminder->setType("reminder_popup");
						$reminder->setContext("eauthfail ". $from_k[0]);
						$reminder->setObject($usu);
						$reminder->setUserId($usu->getId());
						$reminder->setDate(DateTimeValueLib::now());
						$reminder->save();
					}
				}
			}
		}
		return $count;
	}

	/**
	 * This function will return SMTP connection. It will try to load options from
	 * config and if it fails it will use settings from php.ini
	 *
	 * @param void
	 * @return Swift
	 */
	static function getMailer($parameters = null) {
		
		Hook::fire("override_notifier_mailer_parameters", null, $parameters);
		
		if (is_array($parameters)) {
			$mail_transport_config = self::MAIL_TRANSPORT_SMTP;
			$smtp_authenticate = true;
		
			$smtp_server = array_var($parameters, 'smtp_server');
			$smtp_port = array_var($parameters, 'smtp_port', 25);
			$smtp_secure_connection = array_var($parameters, 'smtp_secure_connection', self::SMTP_SECURE_CONNECTION_NO);
			$smtp_username = array_var($parameters, 'smtp_username');
			$smtp_password = array_var($parameters, 'smtp_password');
		
		} else {
			$mail_transport_config = config_option('mail_transport', self::MAIL_TRANSPORT_MAIL);
		}
		
		// Emulate mail() - use NativeMail
		if($mail_transport_config == self::MAIL_TRANSPORT_MAIL) {

			return Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
			// Use SMTP server
		} elseif($mail_transport_config == self::MAIL_TRANSPORT_SMTP) {
			
			if (!is_array($parameters)) {
				// Load SMTP config
				$smtp_server = config_option('smtp_server');
				$smtp_port = config_option('smtp_port', 25);
				$smtp_secure_connection = config_option('smtp_secure_connection', self::SMTP_SECURE_CONNECTION_NO);
				$smtp_authenticate = config_option('smtp_authenticate', false);
				if($smtp_authenticate) {
					$smtp_username = config_option('smtp_username');
					$smtp_password = config_option('smtp_password');
				} // if
			}

			switch($smtp_secure_connection) {
				case self::SMTP_SECURE_CONNECTION_SSL:
					$transport = 'ssl';
					break;
				case self::SMTP_SECURE_CONNECTION_TLS:
					$transport = 'tls';
					break;
				default:
					$transport = null;
			} // switch
			
			$mail_transport = Swift_SmtpTransport::newInstance($smtp_server, $smtp_port, $transport);		
			$smtp_authenticate = isset($smtp_username) && $smtp_username != null;
			if($smtp_authenticate) {
				$mail_transport->setUsername($smtp_username);
				$mail_transport->setPassword($smtp_password);
			}

			$local_domain = parse_url(ROOT_URL);
			if(is_array($local_domain) && array_key_exists('host', $local_domain) && $local_domain['host'] !== ''){
				$mail_transport->setLocalDomain($local_domain['host']);
			}

			return Swift_Mailer::newInstance($mail_transport);
			
			// Somethings wrong here...
		} else {
			return null;
		} // if
	} // getMailer

	function sendReminders() {
		include_once "application/cron_functions.php";
		send_reminders();
	}
	
	
	/**
	 * When a task is completed, sends a notification to the assigned users of all the 
	 * dependant tasks of the completed task to inform that the previous task has been completed.
	 * @param $object The task that has been completed
	 */
	static function notifyDependantTaskAssignedUsersOfTaskCompletion($object) { /* @var $object ProjectTask */
		
		if (in_array($object->getObjectTypeId(), config_option("disable_notifications_for_object_type"))) {
			return;
		}
		
		$emails = array();
		// get dependant tasks
		$dependant_tasks = ProjectTaskDependencies::getDependantTasks($object->getId());
		
		// set sender user as the one who completed the task
		$sender = $object->getCompletedBy();
		if ($sender instanceof Contact) {
			$sendername = $sender->getObjectName();
			$senderemail = $sender->getEmailAddress();
		} else {
			return;
		}
			
		foreach ($dependant_tasks as $dep_task) {
			/* @var $dep_task ProjectTask */
			$assigned_user = $dep_task->getAssignedTo();
			
			// check that dependant task is assigned to a valid user
			if ($assigned_user instanceof Contact && $assigned_user->isUser()) {
				
				// check that all previous tasks are completed
				$all_previous_completed = true;
				$previous_tasks = ProjectTaskDependencies::getPreviousTasks($dep_task->getId());
				foreach ($previous_tasks as $pt) {
					if ($pt->getId() == $object->getId()) {
						continue;
					}
					if ($pt->getCompletedById() == 0) {
						$all_previous_completed = false;
						break;
					}
				}
				// send the notification only if all previous tasks of this task are completed
				if ($all_previous_completed) {
					
					// set notificated user localization
					Localization::instance()->loadSettings($assigned_user->getLocale(), ROOT . '/language');
					
					// format notification data
					$assigned_by_name = $dep_task->getAssignedBy() instanceof Contact ? $dep_task->getAssignedBy()->getObjectName() : "";
					$assigned_to_name = $dep_task->getAssignedToName();
					
					tpl_assign('object', $dep_task);
					tpl_assign('title', lang('task x can be started', $dep_task->getObjectName()));
					tpl_assign('by', $assigned_by_name);
					tpl_assign('asigned', $assigned_to_name);
					tpl_assign('description', $dep_task->getDescription());
					
					$contexts = self::buildContextObjectForNotification($dep_task);
					tpl_assign('contexts', $contexts);
					
					$priority_data = self::getTaskPriorityData($dep_task);
					tpl_assign('priority', $priority_data);
					
					$tz_offset = Timezones::getTimezoneOffsetToApply($dep_task, $assigned_user);
					$tz = $tz_offset/3600;
					
					$start_date = self::getTaskDateFormatted($dep_task, 'start_date', $tz);
					tpl_assign('start_date', $start_date);
					
					$due_date = self::getTaskDateFormatted($dep_task, 'due_date', $tz);
					tpl_assign('due_date', $due_date);
					
					$attachments = array();
					$logo_info = self::getLogoAttachmentData($assigned_user->getEmailAddress());
					if (is_array($logo_info) && count($logo_info) > 0) {
						$attachments['logo'] = $logo_info;
					}
					tpl_assign('attachments', $attachments);
					
					$quit = false;
					Hook::fire('filter_object_notification_single_user', array('user' => $assigned_user), $quit);
					if ($quit) continue;
					
					// send notification
					$to_addresses = array();
					$to_addresses[$assigned_user->getId()] = self::prepareEmailAddress($assigned_user->getEmailAddress(), $assigned_user->getObjectName());
					
					$subject = lang('all previous tasks have been completed', $dep_task->getObjectName());
					
					$recipients_field = config_option('notification_recipients_field', 'to');
					$emails[] = array(
					    "object_id" => $dep_task->getId(),
						"$recipients_field" => $to_addresses,
						"from" => self::prepareEmailAddress($senderemail, $sendername),
						"subject" => $subject,
						"body" => tpl_fetch(get_template_path('previous_task_completed', 'notifier')),
						"attachments" => $attachments,
					);
				}
			}
		}
		if (count($emails) > 0) {
			self::queueEmails($emails);
			
			$locale = logged_user() instanceof Contact ? logged_user()->getLocale() : DEFAULT_LOCALIZATION;
			Localization::instance()->loadSettings($locale, ROOT . '/language');
		}
	}
	
	private static function getTaskPriorityData($object) {
		if ($object instanceof ContentDataObject && $object->columnExists('priority') && trim($object->getColumnValue('priority'))) {
			if ($object->getColumnValue('priority') >= ProjectTasks::PRIORITY_URGENT) {
				$priorityColor = "#FF0000";
				$priority = lang('urgent priority');
			}else if ($object->getColumnValue('priority') >= ProjectTasks::PRIORITY_HIGH) {
				$priorityColor = "#FF9088";
				$priority = lang('high priority');
			} else if ($object->getColumnValue('priority') <= ProjectTasks::PRIORITY_LOW) {
				$priorityColor = "white";
				$priority = lang('low priority');
			}else{
				$priorityColor = "#DAE3F0";
				$priority = lang('normal priority');
			}
			return array($priority, $priorityColor);
		}
		return "";
	}
	
	private static function getTaskDateFormatted($object, $date_column, $timezone) {
		$date = "";
		if ($object->columnExists($date_column) && $object->getColumnValue($date_column)) {
			$date_val = $object->getColumnValue($date_column);
			if ($date_val instanceof DateTimeValue) {
				$date = Localization::instance()->formatDescriptiveDate($date_val, $timezone);
				$time = Localization::instance()->formatTime($date_val, $timezone);
				if($time > 0) {
					$date .= " " . $time;
				}
			}
		}
		return $date;
	}
	
	private static function getLogoAttachmentData($toemail) {
		$logo_info = array();
		try {
			$content = FileRepository::getBackend()->getFileContent(owner_company()->getPictureFile());
			if ($content != "" && config_option('show company logo in notifications')) {
				$file_path = ROOT . "/tmp/logo_empresa.png";
				$handle = fopen($file_path, 'wb');
				if ($handle) {
					fwrite($handle, $content);
					fclose($handle);
					if (!$toemail) $toemail = "recipient@";
					$logo_info = array(
						'cid' => gen_id() . substr($toemail, strpos($toemail, '@')),
						'path' => $file_path,
						'type' => 'image/png',
						'disposition' => 'inline',
						'name' => 'logo_empresa.png',
					);
				}
			}
		} catch (FileNotInRepositoryError $e) {
			Logger::log("Could not find owner company picture file: ".$e->getMessage());
		}
		return $logo_info;
	}
	
	private static function buildContextObjectForNotification($object) {
		$contexts = array();
		$members = $object->getMembers();
		if(count($members) > 0){
			foreach ($members as $member){
				$dim = $member->getDimension();
				if($dim->getIsManageable()){
					/* @var $member Member */
					$parent_members = $member->getAllParentMembersInHierarchy();
					$parents_str = '';
					foreach ($parent_members as $pm) {
						/* @var $pm Member */
						if (!$pm instanceof Member) continue;
						$parents_str .= '<span style="'.get_workspace_css_properties($pm->getMemberColor()).'">'. $pm->getName() .'</span>';
					}
					if ($dim->getCode() == "customer_project" || $dim->getCode() == "customers"){
						$obj_type = ObjectTypes::findById($member->getObjectTypeId());
						if ($obj_type instanceof ObjectType) {
							$contexts[$dim->getCode()][$obj_type->getName()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
						}
					}else{
						$contexts[$dim->getCode()][]= $parents_str . '<span style="'.get_workspace_css_properties($member->getMemberColor()).'">'. $member->getName() .'</span>';
					}
				}
			}
		}
		
		return $contexts;
	}
	
	/**
	 * Delete sent notifications logs which are older than $days days
	 * @param $days: number of days to mantain logs, default value=60 
	 */
	private static function deleteOldNotificationHistory($days = 60) {
		$date = DateTimeValueLib::now();
		$date->add("d", -1 * $days);
		$sql = "DELETE FROM ".TABLE_PREFIX."sent_notifications WHERE sent_date < ".DB::escape($date->toMySQL());
		try {
			DB::execute($sql);
		} catch (Exception $e) {
			Logger::log("ERROR: Could not delete old notification history.\nMessage:".$e->getMessage()."\nSQL:\n$sql");
		}
	}
	
	/**
	 * Save notifications history, saves the same information stored in queued_emails after sending the email 
	 * and before deleting the record from this table 
	 * @param $parameters: array with the data to save, if key 'email_object' exists and its value is a QueuedEmail 
	 * 					   object then the values are taken from this object, else use the 'from', 'to', etc. parameters. 
	 */
	private static function saveNotificationHistory($parameters) {
		// first clean old history
		self::deleteOldNotificationHistory();
		
		// build column values to insert
		$email = array_var($parameters, 'email_object');
		if ($email instanceof QueuedEmail) {
			// if notification was sent by cron
			$values = array(
				$email->getId(),
				DB::escape(DateTimeValueLib::now()->toMySQL()), 
				DB::escape($email->getTo()), 
				DB::escape($email->getCc()), 
				DB::escape($email->getBcc()), 
				DB::escape($email->getFrom()), 
				DB::escape($email->getSubject()), 
				DB::escape($email->getBody()), 
				DB::escape($email->getAttachments()), 
				DB::escape($email->getTimestamp()),
				$email->getObjectId(),
			);
		} else {
			// if notification is sent inmediately after an action (not by cron)
			$values = array(
				0,
				DB::escape(DateTimeValueLib::now()->toMySQL()),
				DB::escape(array_var($parameters, 'to', '')), 
				DB::escape(array_var($parameters, 'cc', '')), 
				DB::escape(array_var($parameters, 'bcc', '')), 
				DB::escape(array_var($parameters, 'from', '')), 
				DB::escape(array_var($parameters, 'subject', '')), 
				DB::escape(array_var($parameters, 'body', '')), 
				DB::escape(array_var($parameters, 'attachments', '')), 
				DB::escape(array_var($parameters, 'timestamp', '')),
				array_var($parameters, 'object_id', '0'),
			);
		}
		// columns to set
		$columns_sql = "`queued_email_id`, `sent_date`, `to`, `cc`, `bcc`, `from`, `subject`, `body`, `attachments`, `timestamp`, `object_id`";
		
		// sql query
		$sql = "INSERT INTO ".TABLE_PREFIX."sent_notifications ($columns_sql)
				VALUES (". implode(",", $values) .")
				ON DUPLICATE KEY UPDATE id=id";
		
		// execute the query
		try {
			DB::execute($sql);
		} catch (Exception $e) {
			Logger::log("ERROR: Could not save notification history.\nMessage:".$e->getMessage()."\nSQL:\n$sql");
		}
	}
	
} // Notifier

?>
