<?php

/**
 * Multi object-type field selector for notification information blocks.
 * Stores JSON: {"task":"name,due_date,...","milestone":"name,..."}
 */
class NotificationFieldsConfigHandler extends ConfigHandler {

	/**
	 * Core fields recommended for notification emails, by object type.
	 * Hardcoded curated list (not from Property Groups / install config):
	 * the fields we usually want visible in notification emails.
	 *
	 * @return array<object_type_name, string[]>
	 */
	protected function getRecommendedFieldIds() {
		return array(
			'task' => array('name', 'assigned_to_contact_id', 'assigned_to', 'due_date', 'status', 'priority', 'classifications', 'description', 'text'),
			'milestone' => array('name', 'due_date', 'status', 'classifications', 'description', 'text'),
			'event' => array('name', 'start', 'start_date', 'duration', 'classifications', 'description', 'text'),
			'message' => array('name', 'classifications', 'description', 'text'),
			'weblink' => array('name', 'classifications', 'description', 'text'),
			'file' => array('name', 'classifications', 'description', 'text'),
			'contact' => array('name', 'classifications'),
			'company' => array('name', 'classifications'),
			'timeslot' => array('name', 'classifications', 'description', 'text'),
			'mail' => array('name', 'classifications'),
		);
	}

	protected function getExtraFields($object_type_name) {
		$extras = array(
			array('id' => 'classifications', 'name' => lang('classifications'), 'type' => 'text'),
		);
		if ($object_type_name === 'task') {
			$extras[] = array('id' => 'status', 'name' => lang('status'), 'type' => 'text');
			$extras[] = array('id' => 'priority', 'name' => lang('priority'), 'type' => 'text');
			$extras[] = array('id' => 'description', 'name' => lang('description'), 'type' => 'text');
		}
		if (in_array($object_type_name, array('milestone', 'message', 'weblink', 'file', 'event'))) {
			$extras[] = array('id' => 'description', 'name' => lang('description'), 'type' => 'text');
		}
		return $extras;
	}

	/**
	 * Manageable dimensions allowed for an object type (for classification sub-options).
	 *
	 * @param int $object_type_id
	 * @return Dimension[]
	 */
	protected function getClassificationDimensionsForObjectType($object_type_id) {
		$object_type_id = (int) $object_type_id;
		$all_dimensions = Dimensions::getAllowedDimensions($object_type_id);
		Hook::fire('allowed_dimensions_in_member_selector', array('ot' => $object_type_id), $all_dimensions);
		$result = array();
		$seen = array();
		foreach ($all_dimensions as $dim_row) {
			$dim_id = (int) array_var($dim_row, 'dimension_id', array_var($dim_row, 'id'));
			if ($dim_id <= 0 || isset($seen[$dim_id])) {
				continue;
			}
			$dimension = Dimensions::getDimensionById($dim_id);
			if (!$dimension instanceof Dimension || !$dimension->getIsManageable()) {
				continue;
			}
			$seen[$dim_id] = true;
			$result[] = $dimension;
		}
		usort($result, function ($a, $b) {
			return strcmp($a->getName(), $b->getName());
		});
		return $result;
	}

	/**
	 * Nested dimension checkboxes under Classifications.
	 *
	 * @param string $genid
	 * @param string $control_name
	 * @param string $ot_name
	 * @param int $ot_id
	 * @param array $selected_for_ot
	 * @return string
	 */
	protected function renderClassificationDimensionOptions($genid, $control_name, $ot_name, $ot_id, $selected_for_ot) {
		$dimensions = $this->getClassificationDimensionsForObjectType($ot_id);
		if (!count($dimensions)) {
			return '';
		}

		$any_dim_selected = false;
		foreach ($selected_for_ot as $selected_id) {
			if (str_starts_with((string) $selected_id, 'classification_dim_')) {
				$any_dim_selected = true;
				break;
			}
		}

		$out = '<div class="nfc-class-dims" data-ot="'.clean($ot_name).'">';
		$hint = $any_dim_selected
			? lang('notification fields classification dims hint')
			: lang('notification fields classification dims hint all');
		$out .= '<div class="nfc-class-dims__hint">'.clean($hint).'</div>';
		$out .= '<div class="nfc-class-dims__grid">';
		foreach ($dimensions as $dimension) {
			$field_id = 'classification_dim_'.$dimension->getId();
			$input_id = $genid.'_'.$control_name.'_'.$ot_name.'_'.$field_id;
			$input_name = $control_name.'['.$ot_name.']['.$field_id.']';
			// Explicit selection only. Classifications on + no dims stored ⇒ all dims at runtime.
			$checked = $this->isFieldSelected($field_id, $selected_for_ot);
			$label_text = $dimension->getName();
			$search_text = strtolower($label_text.' '.$field_id);
			$out .= '<label class="nfc-field nfc-field--dim'.($checked ? ' is-checked' : '').'" data-search="'.clean($search_text).'" for="'.$input_id.'">';
			$out .= checkbox_field($input_name, $checked, array(
				'id' => $input_id,
				'class' => 'nfc-class-dim-cb',
				'onchange' => "og.nfcOnClassificationDimChange(this, '".$genid."', '".$ot_name."')",
			));
			$out .= '<span class="nfc-field__body"><span class="nfc-field__label">'.clean($label_text).'</span></span>';
			$out .= '</label>';
		}
		$out .= '</div></div>';
		return $out;
	}

	/**
	 * Alias map so the same semantic field is only shown once (e.g. text/description).
	 *
	 * @return array<field_id, canonical_id>
	 */
	protected function getFieldAliasMap() {
		return array(
			'text' => 'description',
			'description' => 'description',
			'assigned_to' => 'assigned_to_contact_id',
			'assigned_to_contact_id' => 'assigned_to_contact_id',
			'start' => 'start_date',
			'start_date' => 'start_date',
		);
	}

	protected function canonicalizeFieldId($field_id) {
		$map = $this->getFieldAliasMap();
		$id = (string) $field_id;
		return isset($map[$id]) ? $map[$id] : $id;
	}

	protected function isFieldSelected($field_id, $selected_for_ot) {
		$canonical = $this->canonicalizeFieldId($field_id);
		foreach ((array) $selected_for_ot as $selected_id) {
			if ($this->canonicalizeFieldId($selected_id) === $canonical) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Collapse aliases into one property per canonical id.
	 *
	 * @param array $properties
	 * @return array
	 */
	protected function dedupeProperties($properties) {
		$by_canonical = array();
		foreach ($properties as $prop) {
			$id = (string) array_var($prop, 'id', '');
			if ($id === '') {
				continue;
			}
			$canonical = $this->canonicalizeFieldId($id);
			$prop['id'] = $canonical;
			if (!isset($by_canonical[$canonical])) {
				$by_canonical[$canonical] = $prop;
				continue;
			}
			// Prefer a friendlier display name if the first one looks like a raw id.
			$existing_name = trim((string) array_var($by_canonical[$canonical], 'name', ''));
			$new_name = trim((string) array_var($prop, 'name', ''));
			if (($existing_name === '' || $existing_name === $canonical) && $new_name !== '') {
				$by_canonical[$canonical]['name'] = $new_name;
			}
		}
		return array_values($by_canonical);
	}

	protected function getConfigurableObjectTypes() {
		Env::useHelper('notification_information');
		$names = array_keys(notification_information_default_fields());
		$types = array();
		foreach ($names as $name) {
			$ot = ObjectTypes::instance()->findByName($name);
			if ($ot instanceof ObjectType) {
				$types[] = $ot;
			}
		}
		return $types;
	}

	/**
	 * Map property_id → property group meta for an object type (from advanced_core).
	 * Keys include both raw ids (due_date, 12) and cp_12 forms.
	 *
	 * @param int $object_type_id
	 * @return array<string, array{key:string,label:string,order:int}>
	 */
	protected function getPropertyGroupMapForObjectType($object_type_id) {
		static $cache = array();
		$object_type_id = (int) $object_type_id;
		if (isset($cache[$object_type_id])) {
			return $cache[$object_type_id];
		}

		$map = array();
		if (!Plugins::instance()->isActivePlugin('advanced_core') || !class_exists('PropertyGroups')) {
			return $cache[$object_type_id] = $map;
		}

		$grouped = PropertyGroups::getAllPropertiesGroupedByPropertyGroup($object_type_id);
		if (!is_array($grouped)) {
			return $cache[$object_type_id] = $map;
		}

		foreach ($grouped as $group) {
			$key = 'pg_' . array_var($group, 'id');
			$meta = array(
				'key' => $key,
				'label' => (string) array_var($group, 'name', $key),
				'order' => (int) array_var($group, 'order', 999),
			);
			foreach ((array) array_var($group, 'properties', array()) as $gp) {
				$pid = (string) array_var($gp, 'property_id', '');
				if ($pid === '') {
					continue;
				}
				$map[$pid] = $meta;
				if (ctype_digit($pid)) {
					$map['cp_' . $pid] = $meta;
				} elseif (str_starts_with($pid, 'cp_')) {
					$map[substr($pid, 3)] = $meta;
				}
				// Alias map so assigned_to / text / start match PG ids
				$canonical = $this->canonicalizeFieldId($pid);
				if ($canonical !== $pid) {
					$map[$canonical] = $meta;
				}
			}
		}

		return $cache[$object_type_id] = $map;
	}

	/**
	 * Bucket a property into a UI group.
	 * Recommended first; then the install's Property Group; else Other.
	 *
	 * @param array $prop
	 * @param string $object_type_name
	 * @param int $object_type_id
	 * @return string group key
	 */
	protected function classifyPropertyGroup($prop, $object_type_name, $object_type_id = 0) {
		$id = $this->canonicalizeFieldId(array_var($prop, 'id', ''));

		$recommended = array_var($this->getRecommendedFieldIds(), $object_type_name, array());
		$recommended_canonical = array();
		foreach ($recommended as $rec_id) {
			$recommended_canonical[$this->canonicalizeFieldId($rec_id)] = true;
		}
		if (isset($recommended_canonical[$id])) {
			return 'recommended';
		}

		$pg_map = $this->getPropertyGroupMapForObjectType($object_type_id);
		$candidates = array($id, (string) array_var($prop, 'id', ''));
		if (str_starts_with($id, 'cp_')) {
			$candidates[] = substr($id, 3);
		}
		foreach ($candidates as $candidate) {
			if ($candidate !== '' && isset($pg_map[$candidate])) {
				return $pg_map[$candidate]['key'];
			}
		}

		return 'other';
	}

	/**
	 * Base groups + dynamic property groups for this object type.
	 *
	 * @param int $object_type_id
	 * @return array<string, array{label:string,hint:string,order:int}>
	 */
	protected function getGroupMetaForObjectType($object_type_id) {
		$meta = array(
			'recommended' => array(
				'label' => lang('notification fields group recommended'),
				'hint' => lang('notification fields group recommended desc'),
				'order' => -1000,
			),
			'other' => array(
				'label' => lang('notification fields group other'),
				'hint' => '',
				'order' => 100000,
			),
		);

		$pg_map = $this->getPropertyGroupMapForObjectType($object_type_id);
		foreach ($pg_map as $pg) {
			$key = $pg['key'];
			if (!isset($meta[$key])) {
				$meta[$key] = array(
					'label' => $pg['label'],
					'hint' => '',
					'order' => $pg['order'],
				);
			}
		}

		uasort($meta, function ($a, $b) {
			$ao = isset($a['order']) ? (int) $a['order'] : 0;
			$bo = isset($b['order']) ? (int) $b['order'] : 0;
			if ($ao === $bo) {
				return strcmp($a['label'], $b['label']);
			}
			return $ao - $bo;
		});

		return $meta;
	}

	protected function getGroupMeta() {
		// Kept for compatibility; prefer getGroupMetaForObjectType().
		return array(
			'recommended' => array(
				'label' => lang('notification fields group recommended'),
				'hint' => lang('notification fields group recommended desc'),
			),
			'other' => array(
				'label' => lang('notification fields group other'),
				'hint' => '',
			),
		);
	}

	function render($control_name) {
		Env::useHelper('notification_information');
		$genid = gen_id();
		$selected = $this->getValue();
		if (!is_array($selected)) {
			$selected = array();
		}

		$out = '<div class="nfc-fields" id="'.$genid.'_nfc">';
		$out .= '<div class="nfc-fields__toolbar">';
		$out .= '<div class="nfc-fields__search-field">';
		$out .= '<input type="search" class="nfc-fields__search" placeholder="'.clean(lang('notification fields search placeholder')).'" oninput="og.nfcFilterFields(\''.$genid.'\', this.value)" aria-label="'.clean(lang('notification fields search placeholder')).'" />';
		$out .= '</div>';
		$out .= '<div class="nfc-fields__toolbar-actions">';
		$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcExpandAll(\''.$genid.'\', true); return false;">'.clean(lang('expand all')).'</a>';
		$out .= '<span class="nfc-fields__sep" aria-hidden="true">·</span>';
		$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcExpandAll(\''.$genid.'\', false); return false;">'.clean(lang('collapse all')).'</a>';
		$out .= '</div></div>';

		foreach ($this->getConfigurableObjectTypes() as $object_type) {
			$ot_name = $object_type->getName();
			$ot_id = $object_type->getId();
			$group_meta = $this->getGroupMetaForObjectType($ot_id);
			$selected_for_ot = array();
			if (isset($selected[$ot_name])) {
				if (is_array($selected[$ot_name])) {
					$selected_for_ot = $selected[$ot_name];
				} else {
					$selected_for_ot = array_filter(array_map('trim', explode(',', (string) $selected[$ot_name])));
				}
			}

			$properties = $object_type->getObjectTypeProperties(true, true, false);
			$property_ids = array();
			foreach ($properties as $prop) {
				$property_ids[$this->canonicalizeFieldId($prop['id'])] = true;
			}
			foreach ($this->getExtraFields($ot_name) as $extra) {
				$canonical_extra = $this->canonicalizeFieldId($extra['id']);
				if (!isset($property_ids[$canonical_extra])) {
					$extra['id'] = $canonical_extra;
					$properties[] = $extra;
					$property_ids[$canonical_extra] = true;
				}
			}
			$properties = $this->dedupeProperties($properties);

			$grouped = array();
			foreach ($group_meta as $group_key => $meta) {
				$grouped[$group_key] = array();
			}
			foreach ($properties as $prop) {
				$group_key = $this->classifyPropertyGroup($prop, $ot_name, $ot_id);
				if (!isset($grouped[$group_key])) {
					$group_key = 'other';
				}
				$grouped[$group_key][] = $prop;
			}
			foreach ($grouped as $group_key => &$group_props) {
				usort($group_props, function ($a, $b) {
					return strcmp($a['name'], $b['name']);
				});
			}
			unset($group_props);

			$selected_count = 0;
			$counted = array();
			foreach ($grouped as $group_props) {
				foreach ($group_props as $prop) {
					$canonical = $this->canonicalizeFieldId($prop['id']);
					if (isset($counted[$canonical])) {
						continue;
					}
					if ($this->isFieldSelected($prop['id'], $selected_for_ot)) {
						$selected_count++;
						$counted[$canonical] = true;
					}
				}
			}

			$panel_id = $genid.'_ot_'.$ot_name;

			$out .= '<section class="nfc-ot" data-ot="'.$ot_name.'" id="'.$panel_id.'">';
			$out .= '<header class="nfc-ot__header" onclick="og.nfcTogglePanel(\''.$panel_id.'\')">';
			$out .= '<div class="nfc-ot__title">';
			$out .= '<span class="nfc-ot__chevron" aria-hidden="true"></span>';
			$out .= '<strong>'.clean($object_type->getObjectTypeName()).'</strong>';
			$out .= '<span class="nfc-ot__count" data-count-for="'.$ot_name.'">'.$selected_count.' '.clean(lang('selected')).'</span>';
			$out .= '</div>';
			$out .= '<div class="nfc-ot__actions" onclick="event.stopPropagation();">';
			$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcToggleOt(\''.$genid.'\', \''.$ot_name.'\', true); return false;">'.clean(lang('select all')).'</a>';
			$out .= '<span class="nfc-fields__sep" aria-hidden="true">·</span>';
			$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcToggleOt(\''.$genid.'\', \''.$ot_name.'\', false); return false;">'.clean(lang('select none')).'</a>';
			$out .= '</div></header>';

			$out .= '<div class="nfc-ot__body">';
			foreach ($grouped as $group_key => $group_props) {
				if (!count($group_props)) {
					continue;
				}
				$group_selected = 0;
				foreach ($group_props as $prop) {
					if ($this->isFieldSelected($prop['id'], $selected_for_ot)) {
						$group_selected++;
					}
				}
				$group_id = $panel_id.'_'.$group_key;
				$meta = $group_meta[$group_key];

				$out .= '<div class="nfc-group" data-group="'.$group_key.'" id="'.$group_id.'">';
				$out .= '<div class="nfc-group__header" onclick="og.nfcTogglePanel(\''.$group_id.'\')">';
				$out .= '<div class="nfc-group__title">';
				$out .= '<span class="nfc-ot__chevron" aria-hidden="true"></span>';
				$out .= '<span>'.clean($meta['label']).'</span>';
				$out .= '<span class="nfc-group__count">'.$group_selected.'/'.count($group_props).'</span>';
				$out .= '</div>';
				$out .= '<div class="nfc-group__actions" onclick="event.stopPropagation();">';
				$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcToggleGroup(\''.$group_id.'\', true); return false;">'.clean(lang('all')).'</a>';
				$out .= '<span class="nfc-fields__sep" aria-hidden="true">·</span>';
				$out .= '<a href="#" class="nfc-fields__link" onclick="og.nfcToggleGroup(\''.$group_id.'\', false); return false;">'.clean(lang('select none')).'</a>';
				$out .= '</div></div>';

				if (!empty($meta['hint'])) {
					$out .= '<div class="nfc-group__hint">'.clean($meta['hint']).'</div>';
				}

				$out .= '<div class="nfc-group__body"><div class="nfc-fields__grid">';
				foreach ($group_props as $prop) {
					$field_id = $this->canonicalizeFieldId($prop['id']);
					$input_id = $genid.'_'.$control_name.'_'.$ot_name.'_'.$field_id;
					$input_name = $control_name.'['.$ot_name.']['.$field_id.']';
					$checked = $this->isFieldSelected($field_id, $selected_for_ot);
					$search_text = strtolower($prop['name'].' '.$field_id);

					$is_classifications = ($field_id === 'classifications');
					if ($is_classifications) {
						$out .= '<div class="nfc-field-row nfc-field-row--classifications'.($checked ? ' is-enabled' : '').'">';
					}

					$out .= '<label class="nfc-field'.($is_classifications ? ' nfc-field--classifications' : '').($checked ? ' is-checked' : '').'" data-search="'.clean($search_text).'" for="'.$input_id.'">';
					$out .= checkbox_field($input_name, $checked, array(
						'id' => $input_id,
						'onchange' => "og.nfcOnFieldChange(this, '".$genid."', '".$ot_name."')",
						'data-field-id' => $field_id,
					));
					$out .= '<span class="nfc-field__body">';
					$out .= '<span class="nfc-field__label">'.clean($prop['name']).'</span>';
					if ($is_classifications) {
						$out .= '<span class="nfc-field__help">'.clean(lang('notification fields classifications toggle help')).'</span>';
					}
					$out .= '</span></label>';
					if ($is_classifications) {
						$out .= $this->renderClassificationDimensionOptions($genid, $control_name, $ot_name, $ot_id, $selected_for_ot);
						$out .= '</div>';
					}
				}
				$out .= '</div></div></div>';
			}

			$out .= '<input type="hidden" name="'.$control_name.'['.$ot_name.'][0]" value=" ">';
			$out .= '</div></section>';
		}

		$out .= '</div>';
		$out .= $this->getClientScript();
		return $out;
	}

	protected function getClientScript() {
		$selected_word = addslashes(lang('selected'));
		return '<script type="text/javascript">
if (!og.nfcTogglePanel) {
	og.nfcTogglePanel = function(id) {
		var el = document.getElementById(id);
		if (el) el.classList.toggle("is-open");
	};
	og.nfcExpandAll = function(genid, open) {
		var root = document.getElementById(genid + "_nfc");
		if (!root) return;
		root.querySelectorAll(".nfc-ot, .nfc-group").forEach(function(el) {
			if (open) el.classList.add("is-open"); else el.classList.remove("is-open");
		});
	};
	og.nfcToggleOt = function(genid, ot, checked) {
		var root = document.getElementById(genid + "_nfc");
		if (!root) return;
		var panel = root.querySelector(\'.nfc-ot[data-ot="\' + ot + \'"]\');
		if (!panel) return;
		panel.querySelectorAll(\'input[type="checkbox"]\').forEach(function(cb) {
			cb.checked = checked;
			var label = cb.closest(".nfc-field");
			if (label) label.classList.toggle("is-checked", checked);
		});
		panel.querySelectorAll(".nfc-field-row--classifications").forEach(function(row) {
			row.classList.toggle("is-enabled", checked);
		});
		og.nfcRefreshCounts(genid, ot);
	};
	og.nfcToggleGroup = function(groupId, checked) {
		var group = document.getElementById(groupId);
		if (!group) return;
		group.querySelectorAll(\'input[type="checkbox"]\').forEach(function(cb) {
			cb.checked = checked;
			var label = cb.closest(".nfc-field");
			if (label) label.classList.toggle("is-checked", checked);
		});
		group.querySelectorAll(".nfc-field-row--classifications").forEach(function(row) {
			row.classList.toggle("is-enabled", checked);
		});
		var panel = group.closest(".nfc-ot");
		var root = group.closest(".nfc-fields");
		if (panel && root) {
			og.nfcRefreshCounts(root.id.replace(/_nfc$/, ""), panel.getAttribute("data-ot"));
		} else {
			og.nfcRefreshGroupCount(group);
		}
	};
	og.nfcOnFieldChange = function(cb, genid, ot) {
		var label = cb.closest(".nfc-field");
		if (label) label.classList.toggle("is-checked", cb.checked);
		var group = cb.closest(".nfc-group");
		if (group) og.nfcRefreshGroupCount(group);
		og.nfcRefreshCounts(genid, ot);
		if (cb.getAttribute("data-field-id") === "classifications") {
			var row = cb.closest(".nfc-field-row--classifications");
			if (row) row.classList.toggle("is-enabled", cb.checked);
			if (!cb.checked) {
				var panel = cb.closest(".nfc-ot");
				if (panel) {
					panel.querySelectorAll(".nfc-class-dim-cb").forEach(function(dimCb) {
						dimCb.checked = false;
						var dimLabel = dimCb.closest(".nfc-field");
						if (dimLabel) dimLabel.classList.remove("is-checked");
					});
				}
			}
		}
	};
	og.nfcOnClassificationDimChange = function(cb, genid, ot) {
		var label = cb.closest(".nfc-field");
		if (label) label.classList.toggle("is-checked", cb.checked);
		var panel = cb.closest(".nfc-ot");
		if (panel && cb.checked) {
			var parent = panel.querySelector(\'input[data-field-id="classifications"]\');
			if (parent && !parent.checked) {
				parent.checked = true;
				var parentLabel = parent.closest(".nfc-field");
				if (parentLabel) parentLabel.classList.add("is-checked");
				var row = parent.closest(".nfc-field-row--classifications");
				if (row) row.classList.add("is-enabled");
			}
		}
		og.nfcRefreshCounts(genid, ot);
	};
	og.nfcRefreshGroupCount = function(group) {
		if (!group) return;
		var boxes = group.querySelectorAll(\'input[type="checkbox"]:not(.nfc-class-dim-cb)\');
		var selected = 0;
		boxes.forEach(function(cb) { if (cb.checked) selected++; });
		var badge = group.querySelector(".nfc-group__count");
		if (badge) badge.textContent = selected + "/" + boxes.length;
	};
	og.nfcRefreshCounts = function(genid, ot) {
		var root = document.getElementById(genid + "_nfc");
		if (!root) return;
		var panel = root.querySelector(\'.nfc-ot[data-ot="\' + ot + \'"]\');
		if (!panel) return;
		var selected = 0;
		panel.querySelectorAll(\'input[type="checkbox"]:not(.nfc-class-dim-cb)\').forEach(function(cb) { if (cb.checked) selected++; });
		var badge = panel.querySelector(\'[data-count-for="\' + ot + \'"]\');
		if (badge) badge.textContent = selected + " '.$selected_word.'";
		panel.querySelectorAll(".nfc-group").forEach(og.nfcRefreshGroupCount);
	};
	og.nfcFilterFields = function(genid, query) {
		var root = document.getElementById(genid + "_nfc");
		if (!root) return;
		query = (query || "").toLowerCase().trim();
		root.querySelectorAll(".nfc-field").forEach(function(field) {
			if (field.classList.contains("nfc-field--dim")) {
				return;
			}
			var hay = field.getAttribute("data-search") || "";
			var match = !query || hay.indexOf(query) !== -1;
			var row = field.closest(".nfc-field-row--classifications");
			if (row) {
				row.style.display = match ? "" : "none";
			} else {
				field.style.display = match ? "" : "none";
			}
			if (match && query) {
				var group = field.closest(".nfc-group");
				var ot = field.closest(".nfc-ot");
				if (group) group.classList.add("is-open");
				if (ot) ot.classList.add("is-open");
			}
		});
	};
}
</script>';
	}

	function rawToPhp($value) {
		if (is_array($value)) {
			return $value;
		}
		$defaults = array(
			'task' => 'name,assigned_to_contact_id,due_date,status,priority,classifications,description',
			'milestone' => 'name,due_date,status,classifications,description',
			'event' => 'name,start,duration,classifications,description',
			'message' => 'name,classifications,description',
			'weblink' => 'name,classifications,description',
			'file' => 'name,classifications,description',
			'contact' => 'name,classifications',
			'company' => 'name,classifications',
			'timeslot' => 'name,classifications,description',
			'mail' => 'name,classifications',
		);
		if (!is_string($value) || trim($value) === '') {
			return $defaults;
		}
		$decoded = json_decode($value, true);
		if (!is_array($decoded)) {
			return $defaults;
		}
		foreach ($decoded as $ot_name => $fields) {
			if ($fields === '__none__') {
				// Intentional empty selection for the admin UI (no checkboxes checked).
				$decoded[$ot_name] = array();
			}
		}
		return $decoded;
	}

	function phpToRaw($value) {
		$defaults = array(
			'task' => 'name,assigned_to_contact_id,due_date,status,priority,classifications,description',
			'milestone' => 'name,due_date,status,classifications,description',
			'event' => 'name,start,duration,classifications,description',
			'message' => 'name,classifications,description',
			'weblink' => 'name,classifications,description',
			'file' => 'name,classifications,description',
			'contact' => 'name,classifications',
			'company' => 'name,classifications',
			'timeslot' => 'name,classifications,description',
			'mail' => 'name,classifications',
		);
		if (!is_array($value)) {
			return is_string($value) ? $value : json_encode($defaults);
		}

		$result = array();
		foreach ($value as $ot_name => $fields) {
			if (!is_array($fields)) {
				$result[$ot_name] = (string) $fields;
				continue;
			}
			unset($fields[0]);
			$keys = array();
			$seen = array();
			$has_classifications = false;
			$has_class_dim = false;
			foreach (array_keys($fields) as $k) {
				if ((string) $k === '0' || trim((string) $k) === '') {
					continue;
				}
				$canonical = $this->canonicalizeFieldId($k);
				if (isset($seen[$canonical])) {
					continue;
				}
				$seen[$canonical] = true;
				$keys[] = $canonical;
				if ($canonical === 'classifications') {
					$has_classifications = true;
				}
				if (str_starts_with($canonical, 'classification_dim_')) {
					$has_class_dim = true;
				}
			}
			if ($has_class_dim && !$has_classifications) {
				array_unshift($keys, 'classifications');
			}
			// Distinguish "not configured" (absent key) from "intentionally empty".
			$result[$ot_name] = count($keys) === 0 ? '__none__' : implode(',', $keys);
		}
		return json_encode($result);
	}
}
