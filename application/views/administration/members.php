<?php
  set_page_title(lang('members'));
  
 if(Contact::canAddUser(logged_user())) {
    add_page_action(lang('add user'), owner_company()->getAddUserUrl(), 'ico-add',null,null,true);
  } // if
?>

<div class="adminUsersList" style="height:100%;background-color:white">
<div class="coInputHeader">
  <div>
	<div class="coInputName">
		<div class="coInputTitle">
		  	<?php echo lang('users') . (config_option('max_users')?(' (' . Contacts::count() .' / ' .  config_option('max_users') . ')'):'') ?>
		</div>
	</div>
	<div class="clear"></div>
  </div>
</div>
  <div class="adminMainBlock">
  <?php
  		foreach ($users_by_company as $company_row){
  			$company = $company_row['details'];
			$users = $company_row['users'];
			if (count($users) == 0) continue;
			tpl_assign('users', $users);
			tpl_assign('company', $company);
	?>
<div style='padding-bottom:20px;max-width:700px'>
<div style="padding:10px;padding-bottom:13px;background-color:#D7E5F5">
	<h1 style="font-size:140%;font-weight:bold"><a class="internalLink" href="<?php echo ($company instanceof Contact ? $company->getCardUrl() : "#") ?>"><?php echo ($company instanceof Contact ? clean($company->getObjectName()) : lang('without company')) ?></a></h1>
	<div style="float:right;" id="companypagination<?php echo ($company instanceof Contact ? $company->getId() : "0"); ?>"></div>
</div>
<div id="usersList" style="border:1px solid #DDD">

  <?php $this->includeTemplate(get_template_path('list_users', 'administration')); ?>
  </div></div>
  <?php } // foreach ?>
  
  </div>
</div>

<script type="text/javascript">
og.userListPagination=function(page,compid,cantpages,element) {
	divs = element.parentNode.childNodes;
	for (i=1;i<=cantpages;i++){
		d = divs[i-1];
		d.className="pagination-user";
		elem = document.getElementById(i + '-' + compid + 'userspage');
		if(elem){
			elem.style.display = "none";
		}
	}
	element.className = "pagination-user-active";
	page = document.getElementById(page + '-' + compid + 'userspage');
	if (page){
		page.style.display = "";
	}
};
</script>
 