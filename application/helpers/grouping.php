<?php


	function getGroup($groups, $member_id, $group_type, $all_pgroup_ids) {
		if (!$member_id && $groups[0]['group']['name'] != lang('unclassified')) return null;
		if (count($all_pgroup_ids) > 0) {
			$pg_id = array_shift($all_pgroup_ids);
			return getGroup($groups[$pg_id]['subgroups'], $member_id, $group_type, $all_pgroup_ids);
		}
		
		if (array_key_exists($member_id, $groups) && $groups[$member_id]['group']['group_type'] == $group_type) {
			return $groups[$member_id];
		}
		
		return null;
	}
	
	function setGroup(&$groups, $member_id, $group, $group_type, $all_pgroup_ids) {
		
		if (count($all_pgroup_ids) > 0) {
			$pg_id = array_shift($all_pgroup_ids);
			return setGroup($groups[$pg_id]['subgroups'], $member_id, $group, $group_type, $all_pgroup_ids);
		}
		
		if (array_key_exists($member_id, $groups) && $groups[$member_id]['group']['group_type'] == $group_type) {
			$groups[$member_id] = $group;
			return true;
		}
		
		return false;
	}
	
	
	function makeDimensionGroups($objects, $dimension_id, &$parent_group = null) {
		// key = member_id - values = subset of objects or subgroups
		$groups = array();
		$grouped_objects = array();
		
		$max_level = 0;
		
		foreach ($objects as $object) {
			if (($object instanceof ApplicationReadLog || $object instanceof ApplicationLog) && $object->getColumnValue('rel_object_id') > 0) {
				$object_id = $object->getRelObjectId();
			} else {
				$object_id = $object->getId();
			}
			
			$members = ObjectMembers::getMembersByObjectAndDimension($object_id, $dimension_id, "AND om.is_optimization = 0");
			if (is_array($members) && count($members) > 0) {
				if (count($members) > 1) {
					$max_depth = 0;
					$member = null;
					foreach ($members as $tmp_m) {
						if ($tmp_m->getDepth() > $max_depth) {
							$max_depth = $tmp_m->getDepth();
							$member = $tmp_m;
						}
					}
				} else {
					$member = $members[0];
				}
				$all_parents = array_reverse($member->getAllParentMembersInHierarchy(true));
				$all_p_keys = "";
				
				foreach ($all_parents as $p_member) {
					$all_p_keys .= ($all_p_keys == "" ? "" : "_") . $p_member->getId();
					
					$new_group = array('group' => array('id' => $p_member->getId(), 'name' => $p_member->getName(), 'pid' => $p_member->getParentMemberId(), 
						'type' => $p_member->getObjectTypeId(), 'obj' => $p_member->getObjectId(), 'group_type' => 'dimension'), 'subgroups' => array());
					
					$level = $p_member->getDepth();
					$max_level = $level > $max_level ? $level : $max_level;
					
					if (isset($groups[$level]) && isset($groups[$level][$p_member->getId()])) {
						$new_group = $groups[$level][$p_member->getId()];
					}
					
					if (!isset($groups[$level])) {
						$groups[$level] = array($p_member->getId() => $new_group);
					} else if (!isset($groups[$level][$p_member->getId()])) {
						$groups[$level][$p_member->getId()] = $new_group;
					}
					
					if ($p_member->getId() == $member->getId()) {
						
						if (!isset($grouped_objects[$all_p_keys])) $grouped_objects[$all_p_keys] = array($object);
						else $grouped_objects[$all_p_keys][] = $object;
						
					}
				}
			} else {
				if (!isset($groups[1])) $groups[1] = array();
				if (!isset($groups[1][0])) $groups[1][0] = array('group' => array('id' => 0, 'name' => lang('unclassified'), 'pid' => 0, 'type' => 0, 'obj' => 0, 'group_type' => 'dimension'), 'subgroups' => array());
				
				if (!isset($grouped_objects[0])) $grouped_objects[0] = array();
				$grouped_objects[0][] = $object;
			}
		}
		
		$i = $max_level;
		while ($i > 1) {
			foreach ($groups[$i] as $member_id => $gp) {
				$member = $gp['group'];
				$pid = $member['pid'];
				
				foreach ($groups as $l => $g) {
					if (isset($groups[$l][$pid])) {
						if ($pid > 0) {
							$groups[$l][$pid]['subgroups'][$member_id] = $gp;
						} else {
							$groups[1][$member_id] = $gp;
						}
						break;
					}
				}
			}
			$i--;
		}
		
		foreach ($groups as $level => $value) {
			if ($level > 1) unset($groups[$level]);
		}
		
		if ($parent_group != null && isset($groups[1])) {
			foreach ($groups[1] as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
		
		return array('groups' => isset($groups[1]) ? $groups[1] : array(), 'grouped_objects' => $grouped_objects);
	}
	
	
	function groupObjects($group_by, $objects) {
		if (count($group_by) == 0) {
			$grouped = array(
				'groups' => array(array('group' => array('id' => 0, 'name' => '', 'pid' => 0), 'subgroups' => array())),
				'grouped_objects' => array(0 => $objects),
			);
		} else {
			// first grouping
			$grouped = makeGroups($objects, $group_by[0]);
			
			// more groupings
			for ($gb_index = 1; $gb_index < count($group_by); $gb_index++) {
			
				$to_remove = array();
				foreach ($grouped['grouped_objects'] as $key => $gobjects) {
					
					$member_id = strrpos($key, "_") === FALSE ? $key : substr($key, strrpos($key, "_")+1);
					
					$all_pgroup_ids = (explode("_", $key));
					array_pop($all_pgroup_ids);
					
					$group_type = $group_by[$gb_index-1]['type'];
					
					$parent_group = getGroup($grouped['groups'], $member_id, $group_type, $all_pgroup_ids);
					
					$grouped_tmp = makeGroups($gobjects, $group_by[$gb_index], $parent_group);
					
					if ($parent_group) {
						setGroup($grouped['groups'], $member_id, $parent_group, $group_type, $all_pgroup_ids);
					}
					
					if (count($grouped_tmp['grouped_objects']) > 0) {
						foreach ($grouped_tmp['grouped_objects'] as $m => $objs) {
							foreach ($objs as $obj) {
								$grouped['grouped_objects'][$key . "_" . $m][] = $obj;
							}
						}
						$to_remove[] = $key;
					}
				}
				foreach ($to_remove as $k) unset($grouped['grouped_objects'][$k]);

			}
		}
		
		return $grouped;
	}
	
	
	function makeGroups($objects, $gb_criteria, &$parent_group = null) {
		if (array_var($gb_criteria, 'type') == 'dimension') {
			$grouped = makeDimensionGroups($objects, array_var($gb_criteria, 'value'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'column') {
			$grouped = groupObjectsByColumnValue($objects, array_var($gb_criteria, 'value'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'assoc_obj') {
			$grouped = groupObjectsByAssocObjColumnValue($objects, array_var($gb_criteria, 'value'), array_var($gb_criteria, 'fk'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'custom_prop') {
			$grouped = groupObjectsByCustomPropertyValue($objects, array_var($gb_criteria, 'value'), $parent_group);
		} else if (array_var($gb_criteria, 'type') == 'first_level_task_only') {
			$grouped = groupObjectsByColumnValue($objects, 'rel_object_id', $parent_group, true);
		}
		return $grouped;
	}
	
	
	function order_groups_by_name($groups) {
		$tmp = array();
		if (is_array($groups)) {
		  foreach ($groups as $group_obj) {
			// id is concatenated to avoid losing information when two groups have the same name
			$tmp[strtoupper($group_obj['group']['name'] . "_" . $group_obj['group']['id'])] = $group_obj;
		  }
		}
		ksort($tmp, SORT_STRING);
		$ordered = array();
		foreach ($tmp as $group_obj) {
			$ordered[$group_obj['group']['id']] = $group_obj;
		}
		return $ordered;
	}
	
	
	function groupObjectsByColumnValue($objects, $column, &$parent_group = null, $only_first_level_task = false) {
		
		$groups = array();
		$grouped_objects = array();
		
		foreach ($objects as $obj) {
			$gb_val = $obj->getColumnValue($column);
			
			if ($gb_val > 0 && $only_first_level_task) {
				$tmp_task = ProjectTasks::instance()->findById($gb_val);
				while ($tmp_task instanceof ProjectTask) {
					if ($tmp_task->getParentId() > 0) {
						$gb_val = $tmp_task->getParentId();
					} else {
						break;
					}
					$tmp_task = ProjectTasks::instance()->findById($gb_val);
				}
			}
			
			$group = null;
			foreach ($groups as $g) {
				if (array_var($g, 'id') === $gb_val) $group = $g;
			}
			if (is_null($group)) {
				/* @var $obj ContentDataObject */
				
				if ($column == 'priority') {
					$name = lang('priority '.$gb_val);
				} else {
					$related_object = Objects::findObject($gb_val);
					if ($related_object instanceof ContentDataObject) {
						$name = $related_object->getObjectName();
					} else {
						$name = lang('unclassified');
					}
				}
				$group_type = 'column';
				if ($only_first_level_task) {
					$group_type = 'first_level_task_only';
				}
				$group = array('group' => array('id' => $gb_val, 'name' => $name, 'pid' => 0, 'group_type' => $group_type), 'subgroups' => array());
				$groups[$gb_val] = $group;
			}
			
			if (!isset($grouped_objects[$gb_val])) $grouped_objects[$gb_val] = array();
			$grouped_objects[$gb_val][] = $obj;
		}
		
		if ($parent_group != null) {
			foreach ($groups as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
		
		return array('groups' => $groups, 'grouped_objects' => $grouped_objects);
	}
	
	function groupObjectsByAssocObjColumnValue($objects, $column, $fk, &$parent_group = null) {
		
		$groups = array();
		$grouped_objects = array();
		$i=1;
		foreach ($objects as $obj) {
			$group = null;
			$rel_obj = Objects::findObject($obj->getColumnValue($fk));
			if (!$rel_obj instanceof ContentDataObject) {
				$gb_val = 'unclassified';
			} else {
				$gb_val = $rel_obj->getColumnValue($column);
				if ($gb_val == 0) $gb_val = 'unclassified';
			}
			foreach ($groups as $g) {
				if (array_var($g, 'id') == $gb_val) $group = $g;
			}
			if (is_null($group)) {
				if ($gb_val != 'unclassified' && in_array($column, $rel_obj->manager()->getExternalColumns())) {
					if ($obj instanceof Timeslot) {
						$related_object = Objects::findObject($rel_obj->getColumnValue($column));
					} else {
						$related_object = Objects::findObject($obj->getColumnValue($column));
					}
					if ($related_object instanceof ContentDataObject) {
						$name = $related_object->getObjectName();
					} else {
						$name = lang('unclassified');
					}
				} else if ($gb_val == 'unclassified') {
					$name = lang('unclassified');
				} else {
					$name = lang("$column $gb_val");
				}
				
				$group = array('group' => array('id' => $gb_val, 'name' => $name, 'pid' => 0, 'group_type' => 'assoc_obj'), 'subgroups' => array());
				$groups[$gb_val] = $group;
			}
			
			if (!isset($grouped_objects[$gb_val])) $grouped_objects[$gb_val] = array();
			$grouped_objects[$gb_val][] = $obj;
		}
		
		if ($parent_group != null) {
			foreach ($groups as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
		
		return array('groups' => $groups, 'grouped_objects' => $grouped_objects);
	}
	
	
	function groupObjectsByCustomPropertyValue($objects, $cp_id, &$parent_group = null) {
	
		$cp = CustomProperties::instance()->findById($cp_id);
		if (!$cp instanceof CustomProperty) {
			return array('groups' => array(), 'grouped_objects' => $objects);
		}
		
		$groups = array();
		$grouped_objects = array();
		$i=1;
		foreach ($objects as $obj) {
			$group = null;
			$cp_value = CustomPropertyValues::getCustomPropertyValue($obj->getId(), $cp->getId());
			
			if (!$cp_value instanceof CustomPropertyValue) {
				$gb_val = 'unclassified';
			} else {
				$gb_val = trim($cp_value->getValue());
				if ($gb_val == "") $gb_val = 'unclassified';
			}
			foreach ($groups as $g) {
				if (array_var($g, 'id') == $gb_val) $group = $g;
			}
			if (is_null($group)) {
				if ($gb_val != 'unclassified') {
					$name = $cp->getName() . ": " . $gb_val;
				} else {
					$name = lang('unclassified');
				}
	
				$group = array('group' => array('id' => $gb_val, 'name' => $name, 'pid' => 0, 'group_type' => 'assoc_obj'), 'subgroups' => array());
				$groups[$gb_val] = $group;
			}
				
			if (!isset($grouped_objects[$gb_val])) $grouped_objects[$gb_val] = array();
			$grouped_objects[$gb_val][] = $obj;
		}
	
		if ($parent_group != null) {
			foreach ($groups as $mid => $group) {
				$parent_group['subgroups'][$mid] = $group;
			}
		}
	
		return array('groups' => $groups, 'grouped_objects' => $grouped_objects);
	}