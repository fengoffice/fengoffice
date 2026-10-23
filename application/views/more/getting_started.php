<?php
/**
 * Getting started checklist: content of the "Getting started" tab while the initial setup is
 * not finished. The steps and their state come from the getting_started helper.
 */
Env::useHelper('getting_started');

$steps = getting_started_steps(logged_user());
$progress = getting_started_progress($steps);
// a skipped step is not a done one, but it does not hold the setup either
$all_done = $progress['total'] > 0 && $progress['done'] + $progress['skipped'] == $progress['total'];
$progress_text = $progress['skipped'] > 0
	? lang('getting started progress skipped', $progress['done'], $progress['total'], $progress['skipped'])
	: lang('getting started progress', $progress['done'], $progress['total']);
$can_finish = getting_started_can_finish(logged_user());
$number = 0;
$next_found = false;
?>
<div class="gs-page">
<?php if (count($steps) > 0) { ?>
	<section class="gs-checklist" id="<?php echo $genid ?>gs">
		<header class="gs-header">
			<h1 class="gs-title"><?php echo clean(lang('getting started with', product_name())) ?></h1>
			<p class="gs-subtitle"><?php echo clean(lang(getting_started_subtitle_key($all_done, $can_finish, $progress['skipped'] > 0))) ?></p>
			<div class="gs-progress-row">
				<progress class="gs-progress" value="<?php echo $progress['done'] + $progress['skipped'] ?>" max="<?php echo $progress['total'] ?>"
					aria-label="<?php echo clean($progress_text) ?>"></progress>
				<span class="gs-progress-text"><?php echo clean($progress_text) ?></span>
			<?php if ($can_finish && $all_done) { ?>
				<button type="button" class="gs-btn gs-btn-primary gs-finish"><?php echo clean(lang('getting started finish')) ?></button>
			<?php } else if ($can_finish) { ?>
				<button type="button" class="gs-btn gs-btn-ghost gs-skip"><?php echo clean(lang('getting started skip setup')) ?></button>
			<?php } ?>
			</div>
		<?php if ($can_finish && !$all_done) { ?>
			<div class="gs-skip-confirm" hidden>
				<span class="gs-skip-confirm-text"><?php echo clean(lang('getting started skip confirm')) ?></span>
				<button type="button" class="gs-btn gs-btn-secondary gs-skip-confirm-yes"><?php echo clean(lang('getting started skip setup')) ?></button>
				<button type="button" class="gs-btn gs-btn-ghost gs-skip-cancel"><?php echo clean(lang('cancel')) ?></button>
			</div>
		<?php } ?>
		</header>

		<ol class="gs-steps">
		<?php foreach ($steps as $step) {
			$is_next = !$step['done'] && !$step['skipped'] && !$step['optional'] && !$next_found;
			if ($is_next) $next_found = true;
			if (!$step['optional']) $number++;
			// a pending required step can be skipped by whoever can finish the setup
			$can_skip = $can_finish && !$step['done'] && !$step['skipped'] && !$step['optional'];

			$row_class = 'gs-step' . ($step['done'] ? ' is-done' : '') . ($step['skipped'] ? ' is-skipped' : '') . ($is_next ? ' is-next' : '') . ($step['optional'] ? ' is-optional' : '');
			$button_class = $step['done'] ? 'gs-btn-ghost' : ($is_next ? 'gs-btn-primary' : 'gs-btn-secondary');
			$button_label = $step['done'] ? lang('getting started review') : $step['action_label'];
			$action = $step['action'];
		?>
			<li class="<?php echo $row_class ?>" data-step="<?php echo clean($step['id']) ?>">
				<span class="gs-step-status" aria-hidden="true"><?php
					if ($step['done']) {
						echo '<i class="icon-check"></i>';
					} else if ($step['skipped']) {
						echo '<i class="icon-minus"></i>';
					} else if ($step['optional']) {
						echo '<i class="icon-plus"></i>';
					} else {
						echo $number;
					}
				?></span>
				<div class="gs-step-text">
					<span class="gs-step-title"><?php echo clean($step['title']) ?><?php
						if ($step['optional']) { ?> <span class="gs-badge"><?php echo clean(lang('getting started optional')) ?></span><?php }
						if ($step['skipped']) { ?> <span class="gs-badge"><?php echo clean(lang('getting started skipped')) ?></span><?php }
						if ($step['done']) { ?> <span class="gs-sr-only"><?php echo clean(lang('getting started completed')) ?></span><?php }
					?></span>
				<?php if (!$step['done']) { ?>
					<span class="gs-step-desc"><?php echo clean($step['description']) ?></span>
				<?php } ?>
				</div>
				<button type="button" class="gs-btn <?php echo $button_class ?> gs-step-action"
					data-action-type="<?php echo clean($action['type']) ?>"
				<?php if ($action['type'] == 'modal') { ?>
					data-controller="<?php echo clean($action['controller']) ?>"
					data-action="<?php echo clean($action['action']) ?>"
					data-params="<?php echo clean(json_encode((object) $action['params'])) ?>"
				<?php } else { ?>
					data-url="<?php echo clean($action['url']) ?>"
				<?php } ?>
				><?php echo clean($button_label) ?></button>
			<?php if ($can_finish) { // the slot is on every row, so the action buttons end on the same line ?>
				<span class="gs-step-skip-slot"><?php if ($can_skip) {
					?><button type="button" class="gs-step-skip" data-step="<?php echo clean($step['id']) ?>" title="<?php echo clean(lang('getting started skip step')) ?>" aria-label="<?php echo clean(lang('getting started skip step')) ?>"><i class="icon-skip-forward" aria-hidden="true"></i></button><?php
				} ?></span>
			<?php } ?>
			</li>
		<?php } ?>
		</ol>
	</section>
<?php } else { ?>
	<?php // no steps for this user: the page still says what it is, above the links ?>
	<h1 class="gs-title"><?php echo clean(lang('getting started with', product_name())) ?></h1>
<?php } ?>

	<nav class="gs-footer">
	<?php if (can_manage_configuration(logged_user())) { ?>
		<a class="internalLink gs-all-settings" href="<?php echo get_url('more', 'index', array('more_settings_expanded' => 1)) ?>"><?php echo clean(lang('getting started all settings')) ?></a>
	<?php } ?>
	<?php
		// the help and personal links of the settings page, restyled as plain links by getting_started.css
		tpl_assign('genid', $genid);
		if (!(defined('REMOVE_HELP') && REMOVE_HELP)) {
			$this->includeTemplate(get_template_path('section2', 'more'));
		}
		$this->includeTemplate(get_template_path('section3', 'more'));
	?>
	</nav>
</div>
<script>
og.GettingStarted.init('<?php echo $genid ?>');
</script>
