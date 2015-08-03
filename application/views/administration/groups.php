<?php 
  set_page_title(lang('groups'));
  if(can_manage_security(logged_user())) {
    add_page_action(lang('add group'), get_url('group', 'add'), 'ico-add');
  } // if
  
  $genid = gen_id();
?>
<div class="coInputHeader">

  <div class="coInputHeaderUpperRow">
	<div class="coInputTitle">
		<?php echo lang('groups') ?>
	</div>
  </div>

</div>

<div class="coInputMainBlock adminMainBlock">
<?php if(isset($permission_groups) && is_array($permission_groups) && count($permission_groups)) { ?>
<table style="min-width:400px;">
  <tr>
    <th><?php echo lang('name') ?></th>
    <th style="text-align: center"><?php echo lang('users') ?></th>
    <th style="text-align: center"><?php echo lang('options') ?></th>
  </tr>
<?php
	$isAlt = true;
	foreach($permission_groups as $group) { 
		$isAlt = !$isAlt;
?>
	  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
	    <td><a class="internalLink" href="<?php echo $group->getViewUrl()?>"><?php echo clean($group->getName()) ?></a></td>
	    <td style="text-align: center"><?php echo array_var($gr_lengths, $group->getId()) ?></td>
	<?php 
		$options = array(); 
		if(can_manage_security(logged_user())) {
			$options[] = '<a class="internalLink" href="' . $group->getEditUrl() . '">' . lang('edit') . '</a>';
			$options[] = '<a class="internalLink" href="' . $group->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete group')) . '\')">' . lang('delete') . '</a>';
		}
	?>
	    <td style="text-align: center;"><?php echo implode(' | ', $options) ?></td>
	  </tr>
<?php } ?>
</table>
<?php } else { ?>
<?php echo lang('no groups in company') ?>
<?php } // if ?>
</div>
</div>
