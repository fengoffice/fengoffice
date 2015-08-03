/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */

function formulaWizard() {
	Ext.onReady(function(){
		/*********** Data Source ***********/
	    var store = new Ext.data.SimpleStore({
	        fields: ['function_id', 'function_name', 'function_category' , 'function_description'],
	        data :  calculator.getFunctionNameList() 
	    });
	    store.sort('function_id');

	    /********** Grid Panel ************/
	    var grid = new Ext.grid.GridPanel({
	        store		: store,
	        columns		: [
		            {header: "Function", width: 120, dataIndex: 'function_id', sortable: true},
		            {header: "Category", width: 115, dataIndex: 'function_category', sortable: true}
	        ],
			sm			: new Ext.grid.RowSelectionModel({singleSelect: true}),
			viewConfig	: {forceFit: true},
	        height		:210,
	        autoWidth	: true,
			split		: true,
			region		: 'north'
	    });
		
		// define a template to use for the detail view
	    var bookTpl = new Ext.Template([
			'<br/>{function_description} <br/>'
		]);
		
		

		var ct = new Ext.Panel({
			
			frame: true,
			title: 'Select a function...',
			//width: 500,
			autoHeight: true,
			autoWidth: true,
			//layout: 'border',
			items: [
				grid,
				{
					id: 'detailPanel',
					region: 'center',
					bodyStyle: {
						padding: '10px'
					},
					html: '<br><br><strong>Please select a function to see additional details.<strong>'
				}
			]
		});
		
		grid.getSelectionModel().on('rowselect', function(sm, rowIdx, r) {
			var detailPanel = Ext.getCmp('detailPanel');
			bookTpl.overwrite(detailPanel.body, r.data);
		});
		
		
		var win = new Ext.Window({
			title		  	: 'Insert a function:',
	        applyToMarkup	: 'dialog-container',
	        layout        	: 'fit',
	        autoHeight  	: true ,
	        width			: 500 ,
	        plain       	: true ,
	        modal 			: true , 
	        items			: ct,  
	        resizable		: false
	    });
		
		function selectFunction() {
	    	var functionName = "="+grid.getSelectionModel().getSelected().data.function_id +"()";
			FormulaBar.setValue(functionName);
			FormulaBar.focus() ;
			win.close() ;
		};
		
		win.addButton({
			text:'Ok', 
	        handler: selectFunction 
		});
		win.addButton( {text:'Close', handler: function(){win.close();}} ) ;
		
		grid.on('rowdblclick',selectFunction);
		
		win.show() ; 
		//ct.show() ;

		});
}


function namesDialog() {
	Ext.onReady(function(){
		/*********** Data Source ***********/
	    var store = new Ext.data.SimpleStore({
	        fields: ['name', 'range'],
	        data :  window.activeSheet.getNames()
	    });
	    store.sort('name');

	    /********** Grid Panel ************/
	    var grid = new Ext.grid.GridPanel({
	        store		: store,
	        columns		: [
		            {header: "Name", width: 120, dataIndex: 'name', sortable: true},
		            {header: "Range", width: 115, dataIndex: 'range', sortable: true}
	        ],
			sm			: new Ext.grid.RowSelectionModel({singleSelect: true}),
			viewConfig	: {forceFit: true},
	        height		: 210,
	        autoWidth	: true,
			split		: true,
			region		: 'north'
	    });
		
		// define a template to use for the detail view
	    var bookTpl = new Ext.Template([
			'<br/>{name} => {range} <br/>'
		]);
		
	
		var ct = new Ext.Panel({
			frame: true,
			title: lang('Range names')+'...',
			autoHeight: true,
			autoWidth: true,
			items: [
				grid,
				{
					region: 'center',
					bodyStyle: {
						padding: '10px'
					},
					html: '<br><br><strong>Here are listed all the range names on the book.<strong>'
				}
			]
		});
		
		var win = new Ext.Window({
			title		  	: 'Ranges:',
	        applyToMarkup	: 'dialog-container',
	        layout        	: 'fit',
	        autoHeight  	: true ,
	        width			: 500 ,
	        plain       	: true ,
	        modal 			: true , 
	        items			: ct,  
	        resizable		: false
	    });
		win.addButton({
			text:'Ok', 
	        handler: function() {
				var selected = grid.getSelections()  ; 
				if (selected[0] != undefined ) {
					var value = selected[0].data.name ;
					var current = application.getActiveCellValue();
			    	if ( trim(current) !=  "") {
			    		application.editActiveCell(current+value) ;
			    	}else {
			    		application.editActiveCell("="+value) ;
			    	}
			    	application.focusActiveCell();
		    		win.close();

				}
			}
		
		});
		win.addButton( {text:'Close', handler: function(){win.close();}} ) ;
		win.show() ; 

		}
	);
}

