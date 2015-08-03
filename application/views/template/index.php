<?php
	set_page_title(lang('templates'));
//	add_page_action(lang('new template'), get_url('template', 'add'), 'ico-add');
	$genid = gen_id();

?>
<div class="adminClients" style="height: 100%; background-color: white">
	<div class="coInputHeader">

	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('task templates') ?>
		</div>
	  </div>
	
	</div>
	
	<div class="coInputMainBlock adminMainBlock">
	  <div class="template-list-container">
		<?php if(isset($templates) && is_array($templates) && count($templates)) : ?>
		<table style="min-width: 500px;" id="<?php echo $genid ?>-ws" class="templates-table">
			<tr>
				<th><?php echo lang('template') ?></th>
				<th><?php echo lang('actions') ?></th>
			</tr>
			<?php
			$isAlt = true;
			foreach($templates as $cotemplate) :
				$isAlt = !$isAlt; 	$options = array();
				if ($cotemplate->canEdit(logged_user())) {
					$options[] = '<a class="internalLink link-ico ico-edit" href="' . $cotemplate->getEditUrl() .'&popup=true" title="'.lang('edit').'">&nbsp;</a>';
				}
				if($cotemplate->canDelete(logged_user())) {
					$options[] = '<a class="internalLink link-ico ico-delete" href="' . $cotemplate->getDeleteUrl() .'&popup=true" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete template')) . '\')" title="'.lang('delete template').'">&nbsp;</a>';
				}
			?>
			<tr class="<?php echo $isAlt? 'altRow' : ''?>">
				<td><a class="internalLink ico-template bg-ico"
					href="<?php echo $cotemplate->getEditUrl() ?>"><?php echo clean($cotemplate->getObjectName()) ?></a></td>
				
				<td style="width:65px;"><?php echo implode(' ', $options) ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php else:?> 
		<?php echo lang('no templates') ?><br/>
		<?php endif; // if ?> <br/>
		<a 	href="<?php echo get_url("template", "add") ?>" class="internalLink ico-add bg-ico"><?php echo lang("new task template") ?></a>
		
	  </div>
	</div>

	<?php $null = null;
		Hook::fire("render_more_type_templates", null, $null);
	?>
		
</div>
