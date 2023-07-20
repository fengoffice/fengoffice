<?php

class ApplicationLogDetails extends BaseApplicationLogDetails {


	static function countLogDetails($application_log_id) {
		return self::count("application_log_id = ".DB::escape($application_log_id));
	}

	static function getLogDetails($application_log_id) {
		return self::findAll(array("conditions" => array("application_log_id = ?", $application_log_id)));
	}

	
	static function calculateSavedObjectDifferences($object, $old_object) {
		
		if ($object instanceof Member) {
			$object = Objects::findObject($object->getObjectId());
		}
		if (!$object instanceof ContentDataObject) {
			return;
		}

		// ensure that we have the latest version of the object
		$object = Objects::findObject($object->getId(), true);
		
		$manager = $object->manager();
		$object_columns = array_merge(array('name'), $manager->getColumns());
		
		if (!isset($old_object->member_ids)) $old_object->member_ids = array();
		if (!isset($old_object->subscriber_ids)) $old_object->subscriber_ids = array();
		if (!isset($old_object->linked_object_ids)) $old_object->linked_object_ids = array();
		if (!isset($old_object->custom_properties)) $old_object->custom_properties = array();
		
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
			if (array_var($cp_values, $cp->getId()) != array_var($old_cp_values, $cp->getId())) {
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
		
		// check if any relation with other content object has changed (like changing the task of a timeslot)
		$changed_relations = $object->getChangedRelations($old_object);
		if (!empty($changed_relations)) {
			$differences['changed_relations'] = $changed_relations;
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

		foreach ($differences_to_save as $key => $object_difference) {
			if ($key == 'changed_relations') continue;

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
		
		$changed_relations = array_var($differences_to_save, 'changed_relations');
		if (!empty($changed_relations)) {

			// log_action can be relation_added, relation_edited, relation_removed
			foreach ($changed_relations as $log_action => $related_object_ids) {

				// for each related object we must create a new log entry saying that the relation changed
				foreach ($related_object_ids as $obj_id) {
					// find the related object
					$related_object = Objects::findObject($obj_id);

					if ($related_object instanceof ContentDataObject) {
						// create the log and set the associated log entry in the log_data value, so we can track which object triggered this log
						ApplicationLogs::createLog($related_object, $log_action, false, true, true, $application_log_id);
					}
				}
			}
		}
		
	}
	
	
	static function buildLogDetailsHtml(ApplicationLog $log, $email_type) {
		
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

				case 'move_direction_non_working_days':
					break;
				case 'assigned_to_contact_id':
					$config_options = user_config_option('user_assigned_to_task');
					if (!is_array($config_options)) {
						$config_options = explode(',', $config_options);
					}
					if($email_type == '' || in_array($email_type, $config_options)){
						$newId = $detail->getNewValue();
						$oldId = $detail->getOldValue();
						$newContactObj = Contacts::findOne(array('conditions' => array('object_id = ?', $newId)));
						$oldContactObj = Contacts::findOne(array('conditions' => array('object_id = ?', $oldId)));
						$newContact = $newContactObj instanceof Contact ? $newContactObj->getDisplayName() : '';
						$oldContact = $oldContactObj instanceof Contact ? $oldContactObj->getDisplayName() : '';
						if (isset($oldContact)){
							$log_text .= 'Assigned to: <span class="log-detail--old-value">' . $oldContact . '</span> <span class="log-detail--new-value">' . $newContact . '</span>';
						} else {
							$log_text .= 'Assigned to: <span class="log-detail--new-value">' . $newContact . '</span>';
						}
					} 
					break;

				case 'text':
					$config_options = user_config_option('description_changed');
					if (!is_array($config_options)) {
						$config_options = explode(',', $config_options);
					}
					if($email_type == '' || in_array($email_type, $config_options)){
						$newDescription = trim($detail->getNewValue(), '<br />&nbsp;');
						if ($newDescription != ''){
							$log_text .= 'Description: <span class="log-detail--description">"' . $newDescription . '"</span>';
						} else {
							$log_text .= 'Description: " "';
						}
					}
					break;

				case 'start_date':
				case 'due_date':
					$config_options = user_config_option('start_or_due_date_modified');
					if (!is_array($config_options)) {
						$config_options = explode(',', $config_options);
					}
					if($email_type == '' || in_array($email_type, $config_options)){
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
					}
					break;

				case 'classification':
					$config_options = user_config_option('classification_changed');
					if (!is_array($config_options)) {
						$config_options = explode(',', $config_options);
					}
					if($email_type == '' || in_array($email_type, $config_options)){
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
							$log_text .= "Added classification: <span class='log-detail--new-value'>" . $member_names . "</span>";
						}
						if (count($removed_ids) > 0) {
							$member_names = '';
							$members = Members::findAll(array('conditions' => 'id IN ('.implode(',',$removed_ids).')'));
							foreach ($members as $m) {
								$member_names .= ($member_names == '' ? '' : ', ') . $m->getName();
							}
							if($member_names != ''){
								$log_text .= ($log_text==''?'':'<br/>') . "Removed classification: <span class='log-detail--new-value'>" . $member_names . "</span>";
							}
						}
					}
					break;
				case 'name':
					$config_options = explode(',', user_config_option('name_changed'));
					if($email_type == '' || in_array($email_type, $config_options)){
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
					}
					break;	
				case 'time_estimate':
					$config_options = explode(',', user_config_option('time_estimate_changed'));
					if($email_type == '' || in_array($email_type, $config_options)){
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
					}
					break;
				case 'percent_completed':
					$config_options = explode(',', user_config_option('percent_completed_changed'));
					if($email_type == '' || in_array($email_type, $config_options)){
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
					}
					break;
				case 'priority':
					$config_options = explode(',', user_config_option('priority_changed'));
					if($email_type == '' || in_array($email_type, $config_options)){
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
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
						
						if($cp instanceof CustomProperty) {
							$old_value = $detail->getOldValue();
							$new_value = $detail->getNewValue();
							
							if (in_array($cp->getType(), array('contact','user','numeric'))) {
								if (!is_numeric($old_value)) $old_value = '';
								if (!is_numeric($new_value)) $new_value = '';
								if ($old_value == $new_value) break;
							}
							
							$field_name = $cp->getName();
	
							if ($detail->getOldValue() != '') { 
								$log_text .= $field_name . ': <span class="log-detail--old-value">' . $old_value . '</span> ' . '<span class="log-detail--new-value">' . $new_value . '</span>';
							} else {
								$log_text .= $field_name . ': <span class="log-detail--new-value">' . $new_value . '</span>';
							}
						}

					} else if (in_array($detail->getProperty(), $co_columns) && !in_array($detail->getProperty(), $system_columns)) {
						$log_text .= self::buildDetailHtml($detail, $object, $manager, $object_type);
					}
			}
			
			if ($log_text != '') {
				$logs_html .= '<li class="log-detail">' . $log_text . '</li>';
			}
		}
		
		if ($logs_html != '') {
			$html .= '<div class="logs-group"><div class="log-header">' . format_datetime($log->getCreatedOn()) .' by '. $log_user_name .':</div><ul class="log-details">'. $logs_html .'</ul></div>';
		}
		
		return $html;
		
	}

	static function buildDetailHtml($detail, $object, $manager, $object_type){
		$old_value = format_value_to_print($detail->getProperty(), $detail->getOldValue(), $manager->getColumnType($detail->getProperty()), $object->getObjectTypeId());
		$new_value = format_value_to_print($detail->getProperty(), $detail->getNewValue(), $manager->getColumnType($detail->getProperty()), $object->getObjectTypeId());

		if ($old_value == '--') $old_value = '';
		if ($new_value == '--') $new_value = '';
		
		$field_name = Localization::instance()->lang('field '.$object_type->getHandlerClass().' '.$detail->getProperty());
		if (is_null($field_name)) $field_name = Localization::instance()->lang('field Objects '.$detail->getProperty());
		if (is_null($field_name)) $field_name = Localization::instance()->lang($detail->getProperty());
		if (is_null($field_name)) $field_name = $detail->getProperty();
		
		if ($old_value != '') {
			return $field_name . ': <span class="log-detail--old-value">' . $old_value . '</span> ' . '<span class="log-detail--new-value">' . $new_value . '</span>';
		} else {
			return $field_name . ': <span class="log-detail--new-value">' . $new_value . '</span>';
		}
	}

} // ApplicationLogDetails

?>