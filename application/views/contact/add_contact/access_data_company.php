<?php
	$permission_groups = array(); 
	$groups = PermissionGroups::getNonPersonalSameLevelPermissionsGroups('id');
	foreach($groups as $group){
    	$permission_groups[] = array($group->getId(), lang($group->getName()));
    }
    $genid = gen_id();
    $jqid = "#$genid";
?>
<script>
	$(function(){
		$("<?php echo $jqid ?>.access-data input.checkbox").click(function(){
			if ($(this).is(":checked")) {
				$("<?php echo $jqid ?>.access-data .user-data").slideDown();
			} else {
				$("<?php echo $jqid ?>.access-data .user-data").slideUp();
			}
		});
	});
</script>


<div id = "<?php echo $genid ?>" class="access-data">    
	<div class="field role">
		<label><?php echo lang("company")?></label>
		<div><?php echo select_box('contact[user][company_id]', array(), array('id' => $genid.'company-combo', 'style' => 'max-width:300px;margin-left:0;'))?></div>
		<span class="widget-body loading" id="<?php echo $genid?>company-combo-loading" style="heigth:20px;background-color:transparent;border:0px none;display:none;"></span>
		<div><?php //echo select_company('contact[user][company_id]',owner_company()->getId(), array('style' => 'max-width:300px;margin-left:0;'))?></div>
	</div>
	<div class="clear"></div>
	<div class="field role" style="vertical-align:middle;">
		<div style="height:5px;"></div>
		<label class="checkbox" for="<?php echo $genid?>contact[user][create-user]"><?php echo lang("will this person use feng office?") ?></label>
		<input style="float: left;" class="checkbox" type="checkbox" name="contact[user][create-user]" checked id="<?php echo $genid?>contact[user][create-user]"></input>
	</div>
	<div class="clear"></div>
	<div class="user-data" style="margin-bottom: 10px;">
		<div class="field role">
			<label><?php echo lang("user type")?></label>
			<div><?php echo simple_select_box('contact[user][type]', $permission_groups, 4, array('style' => 'max-width:300px;margin-left:0;'))?></div>
		</div>
	</div>
</div>

<script>
$( "#add-person-form-show" ).one( "click", function() {
	og.load_company_combo("<?php echo $genid?>company-combo", '<?php echo owner_company()->getId();?>');
	});
</script>