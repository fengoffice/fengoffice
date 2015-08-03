<?php add_page_action(lang('edit group'), $group->getEditUrl(), 'ico-edit')?>

<div class="adminGroups" style="height:100%;background-color:white">
  <div class="coInputHeader">
  	<div class="coInputTitle"><?php echo clean($group->getName()) ?></div>
  </div>
  <div class="adminMainBlock">
<?php
  $this->assign('users', $group_users);
  $this->includeTemplate(get_template_path('list_users', 'administration'));
?>
</div>
</div>

