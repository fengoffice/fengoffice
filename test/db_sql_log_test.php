<?php
/**
 * Regression test for the in-memory SQL log kept by DB (environment/library/database/DB.class.php).
 *
 * Every executed statement used to be appended to DB::$sql_log unconditionally. Nothing reads that
 * log unless DB debugging is on, and in long-running processes (plugin updates, cron, imports) the
 * accumulated statements exhausted memory_limit: the mail plugin update that rewrites body_plain
 * for every email died this way on large installs. The log must only grow while DB debugging is on.
 *
 * Run from the Feng root:  php test/db_sql_log_test.php
 *
 * Boots Feng in console mode using config/config.php and only runs read-only statements.
 * Exit code 0 when every check passes; the test is skipped (exit 0) when DEBUG_DB is on, because
 * then the log is expected to grow.
 */
error_reporting(E_ALL & ~E_DEPRECATED);
chdir(dirname(__FILE__) . '/..');
define('CONSOLE_MODE', true);
define('PUBLIC_FOLDER', 'public');

include 'init.php';
session_commit();
restore_error_handler();
restore_exception_handler();

if (Env::isDebuggingDB()) {
	echo "SKIP: DEBUG_DB is on in config/config.php, the SQL log is expected to grow.\n";
	exit(0);
}

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

echo "- executed statements are not kept in memory when DB debugging is off\n";
$before = count(DB::getSQLLog());
for ($i = 0; $i < 50; $i++) {
	DB::executeOne("SELECT " . intval($i) . " AS n");
}
$after = count(DB::getSQLLog());
check($after === $before, "SQL log grew from $before to $after entries after 50 statements");

echo "\n" . ($checks - $failures) . " of $checks checks passed\n";
exit($failures > 0 ? 1 : 0);
