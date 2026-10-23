<?php
/**
 * Hidden popover template for report actions menu.
 * Expected variable: $report
 */
$report_id = $report->getId();
$can_edit = $report->canEdit(logged_user());
$can_delete = $report->canDelete(logged_user());
$can_copy = !$report->getFunctionUrl() && $can_edit;

if (!$can_edit && !$can_delete && !$can_copy) return;

$template_id = 'reportActionsTemplate' . $report_id;
$edit_url = $report->getFunctionUrl()
	? get_url('reporting', 'edit_default_report', array('id' => $report_id))
	: get_url('reporting', 'edit_custom_report', array('id' => $report_id));
?>
<div id="<?php echo $template_id ?>" style="display: none;">
	<div class="popover report-actions-popover">
		<div class="arrow"></div>
		<div class="popover-inner">
			<ul class="report-actions-menu">
				<?php if ($can_edit) { ?>
				<li>
					<a href="#" class="coViewAction ico-edit report-action-link" data-report-url="<?php echo htmlspecialchars($edit_url, ENT_QUOTES, 'UTF-8') ?>"><?php echo lang('edit') ?></a>
				</li>
				<?php } ?>
				<?php if ($can_copy) { ?>
				<li>
					<a href="#" class="coViewAction ico-copy report-action-link" data-report-url="<?php echo htmlspecialchars(get_url('reporting', 'clone_custom_report', array('id' => $report_id)), ENT_QUOTES, 'UTF-8') ?>"><?php echo lang('copy') ?></a>
				</li>
				<?php } ?>
				<?php if ($can_delete) { ?>
				<li>
					<a href="#" class="coViewAction ico-delete report-action-link" data-report-id="<?php echo (int)$report_id ?>"><?php echo lang('delete') ?></a>
				</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
