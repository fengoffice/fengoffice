

og.SimpleMemberSelector = function(config) {

	var mode = 'remote';
	if (og.simple_member_selectors && og.simple_member_selectors.store_cache && og.simple_member_selectors.store_cache[config.dimensionId]) {
		mode = 'local';
	}

	if (mode == 'local') {
		// all members of the dimension are preloaded so we can use them without requests to the server
		var store = new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : config.options ? config.options : []
		});
		var value_field = 'value';

	} else {
		// the dimension is too big to have all the members in cache, so we have to use remote queries
		var parameters = {
			genid: config.genid,
			dimension_id: config.dimensionId,
		};

		var mem_id = og.getMemberIdByDimension(config.dimensionId, "customer_project");
		if (mem_id !== undefined) {
			parameters.id = mem_id;
		}

		var store = new Ext.data.Store({
			proxy: new Ext.data.HttpProxy({
				method: "GET",
				url: og.makeAjaxUrl(og.getUrl('dimension', 'quick_add_row_members_tree', parameters))
			}),
			reader: new Ext.data.JsonReader({
				root: "dimension_members",
				fields: ['id', 'text'],
				//fields: [{name: "id"},{name: "text"}]
			})
		});
		var value_field = 'id';
	}


	var uid = Ext.id();
	var combo_config = {
		renderTo: config.renderTo,
		id: config.id,
		genid: config.genid,
		name: config.name,
		store: store,
		displayField: 'text',
		mode: mode,
		triggerAction: 'all',
		selectOnFocus: true,
		valueField: value_field,
		valueNotFoundText: ''
	};
	if (config.tabIndex) {
		combo_config.tabIndex = config.tabIndex;
	}

	if (!config.listeners) config.listeners = {};

	// merge fixed config with config parameter
	Ext.applyIf(config, combo_config);
	
	// create the component
	og.SimpleMemberSelector.superclass.constructor.call(this, config);

	this.on({
		select: function(combo, record, index) {
			// clean text of the input to show, remove html tags
			$(combo.el.dom).val(Ext.util.Format.htmlDecode(og.removeTags($(combo.el.dom).val())));
			if (!combo.dont_select_associated_member) {
				this.selectAssociatedMembers();
			}

			// additional specific function to call after a value is selected
			if (combo.onselect_fn && typeof combo.onselect_fn == 'function') {
				combo.onselect_fn.call(null, combo);
			}
		}
	});


	if (!og.simple_member_selectors) {
		og.simple_member_selectors = {
			store_cache: {}
		};
	}
	if (!og.simple_member_selectors[config.genid]) {
		og.simple_member_selectors[config.genid] = {};
	}
	og.simple_member_selectors[config.genid][config.dimensionId] = this;

	this.init();

}

og.getMemberIdByDimension = function (dimensionId, dimensionCode) {
    var mem_id;
    
    if (og.dimensions_info[dimensionId].code == dimensionCode) {
        var members = og.contextManager.getDimensionMembers(dimensionId);
        if (members.length > 1) {
            mem_id = members[1];
        }
    }

    return mem_id;
}

og.buildPlainStoreFromTree = function (tree_members, store_data) {
	for (var i = 0; i < tree_members.length; i++) {
		var member = tree_members[i];
		if (member) {
			var mem_name = '<span style="margin-left:' + (20 * (member.depth - 1)) + 'px">' + member.display_name + '</span>';

			store_data.push([member.id, mem_name]);

			og.addMemberToOgDimensions(member.dimension_id, member);

			if (!member.leaf) {
				og.buildPlainStoreFromTree(member.children, store_data);
			}
		}
	}
}

Ext.extend(og.SimpleMemberSelector, Ext.form.ComboBox, {

	init: function() {
		if (this.mode == 'local') {

			var use_store_cache = false;
			
			// if cache is older than 1 hours then don't use it and refresh it
			var cache_date_to_check = new Date();
			cache_date_to_check.setHours(cache_date_to_check.getHours() - 1);
			
			if (og.simple_member_selectors.store_cache[this.dimensionId] && 
				og.simple_member_selectors.store_cache[this.dimensionId].date.getTime() > cache_date_to_check.getTime()) {
				
				use_store_cache = true;
			}
	
			// use cache, don't go to server
			if (use_store_cache && og.simple_member_selectors.store_cache[this.dimensionId].store) {
	
				// apply the cached store data
				var store_data = og.simple_member_selectors.store_cache[this.dimensionId].store;
				this.store.loadData(store_data);
	
				// if we are filtering by a member of this dimension then autocomplete this selector with it
				if (this.select_current_context) {
					this.selectCurrentContext();
				}
	
			} else {
				var parameters = {
					genid: this.genid,
					dimension_id: this.dimensionId,
				};

				var mem_id = og.getMemberIdByDimension(this.dimensionId, "customer_project");
				if (mem_id !== undefined) {
					parameters.id = mem_id;
				}

				// get members from server
				og.openLink(og.getUrl('dimension', 'quick_add_row_members_tree', parameters), {
					hideLoading:true,
					hideErrors:true,
					callback: function(success, data){					
						if (!success || !data) return;
	
						var selector = og.simple_member_selectors[data.genid][data.dimension_id];
		
						// format the store
						store_data = [[0, lang('none')]];
						og.buildPlainStoreFromTree(data.dimension_members, store_data);
						// load the store data
						selector.store.loadData(store_data);
		
						// store cache data for some time to avoid making so much queries to the server when redrawing quick add row
						og.simple_member_selectors.store_cache[data.dimension_id] = {
							store: store_data,
							date: new Date(),
						}
		
						// if we are filtering by a member of this dimension then autocomplete this selector with it
						if (selector.select_current_context) {
							selector.selectCurrentContext();
						}
		
					}
				});
			}
		} else {

			if (this.select_current_context) {
				this.selectCurrentContext();
			}
		}

	},

	selectMember: function(mem_id) {
		if (this.getValue() == mem_id) return;

		if (this.mode == 'local') {

			this.setValue(mem_id);
	
			if (mem_id > 0) {
				var idx = this.store.find(this.valueField, mem_id);
				var r = this.store.getAt(idx);
				this.fireEvent('select', this, r, idx);
			}

		} else {

			var options = {
				params: {
					id: mem_id
				}, 
				callback: function(record, options, success) {
					if (record.length > 0) {
						let member_id = record[0].data.id;
						this.setValue(member_id);

						if (member_id > 0) {
							var idx = this.store.find(this.valueField, member_id);
							var r = this.store.getAt(idx);
							this.fireEvent('select', this, r, idx);
						}
					}
				},
				scope: this
			};
			this.store.load(options);
		}
	},

	selectCurrentContext: function() {
		
		var sel_members = og.contextManager.getDimensionMembers(this.dimensionId);
		if (sel_members.length > 1) {
			var mem_id = sel_members[1];
			this.selectMember(mem_id);
		}

	},

	selectAssociatedMembers: function() {

		var genid = this.genid;
		var dimension_id = this.dimensionId;
		var member_id = this.getValue();

		if (isNaN(member_id) || member_id == 0) return;
	
		og.openLink(og.getUrl('dimension', 'get_all_associated_members', {member_id: member_id, dimension_id: dimension_id, genid: genid}), {
			hideLoading: true,
			callback: function(success, response_data) {
				if (!response_data) return;
	
				var d_associations = [];
				for (var ot in og.dimension_member_associations[response_data.dimension_id]) {
					for (var j=0; j<og.dimension_member_associations[response_data.dimension_id][ot].length; j++) {
						var association = og.dimension_member_associations[response_data.dimension_id][ot][j];
						if (association && !association.is_reverse) {
							d_associations.push(association);
						}
					}
				}
	
				let processed_dimension_ids = [];
	
				for (var i=0; i<d_associations.length; i++) {
					var assoc = d_associations[i];
		  
					if (assoc && assoc.allows_default_selection) {
	
						// don't override the same dimension with anohter association (e.g: don't override client with billing client)
						if (processed_dimension_ids.indexOf(assoc.assoc_dimension_id) >= 0) continue;
						processed_dimension_ids.push(assoc.assoc_dimension_id);
						
						var data = response_data.associations_data[assoc.id];
						if (!data) continue;

						if (og.simple_member_selectors[data.genid] && og.simple_member_selectors[data.genid][data.dimension_id]) {
							var assoc_selector = og.simple_member_selectors[data.genid][data.dimension_id];
							if (assoc_selector) {
								
								if (data.member_ids.length > 0) {
									assoc_selector.dont_select_associated_member = true;
									
									assoc_selector.selectMember(data.member_ids[0], true);
									
									assoc_selector.dont_select_associated_member = false;
								} else {
									assoc_selector.selectMember(0);
								}
							}
						}
						
					}
				}
			}
		});
	}
	
});


Ext.reg('simple-member-selector', og.SimpleMemberSelector);
