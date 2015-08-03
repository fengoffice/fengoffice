<?php

/**
 * Comment class
 * Generated on Wed, 19 Jul 2006 22:17:32 +0200 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Comment extends BaseComment {

	/**
	 * Comment # for specific object
	 *
	 * @var integer
	 */
	protected $comment_num = null;

	/**
	 * We can attach files to comments
	 *
	 * @var array
	 */
	protected $is_file_container = true;
	
	/**
	 * Return object connected with this action
	 *
	 * @access public
	 * @param void
	 * @return ContentDataObject
	 */
	function getRelObject() {
		return Objects::findObject($this->getRelObjectId());
	} // getObject

	/**
	 * Return the first $len - 3 characters of the comment's text followed by "..."
	 *
	 * @param unknown_type $len
	 */
	function getPreviewText($len = 30) {
		if ($len <= 3) return "...";
		$text = $this->getText();
		if (strlen_utf($text) > $len) {
			return substr_utf($text, 0, $len - 3) . "...";
		} else {
			return $text;
		}
	}
	
	/**
	 * Return comment #
	 *
	 * @param void
	 * @return integer
	 */
	function getCommentNum() {
		if(is_null($this->comment_num)) {
			$object = $this->getRelObject();
			$this->comment_num = $object instanceof ContentDataObject ? $object->getCommentNum($this) : 0;
		} // if
		return $this->comment_num;
	} // getCommentNum

	// ---------------------------------------------------
	//  URLs
	// ---------------------------------------------------

	/**
	 * Return tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		$object = $this->getRelObject();
		return $object instanceof ContentDataObject ? $object->getObjectUrl() : '';
	} // getViewUrl

	/**
	 * Return add comment URL for specific object
	 *
	 * @param ContentDataObject $object
	 * @return string
	 */
	static function getAddUrl(ContentDataObject $object) {
		return get_url('comment', 'add', array('object_id' => $object->getObjectId()));
	} // getAddUrl

	/**
	 * Return edit URL
	 *
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return get_url('comment', 'edit', array('id' => $this->getId()));
	} // getEditUrl

	/**
	 * Return delete URL
	 *
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('comment', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canView(Contact $user) {
		return can_read($user, $this->getRelObject()->getMembers(), $this->getRelObject()->getObjectTypeId());
	} // canView

	/**
	 * @param Contact $user
	 * @param Member $member
	 * @return boolean
	 */
	function canAddToMember(Contact $user, Member $member, $context_members) {
		$rel_object = $this->getRelObject();
		if (!$rel_object instanceof ContentDataObject) {
			return false;
		}
		return can_add_to_member($user, $member, $context_members, $rel_object->getObjectTypeId());
	} // canAdd

	
	function canAdd(Contact $user, $context, &$notAllowedMember = ''){
		$object = $this->getRelObject();
		if (!$object instanceof ContentDataObject) {
			return false;
		}
		return can_add($user, $context, $object->getObjectTypeId(), $notAllowedMember );
	}
	
	
	/**
	 * @param Contact $user
	 * @return boolean
	 */
	function canEdit(Contact $user) {
		$userId = $user->getId();
		$creatorId = $this->getCreatedById();
		$object = $this->getRelObject();
		if (!$object instanceof ContentDataObject) {
			return false;
		}
		return can_write($user, $object->getMembers(), $object->getObjectTypeId()) && ($user->isAdministrator() || $userId == $creatorId);
	} // canEdit

	/**
	 * Empty implementation of static method. Update tag permissions are check by the taggable
	 * object, not tag itself
	 *
	 * @param Contact $user
	 * @return boolean
	 */
	function canDelete(Contact $user) {
		$object = $this->getRelObject();
		if (!$object instanceof ContentDataObject) {
			return false;
		}
		return can_delete($user, $object->getMembers(), $object->getObjectTypeId());
	} // canDelete

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Validate before save
	 *
	 * @param array $error
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('text')) {
			$errors[] = lang('comment text required');
		} // if
	} // validate

	/**
	 * Save the object
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		$is_new = $this->isNew();
		$saved = parent::save();
		if($saved) {
			$object = $this->getRelObject();
			
			if($object instanceof ContentDataObject) {
				if($is_new) {
					$object->onAddComment($this);
				} else {
					$object->onEditComment($this);
				}
				$object->save();
			}
		}
		return $saved;
	}

	/**
	 * Delete comment
	 *
	 * @param void
	 * @return null
	 */
	function delete() {
		$deleted = parent::delete();
		if($deleted) {
			$object = $this->getRelObject();
			if($object instanceof ContentDataObject) {
				$object->onDeleteComment($this);
			} // if
		} // if
		return $deleted;
	} // delete

	// ---------------------------------------------------
	//  ApplicationDataObject implementation
	// ---------------------------------------------------

	/**
	 * Return object name
	 *
	 * @param void
	 * @return string
	 */
	function getObjectName() {
		$object = $this->getRelObject();
		//return $object instanceof ContentDataObject ? lang('comment on object', substr_utf($this->getText(), 0, 50) . '...', $object->getObjectName()) : $this->getObjectTypeName();
		return $object instanceof ContentDataObject ? $object->getObjectName() : $this->getObjectTypeName();
	} // getObjectName

	/**
	 * Return view tag URL
	 *
	 * @param void
	 * @return string
	 */
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl
	

} // Comment

?>