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
	function VCell(row, column){
	var self = document.createElement("TD");

	self.construct = function(row, column){
		this.className = "Cell Unselected";
		this.row = row;
		this.column = column;
		this.address = {"row":row,"col":column}; //Reference Address
		this.value = undefined;
		this.fontStyleId = 0;
		this.container = document.createElement("DIV");
		this.container.className ='CellContainer' ;
		this.style.whiteSpace = "nowrap";
		self.appendChild(this.container) ;
		WrapStyle(this);
		WrapEvents(this);
	}

	self.add = function(elem){
		this.container.appendChild(elem) ;
	}
	self.getFontStyleId = function(){
		return this.fontStyleId;
	}

	self.getValue = function(){
		return this.value;
	}

	self.setAddress = function(row,column){
		this.address.row = row;
		this.address.col = column;
	}
	

	
	self.getAddress = function(){
		return {row:this.address.row,col:this.address.col};
	}

	self.getColumn = function(){
		return this.column;
	}

	self.getRow = function(){
		return this.row;
	}
	self.setValue = function(value){
		this.value = value;
		self.setInnerHTML(value);
	}

	self.setCell = function(cell){
		this.cell = cell;
	}

	self.activate = function(){
		this.className = "Cell Focused";
	}

	self.deactivate = function(){
		this.className = "Cell Unselected";
	}

	self.select = function(){
		this.className = "Cell Selected";
	}

	self.unselect = function(){
		this.className = "Cell Unselected";
	}

	self.setInnerHTML = function(value){
	    if(value)
		    this.container.innerHTML = value;
		else
		    this.container.innerHTML = "";
	}

	self.updateFontStyle = function(newFontStyleId){
		if(this.fontStyleId != newFontStyleId){
			WrapFontStyle(this,newFontStyleId);
			this.fontStyleId = newFontStyleId;
		}
	}

	self.refresh = function(){
		if(this.cell!=undefined)
			if(this.cell.value!=undefined) //IE doesnt support AND with short circuit
				this.container.innerHTML = this.cell.value;
			else
				this.container.innerHTML = "";
		else
			this.conteiner.innerHTML = "";
	}
	
	self.construct(row, column);
	
	/***** must override construct methods:  *****/
	self.setHeight = function(height) {
		this.style.height = px(height) ;
		//this.container.style.height = px(height) ;
	}
	self.setTextDecoration = function(value){
		self.container.style.textDecoration = value ;
	}
	self.getTextDecoration = function(){
		return self.container.style.textDecoration ;
	}
	
	return self;
}

