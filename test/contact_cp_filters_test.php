<?php

/**
 * Standalone assertions for the contact custom property type filter helpers.
 * Run: php test/contact_cp_filters_test.php
 */

$root = dirname(__DIR__);
include_once $root . '/environment/constants.php';
include_once $root . '/application/helpers/custom_properties.php';

$failures = 0;
$checks = 0;

function check($label, $expected, $actual) {
	global $failures, $checks;
	$checks++;
	if ($expected === $actual) return;
	$failures++;
	echo "FAIL: $label\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n";
}

// --- constants -------------------------------------------------------------
check('companies bit', 1, CONTACT_CP_FILTER_COMPANIES);
check('contacts bit', 2, CONTACT_CP_FILTER_CONTACTS);
check('users bit', 4, CONTACT_CP_FILTER_USERS);

// --- get_contact_cp_filter_types -------------------------------------------
check('empty value is no restriction', array(), get_contact_cp_filter_types(''));
check('null value is no restriction', array(), get_contact_cp_filter_types(null));
check('legacy contacts', array('contacts'), get_contact_cp_filter_types('contacts'));
check('legacy companies', array('companies'), get_contact_cp_filter_types('companies'));
check('users only', array('users'), get_contact_cp_filter_types('users'));
check('two tokens', array('companies', 'users'), get_contact_cp_filter_types('companies,users'));
check('canonical order is restored', array('companies', 'users'), get_contact_cp_filter_types('users,companies'));
check('all three collapse to no restriction', array(), get_contact_cp_filter_types('companies,contacts,users'));
check('whitespace is trimmed', array('companies', 'contacts'), get_contact_cp_filter_types(' companies , contacts '));
check('duplicates are dropped', array('users'), get_contact_cp_filter_types('users,users'));
check('unknown tokens are dropped', array('users'), get_contact_cp_filter_types('users,robots'));
check('only unknown tokens is no restriction', array(), get_contact_cp_filter_types('robots'));
check('empty segments are ignored', array('contacts'), get_contact_cp_filter_types(',contacts,'));

// --- contact_cp_filter_types_to_mask ---------------------------------------
check('no tokens is mask 0', 0, contact_cp_filter_types_to_mask(array()));
check('non array is mask 0', 0, contact_cp_filter_types_to_mask(null));
check('companies mask', 1, contact_cp_filter_types_to_mask(array('companies')));
check('contacts mask', 2, contact_cp_filter_types_to_mask(array('contacts')));
check('users mask', 4, contact_cp_filter_types_to_mask(array('users')));
check('companies plus users mask', 5, contact_cp_filter_types_to_mask(array('companies', 'users')));
check('all three is mask 0', 0, contact_cp_filter_types_to_mask(array('companies', 'contacts', 'users')));
check('unknown token contributes nothing', 0, contact_cp_filter_types_to_mask(array('robots')));

// --- get_contact_type_mask_sql_condition -----------------------------------
check('mask 0 has no condition', '', get_contact_type_mask_sql_condition(0));
check('negative mask has no condition', '', get_contact_type_mask_sql_condition(-1));
check('mask 7 has no condition', '', get_contact_type_mask_sql_condition(7));
check(
	'companies condition',
	'((`is_company` = 1))',
	get_contact_type_mask_sql_condition(1)
);
check(
	'contacts condition',
	'((`is_company` = 0 AND `user_type` = 0))',
	get_contact_type_mask_sql_condition(2)
);
check(
	'users condition',
	'((`is_company` = 0 AND `user_type` > 0 AND `disabled` = 0))',
	get_contact_type_mask_sql_condition(4)
);
check(
	'companies plus users condition',
	'((`is_company` = 1) OR (`is_company` = 0 AND `user_type` > 0 AND `disabled` = 0))',
	get_contact_type_mask_sql_condition(5)
);
check(
	'aliased condition',
	'((c2.`is_company` = 1) OR (c2.`is_company` = 0 AND c2.`user_type` > 0 AND c2.`disabled` = 0))',
	get_contact_type_mask_sql_condition(5, 'c2')
);
check(
	'disabled only constrains the users group',
	'((`is_company` = 1) OR (`is_company` = 0 AND `user_type` = 0))',
	get_contact_type_mask_sql_condition(CONTACT_CP_FILTER_COMPANIES | CONTACT_CP_FILTER_CONTACTS)
);
check('mask is cast to int', '((`is_company` = 1))', get_contact_type_mask_sql_condition('1'));

// --- end to end: stored value to SQL ---------------------------------------
check(
	'legacy contacts value produces the legacy condition',
	'((`is_company` = 0 AND `user_type` = 0))',
	get_contact_type_mask_sql_condition(contact_cp_filter_types_to_mask(get_contact_cp_filter_types('contacts')))
);
check(
	'legacy companies value produces the legacy condition',
	'((`is_company` = 1))',
	get_contact_type_mask_sql_condition(contact_cp_filter_types_to_mask(get_contact_cp_filter_types('companies')))
);
check(
	'empty value produces no condition',
	'',
	get_contact_type_mask_sql_condition(contact_cp_filter_types_to_mask(get_contact_cp_filter_types('')))
);

// --- get_contact_cp_type_filters -------------------------------------------
check(
	'contact cp with no restriction keeps include_companies',
	array('include_companies' => 1),
	get_contact_cp_type_filters('contact', '')
);
check(
	'contact cp with all groups keeps include_companies',
	array('include_companies' => 1),
	get_contact_cp_type_filters('contact', 'companies,contacts,users')
);
check(
	'legacy contacts value emits the mask',
	array('contact_type_mask' => 2),
	get_contact_cp_type_filters('contact', 'contacts')
);
check(
	'legacy companies value emits the mask',
	array('contact_type_mask' => 1),
	get_contact_cp_type_filters('contact', 'companies')
);
check(
	'combination emits the combined mask',
	array('contact_type_mask' => 5),
	get_contact_cp_type_filters('contact', 'companies,users')
);
check(
	'user cp type ignores the filter and keeps legacy behavior',
	array('include_companies' => 1),
	get_contact_cp_type_filters('user', 'contacts')
);
check(
	'user cp type with no filter keeps legacy behavior',
	array('include_companies' => 1),
	get_contact_cp_type_filters('user', '')
);

echo ($failures == 0 ? "OK" : "FAILED") . ": $checks checks, $failures failures\n";
exit($failures == 0 ? 0 : 1);
