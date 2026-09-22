<?php
/**
 * Integration test for mail_fix_empty_body_plain() (plugins/mail/update.php), the step of
 * mail_update_38_39 that fills mail_datas.body_plain from body_html.
 *
 * The original implementation loaded every matching body_html into one array, which exhausted
 * memory_limit on large installs and killed the whole plugin update run. The helper must therefore
 * process the table in batches so memory stays bounded regardless of table size, must terminate
 * when the remaining rows have no HTML to extract from, and must not touch rows that already have
 * a plain body.
 *
 * Run from the Feng root:  php plugins/mail/tests/mail_update_body_plain_batches_test.php
 *
 * Boots Feng in console mode using config/config.php, so it runs against the configured database:
 * point config.php at a throwaway copy. Fixture rows are inserted into mail_datas with ids in a
 * reserved high range and deleted from a shutdown function registered before Feng boots, so a fatal
 * error cannot leave them behind. The helper under test runs in a child PHP process with a fixed
 * memory_limit; the parent only checks its exit code, output and the resulting rows.
 * Exit code 0 when every check passes.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
chdir(dirname(__FILE__) . '/../../..');
define('CONSOLE_MODE', true);
define('PUBLIC_FOLDER', 'public');

// ids far above anything a real install uses (column is int(10) unsigned, max 4294967295)
define('FIXTURE_ID_BASE', 4200000000);
define('FIXTURE_ID_MAX', 4200001000);

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
	DB::execute("DELETE FROM `{$T}mail_datas` WHERE id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
}
if (!$is_worker) {
	register_shutdown_function('cleanup_fixtures');
}

include 'init.php';
session_commit();
restore_error_handler();
restore_exception_handler();

$T = TABLE_PREFIX;
require_once ROOT . '/plugins/mail/update.php'; // the helper under test and its size limit constant

// ---------------------------------------------------------------------------------------------
//  Worker mode: run the helper under test inside a child process with a fixed memory_limit.
//  Usage: <this file> --worker <memory_limit> <batch_size>
// ---------------------------------------------------------------------------------------------
if ($is_worker) {
	$pos = array_search('--worker', $argv);
	$memory_limit = isset($argv[$pos + 1]) ? $argv[$pos + 1] : '128M';
	$batch_size = isset($argv[$pos + 2]) ? (int)$argv[$pos + 2] : 20;
	ini_set('memory_limit', $memory_limit); // after init.php so nothing at boot can raise it again
	$fixed = mail_fix_empty_body_plain($batch_size);
	echo "fixed=" . $fixed . "\n";
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
 * Inserts one mail_datas fixture row. Only the columns the helper reads or writes matter; the
 * NOT NULL address columns are filled with empty strings.
 */
function insert_fixture_row($id, $body_plain, $body_html) {
	global $T;
	DB::execute("INSERT INTO `{$T}mail_datas` (`id`, `to`, `cc`, `bcc`, `subject`, `body_plain`, `body_html`)
		VALUES (" . intval($id) . ", '', '', '', 'fixture', " . DB::escape($body_plain) . ", " . DB::escape($body_html) . ")");
}

function fixture_body_plain($id) {
	global $T;
	$row = DB::executeOne("SELECT body_plain FROM `{$T}mail_datas` WHERE id = " . intval($id));
	return $row ? $row['body_plain'] : null;
}

/**
 * Runs the helper in a child PHP process and returns array(exit_code, stdout, stderr).
 * The child is killed after $timeout_seconds so a non-terminating loop fails the test instead of
 * hanging it.
 */
function run_fix_in_child($memory_limit, $batch_size, $timeout_seconds = 120) {
	$cmd = escapeshellarg(PHP_BINARY) . " -d memory_limit=" . escapeshellarg($memory_limit)
		. " " . escapeshellarg(__FILE__) . " --worker " . escapeshellarg($memory_limit) . " " . intval($batch_size);
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
	return array($exit_code, $stdout, $stderr);
}

// make sure the reserved id range is free before inserting fixtures
$leftover = DB::executeOne("SELECT COUNT(*) AS cnt FROM `{$T}mail_datas` WHERE id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
if ($leftover && $leftover['cnt'] > 0) {
	echo "Reserved fixture id range is not empty in `{$T}mail_datas`, aborting to avoid touching real rows.\n";
	exit(1);
}

// the helper walks the whole table: real rows still pending extraction would be rewritten by the
// test and would be counted in its output, so it needs a database with none of them
$pending = DB::executeOne("SELECT COUNT(*) AS cnt FROM `{$T}mail_datas`
	WHERE body_plain = '' AND body_html <> '' AND id NOT BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
if ($pending && $pending['cnt'] > 0) {
	echo "SKIP: `{$T}mail_datas` has " . $pending['cnt'] . " rows pending body_plain extraction; "
		. "point config/config.php at a database that has none (e.g. one already upgraded).\n";
	exit(0);
}

// 100 rows x 1 MB of HTML = 100 MB. Loading it all at once needs the buffered result plus the PHP
// array (>= 200 MB) and cannot fit in 128 MB; batches of 20 rows fit comfortably.
$row_count = 100;
$html_body = '<html><body><p>' . str_repeat('lorem ipsum dolor ', 58000) . '</p><script>var x = 1;</script></body></html>';

run_test('fills body_plain from body_html in batches within a fixed memory limit', function () use ($row_count, $html_body) {
	for ($i = 0; $i < $row_count; $i++) {
		insert_fixture_row(FIXTURE_ID_BASE + $i, '', $html_body);
	}

	list($exit_code, $stdout, $stderr) = run_fix_in_child('128M', 20);

	check($exit_code === 0, "child exited with code $exit_code\nstdout: $stdout\nstderr: $stderr");
	check(strpos($stdout, "fixed=$row_count") !== false, "expected fixed=$row_count in output, got: $stdout");

	$first = fixture_body_plain(FIXTURE_ID_BASE);
	$last = fixture_body_plain(FIXTURE_ID_BASE + $row_count - 1);
	check($first !== null && strpos($first, 'lorem ipsum dolor') === 0, 'first row body_plain starts with the extracted text');
	check($first !== null && strpos($first, '<p>') === false, 'first row body_plain contains no html tags');
	check($first !== null && strpos($first, 'var x') === false, 'first row body_plain drops script contents');
	check($last !== null && strpos($last, 'lorem ipsum dolor') === 0, 'last row body_plain starts with the extracted text');

	$remaining = DB::executeOne("SELECT COUNT(*) AS cnt FROM `" . TABLE_PREFIX . "mail_datas` WHERE body_plain = '' AND id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
	check($remaining && (int)$remaining['cnt'] === 0, 'no fixture row is left with an empty body_plain');

	cleanup_fixtures_now();
});

run_test('terminates when the remaining rows have no html to extract from', function () {
	for ($i = 0; $i < 30; $i++) {
		insert_fixture_row(FIXTURE_ID_BASE + $i, '', '');
	}

	list($exit_code, $stdout, $stderr) = run_fix_in_child('128M', 7, 60);

	check($exit_code === 0, "child exited with code $exit_code (-1 means it was killed after the timeout)\nstdout: $stdout\nstderr: $stderr");
	check(strpos($stdout, "fixed=0") !== false, "expected fixed=0 in output, got: $stdout");

	cleanup_fixtures_now();
});

run_test('skips rows whose html is larger than the extraction limit instead of loading them', function () {
	// one row just over the limit and one normal row; the oversized one must be neither loaded
	// (the batch query excludes it) nor extracted, the normal one must still be fixed
	$oversized = '<p>' . str_repeat('x', MAIL_BODY_PLAIN_MAX_HTML_BYTES + 1024) . '</p>';
	insert_fixture_row(FIXTURE_ID_BASE, '', $oversized);
	insert_fixture_row(FIXTURE_ID_BASE + 1, '', '<p>small enough</p>');

	list($exit_code, $stdout, $stderr) = run_fix_in_child('128M', 20, 120);

	check($exit_code === 0, "child exited with code $exit_code\nstdout: $stdout\nstderr: $stderr");
	check(strpos($stdout, "fixed=1") !== false, "expected fixed=1 in output, got: $stdout");
	check(fixture_body_plain(FIXTURE_ID_BASE) === '', 'oversized row keeps an empty body_plain');
	check(fixture_body_plain(FIXTURE_ID_BASE + 1) === 'small enough', 'normal row in the same batch gets its text');

	cleanup_fixtures_now();
});

run_test('leaves rows that already have a plain body untouched', function () {
	insert_fixture_row(FIXTURE_ID_BASE, 'already plain', '<p>different html</p>');
	insert_fixture_row(FIXTURE_ID_BASE + 1, '', '<p>needs extraction</p>');

	list($exit_code, $stdout, $stderr) = run_fix_in_child('128M', 20, 60);

	check($exit_code === 0, "child exited with code $exit_code\nstdout: $stdout\nstderr: $stderr");
	check(strpos($stdout, "fixed=1") !== false, "expected fixed=1 in output, got: $stdout");
	check(fixture_body_plain(FIXTURE_ID_BASE) === 'already plain', 'row with a plain body keeps it');
	check(fixture_body_plain(FIXTURE_ID_BASE + 1) === 'needs extraction', 'row with an empty plain body gets the extracted text');

	cleanup_fixtures_now();
});

/**
 * Deletes fixture rows between tests (the shutdown cleanup only runs once, at the very end).
 */
function cleanup_fixtures_now() {
	global $T;
	DB::execute("DELETE FROM `{$T}mail_datas` WHERE id BETWEEN " . FIXTURE_ID_BASE . " AND " . FIXTURE_ID_MAX);
}

echo "\n" . ($GLOBALS['checks'] - $GLOBALS['failures']) . " of " . $GLOBALS['checks'] . " checks passed\n";
exit($GLOBALS['failures'] > 0 ? 1 : 0);
