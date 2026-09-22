<?php
/**
 * Regression test for the hierarchy walks of ProjectTask (application/models/project_tasks/ProjectTask.class.php):
 * getAllParents() walking up through parent_id and getAllSubTasks() walking down through the
 * tasks whose parent_id points at the current task.
 *
 * Neither walk had a cycle guard. A task that is its own parent, or a pair of tasks pointing at each
 * other, made them loop or recurse forever and grow until PHP ran out of memory; the advanced_billing
 * 45->46 data change hit both on real data and killed the whole plugin update run. Each walk must stop
 * as soon as it meets a task it has already visited, and still return the full chain or subtree for
 * well-formed tasks.
 *
 * Run from the Feng root:  php test/project_task_hierarchy_cycle_test.php
 *
 * Boots Feng in console mode using config/config.php: point it at a throwaway database. Fixture
 * tasks are inserted with ids in a reserved high range and deleted from a shutdown function
 * registered before Feng boots, so a fatal error cannot leave them behind. The cyclic cases run in
 * a child PHP process with a small memory_limit so a regression fails the test instead of killing it.
 * Exit code 0 when every check passes.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
chdir(dirname(__FILE__) . '/..');
define('CONSOLE_MODE', true);
define('PUBLIC_FOLDER', 'public');

// ids far above anything a real install uses (columns are int(10) unsigned, max 4294967295)
define('FIXTURE_ID_BASE', 4200000000);
define('FIXTURE_ID_MAX', 4200000100);

$is_worker = in_array('--worker', $argv);

/**
 * Removes every fixture row; runs only once. Registered as a shutdown function before Feng boots
 * because Feng's own shutdown function, registered by init.php, closes the database connection and
 * shutdown functions run in registration order.
 */
function cleanup_fixtures() {
	static $done = false;
	if ($done) return;
	$done = true;
	if (!class_exists('DB', false)) return; // the script died before Feng booted
	global $T;
	DB::execute("DELETE FROM `{$T}project_tasks` WHERE object_id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
	DB::execute("DELETE FROM `{$T}objects` WHERE id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
}
if (!$is_worker) {
	register_shutdown_function('cleanup_fixtures');
}

include 'init.php';
session_commit();
restore_error_handler();
restore_exception_handler();

$T = TABLE_PREFIX;

// the subtask id listing filters by the logged user's permissions; log the first user in the same
// way public/install/plugin-console.php does for plugin updates
$console_user = Contacts::instance()->findOne(array("conditions" => "user_type > 0", "order" => "user_type"));
if (!$console_user instanceof Contact) {
	echo "No user found in the database, cannot run the hierarchy walks.\n";
	exit(1);
}
CompanyWebsite::instance()->logUserIn($console_user, false, false);

// ---------------------------------------------------------------------------------------------
//  Worker mode: run one walk for one task inside a child process with a fixed memory_limit.
//  Usage: <this file> --worker <memory_limit> <task_id> <parents|subtasks>
// ---------------------------------------------------------------------------------------------
if ($is_worker) {
	$pos = array_search('--worker', $argv);
	$memory_limit = isset($argv[$pos + 1]) ? $argv[$pos + 1] : '64M';
	$task_id = isset($argv[$pos + 2]) ? (int)$argv[$pos + 2] : 0;
	$walk = isset($argv[$pos + 3]) ? $argv[$pos + 3] : 'parents';
	ini_set('memory_limit', $memory_limit); // after init.php so nothing at boot can raise it again
	$task = ProjectTasks::instance()->findById($task_id);
	if (!$task instanceof ProjectTask) {
		echo "task $task_id not found\n";
		exit(2);
	}
	switch ($walk) {
		case 'subtasks':
			$ids = array();
			foreach ($task->getAllSubTasks() as $other) $ids[] = $other->getId();
			echo "$walk=" . implode(',', $ids) . "\n";
			break;
		case 'subtask_ids':
			$ids = array_values($task->getAllSubtaskIdsInHierarchy());
			sort($ids);
			echo "$walk=" . implode(',', $ids) . "\n";
			break;
		case 'time_estimate':
			$task->calculateTotalTimeEstimate();
			echo "$walk=done\n";
			break;
		case 'depth_path':
			$task->updateDepthAndParentsPath($task->getParentId());
			echo "$walk=" . $task->getColumnValue('depth') . ":" . $task->getColumnValue('parents_path') . "\n";
			break;
		case 'percent_complete':
			$task->calculatePercentComplete();
			echo "$walk=done\n";
			break;
		case 'worked_time':
			$task->calculateAndSaveOverallTotalWorkedTime();
			echo "$walk=done\n";
			break;
		default:
			$ids = array();
			foreach ($task->getAllParents() as $other) $ids[] = $other->getId();
			echo "parents=" . implode(',', $ids) . "\n";
	}
	exit(0);
}

// ---------------------------------------------------------------------------------------------
//  Test harness
// ---------------------------------------------------------------------------------------------
$GLOBALS['failures'] = 0;
$GLOBALS['checks'] = 0;
function check($condition, $message) {
	$GLOBALS['checks']++;
	if (!$condition) {
		$GLOBALS['failures']++;
		echo "  FAIL: $message\n";
	}
}
function run_test($name, $fn) {
	echo "- $name\n";
	try {
		$fn();
	} catch (Throwable $t) {
		$GLOBALS['checks']++;
		$GLOBALS['failures']++;
		echo "  ERROR: " . get_class($t) . ': ' . $t->getMessage() . "\n" . $t->getTraceAsString() . "\n";
	}
}

/**
 * Inserts one task fixture (an objects row plus its project_tasks row). Only parent_id matters to
 * the code under test; the NOT NULL columns without defaults get neutral values.
 */
function insert_fixture_task($id, $parent_id, $time_estimate = 0) {
	global $T, $task_object_type_id;
	// the trashed/archived columns default to NULL on the table but the hierarchy queries filter on
	// = 0, so store what the ORM stores for a live object: 0 ids and zero dates
	DB::execute("INSERT INTO `{$T}objects` (`id`, `object_type_id`, `name`, `created_on`, `updated_on`, `trashed_by_id`, `archived_by_id`, `trashed_on`, `archived_on`)
		VALUES (" . intval($id) . ", " . intval($task_object_type_id) . ", 'hierarchy cycle fixture', NOW(), NOW(), 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00')");
	DB::execute("INSERT INTO `{$T}project_tasks` (`object_id`, `parent_id`, `time_estimate`, `due_date`, `start_date`, `completed_on`,
			`started_by_id`, `repeat_end`, `repeat_forever`, `repeat_d`, `repeat_m`, `repeat_y`, `repeat_generate_instances`)
		VALUES (" . intval($id) . ", " . intval($parent_id) . ", " . intval($time_estimate) . ", '2000-01-01 00:00:00', '2000-01-01 00:00:00', '2000-01-01 00:00:00',
			0, '2000-01-01 00:00:00', 0, 0, 0, 0, 0)");
}

function fixture_total_time_estimate($id) {
	global $T;
	$row = DB::executeOne("SELECT total_time_estimate FROM `{$T}project_tasks` WHERE object_id = " . intval($id));
	return $row ? (int)$row['total_time_estimate'] : null;
}

/**
 * Runs one walk for one task in a child PHP process and returns array(exit_code, stdout, stderr).
 * The child is killed after $timeout_seconds so a non-terminating walk fails the test instead of hanging it.
 */
function walk_in_child($walk, $task_id, $memory_limit = '64M', $timeout_seconds = 60) {
	$cmd = escapeshellarg(PHP_BINARY) . " -d memory_limit=" . escapeshellarg($memory_limit)
		. " " . escapeshellarg(__FILE__) . " --worker " . escapeshellarg($memory_limit) . " " . intval($task_id) . " " . escapeshellarg($walk);
	$pipes = array();
	$proc = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, ROOT);
	if (!is_resource($proc)) {
		throw new Exception("Could not start child process: $cmd");
	}
	stream_set_blocking($pipes[1], false);
	stream_set_blocking($pipes[2], false);
	$stdout = '';
	$stderr = '';
	$started = microtime(true);
	$timed_out = false;
	while (true) {
		$stdout .= stream_get_contents($pipes[1]);
		$stderr .= stream_get_contents($pipes[2]);
		$status = proc_get_status($proc);
		if (!$status['running']) break;
		if (microtime(true) - $started > $timeout_seconds) {
			$timed_out = true;
			proc_terminate($proc, 9);
			break;
		}
		usleep(100000);
	}
	$stdout .= stream_get_contents($pipes[1]);
	$stderr .= stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$exit_code = $timed_out ? -1 : $status['exitcode'];
	proc_close($proc);
	return array($exit_code, trim($stdout), trim($stderr));
}

/**
 * Asserts that a walk run in a child process terminated normally and printed the expected ids.
 */
function check_walk($walk, $task_id, $expected_ids) {
	list($exit_code, $stdout, $stderr) = walk_in_child($walk, $task_id);
	check($exit_code === 0, "child exited with code $exit_code (-1 = killed after timeout)\nstdout: $stdout\nstderr: $stderr");
	$expected = "$walk=" . implode(',', $expected_ids);
	check($stdout === $expected, "expected '$expected', got: $stdout");
}

$task_ot = ObjectTypes::findByName('task');
if (!$task_ot instanceof ObjectType) {
	echo "Object type 'task' not found, cannot create fixtures.\n";
	exit(1);
}
$task_object_type_id = $task_ot->getId();

// make sure the reserved id range is free before inserting fixtures
$leftover = DB::executeOne("SELECT COUNT(*) AS cnt FROM `{$T}objects` WHERE id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
if ($leftover && $leftover['cnt'] > 0) {
	echo "Reserved fixture id range is not empty in `{$T}objects`, aborting to avoid touching real rows.\n";
	exit(1);
}

$self_parent   = FIXTURE_ID_BASE + 1; // parent_id points at itself
$under_self    = FIXTURE_ID_BASE + 2; // child of the self-parented task
$cycle_a       = FIXTURE_ID_BASE + 3; // cycle_a -> cycle_b -> cycle_a
$cycle_b       = FIXTURE_ID_BASE + 4;
$leaf          = FIXTURE_ID_BASE + 5; // leaf -> middle -> root, well formed
$middle        = FIXTURE_ID_BASE + 6;
$root          = FIXTURE_ID_BASE + 7;

insert_fixture_task($self_parent, $self_parent, 5);
insert_fixture_task($under_self, $self_parent, 5);
insert_fixture_task($cycle_a, $cycle_b, 5);
insert_fixture_task($cycle_b, $cycle_a, 5);
insert_fixture_task($root, 0, 10);
insert_fixture_task($middle, $root, 20);
insert_fixture_task($leaf, $middle, 30);

// --- one step up or down ----------------------------------------------------------------------
// Every roll-up in ProjectTask climbs through getParent() or descends through getSubTasks(); a task
// that is its own parent must not be returned by either, or each of those roll-ups recurses forever.

run_test('getParent returns the parent of a well formed task and nothing for a self-parented one', function () use ($leaf, $middle, $self_parent) {
	$parent = ProjectTasks::instance()->findById($leaf)->getParent();
	check($parent instanceof ProjectTask && $parent->getId() == $middle, 'leaf parent is middle');
	check(ProjectTasks::instance()->findById($self_parent)->getParent() === null, 'a self-parented task has no parent');
});

run_test('getSubTasks never returns the task itself', function () use ($self_parent, $under_self) {
	$ids = array();
	foreach (ProjectTasks::instance()->findById($self_parent)->getSubTasks() as $subtask) {
		$ids[] = $subtask->getId();
	}
	check($ids === array($under_self), 'expected [under_self], got [' . implode(',', $ids) . ']');
});

run_test('getSubTasks still excludes the task itself when getAllSubTasks filled the shared cache first', function () use ($self_parent, $under_self) {
	ProjectTasks::instance()->clearCache();
	$task = ProjectTasks::instance()->findById($self_parent);
	$task->getAllSubTasks(); // fills $this->all_tasks
	$ids = array();
	foreach ($task->getSubTasks() as $subtask) {
		$ids[] = $subtask->getId();
	}
	check($ids === array($under_self), 'expected [under_self] from the cached list, got [' . implode(',', $ids) . ']');
});

run_test('countAllSubTasks never counts the task itself', function () use ($self_parent) {
	ProjectTasks::instance()->clearCache();
	$fresh = ProjectTasks::instance()->findById($self_parent);
	check($fresh->countAllSubTasks() === 1, 'expected 1 (under_self) from the SQL count, got ' . $fresh->countAllSubTasks());
	ProjectTasks::instance()->clearCache();
	$cached = ProjectTasks::instance()->findById($self_parent);
	$cached->getAllSubTasks(); // fills the shared list first
	check($cached->countAllSubTasks() === 1, 'expected 1 (under_self) from the cached list, got ' . $cached->countAllSubTasks());
});

// --- walking up -------------------------------------------------------------------------------

run_test('getAllParents returns the whole chain, nearest parent first, for a well formed task', function () use ($leaf, $middle, $root) {
	$task = ProjectTasks::instance()->findById($leaf);
	check($task instanceof ProjectTask, 'leaf fixture task loads');
	$ids = array();
	foreach ($task->getAllParents() as $parent) {
		$ids[] = $parent->getId();
	}
	check($ids === array($middle, $root), 'expected [middle, root], got [' . implode(',', $ids) . ']');
});

run_test('getAllParents stops when a task is its own parent', function () use ($self_parent) {
	check_walk('parents', $self_parent, array());
});

run_test('getAllParents stops when two tasks point at each other', function () use ($cycle_a, $cycle_b) {
	check_walk('parents', $cycle_a, array($cycle_b));
});

run_test('getAllParents stops for a child whose ancestor is its own parent', function () use ($under_self, $self_parent) {
	check_walk('parents', $under_self, array($self_parent));
});

// --- walking down -----------------------------------------------------------------------------

run_test('getAllSubTasks returns the whole subtree, direct children first, for a well formed task', function () use ($root, $middle, $leaf) {
	$task = ProjectTasks::instance()->findById($root);
	check($task instanceof ProjectTask, 'root fixture task loads');
	$ids = array();
	foreach ($task->getAllSubTasks() as $subtask) {
		$ids[] = $subtask->getId();
	}
	check($ids === array($middle, $leaf), 'expected [middle, leaf], got [' . implode(',', $ids) . ']');
});

run_test('getAllSubTasks does not list a task as its own descendant', function () use ($self_parent, $under_self) {
	check_walk('subtasks', $self_parent, array($under_self));
});

run_test('getAllSubTasks stops when two tasks point at each other', function () use ($cycle_a, $cycle_b) {
	check_walk('subtasks', $cycle_a, array($cycle_b));
});

run_test('getAllSubtaskIdsInHierarchy returns every descendant id for a well formed task', function () use ($root, $middle, $leaf) {
	$task = ProjectTasks::instance()->findById($root);
	$ids = array_values($task->getAllSubtaskIdsInHierarchy());
	sort($ids); // the result is a set, compare it in id order (leaf has the lower id)
	check($ids === array($leaf, $middle), 'expected [leaf, middle], got [' . implode(',', $ids) . ']');
});

run_test('getAllSubtaskIdsInHierarchy does not list a task as its own descendant', function () use ($self_parent, $under_self) {
	check_walk('subtask_ids', $self_parent, array($under_self));
});

run_test('getAllSubtaskIdsInHierarchy stops when two tasks point at each other', function () use ($cycle_a, $cycle_b) {
	check_walk('subtask_ids', $cycle_a, array($cycle_b));
});

// --- propagating totals up ----------------------------------------------------------------------

run_test('calculateTotalTimeEstimate rolls the estimate up through every ancestor of a well formed task', function () use ($leaf, $middle, $root) {
	$task = ProjectTasks::instance()->findById($leaf);
	$task->calculateTotalTimeEstimate();
	check(fixture_total_time_estimate($leaf) === 30, 'leaf total is its own estimate, got ' . fixture_total_time_estimate($leaf));
	check(fixture_total_time_estimate($middle) === 50, 'middle total includes the leaf, got ' . fixture_total_time_estimate($middle));
	check(fixture_total_time_estimate($root) === 60, 'root total includes both, got ' . fixture_total_time_estimate($root));
});

run_test('calculateTotalTimeEstimate stops when a task is its own parent', function () use ($self_parent) {
	check_walk('time_estimate', $self_parent, array('done'));
});

run_test('calculateTotalTimeEstimate stops when two tasks point at each other', function () use ($cycle_a) {
	check_walk('time_estimate', $cycle_a, array('done'));
});

// --- recomputing depth and parents_path -----------------------------------------------------------

run_test('updateDepthAndParentsPath records the chain and depth of a well formed task', function () use ($leaf, $middle, $root) {
	$task = ProjectTasks::instance()->findById($leaf);
	$task->updateDepthAndParentsPath($task->getParentId());
	check($task->getColumnValue('parents_path') === "$middle,$root", 'expected parents_path middle,root, got ' . $task->getColumnValue('parents_path'));
	check((int)$task->getColumnValue('depth') === 2, 'expected depth 2, got ' . $task->getColumnValue('depth'));
});

run_test('updateDepthAndParentsPath stops when a task is its own parent', function () use ($self_parent) {
	// no valid ancestor: same answer getAllParents gives for this task
	check_walk('depth_path', $self_parent, array("0:"));
});

run_test('updateDepthAndParentsPath stops when two tasks point at each other', function () use ($cycle_a, $cycle_b) {
	check_walk('depth_path', $cycle_a, array("1:$cycle_b"));
});

// --- the roll-ups that climb through getParent() and descend through getSubTasks() ---------------

run_test('calculatePercentComplete terminates for a task that is its own parent', function () use ($self_parent) {
	check_walk('percent_complete', $self_parent, array('done'));
});

run_test('calculatePercentComplete terminates for a child whose parent is its own parent', function () use ($under_self) {
	check_walk('percent_complete', $under_self, array('done'));
});

run_test('calculatePercentComplete terminates when two tasks point at each other', function () use ($cycle_a) {
	check_walk('percent_complete', $cycle_a, array('done'));
});

run_test('calculateAndSaveOverallTotalWorkedTime terminates for a task that is its own parent', function () use ($self_parent) {
	check_walk('worked_time', $self_parent, array('done'));
});

run_test('calculateAndSaveOverallTotalWorkedTime terminates for a child whose parent is its own parent', function () use ($under_self) {
	check_walk('worked_time', $under_self, array('done'));
});

run_test('calculateAndSaveOverallTotalWorkedTime terminates when two tasks point at each other', function () use ($cycle_a) {
	check_walk('worked_time', $cycle_a, array('done'));
});

cleanup_fixtures();

echo "\n" . ($GLOBALS['checks'] - $GLOBALS['failures']) . " of " . $GLOBALS['checks'] . " checks passed\n";
exit($GLOBALS['failures'] > 0 ? 1 : 0);
