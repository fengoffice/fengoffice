<script>
og.eventManager.addListener('reload company users', function(data){
	og.openLink(og.getUrl('contact', 'reload_company_users', {company:data.company_id, context:og.contextManager.plainContext(), current:data.current}), {
		preventPanelLoad:true,
		callback: function(success, data) {
			document.getElementById('companyUsers').innerHTML = data.current.data;
			og.captureLinks('companyUsers', data.current);
		}
	});
});

og.eventManager.addListener('template object added',function(data){
	if (data.object) {
		og.redrawTemplateObjectsLists(data.object);
	}
});

og.eventManager.addListener('reload member restrictions', 
 	function (genid){ 
		App.modules.addMemberForm.drawDimensionRestrictions(genid, document.getElementById(genid + 'dimension_id').value);
 	}
);

og.eventManager.addListener('current panel back',
	function () {
		var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
		if (currentPanel) {
			currentPanel.back();
		}
	}
);

og.eventManager.addListener('reload current panel',
	function () {
		var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
		if (currentPanel) {
			currentPanel.reload();
		}
	}
);
og.eventManager.addListener('reload tab panel', 
 	function (name){
 		if (name) {
			var el = Ext.getCmp(name);
			if (el) el.reset();
  		}
 	}
);

og.eventManager.addListener('reload user picture', 
 	function (data){
 		var el = document.getElementById(data.el_id);
 		if (el) el.src=data.url;
 		if (data.file_id && data.hf_picture) {
 	 		var hf = document.getElementById(data.hf_picture);
 	 		if (hf) hf.value = data.file_id;
 		}
 	}
);

og.eventManager.addListener('reload member properties', 
 	function (genid){
 		App.modules.addMemberForm.drawDimensionProperties(genid, document.getElementById(genid + 'dimension_id').value);
 	}
);

og.eventManager.addListener('update dimension tree node',
		function (data){
			var tree = Ext.getCmp("dimension-panel-" + data.dim_id);
			if (tree && !tree.hidden){

				var callback_extra_params = {
					dim_id:data.dim_id,
					select_node: data.select_node,
					member_id:data.member_id
				};
				og.getMemberFromServer(data.member_id, og.updateDimensionTreeNode, callback_extra_params);					
			}
		}
);

og.eventManager.addListener('reload dimension tree',
	function (data){
		var tree = Ext.getCmp("dimension-panel-" + data.dim_id);
		if (tree && !tree.hidden){
			if (!og.reloadingDimensions){
				og.reloadingDimensions = {};
			}
			if (!og.reloadingDimensions[data.dim_id]){
				og.reloadingDimensions[data.dim_id] = true;
				setTimeout(function(){
					og.reloadingDimensions[data.dim_id] = false;
				}, 1000);
								
				if (tree) {
					var selection = tree.getSelectionModel().getSelectedNode();
	
					tree.suspendEvents();
					var expanded = [];
					tree.root.cascade(function(){
						if (this.isExpanded()) expanded.push(this.id);
					});
					tree.loader.load(tree.getRootNode(), function() {
						og.reloadingDimensions[data.dim_id] = false;
						tree.expanded_once = false;
						og.expandCollapseDimensionTree(tree, expanded, selection ? selection.id : null);
						if(selection){
							setTimeout(function(){
								if (data.node) {
									var treenode = data.node;
								} else {
									var treenode = selection.id;
								}

								og.memberTreeExternalClick(tree.dimensionCode,treenode);								
							}, 200);
							og.contextManager.addActiveMember(selection.id, data.dim_id, selection.id);
						}
					});
					tree.resumeEvents();
				}
			}
		}
	}
);

og.eventManager.addListener('reset dimension tree', 
 	function (dim_id){
 		if (!og.reloadingDimensions){ 
 			og.reloadingDimensions = {} ;
 		}
 		if (!og.reloadingDimensions[dim_id]){
	 		og.reloadingDimensions[dim_id] = true ;
	 		var tree = Ext.getCmp("dimension-panel-" + dim_id);
	 		if (tree) {
		 		tree.suspendEvents();
 				tree.loader = tree.initialLoader;
		 		tree.loader.load(tree.getRootNode(),function(){
			 		tree.resumeEvents(); 
			 		og.Breadcrumbs.refresh(tree.getRootNode());
			 	});
		 		tree.expandAll();
	 		}
 		}
 	}
);

og.eventManager.addListener('external dimension member click', 
		function (data){
			var tree = Ext.getCmp("dimension-panel-" + data.dim_id);
			og.memberTreeExternalClick(tree.dimensionCode, data.member_id);
		}
);

og.eventManager.addListener('select dimension member', 
	function (data){
		if (og.reloadingDimensions[data.dim_id]) {
		//	og.select_member_after_reload = data;
		} else {
			og.selectDimensionTreeMember(data);
		}
	}
);

og.eventManager.addListener('company added', 
 	function (company) {
 		var elems = document.getElementsByName("contact[company_id]");
 		for (var i=0; i < elems.length; i++) {
 			if (elems[i].tagName == 'SELECT') {
	 			var opt = document.createElement('option');
	        	opt.value = company.id;
		        opt.innerHTML = company.name;
	 			elems[i].appendChild(opt);
 			}
 		}
 	}
);

og.eventManager.addListener('contact added from mail', 
	function (obj) {
		var hf_contacts = document.getElementById(obj.hf_contacts);
		if (hf_contacts) hf_contacts.value += (hf_contacts != '' ? "," : "") + obj.combo_val;
		var div = Ext.get(obj.div_id);
 		if (div) div.remove();
 	}
);

og.eventManager.addListener('draft mail autosaved', 
	function (obj) {
		var hf_id = document.getElementById(obj.hf_id);
		if (hf_id) hf_id.value = obj.id;
 	}
);

og.eventManager.addListener('popup',
	function (args) {
		og.msg(args.title, args.message, 0, args.type, args.sound);
	}
);

og.eventManager.addListener('user preference changed',
	function(option) {
		switch (option.name) {
			case 'localization':
				window.location.reload();
				break;
			default: 
				og.preferences[option.name] = option.value;
				break;
		}
	}
);

og.eventManager.addListener('download document',
	function(args) {
		if(args.reloadDocs){
			//og.openLink(og.getUrl('files', 'list_files'));
			og.panels.documents.reload();
		}	
		location.href = og.getUrl('files', 'download_file', {id: args.id, validate:0});
	}
);

og.eventManager.addListener('config option changed',
	function(option) {
		og.config[option.name] = option.value;
	}
);

og.eventManager.addListener('tabs changed',
	function(option) {
		window.location.href = '<?php echo ROOT_URL?>';
	}
);
og.eventManager.addListener('logo changed',
	function(option) {
		if (og.ownerCompany.id == option.id) {
			window.location.href = '<?php echo ROOT_URL?>';
		}
	}
);
og.eventManager.addListener('expand menu panel',
	function(options) {
		og.expandMenuPanel(options);
	}
);

og.eventManager.addListener('after member save', 
	function (member){
		//add member to og.dimension
		og.addMemberToOgDimensions(member.dimension_id,member);		
		
	}
);

og.eventManager.addListener('try to select member',
	function (member) {
		if (og.resettingAllTrees) return;
		
		var interval = setInterval(function(){
			var tree = Ext.getCmp("dimension-panel-" + member.dimension_id);
			var treenode = tree ? (member.id > 0 ? tree.getNodeById(member.id) : tree.getRootNode()) : null;
			if (treenode) {
				treenode.fireEvent('click', treenode);
				og.Breadcrumbs.refresh(treenode);
				clearInterval(interval);
			}
		}, 1000);
	}
);

og.eventManager.addListener('try to expand member',
	function (member) {
		var interval = setInterval(function(){
			var tree = Ext.getCmp("dimension-panel-" + member.dimension_id);
			var treenode = tree ? tree.getNodeById(member.id) : null;
			if (treenode) {
				treenode.expand();
				clearInterval(interval);
			}
		}, 600);
	}
);


og.eventManager.addListener('select member after add',
	function (member){
		if (og.preferences.access_member_after_add) {
			var tree = Ext.getCmp("dimension-panel-" + member.dimension_id);
			if (tree) {
				setTimeout(function () {
					if (member.parent_id > 0) {
						og.eventManager.fireEvent('try to expand member', {id:member.parent_id, dimension_id:member.dimension_id});
					}
					og.eventManager.fireEvent('try to select member', member);
				}, 1000);
			}
		}
	}
);

og.eventManager.addListener('ask to select member',
	function (member){
		
			if (og.preferences.access_member_after_add_remember == '1') {
	
				if (og.preferences.access_member_after_add) {
					var tree = Ext.getCmp("dimension-panel-" + member.dimension_id);
					if (tree) {
						setTimeout(function () {
							var treenode = tree.getNodeById(member.id);
							if (treenode) {
								treenode.fireEvent('click', treenode);
							} else {
								og.eventManager.fireEvent('try to select member', member);
							}
						}, 500);
					}
				}
				
			} else {
	
				var selected_member_name = member.sel_mem != '' ? member.sel_mem : lang('general view');
				
				var old_yes_text = Ext.MessageBox.buttonText.yes;
				var old_no_text = Ext.MessageBox.buttonText.no;
				Ext.MessageBox.buttonText.yes = lang('access member', '<span class="bold">'+ member.name +'</span>');
				Ext.MessageBox.buttonText.no = lang('stay at', '<span class="bold">'+ selected_member_name +'</span>');
	
				var html = lang('new member added popup msg', '<span class="bold">' + member.type + '</span>', '<span class="bold">' + member.name + '</span>') + '<br />';
				html += lang('what would you like to do next') + '<br /><br />';
				html += '<input type="checkbox" name="remember_after_member_add" id="remember_after_member_add">&nbsp;';
				html += '<label for="remember_after_member_add" style="cursor:pointer;display:inline;font-weight:normal;font-size:100%;margin:0;">' + 
					lang('remember my choice and do not ask again in the future') + '</label><br />';
				html += '<span class="bold">'+ lang('message') +': </span>' + lang('this user option can be changed');
	
				Ext.Msg.show({
					title: lang('new member added popup title', member.type, member.name),
					msg: html,
					buttons: Ext.Msg.YESNO,
					fn: function(button, text){
	
						if (button == 'yes') {
							var tree = Ext.getCmp("dimension-panel-" + member.dimension_id);
							if (tree) {
								var treenode = tree.getNodeById(member.id);
								if (treenode) {
									treenode.fireEvent('click', treenode);
								} else {
									og.eventManager.fireEvent('try to select member', member);
								}
							}
						}
					
						var remember = document.getElementById("remember_after_member_add").checked;
						if (remember) {
							og.openLink(og.getUrl('account', 'update_user_preference', {name:'access_member_after_add_remember', value:'1'}));
							og.openLink(og.getUrl('account', 'update_user_preference', {name:'access_member_after_add', value: button == 'yes' ? '1' : '0'}));
						}
					
					},
					icon: Ext.MessageBox.QUESTION
				});
	
				Ext.MessageBox.buttonText.yes = old_yes_text;
				Ext.MessageBox.buttonText.no = old_no_text;			
			}

			og.expandMenuPanel({expand: true});
	}
);

og.eventManager.addListener('member tree node click',
	function (node) {			
		var interval = setInterval(function(){
			var tree = node.ownerTree;
			var treenode = tree ? tree.getNodeById(node.id) : null;
			if (treenode) {			
				og.Breadcrumbs.refresh(treenode);
				clearInterval(interval);
			}
		}, 700);
	}	
);


og.eventManager.addListener('mark_error_field', 
 	function (data){
 		if (data.field) {
 	 		
 			var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
 			var inputs = $("#" + currentPanel.id + " input");
 			
 	 		if (inputs && inputs.length > 0) {
 	 	 		for (var i=0; i<inputs.length; i++) {
 	 	 			var inp = inputs[i];
 	 	 			var name = $(inp).attr('name');
 	 	 			
 	 	 	 		if (name && name.indexOf("["+data.field+"]") > 0) {
 	 	 	 	 		// add error class to error field
 	 	 	 			$(inp).addClass('field-with-error');
 	 	 	 			// remove error class when writing the input
 	 	 	 			$(inp).keydown(function(){
 	 	 	 	 			$(this).removeClass('field-with-error');
 	 	 	 	 		});

 	 	 	 			// set the input tab visible, if input belongs to a tab
 	 	 	 			var tab = $(inp).closest(".form-tab");
 	 	 	 	 		if ($(tab).length > 0) {
 	 	 	 	 			$(".edit-form-tabs a[href=#"+$(tab).attr('id')+"]").click();
 	 	 	 			}
 	 	 	 	 		
 	 	 	 			break;
 	 	 	 		}
 	 	 		}
 	 	 		
 	 		}
  		}
 	}
);


og.eventManager.addListener('ask to change subtasks dates',
	function (data) {
		var s = data.sd_diff;
		var d = data.dd_diff;

		if (d) {
			var dd_str = (d.days > 0 ? ' '+d.days+' '+lang('days') : '') + (d.hours > 0 ? ', '+d.hours+' '+lang('hours') : '') + 
				(d.mins > 0 ? ', '+d.mins+' '+lang('minutes') : '') + ' '+(d.sign >= 0 ? lang('forward'):lang('backward'));
		}
		if (s) {
			var sd_str = (s.days > 0 ? ' '+s.days+' '+lang('days') : '') + (s.hours > 0 ? ', '+s.hours+' '+lang('hours') : '') + 
				(s.mins > 0 ? ', '+s.mins+' '+lang('minutes') : '') + ' '+(s.sign >= 0 ? lang('forward'):lang('backward'));
		}

		var question = null;
		if (d && s) {
			question = lang('do you want to move subtasks due date X and start date Y', dd_str, sd_str);
		} else if (d) {
			question = lang('do you want to move subtasks due date X', dd_str);
		} else if (s) {
			question = lang('do you want to move subtasks start date X', sd_str);
		}
		if (question) {
			var info = lang('task start or due date has been changed');
			var div = document.createElement('div');
			div.style = "border-radius: 5px; background-color: #fff; padding: 10px; width: 400px;";
			var genid = Ext.id();
			div.innerHTML = '<div><label class="coInputTitle">'+lang('modify subtasks dates')+'</label></div>'+
				'<div id="'+genid+'_question">'+ info + '</br>' + question+'</div>'+
				'<div id="'+genid+'_buttons">'+
				'<button class="yes submit blue">'+lang('yes')+'</button><button class="no submit blue">'+lang('no')+'</button>'+
				'</div><div class="clear"></div>';

			var modal_params = {
				'escClose': false,
				'overlayClose': false,
				'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
				'onShow': function (dialog) {
					$("#"+genid+"_close_link").addClass("modal-close-img");
					$("#"+genid+"_buttons").css('text-align', 'right').css('margin', '10px 0');
					$("#"+genid+"_question").css('margin', '10px 0');
					$("#"+genid+"_buttons button.yes").css('margin-right', '10px').click(function(){
						og.openLink(og.getUrl('task', 'advance_subtasks_dates'), {post: {
							task_id: data.task_id,
							dd_diff: d ? Ext.util.JSON.encode(d) : '',
							sd_diff: s ? Ext.util.JSON.encode(s) : ''
						}});
						$('.modal-close').click();
					});
					$("#"+genid+"_buttons button.no").css('margin-right', '10px').click(function(){
						$('.modal-close').click();
					});
			    }
			};
			setTimeout(function() {
				$.modal(div, modal_params);
			}, 100);
		}
	}
);


og.eventManager.addListener('new user added', 
 	function (data){
 		if (data && data.id > 0) { 
 			og.allUsers[data.id] = data;
 		}
 	}
);


og.eventManager.addListener('scroll to comment', 
 	function (data){
 		if (data && data.comment_id > 0) {
 	 		var interval = setInterval(function() {
	 			var offset = $("#comment"+data.comment_id).offset();
	 			if (offset && offset.top) {
		 			$('.x-panel-body.x-panel-body-noheader').animate({
		 	 			scrollTop: offset.top
		 	 		}, 'slow');
		 	 		clearInterval(interval);
	 			}
 	 		}, 500);
 		}
 	}
);

og.eventManager.addListener('ask to assign default permissions', 
	function (data){
		if (data && data.user_ids.length > 0) {
			var user_names = []; 
			for (var i=0; i<data.user_ids.length; i++) {
				user_names.push(og.allUsers[data.user_ids[i]].name);
			}
			if (confirm(lang('do you want to add default permissions in member for users', data.member.name, user_names.join(', ')))) {
				og.openLink(og.getUrl('member', 'add_default_permissions', {member_id:data.member.id, user_ids:data.user_ids}));
			}
		}
	}
);

og.eventManager.addListener('refresh member list parameters', 
	function (data){
		if (!og.member_list_params) og.member_list_params = {};
		og.member_list_params.object_type_name = data.object_type_name;
		og.member_list_params.object_type_id = data.object_type_id;
		og.member_list_params.dimension_id = data.dimension_id;
		og.member_list_params.dimension_code = data.dimension_code;
	}
);

og.eventManager.addListener('add tasks info to tasks list', 
	function (data) {
		if (data && data.tasks && data.tasks.length > 0) {
			ogTasks.drawTasksRowsAfterAddEdit(data);
		}
	}
);
</script>