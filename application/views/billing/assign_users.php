<form style="height:100%;background-color:white" class="internalForm" action="<?php echo get_url('billing', 'assign_users') ?>" method="post">
<div class="adminBilling" style="height:100%;background-color:white">
  <div class="adminHeader">
  	<div class="adminTitle"><table style="width:535px"><tr><td>
  			<?php echo lang('assign billing categories to users') ?>
  		</td><td style="text-align:right">
  			<?php echo submit_button(lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px')); ?>
  		</td></tr></table>
  	</div>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
<?php
	$genid = gen_id();
	
	if ($users_by_company){?>		
		<?php
		foreach ($users_by_company as $company_row){
			$company = $company_row['details'];
			$users = $company_row['users'];
		?>
<div style='padding-bottom:20px;max-width:700px'>
<div style="padding:10px;background-color:#D7E5F5"><h1 style="font-size:140%;font-weight:bold"><a class="internalLink" href="<?php echo ($company instanceof Contact ? $company->getCardUrl() : "#")?>"><?php echo ($company instanceof Contact ? clean($company->getName()) : lang('without company')) ?></a></h1></div>
<div id="usersList" style="border:1px solid #DDD">
<?php $counter = 0; 
  foreach($users as $user) {
	$counter++; ?>
  <div class="listedUser <?php echo $counter % 2 ? 'even' : 'odd' ?>">
    <div class="userAvatar"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> <?php echo lang('avatar') ?>" /></div>
    <div class="userDetails">
      <div class="userName"><a class="internalLink" href="<?php echo $user->getCardUrl() ?>"><?php echo clean($user->getObjectName()) ?></a></div>
      <div class="userIsAdmin">
	<?php 
		$options = array(option_tag(lang('select billing category'),0,($user->getDefaultBillingId() == 0?array('selected' => 'selected'):null)));
		foreach ($billing_categories as $category){
			$options[] = option_tag($category->getName(),$category->getId(),($category->getId()==$user->getDefaultBillingId())?array('selected' => 'selected'):null);	
		}?>
		<table><tr>
			<td><?php echo label_tag(lang('billing category'), null, false);?></td>
			<td style="padding-left:10px"><?php echo select_box('users[' . $user->getId() . ']',$options,array('id' => 'userDefaultBilling'))?></td>
		</tr></table>
	  </div>
      <div class="clear"></div>
    </div>
  </div>  
<?php } // foreach ?>
</div>
</div>

<?php 	} // foreach
	echo submit_button(lang('save changes'), 's', array('style'=>'margin-top:0px;margin-left:10px'));?>
<?php } // if ?>
</div>
</div>
</form>