<?php
	$submit_url = get_url('event', 'icalendar_export_new');
	$genid = gen_id();
?>

<form onsubmit="return false;" style="height:100%;background-color:white" id="<?php echo $genid ?>calexport" name="<?php echo $genid ?>calexport" class="internalForm" action="<?php echo $submit_url ?>" method="post" enctype="multipart/form-data">

<div class="file">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
	<table style="width:535px"><tr><td><?php echo lang('export calendar');?></td>
	</tr></table>
</div>
</div>
</div>
<div class="coInputMainBlock adminMainBlock">
<?php if (!isset($result_msg)) { ?>
<span><b><?php echo lang('calendar will be exported in icalendar format') ?></b></span>
<?php 
	$from_date = DateTimeValueLib::now();
	$from_date->add('M', -6);
	$to_date = DateTimeValueLib::now();
	$to_date->add('M', 6);
?>
<fieldset><legend><?php echo lang('range of events') ?></legend>
	<table><tr style="padding-bottom:4px;">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('from date') ?></td>
		<td><?php echo pick_date_widget2('from_date', $from_date, $genid, 20); ?></td></tr>
	<tr style="padding-bottom:4px;">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('to date') ?></td>
		<td><?php echo pick_date_widget2('to_date', $to_date, $genid, 30);  ?></td></tr>

	<tr style="padding-bottom:4px;">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('include tasks') ?></td>
		<td><label for='export_tasks'><input type='checkbox' name='export_tasks' id='export_tasks' style="margin: 5px 0;" /></label></span></td></tr>


	<tr style="padding-bottom:4px;">
		<td align="right" style="padding-right:10px;padding-bottom:6px;padding-top:2px"><?php echo lang('name') ?></td>
		<td><?php echo text_field('calendar_name', logged_user()->getObjectName(), array("style" => "width:120px;", 'tabindex' => '40')) ?></td><td><span class="desc"><?php echo lang('calendar name desc') ?></span></td></tr>
	</table>
	<table style="width:535px">
	<tr><td><?php echo button(lang('export'), 'e', array('style'=>'margin-top:10px;margin-left:10px', 'tabindex' => '50', 'id' => $genid.'cal_export_submit1', 'onclick'=>"og.submitCalendarExport();")) ?></td></tr></table>
</fieldset>
</div>
<?php } else { ?>
	<div><b><?php echo $result_msg ?></b></div>
<?php } ?>

</div>
</form>
<script>
	
	var genid = '<?php echo $genid ?>';
	
	og.submitCalendarExport = function() {
		var form = document.getElementById(genid + 'calexport');
		form.action += "&context=" + escape(og.contextManager.plainContext());
		og.submit(form, {
			callback: function() {
			}
		});
		og.hideLoading();
		og.closeView();
	}

	btn = Ext.get('<?php echo $genid ?>cal_export_submit1');
	if (btn != null) btn.focus()
</script>