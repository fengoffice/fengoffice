<?php
	set_page_title(lang('manage task template categories'));
	$genid = gen_id();
?>
<div class="adminClients template-admin-page">
	<div class="coInputHeader">
		<div class="coInputHeaderUpperRow">
			<div class="coInputTitle"><?php echo lang('manage task template categories') ?></div>
		</div>
	</div>

	<div class="coInputMainBlock adminMainBlock">
		<form method="post" action="<?php echo get_url('template', 'manage_task_template_categories') ?>" class="template-category-form">
			<input type="hidden" name="add_category_submitted" value="1" />
			<fieldset>
				<legend><?php echo lang('new task template category') ?></legend>
				<div class="dataBlock">
					<?php echo label_tag(lang('name'), $genid . 'category_name', true) ?>
					<?php echo text_field('category_name', '', array('id' => $genid . 'category_name', 'class' => 'long')) ?>
				</div>
				<div class="dataBlock">
					<?php echo label_tag(lang('task template category sort order'), $genid . 'sort_order') ?>
					<?php echo text_field('sort_order', '0', array('id' => $genid . 'sort_order', 'class' => 'template-category-sort-order')) ?>
				</div>
				<?php echo submit_button(lang('add'), 's') ?>
			</fieldset>
		</form>

		<?php if (isset($task_template_categories) && count($task_template_categories)) : ?>
		<form method="post" action="<?php echo get_url('template', 'manage_task_template_categories') ?>">
			<input type="hidden" name="update_categories_submitted" value="1" />
			<table class="templates-table template-categories-table">
				<tr>
					<th><?php echo lang('name') ?></th>
					<th><?php echo lang('task template category sort order') ?></th>
					<th><?php echo lang('actions') ?></th>
				</tr>
				<?php $is_alt = true;
				foreach ($task_template_categories as $cat) :
					$is_alt = !$is_alt;
					$cat_id = $cat->getId();
					$del_url = get_url('template', 'manage_task_template_categories', array('delete_category' => $cat_id));
					?>
				<tr class="<?php echo $is_alt ? 'altRow' : '' ?>">
					<td>
						<?php echo text_field("categories[$cat_id][name]", $cat->getName(), array('class' => 'long')) ?>
					</td>
					<td>
						<?php echo text_field("categories[$cat_id][sort_order]", (int) $cat->getSortOrder(), array('class' => 'template-category-sort-order')) ?>
					</td>
					<td class="template-actions-cell">
						<a class="internalLink link-ico ico-delete" href="<?php echo $del_url ?>"
							onclick="return confirm('<?php echo escape_single_quotes(lang('confirm delete task template category')) ?>')"
							title="<?php echo lang('delete') ?>">&nbsp;</a>
					</td>
				</tr>
				<?php endforeach ?>
			</table>
			<div class="template-category-actions">
				<?php echo submit_button(lang('save changes'), 's') ?>
			</div>
		</form>
		<?php else : ?>
		<p><?php echo lang('no task template categories') ?></p>
		<?php endif ?>
	</div>
</div>
