<?php

if (!isset($selected_color)) $selected_color = 1;
if (!isset($input_name)) $input_name = 'color_id';
if (!isset($genid)) $genid = gen_id();
if (!isset($input_id)) $input_id = $genid . 'color_id';

$input_id = str_replace('|','_',$input_id);
?>

<div style="display:inline-block;" class="color-selector-sample color-<?php echo $selected_color ?>"
	id="<?php echo $input_id."_sample"?>"
	>Abc</div>

<input type="hidden" name="<?php echo $input_name ?>" id="<?php echo $input_id ?>" value="<?php echo $selected_color ?>">
<div id="<?php echo $input_id."_palette_container"?>" style="display:none;">
	<div class="title-container">
		<span class="title"><?php echo lang('pick a color')?></span>
		<a href="#" class="modal-close-img" onclick="og.color_selector.close_palette('<?php echo $input_id ?>')"></a>
	</div>
	<table class="color-selector" id="<?php echo $input_id."_palette"?>"><tr>
	<?php 
	
	$i = 1;
	while ($i <= 26) {
		?><td><div class="color-selector-sample color-<?php echo $i ?> <?php echo ($selected_color==$i ? "selected" : "") ?>" 
				onclick="og.color_selector.select(<?php echo $i ?>, '<?php echo $input_id?>');">
			Abc
		</div></td><?php
		if ($i % 4 == 0) {
			?></tr><tr><?php
		}
		$i++;
	}
	?>
	</tr></table>
</div>
<script>

$("#<?php echo $input_id ?>_sample").popover({
	content: $("#<?php echo $input_id ?>_palette_container").html(),
	html: true,
	trigger: 'click',
	placement: 'right',
});

</script>