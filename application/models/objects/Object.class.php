<?php

/**
 * Object class
 *
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
class Object extends BaseObject {
	
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
		return ObjectTypes::findById($this->getObjectTypeId());
	}
	
	
	function getObjectTypeName(){
		if (!$this->object_type)
			$this->object_type = ObjectTypes::findById($this->getObjectTypeId());
		return $this->object_type instanceof ObjectType ? $this->object_type->getName() : "";
	}

	
	function getArrayInfo($include_trash_info = false, $include_archive_info = false) {
		if ($this->getCreatedOn()){
			$dateCreated = $this->getCreatedOn()->isToday() ? lang('today') ." ". format_time($this->getCreatedOn()) : format_datetime($this->getCreatedOn());
		}
		if ($this->getUpdatedOn()){
			$dateUpdated = $this->getUpdatedOn()->isToday() ? lang('today') ." ". format_time($this->getUpdatedOn()) : format_datetime($this->getUpdatedOn());
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
			'updatedBy' => $this->getUpdatedByDisplayName(),
			'updatedById' => $this->getUpdatedById(),
			'dateUpdated' => $dateUpdated,
			'url' => $this->getViewUrl()
		);
		
		if ($include_trash_info) {
			$dateTrashed = $this->getTrashedOn() instanceof DateTimeValue ? ($this->getTrashedOn()->isToday() ? format_time($this->getTrashedOn()) : format_datetime($this->getTrashedOn())) : lang('n/a');
			$info['deletedBy'] = $this->getTrashedByDisplayName();
			$info['deletedById'] = $this->getColumnValue('trashed_by_id');
			$info['dateDeleted'] = $dateTrashed;
		}
		if ($include_archive_info) {
			$dateArchived = $this->getArchivedOn() instanceof DateTimeValue ? ($this->getArchivedOn()->isToday() ? format_time($this->getArchivedOn()) : format_datetime($this->getArchivedOn())) : lang('n/a');
			$archived_by = Contacts::findById($this->getArchivedById());
			$info['archivedBy'] = $archived_by instanceof Contact ? $archived_by->getObjectName() : lang('n/a');
			$info['archivedById'] = $this->getArchivedById();
			$info['dateArchived'] = $dateArchived;
		}
		
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
