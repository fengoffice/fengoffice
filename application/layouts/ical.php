<?php header('Content-Type: text/calendar', true); ?>
<?php header("Content-Disposition: attachment; filename=\"cal.ics\""); ?>
<?php echo $content_for_layout ?>