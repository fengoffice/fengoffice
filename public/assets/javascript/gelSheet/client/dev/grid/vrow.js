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

function RowResizeArea(){
	var self = document.createElement("DIV");

	self.construct = function(){
		self.style.left = "0px";
		self.style.width = "100%";
		self.style.top = "90%";
		self.style.height = "5px";
		self.style.backgroundColor = "transparent";
		self.style.cursor = "s-resize"; 
	}
	self.construct();
	return self;
}

function VRow(index){
	/*
	 * +-<TR self>-------------------------------------+
	 * |+-<TH header>---------------------------------+|
	 * ||+---<TABLE>--------------------------------+|||
	 * |||+---<TBODY>------------------------------+||||
	 * ||||+---<TR Title Area 90%>-----------------+||||
	 * |||||				<TD Title Cell>	   	   |||||
	 * ||||+---------------------------------------+||||
	 * ||||+---<TR Resize Area 10%>----------------+||||
	 * |||||				<TD Resize Cell>	   |||||
	 * ||||+---------------------------------------+||||
	 * ||+------------------------------------------+|||
	 * |+---------------------------------------------+|
	 * +-----------------------------------------------+
	 */

	var self = document.createElement("TR");

	self.construct = function(index){
		this.vcells = new Array();
		this.index = index;
		this.selected = false;
		
		this.header = document.createElement("TH"); //Row Header, First Cell in Row
		this.header.style.verticalAlign = "bottom";

		var titleCell = document.createElement('DIV') ;
//		titleCell.style.cursor = "url(img/row.cur2.ico), default";
		titleCell.style.height = '100%' ;
		titleCell.style.textAlign = "center";
  		
		var resizeArea = document.createElement('DIV');
		resizeArea.className = "VerticalResizeArea" ;
		
		this.style.overflow = "hidden";
		this.style.padding = "0px";
		
		this.header.className = this.header.className + " RowUnselected";
		if (index == 0) 
			this.header.className = this.header.className + " top-right-cell" ;
		this.header.style.overflow = "hidden";
		
		this.style.overflow = "hidden";
		
		
		//Save reference pointers to private vars
		this.titleCell = titleCell;
		this.resizeArea = resizeArea;
		this.header.appendChild(titleCell) ;
		this.header.appendChild(resizeArea) ;

		
		this.appendChild(this.header);
		
	
		WrapStyle(this.header);
		WrapStyle(self);
		
		titleCell.innerHTML = index+1;
	}
	self.addCell = function(cell){
		this.vcells.push(cell);
		this.appendChild(cell);
	}

	self.activate = function(){
		this.header.className = "RowFocused";
	}

	self.deactivate = function(){
		this.selected = false;
		this.header.className = "RowUnselected";
	}

	self.select = function(pasive){
		this.selected = true;
		this.header.className = "RowSelected";
		
		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].select();
		}
	}

	self.unselect = function(){
		this.selected = false;
		this.header.className = "RowUnselected";

		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].unselect();
		}
	}

	
	self.isSelected = function(){
		return this.selected;
	}
	
	self.getIndex = function(){
		return this.index;
	}

	self.setIndex = function(index){
		this.index = index;
	}

	self.getAddress = function(){
		return {row:this.index};
	}

	self.setSize = function(size){

		if(this.getHeight()!=size){
			this.setHeight(size);
			this.header.style.height = px(size);
			this.setHeight(size);
			for(var i=0;i<this.vcells.length;i++){
				this.vcells[i].setHeight(size);
			}
		}
	}

	self.getSize = function(){
		return this.getHeight();
	}

	self.hide = function(){
		this.style.display = "none";
	}
	
	self.show = function(){
		this.style.display = "";
	}
	
	self.resize = function(delta){
		var height = this.getHeight() - delta;
		if (height < 1)
			self.hide();
		else{
			this.setSize(height);			
		}
	}

	self.setInnerHTML = function(value){
		this.titleCell.innerHTML = value;
	}

	self.setTitle = function(value){
		this.titleCell.innerHTML = value;
	}
	
	self.construct(index);
	
	//################ EVENTS ###################################
	
	self.resizeArea.onmousedown = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('resizemousedown',e);
		e.stopPropagation();
		return false;
	}
	
	self.header.onmousedown = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('mousedown',e);
	}
	
	self.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('mouseover',e);
	}
	
	
	WrapEvents(self);
	self.register('mousedown');
	self.register('mouseover');
	self.register('resizemousedown');
	return self;
}
