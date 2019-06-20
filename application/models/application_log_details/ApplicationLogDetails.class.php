<?php

class ApplicationLogDetails extends BaseApplicationLogDetails {

	
	static function calculateSavedObjectDifferences(ContentDataObject $object, ContentDataObject $old_object) {
		
		if ($object instanceof Member) {
			$object = Objects::findObject($object->getObjectId());
		}
		if (!$object instanceof ContentDataObject) {
			return;
		}
		
		$manager = $object->manager();
		$object_columns = array_merge(array('name'), $manager->getColumns());
		
		// member ids
		$old_member_ids = array_filter($old_object->member_ids);
		$current_member_ids = array_filter($object->getMemberIds());
		
		// remove member ids of persons dimension from both arrays
		// get the person member ids
		$person_member_ids = DB::executeAll("
			SELECT m.id FROM ".TABLE_PREFIX."members m 
			INNER JOIN ".TABLE_PREFIX."dimensions d ON d.id=m.dimension_id
			WHERE d.code = 'feng_persons';
		");
		$person_member_ids = array_flat($person_member_ids);
		
		// remove person member ids from both arrays
		$old_member_ids = array_diff($old_member_ids, $person_member_ids);
		$current_member_ids = array_diff($current_member_ids, $person_member_ids);
		// --
		
		
		// custom properties
		$old_cp_values = $old_object->custom_properties;
		$cp_values = array();
		$custom_properties = CustomProperties::getAllCustomPropertiesByObjectType($object->getObjectTypeId());
		foreach ($custom_properties as $cp) {
			$cpval = CustomPropertyValues::instance()->getCustomPropertyValue($object->getId(), $cp->getId());
			$cp_values[$cp->getId()] = $cpval instanceof CustomPropertyValue ? $cpval->getValue() : '';
		}

		// subscribers
		$old_subscriber_ids = $old_object->subscriber_ids;
		$subscriber_ids = $object->getSubscriberIds();
		
		// linked objects
		$old_linked_object_ids = $old_object->linked_object_ids;
		$linked_object_ids = $object->getAllLinkedObjectIds();
		
		$differences = array();
		
		// compare object columns
		foreach ($object_columns as $column) {
			if ($object->getColumnValue($column) != $old_object->getColumnValue($column)) {
				$differences[$column] = array(
					'property' => $column,
					'old_value' => $old_object->getColumnValue($column),
					'new_value' => $object->getColumnValue($column),
				);
			}
		}
		
		// compare custom properties
		foreach ($custom_properties as $cp) {
			if ($cp_values[$cp->getId()] != $old_cp_values[$cp->getId()]) {
				$differences['cp_' . $cp->getId()] = array(
					'property' => 'cp_' . $cp->getId(),
					'old_value' => $old_cp_values[$cp->getId()],
					'new_value' => $cp_values[$cp->getId()],
				);
			}
		}
		
		// compare classification
		$members_added = array_diff($current_member_ids, $old_member_ids);
		$members_removed = array_diff($old_member_ids, $current_member_ids);
		if (count($members_added) > 0 || count($members_removed) > 0) {
			$differences['classification'] = array(
					'property' => 'classification',
					'old_value' => $old_member_ids,
					'new_value' => $current_member_ids,
			);
		}
		
		// compare subscribers
		$subscribers_added = array_diff($subscriber_ids, $old_subscriber_ids);
		$subscribers_removed = array_diff($old_subscriber_ids, $subscriber_ids);
		if (count($subscribers_added) > 0 || count($subscribers_removed) > 0) {
			$differences['subscribers'] = array(
					'property' => 'subscribers',
					'old_value' => $old_subscriber_ids,
					'new_value' => $subscriber_ids,
			);
		}
		
		// compare linked objects
		$linked_objects_added = array_diff($linked_object_ids, $old_linked_object_ids);
		$linked_objects_removed = array_diff($old_linked_object_ids, $linked_object_ids);
		if (count($linked_objects_added) > 0 || count($linked_objects_removed) > 0) {
			$differences['linked_objects'] = array(
					'property' => 'linked_objects',
					'old_value' => $old_linked_object_ids,
					'new_value' => $linked_object_ids,
			);
		}
		
		
		return $differences;
	}
	
	
	
	static function saveObjectDifferences(ApplicationLog $log, $object_differences) {
		
		if ($log->getAction() == ApplicationLogs::ACTION_LINK) {
			if (array_var($object_differences, 'linked_objects')) {
				$differences_to_save = array('linked_objects' => $object_differences['linked_objects']);
			}
		} else {
			$differences_to_save = $object_differences;
		}
		
		$application_log_id = $log->getId();

		foreach ($differences_to_save as $object_difference) {
			$object_difference['application_log_id'] = $application_log_id;

			if ($object_difference['old_value'] instanceof DateTimeValue) {
				$object_difference['old_value'] = $object_difference['old_value']->toMySQL();
			} else if (is_array($object_difference['old_value'])) {
				$object_difference['old_value'] = implode(',', $object_difference['old_value']);
			}

			if ($object_difference['new_value'] instanceof DateTimeValue) {
				$object_difference['new_value'] = $object_difference['new_value']->toMySQL();
			} else if (is_array($object_difference['new_value'])) {
				$object_difference['new_value'] = implode(',', $object_difference['new_value']);
			}
			
			$detail = new ApplicationLogDetail();
			$detail->setFromAttributes($object_difference);
			$detail->save();
		}
		
		
	}
	
	
	static function buildLogDetailsHtml(ApplicationLog $log) {
		
		$html = '';
		$all_details = self::findAll(array('conditions' => 'application_log_id = '.$log->getId()));
		$object = Objects::findObject($log->getRelObjectId());
		$manager = $object->manager();
		$object_type = ObjectTypes::findByID($object->getObjectTypeId());
		$log_user_name = $log->getTakenByDisplayName();
		
		$logs_html = '';
		
		foreach ($all_details as $detail) {
			/* @var $detail ApplicationLogDetail */
			
			$log_text = '';
			
			switch ($detail->getProperty()) {
				case 'classification':
					$new_ids = explode(',', $detail->getNewValue());
					$old_ids = explode(',', $detail->getOldValue());

					$added_ids = array_diff($new_ids, $old_ids);
					$removed_ids = array_diff($old_ids, $new_ids);

					if (count($added_ids) > 0) {
						$member_names = '';
						$members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$added_ids).')'));
						foreach ($members as $m) {
							$member_names .= ($member_names == '' ? '' : ', ') . $m->getName();
						}
						$log_text .= "Added classification in: $member_names";
					}
					if (count($removed_ids) > 0) {
						$member_names = '';
						$members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$removed_ids).')'));
						foreach ($members as $m) {
							$member_names .= ($member_names == '' ? '' : ', ') . $m->getName();
						}
						$log_text .= ($log_text==''?'':'<br/>') . "Removed classification in: $member_names";
					}
					
					break;
					
				case 'linked_objects':
					$new_ids = explode(',', $detail->getNewValue());
					$old_ids = explode(',', $detail->getOldValue());

					$added_ids = array_diff($new_ids, $old_ids);
					$removed_ids = array_diff($old_ids, $new_ids);

					if (count($added_ids) > 0) {
						$object_names = '';
						$objects = Objects::findAll(array('conditions' => 'id IN ('.implode(',',$added_ids).')'));
						foreach ($objects as $o) {
							$object_names .= ($object_names == '' ? '' : ', ') . $o->getName();
						}
						$log_text .= "Linked to objects: $object_names";
					}
					if (count($removed_ids) > 0) {
						$object_names = '';
						$objects = Objects::findAll(array('conditions' => 'id IN ('.implode(',',$removed_ids).')'));
						foreach ($objects as $o) {
							$object_names .= ($object_names == '' ? '' : ', ') . $o->getName();
						}
						$log_text .= ($log_text==''?'':'<br/>') . "Unlinked to objects: $object_names";
					}
					
					break;
					
				default:
					$co_columns = array_merge(array('name'), $manager->getColumns());
					$system_columns = $manager->getSystemColumns();
					if ($manager instanceof ProjectTasks) {
						$system_columns[] = 'assigned_by_id';
						$system_columns[] = 'assigned_on';
						$system_columns[] = 'order';
						$system_columns[] = 'type_content';
					}
					
					if (str_starts_with($detail->getProperty(), "cp_")) {
						$cp_id = str_replace("cp_", "", $detail->getProperty());
						$cp = CustomProperties::findById($cp_id);
						
						$old_value = $detail->getOldValue();
						$new_value = $detail->getNewValue();
						
						if (in_array($cp->getType(), array('contact','user','numeric'))) {
							if (!is_numeric($old_value)) $old_value = '';
							if (!is_numeric($new_value)) $new_value = '';
							if ($old_value == $new_value) break;
						}
						
						if ($detail->getOldValue() != '') {
							$log_text = "Changed the property " . $cp->getName() . ' from "' . $old_value . '"' . ' to "' . $new_value . '"';
						} else {
							$log_text = $cp->getName() . ' (previously empty) is now defined as: "' . $new_value . '"';
						}
					} else if (in_array($detail->getProperty(), $co_columns) && !in_array($detail->getProperty(), $system_columns)) {
						
						$old_value = format_value_to_print($detail->getProperty(), $detail->getOldValue(), $manager->getColumnType($detail->getProperty()), $object->getObjectTypeId());
						$new_value = format_value_to_print($detail->getProperty(), $detail->getNewValue(), $manager->getColumnType($detail->getProperty()), $object->getObjectTypeId());

						if ($old_value == '--') $old_value = '';
						if ($new_value == '--') $new_value = '';
						
						$field_name = Localization::instance()->lang('field '.$object_type->getHandlerClass().' '.$detail->getProperty());
						if (is_null($field_name)) $field_name = Localization::instance()->lang('field Objects '.$detail->getProperty());
						if (is_null($field_name)) $field_name = Localization::instance()->lang($detail->getProperty());
						if (is_null($field_name)) $field_name = $detail->getProperty();
						
						if ($old_value != '') {
							$log_text = "Changed the property " . $field_name . ' from "' . $old_value . '"' . ' to "' . $new_value . '"';
						} else {
							$log_text = $field_name . ' (previously empty) is now defined as: "' . $new_value . '"';
						}
					}
			}
			
			if ($log_text != '') {
				$logs_html .= '<li class="log-detail">' . $log_text . '</li>';
			}
		}
		
		if ($logs_html != '') {
			$html .= '<div class="logs-group"><div class="log-header">' . format_datetime($log->getCreatedOn()) .' - '. $log_user_name .':</div><ul>'. $logs_html .'</ul></div>';
		}
		
		return $html;
		
	}

} // ApplicationLogDetails

?>