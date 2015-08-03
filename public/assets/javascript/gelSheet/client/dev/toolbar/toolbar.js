function createToolbars(application){
	
	Ext.onReady(function(){
		var imgpath = "themes/" + application.configs.theme + '/img/' ;
		var iconspath = imgpath+'icons/';

	    Ext.QuickTips.init();
	 
	    var tb = new Ext.Toolbar();
	    tb.render('north');

		//----------- SAVE ------------//

	    tb.add('-', {
	        icon: iconspath+'saveas-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('Save')+'</b><br/>'+lang('Save the current book'),
	        handler: function(){
	    		application.saveBook();
	    	}
	    });
	    
		
		//--------- SAVE AS -----------//
		
			tb.add( {
	        icon: iconspath+'pencil-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('Save as')+'..</b><br/>'+lang('Save the spreadsheet with a new filename'),
	        handler: function(){
				saveBookConfirm() ;
			}
	    });
			
		//--------- NEW -----------//
			
			tb.add( {
	        icon: iconspath+'new-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('New')+'..</b><br/>'+lang('New spreadsheet'),
	        handler: function(){
				application.newBook() ;
			}
	    },'-');			

			
		//--------- REFRESH -----------//
			
		tb.add( {
	        icon: iconspath+'refresh-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('Refresh grid ')+'..</b><br/>'+lang('Refresh Grid'),
	        handler: function() {
				application.refresh() ;
			}
		
	    },'-');	
			
			
		//----------- EXPORT ------------//
	
	    var exportMenu = new Ext.menu.Menu({
	        id: 'exportMenu',
	        items: [
	            {
	                text: 'PDF',
				    icon: iconspath+'PDF-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' PDF</b><br/>'+lang('Export to')+' PDF. <br/>',
	        		handler: function(){
	            		application.exportBook('pdf');
	            	}
	            },
	        	{
	                text: 'XLS',
				    icon: iconspath+'XLS-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' XLS</b><br/>'+lang('Export to')+' XLS. <br/>',
	        		handler: function(){
            			application.exportBook('xls');
            		} 
	            },            
	        	{
	                text: 'XLSX',
				    icon: iconspath+'XLSX-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' XLSX</b><br/>'+lang('Export to')+' XLSX. <br/>',
	        		handler: function(){
	            		application.exportBook('xlsx');
	            	} 
	        	},
	           	{
	                text: 'ODS',
				    icon: iconspath+'ODS-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' ODS</b><br/>'+lang('Export to')+' ODS. <br/>',
	        		handler: function(){
	            		application.exportBook('ods');
	            	}
	            }
			]
	    });
	
	   tb.add( {
	        icon: iconspath+'export.png', // icons can also be specified inline
	        text: lang('export'),
	        iconCls: 'bmenu', 
	        tooltip: '<b>'+lang('Export')+'</b><br/>'+lang('Export to many formats')+'. <br/>',
	        menu: exportMenu  
	    },'-');
	
		//----------- UNDO ------------//

		
		tb.add({
	        icon: iconspath+'undo-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('undo')+'</b>',
	        handler: function(){
	    		application.undo();
	    	}
	    });


		//----------- REDO ------------//
		
		tb.add({
	        icon: iconspath+'redo-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('redo')+'</b>',
	        handler: function(){
	    		application.redo();
	    	}
	    });


		//----------- FONT BOLD ------------//

	    tb.add({
	        icon: iconspath+'bold-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('bold')+'</b>',
	        handler: bold
	    });

	
		//----------- FONT ITALIC ------------//
	
	    tb.add({
	        icon: iconspath+'italic-16x16.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('italic')+'</i>',
	        handler: italic
	    });

		//----------- FONT UNDERLINE ------------//

	     tb.add({
	        icon: iconspath+'underline-16x16.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<u>'+lang('underline')+'</u>',
	        handler: underline
	    },"-"); 
	     
		tb.add({
	        icon: iconspath+'delete-16x16.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<u>'+lang('Delete')+'</u>',
	        handler: window.deleteSelection
	    });  
	     
		tb.add({
	        icon: iconspath+'unformat-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<u>'+lang('Clear format')+'</u>',
	        handler: unformat
	    },'-');   
	
		//----------- FONT COLOR ------------//
	
		var fontColorMenu = new Ext.menu.ColorMenu({});

		fontColorMenu.on('select',function(cm, color){
		    				cmdSetFontColor('#'+color);	
		    			});
	     tb.add({
	        icon: iconspath+'font-color-16x16.png',
	        cls: 'x-btn-icon',
	        tooltip: lang('Font color'),
	        menu: fontColorMenu
	    },"-");

		//----------- BACKGROUND COLOR ------------//
/*
		var bgColorMenu = new Ext.menu.ColorMenu({});
		
		bgColorMenu.on('select',function(cm, color){
					cmdSetBgColor('#'+color);	
				});

		
	     tb.add({
	        icon: iconspath+'bgcolor-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Background color'),
	        menu: bgColorMenu 
	       
	    },'-');  
*/	    
	    	
		//----------- FONT ------------//
	     var fontItems = [];
		    
	    	for(var i in application.Fonts){
	    		if(i != 'remove'){
	    			fontItems.push({
	    				text: '<span style="font-family: '+ application.Fonts[i] +  ' ">' + application.Fonts[i] + '</span>',
	    				index : i,
		        		handler: function(){
	    					cmdSetFontStyle(this.index);
	    				}
	    			});
	    		}
	    	}
	    
	    var fontMenu = new Ext.menu.Menu({
	        id: 'fontMenu',
	        items: fontItems
	        /*items: [
	            {
	                text: '<span style="font-family: Arial">Arial</span>',				 
	        		handler: function(){cmdSetFontStyle('0');}
	            },
	           	{
	                text: '<span style="font-family: Times New Roman">Times New Roman</span>',				 
	        		handler: function(){cmdSetFontStyle('1');}
	            },
	           	{
	                text: '<span style="font-family: Verdana">Verdana</span>',				 
	        		handler: function(){cmdSetFontStyle('2');}
	            },
	           	{
	                text: '<span style="font-family: Courier">Courier</span>',				 
	        		handler: function(){cmdSetFontStyle('3');}
	            },
	            {
	                text: '<span style="font-family: Lucida Sans Console">Lucida Sans Console</span>',				 
	        		handler: function(){cmdSetFontStyle('4');}
	            },
	           	{
	                text: '<span style="font-family: Tahoma">Tahoma</span>',				 
	        		handler: function(){cmdSetFontStyle('5');}
	            }
			]*/
	       	
	        
	    });	        
	    tb.add({
	        icon: iconspath+'font-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Select font'),
	        menu: fontMenu 
	    });  
	
	
	
		//----------- FONT SIZE ------------//
	
		var fontSize = new Ext.form.ComboBox({
			store: [
						['6', '6', '6'],
						['7', '7', '7'],
						['8', '8', '8'],
						['9', '9', '9'],
						['10', '10', '10'],
						['11', '11', '11'],
						['12', '12', '12'],
						['14', '14', '14'],
						['18', '18', '18'],
						['24', '24', '24'],
						['36', '36', '36']
					],
	        displayField:'function_name',
	        typeAhead: true,
	        editable:false,
	        mode: 'local',
	        triggerAction: 'all',
	        emptyText:'10',
	        width: 60 ,
	        selectOnFocus:true,
	        tooltip: lang('Font size')
	    });
	    
	    fontSize.on('select',function(combo,record,index){
	        				cmdSetFontSizeStyle(combo.getValue());
	        			});
	        			

		tb.addField(fontSize) ;
		tb.add('-');
	/*	
	    var borderMenu = new Ext.menu.Menu({
	        id: 'borderMenu',
	       
	        items: [
	            {
	            	hideLabel: true ,
	            	disabled: true ,
	            	icon: iconspath+'border_none.png' ,
	            	text: '(Unimplemented)',
	        		handler: function (){setBorderNone() ;} 
	            },
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_left.png' ,
	           		text: lang('Border left'),
	        		handler: function (){setBorderLeft() ;} 
	            }
				,
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_bottom.png' ,
	           		text: lang('Border bottom'),
	        		handler: function (){setBorderBottom() ;} 
	            }
				,
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_right.png' ,
	           		text: lang('Border right'),
	        		handler: function(){setBorderRight();}
	            },	       
				
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_top.png' ,
	           		text: lang('Border top'),
	        		handler: function(){setBorderTop();}
	            }	                 	            
			]
	        
	    });	        
	    tb.add({
	        icon: iconspath+'border_bottom.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Border'),
	        menu: borderMenu 
	    });  
		
		tb.add("-");
*/		
		tb.add({
			disabled: false ,
	        icon: iconspath+'align_left-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align left')+'</i>',
	        handler: function() { cmdSetAlignStyle('left') ; }
	    });

		tb.add({
			disabled: false ,
	        icon: iconspath+'align_center-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align center')+'</i>',
	        handler: function() { cmdSetAlignStyle('center') ; }
	    });		

		tb.add({
			disabled: false ,
	        icon: iconspath+'align_right-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align right')+'</i>',
	        handler: function() { cmdSetAlignStyle('right') ; }
	    });
	
		tb.add("-");

		tb.add({
			disabled: false ,
	        icon: iconspath+'valign_button-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align bottom')+'</i>',
	        handler: function(){cmdSetValignStyle('bottom') ;}
	    });

		tb.add({
			disabled: false ,
	        icon: iconspath+'valign_center-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align center')+'</i>',
	        handler: function(){cmdSetValignStyle('middle') ;}
	    });		

		tb.add({
			disabled: false ,
	        icon: iconspath+'valign_top-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align top')+'</i>',
	        handler: function(){cmdSetValignStyle('top') ;}
	    });

		
		tb.add("-");

		tb.add({
	        icon: iconspath+'fx-16x16.png',
	        cls: 'x-btn-icon',
	        menu:
				new Ext.menu.Menu({
			        items: [
			            {
			            	//icon: iconspath+'sum.png',
			            	hideLabel: true ,
			            	text: 'Sum',
			        		handler: function(){window.FormulaBar.setValue("=Sum("); window.FormulaBar.focus();} 
			            },
			            {
			            	hideLabel: true ,
			            	text: 'Average',
			        		handler: function(){window.FormulaBar.setValue("=Average("); window.FormulaBar.focus();} 
			            },
			            {
			            	hideLabel: true ,
			            	text: 'Count',
			        		handler: function(){window.FormulaBar.setValue("=Count("); window.FormulaBar.focus();} 
			            },
			            {
			            	hideLabel: true ,
			            	text: 'Max',
			        		handler: function(){window.FormulaBar.setValue("=Max("); window.FormulaBar.focus();} 
			            },     
			            {
			            	hideLabel: true ,
			            	text: 'Min',
			        		handler: function(){window.FormulaBar.setValue("=Min("); window.FormulaBar.focus();} 
			            },
			            "-",
			            {
			            	hideLabel: true ,
			            	text: lang('More formulas'),
			        		handler: formulaWizard
			            }
			            
					]
			    })
		});
		
		tb.add({
			disabled: false ,
	        icon: iconspath+'range2.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Range names')+'</i>',
	        handler: function() {namesDialog();}
	    });
		
		tb.add({
			disabled: false ,
	        icon: iconspath+'show-formula.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Show formulas')+'</i>',
	        handler: function() {
				application.switchViewMode(!this.pressed) ;
				this.toggle(!this.pressed) ;
			}
	    });

		tb.add({
			disabled: false ,
	        icon: iconspath+'decimal-increase.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Decimal increase')+'</i>',
	        handler: function() {
				application.increaseDecimals();
			}	
	    });
		
		tb.add({
			disabled: false ,
	        icon: iconspath+'decimal-decrease.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Decimal decrease')+'</i>',
	        handler: function() {
				application.decreaseDecimals();
			}
	    });		
		/***************** SECOND TOOLBAR *****************/ 
		
	    var tb2 = new Ext.Toolbar();
	    tb2.render('north');
	   
	    var nameSelector = new Ext.form.ComboBox({
	    	displayField: 'name' ,
	    	store: namesStore,
	        typeAhead: true,
	        mode: 'local',
	        forceSelection: false,
	        width: 148 ,
	        height: 23 ,
	        triggerAction: 'all',
	        selectOnFocus: true,
	        enableKeyEvents: true,
	        ctCls: 'nameSelectorContainer',
	        id: 'nameSelector'
	    }) ;
	    
	    window.nameSelector = nameSelector;
	    nameSelector.on('keydown', function(object,e) {
		    if (e.getKey() == e.ENTER) {
				if ( ! text.isExpanded() ) {
					setTimeout(function() {application.nameSelectorChanged(nameSelector.getValue())} , 1);
					//application.model.moveDown() ;
				}
			}
	    });
	    
	    nameSelector.on('select',function(){
	    	application.nameSelectorChanged(nameSelector.getValue());
	    });
	    
	    tb2.add(nameSelector); 
	
		tb2.add('') ;
		
		tb2.add( {
	        icon: iconspath+'fx-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Insert Function'),
	        handler: formulaWizard 
	    });		
		
		tb2.add('') ;
				
	    var function_list = new Ext.data.SimpleStore({
	        fields: ['function_id', 'function_name'],
	        data :  calculator.getFunctionList() 
	    });
	    function_list.sort('function_id');

	    
	    var text = new Ext.form.ComboBox({
	        store: function_list , 
	        displayField:'function_name',
	        typeAhead: true,
	        mode: 'local',
	        forceSelection: false,
	        width: 460 ,
	        triggerAction: 'all',
	        selectOnFocus: false,
	        id: 'FormulaBar',
	        ctCls: 'no-image',
	        enableKeyEvents: true
	    });
	
	    
		text.on('keydown', function(object,e) {
				if (e.getKey() == e.ENTER) {
					if ( ! text.isExpanded() ) {
						setTimeout(function() {application.editActiveCell(text.getValue())} , 1);
						application.model.moveDown() ;
					}
				}
				setTimeout(function() {application.editActiveCell(text.getValue())} , 1);
				return true ;
			} 
		);
	
		
		text.on('select', function(object,e) {
				application.model.editActiveCell(text.getValue()); 
				return false ; 
			} 
		);
		
		text.on('focus', function(object,e) {
				application.editActiveCell(text.getValue());
				return true ;
			}
		);
		
		
		tb2.addField(text) ;
		window.FormulaBar = text ;
	});
}