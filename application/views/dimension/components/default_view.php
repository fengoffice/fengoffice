<div class="dimension-selector-container <?php  if ($horizontal) echo "dimension-selector-container-horizontal";?>">
<?php if (!isset($hide_label) || !$hide_label) : /*margin-right: 10px; min-width: 0px;*/?>
	<label style="font-size: 100%; <?php  if (!$horizontal) echo "float:left;";?>"><?php echo (isset($label) && $label != '' ? $label : $dimension_name) ?>:</label>
<?php endif;

$container_width = 385;
if (array_var($options, 'width')) {
	$container_width = array_var($options, 'width');
	$opts['width'] = array_var($options, 'width');
}
?>
<div style="display: inline-block;float:right;width: <?php echo $container_width ?>px;">
<?php
// tree with members where user has permissions
$dim = Dimensions::getDimensionById($dimension_id);
$opts = array('checkBoxes'=>false,'all_members' => false,'use_ajax_member_tree' => true, 'search_placeholder' => $search_placeholder, 'select_function' => $select_function);

if(array_var($options, 'allowedMemberTypes', false)){
	$opts["allowedMemberTypes"]= array_var($options, 'allowedMemberTypes');
}

$is_multiple = false;
if(array_var($options, 'is_multiple', true)){
	$is_multiple = array_var($options, 'is_multiple', true);
}

$opts["is_multiple"]= $is_multiple;
if (trim(array_var($options, 'root_lang')) != "") {
	$opts["root_lang"] = array_var($options, 'root_lang');
}

$add_selected_classes = "";
if (isset($default_selection_checkboxes)) {
	$add_selected_classes = "with-checkbox";
} else {
	$default_selection_checkboxes = false;
}

?>
		
	<div id="<?php echo $genid; ?>selected-members-dim<?php echo $dimension_id?>" class="selected-members <?php echo $add_selected_classes?>">
				<?php
				$dimension_has_selection = false; 
				if (count($dimension_selected_members) > 0) : 
					$alt_cls = "";
					foreach ($dimension_selected_members as $selected_member) :
						$allowed_members = array_keys($members_dimension);
						if (count($allowed_members) > 0 && !in_array($selected_member->getId(), $allowed_members)) continue;
						$dimension_has_selection = true;
						
						$complete_path_add_style = "";
						$checked_str = "";
						$actions_div_width = 20;
						if ($default_selection_checkboxes && $related_member_id > 0) {
							$actions_div_width = 40;
							$row = DB::executeOne("SELECT count(*) as cant FROM ".TABLE_PREFIX."dimension_member_association_default_selections
								WHERE member_id='$related_member_id' AND selected_member_id='".$selected_member->getId()."'");
							if ($row['cant'] > 0) {
								$checked_str = 'checked="checked"';
							}
							$complete_path_add_style = "width:calc(100% - 40px);";
						}
						
						
						// if is a member_property_member of another selected member and the association doesn't have the config "allow_remove_from_property_member" => dont let remove
						$can_remove_classification = true;
						$mpm = MemberPropertyMembers::findOne(array('conditions' => 'property_member_id = '.$selected_member->getId() . 
								" AND member_id IN (".implode(',',$selected_member_ids).")"));
						if ($mpm instanceof MemberPropertyMember) {
							$can_remove_classification = DimensionAssociationsConfigs::getConfigValue($mpm->getAssociationId(), 'allow_remove_from_property_member');
						}
						
				?>
						<div class="selected-member-div <?php echo $alt_cls?>" id="<?php echo $genid?>selected-member<?php echo $selected_member->getId()?>">
							<div class="completePath" style="<?php echo $complete_path_add_style?>"></div>
							<?php if ($is_multiple) : ?>
							<div class="selected-member-actions" <?php echo $is_ie ? 'style="display:inline;margin-left:'.$actions_div_width.'px;float:none;"' : 'style="width:'.$actions_div_width.'px;"'?>>
								<?php if ($default_selection_checkboxes) { ?>
								<input type="checkbox" class="checkbox" name="member[default_selection][<?php echo $selected_member->getId()?>]" <?php echo $checked_str ?> title="<?php echo lang('select by default')?>"/>
								<?php } ?>
								
								<?php if ($can_remove_classification) { ?>
								<a href="#" class="coViewAction ico-delete" title="<?php echo lang('remove relation')?>" onclick="member_selector.remove_relation(<?php echo $dimension_id?>,'<?php echo $genid?>', <?php echo $selected_member->getId()?>)"></a>
								<?php } ?>
							</div>	
							<div class="clear"></div>
							<?php endif;?>	
						</div>	
				<?php	$alt_cls = $alt_cls == "" ? "alt-row" : "";
						$sel_mem_ids[] = $selected_member->getId();
					endforeach; 
				?>
					<div class="separator"></div>
				
				<?php endif;?>
	</div>	
	
	<?php 
	echo render_single_dimension_tree ( $dim, $genid, null, $opts);
	?>
</div>
</div>
	
<script>
$(function() {
	$("#<?php echo $genid; ?>-member-chooser-panel-<?php echo $dimension_id?>-tree").css('width', '<?php echo $container_width?>px');
	$("#<?php echo $genid; ?>-member-chooser-panel-<?php echo $dimension_id?>-tree .x-panel-body.collapsible-body").css('width', '<?php echo $container_width - 2?>px');
});

$("#<?php echo $genid; ?>selected-members-dim<?php echo $dimension_id?>").appendTo("#<?php echo $genid?>-member-chooser-panel-<?php echo $dimension_id?>-tree-current-selected");


 <?php 
			//add bredcrumb foreach selected member
			if (count($dimension_selected_members) > 0){
				foreach ($dimension_selected_members as $selected_member){
					?> 
					var member_id = <?php echo $selected_member->getId()?>;
					var dimension_id = <?php echo $selected_member->getDimensionId()?>;
					var tmp_member = {};
					tmp_member[member_id] = member_id;
					var tmp_dim = {};
					tmp_dim[dimension_id] = tmp_member;
					mem_path = og.getEmptyCrumbHtml(tmp_dim,".completePath",null,false);

								
					$("#<?php echo $genid?>selected-member<?php echo $selected_member->getId()?> .completePath").append(mem_path);
										
	    <?php 
				}			
			} else { 
		?>$("#<?php echo $genid?>-member-chooser-panel-<?php echo $dimension_id?>-tree-current-selected .empty-text").show();<?php
			}
	    ?> 
</script>

