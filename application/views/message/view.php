<?php
if (isset($message) && $message instanceof ProjectMessage) {
	if (!$message->isTrashed()){
		if($message->canEdit(logged_user())) {
			add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'message', a:'edit', params: {id:".$message->getId()."}});", 'ico-edit', null, null, true);
			if (!$message->isArchived())
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $message->getArchiveUrl() ."');", 'ico-archive-obj');
			else
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $message->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
		} // if
	}
	if ($message->canDelete(logged_user())) {
		if ($message->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $message->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $message->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $message->getTrashUrl() ."');", 'ico-trash', null, null, true);
		}
	} // if
	add_page_action(lang('print view'), $message->getPrintViewUrl(), "ico-print", "_blank");
?>

<div style="padding:7px">
<div class="message">
<?php
	if($message->getTypeContent() == "text"){
		$content = escape_html_whitespace(convert_to_links(clean($message->getText())));
	}else{
		$content = '<div class="wysiwyg-description">' . convert_to_links(purify_html(nl2br($message->getText()))) . '</div>';
	}
				
	tpl_assign("content", $content);
	tpl_assign("object", $message);
	tpl_assign('iconclass', $message->isTrashed()? 'ico-large-message-trashed' : ($message->isArchived() ? 'ico-large-message-archived' : 'ico-large-message'));
	
	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>
<?php } //if isset ?>
