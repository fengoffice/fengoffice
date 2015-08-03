<?php

	require_javascript('slimey/slimey.js');
	require_javascript('slimey/functions.js');
	require_javascript('slimey/stack.js');
	require_javascript('slimey/editor.js');
	require_javascript('slimey/navigation.js');
	require_javascript('slimey/actions.js');
	require_javascript('slimey/tools.js');
	require_javascript('slimey/toolbar.js');
	require_javascript('slimey/integration.js');
	require_javascript('og/ImageChooser.js');

	set_page_title($file->isNew() ? lang('new presentation') : lang('edit presentation'). ' - ' . $file->getFilename());
	project_tabbed_navigation(PROJECT_TAB_FILES);
	project_crumbs(array(
		array(lang('files'), get_url('files')),
		array($file->isNew() ? lang('add presentation') : lang('edit presentation'))
	));
?>

<?php
	if (!$file->isNew()) {
		$url = str_replace("&amp;", "&", get_url('files', 'save_presentation', array(
				'id' => $file->getId())));
		$filename = $file->getFilename();
		$slimContent = escapeSLIM(remove_css_and_scripts($file->getFileContent()));
	} else {
		$url = str_replace("&amp;", "&", get_url('files', 'save_presentation'));
		$filename = '';
		$slimContent = escapeSLIM('<div class="slide"><div style="font-size: 200%; font-weight: bold; font-family: sans-serif; position: absolute; left: 5%; top: 0%; width: 90%; height: 10%; text-align: center;">'.lang("new presentation").'</div></div>');
	}
	$id = gen_id();
?>

<div id="<?php echo $id ?>" style="width: 100%; height: 100%; overflow: hidden;">
</div>

<script>
	var panel = Ext.getCmp(og.getParentContentPanel('<?php echo $id ?>').id);
	var <?php echo $id ?> = new Slimey({
		container: "<?php echo $id ?>",
		rootDir: '<?php echo SLIMEY_PATH ?>',
		imagesDir: '<?php echo get_theme_url("slimey/images/") ?>',
		filename: <?php echo ($file->isNew()?"''":json_encode($file->getFilename())) ?>,
		fileId: <?php echo ($file->isNew()?0:$file->getId()) ?>,
		slimContent: '<?php echo $slimContent ?>',
		saveUrl: '<?php echo $url ?>'
	});
	<?php echo $id ?>.layout();
	setTimeout(function() {
		<?php echo $id ?>.layout();
	}, 1000);
	panel.on('resize', <?php echo $id ?>.layout, <?php echo $id ?>);
	
	// for the image chooser
	imagesUrl = '<?php echo get_url('files', 'list_files', array('type' => 'image', 'ajax' => 'true')) ?>';
	
	og.eventManager.addListener("presentation saved", function(obj) {
		this.config.fileId = obj.id;
		this.isDirty = false;
		this.onDirty();
	}, <?php echo $id ?>, {replace:true});
	document.getElementById('<?php echo $id ?>').slimey = <?php echo $id ?>;

</script>

<?php
if (config_option('checkout_for_editing_online')) {
	ajx_on_leave("og.openLink('" . get_url('files', 'release_file', array('id' => $file->getId())) . "')");
	add_page_action(lang("checkin file"), "javascript:(function(){ document.getElementById('$id').slimey.submitFile(true, false, 1); })()", "ico-checkin");
}

add_page_action(lang("save as").' <b>'.$filename.'</b>', "javascript:(function(){ document.getElementById('$id').slimey.submitFile(true); })()", "save");
if(!$file->isNew()) add_page_action(lang("save with a new name"), "javascript:(function(){ document.getElementById('$id').slimey.submitFile(true, true); })()", "save_as");
?>

<?php tpl_display(get_template_path('form_errors')) ?>


