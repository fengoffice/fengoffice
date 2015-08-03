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
function Book(name){
	var self = this;
	self.construct = function(name){
		this.id = undefined;
		this.name = name;
		this.sheet = undefined
		//this.sheets = new Array(); //Comming soon array of sheets
	}
	
	self.setSheet = function(sheet){
		this.sheet = sheet;
	}
	
	self.getSheet = function(){
		return this.sheet;
	}
	
	self.setId = function(id){
		this.id = id;
	}

	self.setName = function(name){
		this.name = name;
	}

	self.getId = function(){
		return this.id;
	}

	self.getName = function(){
		return this.name;
	}

	self.getSheetsCount = function(){
		return this.sheets.length;
	}

	self.construct(name);
	return self;
}