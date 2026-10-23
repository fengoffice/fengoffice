<?php
	set_page_title(lang('templates'));
//	add_page_action(lang('new template'), get_url('template', 'add'), 'ico-add');
	$genid = gen_id();

?>
<div class="adminClients template-admin-page">
	<div class="coInputHeader">

	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('task templates') ?>
		</div>
	  </div>
	
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
	  <div class="template-list-container">
		<p class="template-manage-categories-link"><a class="internalLink ico-edit bg-ico" href="<?php echo get_url('template', 'manage_task_template_categories') ?>"><?php echo lang('manage task template categories') ?></a></p>
		<?php
			$grouped = (isset($grouped_task_templates) && is_array($grouped_task_templates))
				? $grouped_task_templates
				: array();
			if (!count($grouped) && isset($templates) && is_array($templates)) {
				$grouped = array(lang('task templates uncategorized') => $templates);
			}
			$has_template_groups = false;
			foreach ($grouped as $group_label => $bucket) {
				if (count($bucket) || $group_label !== lang('task templates uncategorized')) {
					$has_template_groups = true;
					break;
				}
			}
		?>
		<?php if ($has_template_groups) : ?>
		<table id="<?php echo $genid ?>-ws" class="templates-table template-templates-table">
			<tr>
				<th><?php echo lang('template') ?></th>
				<th><?php echo lang('actions') ?></th>
			</tr>
			<?php
			foreach ($grouped as $group_label => $group_templates) :
			?>
			<tr class="template-category-row">
				<td colspan="2"><?php echo clean($group_label) ?></td>
			</tr>
			<?php
				$isAlt = true;
				foreach ($group_templates as $cotemplate) :
				$isAlt = !$isAlt;
				$options = array();
				if ($cotemplate->canEdit(logged_user())) {
					$options[] = '<a class="internalLink link-ico ico-edit" href="' . $cotemplate->getEditUrl() .'&popup=true" title="'.lang('edit').'">&nbsp;</a>';
				}
				if (can_manage_templates(logged_user())) {
					$options[] = '<a class="internalLink link-ico ico-copy" href="' . get_url('template','copy_task_template',array('template_id'=>$cotemplate->getId())) .'" title="'.lang('copy').'">&nbsp;</a>';
				}
				if($cotemplate->canDelete(logged_user())) {
					$options[] = '<a class="internalLink link-ico ico-delete" href="' . $cotemplate->getDeleteUrl() .'&popup=true" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete template')) . '\')" title="'.lang('delete template').'">&nbsp;</a>';
				}
			?>
			<tr class="<?php echo $isAlt? 'altRow' : ''?>">
				<td><a class="internalLink ico-template bg-ico"
					href="<?php echo $cotemplate->getEditUrl() ?>"><?php echo clean($cotemplate->getObjectName()) ?></a></td>
				<td class="template-actions-cell"><?php echo implode(' ', $options) ?></td>
			</tr>
			<?php
				endforeach;
			endforeach;
			?>
		</table>
		<?php else : ?>
		<?php echo lang('no templates') ?><br/>
		<?php endif; // if ?> <br/>
		<a 	href="<?php echo get_url("template", "add") ?>" class="internalLink ico-add bg-ico"><?php echo lang("new task template") ?></a>
		
	  </div>
	</div>

	<?php $null = null;
		Hook::fire("render_more_type_templates", null, $null);
	?>
		
</div>
