<?php
/**
 * Test for DataManager::clearAllCaches() (environment/classes/dataaccess/DataManager.class.php).
 *
 * Every manager keeps the objects it loads in an item cache, so a loop that walks a whole table one
 * object at a time (the advanced_billing 45->46 and 47->48 data changes over 300k tasks and 160k
 * timeslots) retains every object until memory_limit is exhausted, no matter how the loop unsets
 * its local variables. Such loops need one call that empties the cache of every manager at once.
 *
 * Run from the Feng root:  php test/data_manager_clear_all_caches_test.php
 *
 * Boots Feng in console mode using config/config.php and only reads existing rows; nothing is
 * written. Exit code 0 when every check passes.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
chdir(dirname(__FILE__) . '/..');
define('CONSOLE_MODE', true);
define('PUBLIC_FOLDER', 'public');

include 'init.php';
session_commit();
restore_error_handler();
restore_exception_handler();

$failures = 0;
$checks = 0;
function check($condition, $message) {
	global $failures, $checks;
	$checks++;
	if (!$condition) {
		$failures++;
		echo "  FAIL: $message\n";
	}
}

// one row per manager, loaded through the manager so it lands in that manager's cache
$loaded = array();
$object_type = ObjectTypes::instance()->findOne();
if ($object_type instanceof ObjectType) {
	$loaded['ObjectTypes'] = ObjectTypes::instance()->findById($object_type->getId());
}
$contact = Contacts::instance()->findOne();
if ($contact instanceof Contact) {
	$loaded['Contacts'] = Contacts::instance()->findById($contact->getId());
}
if (count($loaded) < 2) {
	echo "Need at least an object type and a contact in the database to run this test.\n";
	exit(1);
}

echo "- objects loaded by id are held in their manager's cache\n";
foreach ($loaded as $manager_class => $object) {
	$cached = $manager_class::instance()->getCachedItem($object->getId());
	check($cached === $object, "$manager_class did not cache the object it loaded");
}

echo "- clearAllCaches empties the cache of every manager at once\n";
try {
	DataManager::clearAllCaches();
	foreach ($loaded as $manager_class => $object) {
		$cached = $manager_class::instance()->getCachedItem($object->getId());
		check($cached === null, "$manager_class still returns a cached object after clearAllCaches");
	}
} catch (Throwable $t) {
	$checks++;
	$failures++;
	echo "  ERROR: " . get_class($t) . ': ' . $t->getMessage() . "\n";
}

echo "- managers keep working after their caches were cleared\n";
foreach ($loaded as $manager_class => $object) {
	$reloaded = $manager_class::instance()->findById($object->getId());
	check($reloaded instanceof DataObject && $reloaded->getId() == $object->getId(), "$manager_class could not reload the object after the clear");
}

echo "\n" . ($checks - $failures) . " of $checks checks passed\n";
exit($failures > 0 ? 1 : 0);
