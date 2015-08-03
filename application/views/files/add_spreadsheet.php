<?php
  set_page_title($file->isNew() ? lang('new spreadsheet') : lang('edit spreadsheet'). ' - ' . $file->getFilename());
  $url = ROOT_URL . "/" . PUBLIC_FOLDER . "/assets/javascript/gelSheet/index.php";	
?>
<div style="width:100%;height:100%;border:0px;margin:0px;padding:0px;overflow:hidden">
	<iframe style="width:100%;height:100%;border:0px;margin:0px;padding:0px;overflow:hidden"
	<?php if ($file->isNew()) { ?>
		src="<?php echo $url . "?wid=" . active_or_personal_project()->getId() ?>">
	<?php } else { ?>
		src="<?php echo $url . "?id=" . $file->getId() . "&book=" . $file->getFileContent()."&wid=" . active_or_personal_project()->getId() ?>">
	<?php } ?>
	</iframe>
</div>
