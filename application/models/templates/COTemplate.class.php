<?php

/**
 * COTemplate class
 * Generated on Sat, 04 Mar 2006 12:50:11 +0100 by DataObject generation tool
 *
 * @author Ignacio de Soto <ignacio.desoto@gmail.com>
 */
class COTemplate extends BaseCOTemplate {

	protected $is_commentable = true;
	
	protected $is_linkable_object= false;
	
	function getObjects() {
		if ($this->isNew()) {
			return array();
		}
		return TemplateObjects::getObjectsByTemplate($this->getId());
	}
	
	function removeObjects() {
		if (!$this->isNew()) {
			return TemplateObjects::deleteObjectsByTemplate($this->getId());
		}
	}
	
	function hasObject($object) {
		return TemplateObjects::templateHasObject($this, $object);
	}
	

	
	
	/**
	 * 
	 * 
	 * @param ContentDataObject $object
	 * @param $additional_attributes array 
	 * @param $go_deep bool copy all subtasks or if is a milestone copy all tasks 
	 * @return int Template Object id
	 */
	function addObject($object, $additional_attributes = array(), $go_deep = true) {
		//if ($this->hasObject($object)) return;
		
		//if object is a ProjectTask
		if ($object instanceof ProjectTask) {
			if($go_deep){
				$object = TemplateTask::copyFromProjectTaskIncludeSubTasks($object,$this->getId());
			}else{
				$object = TemplateTask::copyFromProjectTask($object,$this->getId());
			}
		
		//if object is a ProjectMilestone
		}else if ($object instanceof ProjectMilestone) {
			$object = TemplateMilestone::copyFromProjectMilestone($object,$this->getId(), $go_deep);
			
		//if object is a TemplateTask
		}else if ($object instanceof TemplateTask) { 
			$object->setColumnValue('template_id', $this->getId());
			$object->setColumnValue('session_id', null);
			if (isset($additional_attributes['milestone'])) {
				$object->setMilestoneId($additional_attributes['milestone']);
			}
			$object->save();
		
		//if object is a TemplateMilestone
		}else if ($object instanceof TemplateMilestone) {
			$object->setColumnValue('template_id', $this->getId());
			$object->setColumnValue('session_id', null);
				
			$object->save();
		}
		
			
		// the object is already a template or can't be one, use it as it is
		$template = $object;
		
		//create a TemplateObject
		$to = new TemplateObject();
		$to->setObject($template);
		$to->setTemplate($this);
		$to->save();
		
		return $template->getObjectId();
	}
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	
	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		return 1 ;
		//FIXME: return can_manage_templates($user) && can_add($user, $context, COTemplates::instance()->getObjectTypeId());
	}
	
	
	/**
	 * Returns true if $user can view this template
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		return 1 ;
		// FIXME return can_manage_templates($user);
	} // canView

	
	/**
	 * Check if specific user can add new templates to specific project
	 *
	 * @access public
	 * @param Contact $user
	 * @param Member $member
	 * @return boolean
	 */
	function canAddToMember(Contact $contact, Member $member, $context_members) {
		return can_manage_templates($contact) && can_add_to_member($contact,$member,$context_members,$this->getObjectTypeId());
	} // canAdd

	/**
	 * Check if specific user can edit this template
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		return can_manage_templates($user);
	} // canEdit


	/**
	 * Check if specific user can delete this template
	 *
	 * @access public
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		return can_manage_templates($user);
	} // canDelete

	// ---------------------------------------------------
	//  URL
	// ---------------------------------------------------

	function getViewUrl() {
		return get_url('template', 'view', array('id' => $this->getObjectId()));
	} // getViewUrl

	/**
	 * Return edit template URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('template', 'edit', array('id' => $this->getObjectId()));
	} // getEditUrl

	/**
	 * Return delete template URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('template', 'delete', array('id' => $this->getObjectId()));
	} // getDeleteUrl

	function getAssignTemplateToWSUrl() {
		return get_url('template', 'assign_to_ws', array('id' => $this->getObjectId()));
	}
	
	// ---------------------------------------------------
	//  System functions
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return boolean
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) $errors[] = lang('template name required');
	} // validate

	/**
	 * Delete this object and reset all relationship. This function will not delete any of related objec
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function delete() {
		// permanently delete objects set as template (were created specifically for this template)
		$objs = $this->getObjects();
		foreach ($objs as $o) {
			$o->delete();			
		}
		$this->removeObjects();
		TemplateParameters::deleteParametersByTemplate($this->getObjectId());
		TemplateObjectProperties::deletePropertiesByTemplate($this->getObjectId());
		parent::delete();
	} // delete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object URl
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl

	function getTitle() {
		return $this->getObjectName();
	}
	
	function getArrayInfo() {
		return array(
			'id' => $this->getObjectId(),
			't' => $this->getObjectName(),
			'c' => $this->getCreatedOn() instanceof DateTimeValue ? $this->getCreatedOn()->getTimestamp() : 0,
			'cid' => $this->getCreatedById()
		);
	}
	
} // COTemplate

?>