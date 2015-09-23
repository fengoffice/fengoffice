	
	App.modules.addMemberForm = {
			
		object_types : [],
		
		assignableParents : [],
		
		buildIdNameComboStore : function(items) {
			var store = [];
			if (items) {
				for (i=0; i<items.length; i++) {
					store[store.length] = [items[i].id, items[i].name];
				}
			}
			return store;
		},
		
		updateParentsSelector : function(genid, dimension_id, object_type_id) {
			var parents_container =	document.getElementById(genid+"memberParentContainer");
			og.openLink(og.getUrl('member', 'get_assignable_parents', {dim:dimension_id, otype:object_type_id}), {
				hideLoading: true,
				callback: function (success, data) {
					var show_parents = false;
					if (data.parents.length > 0) {
						if (data.parents[0].id == 0) {
							show_parents = data.parents.length > 1;
						}
						else show_parents = true;
					}
					if (parents_container) {
						if (show_parents) {
							parents_container.style.display = "block"
						}else{
							parents_container.style.display = "none";
							document.getElementById(genid+"memberParent").value ="";
						}
					}
				}
			});
		},
		
		/**
		 * @deprecated
		 */
		deprecated_updateAssignableParents : function(genid, dimension_id, object_type_id) {

			var self = this ;
			og.openLink(og.getUrl('member', 'get_assignable_parents', {dim:dimension_id, otype:object_type_id}), {
				hideLoading: true,
				callback: function (success, data) {
					self.assignableParents = data.parents;
				}
			});
		},

		drawObjectTypesSelectBox : function(genid, obj_types, container_div, obj_type_hf, selected, disabled) {
			this.object_types = obj_types;
			if (!selected) selected = 0;
			store = App.modules.addMemberForm.buildIdNameComboStore(obj_types);
			var combo = og.drawComboBox({render_to:genid + container_div, id:genid + 'memberObjectTypesCombo', name:'memberObjectTypesCombo', selected:selected, store:store, disabled:disabled});
			combo.on('select', function() {
				var hf = document.getElementById(genid + obj_type_hf);
				if (hf) hf.value = this.getValue();

				App.modules.addMemberForm.objectTypeChanged(this.value, genid);
			});
			if (selected) {
				hf = document.getElementById(genid + obj_type_hf);
				if (hf) hf.value = selected;
			}
		},

		objectTypeChanged: function(selected, genid, dont_update_parents) {
			var ot = null;
			for (var i=0; i<this.object_types.length; i++) {
				if (this.object_types[i].id == selected) {
					ot = this.object_types[i];
					break;
				}
			}
			if (ot == null) return;
			
			if (!dont_update_parents)
				this.updateParentsSelector(genid, document.getElementById(genid + 'dimension_id').value, ot.id);


			if (ot.type == 'dimension_object') {
				var mem_el = Ext.get(genid + 'member_id');
				var mem_id = 0;
				if (mem_el) mem_id = mem_el.dom.value;
				var parent_el = document.getElementById(genid + 'memberParent');
				var parent_id = parent_el ? parent_el.value : 0;
				og.openLink(og.getUrl('member', 'get_dimension_object_fields', {id: ot.id, mem_id:mem_id, parent_id:parent_id}), {
					callback: function(success, data) {
						if (success) {
							App.modules.addMemberForm.renderDimensionObjectFields(data.fields, data.title);
						}
					}
				});
				
			} else if (ot.type == 'dimension_group') {
				var container = document.getElementById(genid + 'dimension_object_fields');
				if (container) container.innerHTML = '';
			}
			
			if (og.dimRestrictions.ot_with_restrictions && og.dimRestrictions.ot_with_restrictions[ot.id]) {
				var del_res_link = document.getElementById(genid + 'delRestrictionsLink');
				if (del_res_link.style.display != 'none') {
					this.drawDimensionRestrictions(genid, document.getElementById(genid + 'dimension_id').value);
				}
				Ext.get(genid + 'restriction_links').setDisplayed(true);
			} else {
				Ext.get(genid + 'restriction_links').setDisplayed(false);
				var restrictions_container = Ext.get(genid + 'dimension_restrictions');
				if (restrictions_container) restrictions_container.dom.innerHTML = "";
			}
			
			if (!og.ot_hide_vinculations_link || !og.ot_hide_vinculations_link[ot.id]) {
				if (og.dimProperties.ot_with_properties && og.dimProperties.ot_with_properties[ot.id]) {
					var del_prop_link = document.getElementById(genid + 'delPropertiesLink');
					if (del_prop_link.style.display != 'none') {
						this.drawDimensionProperties(genid, document.getElementById(genid + 'dimension_id').value);
					}
				//	Ext.get(genid + 'property_links').setDisplayed(true);
				} else {
					Ext.get(genid + 'property_links').setDisplayed(false);
					var properties_container = Ext.get(genid + 'dimension_properties');
					if (properties_container) properties_container.dom.innerHTML = "";
				}
			}
		},

		renderDimensionObjectFields: function(fields, obj_type_name) {
			if (!fields || fields.length == 0) {
				return;
			}
			var container = document.getElementById(genid + 'dimension_object_fields');
			var js = '';
			var html = '';
			//html += '<fieldset>';
			//html += '<legend>';
			//html += '<span class="og-task-expander toggle_expanded" style="padding-left:20px;" title="'+ lang('expand-collapse') +'" id="'+genid+'expander-memberfields" onclick="og.editMembers.expandCollapseDim(\''+genid+'dimension-memberfields\', false);">';
			//html += lang('member fields', obj_type_name) + '</span></legend>';
			html += '<div id="'+genid+'dimension-memberfields" class="dimension-memberfields" >';
			for (var i=0; i<fields.length; i++) {
				var f = fields[i];
				html += '<div class="member-field type-'+f.type.toLowerCase()+'">';
				html += '<label for="'+ genid + 'dim_obj_' + f.col +'">'+ (f.col_lang ? f.col_lang : lang(f.col));
				if (f.mandatory) html += '&nbsp;<span class="label_required">*</span>';
				html += '</label>';
				switch (f.type) {
					case 'STRING':
						if (f.large)
							html += '<textarea name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'" class="long">'+(f.val ? f.val : '')+'</textarea>';
						else 
							html += '<input name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'" type="text" value="'+(f.val ? f.val : '')+'" />';
						break;
					case 'DATE':
					case 'DATETIME':
						html += '<div id="'+ genid + 'dim_obj_' + f.col +'_container"></div>';
						js += "var " + f.col + genid + " = new og.DateField({\n"+
							"renderTo: '" + genid + "dim_obj_" + f.col + "_container',\n"+
							"id: '" + genid + "dim_obj_" + f.col + "',\n"+
							"name: 'dim_obj[" + f.col + "]',\n"+
							"style:'width:100px',\n"+
							"value:'"+(f.val ? f.val : '')+"'\n"+
						"});\n";
						break;
					case 'INTEGER':
						if (!f.fixed_values) {
							html += '<input name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'" type="text" class="short" value="'+(f.val ? f.val : '')+'" ' + 
								'onblur="App.modules.addMemberForm.checkNumericField(this, \''+f.col_lang+'\');"/>';
						} else {
							html += '<select name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'">';
							for (k in f.fixed_values) {
								var selected = f.val ? f.val == k : (f.fixed_values[k]=='default');
								html += '<option value="' + k + '"'+ (selected ? ' selected="selected"' : '' ) +'>' + (f.use_langs ? lang(f.lang_pre + f.col + k) : k) + '</option>';
							}
							html += '</select>';
						}
						break;
					case 'BOOLEAN':
						html += '<input name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'" type="checkbox" class="checkbox" '+(f.val ? ' checked' : '')+' />';
						break;
					case 'WSCOLOR':
						html += '<input name="dim_obj[' + f.col + ']" id="'+ genid + 'dim_obj_' + f.col +'" class="color-code" type="hidden" value="'+f.val+'" />';
						html += "<div class='ws-color-chooser'>";
						for (var i=0; i<=24; i++) {
							var cls = (f.val == i)?'selected':'';
							html += "<div  class='ico-color"+i+ " "+ cls + " color-cell'  onClick='$(\"input.color-code\").val(\""+i+"\");$(\".color-cell\").removeClass(\"selected\");$(this).addClass(\"selected\");'></div>";
							if (i==12) {
								html+=	'<div class="x-clear"></div><div style="width:20px;float:left;height:10px;"></div>';
							}
						}
						html+=	'<div class="x-clear"></div>';
						html+=	'</div>';
						break;
					default: break;
				}
				html += '</div>';
			}
			html += '</div>' ;
			//html += '</fieldset>';
			
			container.innerHTML = html;
			eval(js);
			container.style.display = 'block';
		},

		checkNumericField: function(field, field_name) {
			if(isNaN(field.value)) {
				alert(lang('must be numeric', field_name));
				field.focus();
			}
		},

		drawDimensionRestrictions: function(genid, dimension_id) {
			var otype = Ext.get(genid + 'memberObjectType');
			if (otype && otype.dom.value == 0) {
				alert(lang('you must select object type to show the possible restrictions'));
				return;
			}
			var mem_el = Ext.get(genid + 'member_id');
			var mem_id = 0;
			if (mem_el) mem_id = mem_el.dom.value;

			og.openLink(og.getUrl('member', 'get_dimensions_for_restrictions', {id:dimension_id, otype:otype.dom.value, genid:genid, mem_id:mem_id}), {
				preventPanelLoad: true,
				callback: function(success, data) {
					if (success) {
						og.dimRestrictions.clearOrders();
						var container = Ext.get(genid + 'dimension_restrictions');
						container.setDisplayed(false);
						container.dom.innerHTML = data.current.data;
						container.slideIn('t', {useDisplay:true});

						for (var i=0; i<data.ord_members.length; i++) {
							og.dimRestrictions.orderableMembers.push(data.ord_members[i]);
						}
						if (data.actual_order) {
							for (var i=0; i<data.actual_order.length; i++) {
								og.dimRestrictions.addMemberToOrderList(genid, data.actual_order[i].dim, data.actual_order[i].mem, data.actual_order[i].parent);
							}
						}
						for (var i=0; i<data.childs.length; i++) {
							og.dimRestrictions.childs.push(data.childs[i]);
						}
						og.dimRestrictions.init(genid);
						
						var to_hide = Ext.get(genid + 'addRestrictionsLink');
						if (to_hide) to_hide.setDisplayed(false);
						var to_show = Ext.get(genid + 'delRestrictionsLink');
						if (to_show) to_show.setDisplayed(true);
					}
				}
			});
		},

		deleteDimensionRestrictions: function(genid) {
			var to_remove = Ext.get(genid + 'dimension_restrictions');
			if (to_remove) to_remove.dom.innerHTML = '';
			var to_hide = Ext.get(genid + 'delRestrictionsLink');
			if (to_hide) to_hide.setDisplayed(false);
			var to_show = Ext.get(genid + 'addRestrictionsLink');
			if (to_show) to_show.setDisplayed(true);
		},
		
		drawDimensionProperties: function(genid, dimension_id) {
			var otype = Ext.get(genid + 'memberObjectType');
			if (otype && otype.dom.value == 0) {
				alert(lang('you must select object type to show the possible properties'));
				return;
			}
			var mem_el = Ext.get(genid + 'member_id');
			var mem_id = 0;
			if (mem_el) mem_id = mem_el.dom.value;

			var parent_id = Ext.get(genid + 'memberParent').dom.value;
				
			og.openLink(og.getUrl('member', 'get_dimensions_for_properties', {id:dimension_id, otype:otype.dom.value, genid:genid, mem_id:mem_id, parent:parent_id}), {
				preventPanelLoad: true,
				callback: function(success, data) {
					if (success) {
						og.dimRestrictions.clearOrders();
						var container = Ext.get(genid + 'dimension_properties');
						container.setDisplayed(false);
						container.dom.innerHTML = data.current.data;
						container.slideIn('t', {useDisplay:true});
						
						og.dimProperties.propertyParents = data.parents;
						og.dimProperties.disableChildProperties(data.genid);
						
						var to_hide = Ext.get(genid + 'addPropertiesLink');
						if (to_hide) to_hide.setDisplayed(false);
						var to_show = Ext.get(genid + 'delPropertiesLink');
						if (to_show) to_show.setDisplayed(true);
					}
				}
			});
		},

		deleteDimensionProperties: function(genid) {
			var to_remove = Ext.get(genid + 'dimension_properties');
			if (to_remove) to_remove.dom.innerHTML = '';
			var to_hide = Ext.get(genid + 'delPropertiesLink');
			if (to_hide) to_hide.setDisplayed(false);
			var to_show = Ext.get(genid + 'addPropertiesLink');
			if (to_show) to_show.setDisplayed(true);
		}
	};

	

	og.dimRestrictions = {};
	og.dimRestrictions.orders = [];
	og.dimRestrictions.orderableMembers = [];
	og.dimRestrictions.childs = [];

	og.dimRestrictions.clearOrders = function() {
		og.dimRestrictions.orders = [];
		og.dimRestrictions.orderableMembers = [];
	}

	og.dimRestrictions.addMemberToOrderList = function(genid, dim, mem, parent) {
		if (!this.isOrderable(mem)) return;
		var add = false;
		var dorder = this.getDimensionOrders(dim, parent);
		if (!dorder) {
			dorder = [];
			add = true;
		}
		dorder.push(mem);
		if (add) og.dimRestrictions.orders.push({dim: dim, parent: parent, members: dorder});
		
		var el = Ext.get(genid + 'order_' + dim + '_' + mem);
		if (el) el.dom.innerHTML = dorder.length;
		el = Ext.get(genid + 'order_num_' + dim + '_' + mem)
		if (el) el.dom.value = dorder.length;
	}

	og.dimRestrictions.delMemberOfOrderList = function(genid, dim, mem, parent) {
		if (!this.isOrderable(mem)) return;
		var dorder = this.getDimensionOrders(dim, parent);
		if (!dorder) return;
		var new_dorder = [];
		for (var i=0; i<dorder.length; i++) {
			if(dorder[i] != mem) {
				new_dorder.push(dorder[i]);
			}
		}
		this.setDimensionOrder(dim, parent, new_dorder);
		this.redrawDimensionOrders(genid, dim, parent);
		Ext.get(genid + 'order_' + dim + '_' + mem).dom.innerHTML = '';
		Ext.get(genid + 'order_num_' + dim + '_' + mem).dom.value = 0;
	}

	og.dimRestrictions.redrawDimensionOrders = function(genid, dim, parent) {
		var order = this.getDimensionOrders(dim, parent);
		for (var i=0; i<order.length; i++) {
			Ext.get(genid + 'order_' + dim + '_' + order[i]).dom.innerHTML = (i+1);
			Ext.get(genid + 'order_num_' + dim + '_' + order[i]).dom.value = (i+1);
		}
	}

	og.dimRestrictions.getDimensionOrders = function(dim, parent) {
		for (var i=0; i<og.dimRestrictions.orders.length; i++) {
			if (og.dimRestrictions.orders[i].dim == dim && og.dimRestrictions.orders[i].parent == parent) {
				return og.dimRestrictions.orders[i].members;
			}
		}
		return null;
	}

	og.dimRestrictions.setDimensionOrder = function(dim, parent, new_order) {
		for (var i=0; i<og.dimRestrictions.orders.length; i++) {
			if (og.dimRestrictions.orders[i].dim == dim && og.dimRestrictions.orders[i].parent == parent) {
				og.dimRestrictions.orders[i] = {dim: dim, parent:parent, members:new_order};
				return;
			}
		}
	}

	og.dimRestrictions.move = function(genid, dim, mem, parent, dir) {
		if (!this.isOrderable(mem)) return;
		if (dir < 0) dir = -1;
		else dir = 1;
		var order = this.getDimensionOrders(dim, parent);
		for (var i=0; i<order.length; i++) {
			if (order[i] == mem) {
				var swap_with = -1;
				if ((dir > 0 && i < order.length - 1) || (dir < 0 && i > 0)) swap_with = i + dir;
				if (swap_with > -1) {
					var tmp = order[i];
					order[i] = order[swap_with];
					order[swap_with] = tmp;
				}
				break;
			}
		}
		this.redrawDimensionOrders(genid, dim, parent);
	}

	og.dimRestrictions.isOrderable = function(mem) {
		for (var i=0; i<og.dimRestrictions.orderableMembers.length; i++) {
			if (mem == og.dimRestrictions.orderableMembers[i]) return true;
		}
		return false;
	}

	og.dimRestrictions.getChildMembers = function(parent) {
		for (var i=0; i<og.dimRestrictions.childs.length; i++) {
			if (parent == og.dimRestrictions.childs[i].p) {
				return og.dimRestrictions.childs[i].ch;
			}
		}
	}

	og.dimRestrictions.enableDisableChilds = function(genid, dim, parent, enable) {
		var childs = this.getChildMembers(parent);
		for (var i=0; i<childs.length; i++) {
			var name = Ext.get(genid + 'name_' + dim + '_' + childs[i]);
			if (name) {
				name.setStyle('color', enable ? '#333' : '#AAA');
			}
			var check_res = Ext.get(genid + 'restricted_members_' + dim + '_' + childs[i]);
			if (check_res) {
				check_res.dom.disabled = !enable;
			}
			if (!enable) {
				this.delMemberOfOrderList(genid, dim, childs[i], parent);
				if (check_res) 
					check_res.dom.checked = '';
				var order_ctrls = Ext.get(genid + 'order_controls_' + dim + '_' + childs[i]);
				if (order_ctrls) {
					order_ctrls.setDisplayed(enable);
				}
			}
		}
	}

	og.dimRestrictions.showHide = function(el_id, show) {
		var el = Ext.get(el_id);
		if (el) el.setDisplayed(show);
	}

	og.dimRestrictions.init = function(genid) {
		for (var i=0; i<this.childs.length; i++) {
			var check_res = Ext.get(genid + 'restricted_members_' + this.childs[i].d + '_' + this.childs[i].p);
			this.enableDisableChilds(genid, this.childs[i].d, this.childs[i].p, check_res && check_res.dom.checked);
		}
	}
	


	og.dimProperties = {};
	og.dimProperties.uncheckOtherMembers = function(checkbox, table_id) {
		if (!checkbox) return;
		if (checkbox.checked) {
			var table = Ext.get(table_id).dom;
			var other_checkboxes = table.getElementsByTagName('input');
			for (var i=0; i<other_checkboxes.length; i++) {
				if (other_checkboxes[i].id != checkbox.id) other_checkboxes[i].checked = false;
			}
		}
	}
	
	og.dimProperties.disableChildProperties = function(genid, dim_id) {
		for (dim in og.dimProperties.propertyParents) {
			if (dim_id && dim_id != dim) continue;
			for (mem_id in og.dimProperties.propertyParents[dim]) {
				var parent_id = og.dimProperties.propertyParents[dim][mem_id];
				if (typeof(parent_id) == 'function') continue;
				
				var parent_input = Ext.get(genid + 'associated_members_' + dim + '_' + parent_id);
				var checkbox = Ext.get(genid + 'associated_members_' + dim + '_' + mem_id);

				if (checkbox) {
					checkbox.dom.disabled = !parent_input.dom.checked;
					if (checkbox.dom.disabled) checkbox.dom.checked = false;
				}

				var name = Ext.get(genid + 'name_' + dim + '_' + mem_id);
				if (name) name.setStyle('color', checkbox.dom.disabled ? '#AAA' : '#333');
			}
		}
	}