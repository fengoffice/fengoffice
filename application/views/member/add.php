<?php
	require_javascript("og/DateField.js");
	require_javascript("og/modules/addMemberForm.js");
	set_page_title(lang('members'));
	$genid = gen_id();
	if (!isset($parent_sel)) $parent_sel = 0;
	if (!isset($obj_type_sel)) $obj_type_sel = 0;
	if (!isset($member)) $member = null;
	$member_color = 0;
	if ($member instanceof Member && !$member->isNew()) {
		$memberId = $member->getId();
		$member_color = $member->getColor();
	} else if ($parent_sel > 0) {
		$p = Members::getMemberById($parent_sel);
		if ($p instanceof Member) $member_color = $p->getColor();
	}
	
	$object_type_selected = $obj_type_sel > 0 ? ObjectTypes::findById($obj_type_sel) : null;
	if ($member instanceof Member) {
	    $object_type_name = $member->getTypeNameToShow();
	} else {
		$object_type_name = $object_type_selected instanceof ObjectType ? lang($object_type_selected->getName()) : null;
	}
	
	$object_type_name = ucwords($object_type_name);
	
	if ($member instanceof Member && $member->isNew()) {
		$member->setObjectTypeId($obj_type_sel);
	}
	if($member instanceof Member && !$member->isNew()) {
		$ot = ObjectTypes::findById($member->getObjectTypeId());
		$ot_name = lang($ot->getName());
		if ($member->getArchivedById() == 0) {
			add_page_action(lang('archive'), "javascript:if(confirm('".lang('confirm archive member',$ot_name)."')) og.openLink('".get_url('member', 'archive', array('id' => $member->getId()))."');", 'ico-archive-obj');
		} else {
			add_page_action(lang('unarchive'), "javascript:if(confirm('".lang('confirm unarchive member',$ot_name)."')) og.openLink('".get_url('member', 'unarchive', array('id' => $member->getId()))."');", 'ico-unarchive-obj');
		}
		$delete_url = get_url('member', 'delete', array('id' => $member->getId(),'start' => true));
		add_page_action(lang('delete'), "javascript:og.deleteMember('".$delete_url."','".$ot_name."');", 'ico-delete');
	}
	$form_title = $object_type_name ? ($member->isNew() ? lang('new') : lang('edit')) . strtolower(" $object_type_name") : lang('new member');
	$new_member_text = $object_type_name ? ($member->isNew() ? lang('add') : lang('edit')) . strtolower(" $object_type_name") : lang('new member');

    $main_properties = array();
    Hook::fire('render_member_properties', array('member' => $member, 'visible_by_default' => true), $main_properties);

    $more_properties = array();
    Hook::fire('render_member_properties', array('member' => $member, 'visible_by_default' => false), $more_properties);

    $categories = array();
	Hook::fire('member_edit_categories', array('member' => $member, 'genid' => $genid), $categories);
	
	$member_ot = ObjectTypes::findById($member->getObjectTypeId());
	
	$dim_obj = null;
	if ($member_ot->getType() == 'dimension_object') {
		if (!$member->isNew()) {
			$dim_obj = Objects::findObject($member->getObjectId());
		} else {
			if (class_exists($member_ot->getHandlerClass())) {
				eval('$ot_manager = '.$member_ot->getHandlerClass().'::instance();');
				if (isset($ot_manager) && $ot_manager instanceof ContentDataObjects) {
					eval('$dim_obj = new '.$ot_manager->getItemClass().'();');
					if ($dim_obj instanceof ContentDataObject) {
						$dim_obj->setNew(true);
						$dim_obj->setObjectTypeId($member->getObjectTypeId()); 
					}
				}
			} 
		}
	}
	
	$form_action = $member == null || $member->isNew() ? get_url('member', 'add') : get_url('member', 'edit', array("id" => $member->getId()));
	
	// on submit functions
	$on_submit = "";
	if ( $current_dimension instanceof Dimension && $current_dimension->getDefinesPermissions()) {
		$on_submit = "if (og.userPermissions) og.userPermissions.ogPermPrepareSendData('$genid', ".($member->isNew() ? 'true' : 'false').");";
	}
	if (array_var($_REQUEST, 'modal')) {
		$on_submit .= " og.submit_modal_form('".$genid."submit-edit-form'); return false;";
	} else {
		$on_submit .= " return true;";
	}
?>
<style>

</style>
<form 
	id="<?php echo $genid ?>submit-edit-form" 
	class="edit-member" 
	method="post" enctype="multipart/form-data"  
	action="<?php echo $form_action ?>"
	onsubmit="<?php echo $on_submit ?>"
>
	<input type="hidden" name="member[dimension_id]" value="<?php echo $current_dimension->getId()?>"/>
	
	<input type="hidden" name="temp_member_id" id="<?php echo $genid?>member_id" value="<?php echo ($member instanceof Member && !$member->isNew() ? $member->getId() : 0)?>"/>

	<div class="coInputHeader">
	
	<?php if ($member instanceof Member && !$member->isNew()) {
			$delete_url = get_url('member', 'delete', array('id' => $member->getId(),'start' => true));
			
			if ($member->getArchivedById() == 0) {
				$arch_action = "if(confirm('".lang('confirm archive member',$ot_name)."')) og.openLink('".get_url('member', 'archive', array('id' => $member->getId()))."', {callback:function(){\$('#_close_link').click();}});";
				$arch_icon = "ico-archive-obj";
			} else {
				$arch_action = "if(confirm('".lang('confirm unarchive member',$ot_name)."')) og.openLink('".get_url('member', 'unarchive', array('id' => $member->getId()))."', {callback:function(){\$('#_close_link').click();}});";
				$arch_icon = "ico-unarchive-obj";
			}
		?>
		<div class="headerToolbar">
			<div class="headerToolbarItem">
				<a onclick="<?php echo $arch_action ?>" 
					href="#" class="link-ico <?php echo $arch_icon ?>"><?php 
						echo lang("archive");
				?></a>
			</div>
			<div class="headerToolbarItem">
				<a onclick="og.deleteMember('<?php echo $delete_url ?>','<?php echo $object_type_name ?>');" 
					href="#" class="link-ico ico-delete"><?php 
						echo lang("delete");
				?></a>
			</div>
		</div>
		<div class="clear"></div>
	<?php } ?>
	
	  <div class="coInputHeaderUpperRow">
		<div class="coInputTitle"><?php echo $form_title ?></div>
	  </div>
	
	  <div>
		<div class="coInputName">
			<?php
			$member_name = array_var($member_data, 'name');
			Hook::fire("render_object_name_prefix", array('object' => $dim_obj), $member_name);
			?>
			<?php echo text_field('member[name]', $member_name, array('id' => $genid . '-name', 'class' => 'title', 'placeholder' => lang('type name here'))) ?>
		</div>
			
		<div class="coInputButtons">
			<?php echo submit_button($member == null || $member->isNew() ? $new_member_text : lang('save changes'),'s',array('style'=>'margin-top:0px;margin-left:10px')) ?>
		</div>
		<div class="clear"></div>
	  </div>
	</div>
	
	
	<div class="coInputMainBlock">
	
	  <div id="<?php echo $genid?>tabs" class="edit-form-tabs">
	
		<ul id="<?php echo $genid?>tab_titles">
		
			<li><a href="#<?php echo $genid?>member_data"><?php echo lang('details') ?></a></li>
			
			<?php if ($current_dimension->getDefinesPermissions() && can_manage_security(logged_user())) {?>
			<li><a href="#<?php echo $genid?>member_permissions_div" id="<?php echo $genid?>permissions_tab"><?php echo lang('permissions') ?></a></li>
			<?php } ?>
			
			<?php if (count($more_properties) > 0) { ?>
			<li id="<?php echo $genid?>add_custom_properties_li"><a href="#<?php echo $genid?>add_custom_properties_div"><?php echo lang('more properties') ?></a></li>
			<?php } ?>
			
			<?php foreach ($categories as $category) { ?>
			<li style="<?php echo array_var($category, 'style')?>" class="<?php echo array_var($category, 'class')?>">
				<a href="#<?php echo $category['id'] ?>" id="<?php echo array_var($category, 'link_id')?>"><?php echo $category['name'] ?></a>
			</li>
			<?php } ?>
		</ul>
		
		<div id="<?php echo $genid?>member_data" class="form-tab">
	
		<div <?php echo ($member == null || $member->isNew() ? "" : 'style="display:none;"')?> class="dataBlock" id="<?php echo $genid?>_member_type_container">
			<?php echo label_tag(lang('type'), "", true) ?>
			<input type="hidden" id="<?php echo $genid ?>memberObjectType" name="member[object_type_id]"></input>
			<div id="<?php echo $genid ?>object_type_combo_container"></div>
			<div class="clear"></div>
		</div>
		<?php
		
		$doths = DimensionObjectTypeHierarchies::findAll(array('conditions' => 'dimension_id='.$current_dimension->getId()." AND child_object_type_id=".$member->getObjectTypeId()));
		$can_have_parent = count($doths) > 0;
		
		//$can_have_parent = count(DimensionObjectTypeHierarchies::getAllParentObjectTypeIds($current_dimension->getId(), $member_ot->getId())) > 0;
		
		if($member instanceof Member && $can_have_parent){ ?>
		<div id="<?php echo $genid?>memberParentContainer" style="width:267px;">
			<?php  
				$selected_members = array();
				if ($parent_sel) {
					$selected_members[] = $parent_sel;
				}
				
				$render = true;
				Hook::fire('member_add_render_parent_selector', array('member' => $member), $render);
				
				if ($render) {
					render_single_member_selector($current_dimension, $genid, $selected_members, array('is_multiple' => false, 'label' => lang('located under'),
					'width'=>400, 'allow_non_manageable' => true, 'dont_filter_this_selector' => true,
					'select_function' => 'og.onParentMemberSelect', 'listeners' => array('on_remove_relation' => "og.onParentMemberRemove('".$genid."');")), false);
				}
			?>
				<input type="hidden" id="<?php echo $genid ?>memberParent" value="<?php echo $parent_sel; ?>" name="member[parent_member_id]"></input>
				<div class="clear"></div>
		</div>
		<?php } ?>
		
		<?php 
			$additional_member_data_fields = array();
			Hook::fire('additional_member_data_fields', array('member' => $member, 'genid' => $genid), $additional_member_data_fields);
			foreach ($additional_member_data_fields as $field) { ?>
				<div id="<?php echo array_var($field, 'container_id')?>" class="dataBlock">
					<label><?php echo array_var($field, 'label')?></label>
					<?php echo array_var($field, 'input_html')?>
				</div>
				<div class="x-clear"></div>
		<?php 
			}
		?>
		
		<?php 
			$render = true;
			Hook::fire('member_form_render_associated_dimension_selectors', array('member' => $member, 'is_new' => $member->isNew()), $render);
			if ($render) {
				render_associated_dimensions_selectors(array('member' => $member, 'is_new' => $member->isNew()));
			}
		?>
		<div class="x-clear"></div>

			<?php 
			if ($member_ot->getType() == 'dimension_object') {
				if ($member->isNew()) {
					eval('$ot_manager = '.$member_ot->getHandlerClass().'::instance();');
					if (isset($ot_manager) && $ot_manager instanceof ContentDataObjects && $ot_manager->getItemClass() != '') {
						eval('$ws_object = new '.$ot_manager->getItemClass().'();');
						$ws_object->setObjectTypeId($member_ot->getId());
					}
				} else {
					$ws_object = Objects::findObject($member->getObjectId());
				}
				$null = null; Hook::fire('before_render_main_custom_properties', array('object' => $ws_object), $null);
			}
			?>
			<div class="main-custom-properties-div">
            <?php
            if (count($main_properties) > 0) {
                foreach ($main_properties as $main_property){
                    echo $main_property['html'];
                }
            }
            ?>
            </div>
		
		<div class="x-clear"></div>
		
		<?php if (!Plugins::instance()->isActivePlugin('member_custom_properties')) { ?>
		
		<div id="<?php echo $genid?>member_color_input" class="dataBlock"></div>
		<div class="x-clear"></div>
		
		<div id="<?php echo $genid?>member_description_div" class="dataBlock">
			<label><?php echo lang('description')?></label>
			<textarea name="member[description]" class="long"><?php echo $member->getDescription()?></textarea>
		</div>
		<div class="x-clear"></div>
		
		<?php } ?>
		
		
		<?php if ($member_ot->getUsesOrder()) { ?>
		
		<div id="<?php echo $genid?>member_order_div" class="dataBlock">
			<label><?php echo lang('order')?></label>
			<input type="number" name="member[order]" value="<?php echo $member->getOrder()?>" />
		</div>
		<div class="x-clear"></div>
		
		<?php } ?>
		
		<div class="x-clear"></div>
		
		
		
		<div id="<?php echo $genid?>dimension_object_fields" style="display:none;"></div>
		
		<div style="margin-top:10px; display:none;" id="<?php echo $genid?>property_links">
			<span id="<?php echo $genid ?>addPropertiesLink"
				onclick="App.modules.addMemberForm.drawDimensionProperties('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);"
				class="db-ico ico-add bold" style="padding:3px 0 0 20px; cursor:pointer;"><?php echo lang('vinculations')?></span>
				
			<span id="<?php echo $genid ?>delPropertiesLink"
				onclick="App.modules.addMemberForm.deleteDimensionProperties('<?php echo $genid?>');"
				class="db-ico ico-delete bold" style="padding:3px 0 0 20px; cursor:pointer; display:none;"><?php echo lang('hide vinculations')?></span>
		</div>
		
		<div id="<?php echo $genid?>dimension_properties" style="width:750px;"></div>
		
		<div style="margin-top:10px; display:none;" id="<?php echo $genid?>restriction_links">
			<input type="hidden" id="<?php echo $genid?>ot_with_restrictions" value="" />
			<span id="<?php echo $genid ?>addRestrictionsLink"
				onclick="App.modules.addMemberForm.drawDimensionRestrictions('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);"
				class="db-ico ico-add bold" style="padding:3px 0 0 20px; cursor:pointer;"><?php echo lang('restrictions')?></span>
				
			<span id="<?php echo $genid ?>delRestrictionsLink"
				onclick="App.modules.addMemberForm.deleteDimensionRestrictions('<?php echo $genid?>');"
				class="db-ico ico-delete bold" style="padding:3px 0 0 20px; cursor:pointer; display:none;"><?php echo lang('hide restrictions')?></span>
		</div>
		
		<div id="<?php echo $genid?>dimension_restrictions" style="width:750px;"></div>
	<?php if (isset($rest_genid)) { ?>
		<input type="hidden" name="rest_genid" value="<?php echo $rest_genid?>" />
	<?php } ?>
	<?php if (isset($prop_genid)) { ?>
		<input type="hidden" name="prop_genid" value="<?php echo $prop_genid?>" />
	<?php } ?>
	
		
		</div>
		
	<?php 
		foreach ($categories as $category) {
			echo $category['content'];
		} 
	?>
		
		<div id="<?php echo $genid?>member_permissions_div" class="form-tab">
		<?php if ($current_dimension->getDefinesPermissions() && can_manage_security(logged_user())):?>
			<label><?php echo lang("users and groups with permissions here")?></label>
			<div class="clear"></div>
			<?php
				tpl_assign('genid', $genid); 
				$this->includeTemplate(get_template_path('member_permissions_control', 'member'));
			?>
		<?php endif ;?>
		</div>
		<div class="x-clear"></div>
		
		<?php if (count($more_properties) > 0) { ?>
		<div id="<?php echo $genid ?>add_custom_properties_div" class="form-tab"><?php
            if ($member instanceof Member) {
                foreach ($more_properties as $more_property){
                    echo $more_property['html'];
                }
            }
		?></div>
		<div class="x-clear"></div>
		<?php } ?>
		
		<?php $additional_hidden_fields = array();
			Hook::fire('additional_member_hidden_fields', $member, $additional_hidden_fields);
			foreach ($additional_hidden_fields as $name => $value) { ?>
				<input name="member[<?php echo $name?>]" id="<?php echo $genid.$name?>" value="<?php echo $value?>" type="hidden"/> 
		<?php } ?>
		
	</div>
	<?php 
	if (!array_var($_REQUEST, 'modal')) {
		echo submit_button($member == null || $member->isNew() ? $new_member_text : lang('save changes'),'s',array('style'=>'margin-top:0px;'));
	}
	?>
</form>

<script>
	og.member_is_new = <?php echo ($member->isNew() ? "1" : "0")?>;
	
	og.prev_parent = null;
	var genid = '<?php echo $genid?>';
	
	og.dimRestrictions.ot_with_restrictions = Ext.util.JSON.decode('<?php echo json_encode($ot_with_restrictions)?>');
	og.dimProperties.ot_with_properties = Ext.util.JSON.decode('<?php echo json_encode($ot_with_associations)?>');

	$(function() {
		$("#<?php echo $genid?>tabs").tabs();

		Ext.get('<?php echo $genid ?>-name').focus();
		
		og.eventManager.fireEvent("after member add render",{
			genid: genid,
			dimensionCode: '<?php echo $current_dimension->getCode()?>'
		});

		App.modules.addMemberForm.drawObjectTypesSelectBox(genid, Ext.util.JSON.decode('<?php echo json_encode($dimension_obj_types)?>'), 'object_type_combo_container', 'memberObjectType', '<?php echo (isset($obj_type_sel) ? $obj_type_sel : 0) ?>', '<?php echo (isset($can_change_type) && $can_change_type ? '0' : '1')?>');
		App.modules.addMemberForm.objectTypeChanged('<?php echo $obj_type_sel ?>', genid, true);

		<?php if (false && count($selected_members) > 0) { ?>
		App.modules.addMemberForm.drawDimensionProperties('<?php echo $genid;?>', <?php echo $current_dimension->getId();?>);
		<?php } ?>

		<?php if (!Plugins::instance()->isActivePlugin('member_custom_properties')) { ?>
		document.getElementById(genid + 'member_color_input').innerHTML = og.getColorInputHtml(genid, 'member', <?php echo "$member_color"?>, 'color', '<?php echo lang('color')?>');
		<?php } ?>
		
		<?php if (isset($obj_type_sel) && $obj_type_sel) {?>
			$("#<?php echo $genid?>_member_type_container").hide();
		<?php }	?>
	});
</script>
