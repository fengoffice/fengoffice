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
function Application(container){
    var self = window;
    
    self.construct = function(container){
    	var configs = loadConfigs();
    	self.configs = configs;
    	self.container = container;
    	self.JsonManager = new JsonHandler();
    	
		self.Fonts = loadFonts(); //Function getted from server in fonts.js.php
    	self.activeBook = new Book(configs.book.defaultName);
    	self.sheets = new Array();
		var sheet = new Sheet(configs.sheet);

		self.namesStore = new Ext.data.SimpleStore({ 
	    		fields: ['name', 'range']
	    });
	  
		self.sheets.push(sheet);
		self.activeSheet = sheet;
//		
		
		//TODO: fix when multi books supported self.books = new Array();
		/*self.activeBook = new Book();
		*/
		//--------------Load Handlers------------------//
		//Style Handler
		self.Styler = new StyleHandler(configs.style);
		self.CommManager = new CommHandler(configs.communication);
		
		createToolbars(self);
		
		var dataSection = new Ext.Viewport({
		    layout: 'border',
		    renderTo:'body',
		    items: [{
		        region: 'north',
		        el:'north',
		        autoHeight: true,
		        height: 0 ,
		        border: false,
		        margins: '0 0 0 0' ,
		        ctCls: 'no-height' 
		    }, {
		        region: 'west',
		        el:'west',
		        hidden:true,
		        collapsible: true,
		        title: 'Navigation'
		        
		    }, {
		        region: 'center',
		        el:'center',
		        xtype: 'tabpanel',
		        items: {
		            title: 'sheet1'
//		            html: 'The first tab\'s content. Others may be added dynamically'
		        }
		    }, {
		        region: 'south',
		        el:'south',
		        hidden:true,
		        title: 'Information',
		        collapsible: true,
		        html: 'Information goes here',
		        split: true,
		        height: 100,
		        minHeight: 100
		    }]
		});

		
		var center = document.getElementById("center");
		self.grid = new Grid({width:center.offsetWidth,height:center.offsetHeight});
    	center.appendChild(self.grid);
    	self.grid.inicialize();
    	self.grid.on("EditingMode",function(){
    		self.FormulaBar.focus();
    	});
    	//		Model Definition
		self.model = new GridModel(self.grid);
		self.model.setDataModel(self.activeSheet);
		
		self.model.on('Error',function(caller,e){
//			alert(e.toSource());
			Ext.Msg.alert('Error', e.description);
		});
		
		self.model.on('NameChanged',function(){
			var data = self.model.getNames();
			self.namesStore.loadData(data);
		});
		
//		self.model.on('ActiveCellChanged',function(obj,address){
		self.model.on('SelectionChanged',function(obj,address){
			nameSelector.setValue(address);
		});
		
		self.model.on('ActiveCellChanged',function(obj,value){
			FormulaBar.setValue(value);
		});
		
		self.model.refresh();
		
		//Create Key Manager
		self.gridShortCuts = new KeyHandler();
		
		self.gridShortCuts.addAction(self.model.goToHome,false, CH_CTRL + CH_HOME);
		//self.keyManager.addAction(navBar.goToEnd,false, CH_END);
		self.gridShortCuts.addAction(self.model.moveRight,false, CH_TAB);
		self.gridShortCuts.addAction(self.model.moveDown,false, CH_ENTER);
		self.gridShortCuts.addAction(self.model.moveLeft,false, CH_LEFT_ARROW);
		self.gridShortCuts.addAction(self.model.moveRight,false, CH_RIGHT_ARROW);
		self.gridShortCuts.addAction(self.model.moveUp,false, CH_UP_ARROW);
		self.gridShortCuts.addAction(self.model.moveDown,false, CH_DOWN_ARROW);
		self.gridShortCuts.addAction(self.model.undo,false, CH_CTRL + CH_Z);
		self.gridShortCuts.addAction(self.model.redo,false, CH_CTRL + CH_SHIFT + CH_Z);
		self.gridShortCuts.addAction(model.deleteSelection,false, CH_DELETE);
		self.gridShortCuts.addAction(model.setValueToSelection,false, CH_CTRL + CH_ENTER);
		self.grid.onkeydown = gridShortCuts.keyHandler;
		
		self.documentShortCuts = new KeyHandler();
		
		self.documentShortCuts.addAction(self.model.pageUp,false, CH_PAGE_UP);
		self.documentShortCuts.addAction(self.model.pageDown,false, CH_PAGE_DOWN);
		
		self.documentShortCuts.addAction(self.saveBook,false, CH_CTRL + CH_S);
		self.documentShortCuts.addAction(saveBookConfirm,false, CH_CTRL + CH_SHIFT + CH_S);

		self.documentShortCuts.addAction(cmdSetBoldStyle,false, CH_CTRL + CH_B);
		self.documentShortCuts.addAction(cmdSetItalicStyle,false, CH_CTRL + CH_I);
		self.documentShortCuts.addAction(cmdSetUnderlineStyle,false, CH_CTRL + CH_U);
		
		self.documentShortCuts.addAction(namesDialog,false, CH_F3);
		self.documentShortCuts.addAction(function(){self.FormulaBar.focus()},false, CH_F2);
		
		if(Environment.browser == "Explorer")
			document.onkeydown = self.documentShortCuts.keyHandler;
		else
			window.onkeydown = self.documentShortCuts.keyHandler;
		
		//Disable Text Selection
		self.grid.onselectstart = function() {return false;}; // ie
		self.grid.onmousedown = function() {return false;}; // mozilla

		//Capture Resize Event
		window.onresize = function (){
			self.grid.resize(center.offsetWidth,center.offsetHeight);
		}
    }
    
    self.nameSelectorChanged = function(name){
    	if(self.model.existsName(name))
    		self.model.goToName(name);
    	else
    		if(true){ //TODO:Change to check if is a valid name
    			self.model.addName(name);
    		}
    }

   
//    self.bookLoaded = function(data){
//    	var sheet =  JsonManager.importSheet(self.con, data);
//    	self.model.refresh();
//    }
    
    addApplicationAPI(self);
    self.construct(container);
    window.application = self;
    
    return self;
}
    

/** this is high-level function.
 * It must react to delta being more/less than zero.
 * http://adomas.org/javascript-mouse-wheel/
 */
function handle(delta) {
        if (delta < 0)
        	grid.scrollDown(2);
        else
        	grid.scrollDown(-2);
}

/** Event handler for mouse wheel event.
 */
function wheel(event){
        var delta = 0;
        if (!event) /* For IE. */
                event = window.event;
        if (event.wheelDelta) { /* IE/Opera. */
                delta = event.wheelDelta/120;
                /** In Opera 9, delta differs in sign as compared to IE.
                 */
                if (window.opera)
                        delta = -delta;
        } else if (event.detail) { /** Mozilla case. */
                /** In Mozilla, sign of delta is different than in IE.
                 * Also, delta is multiple of 3.
                 */
                delta = -event.detail/3;
        }
        /** If delta is nonzero, handle it.
         * Basically, delta is now positive if wheel was scrolled up,
         * and negative, if wheel was scrolled down.
         */
        if (delta)
                handle(delta);
        /** Prevent default actions caused by mouse wheel.
         * That might be ugly, but we handle scrolls somehow
         * anyway, so don't bother here..
         */
        if (event.preventDefault)
                event.preventDefault();
	event.returnValue = false;
}

/** Initialization code. 
 * If you use your own event management code, change it as required.
 */
if (window.addEventListener)
        /** DOMMouseScroll is for mozilla. */
        window.addEventListener('DOMMouseScroll', wheel, false);
/** IE/Opera. */
window.onmousewheel = document.onmousewheel = wheel;


