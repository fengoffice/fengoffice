<?php
 
$cantUsers = count($users);
$cantPages = floor($cantUsers / 10);
$page = 1;
$newPage = true;
isset($isMemberList) && $isMemberList == true ? $isUsersList = true : $isUsersList = false;
if(isset($users) && is_array($users) && $cantUsers) { ?>
<div id="usersList">
<?php $counter = 0; 
  foreach($users as $user) { /* @var $user Contact */
  	
	$counter++; ?>
<?php if ($newPage && $isUsersList ) {
		$newPage = false;
		?>
<div id="<?php echo $page . '-' . $user->getCompanyId()?>userspage" style="display: <?php echo $counter != 1? 'none':'block' ?>" >		
	<?php }//newpage??>
  <div class="listedUser <?php echo $counter % 2 ? 'even' : 'odd' ?> <?php echo $user->getDisabled() ? 'user-disabled' : '' ?>">
	<div class="userAvatar"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?> <?php echo lang('avatar') ?>" /></div>
	<div class="userDetails">
		<div class="userName">
			<a class="internalLink" href="<?php echo $user->getCardUrl() ?>"><?php echo clean($user->getObjectName()) ?></a>
			<span><?php echo $user->getDisabled() ? "(".lang('disabled').")" : '' ?></span>
		</div> 
      
		<div class="userIsAdmin"><span><?php echo $user->getUserTypeName() ?></span></div>
<?php 

  $options = array();
  if (!$user->getDisabled()) {
	  if($user->canUpdateProfile(logged_user())) {
	    $options[] = '<a class="internalLink" href="' . $user->getEditUrl() . '">' . lang('update profile') . '</a>';
	    $options[] = '<a class="internalLink" href="' . $user->getUpdatePictureUrl() . '">' . lang('update avatar') . '</a>';
	  }
	  if ($user->canChangePassword(logged_user())) {
	  	$options[] = '<a class="internalLink" href="' . $user->getEditPasswordUrl() . '">' . lang('change password') . '</a>';
	  }
	  if($user->canUpdatePermissions(logged_user())) {
	    $options[] = '<a class="internalLink" href="' . $user->getUpdatePermissionsUrl() . '">' . lang('permissions') . '</a>';
	  }
	  /*
	  if($user->canDelete(logged_user())) {
	  	if (!$user->hasReferences()) {
	  		$options[] = '<a class="internalLink" href="' . get_url('account', 'delete_user', array('id' => $user->getId())) . '" onclick="return confirm(\''.escape_single_quotes(lang('confirm delete user')) .'\');">' . lang('delete') . '</a>';
	  	}
  		$options[] = '<a class="internalLink" href="' . $user->getDisableUrl() . '" onclick="return confirm(\''.escape_single_quotes(lang('confirm disable user')) .'\');">' . lang('disable') . '</a>';
	  }
  } else {
	  if($user->canDelete(logged_user())) {
	  	$options[] = '<a class="internalLink" href="' . get_url('account', 'restore_user', array('id' => $user->getId())) . '">' . lang('activate') . '</a>';
	  }*/
  }
?>
		<div class="userOptions"><?php echo implode(' | ', $options) ?></div>
		<div class="clear"></div>
	</div>
  </div>
<?php if (($counter % 10 == 0 || ($cantPages > 0 && $counter == $cantUsers)) && $isUsersList ){ ?> 
</div>
<?php 
	  	$newPage = true;
	  	$page ++;
	}
} ?>
</div>

<?php } else { ?>
<p><?php echo lang('no users in company') ; ?></p>
<?php } 
 		$temp = new Contact();
 		$companyUrl =  isset($company) && $company instanceof Contact ? $company->getAddUserUrl() : $temp->getAddUserUrl() ;
		echo  "<div style='padding:10px'><a href='$companyUrl' class='internalLink coViewAction ico-add'>" . lang('add user') . "</a></div>";
		if ($cantPages > 0): ?>

<script type="text/javascript">
		og.paginate = function (cantPages,compId){
			var html ="";
			var op = "";
			html += '<div style="height:15px;">';
			for (i=1;i<=cantPages + 1 ;i++){
				op = (i == 1 ? "-active" : "");
				html += '<div class="pagination-user'+ op + '">';
				html += "<a id='userpaginationnumberlink" + compId + i + "' style='font-size:10px;' class='internalLink' href='#' onclick='og.userListPagination(" + i + "," + compId + "," + (cantPages + 1) + ",this.parentNode)' >" + i + "</a>";
				html += '</div>';
			}
			html += '</div>';
			paginateDiv = document.getElementById("companypagination"+compId);
			if (paginateDiv){
				paginateDiv.innerHTML += html;
			}
		};
		og.paginate(<?php echo $cantPages; ?>,<?php  echo (isset($company) && $company instanceof Contact)?$company->getId():'0'?>);
 </script>
 
		<?php endif; ?>