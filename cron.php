<?php
chdir(dirname(__FILE__));
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";
include APPLICATION_PATH . "/cron_functions.php";

header("Content-type: text/plain");
$type = array_var($_GET, "type");

session_commit(); // we don't need sessions
@set_time_limit(0); // don't limit execution of cron, if possible

$fast_functions = array(
	"send_reminders" => 1,
	"send_password_expiration_reminders" => 1,
	"send_notifications_through_cron"=> 1
);

$events = CronEvents::getDueEvents();

foreach ($events as $event) {
	if ($event->getEnabled()) {
		$function = $event->getName();
		if ( $type=="fast" && array_var($fast_functions, $function) || 
			 $type=="slow" && !array_var($fast_functions, $function) ||
			 !$type ) {
		
			$errordate = DateTimeValueLib::now()->add("m", 30);
			/* setting this date allows to rerun the event in 30 minutes if a fatal error occurs
			   during its execution, which would prevent the event from being rescheduled */
			
			$event->setDate($errordate);
			$event->save();
			$function = $event->getName();
			try {
				if (function_exists($function)) {
					$function();
				} else {
					echo "Could not execute $function - function does not exists\n";
				}
			} catch (Error $e) {
				echo $e->getMessage();
			}
			
			if ($event->getRecursive()) {
				try {
					DB::beginWork();
					$nextdate = DateTimeValueLib::now()->add("m", $event->getDelay());
					$event->setDate($nextdate);
					$event->save();
					DB::commit();
				} catch (Exception $e) {
					echo $e->getMessage();
					DB::rollback();
				}
			}
		}
	}
}
