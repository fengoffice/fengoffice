<?php

	
//	Copyright (c) Reece Pegues
//	sitetheory.com
//
//    Reece PHP Calendar is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or 
//	any later version if you wish.
//
//    You should have received a copy of the GNU General Public License
//    along with this file; if not, write to the Free Software
//    Foundation Inc, 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	

// @var $event ProjectEvent

if (isset($event) && $event instanceof ProjectEvent) {
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : logged_user()->getId();
	
	if (!$event->isTrashed()){
		if ($event->canEdit(logged_user())) {
			add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'event', a:'edit', params: {id:".$event->getId().", view:'$view', user_id:'$user_id'}});", 'ico-edit', null, null, true);
			
			if (!$event->isArchived())
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $event->getArchiveUrl() ."');", 'ico-archive-obj');
			else
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $event->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
		}
	}
		
	if ($event->canDelete(logged_user())) {
		if ($event->isTrashed()) {
	    	add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $event->getUntrashUrl() ."');", 'ico-restore',null, null, true);
	    	add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $event->getDeletePermanentlyUrl() ."');", 'ico-delete',null, null, true);
	    } else {
	    	add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $event->getTrashUrl() ."');", 'ico-trash', null, null, true);
	    }
	} // if

	$modified="";
	$error = "";
	// Do this if we are MODIFYING a form.
	$id = $_GET['id'];
	
    if(!is_numeric($id)) $error = lang('CAL_NO_EVENT_SELECTED');
	// get user who submitted the event, subject, event description, etc.
    $username = clean($event->getCreatedByDisplayName());
    $subject = clean($event->getObjectName());
	$alias = clean($event->getCreatedByDisplayName());
    $desc = escape_html_whitespace(convert_to_links(clean($event->getDescription())));
    $start_time = $event->getStart();
	$mod_username = clean($event->getUpdatedByDisplayName());
	$mod_stamp = $event->getUpdatedOn();
	
	// check username to see if it's anonymous or not
	if($username=="") $username = lang('CAL_ANONYMOUS');
	
	if($mod_username=="") $mod_username = lang('CAL_ANONYMOUS');
	
	// if the event is private and the user is anonymous, return that the event does not exist.
	if($error=="") $error = lang('CAL_DOESNT_EXIST');
	
    $durtime = $event->getDuration()->getTimestamp() - $start_time->getTimestamp();
    $durmin = ($durtime / 60) % 60;     //seconds per minute
    $durhr  = ($durtime / 3600) % 24;   //seconds per hour
    $durday = floor($durtime / 86400);  //seconds per day

	if(user_config_option('time_format_use_24')) $timeformat = 'G:i';
	else $timeformat = 'g:i A';
	$time = format_time($start_time, $timeformat);
	
	// organize duration of event
	$duration = '';
	if ($durday > 0) $duration .= $durday . ' '.lang('days').($durhr!="1" ? ', ' : ' ');
	$duration .= $durhr . ' ';
	if($durhr!="1") $duration .= lang('CAL_HOURS');
	else $duration .= lang('CAL_HOUR');
	if($durmin!="0") $duration .= ", ". $durmin. " ". lang('CAL_MINUTES_SHORT');
	
	// organize other time options for the event
    $typeofevent = $event->getTypeId();
	if($typeofevent=="2") $duration = lang('CAL_FULL_DAY');
	elseif($typeofevent=="3"){
		$time = lang('CAL_NOT_SPECIFIED');
		$duration = lang('CAL_NOT_SPECIFIED');
	}
	elseif($typeofevent=="4") $duration = lang('CAL_NOT_SPECIFIED');
	
	$permission = ProjectEvents::findById($id)->canEdit(logged_user());
	
?>
<div style="padding:7px;">
<div class="event" style="height:100%;">

<?php
	
	$title = lang($event->getObjectTypeName()) . ": " . format_descriptive_date($event->getStart()) . ' - ' . clean($event->getObjectName());
	$description = $event->getTypeId() == 2 ? lang('CAL_FULL_DAY') : lang('CAL_TIME').": $time" ;
  	tpl_assign('description', $description);

	$att_form = '';
  	if (!$event->isNew() && !$event->isTrashed()) {
		$event_inv = EventInvitations::findById(array('event_id' => $event->getId(), 'contact_id' => logged_user()->getId()));
		if ($event_inv != null) {
			$event->addInvitation($event_inv);
			$event_inv_state = $event_inv->getInvitationState();
			if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_update_other_users_invitations')) {
				$options = array(
					option_tag(lang('yes'), 1, ($event_inv_state == 1)?array('selected' => 'selected'):null),
					option_tag(lang('no'), 2, ($event_inv_state == 2)?array('selected' => 'selected'):null),
					option_tag(lang('maybe'), 3, ($event_inv_state == 3)?array('selected' => 'selected'):null)
				);
				if ($event_inv_state == 0) {
					$options[] = option_tag(lang('decide later'), 0, ($event_inv_state == 0) ? array('selected' => 'selected'):null);
				}
			
				$att_form = '<form style="height:100%;background-color:white" class="internalForm" action="' . get_url('event', 'change_invitation_state') . '" method="post">';
				$att_form .= '<table><tr><td style="padding-right:6px;"><b>' . lang('attendance') . '<b></td><td>';
				$att_form .= select_box('event_attendance', $options, array('id' => 'viewEventFormComboAttendance')) . '</td><td>';
				$att_form .= input_field('event_id', $event->getId(), array('type' => 'hidden'));
				$att_form .= input_field('user_id', logged_user()->getId(), array('type' => 'hidden'));
				$att_form .= submit_button(lang('Save'), null, array('style'=>'margin-top:0px;margin-left:10px')) . '</td></tr></table></form>';
			}
		} //if
	} // if

	$otherInvitationsTable = '';
	if (!$event->isNew()) {
		$otherInvitations = EventInvitations::findAll(array ('conditions' => 'event_id = ' . $event->getId()));
		if (isset($otherInvitations) && is_array($otherInvitations)) {
			$otherInvitationsTable .= '<div class="coInputMainBlock adminMainBlock" style="width:70%;">';
			$otherInvitationsTable .= '<table style="width:100%;"><col width="50%" /><col width="50%" />';
			$otherInvitationsTable .= '<tr><th><b>' . lang('name') . '</b></th><th><b>' . lang('participate') . '</b></th></tr>';
			$isAlt = false;
			$cant = 0;
			foreach ($otherInvitations as $inv) {
				$inv_user = Contacts::findById($inv->getContactId());
				if ($inv_user instanceof Contact) {
					if (can_access($inv_user, $event->getMembers(),ProjectEvents::instance()->getObjectTypeId(), ACCESS_LEVEL_READ)) {

						if (!SystemPermissions::userHasSystemPermission(logged_user(), 'can_update_other_users_invitations')) {
							// only show status
							$state_desc = lang('pending response');
							if ($inv->getInvitationState() == 1) $state_desc = lang('yes');
							else if ($inv->getInvitationState() == 2) $state_desc = lang('no');
							else if ($inv->getInvitationState() == 3) $state_desc = lang('maybe');
							$otherInvitationsTable .= '<tr'.($isAlt ? ' class="altRow"' : '').'><td>' . clean($inv_user->getObjectName()) . '</td><td>' . $state_desc . '</td></tr>';
							
						} else {
							// draw status selector and let modify
							$options = array(option_tag(lang('decide later'), 0, $inv->getInvitationState() == 0 ? array('selected' => "selected") : array()), 
								option_tag(lang('yes'), 1, $inv->getInvitationState() == 1 ? array('selected' => "selected") : array()),
								option_tag(lang('no'), 2, $inv->getInvitationState() == 2 ? array('selected' => "selected") : array()),
								option_tag(lang('maybe'), 3, $inv->getInvitationState() == 3 ? array('selected' => "selected") : array()),
							);

							$genid = gen_id();
							$state_sel_html = '<form method="post" action="'.get_url('event', 'change_invitation_state', array('silent' => 1)).'">';
							$state_sel_html .= '<input type="hidden" name="event_id" value="'.$event->getId().'" /><input type="hidden" name="user_id" value="'.$inv_user->getId().'" />';
							$state_sel_html .= select_box('event_attendance', $options, array('onchange' => '$(this).parent().submit();')) . '</form>';
							
							$otherInvitationsTable .= '<tr'.($isAlt ? ' class="altRow"' : '').'><td>' . clean($inv_user->getObjectName()) . '</td><td>' . $state_sel_html . '</td></tr>';
						}
						
						$isAlt = !$isAlt;
						$cant++;
					}
				}
			}
			if ($cant > 0) $otherInvitationsTable .= '</table></div>';
			else $otherInvitationsTable = lang('no invitations to this event');
		} else {
			$otherInvitationsTable = lang('no invitations to this event');
		}
	}
	
	$variables = array();
	$variables['username'] = $username;
	if (isset($modtimeformat))
		$variables['modtimeformat'] = $modtimeformat;
	$variables['mod_username'] = $mod_username;
	$variables['time'] = $time;
	if (!$event->isNew()) {
		$variables['attendance'] = $att_form;
		$variables['other_invitations'] = $otherInvitationsTable;
	}
	$variables['duration'] = $duration;
	$variables['desc'] = $desc;
	
	
	
	tpl_assign("variables", $variables);
	tpl_assign("content_template", array('view_event', 'event'));
	tpl_assign('object', $event);
	tpl_assign('title', $title);
	tpl_assign('iconclass', $event->isTrashed()? 'ico-large-event-trashed' : ($event->isArchived() ? 'ico-large-event-archived' : 'ico-large-event'));

	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>
<?php }//if isset ?>
