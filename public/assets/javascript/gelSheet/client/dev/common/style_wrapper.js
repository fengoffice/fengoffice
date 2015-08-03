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

function px(value){
	return value+"px";
}

function pt(value){
	return value+"pt";
}


function WrapFontStyle(object,fontStyleId){
	if(fontStyleId==undefined) fontStyleId = 0;
	var fontStyle = Styler.getFontStyleById(fontStyleId);
	//alert(fontStyle.toSource());
	object.style.fontFamily = Styler.getFontName(fontStyle.font);
	object.style.fontSize = pt(fontStyle.size);
	window.borrarFont = fontStyle;
	object.style.color = fontStyle.color;
	if(fontStyle.bold)
		object.style.fontWeight = "bold";
	else
		object.style.fontWeight = "normal";

	if(fontStyle.italic)
		object.style.fontStyle = "italic";
	else
		object.style.fontStyle = "normal";

	if(fontStyle.underline)
		object.setTextDecoration("underline");
	else
		object.setTextDecoration("none");
	
	if(fontStyle.align =="general")
		object.style.textAlign = "left";
	else
		object.style.textAlign = fontStyle.align;
	
	object.style.verticalAlign = fontStyle.valign;
	
}

function WrapStyle(obj){
	obj.setTextDecoration = function(value){
		obj.style.textDecoration = value ;
	}
	obj.getTextDecoration = function(){
		return object.style.textDecoration ;
	}

	
    obj.setTop = function(value){
		this.style.top = px(parseInt(value));
	}
	obj.getTop = function(){
		return parseInt(this.style.top);
	}
	obj.setLeft = function(value){
		this.style.left = px(parseInt(value));
	}

	obj.getLeft = function(){
		return parseInt(this.style.left);
	}

	obj.setZIndex = function(value){
		this.style.zIndex = parseInt(value);
	}

	obj.getZIndex = function(){
		return this.style.zIndex;
	}

	obj.setHeight = function(value){
		this.style.height = px(value);
	}

	obj.getHeight = function(){
		return parseInt(this.style.height);
	}

	obj.setWidth = function(value){
		this.style.width = px(value);
	}

	obj.getWidth = function(){
		return parseInt(this.style.width);
	}

	obj.getAbsoluteWidth = function(){
		if(window.isGecko)
			return parseInt(this.style.width) - parseInt(this.style.borderLeftWidth) - parseInt(this.style.borderRightWidth);
		else
			return parseInt(this.style.width) + parseInt(this.style.borderLeftWidth) + parseInt(this.style.borderRightWidth);
	}

	obj.getAbsoluteHeight = function(){
		if(window.isGecko)
			return parseInt(this.style.height) - parseInt(this.style.borderTopWidth) - parseInt(this.style.borderBottomWidth);
		else
			return parseInt(this.style.height) + parseInt(this.style.borderTopWidth) + parseInt(this.style.borderBottomWidth);
	}

	obj.getAbsoluteTop = function(){
		if(window.isGecko)
			return parseInt(this.style.top);
		else
			return parseInt(this.style.top) + parseInt(this.style.borderTopWidth);
	}

	obj.getAbsoluteLeft = function(){
		if(window.isGecko)
			return parseInt(this.style.left);
		else
			return parseInt(this.style.height) + parseInt(this.style.borderLeftWidth);
	}

}