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
function ColumnReziseArea(){
	var self = document.createElement("DIV");

	self.construct = function(){
		this.data = document.createElement("DIV");
		this.data.className = "ColumnTitle" ;
		
		var tdResizer = document.createElement("DIV");
		tdResizer.className = "ColumnResizer" ;
		tdResizer.offset = 0;
		
		tdResizer.onmousedown = function(e){
			e ? e : e = window.event; //get event for IE
			this.offset = e.screenX;
			if(self.onresizing) self.onresizing(e); //Ghost function call, to support external resizing from outside
		}
		this.tdResizer = tdResizer;
		self.style.width="100%" ;
		self.style.height="100%" ;
		self.style.backgroundColor = "transparent";
		self.appendChild(tdResizer) ;
		self.appendChild(this.data) ;
	}

	self.setInnerHTML = function(value){
		this.data.innerHTML = value;
	}
	self.construct();
	return self;
}

function VColumn(index){
	var self = document.createElement("TH");

	self.construct = function(index){
		this.index = index;
		this.vcells = new Array();
		this.style.textAlign = "center";
		//this.style.overflow = "hidden";
//		this.style.cursor = "url(img/col.cur.ico), default";
		this.className = "ColumnUnselected";

		this.resizeArea = new ColumnReziseArea();

		this.resizeArea.setInnerHTML(String.fromCharCode(65+index));
		this.appendChild(this.resizeArea);
		WrapStyle(this);
	}
	
	//############ GETTERS AND SETTERS ##################################

	self.setIndex = function(index){
		this.index = index;
	}

	self.getIndex = function(){
		return this.index;
	}

	self.getAddress = function(){
		return {col:this.index};
	}

	self.getSize = function(){
		return this.getWidth();
	}

	self.setSize = function(size){
		return this.setWidth(size);
	}

	self.setInnerHTML = function(value){
		this.resizeArea.setInnerHTML(value);
	}

	self.setTitle = function(value){
		this.resizeArea.setInnerHTML(value);
	}
	
	//############### METHODS ###########################################
	/**
	 * Adds a Cell (VCell) to the Column
	 */
	self.addCell = function(cell){
		this.vcells.push(cell);
	}

	/**
	 * Changes Style of column to an activated mode defined by the ColumnFocused style
	 * on style.css
	 */
	self.activate = function(){
		this.className = "ColumnFocused";
	}

	/**
	 * Changes Style of column to an unselected mode defined by the ColumnUnselected style
	 * on style.css
	 */
	self.deactivate = function(){
		this.className = "ColumnUnselected";
	}

	/**
	 * Changes Style of column to an selected mode defined by the ColumnSelected style
	 * on style.css
	 * Extends on each of Cell
	 */
	self.select = function(){
		this.className = "ColumnSelected";

		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].select();
		}
	}


	/**
	 * Changes Style of column to an unselected mode defined by the ColumnUnselected style
	 * on style.css
	 * Extends on each of Cell
	 */
	self.unselect = function(){
		this.className = "ColumnUnselected";
		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].unselect();
		}
	}

	/**
	 * Changes Size of the column to a new offset (delta)
	 */
	self.resize = function(delta){
		var width = this.getWidth() - delta;
		if (width < 6) width = 0;
		this.setWidth(width);
		
//		if(this.columnChanged) this.columnChanged();
	}
	
	self.construct(index);

	//################ EVENTS ###################################

	self.resizeArea.onresizing = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('resizemousedown',e);
	}
	
	self.onmousedown = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('mousedown',e);
	}
	
	self.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		self.fire('mouseover',e);
	}
//	

	WrapEvents(self);
	self.register('mousedown');
	self.register('mouseover');
	self.register('resizemousedown');
	return self;
}

/*
var posx = 0;
var posy = 0;
if (!e) var e = window.event;
if (e.pageX || e.pageY) 	{
	posx = e.pageX;
	posy = e.pageY;
}
else if (e.clientX || e.clientY) 	{
	posx = e.clientX + document.body.scrollLeft
		+ document.documentElement.scrollLeft;
	posy = e.clientY + document.body.scrollTop
		+ document.documentElement.scrollTop;
}
// posx and posy contain the mouse position relative to the document
// Do something with this information



//============================================================
// functions to find the X and Y coords of an element on which
// an event occurred - the event object is passed
//============================================================
function findPosX(e)
{
    var x = (e.offsetX) ? e.offsetX : e.layerX;
    var X = (e.pageX)   ? e.pageX   : e.clientX;
    var pos = X - x;
    return pos;
}

function findPosY(e)
{
    var y = (e.offsetY) ? e.offsetY : e.layerY;
    var Y = (e.pageY)   ? e.pageY   : e.clientY;
    var pos = Y - y;
    return pos;
}

*/