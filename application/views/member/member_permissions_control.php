<?php
	require_javascript("og/Permissions.js");
	if (!isset($genid)) $genid = gen_id();
	set_page_title(lang('update permissions'));
	
	$member = array_var($permission_parameters, 'member');
	$allowed_object_types = array_var($permission_parameters, 'allowed_object_types');
	$allowed_object_types_json = array_var($permission_parameters, 'allowed_object_types_json');
	$permission_groups = array_var($permission_parameters, 'permission_groups');
	$member_permissions = array_var($permission_parameters, 'member_permissions');
	
	$pg_condition = " AND EXISTS (SELECT pg.id FROM ".TABLE_PREFIX."permission_groups pg WHERE pg.type<>'roles' AND pg.id=cmp.permission_group_id)";
	$with_perm_pg_ids = array();
	if ($member instanceof Member) {
		$with_perm_pg_ids = DB::executeAll("SELECT DISTINCT(cmp.permission_group_id) FROM ".TABLE_PREFIX."contact_member_permissions cmp where cmp.member_id=".$member->getId()." $pg_condition AND object_type_id IN (".implode(',', $allowed_object_types_json).")");
	} else {
		if (isset($parent_sel) && $parent_sel > 0) {
			$with_perm_pg_ids = DB::executeAll("SELECT DISTINCT(cmp.permission_group_id) FROM ".TABLE_PREFIX."contact_member_permissions cmp where cmp.member_id=".$parent_sel." $pg_condition AND object_type_id IN (".implode(',', $allowed_object_types_json).")");
		} else {
			$with_perm_pg_ids = DB::executeAll("SELECT c.permission_group_id FROM ".TABLE_PREFIX."contacts c where c.user_type IN (SELECT id FROM ".TABLE_PREFIX."permission_groups WHERE type='roles' AND name IN ('Executive','Manager','Administrator','Super Administrator'));");
		}
	}
	if (count($with_perm_pg_ids)) $with_perm_pg_ids = array_flat($with_perm_pg_ids);
	else $with_perm_pg_ids = array(0);
	
	if (count($with_perm_pg_ids) > 0) {
		$with_perm_pgs = PermissionGroups::instance()->FindAll(array('conditions' => 'id IN ('.implode(',', $with_perm_pg_ids).')'));
	}
	$users_with_perms = array();
	$groups_with_perms = array();
	foreach ($with_perm_pgs as $pg) {
		if ($pg->getType() == 'user_groups') {
			$groups_with_perms[] = $pg;
		} else {
			$c = Contacts::findById($pg->getContactId());
			if ($c instanceof Contact && !$c->getDisabled() && ($c->getUserType() >= logged_user()->getUserType() || $c->getId() == logged_user()->getId())) {
				// key is to order by role and name
				$users_with_perms[str_pad($c->getUserType(), 2, '0', STR_PAD_LEFT) . "_" . $c->getObjectName()] = $c;
			}
		}
	}
	ksort($users_with_perms);
?>

	<input id="<?php echo $genid ?>hfPerms" type="hidden" value="<?php echo str_replace('"',"'", json_encode($member_permissions));?>"/>
	<input id="<?php echo $genid ?>hfAllowedOT" type="hidden" value="<?php echo str_replace('"',"'", json_encode($allowed_object_types_json));?>"/>
	
	<input id="<?php echo $genid ?>hfPermsSend" name="permissions" type="hidden" value=""/>
	
	<div class="member-permissions">
		<div class="users-container"><ul id="<?php echo $genid?>_permissions_list">
	<?php
		$max_users_to_show = 10;
		$count = 0;
		foreach ($users_with_perms as $user) {
			$count++;
			$add_cls = '';
			if ($count > $max_users_to_show) $add_cls = " hidden";
	?>
			<li class="user-data<?php echo $add_cls?>" id="<?php echo $genid?>_pg_<?php echo $user->getPermissionGroupId()?>">
			<?php if ($user->hasPicture()){ ?>
				<div class="coViewIconImage"><img src="<?php echo $user->getPictureUrl() ?>" alt="<?php echo clean($user->getObjectName()) ?>" /></div>
			<?php } else { ?>
				<div class="coViewIconImage ico-large-contact"></div>
			<?php } ?>
				<div class="user-name-container">
					<span id="username_<?php echo $user->getPermissionGroupId()?>" class="bold"><?php echo $user->getObjectName()?></span>
					<input id="<?php echo $genid ?>_is_guest_<?php echo $user->getPermissionGroupId()?>" name="is_guest" type="hidden" value="<?php echo ($user->isGuest() ? '1' : '0')?>"/>
					<input id="<?php echo $genid ?>_user_id_<?php echo $user->getPermissionGroupId()?>" name="user_id" type="hidden" value="<?php echo $user->getId()?>"/>
					
					<?php if ($user->getCompanyId() > 0) { ?>
					<div class="desc"><?php echo $user->getCompany()->getObjectName(); ?></div>
					<?php } ?>
					
					<div class="desc"><?php echo $user->getUserTypeName(); ?></div>
				</div>
				<div class="clear"></div>
			</li>
	<?php
		}
		
		foreach ($groups_with_perms as $group) {
			$count++;
			$add_cls = '';
			if ($count > $max_users_to_show) $add_cls = " hidden";
	?>
			<li class="user-data<?php echo $add_cls?>" id="<?php echo $genid?>_pg_<?php echo $group->getId()?>">
				<div class="coViewIconImage ico-large-group"></div>
				<div class="user-name-container">
					<span id="username_<?php echo $group->getId()?>" class="bold"><?php echo $group->getName()?></span>
					<input id="<?php echo $genid ?>_is_guest_<?php echo $group->getId()?>" name="is_guest" type="hidden" value="0"/>
					<div class="desc"><?php echo lang('group') ?></div>
				</div>
				<div class="clear"></div>
			</li>
	<?php
		}
	?>
		</ul></div>
		
	<?php if ($count > $max_users_to_show) { ?>
		<div class="clear"></div>
		<div style="margin-top: 15px;">
			<a class="db-ico coViewAction ico-expand" href="#" onclick="$('.user-data').removeClass('hidden');$(this).hide();" id="<?php echo $genid?>_more_users_permissions"><?php echo lang('show all users with permissions x more', $count - $max_users_to_show)?></a>
		</div>
	<?php } ?>
		
		<div class="clear"></div>
		<div style="margin-top: 15px; width:450px;">
		<?php
			// Permission group selector parameters
			$container_id = $genid . '_pg_selector';
			$extra_param = '0';
			$search_function = 'ogSearchSelector.searchPermissionGroup';
			$select_function = 'ogSearchSelector.onItemPermissionGroupSelect';
			$search_placeholder = lang('add permissions to more users or groups');
			$result_limit = '5';
			$search_minLength = 0;
			$search_delay = 500;
			
			include get_template_path("search_selector_view", "search_selector");			
		?>
		</div>


		<div id="<?php echo $genid ?>member_permissions" class="permission-form-container" style="display:none;">
		  <div id="<?php echo $genid ?>pg_name" style="font-weight:bold;font-size:120%;padding-bottom:5px"></div>
	  	  <table>
		  	<tr style="border-bottom:1px solid #888;margin-bottom:5px">
		  	<td style="vertical-align:middle">
		  		<span class="perm_all_checkbox_container">
					<?php echo checkbox_field($genid . 'pAll', false, array('id' => $genid . 'pAll', 'onclick' => 'og.userPermissions.ogPermAllChecked("' . $genid . '", this.checked)')) ?>
					<label style="font-weight:bold" for="<?php echo $genid ?>pAll" class="checkbox"><?php echo lang('all') ?></label>   
		  		</span>
		  	</td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-3" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 3);return false;"><?php echo lang('read write and delete') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-2" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 2);return false;"><?php echo lang('read and write') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-1" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 1);return false;"><?php echo lang('read only') ?></a></td>
		  	<td align=center style="padding:0 10px;width:100px;"><a href="#" class="internalLink radio-title-0" onclick="og.userPermissions.ogPermSetLevel('<?php echo $genid ?>', 0);return false;"><?php echo lang('none no bars') ?></a></td></tr>
		  	
		<?php 
			$row_cls = "";
			foreach ($allowed_object_types as $ot) {
				$row_cls = $row_cls == "" ? "altRow" : "";
				$id_suffix = $ot->getId();
				$change_parameters = '\'' . $genid . '\', ' . $ot->getId();
		?>
		  	<tr class="<?php echo $row_cls?>">
		  		<td style="padding-right:20px"><span id="<?php echo $genid.'obj_type_label'.$id_suffix?>"><?php echo lang($ot->getName()) ?></span></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '3', 'style' => 'width:16px', 'id' => $genid . 'rg_3_'.$id_suffix, 'class' => "radio_3")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '2', 'style' => 'width:16px', 'id' => $genid . 'rg_2_'.$id_suffix, 'class' => "radio_2")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '1', 'style' => 'width:16px', 'id' => $genid . 'rg_1_'.$id_suffix, 'class' => "radio_1")) ?></td>
		  		<td align=center><?php echo radio_field($genid .'rg_'.$id_suffix, false, array('onchange' => 'og.userPermissions.ogPermValueChanged('. $change_parameters .')', 'value' => '0', 'style' => 'width:16px', 'id' => $genid . 'rg_0_'.$id_suffix, 'class' => "radio_0")) ?></td>
		    </tr>
		<?php }?>
		  </table>
		<?php if ($member instanceof Member) { ?>
		  <input type="hidden" id="<?php echo $genid?>_dim_id" value="<?php echo $member->getDimensionId()?>" />
		  <div class="apply-member-permissions-to-submembers">
			<?php echo checkbox_field('apply_to_submembers', false, array('id' => $genid."apply_to_submembers"))?>
			<?php echo label_tag("", $genid."apply_to_submembers", false, array('id' => $genid . "_apply_to_submembers_label", "style" => "display:inline;cursor:pointer;"))?>
		  </div>
		<?php } ?>
		  
		  <div class="additional-member-permissions">
		<?php $ret=null; Hook::fire('render_additional_member_permissions', array('member' => $member, 'genid' => $genid, 'dim' => $current_dimension, 'ot_id' => $obj_type_sel), $ret); ?>
		  </div>
		  
		  <div style="float:right;">
		<?php 
		  	if ($member instanceof Member) { 
				?><input id="<?php echo $genid?>_member_id" type="hidden" name="member_id" value="<?php echo $member->getId()?>"/><?php 
		  		$save_perms_fn = "og.userPermissions.savePermissions('".$genid."', ".$member->getId().");";
			} else {
				$save_perms_fn = "";
			}
		?>
			<button title="<?php echo lang('cancel')?>" class="add-first-btn" onclick="$('#<?php echo $genid?>_close_link').click();" id="<?php echo $genid?>_cancel_btn" style="margin-right:10px;">
				<img src="public/assets/themes/default/images/16x16/del.png">&nbsp;<?php echo lang('cancel')?>
			</button>
			
			<button title="<?php echo lang('save changes')?>" class="add-first-btn" onclick="<?php echo $save_perms_fn?> og.userPermissions.afterChangingPermissions('<?php echo $genid?>'); $('#<?php echo $genid?>_close_link').click();" id="<?php echo $genid?>_close_btn">
				<img src="public/assets/themes/default/images/16x16/save.png">&nbsp;<?php echo lang('save changes')?>
			</button>
			
		  </div>
		</div>
	
	</div>

<script>
var genid = '<?php echo $genid ?>';

$(function() {
	
	og.userPermissions.current_pg_id = 0;
	
	og.userPermissions.loadPermissions(genid, "user_selector");
	
	$(".user-data").click(function() {
		og.userPermissions.showPermissionsPopup($(this), genid);
	});
	
});

</script>