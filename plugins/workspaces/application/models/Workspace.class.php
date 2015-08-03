<?php

/**
 * Workspace class
 * Generated on Sat, 04 Mar 2006 12:21:44 +0100 by DataObject generation tool
 *
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
class Workspace extends BaseWorkspace {

	protected $searchable_columns = array('name');
	
	/**
	 * @var string
	 */
	var $summary_field = "text";
	
	
	/**
	 * Message is file container
	 *
	 * @var boolean
	 */


	/**
	 * Cached array of related forms
	 *
	 * @var array
	 */
	private $related_forms;

	// ---------------------------------------------------
	//  Comments
	// ---------------------------------------------------

	
	/**
	 * Create new comment. This function is used by ProjectForms to post comments
	 * to the messages
	 *
	 * @param string $content
	 * @param boolean $is_private
	 * @return Comment or NULL if we fail to save comment
	 * @throws DAOValidationError
	 */
	function addComment($content, $is_private = false) {
		$comment = new Comment();
		$comment->setText($content);
		return $this->attachComment($comment);
	} // addComment
	


	// ---------------------------------------------------
	//  Related forms
	// ---------------------------------------------------

	/**
	 * Get project forms that are in relation with this message
	 *
	 * @param void
	 * @return array
	 */
	function getRelatedForms() {
		if(is_null($this->related_forms)) {
			$this->related_forms = ProjectForms::findAll(array(
          'conditions' => '`action` = ' . DB::escape(ProjectForm::ADD_COMMENT_ACTION) . ' AND `in_object_id` = ' . DB::escape($this->getId()),
          'order' => '`order`'
          )); // findAll
		} // if
		return $this->related_forms;
	} // getRelatedForms

	
	
	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	function canAdd(Contact $contact, $context, &$notAllowedMember = ''){
		return can_manage_dimension_members($contact);	
	}
	

	/**
	 * Returns true if $contact can access this message
	 *
	 * @param Contact $contact
	 * @return boolean
	 */
	function canView(Contact $contact) {
		return can_manage_dimension_members($contact); 
	} // canView

	/**
	 * Check if specific user can edit this messages
	 *
	 * @access public
	 * @param Contact $contact
	 * @return boolean
	 */
	function canEdit(Contact $contact) {
		return can_manage_dimension_members($contact);	
	} 


	/**
	 * Check if specific user can delete this messages
	 *
	 * @access public
	 * @param Contact $contact
	 * @return boolean
	 */
	function canDelete(Contact $contact) {
		return can_manage_dimension_members($contact);
	} // canDelete

	/**
	 * Check if specific user can comment this message
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function canAddComment(Contact $contact) {
		return can_write($contact, $this->getMembers(), $this->getObjectTypeId());
	} // canAddComment


	// ---------------------------------------------------
	//  URLS
	// ---------------------------------------------------

	/**
	 * Return view message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getViewUrl() {
		if ($member = $this->getSelfMember()) {
			return "
				javascript:og.workspaces.onWorkspaceClick(".$member->getId().");og.openTab('overview-panel')
			" ;
		}
		return null ;
	}

	/**
	 * Return edit message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getEditUrl() {
		return null;
	} 


	/**
	 * Return delete message URL
	 *
	 * @access public
	 * @param void
	 * @return string
	 */
	function getDeleteUrl() {
		return get_url('member', 'delete', array('id' => $this->getId()));
	} // getDeleteUrl



	/**
	 * Return print view URL
	 *
	 * @return string
	 */
	function getPrintViewUrl() {
		return get_url('workspace', 'print_view', array('id' => $this->getId()));
	}
	
	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Delete this object
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function delete() {
		return parent::delete();
	} // delete

	/**
	 * Validate before save
	 *
	 * @access public
	 * @param array $errors
	 * @return null
	 */
	function validate(&$errors) {
		if(!$this->validatePresenceOf('name')) {
			$errors[] = lang('message title required');
		} 
	} // validate

	
	/**
	* Return object URL
	*
	* @access public
	* @param void
	* @return string
	*/
	function getObjectUrl() {
		return $this->getViewUrl();
	} // getObjectUrl
	
	
    function getId(){
    	return parent::getObjectId();
    }
    
    function getArrayInfo(){
    
    	return array(
    		"id" => $this->getId(),
    		"description" => $this->getDescription()
    	);
    }
    
    function getIconClass() {
    	$d = Dimensions::findByCode('workspaces');
    	$m = Members::findOneByObjectId($this->getId(), $d->getId());
    	
    	return "ico-color" . ($m instanceof Member ? $m->getColor() : '0');
    }
    
} 