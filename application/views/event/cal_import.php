<?php
	$submit_url = get_url('event', 'icalendar_import');
	$genid = gen_id();
?>

<script>
og.submitIcalFile = function(genid) {
	fname = document.getElementById(genid + 'filenamefield');
	if (fname.value != '') {
		form = document.getElementById(genid + 'calimport');
		form.action += "&context=" + og.contextManager.plainContext();
		og.submit(form);
	}
}
</script>

<form style="height:100%;background-color:white" id="<?php echo $genid ?>calimport" name="<?php echo $genid ?>calimport" class="internalForm" action="<?php echo $submit_url ?>" method="post" enctype="multipart/form-data">

<div class="file">
<div class="coInputHeader">
<div class="coInputHeaderUpperRow">
<div class="coInputTitle">
	<table style="width:535px"><tr><td><?php echo lang('import events from file');?></td>
	<td style="text-align:right"><?php echo submit_button(lang('import'), 's', array('style'=>'margin-top:0px;margin-left:10px', 'tabindex' => '10','id' => $genid.'cal_import_submit1')) ?></td>
	</tr></table>
</div>
</div>

<div id="<?php echo $genid ?>selectFileControlDiv">
    <?php echo label_tag(lang('file'), $genid . 'filenamefield', true) ?>
    <?php echo file_field('cal_file', null, array('id' => $genid . 'filenamefield', 'tabindex' => '1', 'class' => 'title', 'size' => '88', "onchange" => 'javascript:og.submitIcalFile(\'' . $genid .'\')')) ?>
    <input type="hidden" name="atimportform" value="1"></input>
    <input type="hidden" name="subscribers[user_<?php echo logged_user()->getId() ?>]" value="checked"></input>
</div>

</div>
<div class="coInputMainBlock adminMainBlock">
	<span><b><?php echo lang('file should be in icalendar format') ?></b></span>
</div>
</div>
</form>
<script>
	Ext.get('<?php echo $genid ?>filenamefield').focus();
</script>