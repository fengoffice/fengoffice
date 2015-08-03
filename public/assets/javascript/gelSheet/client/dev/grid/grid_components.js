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
function CellEditor(){
	var self = document.createElement("INPUT");
	self.construct = function(){
		this.editing = false; 
		this.id = "ActiveCell";
		this.type = "TEXT";
		this.cols = 2000;
		this.rows = 2;
		//this.style.position  = "absolute";
		this.style.overflow  = "visible";
		//this.style.border = "2px solid #000000";
		//this.style.backgroundColor = "#FFFFFF";
		this.style.zIndex = 1000;
		this.activeCell = undefined;
		this.style.top = "0px";
		this.style.left = "0px";
		this.style.width = "100%";
		//this.style.height = "100%";
		this.cell = undefined;
		this.fontStyleId = 0;
		WrapStyle(this);
		WrapEvents(this);
		self.register("ValueChanged");
	}

	self.reFit = function(){
		this.style.width = "100%";
		//this.style.height = "100%";
	}
	
	self.fitToCell = function(vcell){
		this.editing = false;
		this.cell = vcell;
		self.style.visibility = "hidden";
//		self.value = vcell.innerHTML;
		vcell.setInnerHTML("");
		this.value = "";
		this.fontStyleId = vcell.getFontStyleId();
		//if(vcell.getFontStyleId() != this.fontStyleId){
			WrapFontStyle(self,vcell.getFontStyleId());
		//}
		vcell.add(self);
		vcell.className = vcell.className + " Editing" ; 
		self.style.visibility = "visible";
		self.focus();
	}

	self.updateFontStyle = function(){
		var address = self.activeCell.getAddress();
		var cell = scGetCell(activeSheet,address.row,address.col);

		WrapFontStyle(self,scGetCell(activeSheet,address.row,address.col).getFontStyleId());
	}

	self.updateValue = function(newValue){
		self.value = newValue;
	}

	self.setValue = function(value){
		if(value!=undefined)
			this.value = value;
		else
			this.value = "";
	}

	self.getValue = function(){
		return this.value;
	}
	
	self.setFontStyleId = function(fsId){
		if(fsId)
			this.fontStyleId = fontStyleId;
	}
	
	self.getFontStyleId = function(){
		return this.fontStyleId;
	}

	self.getColumn = function(){
		return this.cell.getColumn();
	}

	self.getRow = function(){
		return this.cell.getRow();
	}
	
	self.onkeyup = function(e){
		self.fire("ValueChanged",this.value);
	};
	
	/*self.onchange = function(){
		//alert("entra");
		/*var vcell = this.activeCell;
		var address = vcell.getAddress();
		var formula = "";
		if (this.value) formula = this.value;
		try{
			scUpdateCell(window.activeSheet,address.row,address.col,formula);
		}catch(e){errorConsole.println("grid_components::CellEditor:onchange ->"+ e.toSource());}

		vcell.setInnerHTML(formula);
		* /
	    //this.activeCell.refresh();
	}*/

	self.refresh = function(){
	  //  this.fitToCell(this.activeCell);
	}

	self.construct();
	return self;
}

function SelectorBox(){
	var self = document.createElement("DIV");

	self.construct = function(){
		this.id = "ActiveRange";
		this.style.position  = "absolute";
		this.style.overflow = "visible";
		//this.style.border = "3px solid #000000";
		WrapStyle(this);
		this.setZIndex(3000);
		var fillBox = document.createElement("DIV");
		fillBox.style.position  = "absolute";
		fillBox.style.width = "5px";
		fillBox.style.height = "5px";
		fillBox.style.zIndex = 3001;
		fillBox.style.backgroundColor = "#000000";
		fillBox.style.cursor = "crosshair";
		fillBox.style.border = "1px solid #FFFFFF";
		this.fillBox = fillBox;
		this.appendChild(fillBox);
		WrapEvents(this);
		self.register("EditingMode");
		//this.focus();
	}

	self.setVisible = function(value){
		if(value)
			this.style.visibility = "visible";
		else
			this.style.visibility = "hidden";
	}
	self.fitToRange = function(range){
		//alert(range.getAddress().toSource());
		
		var borderWidth = 3;//parseInt(self.style.borderWidth);
		self.setLeft(range.offsetLeft-borderWidth/2);
		self.setTop(range.offsetTop-borderWidth/2);
		try{
			self.setWidth(range.offsetWidth-borderWidth);
			self.setHeight(range.offsetHeight-borderWidth);
			self.fillBox.style.left = px(parseInt(self.style.width)-2);
			self.fillBox.style.top =  px(parseInt(self.style.height)-2);
		}catch(e){};
		this.style.visibility = "visible";
	}
	
	
	self.fitToArea = function(area){
		//alert(range.getAddress().toSource());
		var borderWidth = 3;//parseInt(self.style.borderWidth);
		
		self.setLeft(area.left-borderWidth/2);
		self.setTop(area.top-borderWidth/2);
		try{
			self.setWidth(area.width-2);
			self.setHeight(area.height-2);
			self.fillBox.style.left = px(parseInt(self.style.width)-2);
			self.fillBox.style.top =  px(parseInt(self.style.height)-2);
		}catch(e){};
	}

	self.refresh = function(){
		self.fitToRange(grid.activeCell);
	}
	
	self.onclick = function(){
		self.fire("EditingMode",true);
	};
	
	self.construct();
	return self;
}

