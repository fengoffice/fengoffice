<?php

class ContactMemberCache extends BaseContactMemberCache {
	
	private $parent_member_cache = null;
	
	/**
	 * Returns the parent member cache or null if there isn't one
	 * @return Member
	 */
	function getParentMemberCache() {
		if ($this->parent_member_cache == null){
			if ($this->getParentMemberId() != 0) {
				$id = array('contact_id' => $this->getContactId(), 'member_id' => $this->getParentMemberId());
				$this->parent_member_cache = ContactMemberCaches::findById($id);
			}
		}
		return $this->parent_member_cache;
	}
	

}