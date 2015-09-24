<?php
if (!isset($genid)) $genid = gen_id();
if (!isset($allow_export)) $allow_export = true;
  	/*add_page_action(lang('print view'), '#', "ico-print", "_blank", array('onclick' => 'this.form' . $genid . '.submit'));*/
?>
<form id="form<?php echo $genid ?>" name="form<?php echo $genid ?>" action="<?php echo get_url('reporting', $template_name . '_print') ?>" method="post" enctype="multipart/form-data" target="_download">

    <input id="post<?php echo $genid ?>" name="post" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
    
<div class="report" style="padding:7px">
<table style="min-width:600px">
<tr>
	<td rowspan=2 colspan="2" class="coViewHeader" style="width:auto;">
		<div id="iconDiv" class="coViewIconImage ico-large-report" style="float:left;"></div>

		<div class="coViewTitleContainer">
			<div class="coViewTitle" style="margin-left:55px;"><?php echo $title ?></div>
			<input type="submit" name="print" value="<?php echo lang('print view') ?>" onclick="og.reports.printReport('<?php echo $genid?>','<?php echo escape_character($title) ?>'); return false;" style="width:150px; margin-top:10px;"/>

			<input type="submit" name="exportCSV" value="<?php echo lang('export csv') ?>" onclick="og.submit_csv_form('<?php echo $genid ?>');return false;" style="width:150px; margin-top:10px;"/>
			
		<?php if ($allow_export) { ?>
			<input type="button" name="exportPDFOptions" onclick="og.showPDFOptions();" value="<?php echo lang('export pdf') ?>" style="width:150px; margin-top:10px;"/>
		<?php } ?>
			<input name="parameters" type="hidden" value="<?php echo str_replace('"',"'", json_encode($post))?>"/>
			<input name="context" type="hidden" value="" id="<?php echo $genid?>_plain_context"/>
		</div>
		<div class="clear"></div>
	</td>
	
	<td class="coViewTopRight" width="10px"></td>
</tr>
<tr>
	<td class="coViewRight" rowspan=1></td>
</tr>
<tr>
	<td colspan=2 class="coViewBody" style="padding-left:12px" id="<?php echo $genid?>report_container">
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
</form>
<script>
document.getElementById('<?php echo $genid?>_plain_context').value = og.contextManager.plainContext();

og.submit_csv_form = function(genid) {
	var form_id = 'form' + genid;
	var form = document.getElementById(form_id);

	var post_id = 'post' + genid;
	var post_el = document.getElementById(post_id);
	var post = Ext.util.JSON.decode(post_el.value);

	var params = {'exportCSV': true};
	for (x in post) {
		
		if(x == 'params'){
			params["report_params"] = Ext.util.JSON.encode(post[x]);
		}else if (x != 'c' && x != 'a' && x != 'ajax') {
			params[x] = post[x];

			var i = document.createElement("input");
			i.type= "hidden";
			i.value = post[x];
			i.name = x;
			form.appendChild(i);
		}
	}

	og.openLink(form.action, {
		post: params,
		callback: function(success, data) {
			var $form = $("<form></form>");
			$form.attr("action", og.getUrl('reporting', 'download_file'));
			$form.attr("method", "post");
			$form.append('<input type="text" name="file_name" value="'+data.filename+'" />');
			$form.append('<input type="text" name="file_type" value="application/csv" />');
			
			$form.appendTo('body').submit().remove();				
		}
	});
	return false;
}

</script>