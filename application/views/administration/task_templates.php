<?php 

  // Set page title and set crumbs to index
  set_page_title(lang('task templates'));

?>


<div class="adminClients">
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('task templates') ?>
	</div>
  </div>

</div>

<div class="coInputMainBlock adminMainBlock">

<?php if(isset($task_templates) && is_array($task_templates) && count($task_templates)) { ?>
<table style="min-width:400px;">
  <tr>
    <th><?php echo lang('template') ?></th>
    <th><?php echo lang('options') ?></th>
  </tr>
<?php 
	$isAlt = true;
foreach($task_templates as $task_template) { 
	$isAlt = !$isAlt; ?>
  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
    <td><a class="internalLink" href="<?php echo $task_template->getViewUrl() ?>"><?php echo clean($task_template->getTitle()) ?></a></td>
<?php 
  $options = array(); 
  if($task_template->canDelete(logged_user())) {
  	$options[] = '<a class="internalLink" href="' . $task_template->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete task template')) . '\')">' . lang('delete template') . '</a>';
  }
?>
    <td><?php echo implode(' | ', $options) ?></td>
  </tr>
<?php } // foreach ?>
</table>
<?php } else { ?>
<?php echo lang('no task templates') ?>
<?php } // if ?>
</div>
</div>