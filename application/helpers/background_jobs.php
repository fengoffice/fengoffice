<?php

/**
 * Deferred, coalescing spawner for background worker processes.
 *
 * Call sites queue jobs instead of exec'ing one process each. Jobs are grouped by job type and
 * user, merged, and dispatched as a single niced process per group when the request ends. This
 * bounds the number of processes a request can create at the number of distinct job types it
 * touches, rather than the number of objects it saves.
 *
 * The file deliberately touches ROOT and PHP_PATH only inside function bodies, so it can be
 * included by a standalone test without bootstrapping init.php.
 */

/**
 * Returns the registry of known background job types.
 *
 * Each entry declares the worker script to run, relative to ROOT, and how several payloads of the
 * same type are merged: 'id_list' joins deduplicated ids into a single CSV argument, 'structured'
 * appends each payload to a JSON Lines spool file and passes that file's path.
 *
 * @return array Job type => array('script' => string, 'strategy' => string).
 */
function background_job_registry() {
	return array(
		'recalculate_project_financials_of_objects' => array(
			'script'   => 'plugins/advanced_billing/application/helpers/recalculate_project_financials_of_objects_background.php',
			'strategy' => 'id_list',
		),
		'recalculate_project_financials_of_members' => array(
			'script'   => 'plugins/advanced_billing/application/helpers/recalculate_project_financials_of_members_background.php',
			'strategy' => 'id_list',
		),
		'recalculate_task_financials' => array(
			'script'   => 'plugins/advanced_billing/application/helpers/recalculate_task_financials_background.php',
			'strategy' => 'id_list',
		),
		'save_member_to_qbo' => array(
			'script'   => 'plugins/quickbooks/application/helpers/save_member_to_qbo_in_background.php',
			'strategy' => 'id_list',
		),
		'save_objects_to_qbo' => array(
			'script'   => 'plugins/quickbooks/application/helpers/save_objects_to_qbo_in_background.php',
			'strategy' => 'id_list',
		),
		'add_object_to_sharing_table' => array(
			'script'   => 'application/helpers/add_object_to_sharing_table.php',
			'strategy' => 'id_list',
		),
		'recalculate_contact_member_cache_for_user' => array(
			'script'   => 'application/helpers/recalculate_contact_member_cache_for_user.php',
			'strategy' => 'id_list',
		),
		'rebuild_sharing_table_for_pg' => array(
			'script'   => 'application/helpers/rebuild_sharing_table_for_pg.php',
			'strategy' => 'id_list',
		),
		'save_member_permissions' => array(
			'script'   => 'application/helpers/save_member_permissions.php',
			'strategy' => 'structured',
		),
		'member_parent_changed_refresh_object_permisssions' => array(
			'script'   => 'application/helpers/member_parent_changed_refresh_object_permisssions.php',
			'strategy' => 'structured',
		),
		'save_user_permissions' => array(
			'script'   => 'application/helpers/save_user_permissions.php',
			'strategy' => 'structured',
		),
	);
}

/**
 * Returns a reference to the per-request queue state.
 *
 * Kept in one accessor so every function in this helper shares the same static storage.
 *
 * @return array Reference to array('queued' => array, 'registrations' => int).
 */
function &background_jobs_state() {
	static $state = array('queued' => array(), 'registrations' => 0);
	return $state;
}

/**
 * Returns the currently queued job groups.
 *
 * @return array Group key => group data.
 */
function background_jobs_queued() {
	$state = &background_jobs_state();
	return $state['queued'];
}

/**
 * Returns how many shutdown callbacks this helper has registered in the current request.
 *
 * Always 1 once anything has been queued. Exposed so tests can assert the coalescing invariant.
 *
 * @return int
 */
function background_jobs_registration_count() {
	$state = &background_jobs_state();
	return $state['registrations'];
}

/**
 * Clears the queue state. Intended for tests; not used by application code.
 *
 * @return void
 */
function background_jobs_reset() {
	$state = &background_jobs_state();
	$state['queued'] = array();
	$state['registrations'] = 0;
}

/**
 * Builds the path of a spool file for a structured job group.
 *
 * uniqid() and getmypid() are used instead of gen_id() so this helper stays loadable without the
 * full application bootstrap.
 *
 * @param string $job_type Job type from the registry.
 * @return string Absolute path inside the installation's tmp directory.
 */
function background_job_spool_path($job_type) {
	return ROOT . '/tmp/bgjob_' . $job_type . '_' . getmypid() . '_' . uniqid('', true) . '.json';
}

/**
 * Strips the ".processing.<pid>" suffix a worker adds when it claims a spool file.
 *
 * @param string $spool_file Claimed or unclaimed spool path.
 * @return string The original spool path, which is also where the .meta sidecar lives.
 */
function background_job_spool_base($spool_file) {
	return preg_replace('/\.processing\.\d+$/', '', $spool_file);
}

/**
 * Takes exclusive ownership of a spool file by renaming it.
 *
 * rename() is atomic within a filesystem, so of two processes racing for the same spool exactly
 * one succeeds and the loser backs off. This is what stops the cron sweeper and the request's own
 * shutdown handler from both processing the same batch.
 *
 * Claiming also protects the lines the originating request has not written yet: it keeps appending
 * to the original path, which is recreated on the next append, and its own shutdown handler
 * dispatches a worker for that remainder.
 *
 * @param string $spool_file Path to claim.
 * @return string|null The claimed path, or null when the file is gone or another worker won it.
 */
function claim_background_job_spool($spool_file) {
	if (!is_file($spool_file)) {
		return null;
	}

	$claimed = background_job_spool_base($spool_file) . '.processing.' . getmypid();
	if ($claimed === $spool_file) {
		return $spool_file; // already claimed by this process
	}
	if (!@rename($spool_file, $claimed)) {
		return null;
	}
	return $claimed;
}

/**
 * Reports whether a process id is still alive.
 *
 * Used by the sweeper so it never touches a spool whose request is still appending to it.
 * When neither check is available the caller falls back to the age cutoff alone.
 *
 * @param int $pid Process id to test.
 * @return bool
 */
function background_job_pid_is_running($pid) {
	$pid = (int) $pid;
	if ($pid <= 0) {
		return false;
	}

	if (function_exists('posix_kill')) {
		if (@posix_kill($pid, 0)) {
			return true;
		}
		// EPERM means the process exists but belongs to another user, which still counts as alive.
		return function_exists('posix_get_last_error') && posix_get_last_error() === 1;
	}

	$output = array();
	@exec('ps -p ' . $pid . ' -o pid=', $output);
	return count($output) > 0;
}

/**
 * Reads a JSON Lines spool file written by queue_background_job().
 *
 * Malformed lines are skipped rather than aborting the batch, so one corrupt entry cannot cost the
 * remaining jobs.
 *
 * @param string $spool_file Absolute path to the spool file.
 * @return array List of payload arrays.
 */
function read_background_job_spool($spool_file) {
	$jobs = array();
	if (!is_file($spool_file)) {
		return $jobs;
	}
	$lines = file($spool_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if (!is_array($lines)) {
		return $jobs;
	}
	foreach ($lines as $line) {
		$job = json_decode($line, true);
		if (is_array($job)) {
			$jobs[] = $job;
		}
	}
	return $jobs;
}

/**
 * Queues one background job for dispatch at the end of the request.
 *
 * Jobs are grouped by job type and user id. The user is part of the key because each worker
 * authenticates as a single user from its argv, so jobs belonging to different users must not
 * share a process.
 *
 * For structured jobs the payload is appended to the group's spool file immediately rather than at
 * shutdown, so that work already queued survives a fatal error later in the request.
 *
 * @param string $job_type Job type from background_job_registry().
 * @param mixed $payload Array of ids for 'id_list' jobs, associative array for 'structured' ones.
 * @param int|string $user_id Id of the user the worker will authenticate as.
 * @param string $token That user's twisted token.
 * @return void
 * @throws Exception When the job type is not registered.
 */
function queue_background_job($job_type, $payload, $user_id, $token) {
	$registry = background_job_registry();
	if (!isset($registry[$job_type])) {
		throw new Exception("Unknown background job type '$job_type'");
	}

	$state = &background_jobs_state();
	$key = $job_type . '|' . $user_id;

	if (!isset($state['queued'][$key])) {
		$group = array(
			'job_type' => $job_type,
			'user_id'  => $user_id,
			'token'    => $token,
			'payloads' => array(),
		);
		if ($registry[$job_type]['strategy'] == 'structured') {
			$group['spool_file'] = background_job_spool_path($job_type);
			// The sidecar lets the orphan sweeper rebuild the command without storing the token
			// on disk: it looks the user up and reads a fresh token instead. The pid is recorded
			// so the sweeper can tell "this request is still appending" from "this request died".
			file_put_contents(
				$group['spool_file'] . '.meta',
				json_encode(array('job_type' => $job_type, 'user_id' => $user_id, 'pid' => getmypid()))
			);
		}
		$state['queued'][$key] = $group;
	}

	if ($registry[$job_type]['strategy'] == 'id_list') {
		foreach ((array) $payload as $id) {
			$id = trim((string) $id);
			if ($id !== '') {
				// Keyed by value so repeated ids collapse into one entry.
				$state['queued'][$key]['payloads'][$id] = $id;
			}
		}
	} else {
		$state['queued'][$key]['payloads'][] = $payload;
		file_put_contents(
			$state['queued'][$key]['spool_file'],
			json_encode($payload) . "\n",
			FILE_APPEND | LOCK_EX
		);
	}

	if ($state['registrations'] === 0) {
		register_shutdown_function('run_queued_background_jobs');
		$state['registrations'] = 1;
	}
}

/**
 * Turns queued job groups into shell commands, one per group.
 *
 * Pure function: it reads no static state and spawns nothing, so it can be asserted on directly.
 *
 * @param array $queued Job groups as returned by background_jobs_queued().
 * @return array List of shell command strings.
 */
function build_background_job_commands($queued) {
	$registry = background_job_registry();
	$commands = array();

	foreach ($queued as $group) {
		if (!isset($registry[$group['job_type']])) {
			continue;
		}
		$config = $registry[$group['job_type']];

		if ($config['strategy'] == 'id_list') {
			if (count($group['payloads']) == 0) {
				continue;
			}
			$argument = implode(',', $group['payloads']);
		} else {
			if (!isset($group['spool_file']) || count($group['payloads']) == 0) {
				continue;
			}
			$argument = $group['spool_file'];
		}

		$commands[] = 'nice -n19 ' . PHP_PATH
			. ' ' . escapeshellarg(ROOT . '/' . $config['script'])
			. ' ' . escapeshellarg(ROOT)
			. ' ' . escapeshellarg((string) $group['user_id'])
			. ' ' . escapeshellarg((string) $group['token'])
			. ' ' . escapeshellarg($argument);
	}

	return $commands;
}

/**
 * Shutdown handler: dispatches one process per queued job group.
 *
 * The queue is cleared before spawning so a job cannot be dispatched twice if this runs again.
 *
 * @return void
 */
function run_queued_background_jobs() {
	$state = &background_jobs_state();
	$commands = build_background_job_commands($state['queued']);
	$state['queued'] = array();

	foreach ($commands as $command) {
		exec($command . ' > /dev/null &');
	}
}

/**
 * Respawns workers for spool files left behind by requests that died before their shutdown handler
 * ran, for example on a fatal error or a php-fpm timeout.
 *
 * The token is not stored on disk: the sidecar records only the job type and user id, and a fresh
 * token is read from the user record here. Spool files whose user no longer exists are removed.
 *
 * Requires the full application bootstrap, so it is only called from cron.
 *
 * @param int $max_age_minutes Only sweep spool files older than this, so live requests are left alone.
 * @return int Number of spool files respawned.
 */
function respawn_orphan_background_jobs($max_age_minutes = 60) {
	$registry = background_job_registry();
	$cutoff = time() - ($max_age_minutes * 60);
	$respawned = 0;

	// Unclaimed spools, plus spools whose worker claimed them and then died.
	$candidates = array_merge(
		glob(ROOT . '/tmp/bgjob_*.json') ?: array(),
		glob(ROOT . '/tmp/bgjob_*.json.processing.*') ?: array()
	);

	foreach ($candidates as $spool_file) {
		if (!is_file($spool_file) || filemtime($spool_file) > $cutoff) {
			continue;
		}

		// A claimed spool belongs to its worker until that worker is gone.
		if (preg_match('/\.processing\.(\d+)$/', $spool_file, $m)
			&& background_job_pid_is_running($m[1])) {
			continue;
		}

		$meta_file = background_job_spool_base($spool_file) . '.meta';
		$meta = is_file($meta_file) ? json_decode(file_get_contents($meta_file), true) : null;
		if (!is_array($meta) || !isset($meta['job_type']) || !isset($registry[$meta['job_type']])) {
			@unlink($spool_file);
			@unlink($meta_file);
			continue;
		}

		// Never touch a spool the originating request is still appending to. Without this the
		// sweeper races long imports, which is exactly the workload this queue exists for.
		$owner_pid = isset($meta['pid']) ? $meta['pid'] : null;
		if ($owner_pid !== null && background_job_pid_is_running($owner_pid)) {
			continue;
		}

		$user = Contacts::instance()->findById(isset($meta['user_id']) ? $meta['user_id'] : 0);
		if (!$user instanceof Contact) {
			@unlink($spool_file);
			@unlink($meta_file);
			continue;
		}

		$payloads = read_background_job_spool($spool_file);
		if (count($payloads) == 0) {
			@unlink($spool_file);
			@unlink($meta_file);
			continue;
		}

		$commands = build_background_job_commands(array(array(
			'job_type'   => $meta['job_type'],
			'user_id'    => $user->getId(),
			'token'      => $user->getTwistedToken(),
			'payloads'   => $payloads,
			'spool_file' => $spool_file,
		)));

		foreach ($commands as $command) {
			// Touch the file first so a worker that dies again is not respawned on every cron run.
			touch($spool_file);
			exec($command . ' > /dev/null &');
			$respawned++;
		}
	}

	return $respawned;
}
