<?php

/**
 * SystemContext
 *
 * Provides a scoped execution context for system/cron processes that run
 * without a logged-in user. While active, permission checks that would
 * normally filter results by the current user are bypassed entirely.
 *
 * Usage:
 *   SystemContext::run(function() {
 *       $subtasks = $task->getSubTasks();
 *       // ... unrestricted access
 *   });
 *
 * The context is automatically closed even if the callable throws, and
 * supports nesting (inner run() calls do not prematurely close an outer one).
 */
class SystemContext {

    private static $depth = 0;

    /**
     * Execute a callable in system context, then restore the previous state.
     *
     * @param callable $fn
     * @return mixed  The return value of $fn.
     */
    static function run(callable $fn) {
        self::$depth++;
        try {
            return $fn();
        } finally {
            self::$depth--;
        }
    }

    /**
     * Returns true when execution is inside a SystemContext::run() call.
     *
     * @return bool
     */
    static function isActive() {
        return self::$depth > 0;
    }
}
