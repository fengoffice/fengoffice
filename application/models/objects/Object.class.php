<?php

/**
 * FengObject class
 *
 * @author Diego Castiglioni
 * @author Conrado Vina
 * 
 */
class FengObject extends BaseObject {
	
	private $object_type = null;
	
	function __destruct() {
		if (isset($this->object_type)) $this->object_type = null;
	}
	
	/**
	 * Returns the object's type instance
	 *
	 * @return ObjectType
	 */
	function getType() {
		return ObjectTypes::instance()->findById($this->getObjectTypeId());
	}
	
	
	function getObjectTypeName(){
		if (!$this->object_type)
			$this->object_type = ObjectTypes::instance()->findById($this->getObjectTypeId());
		return $this->object_type instanceof ObjectType ? $this->object_type->getName() : "";
	}

	
	function getArrayInfo($include_trash_info = false, $include_archive_info = false) {
		$tz_offset = Timezones::getTimezoneOffsetToApplyFromArray(array('timezone_id' => $this->getTimezoneId(), 'timezone_value' => $this->getTimezoneValue()));
		$tz_offset = $tz_offset / 3600;
		
		if ($this->getCreatedOn()){
			if ($this->getCreatedOn()->isToday()) {
				$dateCreated = lang('today') ." ". format_time($this->getCreatedOn(), null, $tz_offset);
				$dateCreatedToday = true;
			} else {
				$dateCreated = format_datetime($this->getCreatedOn(), null, $tz_offset);
				$dateCreatedToday = false;
			}
		}
		if ($this->getUpdatedOn()){
			if ($this->getUpdatedOn()->isToday()) {
				$dateUpdated = lang('today') ." ". format_time($this->getUpdatedOn(), null, $tz_offset);
				$dateUpdatedToday = true;
			} else {
				$dateUpdated = format_datetime($this->getUpdatedOn(), null, $tz_offset);
				$dateUpdatedToday = false;
			}
		}
		
		$info = array(
			'object_id' => $this->getId(),
			'ot_id' => $this->getObjectTypeId(),
			'name' => $this->getName(),
			'type' => $this->getObjectTypeName(),
			'icon' => $this->getType()->getIconClass(),
			'createdBy' => $this->getCreatedByDisplayName(),
			'createdById' => $this->getCreatedById(),
			'dateCreated' => $dateCreated,
			'dateCreated_today' => $dateCreatedToday,
			'updatedBy' => $this->getUpdatedByDisplayName(),
			'updatedById' => $this->getUpdatedById(),
			'dateUpdated' => $dateUpdated,
			'dateUpdated_today' => $dateUpdatedToday,
			'url' => $this->getViewUrl(),
			'timezone_id' => $this->getTimezoneId(),
			'timezone_value' => $this->getTimezoneValue(),
		);
		
		if ($include_trash_info) {
			$dateTrashed = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn(), null, $tz_offset) : format_datetime($this->getTrashedOn(), null, $tz_offset)) : lang('n/a');
			$info['deletedBy'] = $this->getTrashedByDisplayName();
			$info['deletedById'] = $this->getColumnValue('trashed_by_id');
			$info['dateDeleted'] = $dateTrashed;
		}
		if ($include_archive_info) {
			$dateArchived = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn(), null, $tz_offset) : format_datetime($this->getArchivedOn(), null, $tz_offset)) : lang('n/a');
			$archived_by = Contacts::instance()->findById($this->getArchivedById());
			$info['archivedBy'] = $archived_by instanceof Contact ? $archived_by->getObjectName() : lang('n/a');
			$info['archivedById'] = $this->getArchivedById();
			$info['dateArchived'] = $dateArchived;
		}
		
		Hook::fire('general_object_array_info_additional_columns', array('object' => $this), $info);
		
		return $info;
	}
	
	
	function getViewUrl() {
		return get_url($this->getObjectTypeName(), 'view', array("id" => $this->getObjectId()));
	}
	
	
	function getEditUrl() {
		return get_url($this->getObjectTypeName(), 'edit', array("id" => $this->getObjectId()));
	}
	
	
	function getDeleteUrl() {
		return $this->getTrashUrl();
	}
	
	
	function getDeletePermanentlyUrl() {
		return get_url('object', 'delete_permanently', array("object_id" => $this->getObjectId()));
	}
	
	
	function getTrashUrl() {
		return get_url('object', 'trash', array('object_id' => $this->getId()));
	}
		
	
	function getUntrashUrl() {
		return get_url('object', 'untrash', array('object_id' => $this->getId()));
	}
	
	
} // Object
