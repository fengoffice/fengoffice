<?php
require_javascript("og/ReportingFunctions.js");
if (!isset($genid)) $genid = gen_id();
if (!isset($allow_export)) $allow_export = true;
  	/*add_page_action(lang('print view'), '#', "ico-print", "_blank", array('onclick' => 'this.form' . $genid . '.submit'));*/
?>
<form id="form<?php echo $genid ?>" name="form<?php echo $genid ?>" action="<?php echo get_url('reporting', $template_name . '_print') ?>" method="post" enctype="multipart/form-data" target="_download">

    <input id="post<?php echo $genid ?>" name="post" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
    <input id="params_<?php echo $genid ?>" name="params" type="hidden" value="<?php echo str_replace('"',"'", json_encode($parameters))?>"/>
    <input id="report_id_<?php echo $genid ?>" name="report_id" type="hidden" value="<?php echo $id ?>"/>
    
<div class="report" style="padding:0px">
<table style="min-width:600px">
<tr>
	<td rowspan=2 colspan="2" class="coViewHeader" style="width:auto;">
		<!--<div id="iconDiv" class="coViewIconImage ico-large-report" style="float:left;"></div>-->
		<table><tr>
		  <td>
			<div class="coViewTitleContainer pull-left">
				<div class="coViewTitle">
	                <?php echo $title ?>
	            </div>
				
	            <p class="coViewDesc">
	                <?php  if ($description != '') echo clean($description); ?>
	            </p>
	        </div>
          </td><td style="min-width:400px;">
			<div class="report-buttons-container pull-left">
                <?php 
                
                if (is_numeric($id) && $id > 0) { // is a custom report ?>
                
                    <?php if (!isset($disable_print) || !$disable_print) {

                            render_report_header_button_small(array(
                                'name' => 'print', 'text' => lang("print"), 'title' => lang("print view"), 'onclick' => "og.reports.printReport('$genid','".escape_character($title)."', '$id');return false;", 'iconcls' => "ico-print"
                            ));

                        }

                        if ($allow_export) {

                            render_report_header_button_small(array(
                                'name' => 'exportCSV', 'text' => lang("csv"), 'title' => lang("export csv"), 'onclick' => "og.submit_csv_form('$genid', this);return false;", 'iconcls' => "ico-text"
                            ));

                            render_report_header_button_small(array(
                                'name' => 'exportPDFOptions', 'text' => lang("pdf"), 'title' => lang("export pdf"), 'onclick' => "og.openPDFOptions('$genid');", 'iconcls' => "ico-application-pdf"
                            ));

                            $null=null; Hook::fire('additional_custom_report_export_actions', array('genid' => $genid, 'report_id' => $id), $null);

                        }

                      } else { // predefined report

                        render_report_header_button_small(array(
                            'name' => 'print', 'text' => lang("print"), 'title' => lang("print view"), 'onclick' => "og.reports.printNoPaginatedReport('$genid','".escape_character($title)."', '$id');return false;", 'iconcls' => "ico-print"
                        ));

                        render_report_header_button_small(array(
                            'name' => 'exportCSV', 'text' => lang("csv"), 'title' => lang("export csv"), 'onclick' => "og.submit_fixed_report_csv_form('$genid', this);return false;", 'iconcls' => "ico-text"
                        ));


                      }


                      // close button
                      $on_close_click = "og.closeView();";
                      if (array_var($_REQUEST, 'go_to_tab')) {
                      	$on_close_click .= "var tab = Ext.getCmp('".array_var($_REQUEST, 'go_to_tab')."'); if (tab) Ext.getCmp('tabs-panel').setActiveTab(tab);" ;
                      }
                      render_report_header_button_small(array(
                      		'name' => 'print', 'text' => lang("close"), 'title' => lang("close"), 'onclick' => $on_close_click, 'iconcls' => "ico-back"
                      ));
                ?>
            </div>
		  </td>
		</tr></table>
		
		<input name="parameters" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
		<input name="context" type="hidden" value="" id="<?php echo $genid?>_plain_context"/>
		<div class="clear"></div>
		
		
        <div class="pull-left" style="padding: 0px 12px 0px;">
            <?php
            if (!isset($genid)) $genid = gen_id();
            custom_report_info_blocks(array('id' => $id, 'results' => $results, 'parameters' => $parameters, 'disabled_params' => $disabled_params));
            ?>
            <?php if (!isset($id)) $id= ''; ?>
            <input type="hidden" name="id" value="<?php echo $id ?>" />
            <input type="hidden" name="order_by" value="<?php echo $order_by ?>" />
            <input type="hidden" name="order_by_asc" value="<?php echo $order_by_asc ?>" />
            <div class="clear"></div>
        </div>
		<div class="clear"></div>
	</td>
	
	<td class="coViewTopRight" width="10px"></td>
</tr>
<tr>
	<td class="coViewRight" rowspan=1></td>
</tr>
<tr>
	<td colspan=2 class="coViewBody" style="padding: 0px;padding-top;1px;" id="<?php echo $genid?>report_container">
		<?php $this->includeTemplate(get_template_path($template_name, 'reporting'));?>
	</td>
	<td class="coViewRight"/>
</tr>
<tr>
	<td class="coViewBottomLeft"></td>
	<td class="coViewBottom" style="width:100%;"></td>
	
	<td class="coViewBottomRight"></td>
</tr>
</table>

</div>

<div id="pdfOptions" style="display:none;">
  <div class="coInputMainBlock" style="background-color:white; padding:10px; border-radius: 5px;">
	<div class="coInputTitle" style="min-width: 100px;margin-bottom:15px;">
		<?php echo lang('report pdf options') ?>
	</div>
	
	
	<?php 
		// Get config options to set default pdf layout and pdf size
		$pdf_layout = user_config_option('pdf_page_layout');
		$pdf_page_size = user_config_option('pdf_page_size');
		if($pdf_page_size == '') $pdf_page_size = 'A4';
	?>

	<div class="dataBlock">
		<label><?php echo lang('report pdf page layout') ?></label>
		<select name="pdfPageLayout" id="{gen_id}pdfPageLayout">
			<option value="P" <?php if($pdf_layout == 'Portrait') echo 'selected' ?>><?php echo lang('report pdf vertical') ?></option>
			<option value="L" <?php if($pdf_layout == 'Landscape') echo 'selected' ?>><?php echo lang('report pdf landscape') ?></option>
		</select>
	</div>
	
	<div class="dataBlock">
		<label><?php echo lang('page size') ?></label>
		<select name="pdfPageSize" id="{gen_id}pdfPageSize">
			<option value="A0"<?php if($pdf_page_size == 'A0') echo 'selected' ?>>A0</option>
			<option value="A1"<?php if($pdf_page_size == 'A1') echo 'selected' ?>>A1</option>
			<option value="A2" <?php if($pdf_page_size == 'A2') echo 'selected' ?>>A2</option>
			<option value="A3" <?php if($pdf_page_size == 'A3') echo 'selected' ?>>A3</option>
			<option value="A4" <?php if($pdf_page_size == 'A4') echo 'selected' ?>>A4</option>
			<option value="A5" <?php if($pdf_page_size == 'A5') echo 'selected' ?>>A5</option>
			<option value="Legal" <?php if($pdf_page_size == 'Legal') echo 'selected' ?>>Legal</option>
			<option value="Letter" <?php if($pdf_page_size == 'Letter') echo 'selected' ?>>Letter</option>
		</select>
	</div>
	
	<div class="dataBlock" style="display:none;">
		<label><?php echo lang('report font size') ?></label>
		<select name="pdfFontSize" id="{gen_id}pdfFontSize">
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="12" selected>12</option>
			<option value="13">13</option>
			<option value="14">14</option>
			<option value="15">15</option>
			<option value="16">16</option>
		</select>
	</div>
	
	<button type="submit" class="submit" name="exportPDF" onclick="og.submit_pdf_form('{gen_id}');">
		<?php echo lang('export') ?>
	</button>
	<div class="clear"></div>
	
  </div>
</div>

</form>

<script>

og.reorderCustomReport = function(link, report_id, order, order_by_asc) {

	var params_json = $(link).closest('form').children('input[name="params"]').val();
	var p = Ext.util.JSON.decode(params_json);

	var params = {};
	params.id = report_id;
	params.order_by = order;
	params.order_by_asc = order_by_asc;
	params.replace = 1;

	for (var x in p) {
		params['params['+x+']'] = p[x];
	}
	
	og.openLink(og.getUrl('reporting', 'view_custom_report', params));
};



document.getElementById('<?php echo $genid?>_plain_context').value = og.contextManager.plainContext();

og.disable_export_report_link = function(elem) {
	$(elem).attr('disabled','disabled');
	if (og.export_report_ts) {
		clearTimeout(og.export_report_ts);
	}
	og.export_report_ts = setTimeout(function() {
		$(elem).removeAttr('disabled');
	}, 1000);
}

og.submit_export_excel_form = function(genid, elem) {

	var params = og.get_report_parameters_of_form(genid);
	params['exportCSV'] = true;

	var report_id = $("#report_id_"+genid).val();
	og.openLink(og.getUrl('excel_export', 'export_custom_report_excel', {id: report_id}), {
		post: params,
		callback: function(success, data) {
			var $form = $("<form></form>");
			$form.attr("action", og.getUrl('reporting', 'download_file'));
			$form.attr("method", "post");
			$form.append('<input type="text" name="file_name" value="'+data.filename+'" />');
			$form.append('<input type="text" name="file_type" value="application/vnd.ms-excel" />');
			
			$form.appendTo('body').submit().remove();				
		}
	});

	og.disable_export_report_link(elem);
	
	return false;
}


</script>