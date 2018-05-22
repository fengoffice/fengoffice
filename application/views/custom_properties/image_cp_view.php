<?php
	if (!isset($genid)) $genid = gen_id();
	$preview_url = "";
	
	if (trim($cp_value) != "") {

		$file_info = json_decode($cp_value, true);
		$repo_id = array_var($file_info, 'repository_id');
		if ($repo_id) {
			$preview_url = get_url('files', 'get_public_file', array('id' => $repo_id));
		}
	}
?>
<div class="cp-image-view <?php echo $add_class ?>">
	<img id="<?php echo $genid?>cp-image-view-<?php echo $cp->getId()?>"
		alt="" src="<?php echo $preview_url?>"/>
</div>
