<?php /* @var Project $project */
	$genid = gen_id();
	$projects_dim = Dimensions::findByCode('customer_project');
	$project_dim_id = $projects_dim ? $projects_dim->getId() : 0;
	$active_members = active_context();
	$co_widget_dimensions = ContactConfigOptions::getByName('widget_dimensions');
	
?>



<div class="late-objects-widget widget">

	<div style="overflow: hidden;" class="widget-header" onclick="og.dashExpand('<?php echo $genid?>');">
		<div class="widget-title"><?php echo (isset($widget_title)) ? $widget_title : lang('late tasks and upcoming tasks'); ?></div>
		<div class="dash-expander ico-dash-expanded" id="<?php echo $genid; ?>expander"></div>
	</div>
	
	<div class="widget-body widget-overdue-upcoming-tasks__body" id="<?php echo $genid; ?>_widget_body">
	<?php if (isset($overdue_upcoming_objects) && count($overdue_upcoming_objects)) : ?>
		<table id="dashTableMS" class="widget-table" style="width:100%; margin-bottom: 10px;">

		<?php
			$today = DateTimeValueLib::now()->beginningOfDay();
			$tomorrow = DateTimeValueLib::now()->beginningOfDay()->add('d', 1);
			$c = 0;
			$row_cls = "";
			
			foreach($overdue_upcoming_objects as $object):
				$c++;
				if ($object->getCompletedById() > 0) {
					$days_str = lang('completed');
					$cls = 'completed';
				} else {
					if ($object->getDueDate()){
						$tz_offset = Timezones::getTimezoneOffsetToApply($object);
						$tz = $tz_offset/3600;

                        if($object instanceof ProjectTask && $object->getUseDueTime()) {
                            $object->getDueDate()->advance($tz, true);
                        }
						if ($object->getDueDate()->getTimestamp() < $today->getTimestamp()) {
							$days_str = lang('days late', $object->getLateInDays());
							$cls = 'late';
						} else if ($object->getDueDate()->getTimestamp() >= $today->getTimestamp() && $object->getDueDate()->getTimestamp() < $tomorrow->getTimestamp()) {
							$days_str = lang('today');
							$cls = 'today';
						} else {
							$days_str = lang('days left', $object->getLeftInDays());
							$cls = 'future';
						}
					}
				}?>
		    <tr class="<?php echo $row_cls . ($c > 55? 'noDispLM':''); ?>" style="<?php echo $c > 55? 'display:none':'' ?>">
			    
			    <td class="date-col nobr">
			    	<div class="<?php echo isset($cls)?$cls:'';?>-row">
			    		<?php echo isset($days_str)?($days_str):lang("no due date"); ?>
			    	</div>
			    </td>
			    
			    <td class="db-ico">
			    	<div class="db-ico <?php echo $object->getIconClass()?>"></div>
			    </td>
			    
			    <td style="padding-left:5px;padding-bottom:2px;overflow:hidden;text-overflow:ellipsis;max-width:10px;vertical-align: middle;">
			    	<div class="nobr" style="margin-bottom: 12px;">
			    		<?php
							if ($co_widget_dimensions instanceof ContactConfigOption){
								$widget_dimensions = array_filter(explode(',', user_config_option('widget_dimensions')));
								$active_member_ids = active_context_members(false);
								$member_ids_params = array('exclude_member_ids' => $active_member_ids, 'allowed_dimensions' => $widget_dimensions, 'use_restrictions' => true);
								$crumbOptions = json_encode($object->getMembersIdsToDisplayPath(false,$member_ids_params));
								if($crumbOptions == ""){
									$crumbOptions = "{}";
								}
								$crumbJs = " og.getEmptyCrumbHtml($crumbOptions, '.nobr' ) ";
							} 
			    		?>
			    		<a class="internalLink" href="#" onclick="og.openLink('<?php echo $object->getViewUrl() ?>');" title="<?php echo clean($object->getObjectName()) ?>">
							<?php echo clean($object->getObjectName()) ?>
						</a>
						
						<?php if ($co_widget_dimensions instanceof ContactConfigOption){ ?>
							<div class="clear"></div>
							<span id="object_crumb_<?php echo $object->getId()?>"></span>
							<script>
								var crumbHtml = <?php echo $crumbJs?>;
								$("#object_crumb_<?php echo $object->getId()?>").html(crumbHtml);
							</script>
						<?php } ?>
						
					</div>
				</td>
				
			</tr>
			
		<?php endforeach; ?>
		</table>
		
		<div class="view-all-container widget-overdue-upcoming-tasks__view-all-container">
			<a href="#" onmousedown="og.openLink(og.getUrl('task', 'new_list_tasks'), {caller:'tasks-panel'});" onclick="Ext.getCmp('tabs-panel').activate('tasks-panel');">
				<?php if ($show_more) echo lang('view all');?>
			</a>
		</div>
		
		
	<?php else:?>
		<div class="empty">
			<?php //echo lang("no data to show") ?>
		</div>
	<?php endif; ?>
	
	<?php if ($render_add) : ?>
		<?php if (isset ($overdue_upcoming_objects) && count($overdue_upcoming_objects) > 0) : ?>
		<div class="separator"></div>
		<?php endif; ?>
		<div class="new-task">


			<div style="padding-top:12px;">
				<button class="add-first-btn" type="" onclick="ogTasks.drawAddNewTaskFromData('new_task_<?php echo $genid?>', 'overdue upcoming widget - add task')"><?php echo lang('add task')?></button>
			</div>
			<div class="x-clear"></div>
			
		</div>
	<?php endif;?>
	</div>
</div>