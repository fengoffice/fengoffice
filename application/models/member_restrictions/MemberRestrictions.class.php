<?php

  /**
  * MemberRestrictions
  *
  * @author Diego Castiglioni <diego.castiglioni@fengoffice.com>
  */
  class MemberRestrictions extends BaseMemberRestrictions {
    
    
    function clearRestrictions($member_id) {
    	return self::delete(array("`member_id` = ?", $member_id));
    }
    
  } // MemberRestrictions 

?>