<?php

/**
 * Groups task templates (COTemplate) by category label for UI lists.
 *
 * @param COTemplate[] $templates
 * @return array<string, COTemplate[]> Label => templates (templates sorted by name within each group)
 */
function group_task_templates_by_category_label($templates) {
	if (!is_array($templates)) {
		return array();
	}
	if (!function_exists('checkTableExists') || !checkTableExists(TABLE_PREFIX . 'task_template_categories')
		|| !check_column_exists(TABLE_PREFIX . 'templates', 'task_template_category_id')) {
		return array(lang('task templates uncategorized') => $templates);
	}

	$categories = TaskTemplateCategories::instance()->findAll(array('order' => '`sort_order` ASC, `name` ASC'));
	$labels_by_id = array(0 => lang('task templates uncategorized'));
	foreach ($categories as $c) {
		$labels_by_id[$c->getId()] = $c->getName();
	}

	$buckets = array();
	foreach ($labels_by_id as $label) {
		$buckets[$label] = array();
	}

	foreach ($templates as $tt) {
		if (!($tt instanceof COTemplate)) {
			continue;
		}
		$cid = $tt->getTaskTemplateCategoryId();
		$label = isset($labels_by_id[$cid]) ? $labels_by_id[$cid] : $labels_by_id[0];
		$buckets[$label][] = $tt;
	}

	foreach ($buckets as $lbl => &$items) {
		usort($items, function ($a, $b) {
			return strcasecmp($a->getObjectName(), $b->getObjectName());
		});
	}
	unset($items);

	return $buckets;
}

/**
 * Map category id => display metadata for batch lookups.
 *
 * @return array<int, array{name: string, sort_order: int, position: int}>
 */
function task_template_category_info_map() {
	if (!function_exists('checkTableExists') || !checkTableExists(TABLE_PREFIX . 'task_template_categories')
		|| !check_column_exists(TABLE_PREFIX . 'templates', 'task_template_category_id')) {
		return array();
	}
	$map = array();
	$categories = TaskTemplateCategories::instance()->findAll(array('order' => '`sort_order` ASC, `name` ASC'));
	$position = 0;
	foreach ($categories as $c) {
		$map[$c->getId()] = array(
			'name' => $c->getName(),
			'sort_order' => (int) $c->getSortOrder(),
			'position' => $position++,
		);
	}
	return $map;
}

/**
 * Map category id => display name for batch lookups.
 *
 * @return array<int, string>
 */
function task_template_category_name_map() {
	$map = array();
	foreach (task_template_category_info_map() as $id => $info) {
		$map[$id] = $info['name'];
	}
	return $map;
}
