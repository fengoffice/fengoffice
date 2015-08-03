<?php
	set_page_title(lang('edit file') . ' - ' . clean($file->getFilename()));
	$iname = 'textedit' . $file->getId();
	$filecontent = $file->getFileContent();
?>
<form class="internalForm" id="<?php echo $iname ?>" action="<?php echo get_url('files', 'text_edit') ?>" method="post" enctype="multipart/form-data">

	<div>
		<input type="hidden" name="file[id]" value="<?php echo $file->getId(); ?>" />
		<input type="hidden" name="file[name]" value="<?php echo clean($file->getFilename()); ?>" />
		<input type="hidden" name="file[encoding]" value="<?php echo detect_encoding($filecontent, array('UTF-8','ISO-8859-1')); ?>" />
		<input type="hidden" name="new_revision_document" value="" />
 	</div>

	<textarea class="textedit" name="fileContent"><?php
		echo htmlEntities(EncodingConverter::instance()->convert(detect_encoding($filecontent, array('UTF-8','ISO-8859-1')),'UTF-8',$filecontent), null, 'UTF-8');
	?></textarea>
<?php
add_page_action(lang("save"), "javascript:(function(){ var form = document.getElementById('$iname'); form.new_revision_document.value = 'checked'; form.onsubmit(); })()", "save");
?>
</form>
