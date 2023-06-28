<?php

/**
 * ApplicationLogDetail class
 */
class ApplicationLogDetail extends BaseApplicationLogDetail {

	
	/**
	 * Return user who made this acction
	 *
	 * @access public
	 * @param void
	 * @return Contact
	 */
	function getApplicationLog() {
		return ApplicationLogs::findById($this->getApplicationLogId());
	} // getApplicationLog


	function getFormattedData($object) {

		return array(
			'property' => $this->getPropertyLabel($object),
			'old_value' => $this->formatValue($object, $this->getOldValue()),
			'new_value' => $this->formatValue($object, $this->getNewValue(), true),
		);
	}

	function formatValue($object, $value, $only_differences_with_old_value = false) {

		$formatted = $value;

		$property = $this->getProperty();

		if ($object->columnExists($property)) {

			$object_type_id = $object->getObjectTypeId();
			$ot = ObjectTypes::findById($object_type_id);
			$task_amount_field = $ot->getName() == 'task' && in_array($property, array('executed_cost', 'earned_value', 'estimated_cost', 'estimated_price'));
			$time_amount_field = $ot->getName() == 'timeslot' && in_array($property, array('fixed_billing', 'hourly_billing', 'fixed_cost', 'hourly_cost'));
			$actual_expense_amount_field = $ot->getName() == 'payment_receipt' && in_array($property, array('unit_cost', 'amount', 'unit_price', 'total_price', 'total_cost_without_taxes'));

			if (in_array($property, $object->manager()->getExternalColumns())) {
				$formatted = Reports::instance()->getExternalColumnValue($property, $value, $object->manager());
			} else if($task_amount_field) {
				$currency_symbol = config_option('currency_code');
				$formatted = format_money_amount($value, $currency_symbol);
			} else if($time_amount_field || $actual_expense_amount_field) {
				if($ot->getName() == 'timeslot') {
					$currency_id = $property == 'fixed_billing' || $property == 'hourly_billing' ? $object->getRateCurrencyId() : $object->getCostCurrencyId();
				} else if($ot->getName() == 'payment_receipt') {
					$currency_id = $object->getCurrencyId();
				}
				$currency = Currencies::getCurrency($currency_id);
				$c_symbol = $currency instanceof Currency ? $currency->getSymbol() : config_option('currency_code');
				$formatted = format_money_amount($value, $c_symbol);
			}else {
				$formatted = format_value_to_print($property, $value, $object->getColumnType($property), $object->getObjectTypeId());
			}

		} else if (str_starts_with($property, 'cp_')) {

			$cp_id = str_replace('cp_', '', $property);
			$cp = CustomProperties::getCustomProperty($cp_id);
			if ($cp instanceof CustomProperty) {
				$formatted = format_value_to_print($property, $value, $cp->getOgType(), $object->getObjectTypeId());
			}

		} else if ($property == 'classification') {

			if ($only_differences_with_old_value) {

				$formatted = "";
				$current_member_ids = array_filter(explode(',', $value));
				$old_member_ids = array_filter(explode(',', $this->getOldValue()));
				$members_added_ids = array_diff($current_member_ids, $old_member_ids);
				$members_removed_ids = array_diff($old_member_ids, $current_member_ids);

				$members_added = Members::findAll(array("conditions" => "id IN (".implode(',',$members_added_ids).")"));
				if (count($members_added) > 0) {
					$formatted .= lang('added') . ":<ul>";
					foreach ($members_added as $m) {
						$formatted .= "<li><span class='link-ico ico-color".$m->getColor()." ".$m->getIconClass()."'>" . $m->getDisplayName() . "</span></li>";
					}
					$formatted .= "</ul>";
				}

				$members_removed = Members::findAll(array("conditions" => "id IN (".implode(',',$members_removed_ids).")"));
				if (count($members_removed) > 0) {
					$formatted .= lang('removed') . ":<ul>";
					foreach ($members_removed as $m) {
						$formatted .= "<li><span class='link-ico ico-color".$m->getColor()." ".$m->getIconClass()."'>" . $m->getDisplayName() . "</span></li>";
					}
					$formatted .= "</ul>";
				}

			} else {
				$formatted = "<ul>";

				$mem_ids = array_filter(explode(',', $value));
				if (count($mem_ids) > 0) {
					$members = Members::findAll(array("conditions" => "id IN (".implode(',',$mem_ids).")"));
					foreach ($members as $m) {
						$formatted .= "<li><span class='link-ico ico-color".$m->getColor()." ".$m->getIconClass()."'>" . $m->getDisplayName() . "</span></li>";
					}
				}

				$formatted .= "</ul>";
			}

		} else if ($property == 'linked_objects' || $property == 'subscribers') {

			if ($only_differences_with_old_value) {

				$formatted = "";
				$current_object_ids = array_filter(explode(',', $value));
				$old_object_ids = array_filter(explode(',', $this->getOldValue()));
				$object_added_ids = array_diff($current_object_ids, $old_object_ids);
				$object_removed_ids = array_diff($old_object_ids, $current_object_ids);

				$objects_added = Objects::findAll(array("conditions" => "id IN (".implode(',',$object_added_ids).")"));
				if (count($objects_added) > 0) {
					$formatted .= lang('added') . ":<ul>";
					foreach ($objects_added as $o) {
						$formatted .= '<li><a class="internalLink link-ico ico-'.$o->getObjectTypeName().'" href="'.$o->getViewUrl() .'">' . $o->getName() . "</a></li>";
					}
					$formatted .= "</ul>";
				}

				$object_removed = Objects::findAll(array("conditions" => "id IN (".implode(',',$object_removed_ids).")"));
				if (count($object_removed) > 0) {
					$formatted .= lang('removed') . ":<ul>";
					foreach ($object_removed as $o) {
						$formatted .= '<li><a class="internalLink link-ico ico-'.$o->getObjectTypeName().'" href="'.$o->getViewUrl() .'">' . $o->getName() . "</a></li>";
					}
					$formatted .= "</ul>";
				}

			} else {
				$formatted = "<ul>";

				$obj_ids = array_filter(explode(',', $value));
				if (count($obj_ids) > 0) {
					$objects = Objects::findAll(array("conditions" => "id IN (".implode(',',$obj_ids).")"));
					foreach ($objects as $o) {
						$formatted .= '<li><a class="internalLink link-ico ico-'.$o->getObjectTypeName().'" href="'.$o->getViewUrl() .'">' . $o->getName() . "</a></li>";
					}
				}

				$formatted .= "</ul>";
			}

		}


		return $formatted;
		
	}

	function getPropertyLabel(ContentDataObject $object) {

		$label = $this->getProperty();

		if (str_starts_with($label, 'cp_')) {

			$cp_id = str_replace('cp_','',$label);
			$cp = CustomProperties::getCustomProperty($cp_id);
			if ($cp) {
				$label = $cp->getName();
			}
		
		} else if (in_array($label, array('classification','subscribers','linked_objects'))) {

			$label = lang($label);

		} else if (Localization::instance()->lang_exists('field '. get_class($object->manager()) .' '. $label)) {
			
			$label = lang('field '. get_class($object->manager()) .' '. $label);

		} else if (Localization::instance()->lang_exists('field Objects '. $label)) {

			$label = lang('field Objects '. $label);

		}

		return $label;
	}
	
} // ApplicationLogDetail

?>