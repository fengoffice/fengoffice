<?php 
	$genid = gen_id();
	set_page_title(lang('templates'));
	add_page_action(lang('new template'), get_url('template', 'add'), 'ico-add');	
?>
<form action="<?php echo get_url('template', 'add_to') ?>" method="post">
	<input type="hidden" value="<?php echo $object->getId()?>" name="id"/>
            	
	<div class="adminClients" style="height:100%;background-color:white">
		<div class="adminHeader">
			<div class="adminTitle"><?php echo lang('templates') ?></div>
		</div>
		<div class="adminSeparator"></div>
		<div class="adminMainBlock" style="min-height: 400px;">
			<div>
				<div class="addToTemplateDesc"><?php echo lang("you are adding object to template", lang($object->getObjectTypeName()), '<span class="og-ico ico-'.$object->getObjectTypeName().'">'.clean($object->getObjectName()).'</span>') ?></div>
			
				<div style="margin-top: 10px;">
					<label class="checkbox"><?php echo ($object instanceof ProjectTask)? lang('copy task subtasks'):lang('copy milestone tasks')?></label>
					<input id="copy-tasks" class="checkbox" type="checkbox" name="add_to_temp[copy-tasks]" checked="checked">
				</div>
				
								
				<?php if(isset($templates) && is_array($templates) && count($templates)) { ?>
					<label><?php echo lang('template') ?></label>
					<select id="<?php echo $genid?>milestoneListToAdd" class="select_milestone" name="add_to_temp[template]" >
						<?php 
						foreach($templates as $template) { 
						?>
							<option value="<?php echo $template->getId() ?>">
								<?php echo clean($template->getObjectName()) ?>
							</option>
						<?php } // foreach ?>
					</select>
				<?php } else { ?>
					<?php echo lang('no templates') ?><br>
				<?php } // if ?>
				
				<a href="<?php echo get_url("template", "add", array(
										'id' => $object->getId(),
										'manager' => get_class($object->manager())
				)) ?>" class="internalLink ico-add bg-ico"><?php echo lang("new template") ?></a>
			</div>
			<button id="<?php echo $genid?>submit" class="submit" type="submit">
				<?php echo lang('add') ?>
			</button>
		</div>		
	</div>
</form> 