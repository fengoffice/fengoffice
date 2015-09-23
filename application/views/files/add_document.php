<?php
	require_javascript("og/modules/addFileForm.js");
	$genid = gen_id();
	$comments_required = config_option('file_revision_comments_required');
	
	$loc = user_config_option('localization');
	if (strlen($loc) > 2) $loc = substr($loc, 0, 2);
	
	$fname = $file->getObjectName();
	$extension = "html";
	if (!$file->isNew()) {
		$dot_pos = strrpos($fname, '.');
		if ($dot_pos !== false) {
			$extension = substr($fname, $dot_pos + 1);
			$fname = substr($fname, 0, $dot_pos);
		}
	}
	$fname_s = escape_character($fname);
?>

<form class="internalForm" style="height:100%; overflow:hidden;" id="<?php echo $genid ?>form" action="<?php echo get_url('files', 'save_document') ?>" method="post" enctype="multipart/form-data" onsubmit="return og.addDocumentSubmit('<?php echo $genid ?>');">
<input type="hidden" name="instanceName" value="<?php echo $genid ?>" />
<input type="hidden" id="<?php echo $genid ?>commentsRequired" value="<?php echo config_option('file_revision_comments_required')? '1':'0'?>"/>

<?php
	tpl_display(get_template_path('form_errors'));
	if($file->isNew()) {
		$ckEditorContent = '';
		$filename ='';
	} else {
		$content = $file->getFileContentWithRealUrls() ;		
		require_once LIBRARY_PATH . "/htmlpurifier/HTMLPurifier.standalone.php";
		$ckEditorContent = HTMLPurifier::instance()->purify($content);
		$filename = $file->getName();
	}

	// no back button
	ajx_set_no_back(true);
	
	// name input
	add_page_action("name_text_field", "javascript:", "", null, array('id' => $genid . 'filename_input', 'type' => 'textfield'));
	
	// save button
	add_page_action(lang("save"), "javascript:(function(){ var form = document.getElementById('{$genid}form'); document.getElementById('{$genid}new_revision_document').value = 'checked'; form.onsubmit(); })()", "save", null, array('id' => $genid . 'save_as_name'));
	
	// close button
	$panel_id = array_var($_REQUEST, 'current');
	add_page_action(lang("close"), "javascript: Ext.getCmp('$panel_id').back();", "ico-delete", null, array('id' => $genid . 'close'));
	

?>
 	<div>
		<input type="hidden" id="fileContent" name="fileContent" value="" />
		<input type="hidden" id="fileid" name="file[id]" value="<?php if (!$file->isNew()) echo $file->getId(); ?>" />
		<input type="hidden" id="<?php echo $genid ?>filename" name="file[name]" value="<?php if (!$file->isNew()) echo clean($fname); ?>" />
		<input type="hidden" id="<?php echo $genid ?>fileextension" name="file[extension]" value="<?php echo clean($extension); ?>" />
		<input type="hidden" id="<?php echo $genid ?>comment" name="file[comment]" value="" />
		<input type="hidden" id="<?php echo $genid ?>new_revision_document" name="new_revision_document" value="checked" />
		<input type="hidden" name="checkin" value="" />
	</div>
	

	<div id="<?php echo $genid ?>ckcontainer">
		<textarea style="display:none;" cols="80" id="<?php echo $genid ?>ckeditor" name="editor" rows="10" autocomplete="off"><?php echo clean($ckEditorContent) ?></textarea>
	</div>
</form>

<script>

var h = document.getElementById("<?php echo $genid ?>ckcontainer").offsetHeight;
var editor = CKEDITOR.replace('<?php echo $genid ?>ckeditor', {
	height: (h-100) + 'px',
	allowedContent: true,
	enterMode: CKEDITOR.ENTER_DIV,
	shiftEnterMode: CKEDITOR.ENTER_BR,
	disableNativeSpellChecker: false,
	language: '<?php echo $loc ?>',
	customConfig: '',
	contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
	toolbar: [
				['Source','-','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt','-',
				'Undo','Redo','-','Find','Replace','-','SelectAll', '-',
				'Format','Font','FontSize'],
				'/',
				['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-',
				'NumberedList','BulletedList','-','Outdent','Indent','Blockquote','-',
				'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
				'Link','Unlink', 'Anchor', '-','Maximize','-',
				'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','-', 
				'TextColor','BGColor','RemoveFormat']
			],
	
	keystrokes: [
		[ CKEDITOR.ALT + 121 /*F10*/, 'toolbarFocus' ],
		[ CKEDITOR.ALT + 122 /*F11*/, 'elementsPathFocus' ],

		[ CKEDITOR.SHIFT + 121 /*F10*/, 'contextMenu' ],

		[ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],
		[ CKEDITOR.CTRL + 89 /*Y*/, 'redo' ],
		[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],

		[ CKEDITOR.CTRL + 76 /*L*/, 'link' ],

		[ CKEDITOR.CTRL + 66 /*B*/, 'bold' ],
		[ CKEDITOR.CTRL + 73 /*I*/, 'italic' ],
		[ CKEDITOR.CTRL + 85 /*U*/, 'underline' ],

		//[ CKEDITOR.CTRL + 83 /*S*/, 'save' ],

		[ CKEDITOR.ALT + 109 /*-*/, 'toolbarCollapse' ]
	],
	filebrowserImageUploadUrl : '<?php echo ROOT_URL ?>/ck_upload_handler.php',
	on: {
		instanceReady: function(ev) {
			editor.resetDirty();
			og.resizeCkSpace();
		},
		change: function(ev) {
			var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>ckeditor'));
			Ext.getCmp(p.id).setPreventClose(editor.checkDirty());
		}
	},
	entities_additional : '#39,#336,#337,#368,#369'
});


og.eventManager.addListener("document saved", function(obj) {
	var form = Ext.getDom(obj.instance + 'form');
	if (!form) return;
	form['file[id]'].value = obj.id;
	form['file[comment]'].value = '';

	var instName = obj.instance + 'ckeditor';
	var editor = og.getCkEditorInstance(instName);
	if (editor) {
		if (editor.checkDirty()) editor.resetDirty();
		var p = og.getParentContentPanel(Ext.get(obj.instance + 'form'));
		Ext.getCmp(p.id).setPreventClose(false);
	}
}, null, {replace:true});


og.resizeresizeCkSpaceAux = function() {
	var container = document.getElementById('<?php echo $genid ?>form');
	var parent = document.getElementById('cke_<?php echo $genid ?>ckeditor');
	if (container && parent) {
		var iframes = parent.getElementsByTagName('iframe');
		if (iframes.length > 0) {
			iframes[0].style.height = (container.offsetHeight - 107) + 'px';
			parent.style.height = (container.offsetHeight) + 'px';
		}
	}
}
og.resizeFileNameInput = function() {
	var total_w = $("#<?php echo $genid?>filename_input").closest('.x-toolbar.x-small-editor').width();
	var buttons_w = 0;
	var buttons = $("#<?php echo $genid?>filename_input").parent().siblings();
	for (var i=0; i<buttons.length; i++) {
		buttons_w += $(buttons[i]).width();
	}
	var available_w = total_w - buttons_w - 30;
	$("#<?php echo $genid?>filename_input").css('width', (available_w)+'px');
}
og.resizeCkSpace = function() {
	if (Ext.isIE) setTimeout('og.resizeresizeCkSpaceAux()', 100);
	else og.resizeresizeCkSpaceAux();

	og.resizeFileNameInput();
}
window.onresize = og.resizeCkSpace;

$(function() {
	$("#<?php echo $genid?>filename_input").attr('placeholder', '<?php echo lang('filename')?>');
	$("#<?php echo $genid?>filename_input").val('<?php echo (!$file->isNew() ? $fname_s : ""); ?>');
	$("#<?php echo $genid?>filename_input").addClass('filename');
	$("#<?php echo $genid?>filename_input").change(function(event){
		$("#<?php echo $genid?>filename").val(event.currentTarget.value);
	});

	og.resizeFileNameInput();
});

</script>
