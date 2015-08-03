<?php
$genid = gen_id();
$gs_step = config_option('getting_started_step');
$all_set = array_var($_REQUEST, 'more_settings_expanded');
?>

<div class="more-panel-container">
	<div class="title"><?php echo lang('learn about and manage your Feng Office')?></div>

<?php if (can_manage_configuration(logged_user()) || can_manage_security(logged_user()) || can_manage_templates(logged_user()) || can_manage_billing(logged_user())) {?>
	<div class="more-panel-section">
		<h1><?php echo lang('quick system configuration and settings')?></h1>
		<div class="section-content section1" style="<?php echo (($gs_step < 99 && !$all_set) ? "max-width:100%;" : "")?>">
		<?php
			tpl_assign('genid', $genid);
			$this->includeTemplate(get_template_path('section1', 'more'));
		?>
		</div>
	</div>
<?php } ?>

	<div class="more-panel-section">
		<h1><?php echo lang('quick help')?></h1>
		<div class="section-content section2">
		<?php
			tpl_assign('genid', $genid);
			$this->includeTemplate(get_template_path('section2', 'more'));
		?>
		</div>
	</div>
	
	<div class="more-panel-section">
		<h1><?php echo lang('personal settings')?></h1>
		<div class="section-content section3">
		<?php
			tpl_assign('genid', $genid);
			$this->includeTemplate(get_template_path('section3', 'more'));
		?>
		</div>
	</div>

</div>
<script>
og.animate_highlighted_count = 0;
og.animate_highlighted = function(selector) {
	if (og.animate_highlighted_count > 10 && og.animate_highlighted_interval) {
		clearInterval(og.animate_highlighted_interval);
	}

	if (!selector) selector = ".link.highlighted.on";
	
	$(selector).effect( "shake", {direction:'up', distance: 5, times: 3});
	og.animate_highlighted_count = og.animate_highlighted_count + 1;
}

$(function(){
	$(".more-panel-container").parent().css('backgroundColor', 'white');
	
	og.animate_highlighted_interval = setInterval('og.animate_highlighted();', 3000);
	
});
</script>