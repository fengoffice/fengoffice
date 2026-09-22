<?php
ajx_current("users_and_groups");
set_page_title(lang('Role permissions'));


$roles = PermissionGroups::instance()->findAll(array(
	'conditions' => "type = 'roles'",
	'order' => '`parent_id`, `id` ASC'
));


$roles_by_parent = array();
foreach ($roles as $role) {
	$parent_id = $role->getParentId() ?: 0;
	if (!isset($roles_by_parent[$parent_id])) {
		$roles_by_parent[$parent_id] = array();
	}
	$roles_by_parent[$parent_id][] = $role;
}
?>

<div class="adminConfiguration">

	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle">
				<?php echo lang('Manage roles') ?>
			</div>
		</div>
	</div>

	<div class="coInputMainBlock adminMainBlock">

		<?php

		$group_index = 0;
		foreach (array_var($roles_by_parent, 0, array()) as $parent_role):

			$row_class = ($group_index % 2 === 0) ? '' : 'odd';
			$group_index++;
		?>

			<div class="optionsSectionTitle <?php echo $row_class; ?>">

				<h1>
					<?php echo lang($parent_role->getName()) ?>
				</h1>

				<?php if (isset($roles_by_parent[$parent_role->getId()])): ?>
					<div class="configCategoryDescription roleChildren">

						<?php foreach ($roles_by_parent[$parent_role->getId()] as $child_role):

							$role_url = get_url(
								'account',
								'role_permissions_edit',
								array('role_id' => $child_role->getId())
							);
						?>

							<div class="roleChildItem">
								<a class="internalLink" href="<?php echo $role_url ?>">
									<?php echo lang($child_role->getName()) ?>
								</a>
							</div>

						<?php endforeach; ?>

					</div>
				<?php endif; ?>

			</div>

		<?php endforeach; ?>

	</div>
</div>
