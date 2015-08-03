
<form class='internalForm'  method="post" action="<?php echo get_url('template','assign_to_ws', array('id' => $cotemplate->getId()))?>">
<div class="adminClients" style="height:100%;background-color:white">
	<div class="adminHeader">
  		<div class="adminTitle"><?php echo lang('assign template to workspace', clean($cotemplate->getName())) ?></div>
	</div>
	<div class="adminSeparator"></div>
	<div class="adminMainBlock">
<?php 

	echo select_workspaces("ws_ids", $workspaces, $selected, gen_id());
	echo submit_button(lang('save'), 's', array('tabindex' => '10')); 
?>
	</div>
</div>
   
</form>