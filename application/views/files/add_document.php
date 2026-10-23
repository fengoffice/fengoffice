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
		if (defined('SANDBOX_URL')) {
			$ckEditorContent = $file->getFileContentWithRealUrls();
		} else {
			$ckEditorContent = purify_html($file->getFileContentWithRealUrls());
		}
		
		$filename = $file->getName();
	}

	// no back button
	ajx_set_no_back(true);
	
	// name input
	add_page_action("name_text_field", "javascript:", "", null, array('id' => $genid . 'filename_input', 'type' => 'textfield'));
	
	// save button
	add_page_action("<i class='icon-save'></i>" . lang("save"), "javascript:(function(){ var form = document.getElementById('{$genid}form'); document.getElementById('{$genid}new_revision_document').value = 'checked'; form.onsubmit(); })()", "btn btn-primary", null, array('id' => $genid . 'save_as_name'));
	
	// close button
	$panel_id = array_var($_REQUEST, 'current');
	add_page_action("<i class='icon-circle-x'></i>" . lang("close"), "javascript: Ext.getCmp('$panel_id').back();", "btn", null, array('id' => $genid . 'close'));
	

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
	enterMode: CKEDITOR.ENTER_BR,
	shiftEnterMode: CKEDITOR.ENTER_BR,
	disableNativeSpellChecker: false,
	language: '<?php echo $loc ?>',
	customConfig: '',
	contentsCss: ['<?php echo get_javascript_url('ckeditor/contents.css').'?rev='.product_version_revision();?>', '<?php echo get_stylesheet_url('og/ckeditor_override.css').'?rev='.product_version_revision();?>'],
	toolbar: [
				['Source','-','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt','-',
				'Undo','Redo','-','Find','Replace','-','SelectAll',
				'Format','Font','FontSize'],
				'/',
				['Bold','Italic','Underline','Strike','-','Subscript','Superscript','-',
				'NumberedList','BulletedList','-','Outdent','Indent','Blockquote','-',
				'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-',
				'Link','Unlink', 'Anchor', '-','Maximize','-',
				'Image','Youtube','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','-', 
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
			var layoutEditor = og.docEditorLayout && og.docEditorLayout.editors['<?php echo $genid ?>'];
			if (layoutEditor) layoutEditor.resize();
		},
		change: function(ev) {
			var p = og.getParentContentPanel(Ext.get('<?php echo $genid ?>ckeditor'));
			Ext.getCmp(p.id).setPreventClose(editor.checkDirty());
		}
	},
	fillEmptyBlocks: false,

	extraPlugins: 'youtube',
	
	removePlugins: 'scayt,liststyle,magicline',
	entities_additional : '#336,#337,#368,#369'
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


og.docEditorLayout = og.docEditorLayout || {
	editors: {},
	bound: false,
	bind: function() {
		if (this.bound) return;
		this.bound = true;
		var registry = this;
		Ext.EventManager.onWindowResize(function() {
			for (var id in registry.editors) {
				if (registry.editors.hasOwnProperty(id)) registry.editors[id].resize();
			}
		});
	}
};
(function() {
	var genid = '<?php echo $genid ?>';
	var editor = {
		retryTimer: null,
		inputTimer: null
	};
	og.docEditorLayout.editors[genid] = editor;
	og.docEditorLayout.bind();

	function clearRetry() {
		if (editor.retryTimer) {
			clearTimeout(editor.retryTimer);
			editor.retryTimer = null;
		}
	}
	// One chain per editor. A second Add Document must not reuse this timer or this closure.
	function scheduleRetry(fn) {
		clearRetry();
		editor.retryTimer = setTimeout(function() {
			editor.retryTimer = null;
			fn();
		}, 50);
	}

	editor.resizeCk = function() {
		var container = document.getElementById(genid + 'form');
		var parent = document.getElementById('cke_' + genid + 'ckeditor');
		if (!container || !parent) return;
		var panelEl = og.getParentContentPanel(Ext.get(container));
		if (panelEl) {
			var panel = Ext.getCmp(panelEl.id);
			if (panel && panel.active === false) return;
		}
		if (container.offsetHeight < 120) return;
		var iframes = parent.getElementsByTagName('iframe');
		if (iframes.length > 0) {
			iframes[0].style.height = (container.offsetHeight - 107) + 'px';
			parent.style.height = (container.offsetHeight) + 'px';
		}
	};
	editor.resizeFileName = function(attempt) {
		if (typeof attempt !== 'number') attempt = 0;
		var $input = $('#' + genid + 'filename_input');
		var total_w = $input.length ? $input.closest('.x-toolbar.x-small-editor').width() : 0;
		// The editor HTML is eval'd before Ext finishes laying out the toolbar.
		if (!$input.length || !total_w || total_w < 80) {
			if (attempt < 20) scheduleRetry(function() { editor.resizeFileName(attempt + 1); });
			return;
		}
		clearRetry();
		var buttons_w = 0;
		$input.parent().siblings().each(function() {
			buttons_w += $(this).outerWidth(true);
		});
		// Never grow past the real gap. A forced 180px floor overlaps Save/Close on a narrow bar.
		var max_w = total_w - buttons_w - 24;
		if (max_w < 1) max_w = 1;
		var text = $input.val() || $input.attr('placeholder') || '';
		var text_w = Ext.util.TextMetrics.measure($input[0], text).width + 36;
		var w = text_w < 180 ? 180 : text_w;
		if (w > max_w) w = max_w;
		var cmp = Ext.getCmp(genid + 'filename_input');
		if (cmp && cmp.setWidth) cmp.setWidth(w);
		else $input.css('width', w + 'px');
	};
	editor.resize = function() {
		if (!document.getElementById(genid + 'form')) {
			clearRetry();
			if (editor.inputTimer) clearTimeout(editor.inputTimer);
			delete og.docEditorLayout.editors[genid];
			return;
		}
		if (Ext.isIE) setTimeout(editor.resizeCk, 100);
		else editor.resizeCk();
		editor.resizeFileName();
	};

	$(function() {
		var $input = $('#' + genid + 'filename_input');
		$input.attr('placeholder', '<?php echo lang('filename')?>');
		$input.val('<?php echo (!$file->isNew() ? $fname_s : ""); ?>');
		$input.addClass('filename');
		// keyup covers IE, where "input" does not fire for backspace/delete.
		$input.on('input keyup change', function(event) {
			$('#' + genid + 'filename').val(event.currentTarget.value);
			if (editor.inputTimer) clearTimeout(editor.inputTimer);
			editor.inputTimer = setTimeout(function() {
				editor.inputTimer = null;
				editor.resizeFileName();
			}, 50);
		});
		editor.resizeFileName();
	});
})();

</script>
