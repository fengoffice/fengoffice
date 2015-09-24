
<table class="printTasksListContainer">

<?php foreach ($tasks_list_cols as $col) { ?>
  <col style="width: <?php echo array_var($col, 'col_width') ?>">
<?php } ?>
<thead id="ogTasksPanelColNamesThead">
  <tr id="ogTasksPanelColNames" class="task-list-col-names-template texture-n-1">
  	<?php foreach ($tasks_list_cols as $col) {
    		if (in_array($col['id'], array('task_quick_actions','task_btn_actions'))) {
				continue;
			} 
    ?>
      <th class="<?php echo array_var($col, 'id') ?>" style="width: <?php echo array_var($col, 'col_width') ?>"><?php echo array_var($col, 'title')?></th>
    <?php } ?>   
  </tr>
</thead>


<?php foreach ($groups as $group) { ?>

<tbody id="ogTasksPanelPrintGroup<?php echo array_var($group, 'group_id'); ?>">
  <tr>
    <td colspan=<?php echo count($tasks_list_cols)?> class="ogTasksGroupHeader task-list-row-template">
      <div class='task-single-div'>
        <div class='db-ico <?php echo array_var($group, 'group_icon'); ?>'></div>
      </div>

      <div class='ogTasksGroupHeaderName task-single-div'><?php echo array_var($group, 'group_name'); ?></div>
      
      <?php foreach (array_var($group, 'group_tasks', array()) as $task) { ?>
      
        <tr id="ogTasksPanelPrintTask<?php echo array_var($task, 'id'); ?>G<?php echo array_var($group, 'group_id'); ?>" class="task-list-row-template task-list-row">
		  <td>
		  <div style="width: 53px;float:right">
		      <div class="priority <?php echo "priority-" . array_var($task, 'priority'); ?>"></div>
		  </div>
		  </td>
		  
		  <?php if (array_var($draw_options, 'show_by')) { ?>
		  <td>
		    <div class='task-row-avatar'>
		        <span><?php 
		        if (array_var($task, 'assignedById')) {
					$assignedBy = Contacts::findById(array_var($task, 'assignedById'));
					if ($assignedBy instanceof Contact) {
		        		echo $assignedBy->getObjectName();
		        	}
		        }
 				?></span>
		    </div>
		  </td>
		  <?php } ?>
		  
		  <td>
		    <div class='task-row-avatar'>
		        <span class="assigned-to-name"><?php 
		        if (array_var($task, 'assignedToContactId')) {
		        	echo array_var($task, 'atName');
		        }
 				?></span>
		    </div>
		  </td>
		
		  <td class="task_name">
		    
		        <div class='task-name'>
		        <?php $level = 10 * array_var($task, 'depth', 0); ?>
		          <?php if (array_var($task, 'status')) { ?>
		            <span style='text-decoration:line-through;margin-left: <?php echo $level?>px;'><?php echo array_var($task, 'name')?></span>
		          <?php } else { ?>
		            <span style='margin-left: <?php echo $level?>px;'><?php echo array_var($task, 'name')?></span>            
		          <?php } ?>
		          
		          <?php if (array_var($task, 'repetitive')) { ?>
		            <span style='margin-left: <?php echo $level?>px;' class="ico-recurrent"></span>  
		          <?php } ?>
		        </div>          
		      
		  </td>
		  
		  <?php if (array_var($draw_options, 'show_classification')) {
		  	
			$member_path = json_decode(str_replace("'", '"', $task['memPath']), true);
			$separate_dimensions = array_var($draw_options, 'show_dimension_cols');
			$mem_path_html = "";
			
			foreach ($member_path as $dim_id => $mem_ids) {
				if (in_array($dim_id, $separate_dimensions)) continue;
		  		$sep = '<span class="print-breadcrumb">-</span>';
				foreach ($mem_ids as $mem_id) {
					$mem = Members::getMemberById($mem_id);
					if ($mem instanceof Member) {
						$this_mem_path = $mem->getPathToPrint($sep, '<span class="print-breadcrumb">', '</span>');
						$this_mem_path .= ($this_mem_path=="" ? "" : $sep) . '<span class="print-breadcrumb wide">'. $mem->getName() .'</span>';
						$dim_mem_path_html .= '<span class="member-path real-breadcrumb og-wsname-color-'.$mem->getColor().'">'.$this_mem_path.'</span>';
					}
				}
			}
		  	?>
		  <td>
		    <div class='task-breadcrumb-container'>
		        <?php echo $mem_path_html; ?>
		    </div>
		  </td>
		  <?php } ?>
		
		  <?php foreach (array_var($draw_options, 'show_dimension_cols') as $dim_col) {
		  			if ($dim_col == 0) continue;
		  			$dim_member_path = json_decode(str_replace("'", '"', $task['memPath']), true);
		  			$dim_mem_path_html = "";
		  			
		  			$mem_ids = $dim_member_path[$dim_col];
		  			if (is_array($mem_ids) && count($mem_ids) > 0) {
		  				$sep = '<span class="print-breadcrumb">-</span>';
						foreach ($mem_ids as $mem_id) {
							$mem = Members::getMemberById($mem_id);
							if ($mem instanceof Member) {
								$this_mem_path = $mem->getPathToPrint($sep, '<span class="print-breadcrumb">', '</span>');
								$this_mem_path .= ($this_mem_path=="" ? "" : $sep) .'<span class="print-breadcrumb wide">'. $mem->getName() .'</span>';
								$dim_mem_path_html .= '<span class="member-path real-breadcrumb og-wsname-color-'.$mem->getColor().'">'.$this_mem_path.'</span>';
							}
						}
					}
		  ?>
		  <td class='task_name'>
		    <div class='task-breadcrumb-container'>      
		        <?php echo $dim_mem_path_html ?>
		    </div>
		  </td>
		  <?php } ?>
		
		  
		  <?php if (array_var($draw_options, 'show_percent_completed_bar')) { ?>
		  <td class="task-percent-completed-bar-container">
		    <?php echo build_percent_completed_bar_html($task);  ?>
		  </td>
		  <?php } ?> 
		
		  
		  <?php if (array_var($draw_options, 'show_start_dates')) { ?>
		  <td class="task-date-container">
		    <?php if (array_var($task, 'startDate')) {
		    	$date = new DateTimeValue(array_var($task, 'startDate')); 
		    ?>
		    <span class="nobr" style='font-size: 9px;color: #888;'><?php echo format_datetime($date, null, 0)?></span>  
		    <?php } ?> 
		  </td>
		  <?php } ?>
		
		  
		  <?php if (array_var($draw_options, 'show_end_dates')) { ?>
		  <td class="task-date-container">
		    <?php if (array_var($task, 'dueDate')) {
		    	$date = new DateTimeValue(array_var($task, 'dueDate'));
		    	$due_date_late = $date->getTimestamp() < DateTimeValueLib::now()->getTimestamp();
		    ?>
		     <?php if ($due_date_late) { ?>
		     <span class="nobr" style='font-size: 9px;font-weight:bold;color: #F00;'><?php echo format_datetime($date, null, 0);?></span>  
		     <?php } else { ?>
		     <span class="nobr" style='font-size: 9px;color: #888;'><?php echo format_datetime($date, null, 0)?></span>
		     <?php } ?>
		    <?php } ?>
		  </td>
		  <?php } ?>
		  
		  <?php foreach ($row_total_cols as $row_total_col) { ?>
		  			
		  <td class="task-date-container">
		  	<?php $color = "#888";
		  	if(array_var($row_total_col, 'row_field') == 'worked_time_string' && array_var($task, 'pending_time') < array_var($task, 'worked_time')) {
		  		$color = '#f00';
		  	}
		  	?>
		    <span class="nobr" style='font-size: 9px;color: <?php echo $color?>;'>
		      <?php
		      	$task_date_columm = (array_var($row_total_col, 'row_field')=='estimatedTime' ? 'timeEstimateString' : array_var($row_total_col, 'row_field'));
		      	echo array_var($task, $task_date_columm);
		      ?>
		    </span>
		  </td>
		  
		  <?php }?>
		  
		  <?php if (array_var($draw_options, 'show_previous_pending_tasks')) { ?>
		  <td>  
		    <?php if (array_var($task, 'previous_tasks_total')) { ?>
		    <span class="ctmBadge previous-pending"><?php echo array_var($task, 'previous_tasks_total') ?></span>
		    <?php } ?> 
		  </td>   
		  <?php } ?>
		
		</tr>
      <?php } // foreach task ?>
    </td>
  </tr>
</tbody>

<?php /*
<tbody id="ogTasksPanelGroup{{group.group_id}}Totals">
  <tr id="" class="task-list-row-template task-list-group-totals-template">
    {{#each  total_cols}}
      <td class="{{{this.id}}}">{{{this.text}}}</td>          
    {{/each }}   
  </tr>
</tbody>
*/?>
<?php } // foreach group ?>



</table>