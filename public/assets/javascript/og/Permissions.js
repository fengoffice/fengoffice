/**
 *  Permissions
 *
 * Author: Alvaro Torterola (alvaro.torterola@fengoffice.com)
 */


/******************************************************
 * Functions for member selector
******************************************************/

//	Loads the permission info from a hidden field. 
//	The name of the hidden field must be of the form <genid> + 'hfPerms'
og.permissionInfo = {};

og.ogLoadPermissions = function(genid, isNew){
	var hf = document.getElementById(genid + 'hfPerms');
	if (hf && hf.value != ''){
		var hf_ot = document.getElementById(genid + 'hfAllowedOT');
		var hf_ot_mem = document.getElementById(genid + 'hfAllowedOTbyMemType');
		var hf_memt = document.getElementById(genid + 'hfMemTypes');

		og.permissionInfo[genid] = {
			permissions: Ext.util.JSON.decode(hf.value),
			allowedOt: Ext.util.JSON.decode(hf_ot.value),
			allowedOtByMemType: Ext.util.JSON.decode(hf_ot_mem.value),
			member_types: Ext.util.JSON.decode(hf_memt.value)
		}

		Ext.removeNode(hf);
		Ext.removeNode(hf_ot);
		Ext.removeNode(hf_ot_mem);
		Ext.removeNode(hf_memt);
	} else {
		og.permissionInfo[genid] = {};
	}
	
	og.eventManager.fireEvent('on load user permissions', {});
}

og.getPermissionsForMember = function(genid, member_id) {
	if (!og.permissionInfo[genid].permissions[member_id]) 
		og.permissionInfo[genid].permissions[member_id] = [];
	return og.permissionInfo[genid].permissions[member_id];
}

og.addPermissionsForMember = function(genid, member_id, perm) {
	og.permissionInfo[genid].permissions[member_id].push(perm);
}

og.deletePermissionsForMember = function(genid, member_id) {
	og.permissionInfo[genid].permissions[member_id] = [];
}

og.canEditPermissionObjType = function(genid, member_id, obj_type) {
	var mem_type = og.permissionInfo[genid].member_types[member_id];
	if (!mem_type) return false;
	var allowed = og.permissionInfo[genid].allowedOtByMemType[mem_type];
	for (var i=0; i<allowed.length; i++) {
		if (allowed[i] == obj_type) return true;
	}
	return false;
}

og.setReadOnlyObjectTypeRow = function(genid, dim_id, obj_type, readonly) {
	for (var i=0; i<4; i++) {
		var radio = null;
		var el = Ext.get(genid + 'rg_'+ i +'_' + dim_id + '_' + obj_type);
		if (el) radio = el.dom;
		if (radio) radio.disabled = readonly;
	}
	var label = Ext.get(genid + 'obj_type_label' + dim_id + '_' + obj_type);
	if (label) {
		if (readonly) label.addClass('desc');
		else label.removeClass('desc');
	}
}

og.loadMemberPermissions = function(genid, dim_id, member_id) {
	var allowed_ot = og.permissionInfo[genid].allowedOt;
	var member_perms = og.getPermissionsForMember(genid, member_id);
	
	for (var i=0; i < allowed_ot[dim_id].length; i++) {
		var val = 0;
		var found = false;
		for (var j=0; j<member_perms.length; j++) {
			var perm = member_perms[j];
			if (!perm) continue;
			if (perm.o == allowed_ot[dim_id][i]) {
				val = perm.w == 1 && perm.d == 1 ? 3 : (perm.w == 1 ? 2 : (perm.r ? 1 : 0));
				found = true;
				break;
			}
		}
		if (!found) {
			og.permissionInfo[genid].permissions[member_id].push({o: allowed_ot[dim_id][i], d:0 , w:0, r:0});
		}
		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + allowed_ot[dim_id][i]), val);
		
		og.setReadOnlyObjectTypeRow(genid, dim_id, allowed_ot[dim_id][i], !og.canEditPermissionObjType(genid, member_id, allowed_ot[dim_id][i]));
	}

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = og.hasAllPermissions(genid, member_id, member_perms);
}

//Action to execute when the value of an element of the displayed permission changes
og.ogPermValueChanged = function(genid, dim_id, obj_type){
	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);

	for (var i=0; i<member_perms.length; i++) {
		var tmp = member_perms[i];
		if (tmp.o == obj_type) {
			perm = tmp;
			break;
		}
	}
	if (!perm) return;
		
	var value = og.ogGetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + obj_type));

	perm.modified = true;
	perm.d = (value == 3);
	perm.w = (value >= 2);
	perm.r = (value >= 1);

	if (perm.r) {
		module_check = document.getElementById(genid + 'mod_perm['+perm.o+']');
		if(module_check && !module_check.checked) module_check.checked = true; 
	}

	og.markMemberPermissionModified(genid, dim_id, member_id);

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = og.hasAllPermissions(genid, member_id, member_perms);
}

og.hasAllPermissions = function(genid, member_id, member_permissions) {
	for (var i=0; i<member_permissions.length; i++) {
		if (!member_permissions[i] || !og.canEditPermissionObjType(genid, member_id, member_permissions[i].o)) continue;
		if (!(member_permissions[i].d && member_permissions[i].w && member_permissions[i].r)) return false;
	}
	return true;
}

og.hasAnyPermissions = function(genid, member_id) {
	var member_perms = og.getPermissionsForMember(genid, member_id);
	for (var x=0; x<member_perms.length; x++) {
		if (!member_perms[x] || !og.canEditPermissionObjType(genid, member_id, member_perms[x].o)) continue;
		if (member_perms[x] && member_perms[x].r == 1) return true;
	}
	return false;
}

//Sets all radio permissions to a specific level for a given member
og.ogPermSetLevel = function(genid, dim_id, level){
	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);

	for (var i=0; i<member_perms.length; i++) {
		//if (!og.canEditPermissionObjType(genid, member_id, member_perms[i].o)) continue;
		if (!member_perms[i]) {
			member_perms[i] = {o: og.permissionInfo[genid].allowedOt[dim_id][i], d: 0, w: 0, r: 0};
			og.addPermissionsForMember(genid, member_id, member_perms[i]);
		}
		
		// if radio element is not visible => it cannot be selected
		var radio_el = document.getElementById(genid + "rg_" + level + "_" + dim_id + "_" + member_perms[i].o);
		if (!radio_el || radio_el.style.display == 'none') {
			continue;
		}
		
		member_perms[i].d = (level == 3);
		member_perms[i].w = (level >= 2);
		member_perms[i].r = (level >= 1);
		member_perms[i].modified = true;

		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + dim_id + "_" + member_perms[i].o), level);

		if (member_perms[i].r) {
			module_check = document.getElementById(genid + 'mod_perm['+member_perms[i].o+']');
			if(module_check && !module_check.checked) module_check.checked = true; 
		}
	}

	og.markMemberPermissionModified(genid, dim_id, member_id);
	
	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + dim_id + 'pAll');
	if (chk)
		chk.checked = level == 3;
}

//Action to execute when the 'All' checkbox is checked or unchecked
og.ogPermAllChecked = function(genid, dim_id, value){
	var level = value ? 3 : 0;
	og.ogPermSetLevel(genid, dim_id, level);
}

//Applies the current member permission settings to all submembers
og.ogPermApplyToSubmembers = function(genid, dim_id, from_root_node){

	var member_id = og.permissionInfo[genid].selectedMember;
	var member_perms = og.getPermissionsForMember(genid, member_id);
	
	var trees = [Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree'), Ext.getCmp(genid + '_without_permissions_' + dim_id + '-tree')];
	
	for (var t=0; t<trees.length; t++) {
		var tree = trees[t];
		if (from_root_node) {
			var node = tree.getRootNode();
		} else {
			var node = tree.getNodeById(member_id);
		}
		if (!node) return;
	
		node.submember_ids = og.ogPermGetSubMemberIdsFromNode(node);
		node.expanded_subnodes = 0;
		
		// ensure all nodes visibles before updating permissions
		node.expand(true, false, function(n){
			
			if (isNaN(n.id) && n.id != tree.getRootNode().id) {
				return;
			}
			
			// only execute when all nodes are expanded, when last node is expanded
			node.expanded_subnodes++;
			if (node.expanded_subnodes < node.submember_ids.length + 1) {
				return;
			}
			
			// execute permissions update
			for (var i=0; i<node.submember_ids.length; i++) {
				og.deletePermissionsForMember(genid, node.submember_ids[i]);
				
				for (var j=0; j<member_perms.length; j++) {
					if (!member_perms[j]) {
						if (og.tmp_role_id[genid] && og.defaultRolePermissions) {
							var role_id = og.tmp_role_id[genid];
							var def_perms = og.defaultRolePermissions[role_id];
							member_perms[j] = {o:ot, d:def_perms[ot].d, w:def_perms[ot].w, r:def_perms[ot].r, modified:true};
						}
						if (!member_perms[j]) {
							member_perms[j] = {o: og.permissionInfo[genid].allowedOt[dim_id][j].o, d: 0, w: 0, r: 0};
						}
						og.addPermissionsForMember(genid, member_id, member_perms[j]);
					}
					
					var radio = Ext.get(genid + 'rg_3_' + dim_id + '_' + member_perms[j].o);
					var perm = {o: member_perms[j].o, d: member_perms[j].d, w: member_perms[j].w, r: member_perms[j].r, modified:true};
					
					og.addPermissionsForMember(genid, node.submember_ids[i], perm);
					if (member_perms[j].r) {
						module_check = document.getElementById(genid + 'mod_perm['+member_perms[j].o+']');
						if(module_check && !module_check.checked) module_check.checked = true; 
					}
				}
				og.markMemberPermissionModified(genid, dim_id, node.submember_ids[i]);
			}
			
			og.eventManager.fireEvent('after apply permissions to submembers', {node:node, dim_id: dim_id, subids:node.submember_ids, member_id:member_id});
		});
	}
}

og.eventManager.addListener('after apply permissions to submembers', 
 	function (data){
		// relocate all children in trees
		var node = data.node;
		var ids = data.subids;
		var dim_id = data.dim_id;
		var member_id = data.member_id;
		
		og.ogPermRelocateNodesInTrees(genid, dim_id, ids);
		if (!node.hasChildNodes() && node.ownerTree) {
			// remove node if it cannot be in its tree.
			if (node.ownerTree.getId() == genid + '_with_permissions_' + dim_id + '-tree' && !og.hasAnyPermissions(genid, member_id) || 
				node.ownerTree.getId() == genid + '_without_permissions_' + dim_id + '-tree' && og.hasAnyPermissions(genid, member_id)) {
				
				node.remove();
			}
		}
 	}
);

//Applies the current member permission settings to all dimension members
og.ogPermApplyToAllMembers = function(genid, dim_id){
	og.ogPermApplyToSubmembers(genid, dim_id, true);
}

og.ogPermGetSubMemberIdsFromNode = function(node){
	var result = new Array();
	if (node && node.firstChild){
		var children = node.childNodes;
		for (var i = 0; i < children.length; i++){
			if (children[i] && !isNaN(children[i].id)) {
				result[result.length] = children[i].id;
				result = result.concat(og.ogPermGetSubMemberIdsFromNode(children[i]));
			}
		}
	}
	return result;
}

og.ogPermRelocateNodesInTrees = function(genid, dim_id, ids) {
	new_ids = [];
	for (var i=0;i<ids.length;i++) new_ids.push(parseInt(ids[i]));
	ids = new_ids;
	
	var perm_tree = Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree');
	var no_perm_tree = Ext.getCmp(genid + '_without_permissions_' + dim_id + '-tree');
	
	var nodes_to_remove = [];

	for (var i=0; i<ids.length; i++) {
		var member_id = parseInt(ids[i]);
		var node = perm_tree.getNodeById(member_id);
		if (node && !og.hasAnyPermissions(genid, member_id)) {
			var node_exist = no_perm_tree.getNodeById(member_id);
			if (node_exist) {
				node_exist.ensureVisible();
			} else {
				og.ogPermInsertNodeInTree(node, no_perm_tree, {cancel:false});
			}
			if (node.parentNode) {
				if (!node.hasChildNodes()) node.remove();
				else nodes_to_remove.push(node);
			}
			
		} else {
			
			var node2 = no_perm_tree.getNodeById(member_id);
			if (node2 && og.hasAnyPermissions(genid, member_id)) {
				var node_exist = perm_tree.getNodeById(node2.id);
				if (node_exist) {
					node_exist.ensureVisible();
				} else {
					og.ogPermInsertNodeInTree(node2, perm_tree, {cancel:false});
				}
				if (node2.parentNode) {
					if (!node2.hasChildNodes()) node2.remove();
					else nodes_to_remove.push(node2);
				}
			}
		}
		
	}
	
	nodes_to_remove.reverse();
	for (var j=0; j<nodes_to_remove.length; j++) {
		node = nodes_to_remove[j];
		if (!node.hasChildNodes()) node.remove();
	}
	
}


og.setDefaultPermissionsForMember = function(genid, dimension_id, member_id, role_id) {

	var current_permissions = og.getPermissionsForMember(genid, member_id);
	var perms = og.defaultRolePermissions[role_id];

	for (ot in perms) {
		var id = '';
		if (perms[ot].d == "1") id = (genid + 'rg_3_'+ dimension_id + '_' + ot);
		else if (perms[ot].w == "1") id = (genid + 'rg_2_'+ dimension_id + '_' + ot);
		else if (perms[ot].r == "1") id = (genid + 'rg_1_'+ dimension_id + '_' + ot);
		
		if (id != '' && document.getElementById(id)) {
			var new_p = {o:ot, d:perms[ot].d, w:perms[ot].w, r:perms[ot].r, modified:true};
			og.addPermissionsForMember(genid, member_id, new_p);
			$("#"+id).click();
		}
		
	}
}


og.ogPermInsertNodeHierarchy = function(node, tree_to) {
	// remove empty nodes
	var empty_nodes = [];
	for (var i=0; i < tree_to.getRootNode().childNodes.length; i++) {
		var elnode = tree_to.getRootNode().childNodes[i];
		if (elnode && elnode.text=='') empty_nodes.push(elnode);
	}
	for (var i=0; i < empty_nodes.length; i++) {
		empty_nodes[i].remove();
	}

	var node_id = node.id;
	if (!isNaN(node.parentNode.id) && node.parentNode.id > 0) {
		
		var to_parent = tree_to.getNodeById(node.parentNode.id);
		if (to_parent) {
		
			to_parent.expand();
			var new_node = new Ext.tree.TreeNode({ 'id': parseInt(node.id), 'text': node.text, 'iconCls': node.getUI().getIconEl().className });
			var ok = to_parent.appendChild(new_node);
			
		} else {
			og.ogPermInsertNodeHierarchy(node.parentNode, tree_to);
			
			to_parent = tree_to.getNodeById(node.parentNode.id);
			to_parent.expand();
			var new_node = new Ext.tree.TreeNode({ 'id': parseInt(node.id), 'text': node.text, 'iconCls': node.getUI().getIconEl().className });
			var ok = to_parent.appendChild(new_node);
		}
	} else {
		var new_node = new Ext.tree.TreeNode({ 'id': parseInt(node.id), 'text': node.text, 'iconCls': node.getUI().getIconEl().className });
		var ok = tree_to.getRootNode().appendChild(new_node);
	}

	var inserted = tree_to.getNodeById(node_id)
	if (inserted) inserted.ensureVisible();

	// add emtpy nodes, to fill container area (to allow d&d)
	setTimeout(function() {
		while (tree_to.getRootNode().childNodes.length < 9) {
			var empty_node = new Ext.tree.TreeNode({ 'id': 'temp-'+Ext.id(), 'text': '', 'iconCls': '' });
			tree_to.getRootNode().appendChild(empty_node);
		}
	}, 500);
}


og.ogPermRemoveNodeHierarchy = function(node, child_id) {

	node.expand(true, false, function() {
		var can_remove = false;
		if (!node.childNodes || node.childNodes.length == 0) {
			can_remove = true;
		} else {
			if (node.childNodes.length == 1 && child_id && (!node.firstChild || node.firstChild.id == child_id)) {
				can_remove = true;
			}
		}

		if (can_remove) {
			if (node.parentNode && !isNaN(node.parentNode.id) && node.parentNode.id > 0) {
				og.ogPermRemoveNodeHierarchy(node.parentNode, node.id);
			}
			node.remove();
		}
	});
}

og.ogPermInsertNodeInTree = function(node, tree_to, dropEvent) {
	// rebuild hierarchy in both trees
	og.ogPermInsertNodeHierarchy(node, tree_to);
	og.ogPermRemoveNodeHierarchy(node);
	dropEvent.cancel = true;
		
	return dropEvent;
}


og.permissionsDDAddRemovePermissions = function(dropEvent) {

	var tree_from = dropEvent.source.tree;
	var tree_to = dropEvent.tree;
	var node = dropEvent.data.node;
	
	// if source and target trees are the same => return
	if (tree_from.id == tree_to.id) {
		dropEvent.cancel = true;
		return;
	}

	// if node already exists in the tree => return
	var node_exist = tree_to.getNodeById(node.id);
	if (node_exist) {
		dropEvent.cancel = true;
		if (!node.hasChildNodes()) node.remove();
		node_exist.ensureVisible();
	//	return;
	}

	// insert into tree
	if (!dropEvent.cancel) {
		node.expand(true);
		dropEvent = og.ogPermInsertNodeInTree(node, tree_to, dropEvent);
	}

	// modify permissions
	var remove_permissions = true;
	if (tree_from.id.indexOf('_without_permissions_') > 0) {
		remove_permissions = false;
	}
	
	if (!isNaN(node.id)) {
		if (remove_permissions) {

			og.permissionInfo[genid].selectedMember = node.id;
			og.ogPermSetLevel(genid, tree_to.dimensionId, 0);
			
			if (node.hasChildNodes()) {

				var tree_title = tree_to.title.toLowerCase().replace("&", "and");
				var question = lang('do you want to remove permissions for all submembers too', tree_title, node.text);
				$("#"+genid+"ask_to_remove_sumbembers_text").html(question);

				$("#"+genid+"parent_member_removed_perms").val(node.id);
				$("#"+genid+"dimension_id_removed_perms").val(tree_to.dimensionId);
				
				$('#'+ genid +'ask_to_remove_from_submembers').modal({
					'escClose': true,
					'overlayClose': false,
					'closeHTML': '<a id="'+genid+'_remove_submembers_close_link" class="modal-close" title="'+lang('close')+'"></a>',
					'onShow': function (dialog) {
						$("#"+genid+"_close_link").addClass("modal-close-img");
					}
				});
			}
			
		} else {
			
			og.showPermissionsPopup(genid, tree_to.dimensionId, node.id, node.text, true);
			
		}
	}
}


og.toggleManualPermissions = function(genid, is_modal) {
	var checked = $("#"+genid+"_set_manual_permissions_checkbox").attr('checked') == 'checked';
	$("#"+genid+"_set_manual_permissions").val(checked ? 1 : 0);
	if (checked) {
		$("#"+genid+"_dimension_permissions").show();
		$("#"+genid+"_manual_perm_help").hide();
		
		// show root permissions only if user is executive, manager or administrator
		var type = $("#" + genid + "_user_type_sel_role").find('option:selected').val();
		var executive_selected = og.executive_permission_group_ids.indexOf(parseInt(type)) >= 0;
		if (executive_selected) {
			$("#"+genid+"_root_permissions").show();
		} else {
			$("#"+genid+"_root_permissions").hide();
		}
		
		if (is_modal) {
			setTimeout(function(){
				$("#"+genid+"permissions").animate({
				   scrollTop: $("#"+genid+"_set_manual_permissions_checkbox").offset().top - 120
				});
			}, 600);
			og.resize_modal_form();
		} else {
			$("#"+genid+"permissions").closest('form').parent().animate({
			   scrollTop: $("#"+genid+"_set_manual_permissions_checkbox").offset().top - 140
			});
		}
	} else {
		$("#"+genid+"_dimension_permissions").hide();
		$("#"+genid+"_root_permissions").hide();
		$("#"+genid+"_manual_perm_help").show();
	}
}


og.removePermissionsForSubmembers = function(genid) {
	var dim_id = $("#"+genid+"dimension_id_removed_perms").val();
	og.ogPermApplyToSubmembers(genid, dim_id);
	// now remove node from "with_permissions" tree
	var tree = Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree');
	node = tree.getNodeById(og.permissionInfo[genid].selectedMember);
	if (node) node.remove();
}

og.showPermissionsPopup = function(genid, dim_id, mem_id, name, set_default_permissions) {

	og.permissionInfo[genid].selectedMember = mem_id;
	og.loadMemberPermissions(genid, dim_id, mem_id);
	$('#'+ genid + '_' + dim_id + 'member_name').html(name);

	var tree = Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree');

	var tree_title = tree.title.toLowerCase().replace("&", "and");
	$("#"+genid+ "_"+dim_id+ "_apply_to_submembers").html(lang('apply to all submembers', tree_title, name));
	$("#"+genid+ "_"+dim_id+ "_apply_to_all_members").html(lang('apply to all members', tree_title));
	
	$('#'+ genid +'member_permissions' + dim_id).modal({
		'escClose': true,
		'overlayClose': false,
		'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
		'onShow': function (dialog) {
			$("#"+genid+"_close_link").addClass("modal-close-img");

			var role_id = $("#"+genid+"_user_type_sel_role").val();
			if (set_default_permissions) {
				og.setDefaultPermissionsForMember(genid, dim_id, mem_id, role_id);
			}
			
			// show only the possible radio buttons for permissions (depending on role)
			og.showHidePermissionsRadioButtonsByRole(genid, dim_id, role_id);
		}
	});
}

og.showHidePermissionsRadioButtonsByRole = function(genid, dim_id, role_id) {
	var max_perms = og.maxRoleObjectTypePermissions[role_id];
	
	var object_types = [];
	var ot_radios = $("#"+genid+"member_permissions"+dim_id+" input.radio_3");
	for (var j=0; j<ot_radios.length; j++) {
		var ot = ot_radios[j].id.substring(ot_radios[j].id.lastIndexOf('_')+1);
		object_types.push(ot);
	}
	
	if (!max_perms) { // user groups => show all radiobuttons
		for (var i=0; i<object_types.length; i++) {
			var ot = object_types[i];
			$("#" + genid + "rg_3_" + dim_id + '_' + ot).show();
			$("#" + genid + "rg_2_" + dim_id + '_' + ot).show();
			$("#" + genid + "rg_1_" + dim_id + '_' + ot).show();
		}
	} else { // user => show/hide radiobuttons depending on the user role
	  for (var i=0; i<object_types.length; i++) {
		var ot = object_types[i];
		
		if (max_perms[ot] && max_perms[ot].can_delete) $("#" + genid + "rg_3_" + dim_id + '_' + ot).show();
		else $("#" + genid + "rg_3_" + dim_id + '_' + ot).hide();
		
		if (max_perms[ot] && max_perms[ot].can_write) $("#" + genid + "rg_2_" + dim_id + '_' + ot).show();
		else $("#" + genid + "rg_2_" + dim_id + '_' + ot).hide();
		
		if (max_perms[ot]) $("#" + genid + "rg_1_" + dim_id + '_' + ot).show();
		else $("#" + genid + "rg_1_" + dim_id + '_' + ot).hide();
	  }
	}
}

og.afterChangingPermissions = function(genid) {
}



og.markMemberPermissionModified = function(genid, dim_id, member_id) {
	var trees = [Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree'), Ext.getCmp(genid + '_without_permissions_' + dim_id + '-tree')];
	for (var t=0; t<trees.length; t++) {
		var tree = trees[t];
		if (!tree) return;
		var node = tree.getNodeById(member_id);
		if (node) {
			if (og.hasAnyPermissions(genid, member_id)) {
				node.getUI().removeClass('tree-node-no-permissions');
				node.getUI().addClass('tree-node-modified');
			} else {
				node.getUI().removeClass('tree-node-modified');
				node.getUI().addClass('tree-node-no-permissions');
			}
			
			var pnode = node.parentNode;
			while (pnode && !isNaN(pnode.id)) {
				if (!og.hasAnyPermissions(genid, pnode.id)) {
					pnode.getUI().addClass('tree-node-no-permissions');
				}
				pnode = pnode.parentNode;
			}
		}
	}
}

//Sets the permission information to send inside a hidden field. 
//The id of the hidden field must be of the form: <genid> + 'hfPermsSend'
og.ogPermPrepareSendData = function(genid){
	var result = new Array();
	if (!og.permissionInfo[genid]) {
		return true;
	}
	var permissions = og.permissionInfo[genid].permissions;
	for (i in permissions){
		for (var j = 0; j < permissions[i].length; j++){
			var p = permissions[i][j];
			if (p && p.modified) {
				result[result.length] = {'m':i, 'o':p.o, 'd':p.d, 'w':p.w, 'r':p.r};
			}
		}
	}
	
	var hf = document.getElementById(genid + 'hfPermsSend');
	if (hf) {
		hf.value = Ext.util.JSON.encode(result);
	}
	
	var hfpg = document.getElementById(genid + 'hfPgId');
	var pg_id = hfpg ? hfpg.value : 0;
	og.eventManager.fireEvent('on send user permissions', {pg_id: pg_id, perms: result});
		
	return true;
}

og.removeAllPermissionsForObjType = function(genid, obj_type) {
	for (member_id in og.permissionInfo[genid].permissions) {
		for (var i=0; i<og.permissionInfo[genid].permissions[member_id].length; i++) {
			var perm = og.permissionInfo[genid].permissions[member_id][i];
			if (perm.o == obj_type) {
				perm.r = 0;
				perm.w = 0;
				perm.d = 0;
				break;
			}
		}
	}
	for (var i=0; i<og.permissionDimensions.length; i++) {
		var radio = document.getElementsByName(genid + "rg_" + og.permissionDimensions[i] + "_" + obj_type);
		if (radio) og.ogSetCheckedValue(radio, 0);
	}
}

//	Returns the value of the radio button that is checked
og.ogGetCheckedValue = function(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}


//	Sets the radio button with the given value as being checked
og.ogSetCheckedValue = function(radioObj, newValue) {
	if(!radioObj)
		return;
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		radioObj.checked = (radioObj.value == newValue.toString());
		return;
	}
	for(var i = 0; i < radioLength; i++) {
		radioObj[i].checked = false;
		if(radioObj[i].value == newValue.toString()) {
			radioObj[i].checked = true;
		}
	}
}




og.afterUserTypeChangeAndPermissionsClick = function(genid) {
	
	if (og.tmp_must_check_member_permissions && og.tmp_must_check_member_permissions[genid]) {
		if (is_new_contact) {
    		// poner permisos por defecto
			og.setDefaultPermissionsForAllMembers(genid);
		} else {
    		// verificar cada permiso y hacer el downgrade si corresponde
    		og.checkMemberPermissionsForRole(genid);
		}
	}
}

og.checkMemberPermissionsForRole = function(genid) {
	var def_perms = og.defaultRolePermissions[og.tmp_role_id[genid]];
	var permissions = og.permissionInfo[genid].permissions;
	var all_mem_ids = [];
	
	for (mem_id in permissions) {
		var perms = permissions[mem_id];
		for (var i=0; i<perms.length; i++) {
    		var p = perms[i];
    		var dp = def_perms[parseInt(p.o)];
    		if (!dp) dp = {};
    		
    		if (!parseInt(dp.d) && parseInt(p.d) || !parseInt(dp.w) && parseInt(p.w) || !parseInt(dp.r) && parseInt(p.r)) {
	    		p.modified = true;
	    		if (!parseInt(dp.d)) p.d=0;
	    		if (!parseInt(dp.w)) p.w=0;
	    		if (!parseInt(dp.r)) p.r=0;
	    		
	    		all_mem_ids.push(mem_id);
    		}
		}
	}


	var trees = [];
	var containers = $(".single-tree.member-chooser-container");
	for (var i=0; i<containers.length; i++) {
		var id = containers[i].id;
		if (idx = containers[i].id.indexOf('with_permissions') >= 0) {
			dim_id = containers[i].id.replace('-container','');
    		dim_id = dim_id.substring(containers[i].id.lastIndexOf('_') + 1);
		}

		trees.push(Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree'));
		trees.push(Ext.getCmp(genid + '_without_permissions_' + dim_id + '-tree'));
	}
	for (var t=0; t<trees.length; t++) {
		var tree = trees[t];
		og.ogPermRelocateNodesInTrees(genid, tree.dimensionId, all_mem_ids);
	}
}

og.setDefaultPermissionsForAllMembers = function(genid) {

	var def_perms = og.defaultRolePermissions[og.tmp_role_id[genid]];
	var trees = [];
	var containers = $(".single-tree.member-chooser-container");
	
	for (var i=0; i<containers.length; i++) {
		var id = containers[i].id;
		if (idx = containers[i].id.indexOf('with_permissions') >= 0) {
			dim_id = containers[i].id.replace('-container','');
    		dim_id = dim_id.substring(containers[i].id.lastIndexOf('_') + 1);
		}

		trees.push(Ext.getCmp(genid + '_with_permissions_' + dim_id + '-tree'));
		trees.push(Ext.getCmp(genid + '_without_permissions_' + dim_id + '-tree'));
	}
	
	if (og.config.with_perm_user_types.indexOf(og.tmp_role_id[genid]) < 0) {
		// remove permissions if this user role is not configured to have permissions
		for (var t=0; t<trees.length; t++) {
			var tree = trees[t];
			var mem_ids = og.ogPermGetSubMemberIdsFromNode(tree.getRootNode());
			for (var j=0; j<mem_ids.length; j++) {
				if (isNaN(mem_ids[j])) continue;
				
				og.deletePermissionsForMember(genid, mem_ids[j]);
			}
			og.ogPermRelocateNodesInTrees(genid, tree.dimensionId, mem_ids);
		}
				    			
	} else {
		// add default permissions for all members
		for (var t=0; t<trees.length; t++) {
			var tree = trees[t];
			var mem_ids = og.ogPermGetSubMemberIdsFromNode(tree.getRootNode());
			
			for (var j=0; j<mem_ids.length; j++) {
				if (isNaN(mem_ids[j])) continue;
				
				og.deletePermissionsForMember(genid, mem_ids[j]);
				for (ot in def_perms) {
					var p = {o: ot, d: def_perms[ot].d, w: def_perms[ot].w, r: def_perms[ot].r, modified: true};
					og.addPermissionsForMember(genid, mem_ids[j], p);
				}
			}
			og.ogPermRelocateNodesInTrees(genid, tree.dimensionId, mem_ids);
			
		}
	}
	og.ogPermPrepareSendData(genid);
}


/******************************************************
 * Functions for user selector
******************************************************/

og.userPermissions = {};
og.userPermissions.permissionInfo = [];

og.userPermissions.loadPermissions = function (genid, selector_id) {
	var hf = document.getElementById(genid + 'hfPerms');
	if (hf && hf.value != ''){
		var hf_ot = document.getElementById(genid + 'hfAllowedOT');

		og.userPermissions.permissionInfo[genid] = {
			permissions: Ext.util.JSON.decode(hf.value),
			original_permissions: Ext.util.JSON.decode(hf.value),
			allowedOt: Ext.util.JSON.decode(hf_ot.value)		
		}

		if (selector_id) {
			og.userPermissions.permissionInfo[genid].selectorId = selector_id;
			for (pg_id in og.userPermissions.permissionInfo[genid].permissions) {
				og.userPermissions.setCheckedPG(genid, pg_id);
			}
		}
		
		Ext.removeNode(hf);
		Ext.removeNode(hf_ot);
	} else {
		og.userPermissions.permissionInfo[genid] = {};
	}
}

og.userPermissions.setCheckedPG = function(genid, pg_id) {
	var selector = Ext.getCmp(genid + og.userPermissions.permissionInfo[genid].selectorId);
	if (!selector) return;
	var node = selector.getNodeById(selector.nodeId(pg_id));
	if (node) {
		node.ensureVisible();
		node.suspendEvents();
		var checked = og.userPermissions.hasAnyPermissions(genid, pg_id);
		node.ui.toggleCheck(checked);
		node.user.checked = checked;
		node.resumeEvents();
	}
}

og.userPermissions.getPermissionsForPG = function(genid, pg_id) {
	if (!og.userPermissions.permissionInfo[genid].permissions[pg_id]) {
		og.userPermissions.permissionInfo[genid].permissions[pg_id] = [];
	}
	return og.userPermissions.permissionInfo[genid].permissions[pg_id];
}

og.userPermissions.loadPGPermissions = function(genid, pg_id) {
	var allowed_ot = og.userPermissions.permissionInfo[genid].allowedOt;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	
	for (var i=0; i < allowed_ot.length; i++) {
		var val = 0;
		var found = false;
		for (var j=0; j<permissions.length; j++) {
			var perm = permissions[j];
			if (perm.o == allowed_ot[i]) {
				val = perm.w == 1 && perm.d == 1 ? 3 : (perm.w == 1 ? 2 : (perm.r ? 1 : 0));
				found = true;
				break;
			}
		}
		if (!found) {
			og.userPermissions.permissionInfo[genid].permissions[pg_id].push({o: allowed_ot[i], d:0 , w:0, r:0});
		}
		document.getElementById(genid + 'rg_' + val + '_' + allowed_ot[i]).checked = 1;
	}

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = og.userPermissions.hasAllPermissions(genid, pg_id);
}

og.userPermissions.hasAllPermissions = function(genid, pg_id) {
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	for (var i=0; i<permissions.length; i++) {
		if (!(permissions[i].d && permissions[i].w && permissions[i].r)) return false;
	}
	return true;
}

og.userPermissions.hasAnyPermissions = function(genid, pg_id) {
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);
	for (var i=0; i<permissions.length; i++) {
		if (permissions[i].d || permissions[i].w || permissions[i].r) return true;
	}
	return false;
}

//Sets all radio permissions to a specific level for a given member
og.userPermissions.ogPermSetLevel = function(genid, level){
	var pg_id = og.userPermissions.permissionInfo[genid].selectedPG;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);

	for (var i=0; i<permissions.length; i++) {
		
		var radio_el = document.getElementById(genid + "rg_" + level + "_" + permissions[i].o);
		if (!radio_el || radio_el.style.display == 'none') {
			continue;
		}
		
		permissions[i].d = (level == 3);
		permissions[i].w = (level >= 2);
		permissions[i].r = (level >= 1);
		permissions[i].modified = true;

		og.ogSetCheckedValue(document.getElementsByName(genid + "rg_" + permissions[i].o), level);
	}

	og.userPermissions.setCheckedPG(genid, pg_id);
	
	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = level == 3;
}

//Action to execute when the value of an element of the displayed permission changes
og.userPermissions.ogPermValueChanged = function(genid, obj_type){
	var pg_id = og.userPermissions.permissionInfo[genid].selectedPG;
	var permissions = og.userPermissions.getPermissionsForPG(genid, pg_id);

	var perm = null;
	for (var i=0; i<permissions.length; i++) {
		var tmp = permissions[i];
		if (tmp.o == obj_type) {
			perm = tmp;
			break;
		}
	}
	if (perm == null) return;
		
	var value = og.ogGetCheckedValue(document.getElementsByName(genid + "rg_" + obj_type));

	perm.modified = true;
	perm.d = (value == 3);
	perm.w = (value >= 2);
	perm.r = (value >= 1);

	og.userPermissions.setCheckedPG(genid, pg_id);

	//Update the 'All' checkbox if all permissions are set
	var chk = document.getElementById(genid + 'pAll');
	if (chk)
		chk.checked = og.userPermissions.hasAllPermissions(genid, pg_id);
}

//Action to execute when the 'All' checkbox is checked or unchecked
og.userPermissions.ogPermAllChecked = function(genid, value){
	var level = value ? 3 : 0;
	og.userPermissions.ogPermSetLevel(genid, level);
}

og.userPermissions.ogPermPrepareSendData = function(genid, send_all){
	var result = new Array();
	if (!og.userPermissions.permissionInfo[genid]) {
		return true;
	}
	var permissions = og.userPermissions.permissionInfo[genid].permissions;
	for (i in permissions){
		if (!permissions[i] || typeof(permissions[i]) == 'function') continue;
		for (var j = 0; j < permissions[i].length; j++){
			var p = permissions[i][j];
			if (p && typeof(p) != 'function' && (p.modified || send_all)) {
				result[result.length] = {'pg':i, 'o':p.o, 'd':p.d, 'w':p.w, 'r':p.r};
			}
		}
	}
	
	var hf = document.getElementById(genid + 'hfPermsSend');
	if (hf) {
		hf.value = Ext.util.JSON.encode(result);
	}

	return true;
}


og.userPermissions.afterChangingPermissions = function(genid) {
	if (!og.userPermissions.hasAnyPermissions(genid, og.userPermissions.current_pg_id)) {
		$('#' + genid + '_pg_' + og.userPermissions.current_pg_id).remove();
	}
	
	if (og.on_member_permissions_after_change && og.on_member_permissions_after_change.length > 0) {
		for (var x=0; x<og.on_member_permissions_after_change.length; x++) {
			var func = og.on_member_permissions_after_change[x];
			if (typeof(func) == 'function') {
				func.call(null, genid, og.userPermissions.current_pg_id);
			}
		}
	}
	
	og.userPermissions.current_pg_id = 0;
}

og.userPermissions.showPermissionsPopup = function(container, genid) {
	var id = $(container).attr('id');
	var pg_id = id.substr(id.lastIndexOf('_') + 1);
	
	var name = $("#username_"+pg_id).html();
	var is_guest = $("#" + genid + "_is_guest_" + pg_id).val() > 0;
	
	var dim_id = $("#" + genid + "_dim_id").val();
	var tree = Ext.getCmp("dimension-panel-"+dim_id);
	if (tree) {
		var mem_name = $("#"+genid+"-name").val();
		var tree_title = tree.title.toLowerCase().replace("&", "and");
		$("#" + genid + "_apply_to_submembers_label").html(lang('apply to all submembers', tree_title, mem_name));
	}
	
	og.userPermissions.onUserSelect(genid, {id:pg_id, n:name, isg:is_guest});

	og.userPermissions.current_pg_id = pg_id;
	
	$('#'+ genid +'member_permissions').modal({
		'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
		'onShow': function (dialog) {
			$("#"+genid+"_close_link").addClass("modal-close-img");
			
			// show only the possible radio buttons for permissions (depending on role)
			var user_id = $("#" + genid + "_user_id_" + pg_id).val();
			if (user_id > 0) {
				var role_id = 0;
				if (og.allUsers[user_id]) {
					role_id = og.allUsers[user_id].role;
				}
				if (role_id > 0) {
					og.userPermissions.showHidePermissionsRadioButtonsByRole(genid, role_id);
				}
			}
			// set as modified to save them all 
			var permissions = og.userPermissions.permissionInfo[genid].permissions[pg_id];
			if (permissions.length > 0) {
				for (var i=0; i<permissions.length; i++) {
					if (permissions[i]) permissions[i].modified = true;
				}
			}
		},
		'onClose': function (dialog) {
			og.userPermissions.cancelPermissionsModification(genid, og.userPermissions.current_pg_id);
			$.modal.close();
		}
	});
}

og.userPermissions.cancelPermissionsModification = function(genid, pg_id) {
	// make a copy of the origial permissions and set as current
	var json = Ext.util.JSON.encode(og.userPermissions.permissionInfo[genid].original_permissions[pg_id]);
	og.userPermissions.permissionInfo[genid].permissions[pg_id] = Ext.util.JSON.decode(json);
}

og.userPermissions.showHidePermissionsRadioButtonsByRole = function(genid, role_id) {
	var max_perms = og.maxRoleObjectTypePermissions[role_id];
	
	var object_types = [];
	var ot_radios = $("#"+genid+"member_permissions input.radio_3");
	for (var j=0; j<ot_radios.length; j++) {
		var ot = ot_radios[j].id.substring(ot_radios[j].id.lastIndexOf('_')+1);
		object_types.push(ot);
	}
	
	if (!max_perms) { // user groups => show all radiobuttons
		for (var i=0; i<object_types.length; i++) {
			var ot = object_types[i];
			$("#" + genid + "rg_3_" + dim_id + '_' + ot).show();
			$("#" + genid + "rg_2_" + dim_id + '_' + ot).show();
			$("#" + genid + "rg_1_" + dim_id + '_' + ot).show();
		}
	} else { // user => show/hide radiobuttons depending on the user role
	  for (var i=0; i<object_types.length; i++) {
		var ot = object_types[i];
		
		if (max_perms[ot] && max_perms[ot].can_delete) $("#" + genid + "rg_3_" + ot).show();
		else $("#" + genid + "rg_3_" + ot).hide();
		
		if (max_perms[ot] && max_perms[ot].can_write) $("#" + genid + "rg_2_" + ot).show();
		else $("#" + genid + "rg_2_" + ot).hide();
		
		if (max_perms[ot]) $("#" + genid + "rg_1_" + ot).show();
		else $("#" + genid + "rg_1_" + ot).hide();
	  }
	}
}

og.userPermissions.onUserSelect = function(genid, arguments) {
	var panel = Ext.get(genid + 'member_permissions');
	var pg_id = arguments['id'];
	var name = arguments['n'];
	
	og.showHideNonGuestPermissionOptions(arguments['isg']);
	og.userPermissions.permissionInfo[genid].selectedPG = pg_id;
	og.userPermissions.loadPGPermissions(genid, pg_id);
	Ext.get(genid + 'pg_name').dom.innerHTML = name;

	if (og.on_member_permissions_user_select && og.on_member_permissions_user_select.length > 0) {
		for (var x=0; x<og.on_member_permissions_user_select.length; x++) {
			var func = og.on_member_permissions_user_select[x];
			if (typeof(func) == 'function') {
				func.call(null, genid, pg_id);
			}
		}
	}
}

og.userPermissions.removeAllPermissions = function(genid) {
	og.userPermissions.permissionInfo[genid].permissions = {};
}

og.userPermissions.reload_member_permissions = function (genid, dimension_id, parent_id) {
	if (og.member_is_new) {
		og.openLink(og.getUrl('member', 'get_parent_permissions', {dim_id: dimension_id, parent:parent_id}), {
			preventPanelLoad: true,
			callback: function(success, data) {
				if (data.perms) {
					$("#" + genid + "_permissions_list").html("");
					$("#" + genid + "_more_users_permissions").hide();
					
					og.userPermissions.removeAllPermissions(genid);
					for (pg_id in data.perms) {
						var p = data.perms[pg_id];
						og.userPermissions.permissionInfo[genid].permissions[pg_id] = p;
						og.userPermissions.drawUserListItem(genid, data.pg_data[pg_id], true);
					}
				}
				
			}
		});
	}
}

og.userPermissions.drawUserListItem = function(genid, item, noclick) {
	var el = document.getElementById(genid + '_pg_' + item.pg_id);
	if (!el) {
		var html = '<li class="user-data" id="'+genid+'_pg_'+item.pg_id+'" onclick="og.userPermissions.showPermissionsPopup(this, \''+genid+'\');">';
		
		if (item.type == 'group') {
			html += '<div class="coViewIconImage ico-large-group"></div>';
		} else if (item.picture_url) {
			html += '<div class="coViewIconImage"><img src="'+item.picture_url+'" alt="'+item.name+'" /></div>';
		} else {
			html += '<div class="coViewIconImage ico-large-contact"></div>';
		}
		
		html +=	'<div class="user-name-container">'+
				'<span id="username_'+ item.pg_id +'" class="bold">'+item.name+'</span>'+
				'<input id="'+genid+'_is_guest_'+ item.pg_id +'" name="is_guest" type="hidden" value="'+item.is_guest+'"/>'+
				'<input id="'+genid+'_user_id_'+ item.pg_id +'" name="user_id" type="hidden" value="'+ (item.user_id ? item.user_id : 0)+'"/>'+
				'<div class="desc">'+(item.company_name ? item.company_name : '')+'</div>'+
				'<div class="desc">'+(item.role ? item.role : '')+'</div>'+
			'</div>'+
			'<div class="clear"></div>'+
		'</li>';
		$("#" + genid + "_permissions_list").append(html);
	}
	
	if (!noclick) {
		$("#" + genid + '_pg_' + item.pg_id).click();
	}
}


og.showHideNonGuestPermissionOptions = function (guest_selected) {
	if (guest_selected) {
		$('.radio_3').hide();
		$('.radio_2').hide();
		$('.radio-title-3').hide();
		$('.radio-title-2').hide();
		$('.perm_all_checkbox_container').hide();
	} else {
		$('.radio_3').show();
		$('.radio_2').show();
		$('.radio-title-3').show();
		$('.radio-title-2').show();
		$('.perm_all_checkbox_container').show();
	}
}

og.afterUserTypeChange = function(genid, type) {
	  
	  if (!og.tmp_role_id) og.tmp_role_id = {};
	  og.tmp_role_id[genid] = type;
	
	  $('#'+genid+'userSystemPermissions :input').attr('checked', false);
	  $('#'+genid+'userModulePermissions :input').attr('checked', false);
	  for(i=0; i< og.userRolesPermissions[type].length;i++){
		  $('#'+genid+'userSystemPermissions :input[name$="sys_perm['+og.userRolesPermissions[type][i]+']"]').attr('checked', true);
	  }
	  for(f=0; f< og.tabs_allowed[type].length;f++){
		  $('#'+genid+og.tabs_allowed[type][f]+' :input').attr('checked', true);
	  }
	  
	  og.userPermissions.enableDisableSystemPermissionsByRole(genid, type);

	  var guest_selected = false;
	  for (j=0; j<og.guest_permission_group_ids.length; j++) {
		  if (type == og.guest_permission_group_ids[j]) {
			  guest_selected = true;
			  break;
		  }
	  }
	  
	  var executive_selected = og.executive_permission_group_ids.indexOf(parseInt(type)) >= 0;
	  if (executive_selected) {
		  $("#"+genid+"_root_permissions").show();
	  } else {
		  $("#"+genid+"_root_permissions").hide();
	  }

	  og.showHideNonGuestPermissionOptions(guest_selected);
	  
	  if (!og.tmp_must_check_member_permissions) og.tmp_must_check_member_permissions = {};
	  og.tmp_must_check_member_permissions[genid] = true;
	  
	  // update user role explanation
	  var hint_text = og.userRoles[type].hint;
	  $("#"+genid+'user_role_explanation').html(hint_text);
};


og.userPermissions.savePermissions = function(genid, member_id) {
	
	if (og.userPermissions.current_pg_id > 0) {
	
		og.userPermissions.ogPermPrepareSendData(genid);
		var hf = document.getElementById(genid + 'hfPermsSend');
		if (hf) {
			var to_send = [];
			var temp_pg_id = og.userPermissions.current_pg_id;
			var perms = Ext.util.JSON.decode(hf.value);
			if (perms.length > 0) {
				for (var i=0; i<perms.length; i++) {
					if (perms[i].pg == temp_pg_id) {
						to_send.push(perms[i]);
					}
				}
			}
			
			var applysub = $("#"+genid+"apply_to_submembers").attr('checked') == 'checked' ? 1 : 0;
			
			if (to_send.length > 0) {
				var post_vars = {
					pg_id: og.userPermissions.current_pg_id,
					member_id: member_id,
					apply_submembers: applysub,
					perms: Ext.util.JSON.encode(to_send)
				};
				if (og.before_send_member_permissions && og.before_send_member_permissions.length > 0) {
					for (var x=0; x<og.before_send_member_permissions.length; x++) {
						var func = og.before_send_member_permissions[x];
						if (typeof(func) == 'function') {
							func.call(null, genid, post_vars);
						}
					}
				}
				
				og.openLink(og.getUrl('member', 'save_permission_group'), {
					preventPanelLoad: true,
					post: post_vars,
					callback: function(success, data) {
						if (success) {
							// mark processed permissions as not modified to avoid sending them again
							var permissions = og.userPermissions.permissionInfo[genid].permissions[temp_pg_id];
							if (permissions.length > 0) {
								for (var i=0; i<permissions.length; i++) {
									if (permissions[i]) permissions[i].modified = false;
								}
								
								// modify the original permissions (making a copy)
								var json = Ext.util.JSON.encode(permissions);
								og.userPermissions.permissionInfo[genid].original_permissions[temp_pg_id] = Ext.util.JSON.decode(json);
							}
						}
					}
				});
			}
		}
	}
}

og.userPermissions.enableDisableSystemPermissionsByRole = function(genid, type) {
	$('#'+genid+'userSystemPermissions :input').prop('disabled', true);
	$('#'+genid+'userSystemPermissions label').css('opacity', '0.7');
	if (og.userMaxRolesPermissions[type]) {
		for(i=0; i<og.userMaxRolesPermissions[type].length; i++){
			$('#'+genid+'userSystemPermissions :input[name$="sys_perm['+og.userMaxRolesPermissions[type][i]+']"]').prop('disabled', false);
			$('#'+genid+'userSystemPermissions label[for$="'+genid+'sys_perm['+og.userMaxRolesPermissions[type][i]+']"]').css('opacity', '1.0');
		}
	}
}