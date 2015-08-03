<?php
if (isset($milestone) && $milestone instanceof ProjectMilestone) {
	if (!$milestone->isTrashed()){
		if (!$milestone->isCompleted() && $milestone->canEdit(logged_user())) {
			add_page_action(lang('complete milestone'), $milestone->getCompleteUrl(rawurlencode(get_url('milestone', 'view', array('id'=>$milestone->getId())))) , 'ico-complete', null, null, true);
		} // if
		if ($milestone->isCompleted() && $milestone->canEdit(logged_user())) {
			add_page_action(lang('open milestone'), $milestone->getOpenUrl(rawurlencode(get_url('milestone', 'view', array('id'=>$milestone->getId())))) , 'ico-reopen', null, null, true);
		}
		if (!$milestone->isCompleted()) {
			$m_members = $milestone->getMembers();
			if(array_var($m_members, 0) instanceof Member && $milestone->canAddToMember(logged_user(), array_var($m_members, 0), active_context())) {
				add_page_action(lang('add task list'), $milestone->getAddTaskUrl(), 'ico-task');
			}
		} // if
		if ($milestone->canEdit(logged_user())) {
			add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'milestone', a:'edit', params: {id:".$milestone->getId()."}});", 'ico-edit', null, null, true);
			if (!$milestone->isArchived()) {
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $milestone->getArchiveUrl() ."');", 'ico-archive-obj');
			} else {
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $milestone->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
			}
		} // if
	}
	
	if ($milestone->canDelete(logged_user())) {
		if ($milestone instanceof TemplateMilestone) {
			add_page_action(lang('delete'), "javascript:if(confirm(lang('confirm delete milestone'))) og.openLink('" . $milestone->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else if ($milestone->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $milestone->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $milestone->getDeleteUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $milestone->getTrashUrl() ."');", 'ico-trash', null, null, true);
		}
	} // if
	
	if (!$milestone->isTrashed() && !logged_user()->isGuest()){
		if ($milestone instanceof TemplateMilestone) {
			/*FIXME Fix Copy milestones please!
			add_page_action(lang('new milestone from template'), get_url("milestone", "copy_milestone", array("id" => $milestone->getId())), 'ico-copy');
			*/
		} else {
			//FIXME Fix Copy milestones please! add_page_action(lang('copy milestone'), get_url("milestone", "copy_milestone", array("id" => $milestone->getId())), 'ico-copy');
			if (can_manage_templates(logged_user())) {
				add_page_action(lang('add to a template'), get_url("template", "add_to", array("manager" => 'ProjectMilestones', "id" => $milestone->getId())), 'ico-template');
			}
		}
	}

?>

<div style="padding:7px">
<div class="milestone">
<?php 
	$content = '';
	if ($milestone->getDueDate()->getYear() > DateTimeValueLib::now()->getYear()) { 
		$content = '<div class="dueDate"><b>'.lang('due date').':</b> ' . format_date($milestone->getDueDate(), null, 0) . '</div>';
	} else { 
		$content = '<div class="dueDate"><b>' . lang('due date') . ':</b> ' . format_descriptive_date($milestone->getDueDate(), 0) . '</div>';
	} // if 
	if ($milestone->getDescription()){
		$content .= '<fieldset><legend>'.lang('description').'</legend>'. escape_html_whitespace(convert_to_links(clean($milestone->getDescription()))) . '</fieldset>';
	}
	$openSubtasks = $milestone->getOpenSubTasks();
	if (is_array($openSubtasks)) { 
//		$content .= '<p>' . lang('task lists') . ':</p><ul>';
		
		
		
//show open sub task list
		$content .= '<br/><table style="border:1px solid #717FA1;width:100%; padding-left:10px;"><tr><th style="padding-left:10px;padding-top:4px;padding-bottom:4px;background-color:#E8EDF7;font-size:120%;font-weight:bolder;color:#717FA1;width:100%;">' . lang("view open tasks") . '</th></tr><tr><td style="padding-left:10px;">
			  <div class="openTasks">
			  <table class="blank">';
 		foreach($openSubtasks as $task) { 
      		$content .= '<tr>';
      
			// Checkboxes
			if($task->canChangeStatus(logged_user())) { 
			    $content .= '<td class="taskCheckbox">' . checkbox_link($task->getCompleteUrl(rawurlencode(get_url('milestone', 'view', array('id' => $milestone->getId())))), false, lang('mark task as completed')) . '</td>';
			} else { 
				$content .= '<td class="taskCheckbox"><img src="' . icon_url('not-checked.jpg') . '" alt="' . lang('open task') . '" /></td>';
			} // if
			
			// Task text and options -->
			$content .= '<td class="taskText">';
			if($task->getAssignedTo()) { 
				$content .= '  <span class="assignedTo">'. clean($task->getAssignedTo()->getObjectName()) .':</span> ';
			} // if 
			
			$content .= ' <a class="internalLink" href="' . $task->getObjectUrl() . '">' ;
			$content .=  ($task->getObjectName() && $task->getObjectName() != '' ) ? clean($task->getObjectName()) : clean($task->getText());
			$content .='</a> ';
			
				 if($task->canEdit(logged_user())) { 
			 	$content .= '<a class="internalLink blank" href="'. $task->getEditListUrl() .'" title="' . lang('edit task') . '"><img src="' .
			 	icon_url('edit.gif') .'" alt="" /></a>';
			} // if 
			if($task->canDelete(logged_user())) { 
				$content .= '<a class="internalLink blank" href="' . $task->getDeleteUrl() .'" onclick="return confirm(\'' . 
			  		escape_single_quotes(lang('confirm delete task')) . '\')" title="' . lang('delete task') . '">
			  		<img src="' . icon_url('cancel_gray.gif') .'" alt="" /></a>';
			} // if 
		    $content .= ' </td>     </tr>';
		} // foreach 
	   $content .= '</table>';
		
	$content .= '</div></td></tr></table><br/>';
	}	
	else { 
		$content .=  '<br/>' . lang('no open task in milestone')  .'<br/><br/>';
	} // if 
	
	
$on_list_page = false;
//show completed tasks for the milestone
	$completed_subtasks	= $milestone->getCompletedSubTasks();
	if(is_array($milestone->getCompletedSubTasks($completed_subtasks))) { 
		$content .= '  <table style="border:1px solid #717FA1;width:100%; padding-left:10px;"><tr><th style="padding-left:10px;padding-top:4px;padding-bottom:4px;background-color:#E8EDF7;font-size:120%;font-weight:bolder;color:#717FA1;width:100%;">' . lang("completed tasks") . '</th></tr><tr><td style="padding-left:10px;">
			  <div class="completedTasks">
			  <table class="blank">';
 		$counter = 0; 
 		foreach($completed_subtasks as $task) { 
			 $counter++; 
			 if($on_list_page || ($counter <= 5)) {
			    $content .= '<tr>';
			 	if($task->canChangeStatus(logged_user())) { 
					$content .= '<td class="taskCheckbox">' . checkbox_link($task->getOpenUrl(rawurlencode(get_url('milestone', 'view', array('id' => $milestone->getId())))), true, lang('mark task as open')) . '</td>';
				} else { 
				    $content .= '<td class="taskCheckbox"><img src="' .  icon_url('checked.jpg') .'" alt="' . lang('completed task') .'" /></td>';
				} // if 
			    $content .= '    <td class="taskText">
			        	<a class="internalLink" href="' . $task->getObjectUrl() .'">'.clean($task->getObjectName()) .'</a> ';
	           if($task->canEdit(logged_user())) { 
	           	$content .= '<a class="internalLink" href="' . $task->getEditListUrl() .'" class="blank" title="'. lang('edit task') .
	           		'"><img src="'. icon_url('edit.gif') .'" alt="" /></a> ';
				} // if 
				if($task->canDelete(logged_user())) { 
					$content .= '<a href="'. $task->getDeleteUrl() .'" class="blank internalLink" onclick="return confirm(\'' . 
						escape_single_quotes(lang('confirm delete task')) . '\')" title="' . lang('delete task') . '"><img src="' . icon_url('cancel_gray.gif') .
						'" alt="" /></a> ';
				} // if <br />
	          $content .= '<span class="taskCompletedOnBy">(' .lang('completed on by', format_date($task->getCompletedOn()), $task->getCompletedBy() instanceof Contact ? $task->getCompletedBy()->getCardUserUrl() : '#', $task->getCompletedBy() instanceof Contact ? clean($task->getCompletedBy()->getObjectName()) : lang('n/a')) . ')</span>
				        </td> <td></td>  </tr>';
			 } // if 
		 } // foreach 
		 if(!$on_list_page && $counter > 5) { 
		      $content .= '<tr>
		      
		        <td colspan="2"><a class="internalLink" href="'. get_url("task","new_list_tasks",array('status' => '1','filter' => 'milestone','fval' => $milestone->getId())) .'"> ' . lang('view all completed tasks', $counter) .'</a></td>
		      </tr>';
		 } // if 	   
		$content .= ' </table> </div> </td></tr></table>';
	} // if 	   
	else { 
		$content .=   lang('no closed task in milestone')  .'<br/>';
	} // if 
	   
	tpl_assign("content", $content);
	tpl_assign("object", $milestone);
	tpl_assign('iconclass', $milestone->isTrashed()? 'ico-large-milestone-trashed' : ($milestone->isArchived() ? 'ico-large-milestone-archived' : 'ico-large-milestone'));
	
	$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>

<?php } //if isset ?>
