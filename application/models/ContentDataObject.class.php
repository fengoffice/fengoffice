<?php

/**
 * Abstract class that implements methods that share all content objects
 *
 * @author FengOffice
 */
abstract class ContentDataObject extends ApplicationDataObject {
	
	/**
	 * @var FengObject
	 */
	var $object;
	
	var $memberIds = null;
	
	var $members = null;
	
	/**
	 * 
	 * @var string
	 */
	var $summary_field = "name";
	
	function __construct() {
		$this->object = new FengObject();
		$this->object->setObjectTypeId($this->manager()->getObjectTypeId());
		if ($this->is_searchable) {
			$this->searchable_columns[] = 'object_id';
			$this->searchable_columns[] = 'name';
		}
	}
	
	function __destruct() {
		if (isset($this->object)) {
			$this->object->__destruct();
			$this->object = null;
		}
	}
	
	
	/**
	 * If true this object will not throw no timeslots allowed exception and will make timeslot methods available
	 *
	 * @var boolean
	 */
	protected $allow_timeslots = false;
	
	
	/**
	 * Users can post comments on Content Data Objects are searchable
	 *
	 * @var boolean
	 */
	protected $is_commentable = true;
	
	/**
	 * Content Data Objects are searchable
	 *
	 * @var boolean
	 */	
	protected $is_searchable = true;
	
 	/**
	 * Whether the object can have properties
	 *
	 * @var bool
	 */
	protected $is_property_container = true;
	
	
	protected $all_comments = null;
	protected $comments = null;
	
	protected $timeslots = null;
	
	/**
	 * When this attribute is set to true, no calculations in other tables should be triggered
	 */
	protected $dont_make_calculations = false;
	
	/**
	 * 
	 * Enter description here ...
	 */
	function getObject() {
		return $this->object;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param FengObject $object
	 */
	function setObject(FengObject $object) {
		$this->object = $object;
	}
	
	
	/**
	 * Return value of 'id' field
	 *
	 * @access public
	 * @param void
	 * @return integer 
	 */
	function getId() {
		return $this->object->getId();
	} // getId()
	

	/**
	 * Set value of 'id' field
	 *
	 * @access public   
	 * @param integer $value
	 * @return boolean
	 */
	function setId($value) {
		return $this->object->setId ($value);
	} // setId() 
	
	
	/**
	 * Set value of 'id' field
	 *
	 * @access public   
	 * @param integer $value
	 * @return boolean
	 */
	function setObjectId($value) {
		return $this->setId($value);
	} // setId() 
	
	
	/**
	 * Return value of 'object_type_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getObjectTypeId() {
		return $this->object ? $this->object->getObjectTypeId() : '';
	} // getObjectTypeId()
	
	
	function getObjectTypeName(){
		return $this->object ? $this->object->getObjectTypeName() : '';
	}// getObjectTypeName()
	

	/**
	 * Set value of 'object_type_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setObjectTypeId($value) {
		return $this->object->setObjectTypeId ($value);
	} // setObjectTypeId()
	

	/**
	 * Return value of 'name' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getObjectName() {
		return $this->object ? $this->object->getName() : '';
	} // getName()

	function getName() {
		return $this->getObjectName();
	} // getName()

	function getTitle(){
		return $this->getObjectName();
	}
	

	/**
	 * Set value of 'name' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setObjectName($value) {
		$value = preg_replace('/\s+/', ' ', trim($value)); // remove enters
		return $this->object->setName($value);
	} // setName() 
	

	/**
	 * Return value of 'created_on' field
	 *
	 * @access public
	 * @param void
	 * @return DateTimeValue 
	 */
	function getCreatedOn() {
		return $this->object->getCreatedOn ();
	} // getCreatedOn()
	

	/**
	 * Set value of 'created_on' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setCreatedOn($value) {
		return $this->object->setCreatedOn ( $value );
	
	} // setCreatedOn() 
	

	/**
	 * Return value of 'created_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getCreatedById() {
		return $this->object->getCreatedById();
	} // getCreatedById()
	
	
	/**
	 * Return user who created this object
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	var $created_by = null;
	function getCreatedBy() {
		if(is_null($this->created_by)) {
			if($this->object->columnExists('created_by_id')) $this->created_by = Contacts::findById($this->getCreatedById());
		} //
		return $this->created_by;
	} // getCreatedBy

	
	/**
	 * Set value of 'created_by_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setCreatedById($value) {
		return $this->object->setCreatedById ( $value );
	} // setCreatedById() 
	

	/**
	 * Return user who updated this object
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	var $updated_by = null;
	function getUpdatedBy() {
		if(is_null($this->updated_by)) {
			if($this->object->columnExists('updated_by_id')) $this->updated_by = Contacts::findById($this->getUpdatedById());
		} //
		return $this->updated_by;
	} // getUpdatedBy
	
	
	/**
	 * Return value of 'updated_on' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getUpdatedOn() {
		return $this->object->getUpdatedOn ();
	} // getUpdatedOn()
	

	/**
	 * Set value of 'updated_on' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setUpdatedOn($value) {
		return $this->object->setUpdatedOn ( $value );
	} // setUpdatedOn() 
	

	/**
	 * Return value of 'updated_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getUpdatedById() {
		return $this->object->getUpdatedById ();
	
	} // getUpdatedById()
	

	/**
	 * Set value of 'updated_by_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setUpdatedById($value){
		return $this->object->setUpdatedById($value);
	} // setUpdatedById() 
	

	/**
	 * Return user who trashed this object
	 *
	 * @access public
	 * @param void
	 * @return User
	 */
	var $trashed_by = null;
	function getTrashedBy() {
		if(is_null($this->trashed_by)) {
			if($this->object->columnExists('trashed_by_id')) $this->trashed_by = Contacts::findById($this->getTrashedById());
		} //
		return $this->trashed_by;
	} // getTrashedBy	
	
	
	/**
	 * Return value of 'trashed_on' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getTrashedOn(){
		return $this->object->getTrashedOn();
	} // getTrashedOn()
	

	/**
	 * Set value of 'trashed_on' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setTrashedOn($value){
		return $this->object->setTrashedOn($value);
	} // setTrashedOn()   
	

	/**
	 * Return value of 'archived_on' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getArchivedOn() {
		return $this->object->getArchivedOn();
	} // getArchivedOn()
	

	/**
	 * Set value of 'archived_on' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setArchivedOn($value){
		return $this->object->setArchivedOn ($value);
	} // setArchivedOn() 
	

	/**
	 * Return value of 'archived_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getArchivedById(){
		return $this->object->getArchivedById();
	} // getArchivedById()
	

	/**
	 * Set value of 'archived_by_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setArchivedById($value){
		return $this->object->setArchivedById ($value);
	} // setArchivedById()   
	
	
	/**
	 * Return value of 'trashed_by_id' field
	 *
	 * @access public
	 * @param void
	 * @return string 
	 */
	function getTrashedById(){
		return $this->object->getTrashedById();
	} // getTrashedById()
	

	/**
	 * Set value of 'trashed_by_id' field
	 *
	 * @access public   
	 * @param string $value
	 * @return boolean
	 */
	function setTrashedById($value){
		return $this->object->setTrashedById($value);
	} // setTrashedById()   

	
	
	function getDontMakeCalculations() {
		return $this->dont_make_calculations;
	}
	function setDontMakeCalculations($value) {
		$this->dont_make_calculations = $value;
	}
	
	
	// timezone attributes
	function getTimezoneId() {
    	return $this->getObject()->getColumnValue('timezone_id');
    }
    function setTimezoneId($value) {
    	return $this->getObject()->setColumnValue('timezone_id', $value);
    }
    
    function getTimezoneValue() {
    	return $this->getObject()->getColumnValue('timezone_value');
    }
    function setTimezoneValue($value) {
    	return $this->getObject()->setColumnValue('timezone_value', $value);
    }

	
	/**
	 * (non-PHPdoc)
	 * @see DataObject::validatePresenceOf()
	 * @author Pepe
	 */
	function validatePresenceOf($field, $trim_string = true){
		return (parent::validatePresenceOf ( $field, $trim_string ) || $this->object->validatePresenceOf ( $field, $trim_string ));
	}
	

	/**
	 * (non-PHPdoc)
	 * @see DataObject::validateMaxValueOf()
	 * @author Pepe
	 */
	function validateMaxValueOf($column, $max) {
		return (parent::validateMaxValueOf ( $column, $max ) || $this->object->validateMaxValueOf ( $column, $max ));
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see DataObject::setFromAttributes()
	 * @author Pepe
	 */
	function setFromAttributes($attributes) {
		parent::setFromAttributes ($attributes);
		$this->object->setFromAttributes($attributes);
	}
	
	function getAllAttributes() {
		$attributes = array();
		
		$columns = $this->getColumns();
		foreach ($columns as $column) {
			$attributes[$column] = $this->getColumnValueType($column);
		}
		
		$obj_columns = $this->object->getColumns();
		foreach ($obj_columns as $obj_column) {
			$attributes[$obj_column] = $this->getColumnValueType($obj_column);
		}
		
		return $attributes;
	}
	
	
	/**
	 * Load data from database row. Load content to the object reference
	 *
	 * @access public
	 * @param array $row Database row
	 * @return boolean
	 * @author Pepe
	 */
	function loadFromRow($row) {
		if (is_array ( $row )) {
			foreach ( $row as $k => $v ) {
				
				if ($this->columnExists ( $k )) {
					$this->setColumnValue ( $k, $v );
				}
				if ($this->object->columnExists ( $k )) {
					$this->object->setColumnValue ( $k, $v );
				}
			}
			
			// Prepare stamps...
			$this->setLoaded ( true );
			$this->object->setLoaded ( true );
			$this->notModified ();
			$this->object->notModified ();
			$row = null;
			return true;
		}
		return false;
	}
	
	
	/**
	 * 
	 * @author Pepe
	 */
	function getUniqueObjectId() {
		return $this->getObjectId();
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see DataObject::isColumnModified()
	 */
	function isColumnModified($column_name) {
  	  return parent::isColumnModified($column_name) ||  $this->object->isColumnModified($column_name);
  	} 
  	
  	
	/**
	 * (non-PHPdoc)
	 * @see ApplicationDataObject::getSearchableColumnContent()
	 */
	function getSearchableColumnContent($column_name) {
			
		if($this->columnExists($column_name)) {	
			$content = (string) $this->getColumnValue($column_name);
		}elseif ($this->object->columnExists($column_name)){
			$content = (string) $this->object->getColumnValue($column_name);
		}else{
			throw new Error("Object column '$column_name' does not exist");
		}
		
		return $content ;
		
	} 
	
	
	function copy($copy_members = true) {
		/* @var $copy ContentDataObject */
		$copy  = parent::copy() ;
		$copy->setObject($this->object->copy());
		
		$copy->save();
		
		if ($copy_members) {
			$members = $this->getMembers();
			$copy->addToMembers($members);
			$copy->addToSharingTable();
		}
		
		$copy->copy_custom_properties($this);
		
		Hook::fire('after_content_data_object_copy', array('object' => $this), $copy);
		
		return $copy ;
	}
	
	function copy_custom_properties($object_from) {
		if (!$object_from instanceof ContentDataObject) return;
		
		$cp_values = CustomPropertyValues::findAll(array('conditions' => 'object_id = '.$object_from->getId()));
		foreach ($cp_values as $cp_value) {
			$cp = CustomProperties::getCustomProperty($cp_value->getCustomPropertyId());
			$new_cp_value = new CustomPropertyValue();
			$new_cp_value->setObjectId($this->getId());
			$new_cp_value->setCustomPropertyId($cp_value->getCustomPropertyId());
			
			if ($cp->getType() == 'image') {
				
				if ($cp_value->getValue() != "") {
					$json = json_decode($cp_value->getValue(), true);
					
					$original_repo_id = $json['repository_id'];
					$file_content = FileRepository::getFileContent($original_repo_id);
					$tmp_name = gen_id();
					file_put_contents(ROOT."/tmp/$tmp_name", $file_content);
					
					$repo_id = FileRepository::addFile(ROOT."/tmp/$tmp_name", array('type' => $type, 'public' => true));
					$json['repository_id'] = $repo_id;
					
					$new_cp_value->setValue(json_encode($json));
					@unlink(ROOT."/tmp/$genid");
				}
				
			} else {
				$new_cp_value->setValue($cp_value->getValue());
			}
			$new_cp_value->save();
		}
	}
	
	/**
	 * Save object. If object is searchable this function will add content of searchable fields
	 * to search index
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		$disk_space_used = config_option ( 'disk_space_used' );
		if ($disk_space_used && $disk_space_used > config_option ( 'disk_space_max' )) {
			throw new Exception ( lang ( 'maximum disk space reached' ) );
		}
		// Insert into base table 
		$this->setObjectTypeId($this->manager()->getObjectTypeId());
		if ($this->getObject()->save()) {
			$id = $this->getObject()->getId();
			if (is_numeric($id)) {
				$this->setObjectId($id);
			} else {
				throw new Exception(lang('unable to save'));
			
			}
			
			if (config_option('getting_started_step') < 98) {
				if (in_array($this->getObjectTypeName(), array('task','message','weblink','file','expense','objective','event'))) {
					set_config_option('getting_started_step', 98);
					evt_add('reload tab panel', 'more-panel');
				}
			}
			
			parent::save();
			return true;
		
		}
		return false;
	} // save
	
	function getColumnValue($column_name, $default = null) {
		if ($this->columnExists($column_name)) {
			return parent::getColumnValue($column_name, $default);
		} else if ($this->object->columnExists($column_name)) {
			return $this->object->getColumnValue($column_name, $default);
		} else {
			return $default;
		}
	}
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	
	/**
	 * Can $user view this object
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	abstract function canView(Contact $user);

	
	/**
	 * Check if this user can add a new object to $member.
	 *
	 * @param Contact $user
	 * @param Member $member
	 * @param array $context_members
	 * @return boolean
	 */
	function canAddToMember(Contact $user, Member $member, $context_members) {		
		return can_add_to_member($user,$member,$context_members,$this->getObjectTypeId());
	} // canAddToMember

	
	/**
	 * Check if this user can add a new object in the actual context.
	 *
	 * @param Contact $user
	 * @param array $context_members
	 * @return boolean
	 */
	abstract function canAdd(Contact $user, $context, &$notAlloweMember='');
	
	
	/**
	 * Returns true if this user can edit this object
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	abstract function canEdit(Contact $user);

	
	/**
	 * Returns true if this user can delete this object
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	abstract function canDelete(Contact $user);

	
	
	
	// ---------------------------------------------------
	//  Commentable
	// ---------------------------------------------------

	
	/**
	 * Returns true if users can post comments on this object
	 *
	 * @param void
	 * @return boolean
	 */
	function isCommentable() {
		return (boolean) $this->is_commentable;
	} // isCommentable
	
	
	/**
	 * Attach comment to this object
	 *
	 * @param Comment $comment
	 * @return Comment
	 */
	function attachComment(Comment $comment) {
		$object_id = $this->getObjectId();

		if(($object_id == $comment->getRelObjectId())) {
			return true;
		} // if

		$comment->setRelObjectId($object_id);

		$comment->save();
		return $comment;
	} // attachComment

	
	/**
	 * Return all comments
	 *
	 * @param void
	 * @return boolean
	 */
	function getAllComments($include_trashed = false) {
		if(is_null($this->all_comments)) {
			$this->all_comments = Comments::getCommentsByObject($this, $include_trashed);
		} // if
		return $this->all_comments;
	} // getAllComments

	
	/**
	 * Return object comments, filter private comments if user is not member of owner company
	 *
	 * @param void
	 * @return array
	 */
	function getComments($include_trashed = false) {
		if(logged_user() && logged_user()->isMemberOfOwnerCompany()) {
			return $this->getAllComments($include_trashed);
		} // if
		if(is_null($this->comments)) {
			$this->comments = Comments::getCommentsByObject($this, $include_trashed);
		} // if
		return $this->comments;
	} // getComments

	
	/**
	 * This function returns the total number of comments on the object
	 *
	 * @param void
	 * @return integer
	 */
	private $all_comments_count = null;
	function countAllComments() {
		if(is_null($this->all_comments_count)) {
			$this->all_comments_count = Comments::countCommentsByObject($this);
		} // if
		return $this->all_comments_count;
	} // countAllComments

	
	/**
	 * Return total number of comments
	 *
	 * @param void
	 * @return integer
	 */
	function countComments() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			return $this->countAllComments();
		} // if
		if(is_null($this->comments_count)) {
			$this->comments_count = Comments::countCommentsByObject($this, true);
		} // if
		return $this->comments_count;
	} // countComments

	
	/**
	 * Return # of specific object
	 *
	 * @param Comment $comment
	 * @return integer
	 */
	function getCommentNum($comment) {
		$comments = $this->getComments();
		if(is_array($comments)) {
			$counter = 0;
			foreach($comments as $object_comment) {
				$counter++;
				if($comment->getId() == $object_comment->getId()) return $counter;
			} // foreach
		} // if
		return 0;
	} // getCommentNum

	
	/**
	 * Returns true if this function has associated comments
	 *
	 * @param void
	 * @return boolean
	 */
	function hasComments() {
		return (boolean) $this->countComments();
	} // hasComments

	
	/**
	 * Clear object comments
	 *
	 * @param void
	 * @return boolean
	 */
	function clearComments() {
		return Comments::dropCommentsByObject($this);
	} // clearComments

	/**
	 * This event is triggered when we create a new comments
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onAddComment(Comment $comment) {
		if ($this->isSearchable()){
			$searchable_object = new SearchableObject();
			 
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('comment' . $comment->getId());
			$searchable_object->setContent($comment->getText());
			 
			$searchable_object->save();
		}
		return true;
	} // onAddComment

	
	/**
	 * This event is trigered when comment that belongs to this object is updated
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onEditComment(Comment $comment) {
		if ($this->isSearchable()){
			SearchableObjects::dropContentByObjectColumn($this,'comment' . $comment->getId());
			$searchable_object = new SearchableObject();
			 
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('comment' . $comment->getId());
			$searchable_object->setContent($comment->getText());
			 
			$searchable_object->save();
		}
		return true;
	} // onEditComment

	
	/**
	 * This event is triggered when comment that belongs to this object is deleted
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onDeleteComment(Comment $comment) {
		if ($this->isSearchable())
			SearchableObjects::dropContentByObjectColumn($this,'comment' . $comment->getId());
	} // onDeleteComment

	
	// ---------------------------------------------------
	//  Subscriptions
	// ---------------------------------------------------

	
	/**
	 * Cached array of subscribers
	 *
	 * @var array
	 */
	private $subscribers;

	
	/**
	 * Return array of subscribers
	 *
	 * @param void
	 * @return array
	 */
	function getSubscribers() {
		if(is_null($this->subscribers)) $this->subscribers = ObjectSubscriptions::getUsersByObject($this);
			return $this->subscribers;
	} // getSubscribers
	
	
	function getSubscriberIds() {
		$subscribers = $this->getSubscribers();
		$ids = array();
		foreach ($subscribers as $subscriber) {
			$ids[] = $subscriber->getId();
		}
		return $ids;
	}

	
	/**
	 * Check if specific user is subscriber
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function isSubscriber(Contact $user) {
		if ($this->isNew()) return false;
		$subscription = ObjectSubscriptions::findById(array(
        	'object_id' => $this->getId(),
        	'contact_id' => $user->getId()
		)); // findById
		return $subscription instanceof ObjectSubscription;
	} // isSubscriber

	
	/**
	 * Subscribe specific user to this message
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	//function subscribeUser(Contact $user) {
	function subscribeUser($user) {
		if($this->isNew()) {
			throw new Error('Can\'t subscribe user to object that is not saved');
		} // if
		if($this->isSubscriber($user)) {
			return true;
		} // if

		$this->subscribers = null;
		
		// New subscription
		$subscription = new ObjectSubscription();
		$subscription->setObjectId($this->getId());
		$subscription->setContactId($user->getId());
		return $subscription->save();
	} // subscribeUser

	
	/**
	 * Unsubscribe user
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function unsubscribeUser($user) {
		$subscription = ObjectSubscriptions::findById(array(
        'object_id' => $this->getId(),
        'contact_id' => $user->getId()
		)); // findById
		if($subscription instanceof ObjectSubscription) {
			return $subscription->delete();
		} else {
			return true;
		} // if
	} // unsubscribeUser

	
	/**
	 * Clear all object subscriptions
	 *
	 * @param void
	 * @return boolean
	 */
	function clearSubscriptions() {
		$this->subscribers = null;
		return ObjectSubscriptions::clearByObject($this);
	} // clearSubscriptions

	
	function clearReminders($user = null, $include_subscribers = false) {
		if (isset($user)) {
			return ObjectReminders::clearByObjectAndUser($this, $user, $include_subscribers);
		} else {
			return ObjectReminders::clearByObject($this);
		}
	}

	
	/**
	 * Return subscribe URL
	 *
	 * @param void
	 * @return boolean
	 */
	function getSubscribeUrl() {
		return get_url('object', 'subscribe', array(
			'id' => $this->getId()
		));
	} // getSubscribeUrl

	
	/**
	 * Return unsubscribe URL
	 *
	 * @param void
	 * @return boolean
	 */
	function getUnsubscribeUrl() {
		return get_url('object', 'unsubscribe', array(
			'id' => $this->getId()
		));
	} // getUnsubscribeUrl
	
	
	// ---------------------------------------------------
	//  Archive / Unarchive
	// ---------------------------------------------------
	
	
	function archive($archiveDate = null, $fire_hook = true) {
		if(!isset($archiveDate))
			$archiveDate = DateTimeValueLib::now();
		if ($this->getObject()->columnExists('archived_on')) {
			$this->getObject()->setColumnValue('archived_on', $archiveDate);
		}
		if (logged_user() instanceof Contact && $this->getObject()->columnExists('archived_by_id')) {
			$this->getObject()->setColumnValue('archived_by_id', logged_user()->getId());
		}
		$this->save();
		
		// archive associated member if exists
		$mem = Members::findOneByObjectId($this->getId());
		if ($mem instanceof Member) {
			$mem->archive(logged_user());
		}
		
		if ($fire_hook) {
			$null = null;
			Hook::fire("after_content_object_archive", array('object' => $this), $null);
		}
	}
	
	
	function unarchive($fire_hook = true) {
		if ($this->getObject()->columnExists('archived_on')) {
			$this->getObject()->setColumnValue('archived_on', EMPTY_DATETIME);
		}
		if ($this->getObject()->columnExists('archived_by_id')) {
			$this->getObject()->setColumnValue('archived_by_id', 0);
		}
		$this->save();
		
		// unarchive associated member if exists
		$mem = Members::findOneByObjectId($this->getId());
		if ($mem instanceof Member) {
			$mem->unarchive(logged_user());
		}
		
		if ($fire_hook) {
			$null = null;
			Hook::fire("after_content_object_unarchive", array('object' => $this), $null);
		}
	}
	
	
	function isArchivable() {
		return true;
	}
	
	
	function isArchived() {
		return $this->getObject()->getColumnValue('archived_by_id') != 0;
	}
	
	
	function getArchiveUrl() {
		return get_url('object', 'archive', array ('object_id' => $this->getId ()));
	}
	
	
	function getUnarchiveUrl() {
		return get_url('object', 'unarchive', array ('object_id' => $this->getId ()));
	}
	
	
	// ---------------------------------------------------
	//  Readable Objects
	// ---------------------------------------------------

	protected $is_read_markable = true;
	
	public $is_read = array();
	
	function isReadMarkable(){
		return $this->is_read_markable;
	}

	
	function getIsRead($contact_id) {
		if (!array_key_exists($contact_id,$this->is_read)){
			$this->is_read[$contact_id] = ReadObjects::userHasRead($contact_id,$this);
		}
		return $this->is_read[$contact_id];
	} 
	
	
	/**
	 * remove the entry on readObjects table for this object and the given user
	 * or set is as read.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function setIsRead($contact_id, $isRead) {
		if ($isRead) {
			if ($this->getIsRead($contact_id)) {
				return false; // object is already marked as read
			}
			$now = DateTimeValueLib::now();
			DB::execute("INSERT INTO ".TABLE_PREFIX."read_objects (rel_object_id, contact_id, is_read, created_on) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE is_read=1", $this->getId(), $contact_id, $now);
			$this->is_read[$contact_id] = true;
		} else {
			ReadObjects::delete('rel_object_id = ' . $this->getId() . ' AND contact_id = ' . $contact_id);
		}
		
		return true;
	} 
	
	
	/**
	 * Sets as unread for everyone except logged user
	 * @return null
	 */
	function resetIsRead() {
		$conditions = "`rel_object_id` = " . $this->getId();
		if (logged_user() instanceof Contact) {
			$conditions .= " AND `contact_id` <> " . logged_user()->getId();
		}
		ReadObjects::delete($conditions);
	}
	
	
	// ---------------------------------------------------
	//  Delete
	// ---------------------------------------------------	
	
	
	/**
	 * Delete object and drop content from search table
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		if ($this->isCommentable()) {
			$comments = $this->getComments(true);
			if ($comments && count($comments) > 0) {
				foreach ($comments as $comment) {
					$comment->clearEverything();
					$comment->delete();
				}
			}
		}
		return $this->getObject()->delete() && parent::delete();
	} // delete
	
	
	function clearEverything() {
		if($this->isCommentable()) {
			$this->clearComments();
		} // if
		if($this->isPropertyContainer()){
			$this->clearObjectProperties();
		}
		$this->clearSubscriptions();
		$this->clearReminders();

		if ($this->allowsTimeslots()) {
			$this->clearTimeslots();
		}

		$this->clearMembers();
		$this->clearSharingTable();
		$this->clearReads();
		parent::clearEverything();
	}
	
	
	function clearMembers() {
		return ObjectMembers::delete(array("`object_id` = ?", $this->getId()));
	}

	function clearSharingTable() {
		return SharingTables::delete("`object_id` = ".$this->getId());
		
	}
	

	function clearReads() {
		return ReadObjects::delete(array("`rel_object_id` = ?", $this->getId()));
	}
	
	
	
	
	// ---------------------------------------------------
	//  Trash
	// ---------------------------------------------------
	
	
	function trash($trashDate = null, $fire_hook = true) {
		// dont delete owner company and account owner
		if ($this instanceof Contact && ($this->isOwnerCompany() || $this->isAccountOwner()) ){
			return false;
		}
		if (!$this->getObject() instanceof FengObject) {
			return false;
		}
		if(!$trashDate instanceof DateTimeValue) {
			$trashDate = DateTimeValueLib::now();
		}
		if ($this->getObject()->columnExists('trashed_on')) {
			$this->getObject()->setColumnValue('trashed_on', $trashDate);
		}
		if (logged_user() instanceof Contact && $this->getObject()->columnExists('trashed_by_id')) {
			$this->getObject()->setColumnValue('trashed_by_id', logged_user()->getId());
		}
		$this->getObject()->setMarkTimestamps(false); // Don't modify updated on
		$this->save();
		$this->getObject()->setMarkTimestamps(true);
		
		if ($this->isCommentable()) {
			$comments = $this->getComments();
			if ($comments && count($comments) > 0) {
				foreach ($comments as $comment) $comment->trash();
			}
		}
		
		if ($fire_hook) {
			$null = null;
			Hook::fire("after_content_object_trash", array('object' => $this), $null);
		}
	}
	
	
	function untrash($fire_hook = true) {
		if ($this->getObject()->columnExists('trashed_on')) {
			$this->getObject()->setColumnValue('trashed_on', EMPTY_DATETIME);
		}
		if ($this->getObject()->columnExists('trashed_by_id')) {
			$this->getObject()->setColumnValue('trashed_by_id', 0);
		}
		$this->getObject()->setMarkTimestamps(false); // Don't modify updated on
		$this->save();
		$this->getObject()->setMarkTimestamps(true);
		
		if ($this->isCommentable()) {
			$comments = $this->getComments(true);
			if ($comments && count($comments) > 0) {
				foreach ($comments as $comment) {
					if ($comment->getTrashedById() > 0) $comment->untrash();
				}
			}
		}
		
		if ($fire_hook) {
			$null = null;
			Hook::fire("after_content_object_untrash", array('object' => $this), $null);
		}
	}
	
	
	function isTrashable() {
		return true;
	}
	
	
	function isTrashed() {
		return $this->getObject()->getColumnValue('trashed_by_id') != 0;
	}
	
	
	function getTrashUrl() {
		return $this->getObject()->getTrashUrl();
	}
	
	function getUntrashUrl() {
		return $this->getObject()->getUntrashUrl();
	}
	
	function getViewUrl() {
		return $this->getObject()->getViewUrl();
	}
	
	function getEditUrl() {
		return $this->getObject()->getEditUrl();
	}
	
	function getDeleteUrl() {
		return $this->getObject()->getDeleteUrl();
	}
	
	function getDeletePermanentlyUrl() {
		return $this->getObject()->getDeletePermanentlyUrl();
	}
	
	
	function getIconClass($large = false) {
		$class = 'ico-' . ($large ? "large-" : "") . $this->object->getObjectTypeName();
		if ($this->getObject()->getTrashedById() > 0) $class .= "-trashed";
		else if ($this->getObject()->getArchivedById() > 0) $class .= "-archived";
		
		return $class;
	}
	
	/**
	 * Returns an array with the ids of the members that this object belongs to
	 *
	 */
	function getMemberIds() {
		
		if (is_null($this->memberIds)) {
			 $this->memberIds = ObjectMembers::getMemberIdsByObject($this->getId());
		}
		return $this->memberIds ;
		
		//return ObjectMembers::getMemberIdsByObject($this->getId());
	}
	
	
	/**
	 * Returns an array with the members that this object belongs to
	 *
	 */
	function getMembers() {
		if ( is_null($this->members) ) {
			$this->members =  ObjectMembers::getMembersByObject($this->getId());
		}
		return $this->members ;
	}
	
	function resetCachedVars() {
		$this->memberIds = null;
		$this->members = null;
		$this->subscribers = null;
	}
	
	function getDimensionObjectTypes(){
		return DimensionObjectTypeContents::getDimensionObjectTypesforObject($this->getObjectTypeId());
	}
	
	function addToMembers($members_array, $remove_old_comment_members = false, $is_multiple = false){
		ObjectMembers::addObjectToMembers($this->getId(),$members_array);
		/*if (Plugins::instance()->isActivePlugin('mail') && $this instanceof MailContent) {
			$inline_images = ProjectFiles::findAll(array("conditions" => "mail_id = ".$this->getId()));
			foreach ($inline_images as $inline_img) {
				$inline_img->addToMembers($members_array);
				$inline_img->addToSharingTable();
			}
		}*/
		
		$hook_return = null;
	    Hook::fire("after_classify_object", array('members'=> $members_array, 'object'=>$this, 'is_multiple_classify'=> $is_multiple), $hook_return);
		

		if ($this->isCommentable()) {
			$comments = $this->getComments(true);
			foreach ($comments as $comment) {
				if ($remove_old_comment_members) {
					ObjectMembers::instance()->delete("object_id = ".$comment->getId());
				}
				$comment->addToMembers($members_array);
				$comment->addToSharingTable();
			}
		}
		
		// clear this object's cached variables
		$this->resetCachedVars();
	}
	
	/**
	 * 
	 * 
	 */
	function addToSharingTable() {		
		$oid = $this->getId();
		ContentDataObjects::addObjToSharingTable($oid);
	}
	
	
	
	function removeFromMembers(Contact $user, $members_array){
		$member_ids_to_remove = null;
		if (is_array($members_array) && count($members_array) > 0) {
			$member_ids_to_remove = array();
			foreach ($members_array as $m) $member_ids_to_remove[] = $m->getId();
		}
		return ObjectMembers::removeObjectFromMembers($this, $user, $members_array, $member_ids_to_remove);
	}
	
	function removeFromAllMembers(Contact $user, $members_array){
		return ObjectMembers::removeObjectFromMembers($this, $user, $members_array);
	}
	
	
	function getAllowedMembersToAdd(Contact $user, $enteredMembers, &$not_valid_members=array()){
		
		$validMembers = array();
		foreach ($enteredMembers as $m) {
			if ($this->canAddToMember($user, $m, $enteredMembers)) {
				$validMembers[] = $m;
			} else {
				$not_valid_members[] = $m;
			}
		}
		
		return $validMembers;
	}
	
	/**
	* Return object URL
	*
	* @access public
	* @param void
	* @return string
	*/
	function getObjectUrl() {
		return $this->getViewUrl();
	}
	
	
	function modifyMemberValidations($member) {
		// Override this in the concrete objects
	}
	


	// ---------------------------------------------------
	//  Timeslots
	// ---------------------------------------------------
	
	
	function addTimeslot(Contact $user){
		if ($this->hasOpenTimeslots($user))
			throw new Error("Cannot add timeslot: user already has an open timeslot");



        if (user_config_option('stop_running_timeslots')) {
            $allOpenTimeslot = Timeslots::getAllOpenTimeslotByObjectByUser(logged_user());
            if (!empty($allOpenTimeslot)) {
				$time_c = new TimeslotController();
                foreach ($allOpenTimeslot as $time) {
                	try{
                		$time_c->internal_close($time);
                	}catch(Exception $ex){
                		Logger::log_r("Error closing running timeslot: ".$ex->getMessage());
                	}
                }
            }
        }

        $timeslot = new Timeslot();

        $dt = DateTimeValueLib::now();
        $timeslot->setStartTime($dt);
        $timeslot->setContactId($user->getId());
        $timeslot->setRelObjectId($this->getObjectId());

		$timeslot->save();
		
		$object_controller = new ObjectController();
		$object_controller->add_to_members($timeslot, $this->getMemberIds());
		
		return $timeslot;
	}

	function hasOpenTimeslots($user = null){
		$userCondition = '';
		if ($user)
			$userCondition = ' AND `contact_id` = '. $user->getId();

		return Timeslots::findOne(array(
          'conditions' => array('`rel_object_id` = ? AND end_time = \'' . EMPTY_DATETIME . '\''  . $userCondition, $this->getObjectId()))
		) instanceof Timeslot;
	}

	function closeTimeslots(Contact $user, $description = ''){
		$timeslots = Timeslots::findAll(array('conditions' => 'contact_id = ' . $user->getId() . ' AND rel_object_id = ' . $this->getObjectId() . ' AND end_time = "' . EMPTY_DATETIME . '"'));

		foreach($timeslots as $timeslot){
			$timeslot->close($description);
			Hook::fire('round_minutes_to_fifteen', array('timeslot' => $timeslot), $ret);
			$timeslot->save();
		}
                
                return $timeslot;
	}

	function pauseTimeslots(Contact $user){
		$timeslots = Timeslots::findAll(array('conditions' => 'contact_id = ' . $user->getId() . ' AND rel_object_id = ' . $this->getObjectId() . ' AND end_time = "' . EMPTY_DATETIME . '" AND paused_on = "' . EMPTY_DATETIME . '"'));

		if ($timeslots) {
			foreach($timeslots as $timeslot){
				$timeslot->pause();
				$timeslot->save();
			}
		}
	}

	function resumeTimeslots(Contact $user){
		$timeslots = Timeslots::findAll(array('conditions' => 'contact_id = ' . $user->getId() . ' AND rel_object_id = ' . $this->getObjectId() . ' AND end_time = "' . EMPTY_DATETIME . '" AND paused_on != "' . EMPTY_DATETIME . '"'));

		if ($timeslots)
		foreach($timeslots as $timeslot){
			$timeslot->resume();
			$timeslot->save();
		}
	}
	
	function getOpenTimeslots(){
		return Timeslots::instance()->getOpenTimeslotsByObject($this->getId());
	}

	/**
	 * Returns true if users can assign timeslots on this object
	 *
	 * @param void
	 * @return boolean
	 */
	function allowsTimeslots() {
		return (boolean) $this->allow_timeslots;
	}

	/**
	 * Attach timeslot to this object
	 *
	 * @param Timeslot $timeslot
	 * @return Timeslot
	 */
	function attachTimeslot(Timeslot $timeslot) {
		$object_id = $this->getObjectId();

		if ($object_id == $timeslot->getObjectId()) {
			return true;
		}

		$timeslot->setObjectId($object_id);

		$timeslot->save();
		return $timeslot;
	}

	/**
	 * Return all timeslots
	 *
	 * @param void
	 * @return boolean
	 */
	function getTimeslots() {
		if(!isset($this->timeslots) || is_null($this->timeslots)) {
			$this->timeslots = Timeslots::getTimeslotsByObject($this);
		}
		return $this->timeslots;
	} // getTimeslots

	/**
	 * This function will return number of timeslots
	 *
	 * @param void
	 * @return integer
	 */
	function countTimeslots() {
		if(is_null($this->timeslots_count)) {
			$this->timeslots_count = Timeslots::countTimeslotsByObject($this);
		}
		return $this->timeslots_count;
	} // countTimeslots

	/**
	 * Return # of specific timeslot
	 *
	 * @param Timeslot $timeslot
	 * @return integer
	 */
	function getTimeslotNum(Timeslot $timeslot) {
		$timeslots = $this->getTimeslots();
		if(is_array($timeslots)) {
			$counter = 0;
			foreach($timeslots as $object_timeslot) {
				$counter++;
				if($timeslot->getId() == $object_timeslot->getId()) return $counter;
			}
		}
		return 0;
	} // getTimeslotNum

	/**
	 * Returns true if this function has associated comments
	 *
	 * @param void
	 * @return boolean
	 */
	function hasTimeslots() {
		return (boolean) $this->countTimeslots();
	}

	/**
	 * Clear object timeslots
	 *
	 * @param void
	 * @return boolean
	 */
	function clearTimeslots() {
		return Timeslots::dropTimeslotsByObject($this);
	}

	/**
	 * This event is triggered when we create a new timeslot
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onAddTimeslot(Timeslot $timeslot, $params = array()) {
		if ($this->allowsTimeslots()) {
			$total_worked_time = $this->calculateTotalWorkedTime();
			$twt_column = array_var($params, 'total_worked_time_column');
			$this->saveTotalWorkedTime($total_worked_time, $twt_column);
			if ($this instanceof ProjectTask) $this->calculatePercentComplete();
		}
		return true;
	}

	/**
	 * This event is trigered when Timeslot that belongs to this object is updated
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onEditTimeslot(Timeslot $timeslot, $params = array()) {
		if ($this->allowsTimeslots()) {
			$total_worked_time = $this->calculateTotalWorkedTime();
			$twt_column = array_var($params, 'total_worked_time_column');
			$this->saveTotalWorkedTime($total_worked_time, $twt_column);
			if ($this instanceof ProjectTask) $this->calculatePercentComplete();
		}
		return true;
	}

	/**
	 * This event is triggered when timeslot that belongs to this object is deleted
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onDeleteTimeslot(Timeslot $timeslot, $params = array()) {
		if ($this->allowsTimeslots()) {
			$total_worked_time = $this->calculateTotalWorkedTime();
			$twt_column = array_var($params, 'total_worked_time_column');
			$this->saveTotalWorkedTime($total_worked_time, $twt_column);
			if ($this instanceof ProjectTask) $this->calculatePercentComplete();
		}
		return true;
	}
	
	/**
	 * Returns the total worked time in minutes for this object
	 *
	 * @return integer
	 */
	function calculateTotalWorkedTime() {
		if ($this->allowsTimeslots()) {
			
			$sql = "SELECT (SUM(GREATEST(TIMESTAMPDIFF(MINUTE,start_time,end_time),0)) - SUM(subtract/60)) as total_minutes 
					FROM ".TABLE_PREFIX."timeslots ts 
					INNER JOIN ".TABLE_PREFIX."objects o ON o.id=ts.object_id 
					WHERE ts.rel_object_id=".$this->getId()." AND o.trashed_by_id=0";
			
			$row = DB::executeOne($sql);
			return array_var($row, 'total_minutes');
			
		} else {
			return 0;
		}
	}
	
	/**
	 * Saves the $total_worked_time in the column '$total_worked_time_column'
	 * 
	 * @param integer $total_worked_time
	 * @param string $total_worked_time_column
	 */
	function saveTotalWorkedTime($total_worked_time=0, $total_worked_time_column='') {
		$total_worked_time_column = trim($total_worked_time_column);
		if ($total_worked_time_column && $this->columnExists($total_worked_time_column)) {
			$this->setColumnValue($total_worked_time_column, $total_worked_time);
			$this->save();
		}
	}

	/**
	 * This function returns the total amount of minutes worked in this task
	 *
	 * @return integer
	 */
	//
	function getTotalMinutes(){
		$totalSeconds = Timeslots::getTotalSecondsWorkedOnObject($this->getId());
		$totalMinutes = $totalSeconds / 60;
		return $totalMinutes;
	}

	/**
	 * This function returns the total amount of seconds worked in this task
	 *
	 * @return integer
	 */

	function getTotalSeconds(){
		$totalSeconds = Timeslots::getTotalSecondsWorkedOnObject($this->getId());
		return $totalSeconds;
	}
	

	function getSummaryText () {
		$col = $this->summary_field;
		return $this->getColumnValue($col);
	}

	
	function getSummary($options = null ){
		$text = html_to_text($this->getSummaryText());
		$size = array_var($options, 'size');
		$near = array_var($options, 'near');		

		if (is_array($options)) {
			if ($near){
				$position = strpos($text,$near);
				$spacesBefore = min(10, $position); // TODO: buscar la ultima palabra antes
				if ($size && strlen($text) > $size ){
					return utf8_safe(substr($text , $position - $spacesBefore, $size))."...";
					
				}else{
					return $text ;
				}
			}
		}
	}
	
	
	function getMembersToDisplayPath($member_ids = null, $show_all_members = false, $show_active_context_members = true) {
		$members_info = array();
		
		if (is_null($member_ids)) {
			$member_ids = ObjectMembers::getMemberIdsByObject($this->getId());
		}
		$members = $this->manager()->getCachedMembersInfo($member_ids);

		$dimension_options = array();
		$member_count = array();
		
		$active_context_ids = active_context_members(false);

		$to_display = user_config_option('breadcrumb_member_count');
		
		if(count($members) > 0){
			foreach ($members as $mem) {
				$dimension = Dimensions::getDimensionById($mem['dimension_id']);
				
				$hook_return = null;
				Hook::fire("hidden_breadcrumbs", array('ot_id' => $this->getObjectTypeId(), 'dim_id' => $mem['dimension_id']), $hook_return);
				if (!is_null($hook_return) && array_var($hook_return, 'hidden')) {
					continue;
				}
				
				if (intval($dimension->getOptionValue('showInPaths')) && $dimension->getIsManageable()) {
					if (!isset($members_info[$mem['dimension_id']])) $members_info[$mem['dimension_id']] = array();
					
					$active_context_condition = true;
					if(!$show_active_context_members){
						$active_context_condition = !in_array($mem['id'], $active_context_ids);
					}
					
					if (!$show_all_members && count($members_info[$mem['dimension_id']]) < $to_display && $active_context_condition) {
						$members_info[$mem['dimension_id']][$mem['id']] = array(
							'ot' => $mem['object_type_id'],
							'c' => Members::getMemberById($mem['id'])->getMemberColor(),
							'name' => $mem['name'],
						);
					}
					if (!isset($member_count[$mem['dimension_id']])) $member_count[$mem['dimension_id']] = 1;
					else $member_count[$mem['dimension_id']]++;
				}
			}
		}
		
		foreach ($member_count as $did => $cant) {
			$members_info[$did]['total'] = $cant;
		}
		
		return $members_info;
	}
	
	function getMembersIdsToDisplayPath($show_hidden_breadcrumbs = false, $params = array()) {
		$member_ids = array();
		$dimensions_ids = array();
		$selected_members_ids = $this->getMemberIds();
		$use_restrictions = array_var($params, 'use_restrictions', false);
		$allowed_dimension_ids = array_var($params, 'allowed_dimensions', array());
		$exclude_member_ids = array_var($params, 'exclude_member_ids', array());
		if(count($selected_members_ids) > 0){
			$selected_members_cond = ' AND id IN ('.implode(',',$selected_members_ids).')';
			
			//get all dimensions ids to showInPaths
			$dimensions = Dimensions::getAllowedDimensions($this->getObjectTypeId());
			foreach ($dimensions as $dimension) {
				if($use_restrictions && !in_array($dimension['dimension_id'], $allowed_dimension_ids)) continue;
				$dim = Dimensions::getDimensionById($dimension['dimension_id']);
				if (intval($dim->getOptionValue('showInPaths')) && $dim->getIsManageable()) {
					
					if (!$show_hidden_breadcrumbs) {
						$hook_return = null;
						Hook::fire("hidden_breadcrumbs", array('ot_id' => $this->getObjectTypeId(), 'dim_id' => $dimension['dimension_id']), $hook_return);
						if (!is_null($hook_return) && array_var($hook_return, 'hidden')) {
							continue;
						}
					}
					
					$dimensions_ids[] = $dimension['dimension_id'];
					$to_display = null;
					if ($this instanceof Contact && $this->isUser()) {
						$to_display = user_config_option('breadcrumb_member_count');
					}
					$extra_cond = " AND m.dimension_id = ".$dimension['dimension_id'];
					
					Hook::fire("breadcrumbs_extra_conditions", array('dim'=>$dim), $extra_cond);
					
					$dim_members = ObjectMembers::getMembersIdsByObjectAndExtraCond($this->getId(), $extra_cond, $to_display, false);
					if (is_array($dim_members)) {
						foreach ($dim_members as $mem) {
							if($use_restrictions && in_array($mem['member_id'], $exclude_member_ids)) continue;
							$ot_id = $mem['object_type_id'];
							if ($mem['is_optimization'] == '1') {
								
								if (!isset($member_ids[$dimension['dimension_id']]['opt'])) $member_ids[$dimension['dimension_id']]['opt'] = array();
								if (!isset($member_ids[$dimension['dimension_id']]['opt'][$ot_id])) $member_ids[$dimension['dimension_id']]['opt'][$ot_id] = array();
								$member_ids[$dimension['dimension_id']]['opt'][$ot_id][] = $mem['member_id'];
								
							} else {
								if (!isset($member_ids[$dimension['dimension_id']][$ot_id])) $member_ids[$dimension['dimension_id']][$ot_id] = array();
								$member_ids[$dimension['dimension_id']][$ot_id][] = $mem['member_id'];
							}
						}
						
						if (!user_config_option('show_associated_dims_in_breadcrumbs')) {
							// check if this dimension is associated to any main dimensions
							$main_dims_of_this_dim = array_var($_SESSION['main_dims_of_this_dim'], $dimension['dimension_id']);
							if (is_null($main_dims_of_this_dim)) { 
								$main_dims_of_this_dim = DimensionMemberAssociations::instance()->getAssociatedDimensions($dimension['dimension_id']);
								if (!isset($_SESSION['main_dims_of_this_dim'])) $_SESSION['main_dims_of_this_dim'] = array();
								$_SESSION['main_dims_of_this_dim'][$dimension['dimension_id']] = $main_dims_of_this_dim;
							}
							// don't show associated dimensions in content objects general breadcrumb
							if (count($main_dims_of_this_dim) > 0) {
								$member_ids[$dimension['dimension_id']]['is_assoc_dim'] = '1';
							}
						}
					}
				}
			}
		}
		
		return $member_ids;
	}
	
	
	function getObjectColor($default = null) {
		$color = is_null($default) || !is_numeric($default) ? 1 : $default;
		
		$members = $this->getMembers();
		foreach ($members as $member) {
			if ($member->getDimension()->getIsManageable()) {
				$color = $member->getColor();
				if ($color > 0) break;
			}
		}
	
		Hook::fire('override_object_color', $this, $color);
		
		return $color;
	}
	
	function getObjectColors($default = null) {
		$colors = array();
	
		$members = $this->getMembers();
		foreach ($members as $member) {
			if ($member->getDimension()->getIsManageable()) {
				$color = $member->getColor() > 0 ? $member->getColor() : (is_numeric($default) ? $default : 1);
				$colors[] = $color;
			}
		}
		
		if (count($colors) == 0) {
			$colors[] = is_null($default) || !is_numeric($default) ? 1 : $default;
		}
	
		return $colors;
	}
	
	function canAddTimeslot($user) {
		return can_add_timeslots($user, $this->getMembers());
	}
	
	
	function getAddEditFormTitle() {
		$ot = ObjectTypes::findById($this->manager()->getObjectTypeId());
		if ($ot instanceof ObjectType) {
			$otname = $ot->getName();
			$title = $this->isNew() ? lang("new $otname") : lang("edit $otname");
		} else {
			$title = $this->isNew() ? lang("new object") : lang("edit object");
		}
		
		Hook::fire('override_add_edit_form_title', array('object' => $this, 'ot' => $ot), $title);
		
		return $title;
		
	}
	
	function getSubmitButtonFormTitle() {
		$ot = ObjectTypes::findById($this->manager()->getObjectTypeId());
		if ($ot instanceof ObjectType) {
			$otname = $ot->getName();
			$title = $this->isNew() ? lang("add $otname") : lang("save changes");
		} else {
			$title = $this->isNew() ? lang("add object") : lang("save changes");
		}
		
		Hook::fire('override_submit_button_form_title', array('object' => $this, 'ot' => $ot), $title);
		
		return $title;
	}
	
	function getObjectTypeNameLang() {
		$ot = ObjectTypes::findById($this->manager()->getObjectTypeId());
		if ($ot instanceof ObjectType) {
			$otname = lang($ot->getName());
		} else {
			$otname = lang('object');
		}
		
		Hook::fire('override_get_object_type_name', $this, $otname);
		
		return $otname;
	}
	

	function addToRelatedMembers($members, $from_form = false, $remove_previous_associated_members = false){
		$related_member_ids = array();
		
		foreach ($members as $member) {
			// get normal associations
			$associations = DimensionMemberAssociations::getAssociatations($member->getDimensionId(), $member->getObjectTypeId());
			
			
			// get transitive associations - associations of parent member types
			$transitive_associations = array();
			$parent_mem_type_ids = DimensionObjectTypeHierarchies::getAllParentObjectTypeIds($member->getDimensionId(), $member->getObjectTypeId());
			foreach ($parent_mem_type_ids as $pmem_type_id) {
				$more_assocs = DimensionMemberAssociations::getAssociatations($member->getDimensionId(), $pmem_type_id);
				foreach ($more_assocs as $a) {
					$aconfig = $a->getConfig();
					if (array_var($aconfig, 'autoclassify_in_property_member_by_child')) {
						$transitive_associations[] = $a;
					}
				}
			}
			
			$associations = array_merge($associations, $transitive_associations);
			
			
			foreach ($associations as $a) {/* @var $a DimensionMemberAssociation */
				$aconfig = $a->getConfig();
				$classify_it = false;
				$include_parents_in_query = false;
				
				// first of all: check if this object is already classified in a member of the associated dimension
				// if so, then ignore this association, else continue with the process
				$is_already_classified_in_assoc_dim = false;
				// check in the members sent by the form
				foreach ($members as $m) {
					if ($m->getDimensionId() == $a->getAssociatedDimensionMemberAssociationId() && $m->getObjectTypeId() == $a->getAssociatedObjectType()) {
						$is_already_classified_in_assoc_dim = true;
						break;
					}
				}
				if (!$is_already_classified_in_assoc_dim) {
					// check in the members of the object in the database
					$object_members = $this->getMembers();
					foreach ($object_members as $m) {
						if ($m->getDimensionId() == $a->getAssociatedDimensionMemberAssociationId() && $m->getObjectTypeId() == $a->getAssociatedObjectType()) {
							if ($remove_previous_associated_members) {
								ObjectMembers::removeObjectFromMembers($this, logged_user(), null, array($m->getId()));
							} else {
								$is_already_classified_in_assoc_dim = true;
							}
							break;
						}
					}
				}
				if ($is_already_classified_in_assoc_dim) {
					$classify_it = false;
					continue;
				}
				// --
				
				// classify only if 'autoclassify_in_property_member' option is set for this association
				if (array_var($aconfig, 'autoclassify_in_property_member')) {
					
					// if the request does not come from object form (e.g.: d&d, it hasn't member selectors)
					if (!$from_form) {
						$classify_it = true;
						
					} else {
						// direct association
						if ($member->getObjectTypeId() == $a->getObjectTypeId()) {
							
							// force the add to the related member in background if the member is not allowed to be removed  
							if (!array_var($aconfig, 'allow_remove_from_property_member')) {
								$classify_it = true;
								
							} else {
								// to check if this dimension selector is hidden in forms or not
								$hookparams = array('dim_id' => $a->getAssociatedDimensionMemberAssociationId(), 'ot_id' => $this->getObjectTypeId());
								Hook::fire('more_autoclassify_in_related_checks', $hookparams, $classify_it);
								
							}
							
						} else {
							// transitive association
							$classify_it = true;
							$include_parents_in_query = true;
						}
						
						// @TODO: ver que pasa cuando se hace el submit antes de que se terminen de pre-cargar las dim relacionadas
					}
					
				}
				
				if ($classify_it) {
					$member_ids = array($member->getId());
					
					if ($include_parents_in_query) {
						$pmembers = $member->getAllParentMembersInHierarchy(false, false);
						foreach ($pmembers as $pmem) {
							$member_ids[] = $pmem->getId();
						}
					}
					
					$rel_mem_ids = array_flat(DB::executeAll("SELECT property_member_id FROM ".TABLE_PREFIX."member_property_members 
							WHERE association_id=".$a->getId()." AND member_id IN (".implode(',', $member_ids).")"));
					
					$related_member_ids = array_merge($related_member_ids, $rel_mem_ids);
				}
			}
		}

		$related_member_ids = array_unique(array_filter($related_member_ids));
		if (count($related_member_ids) > 0) {
			$related_members = Members::findAll(array("conditions" => "id IN (".implode(',', $related_member_ids).")"));
			if (count($related_members) > 0) {
				ObjectMembers::addObjectToMembers($this->getId(), $related_members);
			}
		}
		
	}
	
	
	
	function getExternalColumnValue($column) {
		return "";
	}
	
	
	function getParentObjectId() {
		return null;
	}
	
	function getChildObjectIds() {
		return null;
	}
	

	function getObjectData() {
		$info = array();
		
		$cps_by_id = array();
		$cps = CustomProperties::getAllCustomPropertiesByObjectType($this->getObjectTypeId());
		foreach ($cps as $cp) {
			$cps_by_id[$cp->getId()] = $cp;
		}
		
		$definition = $this->manager()->getDefinition();
		
		
		foreach ($definition as $property_id => $property_info) {
			if (isset($info[$property_id])) continue;
			
			if (str_starts_with($property_id, "cp_")) {
				// custom property
				$cp_id = str_replace("cp_", "", $property_id);
				$cp = array_var($cps_by_id, $cp_id);
				
				if ($cp->getIsMultipleValues()) {
					$cp_vals = CustomPropertyValues::getCustomPropertyValues($this->getId(), $cp->getId());
					$cp_vals_info = array();
					foreach ($cp_vals as $cp_val) {
						$cp_vals_info[] = get_custom_property_value_for_listing($cp, $this, array($cp_val), true);
					}
					$info[$property_id] = $cp_vals_info;
				
				} else {
				
					$cp_val = CustomPropertyValues::getCustomPropertyValue($this->getId(), $cp->getId());
					$info[$property_id] = get_custom_property_value_for_listing($cp, $this, array($cp_val), true);
				}
				
			} else {
				// object property
				$info[$property_id] = $this->getColumnValue($property_id);
				if ($info[$property_id] instanceof DateTimeValue) {
					if ($this->getTimezoneId() > 0) {
						$info[$property_id] = format_datetime($info[$property_id], DATE_MYSQL);
					} else {
						$info[$property_id] = date(DATE_MYSQL,$info[$property_id]->getTimestamp());
					}
				}
			}
		}
		
		return $info;
	}
	
	
	function getAdditionalCustomPropertyValues($cp) {
		return array();
	}
	
	function getAdditionalCustomPropertyAssociatedObject($cp) {
		return null;
	}
	
	function getFixedColumnValue($column_name, $raw_data=false) {
		return $this->getColumnValue($column_name);
	}
	
}
