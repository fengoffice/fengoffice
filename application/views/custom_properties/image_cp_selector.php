<?php
	if (!isset($genid)) $genid = gen_id();
	
	$show_preview = false;
	$hf_value = "";
	$preview_url = "";
	
	if (trim($cp_value) != "") {
		
		$file_info = json_decode($cp_value, true);
		
		$hf_value = array_var($file_info, 'repository_id');
		if ($hf_value) {
			$show_preview = true;
			$preview_url = get_url('files', 'get_public_file', array('id' => $hf_value));
		}
	}

	$file_input_id = $genid.'cp'.$cp->getId();
	$hf_input_id = $genid.'hf'.$cp->getId();
	$file_input_name = "file_input_" . $cp->getId();
	
?>

<div id="<?php echo $genid ?>cp-image-selector-container" class="cp-image-selector-container">
	
	<div id="<?php echo $genid ?>cp-image-preview-<?php echo $cp->getId()?>" 
		class="cp-image-preview" style="<?php echo ($show_preview ? "" : "display:none;")?>">
		
		<img id="<?php echo $genid?>cp-preview-<?php echo $cp->getId()?>" alt="" src="<?php echo $preview_url?>"/>
		
		<?php if (!$disabled) { ?>
		<div class="remove-link">
			<a href="#" onclick="og.remove_image_custom_property('<?php echo $genid?>','<?php echo $cp->getId() ?>','<?php echo $hf_input_id ?>')" 
				class="remove link-ico ico-delete"><?php echo lang('remove')?></a>
		</div>
		<?php } ?>
	</div>
<?php
if (!$disabled) {
	echo file_field($file_input_name, null, array('id' => $file_input_id));
}
?>
	<input type="hidden" name="<?php echo $input_name ?>" 
		id="<?php echo $hf_input_id?>" value="<?php echo $hf_value ?>" />
	
</div>
<div class="clear"></div>

<script>

og.remove_image_custom_property = function (genid, cp_id, hf_id) {
	$("#"+hf_id).val("");
	$("#"+genid+"cp-image-preview-"+cp_id).hide();
}

og.upload_temp_custom_property_image = function(genid, file_input_id, file_input_name, hf_id, cp_id, config) {
	var fileInput = document.getElementById(file_input_id);

	var fileParent = fileInput.parentNode;
	fileParent.removeChild(fileInput);
	
	var form = document.createElement('form');
	form.method = 'post';
	form.enctype = 'multipart/form-data';
	form.encoding = 'multipart/form-data';
	form.action = og.getUrl('object', 'tmp_file_upload', {'id': genid + "_" + cp_id, 'input_name': file_input_name});
	form.style.display = 'none';
	form.appendChild(fileInput);
	document.body.appendChild(form);
	
	og.submit(form, {
		callback: function(d) {
			form.removeChild(fileInput);
			fileParent.appendChild(fileInput);
			document.body.removeChild(form);
			
			// set the hidden input
			if (d && d.id) {
				$("#"+hf_id).val(d.id);
			}
			
			if (config && config.callback && typeof(config.callback) == 'function') {
				config.callback.call(config.scope, d);
			}
		}
	});
}


$(document).ready(function() {
	
	var file_input_id = '<?php echo $file_input_id ?>';
	var genid = '<?php echo $genid?>';
	
	// prepare instant preview
	$("#"+file_input_id).change(function(){

		var cp_id = $(this).attr('id');
		cp_id = cp_id.substring(cp_id.indexOf("cp")+2);
		
		$("#"+genid+"cp-image-preview-"+cp_id).hide();

		// For browsers with HTML5 compatibility
		if (window.FileReader) {
			var fr = new FileReader();
			fr.readAsDataURL(document.getElementById($(this).attr('id')).files[0]);
			fr.finput_id = $(this).attr('id');

			fr.onload = function (fevent) {
				
				var cp_id = this.finput_id;
				cp_id = cp_id.substring(cp_id.indexOf("cp")+2);
				var hf_id = genid + 'hf' + cp_id;
				var file_input_name = "file_input_" + cp_id;
				
				$("#"+genid+"cp-preview-"+cp_id).attr('src', fevent.target.result);
				$("#"+genid+"cp-image-preview-"+cp_id).fadeIn();

				og.upload_temp_custom_property_image(genid, this.finput_id, file_input_name, hf_id, cp_id, {}); 
			};
		}
	});
});
</script>