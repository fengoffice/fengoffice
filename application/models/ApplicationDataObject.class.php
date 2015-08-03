<?php

/**
 * Class that implements method common to all application objects (contacts, projects etc)
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>,  Marcos Saiz <marcos.saiz@fengoffice.com>
 */
abstract class ApplicationDataObject extends DataObject {

	// ---------------------------------------------------
	//  Search
	// ---------------------------------------------------

	/**
	 * If this object is searchable search related methods will be unlocked for it. Else this methods will
	 * throw exceptions pointing that this object is not searchable
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

	/**
	 * Array of searchable columns
	 *
	 * @var array
	 */
	protected $searchable_columns = array();
	
	protected $searchable_composite_columns = array();
	 
	/**
	 * Returns true if this object is searchable (maked as searchable and has searchable columns)
	 *
	 * @param void
	 * @return boolean
	 */
	function isSearchable() {
		return $this->is_searchable && is_array($this->searchable_columns) && (count($this->searchable_columns) > 0);
	} // isSearchable

	/**
	 * Returns array of searchable columns or NULL if this object is not searchable or there
	 * is no searchable columns
	 *
	 * @param void
	 * @return array
	 */
	function getSearchableColumns() {
		if(!$this->isSearchable()) return null;
		return $this->searchable_columns;
	} // getSearchableColumns

	/**
	 * This function will return content of specific searchable column. It can be overriden in child
	 * classes to implement extra behaviour (like reading file contents for project files)
	 *
	 * @param string $column_name Column name
	 * @return string
	 */
	function getSearchableColumnContent($column_name) {
		if(!$this->columnExists($column_name)) 
			throw new Error("Object column '$column_name' does not exist");
		return (string) $this->getColumnValue($column_name);
	} // getSearchableColumnContent

	/**
	 * Clear search index that is associated with this object
	 *
	 * @param void
	 * @return boolean
	 */
	function clearSearchIndex() {
		return SearchableObjects::dropContentByObject($this);
	} // clearSearchIndex

	function addToSearchableObjects($wasNew = false){
		$columns_to_drop = array();
		if ($wasNew)
			$columns_to_drop = $this->getSearchableColumns();
		else {
			$searchable_columns = $this->getSearchableColumns();
			if (is_array($searchable_columns)) {
				foreach ($searchable_columns as $column_name){
					if (isset($this->searchable_composite_columns[$column_name])){
						foreach ($this->searchable_composite_columns[$column_name] as $colName){
							if ($this->isColumnModified($colName)){
								$columns_to_drop[] = $column_name;
								break;
							}
						}
					} else if ($this->isColumnModified($column_name))
						$columns_to_drop[] = $column_name;
				}
			}
			$searchable_columns = null;
		}
		 
		if (count($columns_to_drop) > 0){
			if (!$wasNew) {
				SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);
			}
			
			$docx_id = FileTypes::findOne(array('id' => true, 'conditions' => '`extension` = '.DB::escape('docx')));
			$pdf_id = FileTypes::findOne(array('id' => true, 'conditions' => '`extension` = '.DB::escape('pdf')));
			$odt_id = FileTypes::findOne(array('id' => true, 'conditions' => '`extension` = '.DB::escape('odt')));
			$fodt_id = FileTypes::findOne(array('id' => true, 'conditions' => '`extension` = '.DB::escape('fodt')));
			
			foreach($columns_to_drop as $column_name) {
				$content = $this->getSearchableColumnContent($column_name);
				if (get_class($this->manager()) == 'ProjectFiles') {
					$content = utf8_encode($content);                                    
				}elseif(get_class($this->manager()) == 'ProjectFileRevisions'){
					if($column_name == "filecontent"){
						$file = ProjectFileRevisions::findById($this->getObjectId());

						try {
							
							if($file->getFileTypeId() == $docx_id[0]){
								if (class_exists('DOMDocument')) {
									$file_path = "tmp/doc_filecontent_".$this->getObjectId().".docx";
									$file_tmp = @fopen($file_path, 'w');
									if ($file_tmp) {
										fwrite($file_tmp, $file->getFileContent());
										fclose($file_tmp);
										$content = docx2text($file_path);
										unlink($file_path);
									}
								}

							}elseif($file->getFileTypeId() == $pdf_id[0]){
								if (class_exists('DOMDocument')) {
									$file_path = "tmp/pdf_filecontent_".$this->getObjectId().".pdf";
									$file_tmp = @fopen($file_path, 'w');
									if ($file_tmp) {
										fwrite($file_tmp, $file->getFileContent());
										fclose($file_tmp);
										if (function_exists('pdf_to_text_pro')) {
											$content = pdf_to_text_pro($file_path);
										} else {
											$content = pdf2text($file_path);
										}
										unlink($file_path);
									}
								}
							}elseif($file->getFileTypeId() == $odt_id[0]){
								if (class_exists('DOMDocument')) {
									$file_path = "tmp/odt_filecontent_".$this->getObjectId().".odt";
									$file_tmp = @fopen($file_path, 'w');
									if ($file_tmp) {
										fwrite($file_tmp, $file->getFileContent());
										fclose($file_tmp);
										$content = odt2text($file_path);
										unlink($file_path);
									}
								}
								
							}elseif($file->getFileTypeId() == $fodt_id[0]){
								$file_path = "tmp/fodt_filecontent_".$this->getObjectId().".fodt";
								$file_tmp = @fopen($file_path, 'w');
								if ($file_tmp) {
									fwrite($file_tmp, $file->getFileContent());
									fclose($file_tmp);
									$content = fodt2text($file_path,$this->getObjectId());
									unlink($file_path);
								}
							}
						} catch (FileNotInRepositoryError $e) {
							$content = "";
						}
					}else{
						$content = utf8_encode($content);
					}
				}
				if(trim($content) <> '') {
					$searchable_object = new SearchableObject();
					$searchable_object->setRelObjectId($this->getObjectId());
					$searchable_object->setColumnName($column_name);
					if (strlen($content) > 65535) {
						$content = DB::escape(utf8_safe(substr($content, 0, 65535)));
					} else {
						$content = DB::escape(utf8_safe($content));
					}
					
					$sql = "INSERT INTO ".TABLE_PREFIX."searchable_objects (rel_object_id, column_name, content)
						VALUES ('".$searchable_object->getRelObjectId()."', '".$searchable_object->getColumnName()."', ".$content.")
						ON DUPLICATE KEY UPDATE content=".$content;
					
					DB::execute($sql);
					$searchable_object = null;
				}
				$content = null;
			} 
		}
		
		$columns_to_drop = null;
	}

	function save() {
		$wasNew = $this->isNew();
		Hook::fire ( 'before_'.( ($wasNew)?'insert':'update') , $this, $null); 
		$result = parent::save();
		Hook::fire ( 'after_'.( ($wasNew)?'insert':'update') , $this, $null); 
		if ($result && $this->isSearchable()){
			$this->addToSearchableObjects($wasNew);
		}
		return $result;
	} 
	 
	function delete(){
		Hook::fire('before_delete',$this, $null);
		$this->clearEverything();
		return parent::delete();
		Hook::fire('after_delete',$this, $null);
	}
	
	/**
	 * This function deletes everything related to the object.
	 * Child classes can call this method to clear everything
	 * but not delete the object. 
	 * @return void
	 */
	function clearEverything() {
		if($this->isSearchable()) {
			$this->clearSearchIndex();
		} // if
		if($this->isLinkableObject()) {
			$this->clearLinkedObjects();
		} // if
	}

	function getTitle(){
		return lang('no title');
	}

	// ---------------------------------------------------
	//  Linked Objects (Replacement for attached files)
	// ---------------------------------------------------

	/**
	 * Mark this object as linkable to another object (in this case other project data objects can be linked to
	 * this object)
	 *
	 * @var boolean
	 */
	protected $is_linkable_object= true;

	/**
	 * Array of all linked objects
	 *
	 * @var array
	 */
	protected $all_linked_objects;

	/**
	 * Cached array of linked objects (filtered by users access permissions)
	 *
	 * @var array
	 */
	protected $linked_objects;

	/**
	 * Cached array of linked objects (filtered by users access permissions and excluding trashed objects)
	 *
	 * @var array
	 */
	protected $linked_objects_no_trashed;



	/**
	 * Cached author object reference
	 *
	 * @var Contact
	 */
	protected $created_by = null;

	/**
	 * Cached reference to user who created last update on object
	 *
	 * @var Contact
	 */
	protected $updated_by = null;

	/**
	 * Cached reference to user who created last update on object
	 *
	 * @var Contact
	 */
	protected $trashed_by = null;
	


	/**
	 * Return object ID
	 *
	 * @param void
	 * @return integer
	 */
	function getObjectId() {
		if ($this->columnExists('id')) {
			return $this->getColumnValue('id');
		} else if ($this->columnExists('object_id')) {
			return $this->getColumnValue('object_id');
		} else {
			return null;
		}
	} // getObjectId

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		return $this->columnExists('name') ? $this->getName() : null;
	} // getObjectName

	
	/**
	 * Return object type name - message, user, project etc
	 *
	 * @param void
	 * @return string
	 */
	function getObjectTypeName() {
		return '';
	} // getObjectTypeName
	
	/**
	 * Return object URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return '#';
	} // getObjectUrl

	/**
	 * Return time when this object was created
	 *
	 * @param void
	 * @return DateTime
	 */
	function getObjectCreationTime() {
		return $this->columnExists('created_on') ? $this->getCreatedOn() : null;
	} // getObjectCreationTime

	/**
	 * Return time when this object was updated last time
	 *
	 * @param void
	 * @return DateTime
	 */
	function getObjectUpdateTime() {
		return $this->columnExists('updated_on') ? $this->getUpdatedOn() : $this->getObjectCreationTime();
	} // getOjectUpdateTime

	/**
	 * Return time when this object was updated last time
	 *
	 * @param void
	 * @return DateTime
	 */
	function getViewHistoryUrl() {
		return get_url('object','view_history',array('id'=> $this->getId()));
	} // getViewHistoryUrl

	// ---------------------------------------------------
	//  Created by
	// ---------------------------------------------------

	/**
	 * Return user who created this message
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getCreatedBy() {
		if(is_null($this->created_by)) {
			if($this->columnExists('created_by_id')) $this->created_by = Contacts::findById($this->getCreatedById());
		} //
		return $this->created_by;
	} // getCreatedBy

	/**
	 * Return display name of author
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getCreatedByDisplayName() {
		$created_by = $this->getCreatedBy();
		return $created_by instanceof Contact ? $created_by->getObjectName() : lang('n/a');
	} // getCreatedByDisplayName

	/**
	 * Return card URL of created by user
	 *
	 * @param void
	 * @return string
	 */
	function getCreatedByCardUrl() {
		$created_by = $this->getCreatedBy();
		return $created_by instanceof Contact ? $created_by->getCardUserUrl() : null;
	} // getCreatedByCardUrl

	// ---------------------------------------------------
	//  Updated by
	// ---------------------------------------------------

	/**
	 * Return user who updated this object
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getUpdatedBy() {
		if(is_null($this->updated_by)) {
			if($this->columnExists('updated_by_id')) $this->updated_by = Contacts::findById($this->getUpdatedById());
		} //
		return $this->updated_by;
	} // getCreatedBy

	/**
	 * Return display name of author
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getUpdatedByDisplayName() {
		$updated_by = $this->getUpdatedBy();
		return $updated_by instanceof Contact ? $updated_by->getObjectName() : lang('n/a');
	} // getUpdatedByDisplayName

	/**
	 * Return card URL of created by user
	 *
	 * @param void
	 * @return string
	 */
	function getUpdatedByCardUrl() {
		$updated_by = $this->getUpdatedBy();
		return $updated_by instanceof Contact ? $updated_by->getCardUserUrl() : null;
	} // getUpdatedByCardUrl
	
	// ---------------------------------------------------
	//  Trashed by
	// ---------------------------------------------------

	/**
	 * Return user who trashed this object
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getTrashedBy() {
		if(is_null($this->trashed_by)) {
			if($this->columnExists('trashed_by_id')) $this->trashed_by = Contacts::findById($this->getTrashedById());
		} //
		return $this->trashed_by;
	} // getTrashedBy

	/**
	 * Return display name of trasher
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getTrashedByDisplayName() {
		$trashed_by = $this->getTrashedBy();
		return $trashed_by instanceof Contact ? $trashed_by->getObjectName() : lang('n/a');
	} // getTrashedByDisplayName

	/**
	 * Return card URL of trashed by user
	 *
	 * @param void
	 * @return string
	 */
	function getTrashedByCardUrl() {
		$trashed_by = $this->getTrashedBy();
		return $trashed_by instanceof Contact ? $trashed_by->getCardUserUrl() : null;
	} // getTrashedByCardUrl

	// ---------------------------------------------------
	//  Linked Objects
	// ---------------------------------------------------

	/**
	 * This function will return true if this object can have objects linked to it
	 *
	 * @param void
	 * @return boolean
	 */
	function isLinkableObject() {
		return $this->is_linkable_object;
	} // isLinkableObject

	/**
	 * Link object to this object
	 *
	 * @param ApplicationDataObject $object
	 * @return LinkedObject
	 */
	function linkObject(ApplicationDataObject $object) {
		$object_id = $this->getObjectId();

		$linked_object = LinkedObjects::findById(array(
        'rel_object_id' => $object_id,
        'object_id' => $object->getId(),
		)); // findById

		if($linked_object instanceof LinkedObject) {
			return $linked_object; // Already linked
		}
		else
		{//check inverse link
			$linked_object = LinkedObjects::findById(array(
	        'rel_object_id' => $object->getId(),
	        'object_id' => $object_id,
			)); // findById
			if($linked_object instanceof LinkedObject) {
				return $linked_object; // Already linked
			}
		} // if

		$linked_object = new LinkedObject();
		$linked_object->setRelObjectId($object_id);
		$linked_object->setObjectId($object->getId());

		$linked_object->save();
		
		return $linked_object;
	} // linkObject

	/**
	 * Return all linked objects
	 *
	 * @param void
	 * @return array
	 */
	function getAllLinkedObjects() {
		$this->all_linked_objects = LinkedObjects::getLinkedObjectsByObject($this);
		return $this->all_linked_objects;
	} //  getAllLinkedObjects

	/**
	 * Return linked objects but filter the private ones if user is not a member
	 * of the owner company
	 *
	 * @param void
	 * @return array
	 */
	function getLinkedObjects() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			$objects = $this->getAllLinkedObjects();
		} else {
			if (is_null($this->linked_objects)) {
				$this->linked_objects = LinkedObjects::getLinkedObjectsByObject($this, true);
			}
			$objects = $this->linked_objects;
		}
		if ($this instanceof ContentDataObject && $this->isTrashed()) {
			$include_trashed = true;
		} else {
			$include_trashed = false;
		}
		if ($include_trashed) {
			return $objects;
		} else {
			$ret = array();
			if (is_array($objects) && count($objects)) {
				foreach ($objects as $o) {
					if (!$o instanceof ContentDataObject || !$o->isTrashed()) {
						$ret[] = $o;
					}
				}
			}
			return $ret;
		}
	} // getLinkedObjects
	
	function copyLinkedObjectsFrom($object) {
		$linked_objects = $object->getAllLinkedObjects();
		if (is_array($linked_objects)) {
			foreach ($linked_objects as $lo) {
				$this->linkObject($lo);
			}
		}
	}
	
	/**
	 * Drop all relations with linked objects for this object
	 *
	 * @param void
	 * @return null
	 */
	function clearLinkedObjects() {
		return LinkedObjects::clearRelationsByObject($this);
	} // clearLinkedObjects

	/**
	 * Return link objects url
	 *
	 * @param void
	 * @return string
	 */
	function getLinkObjectUrl() {
		return get_url('object', 'link_to_object', array(
        'object_id' => $this->getObjectId()
		)); // get_url
	} // getLinkedObjectsUrl

	/**
	 * Return object properties url
	 *
	 * @param void
	 * @return string
	 */
	function getObjectPropertiesUrl() {
		return get_url('object', 'view_properties', array(
        'object_id' => $this->getObjectId()
		)); // get_url
	} // getLinkedObjectsUrl

	/**
	 * Return unlink object URL
	 *
	 * @param ApplicationDataObject $object
	 * @return string
	 */
	function getUnlinkObjectUrl(ApplicationDataObject $object) {
		return get_url('object', 'unlink_from_object', array(
        'object_id' => $this->getObjectId(),
        'rel_object_id' => $object->getId(),
		)); // get_url
	} //  getUnlinkedObjectUrl


	/**
	 * Returns true if user can link an object to this object
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canLinkObject(Contact $user) {
		if(!$this->isLinkableObject()) return false;
				
		if(can_link_objects($user)){
			return $this->canEdit($user);
		}else{
			return false;
		}
	} // canLinkObject

	/**
	 * Check if $user can un-link $object from this object
	 *
	 * @param Contact $user
	 * @param ApplicationDataObject $object
	 * @return booealn
	 */
	function canUnlinkObject(Contact $user, ApplicationDataObject $object) {
		return $this->canEdit($user);
	} // canUnlinkObject


	//TODO revisar funcion
	function copy() {
		$class = get_class($this);
		$copy = new $class();
		$cols = $this->getColumns();
		$not_to_be_copied = array(
			'id',
			'created_on',
			'created_by_id',
			'updated_on',
			'updated_by_id',
			'trashed_on',
			'trashed_by_id',
		); // columns with special meanings that are not to be copied
		foreach ($cols as $col) {
			if (!in_array($col, $not_to_be_copied)) {
				$copy->setColumnValue($col, $this->getColumnValue($col));
			}
		}
		$cols = null;
		return $copy;
	}
	
	function isTrashed() {
		return false;
	}
	
	// ---------------------------------------------------
	//  Object Properties
	// ---------------------------------------------------
	/**
	 * Returns whether an object can have properties
	 *
	 * @return bool
	 */
	function isPropertyContainer(){
		return $this->is_property_container;
	}

	/**
	 * Given the object_data object (i.e. file_data) this function
	 * updates all ObjectProperties (deleting or creating them when necessary)
	 *
	 * @param  $object_data
	 */
	function save_properties($object_data){
		$properties = array();
		for($i = 0; $i < 200; $i++) {
			if(isset($object_data["property$i"]) && is_array($object_data["property$i"]) &&
			(trim(array_var($object_data["property$i"], 'id')) <> '' || trim(array_var($object_data["property$i"], 'name')) <> '' ||
			trim(array_var($object_data["property$i"], 'value')) <> '')) {
				$name = array_var($object_data["property$i"], 'name');
				$id = array_var($object_data["property$i"], 'id');
				$value = array_var($object_data["property$i"], 'value');
				if($id && trim($name)=='' && trim($value)=='' ){
					$property = ObjectProperties::findById($id);
					$property->delete( 'id = $id');
				}else{
					if($id){
						{
							SearchableObjects::dropContentByObjectColumn($this, 'property' . $id);
							$property = ObjectProperties::findById($id);
						}
					}else{
						$property = new ObjectProperty();
						$property->setRelObjectId($this->getId());
					}
					$property->setFromAttributes($object_data["property$i"]);
					$property->save();
						
					if ($this->isSearchable())
					$this->addPropertyToSearchableObject($property);
				}
			} // if
			else break;
		} // for
	}

	function addPropertyToSearchableObject(ObjectProperty $property){
		$searchable_object = new SearchableObject();
		 
		$searchable_object->setRelObjectId($this->getObjectId());
		$searchable_object->setColumnName('property'.$property->getId());
		$searchable_object->setContent($property->getPropertyValue());
	  
		$searchable_object->save();
	}

	/**
	 * Get one value of a property. Returns an empty string if there's no value.
	 *
	 * @param string $name
	 * @return string
	 */
	function getProperty($name) {
		$op = ObjectProperties::getPropertyByName($this, $name);
		if ($op instanceof ObjectProperty) {
			return $op->getPropertyValue();
		} else {
			return "";
		}
	}

	/**
	 * Return all values of a property
	 *
	 * @param string $name
	 * @return array
	 */
	function getProperties($name) {
		$ops = ObjectProperties::getAllProperties($this, $name);
		$ret = array();
		foreach ($ops as $op) {
			$ret[] = $op->getPropertyValue();
		}
		return $ret;
	}
	
	/**
	 * Returns all ObjectProperties of the object.
	 *
	 * @return array
	 */
	function getCustomProperties() {
		return ObjectProperties::getAllPropertiesByObject($this);
	}
	
	/**
	 * Copies custom properties from an object
	 * @param ApplicationDataObject $object
	 */
	function copyCustomPropertiesFrom($object) {
		$properties = $object->getCustomProperties();
		foreach ($properties as $property) {
			$copy = new ObjectProperty();
			$copy->setPropertyName($property->getPropertyName());
			$copy->setPropertyValue($property->getPropertyValue());
			$copy->setObject($this);
			$copy->save();
		}
	}

	/**
	 * Sets the value of a property, removing all its previous values.
	 *
	 * @param string $name
	 * @param string $value
	 */
	function setProperty($name, $value) {
		$this->deleteProperty($name);
		$this->addProperty($name, $value);
	}

	/**
	 * Adds a value to property $name
	 *
	 * @param string $name
	 * @param string $value
	 */
	function addProperty($name, $value) {
		$op = new ObjectProperty();
		$op->setRelObjectId($this->getId());
		$op->setPropertyName($name);
		$op->setPropertyValue($value);
		$op->save();
	}

	/**
	 * Deletes all values of property $name.
	 *
	 * @param string $name
	 */
	function deleteProperty($name) {
		ObjectProperties::deleteByObjectAndName($this, $name);
	}
	

	function clearObjectProperties(){
		ObjectProperties::deleteAllByObject($this);
		if ($this->isSearchable()){
			SearchableObjects::dropObjectPropertiesByObject($this);
		}
	}

} // ApplicationDataObject

?>