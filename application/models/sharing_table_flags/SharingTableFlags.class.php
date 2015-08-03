<?php

class SharingTableFlags extends BaseSharingTableFlags {
	
	
	function getFlags(DateTimeValue $date) {
		$flags = $this->findAll(array('conditions' => array('`execution_date` < ?', $date)));
		return $flags;
	}
	
	function healPermissionGroup(SharingTableFlag $flag) {
		
		if ($flag->getObjectId() > 0) {
			
			try {
				$obj = Objects::findObject($flag->getObjectId());
				if (!$obj instanceof ContentDataObject) {
					$flag->delete(); // if object does not exists then delete the flag
					return;
				}
				
				DB::beginWork();
				// update sharing table
				$obj->addToSharingTable();
			
				DB::commit();
			
			} catch(Exception $e) {
				DB::rollback();
				Logger::log("Failed to heal object permissions for object ".$flag->getObjectId()." (flag_id = ".$flag->getId().")");
				return false;
			}
			
			// delete flag
			$flag->delete();
			return true;
			
		} else {
			// heal 
			$controller = new SharingTableController();
			
			$permissions_string = $flag->getPermissionString();
			$permission_group_id = $flag->getPermissionGroupId();
			
			$permissions = json_decode($permissions_string);
			if ($flag->getMemberId() > 0) {
				foreach ($permissions as $p) {
					if (!isset($p->m)) $p->m = $flag->getMemberId();
				}
			}
			
			try {
				
				DB::beginWork();
				// update sharing table
				$controller->afterPermissionChanged($permission_group_id, $permissions);
				
				DB::commit();
				
			} catch(Exception $e) {
				DB::rollback();
				Logger::log("Failed to heal permission group $permission_group_id (flag_id = ".$flag->getId().")\n".$e->getTraceAsString());
				return false;
			}
			
			// delete flag
			$flag->delete();
			return true;
		}
	}
}
