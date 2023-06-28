<?php

if (!isset($genid)) $genid = gen_id();
if (!isset($links)) $links = array();
if (!isset($title)) $title = "";

$main_id = $genid . "dropdown_links";
$container_id = $main_id . "_container";

?>

<div id="<?php echo $container_id ?>" style="display:none;">
	<div id="<?php echo $main_id ?>">
		<div class="title-container">
			<span class="title"><?php echo $title ?></span>
			<a href="#" class="modal-close-img" onclick="og.dropdown_links.close('<?php echo $trigger_id ?>')"></a>
		</div>
		<ul class="dropdown-links" id="<?php echo $main_id . "_list" ?>">
		<?php foreach ($links as $link) { ?>
		  <li>
			<a href="<?php echo array_var($link, 'href', '#') ?>" 
				class="<?php echo array_var($link, 'class', '') ?>"
				onclick="<?php echo array_var($link, 'onclick', '') ?>; og.dropdown_links.close('<?php echo $trigger_id ?>'); return false;"><?php echo array_var($link, 'text', '') ?></a>
		  </li>
		<?php } ?>
		</ul>
	</div>
</div>
<script>

og.dropdown_links = {};

// hide popover
og.dropdown_links.close = function(trigger_id) {
	$("#"+trigger_id).popover('hide');
}

// show popover when clicking in trigger_id
$("#<?php echo $trigger_id ?>").popover({
	content: $("#<?php echo $container_id ?>").html(),
	html: true,
	trigger: 'click',
	placement: 'bottom',
});


</script>