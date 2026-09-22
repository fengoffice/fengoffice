<?php
	ajx_current("groups");
	set_page_title(lang('groups'));

	$groups = PermissionGroups::getNonRolePermissionGroups();
	$gr_lengths = array();

	foreach ($groups as $gr) {
		$count = ContactPermissionGroups::instance()->count("`permission_group_id` = ".$gr->getId());
		$gr_lengths[$gr->getId()] = $count;
	}
?>

<div class="user-groups-container" style="height:auto;">

	<div class="user-groups-section" style="margin-top:30px; border-top:0px;">
		<h1><?php echo lang('Manage groups') ?></h1>
		<div class="clear"></div>

		<div class="section-description desc">
			<?php echo lang('groups desc', '<br />') ?>
		</div>

		<div class="clear"></div>

		<div class="section-content section1">
			<ul>
			<?php if (count($groups)): ?>
				<?php foreach ($groups as $group): ?>
					<li class="user">
						<a href="<?php echo $group->getEditUrl() ?>"
						   class="internalLink"
						   target="more-panel">
							<div class="wrapper">
								<div class="coViewIconImage ico-large-group"></div>
								<div class="user-name-container">
									<?php echo $group->getName() ?>
									<div class="desc">
										<?php echo $gr_lengths[$group->getId()] . ' ' . lang('users') ?>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			<?php else: ?>
				<li class="no-user-message"><?php echo lang('no groups in company') ?></li>
			<?php endif; ?>
			</ul>

			<div class="clear"></div>

			<button class="add-first-btn blue"
			        onclick="og.openLink(og.getUrl('group','add'));">

				<img src="public/assets/themes/default/images/16x16/add.png">
				&nbsp;<?php echo lang('add new group') ?>
			</button>

			<div class="clear"></div>
		</div>
	</div>
</div>
