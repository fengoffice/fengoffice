<?php 
	$genid = gen_id();
	$selectedPage = user_config_option('custom_report_tab');
	$customReports = Reports::getAllReportsByObjectType();
	
	$active_members = active_context_members(false);
	if (count($active_members) > 0) {
		$report = new Report();
		$can_add_reports = $report->canAdd(logged_user(), active_context());
	} else {
		$can_add_reports = logged_user()->isManager() || logged_user()->isAdminGroup();
	}
	
	$reports_by_type = array();
	$object_types = ObjectTypes::getAvailableObjectTypes();
	$object_types[] = ObjectTypes::findByName('timeslot');
	Hook::fire('custom_reports_object_types', array('object_types' => $object_types), $object_types);
	
	foreach ($object_types as $ot) {
		$reports_by_type[$ot->getId()] = array("name" => $ot->getName(), "display_name" => $ot->getObjectTypeName(), "icon_class" => $ot->getIconClass());
	}
	
	$ignored = null;
	Hook::fire('modify_report_pages', $ignored, $reports_by_type); // To add, edit or remove report pages
	
	require_javascript("og/ReportingFunctions.js");
?>

<div>

<table>
<tr>
	<td class="coViewTopLeft" style="background-color:white;"></td>
	<td class="coViewTop" style="background-color:white;"></td>
	<td class="coViewTopRight">&nbsp;</td>
</tr>
<tr>
	<td style="heigth:12px; background-color:white;"></td>
	<td style="background-color:white;">
	
		<div style="padding:15px 20px 50px;">
			
			<div class="report-list-section">
				<div class="title"><?php echo lang('custom reports') ?></div>

			<?php
			foreach ($reports_by_type as $type_id => $type_info) {
				$reports = array_var($customReports, $type_id, array());
				if (!is_array($reports) || count($reports) == 0) continue;
				foreach($reports as $report) {?>
				<div class="report-name">
					<?php if($report->getFunctionUrl()){?>
						<a href="<?php echo ROOT_URL.$report->getFunctionUrl() ?>" class="internalLink" target="reporting-panel" style="padding:10px 0;"><?php 
						echo Localization::instance()->lang_exists($report->getObjectName()) ? lang($report->getObjectName()) : $report->getObjectName();
						?></a>
					<?php } else { ?>
						<a href="<?php echo get_url('reporting','view_custom_report', array('id' => $report->getId()))?>" class="internalLink" target="reporting-panel" style="padding:10px 0;"><?php 
							echo $report->getObjectName();
						?></a>
					<?php } ?>
	
						<div style="float:right;">
							<?php if ($report->canEdit(logged_user())) { ?>
							<a style="margin-right:5px;font-weight:normal;" class="internalLink coViewAction ico-edit" href="<?php echo $report->getFunctionUrl() ? get_url('reporting','edit_default_report', array('id' => $report->getId())) : get_url('reporting','edit_custom_report', array('id' => $report->getId())) ?>"><?php echo lang('edit') ?></a>
							<?php } ?>
							<?php if ($report->canDelete(logged_user())) { ?>
							<a style="margin-right:5px;font-weight:normal;" class="internalLink coViewAction ico-delete" href="javascript:og.deleteReport(<?php echo $report->getId() ?>)"><?php echo lang('delete') ?></a>
							<?php } ?>
						</div>

						<div style="float:right; max-width:700px; margin-right:25px; font-weight:normal;" id="report-<?php echo $report->getId();?>">
							<span class="breadcrumb"></span>
							<script>
								<?php $crumbOptions = json_encode($report->getMembersIdsToDisplayPath());
									$crumbJs = " og.getEmptyCrumbHtml($crumbOptions) ";?>
									var crumbHtml = <?php echo $crumbJs;?>;
									$("#report-<?php echo $report->getId()?> .breadcrumb").html(crumbHtml);
							</script>
						</div>
												
                                                <?php if($report->getFunctionUrl()){?>
						<div class="desc"><?php echo Localization::instance()->lang_exists($report->getDescription()) ? lang($report->getDescription()) : $report->getDescription(); ?></div>
					<?php } else { ?>
						<div class="desc"><?php echo $report->getDescription() ?></div>
					<?php } ?>

				</div>
				
				<?php }
				}
				?>
			</div>

		<?php if ($can_add_reports) { ?>
		<a class="internalLink coViewAction ico-add" href="<?php echo get_url('reporting', 'add_custom_report') ?>"><?php echo lang('add custom report')?></a>
		<?php } ?>
		</div>

	</td><td class="coViewRight"></td>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom"></td>
	<td style="width:12px" class="coViewBottomRight">&nbsp;</td>
</tr>

</table>

</div>


<script>
	og.deleteReport = function(id){
		if(confirm(lang('delete report confirmation'))){
			og.openLink(og.getUrl('reporting', 'delete_custom_report', {id: id}));
		}
	};
	$(function() {
		og.eventManager.fireEvent('replace all empty breadcrumb');
	});
</script>