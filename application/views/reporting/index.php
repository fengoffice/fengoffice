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
	$object_types = ObjectTypes::getObjectTypesForCustomReports();

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
				foreach($reports as $report) {
					tpl_assign('report', $report);
					tpl_display(get_template_path('report_list_item', 'reporting'));
				}
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
		og.initReportActionMenus();
	});
</script>