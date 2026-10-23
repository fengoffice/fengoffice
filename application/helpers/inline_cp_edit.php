<?php

/**
 * Inline custom-property editing helpers.
 *
 * Shared between TaskController and any other controller that wants to inline-edit
 * a CustomProperty on a ContentDataObject row (contacts, events, invoices, …).
 *
 * Functions are generic over object_type_id; pass the object type and (when
 * available) the concrete object instance so subtype-aware permissions work.
 */

Env::useHelper('custom_properties');


/**
 * Returns array('is_in_form' => bool, 'can_edit' => bool) for the given CP and user.
 *
 * Falls back to fully-open (both true) when the advanced_core plugin is missing or
 * the object type has no property groups configured — same default the standard
 * form uses.
 *
 * @param int                $cp_id
 * @param int                $object_type_id
 * @param ContentDataObject  $object  optional; used to read object subtype
 */
function inline_cp_get_permission($cp_id, $object_type_id, $object = null) {
    $permission = array('is_in_form' => true, 'can_edit' => true);

    // PropertyGroups / PropertyGroupProperties live in the advanced_core plugin. When it
    // is not installed/active, fall back to the open default (every CP editable inline).
    if (!Plugins::instance()->isActivePlugin('advanced_core')) {
        return $permission;
    }

    $subtype_id = 0;
    if (is_object($object) && method_exists($object, 'getObjectSubtypeId')) {
        $subtype_id = (int) $object->getObjectSubtypeId();
    }

    static $permissions_cache = array();
    $cache_key = $object_type_id . ':' . $subtype_id . ':'
        . logged_user()->getPermissionGroupId() . ':' . logged_user()->getUserType();
    if (isset($permissions_cache[$cache_key])) {
        return array_var($permissions_cache[$cache_key], $cp_id, array('is_in_form' => false, 'can_edit' => false));
    }

    $groups = PropertyGroups::getAllPropertiesGroupedByPropertyGroup($object_type_id, $subtype_id);

    // If there are no property groups at all for this object type, fall back to the
    // normal-form behavior: every custom property is editable inline.
    if (count($groups) == 0) {
        return $permission;
    }

    $permissions_by_cp = array();
    foreach ($groups as $group) {
        $group_perm = null;
        Hook::fire('check_property_group_permissions', array('user' => logged_user(), 'property_group' => $group), $group_perm);
        if ($group_perm == 'none') {
            continue;
        }

        foreach (array_var($group, 'properties', array()) as $prop_data) {
            $property_id = array_var($prop_data, 'property_id');
            if (!is_numeric($property_id)) {
                continue;
            }
            $property_id = (int) $property_id;

            if (!isset($permissions_by_cp[$property_id])) {
                $permissions_by_cp[$property_id] = array('is_in_form' => true, 'can_edit' => false);
            } else {
                $permissions_by_cp[$property_id]['is_in_form'] = true;
            }

            if (array_var($prop_data, 'is_disabled')) {
                continue;
            }

            $property_perm = null;
            Hook::fire('check_property_group_property_permissions', array(
                'user'           => logged_user(),
                'property_group' => $group,
                'property_id'    => $property_id,
            ), $property_perm);

            if ($property_perm == 'none') {
                continue;
            }
            if (array_var($prop_data, 'readonly')) {
                $property_perm = 'view';
            }
            if (is_null($property_perm)) {
                $property_perm = $group_perm;
            }

            if (is_null($property_perm) || $property_perm == 'edit') {
                $permissions_by_cp[$property_id]['can_edit'] = true;
            }
        }
    }

    $permissions_cache[$cache_key] = $permissions_by_cp;
    return array_var($permissions_by_cp, $cp_id, array('is_in_form' => false, 'can_edit' => false));
}


/**
 * @param CustomProperty     $custom_property
 * @param int                $object_type_id  expected owner type for the CP
 * @param ContentDataObject  $object          optional; for subtype-aware permission check
 * @param string             $error_message   out: user-facing reason when the call returns false
 */
function inline_cp_can_edit($custom_property, $object_type_id, $object = null, &$error_message = null) {
    $error_message = lang('no access permissions');

    if (!($custom_property instanceof CustomProperty)) {
        return false;
    }

    if ((int) $custom_property->getObjectTypeId() != (int) $object_type_id) {
        return false;
    }

    if ($custom_property->getIsDisabled()) {
        return false;
    }

    // display_member_property may look read-only, but the normal edit form lets users
    // override the value at the object level (it is stored in custom_property_values).
    // Only block it when the CP itself is marked as not editable.
    if ($custom_property->getType() == 'display_member_property') {
        if (method_exists($custom_property, 'getIsEditable') && !$custom_property->getIsEditable()) {
            return false;
        }
    }

    $is_calculated = method_exists($custom_property, 'getIsCalculated') && $custom_property->getIsCalculated();
    $is_editable   = !method_exists($custom_property, 'getIsEditable') || $custom_property->getIsEditable();
    if ($is_calculated && !$is_editable) {
        return false;
    }

    $inline_permission = inline_cp_get_permission($custom_property->getId(), $object_type_id, $object);
    if (!$inline_permission['is_in_form'] || !$inline_permission['can_edit']) {
        return false;
    }

    return true;
}


/**
 * Normalizes a raw value posted by the inline editor into the format the storage
 * layer expects (system dates, normalized booleans, amount/currency tuple, …).
 *
 * For 'display_member_property' the value is passed through unchanged — the
 * propagation hook (member_fields_in_objects_after_save_custom_properties)
 * forwards it through MemberCustomPropertiesController::add_custom_properties,
 * which itself expects user-locale-format input.
 */
function inline_cp_normalize_value($custom_property, $value, $object_id) {
    if (is_array($value)) {
        foreach ($value as $k => &$v) {
            $v = is_array($v) ? $v : remove_scripts((string) $v);
        }
        unset($v);
    } else {
        $value = remove_scripts((string) $value);
    }

    $type = $custom_property->getType();

    if ($type == 'display_member_property') {
        return $value;
    }

    if ($type == 'date' || $type == 'datetime') {
        $date_format     = user_config_option('date_format');
        $date_format_tip = date_format_tip($date_format);
        if (is_array($value)) {
            $new_values = array();
            foreach ($value as $val) {
                $val = str_replace($date_format_tip, '', $val);
                if (trim($val) == '') {
                    continue;
                }
                $dtv = DateTimeValueLib::dateFromFormatAndString($date_format, $val);
                $new_values[] = $dtv->format($type == 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d');
            }
            return $new_values;
        }

        $value = str_replace($date_format_tip, '', $value);
        if (trim($value) == '') {
            return '';
        }
        $dtv = DateTimeValueLib::dateFromFormatAndString($date_format, $value);
        return $dtv->format($type == 'datetime' ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    if ($type == 'boolean') {
        return normalize_cp_boolean_stored_value($value);
    }

    if ($type == 'amount') {
        $current_value = CustomPropertyValues::getCustomPropertyValue($object_id, $custom_property->getId());
        $amount        = is_array($value) ? array_var($value, 'amount') : $value;
        $currency_id   = is_array($value) ? array_var($value, 'currency_id') : null;
        if (!$currency_id && $current_value instanceof CustomPropertyValue) {
            $currency_id = $current_value->getCurrencyId();
        }
        if (!$currency_id) {
            $currency_id = 1;
        }
        return array(
            'amount'      => clean_formatted_money_amount_for_sql($amount),
            'currency_id' => (int) $currency_id,
        );
    }

    return $value;
}


/**
 * Persists a normalized value into custom_property_values. Returns the canonical
 * scalar that should be indexed for search (amount → amount only, list → joined).
 */
function inline_cp_save_value($object, $custom_property, $value) {
    $cp_id = (int) $custom_property->getId();

    if ($custom_property->getType() == 'amount') {
        $cpv = CustomPropertyValues::getCustomPropertyValue($object->getId(), $cp_id);
        if (!$cpv instanceof CustomPropertyValue) {
            $cpv = new CustomPropertyValue();
            $cpv->setObjectId($object->getId());
            $cpv->setCustomPropertyId($cp_id);
        }
        $amount      = is_array($value) ? array_var($value, 'amount', '') : $value;
        $currency_id = is_array($value) ? array_var($value, 'currency_id') : null;
        if (!$currency_id && $cpv instanceof CustomPropertyValue) {
            $currency_id = $cpv->getCurrencyId();
        }
        if (!$currency_id) {
            $currency_id = 1;
        }
        $cpv->setValue($amount);
        $cpv->setCurrencyId($currency_id);
        $cpv->save();
        return $amount;
    }

    if (is_array($value)) {
        CustomPropertyValues::deleteCustomPropertyValues($object->getId(), $cp_id);
        foreach ($value as $val) {
            if ($val === '') {
                continue;
            }
            $cpv = new CustomPropertyValue();
            $cpv->setObjectId($object->getId());
            $cpv->setCustomPropertyId($cp_id);
            $cpv->setValue($val);
            $cpv->save();
        }
        return implode(', ', $value);
    }

    $cpv = CustomPropertyValues::getCustomPropertyValue($object->getId(), $cp_id);
    if (!$cpv instanceof CustomPropertyValue) {
        $cpv = new CustomPropertyValue();
        $cpv->setObjectId($object->getId());
        $cpv->setCustomPropertyId($cp_id);
    }
    $cpv->setValue($value);
    $cpv->save();

    return $value;
}


/**
 * Updates `searchable_objects` for a single CP after inline save, so the value
 * stays searchable. Skips when the object is not searchable or the CP type is
 * not indexable (file, image, …).
 */
function inline_cp_update_search_index($object, $custom_property, $value) {
    $not_searchable_types = CustomProperties::instance()->getNonSearchableColumnTypes();
    if (!$object->isSearchable() || in_array($custom_property->getType(), $not_searchable_types)) {
        return;
    }

    if (is_array($value) && $custom_property->getType() == 'amount') {
        $value = array_var($value, 'amount', '');
    } else if (is_array($value)) {
        $value = implode(', ', $value);
    }

    DB::execute(
        "INSERT INTO " . TABLE_PREFIX . "searchable_objects (rel_object_id, column_name, content)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE content = VALUES(content)",
        $object->getId(), $custom_property->getName(), $value
    );
}
