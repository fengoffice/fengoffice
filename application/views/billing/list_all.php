<?php 
  set_page_title(lang('billing categories'));
  $isBillingEnabled = can_manage_billing(logged_user()) && isset($billing_categories) && is_array($billing_categories) && count($billing_categories);
?>
<div class="adminBilling" style="height:100%;background-color:white">
  <div class="coInputHeader">

	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle">
			<?php echo lang('billing categories') ?>
		</div>
	  </div>
	
	</div>
	
	<div class="coInputMainBlock adminMainBlock">

<?php if($isBillingEnabled) { 
	echo lang('billing support is enabled');
	?>
<br/>
<table style="min-width:400px;margin-top:10px;border:1px solid #DDD">
  <tr>
    <th><?php echo lang('name') ?></th>
    <th><?php echo lang('hourly rates') ?></th>
    <th style="width:300px"><?php echo lang('description') ?></th>
    <th></th>
  </tr>
<?php 
	$isAlt = true;
foreach($billing_categories as $billing) { 
	$isAlt = !$isAlt; ?>
  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
    <td style="padding:5px;padding-left:10px;padding-right:10px;font-weight:bold">
    	<a class="internalLink" href="javascript:og.render_modal_form('', {c:'billing', a:'edit', params: {id:<?php echo $billing->getId() ?>}});" title="<?php echo lang('edit') ?>"><?php echo clean($billing->getName()) ?></a>
    </td>
    <td style="text-align: center;padding:5px;padding-left:10px;padding-right:10px;"><?php echo config_option('currency_code', '$') ?>&nbsp;<?php echo clean($billing->getDefaultValue()) ?></td>
    <td style="padding:5px;padding-left:10px;padding-right:10px;"><?php echo clean($billing->getDescription()) ?></td>
<?php 
  $options = array(); 
  if($billing->canDelete(logged_user())) {
  	$options[] = '<a class="internalLink coViewAction ico-delete" href="' . $billing->getDeleteUrl() . '" onclick="return confirm(\'' . escape_single_quotes(lang('confirm delete billing category')) . '\')">' . lang('delete') . '</a>';
  }
?>
    <td style="padding:5px;padding-left:10px;padding-right:10px;font-size:80%;"><?php echo implode(' | ', $options) ?></td>
  </tr>
  <tr class="<?php echo $isAlt? 'altRow' : ''?>">
    <td style="padding:5px;padding-left:10px;padding-right:10px;" colspan=4>
    <span class="bold"><?php echo lang('users') ?>:</span>&nbsp;&nbsp;&nbsp;
    <?php $billing_users = $billing->getCategoryUsers();
    	if ($billing_users && count($billing_users) > 0){ 
    		foreach($billing_users as $b_user) { ?>
    			<a href="<?php echo $b_user->getCardUrl()?>" class="internalLink coViewAction ico-user"><?php echo clean($b_user->getObjectName()) ?></a>&nbsp;
    		<?php } ?>
    	<?php } else echo lang('none'); ?>
	</td>
  </tr>
<?php } // foreach ?>
</table>
<?php } else {
	echo lang('no billing categories') . '<br/>';
	echo lang('no billing categories desc') . '<br/>';
} // if ?>
<div style="margin-top:10px">
	<a class="internalLink coViewAction ico-add" href="javascript:og.render_modal_form('', {c:'billing', a:'add'});"><?php echo lang('add billing category') ?></a>
</div>
<?php if($isBillingEnabled) { ?>
<div style="margin-top:10px">
	<a class="internalLink coViewAction ico-user" href="<?php echo get_url('billing', 'assign_users') ?>"><?php echo lang('assign billing categories to users') ?></a>
</div>

<div style="margin-top:10px">
	<a class="internalLink coViewAction ico-recurrent" onclick="return confirm('<?php echo escape_single_quotes(lang('update unset billing values desc'))?>')" href="<?php echo get_url('billing', 'update_unset_billing_values') ?>"><?php echo lang('update unset billing values') ?></a>
</div>

<?php } ?>
</div>
</div>