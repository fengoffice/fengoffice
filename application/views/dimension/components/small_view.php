<div class="dimension-selector-container">
	<?php if (!isset($hide_label) || !$hide_label) : ?>
	<label style="font-size: 100%;"><?php echo (isset($label) && $label != '' ? $label : $dimension_name) ?>:</label>
	<?php endif; ?>
	<div class="input-container">
		<?php	
		include get_template_path("search_selector_view", "search_selector");			
		?>		
	</div>
	<div id="<?php echo $genid; ?>selected-members-dim<?php echo $dimension_id?>" class="selected-members">
			<?php
			$dimension_has_selection = false; 
			if (count($dimension_selected_members) > 0) : 
				$alt_cls = "";
				foreach ($dimension_selected_members as $selected_member) :
					$allowed_members = array_keys($members_dimension);
					if (count($allowed_members) > 0 && !in_array($selected_member->getId(), $allowed_members)) continue;
					$dimension_has_selection = true;
			?>
					<div class="selected-member-div <?php echo $alt_cls?>" id="<?php echo $genid?>selected-member<?php echo $selected_member->getId()?>">
						<div class="completePath"></div>
						<div class="selected-member-actions" <?php echo $is_ie ? 'style="display:inline;margin-left:40px;float:none;"' : ''?>>
							<a href="#" class="coViewAction ico-delete" title="<?php echo lang('remove relation')?>" onclick="member_selector.remove_relation(<?php echo $dimension_id?>,'<?php echo $genid?>', <?php echo $selected_member->getId()?>)"><?php echo lang("remove")?></a>
						</div>	
					</div>	
			<?php	$alt_cls = $alt_cls == "" ? "alt-row" : "";
					$sel_mem_ids[] = $selected_member->getId();
				endforeach; 
			?>
				<div class="separator"></div>
			
			<?php endif;?>
	</div>	
</div>

<script> 
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
		}
    ?>    
</script>