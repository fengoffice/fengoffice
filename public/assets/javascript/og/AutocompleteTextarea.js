og.render_autocomplete_field = function(config) {
	if (!config) config = {};
	
	var comp_id = config.id ? config.id : Ext.id();
	var render_to = config.render_to ? config.render_to : 'autocomplete_textarea';
	var name = config.name ? config.name : "";
	var initial_val = config.value ? config.value : "";
	var height = config.height ? config.height : 22;
	var width = config.width ? config.width : '100%';
	var store = config.store ? config.store : [];
	var grow_max = config.grow_max ? config.grow_max : 40;
	var grow_min = config.grow_min ? config.grow_min : 22;
	var limit = config.limit ? config.limit : 1000;
	
	var query_server = store.length == 0;
	
	var textarea = new Ext.form.TextArea({
		renderTo: render_to,
		id: 'auto_complete_input_' + comp_id,
		name: name,
		value: initial_val,
		grow: true,
		height: height,
		width: width,
		growMax: grow_max,
		growMin: grow_min,
		style: {padding: '2px 2px 0'},
		enableKeyEvents: true,
		store: store,
		listeners: {
			'autosize': function(comp, width) {
				var list = Ext.get('auto_complete_list_'+comp_id)
				if (list) list.setStyle('top', (Ext.get(render_to).getTop() + comp.lastHeight)+'px');
			},
			'keyup': function(comp, e) {
				if (e.keyCode == e.DOWN || e.keyCode == e.UP) {
					comp.moveInList((e.keyCode == e.DOWN ? 1 : -1));
					return;
				}
				if (e.keyCode == e.ENTER) {
					if (comp.selectedItem != -1) {
						comp.selectListElement(comp.selectedItem);
						comp.selectedItem = -1;
					}
					return;
				}
				if (e.keyCode == e.ESC) {
					this.showHideDropDown(false);
					return;
				}
				
				var value = comp.getValue();
				var lastComma = value.lastIndexOf(",");
				if (lastComma >= 0) {
					value = value.substring(lastComma + 1).replace(/^\s*|\s*$/g,"");
				}
				if (value.length > 0) {
					var to_draw = [];
					var k=0;
					if (!query_server) {
						while (k < comp.store.length && to_draw.length < limit) {
							var stv = comp.store[k++];
							if (stv.toLowerCase().indexOf(value.toLowerCase()) >= 0) to_draw[to_draw.length] = stv;
						}
						comp.selectedItem = 0;
						comp.drawDropDown(to_draw);
						comp.showHideDropDown(to_draw.length > 0);
						comp.setSelectedItem(comp.selectedItem);
					
					} else {
						og.tmp_autocomplete_filter_value = value;
						if (comp.tout) clearTimeout(comp.tout);
						comp.tout = setTimeout(function(){
							value = og.tmp_autocomplete_filter_value;
							if (value == comp.last_value) {
								return;
							}
							comp.last_value = value;
							if (value.length > 2) {
								og.openLink(og.getUrl('mail', 'get_allowed_addresses'), {
									post: {name_filter: value},
									callback: function(success, data) {
										comp.store = data.addresses;
		
										while (k < comp.store.length && to_draw.length < limit) {
											var stv = comp.store[k++];
											if (stv.toLowerCase().indexOf(value.toLowerCase()) >= 0) to_draw[to_draw.length] = stv;
										}
										comp.selectedItem = 0;
										comp.drawDropDown(to_draw);
										comp.showHideDropDown(to_draw.length > 0);
										comp.setSelectedItem(comp.selectedItem);
										comp.tout = null;
									}
								});
							} else {
								comp.store = [];
								comp.showHideDropDown(false);
							}
						}, 500);
					}
					
				} else {
					comp.showHideDropDown(false);
				}
			},
			'blur': function(comp) {
				comp.showHideDropDown(false);
			}
		},
		drawDropDown: function(elements) {
			this.current_elements = elements;
			var list_height = 20 * elements.length;
			var list_container = Ext.get('auto_complete_list_'+comp_id);
			if (!list_container) {
				var container = Ext.get(render_to);
				var left = container.getLeft();
				var top = container.getTop() + container.getHeight() - 2;
				
				var html = '<div class="x-layer x-combo-list " id="auto_complete_list_'+comp_id+'" style="position: fixed; z-index: 11000; visibility: visible; left: '+left+'px; top: '+top+'px; width: '+container.getWidth()+'px; height: '+list_height+'px;">';
				html += '<div class="x-combo-list-inner" id="auto_complete_list_inner_'+comp_id+'" style="width: 100%; overflow: auto; height: '+list_height+'px;">';
				html += '</div></div>';
				
				container.insertHtml('beforeEnd', html);
				list_container = Ext.get('auto_complete_list_'+comp_id);
			} else {
				list_container.setStyle('height', list_height+'px');
			}
			var list_inner_cont = Ext.get('auto_complete_list_inner_'+comp_id);
			list_inner_cont.setStyle('height', list_height+'px');
			list_inner_cont.dom.innerHTML = "";
			var k=0;
			while (k < elements.length) {
				var onover = 'Ext.getCmp(\'auto_complete_input_'+comp_id+'\').setSelectedItem('+k+');';
				var html = '<div class="x-combo-list-item " onmouseover="'+onover+'" id="auto_complete_list_item_'+comp_id+'_'+k+'" ';
				html += 'onmousedown="acinput=Ext.getCmp(\'auto_complete_input_'+comp_id+'\');acinput.selectListElement('+k+');acinput.focus(false,true);">'+og.clean(elements[k])+'</div>';
				 
				list_inner_cont.dom.innerHTML += html;
				k++;
			}
		},
		showHideDropDown: function(show) {
			var visibility = 'hidden';
			if (show) visibility = 'visible';
			var list_container = Ext.get('auto_complete_list_'+comp_id);
			if (list_container) list_container.setStyle('visibility', visibility);
		},
		selectListElement: function(element) {
			element = this.current_elements[element];
			this.showHideDropDown(false);
			if (!element) return;
			var value = this.getValue();
			var lastComma = value.lastIndexOf(",");
			if (lastComma >= 0)
				value = value.substring(0, lastComma+1) + " ";
			else value = "";
			value += Ext.util.Format.htmlDecode(element) + ", ";
			this.setValue(value);
		},
		setSelectedItem: function(index) {
			if (this.selectedItem != -1) {
				var item = Ext.get('auto_complete_list_item_'+comp_id+'_'+this.selectedItem);
				if (item) item.dom.className = "x-combo-list-item";
			}
			this.selectedItem = index;
			var item = Ext.get('auto_complete_list_item_'+comp_id+'_'+index);
			if (item) item.dom.className+=' x-combo-selected';
		},
		moveInList: function(dir) {
			var pos = this.selectedItem + dir;
			if (pos >= 0 && pos < this.current_elements.length) {
				this.setSelectedItem(pos);
			}
		}
	});
}