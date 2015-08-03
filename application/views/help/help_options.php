<?php 
	$helpGenid = gen_id(); 
	set_page_title(lang('help'));
	$help_options = array(
		array(
			'title' => lang('help manual'),
			'desc' => '',
			'url' => help_link(),
			'target' => '_blank',
		),
		array(
			'title' => lang('about us'),
			'desc' => '',
			'url' => PRODUCT_URL,
			'target' => '_blank',
		),
	);
	Hook::fire('render_help_options', null, $help_options);
?>

<div style="height:100%;background-color:white;">
	<div class="adminHeader">
		<div class="adminTitle"><?php echo lang('help') ?></div>
	</div>
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">
	<div style="padding: 20px">
		
		<?php foreach ($help_options as $o) { ?>
			<div style="padding:10px 10px 20px 10px">
			<p style="font-size:16px;color:black;font-weight:bold"><a href="<?php echo array_var($o, 'url') ?>"<?php if (array_var($o, 'target')) { ?> target="_blank"<?php }?>><?php echo array_var($o, 'title')?></a></p>
			<?php if (array_var($o, 'desc')) {?>
			<p style="color:grey"><?php echo array_var($o, 'desc') ?></p>
			<?php } ?>
			</div>
		<?php }?>
	</div>
	</div>
</div>
