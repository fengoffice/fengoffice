<?php
/**
 * Gear button for report actions menu.
 * Expected variable: $report
 */
$report_id = $report->getId();
$can_edit = $report->canEdit(logged_user());
$can_delete = $report->canDelete(logged_user());
$can_copy = !$report->getFunctionUrl() && $can_edit;

if (!$can_edit && !$can_delete && !$can_copy) return;

$template_id = 'reportActionsTemplate' . $report_id;
?>
<div class="report-actions-container">
	<button type="button"
		id="reportActionsBtn<?php echo $report_id ?>"
		class="report-actions-btn coViewAction ico-administration"
		data-templateid="<?php echo $template_id ?>"
		data-container="body"
		data-toggle="popover"
		data-placement="left"
		data-trigger="click"
		title="<?php echo lang('actions') ?>">
	</button>
</div>
