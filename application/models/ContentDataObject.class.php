<?php

/**
 * Abstract class that implements methods that share all content objects
 *
 *
 * @version 1.0
 * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
 */
abstract class ContentDataObject extends ApplicationDataObject {
	
	/**
	 * @var Object
	 */
	var $object;
	
	var $memberIds = null ;
	
	var $members = null ;
	
	/**
	 * 
	 * @var string
	 */
	var $summary_field = "name";
	
	function __construct() {
		$this->object = new Object();
		$this->object->setObjectTypeId($this->manager()->getObjectTypeId());
		if ($this->is_searchable) {
			$this->searchable_columns[] = 'object_id';
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
	 * 
	 * Enter description here ...
	 */
	function getObject() {
		return $this->object;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param Object $object
	 */
	function setObject(Object $object) {
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
		return $this->object->getObjectTypeId();
	} // getObjectTypeId()
	
	
	function getObjectTypeName(){
		return $this->object->getObjectTypeName();
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
		return $this->object->getName();
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
		
		return $copy ;
	}
	
	function copy_custom_properties($object_from) {
		if (!$object_from instanceof ContentDataObject) return;
		
		$cp_values = CustomPropertyValues::findAll(array('conditions' => 'object_id = '.$object_from->getId()));
		foreach ($cp_values as $cp_value) {
			$new_cp_value = new CustomPropertyValue();
			$new_cp_value->setObjectId($this->getId());
			$new_cp_value->setCustomPropertyId($cp_value->getCustomPropertyId());
			$new_cp_value->setValue($cp_value->getValue());
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
	 * This function will return number of all comments
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
	function getCommentNum(Comment $comment) {
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
	function subscribeUser(Contact $user) {
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
	function unsubscribeUser(Contact $user) {
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
	
	
	function archive($archiveDate = null) {
		if(!isset($archiveDate))
			$archiveDate = DateTimeValueLib::now();
		if ($this->getObject()->columnExists('archived_on')) {
			$this->getObject()->setColumnValue('archived_on', $archiveDate);
		}
		if (logged_user() instanceof Contact && $this->getObject()->columnExists('archived_by_id')) {
			$this->getObject()->setColumnValue('archived_by_id', logged_user()->getId());
		}
		$this->save();
	}
	
	
	function unarchive() {
		if ($this->getObject()->columnExists('archived_on')) {
			$this->getObject()->setColumnValue('archived_on', EMPTY_DATETIME);
		}
		if ($this->getObject()->columnExists('archived_by_id')) {
			$this->getObject()->setColumnValue('archived_by_id', 0);
		}
		$this->save();
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
	 * @return unknown_type
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
	
	
	function trash($trashDate = null) {
		// dont delete owner company and account owner
		if ($this instanceof Contact && ($this->isOwnerCompany() || $this->isAccountOwner()) ){
			return false;
		}
		if(!isset($trashDate))
			$trashDate = DateTimeValueLib::now();
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
	}
	
	
	function untrash() {
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
	
	
	function getDimensionObjectTypes(){
		return DimensionObjectTypeContents::getDimensionObjectTypesforObject($this->getObjectTypeId());
	}
	
	function addToMembers($members_array, $remove_old_comment_members = false){
		ObjectMembers::addObjectToMembers($this->getId(),$members_array);
		/*if (Plugins::instance()->isActivePlugin('mail') && $this instanceof MailContent) {
			$inline_images = ProjectFiles::findAll(array("conditions" => "mail_id = ".$this->getId()));
			foreach ($inline_images as $inline_img) {
				$inline_img->addToMembers($members_array);
				$inline_img->addToSharingTable();
			}
		}*/
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
	}
	
	/**
	 * 
	 * 
	 */
	function addToSharingTable() {		
		$oid = $this->getId();
		$tid = $this->getObjectTypeId() ;
		$obj_mem_ids = $this->getMemberIds();
		ContentDataObjects::addObjToSharingTable($oid,$tid, count($obj_mem_ids));
	}
	
	
	
	function removeFromMembers(Contact $user, $members_array){
		return ObjectMembers::removeObjectFromMembers($this,$user, $members_array);
	}
	
	
	function getAllowedMembersToAdd(Contact $user, $enteredMembers){
		
		$validMembers = array();
		foreach ($enteredMembers as $m) {
			if ($this->canAddToMember($user, $m, $enteredMembers)) {
				$validMembers[] = $m;
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
	function onAddTimeslot(Timeslot $timeslot) {
		return true;
	}

	/**
	 * This event is trigered when Timeslot that belongs to this object is updated
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onEditTimeslot(Timeslot $timeslot) {
		return true;
	}

	/**
	 * This event is triggered when timeslot that belongs to this object is deleted
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onDeleteTimeslot(Timeslot $timeslot) {
		return true;
	}

	/**
	 * This function returns the total amount of minutes worked in this task
	 *
	 * @return integer
	 */
	//
	function getTotalMinutes(){
		$timeslots = $this->getTimeslots();
		$totalMinutes = 0;
		if (is_array($timeslots)){
			foreach ($timeslots as $ts){
				if (!$ts->isOpen())
				$totalMinutes += $ts->getMinutes();
			}
		}
		return $totalMinutes;
	}

	/**
	 * This function returns the total amount of seconds worked in this task
	 *
	 * @return integer
	 */

	function getTotalSeconds(){
		$timeslots = $this->getTimeslots();
		$totalMinutes = 0;
		if (is_array($timeslots)){
			foreach ($timeslots as $ts){
				if (!$ts->isOpen())
				$totalMinutes += $ts->getSeconds();
			}
		}
		return $totalMinutes;
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
				$options = Dimensions::getDimensionById($mem['dimension_id'])->getOptions(true);
				if (isset($options->showInPaths) && $options->showInPaths) {
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
	
	function getMembersIdsToDisplayPath() {
		$member_ids = array();
		$dimensions_ids = array();
		$selected_members_ids = $this->getMemberIds();
		if(count($selected_members_ids) > 0){
			$selected_members_cond = ' AND id IN ('.implode(',',$selected_members_ids).')';
			
			//get all dimensions ids to showInPaths
			$dimensions = Dimensions::getAllowedDimensions($this->getObjectTypeId());
			foreach ($dimensions as $dimension) {
				$options = json_decode ( $dimension['dimension_options'] );
				if (isset($options->showInPaths) && $options->showInPaths) {
					$dimensions_ids[] = $dimension['dimension_id'];
					$to_display = user_config_option('breadcrumb_member_count');
					$extra_cond = " AND m.dimension_id = ".$dimension['dimension_id'];
					$extra_cond .= $selected_members_cond;
					$dim_members = ObjectMembers::getMembersIdsByObjectAndExtraCond($this->getId(), $extra_cond, $to_display, false);
					foreach ($dim_members as $mem) {
						$member_ids[$dimension['dimension_id']][$mem] = $mem;
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
	
}
