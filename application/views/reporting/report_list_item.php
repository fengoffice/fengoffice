<?php
/**
 * Single report row in the custom reports list.
 * Expected variable: $report
 */
?>
<div class="report-name">
	<div class="report-name-row">
		<div class="report-name-info">
			<?php if ($report->getFunctionUrl()) { ?>
				<a href="<?php echo with_slash(ROOT_URL) . 'index.php' . $report->getFunctionUrl() ?>" class="internalLink report-name-link" target="reporting-panel"><?php
					echo Localization::instance()->lang_exists($report->getObjectName()) ? lang($report->getObjectName()) : $report->getObjectName();
				?></a>
				<div class="desc"><?php echo Localization::instance()->lang_exists($report->getDescription()) ? lang($report->getDescription()) : $report->getDescription(); ?></div>
			<?php } else { ?>
				<a href="<?php echo get_url('reporting', 'view_custom_report', array('id' => $report->getId())) ?>" class="internalLink report-name-link" target="reporting-panel"><?php
					echo $report->getObjectName();
				?></a>
				<div class="desc"><?php echo $report->getDescription() ?></div>
			<?php } ?>
		</div>
		<div class="report-name-side">
			<div class="report-name-breadcrumb" id="report-<?php echo $report->getId(); ?>">
				<span class="breadcrumb"></span>
			</div>
			<?php tpl_assign('report', $report); tpl_display(get_template_path('report_actions_button', 'reporting')); ?>
		</div>
	</div>
	<?php tpl_assign('report', $report); tpl_display(get_template_path('report_actions_template', 'reporting')); ?>
	<script>
		<?php $crumbOptions = json_encode($report->getMembersIdsToDisplayPath());
		$crumbJs = " og.getEmptyCrumbHtml($crumbOptions) "; ?>
		var crumbHtml = <?php echo $crumbJs; ?>;
		$("#report-<?php echo $report->getId(); ?> .breadcrumb").html(crumbHtml);
	</script>
</div>
