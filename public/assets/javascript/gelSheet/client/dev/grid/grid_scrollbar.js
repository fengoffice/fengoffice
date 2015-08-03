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
function ScrollBar(id,width,height){
	var self = document.createElement("DIV");

	self.construct = function(id,width,height){
		this.id = id;
		this.position = {x:0,y:0};
		//this.maxPositon = length*1.1; //10% more
		this.style.position = "absolute";
		this.style.overflow = "scroll";
		this.style.top = "0px";
		this.style.left = "0px";
		this.style.width = "100%";
		this.style.height = "100%";
		//this.style.backgroundColor = "#F00";
		//this.style.zIndex = 1;
		this.inner = document.createElement("DIV");
		WrapStyle(this.inner);
		this.inner.style.position = "absolute";
		this.inner.setTop(0);
		this.inner.setLeft(0);
		this.inner.setWidth(width);
		this.inner.setHeight(height);
		this.appendChild(this.inner);
	}

	self.getHeight = function(){
		return parseInt(this.inner.style.height);
	}

	self.setHeight = function(height){
		this.inner.style.height = px(height);
	}


	self.getWidth = function(){
		return parseInt(this.inner.style.width);
	}

	self.setWidth = function(width){
		this.inner.style.width = px(width);
	}

	//Method that must be overwritten to capture scroll on vertical way
	//self.onVerticalScroll = function(){}

	self.onscroll = function(e){
        e ? e : e = window.event; //get event for IE
		var offsetX = this.scrollLeft - this.position.x;
		var offsetY = this.scrollTop - this.position.y;
		if(offsetY){
			this.position.y = this.scrollTop;
			if(this.onVerticalScroll)this.onVerticalScroll(this.scrollTop);
		}

		if(offsetX){
			this.position.x = this.scrollLeft;
			if(this.onHorizontalScroll)this.onHorizontalScroll(this.scrollLeft);
		}

	}

	self.construct(id,width,height);
	return self;
}