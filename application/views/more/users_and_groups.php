<?php
	ajx_current("users_and_groups");
	set_page_title(lang('users and groups'));
	$genid = gen_id();
?>

<div class="adminConfiguration">

	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('users and groups') ?>
			</div>
		</div>
	</div>

	<div class="coInputMainBlock adminMainBlock">

		<!-- USERS -->
		<div class="configCategory" id="category_users">
			<h2>
				<a class="internalLink" href="<?php echo get_url('more', 'users') ?>">
					<?php echo lang('Manage users') ?>
				</a>
			</h2>
			<div class="configCategoryDescription">
				<?php echo lang('full access users desc') ?><br />
				<?php echo lang('collaborators desc', '<br />') ?><br />
				<?php echo lang('guests desc', '<br />') ?>
			</div>
		</div>

		<!-- GROUPS -->
		<div class="configCategory odd" id="category_groups">
			<h2>
				<a class="internalLink" href="<?php echo get_url('more', 'groups') ?>">
					<?php echo lang('Manage groups') ?>
				</a>
			</h2>
			<div class="configCategoryDescription">
				<?php echo lang('groups desc', '<br />') ?>
			</div>
		</div>

		<!-- ROLES -->
		<div class="configCategory" id="category_roles">
			<h2>
				<a class="internalLink" href="<?php echo get_url('more', 'roles') ?>">
					<?php echo lang('Manage roles') ?>
				</a>
			</h2>
			<div class="configCategoryDescription">
				<?php echo lang('permissions defined per role') ?>
			</div>
		</div>

	</div>
</div>
