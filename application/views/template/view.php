<?php
	/* @var $cotemplate COTemplate   */
	if (!$cotemplate->isTrashed()){
		if ($cotemplate->canEdit(logged_user())) {
			add_page_action(lang('edit'), $cotemplate->getEditUrl(), 'ico-edit', null, null, true);
		} // if
	} // if
	
	if ($cotemplate->canDelete(logged_user())) {
		add_page_action(lang('delete'), "javascript:if(confirm(lang('confirm delete object'))) og.openLink('" . $cotemplate->getDeleteUrl() ."');", 'ico-delete', null, null, true);
	} // if
?>

<div style="padding:7px">
<div class="template">
	<?php 
		$variables = array(
			"description" => nl2br(clean($cotemplate->getDescription())),
			"objects" => $cotemplate->getObjects()
		);
		tpl_assign("variables", $variables);
		tpl_assign("content_template", array('content', 'template'));
		tpl_assign("object", $cotemplate);
		tpl_assign('iconclass', $cotemplate->isTrashed()? 'ico-large-template-trashed' :  ($cotemplate->isArchived() ? 'ico-large-template-archived' : 'ico-large-template'));
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>