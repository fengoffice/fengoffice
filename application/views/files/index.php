<?php
	set_page_title(lang('documents'));
	//project_tabbed_navigation(PROJECT_TAB_FILES);
	$files_crumbs = array(
		0 => array(lang('files'), get_url('files'))
	); // array
/*if($current_folder instanceof ProjectFolder) {
		$files_crumbs[] = array($current_folder->getName(), $current_folder->getBrowseUrl($order));
	} // if
*/	$files_crumbs[] = array(lang('index'));
	
	project_crumbs($files_crumbs);
	
	//add_stylesheet_to_page('file/files.css');
	add_javascript_to_page('file/slideshow.js');

?>

<div id="file-manager"></div>

<script>
	var fm = new og.FileManager({
		<?php if ($projectParam) echo "project: " . $projectParam . "," ?>
		<?php if ($userParam) echo "user: " . $userParam . "," ?>
		<?php if ($typeParam) echo "type: ''" . $typeParam . "'," ?>
		<?php if ($tagParam) echo "tag: ''" . $tagParam . "'," ?>
		nada: true
	});
	fm.setHeight(400);
	fm.render('file-manager');
</script>
