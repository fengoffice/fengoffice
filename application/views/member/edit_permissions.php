<?php
	if (!isset($genid)) $genid = gen_id();
	set_page_title(lang('update permissions'));
	
	$member = array_var($permission_parameters, 'member');
?>

<form style="height:100%;background-color:white" action="<?php echo get_url("member", "edit_permissions", array("id" => $member->getId())) ?>" class="internalForm" onsubmit="javascript:og.userPermissions.ogPermPrepareSendData('<?php echo $genid ?>');return true;" method="POST">
<div class="adminClients">
  <div class="adminHeader">
  	<div class="adminTitle"><?php echo lang("permissions for member", clean($member->getName())) ?></div>
  	<span style="margin-left:30px;"><?php echo submit_button(lang('update permissions')); ?></span>
  </div>
  <div class="adminSeparator"></div>
  <div class="adminMainBlock">
  	<div class="desc" style="margin-bottom:10px;"><?php echo lang('user selector permissions help')?></div>
	<input name="submitted" type="hidden" value="submitted" />
<?php 

tpl_assign('genid', $genid);
$this->includeTemplate(get_template_path('member_permissions_control', 'member'));

echo submit_button(lang('update permissions')); 

?>
</div>
</div>
</form>