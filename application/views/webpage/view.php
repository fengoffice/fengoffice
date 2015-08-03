<?php
if (isset($object) && $object instanceof ProjectWebpage) {
	add_page_action(lang('open weblink'), clean($object->getUrl()), "ico-open-link", "_blank", null, true);
	if (!$object->isTrashed()) {
		if ($object->canEdit(logged_user())) {
			add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'webpage', a:'edit', params: {id:".$object->getId()."}});", 'ico-edit', null, null, true);
			if (!$object->isArchived()){
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $object->getArchiveUrl() ."');", 'ico-archive-obj');
			}else{
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $object->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
			}
		} 
	}
	if ($object->canDelete(logged_user())) {
		if ($object->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $object->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $object->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $object->getTrashUrl() ."');", 'ico-trash', null, null, true);
		}
	} // if
?>

<div style="padding:7px">
<div class="weblink">
	<?php
		$description = escape_html_whitespace(convert_to_links(clean($object->getDescription())));
		$url = clean($object->getUrl());
		$title = clean($object->getObjectName());
		tpl_assign("url", $url);
		tpl_assign("desc", $description);
		tpl_assign("content_template", array('view_content', 'webpage'));
		tpl_assign("object", $object);
		tpl_assign('iconclass', $object->isTrashed()? 'ico-large-weblink-trashed' : ($object->isArchived() ? 'ico-large-weblink-archived' : 'ico-large-weblink')); 
		//tpl_assign('title', "<a class=\"link-ico ico-open-link\" target=\"_blank\" href=\"$url\">$title</a>");
		
		$this->includeTemplate(get_template_path('view', 'co'));
	?>
</div>
</div>
<?php } //if isset ?>
