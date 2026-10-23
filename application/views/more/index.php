<?php
$genid = gen_id();
$gs_step = config_option('getting_started_step');
$all_set = array_var($_REQUEST, 'more_settings_expanded');

if ($gs_step < 99 && !$all_set) {
	// the initial setup is not finished: the tab shows the getting started checklist
	tpl_assign('genid', $genid);
	$this->includeTemplate(get_template_path('getting_started', 'more'));
	return;
}
?>

<div class="more-panel-container">
<?php if ($gs_step < 99) { // the settings were opened while the initial setup is still active ?>
	<a class="internalLink gs-back-to-checklist" href="<?php echo get_url('more', 'index') ?>"><i class="icon-arrow-left"></i><?php echo clean(lang('getting started back to checklist')) ?></a>
<?php } ?>
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

<?php if (!(defined('REMOVE_HELP') && REMOVE_HELP)) { ?>
	<div class="more-panel-section">
		<h1><?php echo lang('quick help')?></h1>
		<div class="section-content section2">
		<?php
			tpl_assign('genid', $genid);
			$this->includeTemplate(get_template_path('section2', 'more'));
		?>
		</div>
	</div>
<?php } ?>	
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
$(function(){
	$(".more-panel-container").parent().css('backgroundColor', 'white');
<?php if ($gs_step >= 99) { ?>
	// the setup can be finished from another session while this page still has the old tab label
	if (og.GettingStarted) og.GettingStarted.showSettingsTab();
<?php } ?>
});
</script>