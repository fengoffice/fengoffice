<?php
require_javascript("og/ObjectPicker.js");
require_javascript("og/modules/addFileForm.js");
?>

<script>
	og.pickObjectToZip = function(zip_id) {
		og.ObjectPicker.show(function(objs) {
			if (objs.length < 1) return;
			if (objs[0].data.type != 'file') {
				og.msg(lang("error"), lang("must choose a file"));
				return;
			}
			obj_ids = '';
			for(i=0; i<objs.length; i++) {
				obj_ids += (obj_ids == '' ? '' : ',') + objs[i].data.object_id;
			}
			og.openLink(og.getUrl('files', 'zip_add', {id:zip_id, objects:obj_ids})); 
		}, this, {
			types: ['file'],
			selected_type: 'file'
		});
	}
	
</script>

<?php
if (isset($file) && $file instanceof ProjectFile) {
	$options = array();

	if (!$file->isTrashed()){
		if ($file && strcmp($file->getTypeString(), 'prsn')==0) {
			add_page_action(lang('slideshow'), "javascript:og.slideshow(".$file->getId().")", 'ico-slideshow');
		}
		
		if ($file && $file->isMP3()) {
			$songinfo = array(
				$file->getProperty("songname"),
				$file->getProperty("songartist"),
				$file->getProperty("songalbum"),
				$file->getProperty("songtrack"),
				$file->getProperty("songyear"),
				$file->getProperty("songduration"),
				$file->getDownloadUrl(),
				$file->getFilename(),
				$file->getId(),
			);
			$songdata = str_replace('"', "'", json_encode($songinfo));
			add_page_action(lang('play'), "javascript:og.playMP3(" . $songdata . ")", 'ico-play');
			add_page_action(lang('queue'), "javascript:og.queueMP3(" . $songdata . ")", 'ico-queue');
		} else if ($file && strcmp($file->getTypeString(), 'application/xspf+xml')==0) {
			add_page_action(lang('play'), "javascript:og.playXSPF(" . $file->getId() . ")", 'ico-play');
		}
		
		if (file_is_zip($file->getTypeString(), get_file_extension($file->getFilename())) && zip_supported()) {
			add_page_action(lang('extract'), get_url('files', 'zip_extract', array('id' => $file->getId())), 'ico-zip-extract');
			if ($file->canEdit(logged_user())) {
				add_page_action(lang('add files to zip'), "javascript:og.pickObjectToZip({$file->getId()})", 'ico-zip-add');
			}
		}
		
		if ($file->canEdit(logged_user())) {
			if (!$file->isArchived())
				add_page_action(lang('archive'), "javascript:if(confirm(lang('confirm archive object'))) og.openLink('" . $file->getArchiveUrl() ."');", 'ico-archive-obj');
			else
				add_page_action(lang('unarchive'), "javascript:if(confirm(lang('confirm unarchive object'))) og.openLink('" . $file->getUnarchiveUrl() ."');", 'ico-unarchive-obj');
		}
	}
	
	if ($file->canDownload(logged_user()) && $file->getType() != ProjectFiles::TYPE_WEBLINK) { 
		$url = $file->getDownloadUrl();
		if (config_option('checkout_notification_dialog')) { 
			$checkedOutById = $file->getCheckedOutById();
			if($checkedOutById != 0){
				$checkedOutByName = ($checkedOutById == logged_user()->getId() ?  "self" : Contacts::findById($checkedOutById)->getObjectName());
			}else{
				$checkedOutByName = '';
			}
			$file_id = $file->getId();
			add_page_action(lang('download') . ' (' . format_filesize($file->getFilesize()) . ')', "javascript:og.checkDownload('$url',$checkedOutById,'$checkedOutByName','$file_id');", 'ico-download', '', array("download" => true), true);
		} else {
			include_once ROOT . "/library/browser/Browser.php";
			if (Browser::instance()->getBrowser() == Browser::BROWSER_IE) {
				$url = "javascript:location.href = '$url';";
			}
			add_page_action(lang('download') . ' (' . format_filesize($file->getFilesize()) . ')', $url, 'ico-download', '_self', null, true);
		}
	}
	
	if ($file->getType() == ProjectFiles::TYPE_WEBLINK){
		add_page_action(lang('open weblink'), clean($file->getUrl()), 'ico-open-link', '_blank', null, true);
	}
	
	if (!$file->isTrashed()){
		if ($file->isCheckedOut()){
			if ($file->canCheckin(logged_user()) && $file->getType() == ProjectFiles::TYPE_DOCUMENT){
				//add_page_action(lang('checkin file'), $file->getCheckinUrl(), 'ico-checkin', null, null, true); 
				add_page_action(lang('undo checkout'), $file->getUndoCheckoutUrl() . "&show=redirect", 'ico-unlocked', null, null, true); 
			}
			
		} else {
			if ($file->canCheckout(logged_user()) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) { 
				add_page_action(lang('checkout file'), $file->getCheckoutUrl(). "&show=redirect", 'ico-locked', null, null, true);
			}
		}
		
		if ($file->canEdit(logged_user())) {
			if ($file->isModifiable() && $file->getType() != ProjectFiles::TYPE_WEBLINK) { 
				add_page_action(lang('edit this file'), $file->getModifyUrl(), 'ico-edit', null, null, true);
			}
			if (!$file->isModifiable() && $file->getType() != ProjectFiles::TYPE_WEBLINK) {
				// if file is checked out, only allow to upload to the user who has checked it out
				add_page_action(lang('upload new revision'), "javascript:og.uploadNewRevision(".$file->getId().",'".gen_id()."')", 'ico-upload', null, null, true);
			}
			if ($file->getType() != ProjectFiles::TYPE_WEBLINK){
				add_page_action(lang('edit file properties'), "javascript:og.render_modal_form('', {c:'files', a:'edit_file', params: {id:".$file->getId()."}});", 'ico-properties', null, null, true);
			} else {
				add_page_action(lang('edit'), "javascript:og.render_modal_form('', {c:'files', a:'edit_weblink', params: {id:".$file->getId()."}});", 'ico-edit', null, null, true);
			}
		}
	}
		
	if ($file->canDelete(logged_user())) {
		if ($file->isTrashed()) {
			add_page_action(lang('restore from trash'), "javascript:if(confirm(lang('confirm restore objects'))) og.openLink('" . $file->getUntrashUrl() ."');", 'ico-restore', null, null, true);
			add_page_action(lang('delete permanently'), "javascript:if(confirm(lang('confirm delete permanently'))) og.openLink('" . $file->getDeletePermanentlyUrl() ."');", 'ico-delete', null, null, true);
		} else {
			add_page_action(lang('move to trash'), "javascript:if(confirm(lang('confirm move to trash'))) og.openLink('" . $file->getTrashUrl() ."');", 'ico-trash', null, null, true);
		}
	}
	
	 if (/* FIXME can_add(logged_user(), active_or_personal_project(), 'ProjectFiles') &&*/ $file->getType() != ProjectFiles::TYPE_WEBLINK) {
		add_page_action(lang('copy file'), $file->getCopyUrl(), 'ico-copy');
	}

?>


<div style="padding:7px">
<div class="files">

<?php 
	$description = '';
	if ($last_revision instanceof ProjectFileRevision) { 
		$description .= '<div id="fileLastRevision"><span class="propertyName">' . lang('last revision') . ':</span>'; 
		if ($last_revision->getCreatedBy() instanceof Contact) {
			$description .= lang('file revision info long', $last_revision->getRevisionNumber(), $last_revision->getCreatedBy()->getCardUserUrl(), clean($last_revision->getCreatedBy()->getObjectName()), format_descriptive_date($last_revision->getCreatedOn()));
		} else {
			$description .= lang('file revision info short', $last_revision->getRevisionNumber(), format_descriptive_date($last_revision->getCreatedOn()));
		}
		$description .= "</div>";
	} // if
	
	if ($file->isCheckedOut()) {
		$description .= '<div id="fileCheckedOutBy" class="coViewAction ico-locked">';
		if($file->getCheckedOutBy() instanceof Contact) {
			$description .= lang('file checkout info long', $file->getCheckedOutBy()->getCardUserUrl(), clean($file->getCheckedOutBy()->getObjectName()), format_descriptive_date($file->getCheckedOutOn()). ", " . format_time($file->getCheckedOutOn()));
		} else {
			$description .= lang('file checkout info short', format_descriptive_date($file->getCheckedOutOn()). ", " . format_time($file->getCheckedOutOn()));
		} // if
		$description .= "</div>";
	} // if

	if (!$file->isTrashed() && !$file->isArchived() && $file->getType() != ProjectFiles::TYPE_WEBLINK) {
		tpl_assign('image', '<div class="coViewIconImage"><img src="' . $file->getTypeIconUrl(false) .'" alt="' . clean($file->getFilename()) . '" /></div>');
	}
	if ($file->isTrashed()) {
		tpl_assign('iconclass', 'ico-large-files-trashed');
	} else if($file->isArchived()) {
		tpl_assign('iconclass', 'ico-large-files-archived');
	} else {
		tpl_assign('iconclass', $file->getType() != ProjectFiles::TYPE_WEBLINK? 'ico-large-files':'ico-large-webfile');
	}
	
	tpl_assign('description', $description);
	/*if ($file->getType() == ProjectFiles::TYPE_WEBLINK){
		tpl_assign('title', '<a class="link-ico ico-open-link" href="' . $file->getUrl() . '">' . clean($file->getFilename()) . '</a>');
	} else {
		tpl_assign('title', clean($file->getFilename()));
	}*/
	$file_details_content_template_info = array('file_details_content', 'files');
	Hook::fire('override_file_details_content', null, $file_details_content_template_info);
	tpl_assign("content_template", $file_details_content_template_info);
	tpl_assign('object', $file);

	$this->includeTemplate(get_template_path('view', 'co'));
?>
</div>
</div>
<?php } //if isset ?>

