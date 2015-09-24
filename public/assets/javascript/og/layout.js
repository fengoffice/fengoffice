Ext.onReady(function(){

	Ext.get("loading").hide();
		
	// fix cursor not showing on message boxs
	Ext.MessageBox.getDialog().on("show", function(d) {
		var div = Ext.get(d.el);
		div.setStyle("overflow", "auto");
		var text = div.select(".ext-mb-textarea", true);
		if (!text.item(0))
			text = div.select(".ext-mb-text", true);
		if (text.item(0))
			text.item(0).dom.select();
	});

	if (og.preferences['rememberGUIState']) {
		Ext.state.Manager.setProvider(new og.HttpProvider({
			saveUrl: og.getUrl('gui', 'save_state'),
			readUrl: og.getUrl('gui', 'read_state'),
			autoRead: false
		}));
		Ext.state.Manager.getProvider().initState(og.initialGUIState);
	}
	
	Ext.QuickTips.init();
	
	//LOAD PANELS AND ADD TO VIEWPORT
	
	og.openLink(og.getUrl('panel', 'list_all'), {
		
		onSuccess: function(data) {
			var panelData = data['panels'] ;
			og.panels = {} ; // Array Map PANEL_NAME => PANEL
			
			var panels = [] ; // Array of PANELS ( backguard compatibiliy )
			for (var i = 0 ; i < panelData.length ; i++ ) {		
				
				og.eventManager.fireEvent("before tab panel construct", panelData[i]);
				
				if (panelData[i]['id'] == 'more-panel' && panelData[i]['closable']) {
					panelData[i].onClose = og.setSettingsClosed;
				}
				var p = new og.ContentPanel(panelData[i]);
				og.panels[p.title] = p;
				
				panels.push(p);
			};
			
			var tab_panel = new Ext.TabPanel({
				id: 'tabs-panel',
				region: 'center',
				activeTab: 0,
				enableTabScroll: false,
				listeners: {
					render: {
						fn: function(tabp) {
							setTimeout(function(){
								og.checkAndAdjustTabsSize();
							}, 1000);
						}
					},
					tabchange: {
						fn: function(tabp) {
							og.checkAndAdjustTabsSize();
						}
					}
				},
				items: (panels && panels.length)?panels:null 

			});
			
			var center_panel = new Ext.Panel({
				layout: 'border', 
				id: 'center-panel',
				region:'center',
				items: [
				   /*	new Ext.Panel({
				   	   id: 'breadcrumbs-panel',	
					   region: 'north', 
					   cls : 'breadcrumbs-container',
					   html: '<div id="breadcrumbs"></div>',
					   expanded: true,
					   collapsed: false ,
					   height: 50,
					   header: false,
					   hideBorders: true ,
					   hideCollapseTool: true,
					   headerAsText: true
				   }),*/
				   tab_panel
				]
			});
			
		
			// ENABLE / DISABLE MODULES
			og.eventManager.addListener('config option changed', function(option) {
				if (option.name.substring(0, 7) == 'enable_' && option.name.substring(option.name.length - 7) == '_module') {
					var module = option.name.substring(7, option.name.length - 7);
					var tab = tab_panel.id + "__" + og.panels[module].id ;
					Ext.get(tab).setDisplayed(option.value);					
				}
			});
			
			
			var viewport = new Ext.Viewport({
				layout: 'border',
				stateful: false ,// og.preferences['rememberGUIState'],
				items: [
				        new Ext.BoxComponent({
				        	region: 'north',
				        	el: 'header'
				        })
				        ,new Ext.BoxComponent({
				        	region: 'south',
				        	el: 'footer'
				        })
				        ,{
				        	region: 'west',

				        	id: 'menu-panel',
				        	split: true,
				        	width: 250,
				        	minWidth: 200,
				        	//bodyBorder: false,
				        	hideCollapseTool:true,
				        	collapseMode:'mini',
				        	collapsible:true ,
				        	collapsed: og.menuPanelCollapsed, // This flag is set in layout.php
				        	//autoWidth: true,
				        	layout: 'multi-accordion',
				        	listeners: {
				        		/*'resize': function(p){
				        			
				        		},*/
				        	    'collapse': function(p) {
				        	      og.eventManager.fireEvent("menu-panel collapse");
				        	      setTimeout(function(){
				        	    	  og.checkAndAdjustTabsSize();
				        	    	  ogTasks.initColResize();
								  }, 200);
				        	    },
				        	    'expand': function(p) {
					        	  og.eventManager.fireEvent("menu-panel expand");
				        	      setTimeout(function(){
				        	    	  og.checkAndAdjustTabsSize();
				        	    	  ogTasks.initColResize();
								  }, 200);
					        	}
				        	  },
				        	layoutConfig: {
				        		// layout-specific configs go here
				        		fill: true,
				        		titleCollapse: true,
				        		animate: true,
				        		maxActiveItems: 3 ,
				        		autoWidth: true,
				        		collapsed: true,
				        		expanded: false
				        	},
				        	//stateful: false,
				        	stateful: og.preferences['rememberGUIState'],
				        	items:  og.dimensionPanels,
				        	bbar : [
				        	    {	
				        			iconCls: 'op-ico-details',
				        			tooltip: '<b>'+lang('see more')+'</b>',
				        			text: lang('see more'),
				        			menu: {
				        				items: og.contextManager.getDimensionMenu(),
				        				cls: "context-menu"
				        			},
				        			hidden: !og.preferences['can_modify_navigation_panel']
				        	    },
				        	    '->',
				        	    {	
				        			iconCls: 'ico-trash',
				        			tooltip: lang('trash'),
				        			text: lang('trash'),
				        			handler: function() {
					        	    	var cp = Ext.getCmp('trash-panel');
										var tp = Ext.getCmp('tabs-panel');
										if (!cp){
											cp = new og.ContentPanel({
												closable: true,
												title: lang('trash'),
												id: 'trash-panel',
												iconCls: 'ico-trash',
												refreshOnWorkspaceChange: true,
												refreshOnTagChange: true,
												defaultContent: {
													type: "url",
													data: og.getUrl('object', 'init_trash')
												}
											});
											tp.add(cp);
										}
										tp.setActiveTab(cp);
				        			}
				        	    },
				        	    {	
				        			iconCls: 'ico-archive-obj',
				        			tooltip: lang('archived objects'),
				        			text: lang('archived'),
				        			handler: function() {
					        	    	var cp = Ext.getCmp('archivedobjs-panel');
										var tp = Ext.getCmp('tabs-panel');
										if (!cp){
											cp = new og.ContentPanel({
												closable: true,
												title: lang('archived objects'),
												id: 'archivedobjs-panel',
												iconCls: 'ico-archive-obj',
												refreshOnWorkspaceChange: true,
												refreshOnTagChange: true,
												defaultContent: {
													type: "url",
													data: og.getUrl('object', 'init_archivedobjs')
												}
											});
											tp.add(cp);
										}
										tp.setActiveTab(cp);
				        			}
				        	    }
				        	]

				        }
				        ,
				        	center_panel
				        ]

			});

			og.captureLinks();

			if (og.preferences['email_polling'] > 0) {
				function updateUnreadCount() {
					og.openLink(og.getUrl('mail', 'get_unread_count'), {
						onSuccess: function(d) {
							if (typeof d.unreadCount != 'undefined') {
								og.updateUnreadEmail(d.unreadCount);
							}
						},
						hideLoading: true,
						hideErrors: true,
						preventPanelLoad: true
					});
				}
				updateUnreadCount();
				setInterval(updateUnreadCount, Math.max(og.preferences['email_polling'], 5000));
			}

			if (og.hasNewVersions) {
				og.msg(lang('new version notification title'), og.hasNewVersions, 0);
			}
		},
		onError: function(data) {
		}
	});


});