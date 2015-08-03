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
function NameHandler(){
	var self = this;
	
	self.getRangeCells = function(row,col){
		if(parseInt(row) > self.maxRows)
			return undefined;
		
		col = (col=="")? undefined:this.getColumnIndex(col);
		if(col!=undefined)
			if(col > self.maxCols)
				return undefined;
		
		row = (row=="")? undefined:row-1;
		return {row:row,col:col};
	};
	
	self.getSimpleRangeAddress = function (address){
		var regArray = /^([A-Z]*)(\d*)$/.exec(address);
		if(regArray)
			return this.getRangeCells(regArray[2],regArray[1]);
	};
	
	self.getRangeAddress = function(address){
		address = address.toUpperCase();
		var ranges = address.split(":");
		var range = new Range();
		if(ranges.length >2)
			return undefined;
		
		if(ranges.length){
			range.start = this.getSimpleRangeAddress(ranges[0]);
			if(range.start==undefined) //Handles that previous declaration has a correct address 
				return undefined;
			if(ranges.length>1){
				range.end = this.getSimpleRangeAddress(ranges[1]);
				if(range.end==undefined)//Handles that previous declaration has a correct address 
					return undefined;
			}
		}else{
			range.start = this.getSimpleRangeAddress(address);
			if(range.start==undefined)//Handles that previous declaration has a correct address 
				return undefined;
		}
		return range;
	};
	
	self.getNameAddress = function(name){
		if(self.names[name]!=undefined)
			return self.names[name];
		else
			return self.getRangeAddress(name);
	};
	
	self.getRangeName = function(range){
		range.normalize();
		if(range.isColumn())
			return self.getColumnName(range.start.col) + ":" + self.getColumnName(range.end.col);
		else
			if(range.isRow())
				return (range.start.row +1) + ":" + (range.end.row +1);
			else{
				var name = self.getColumnName(range.start.col)+(range.start.row +1);
				if((range.start.row != range.end.row) || (range.start.col != range.end.col))
					name += ":" + self.getColumnName(range.end.col) + (range.end.row +1);
				return name;
			}
	}
	
	self.existsName = function(name){
		if(self.getRangeAddress(name)!=undefined)
			return true;
		return (self.names[name] != undefined);
	};
	
	self.getRangeFromName = function(name){
		return self.names[name];
	};
	
	self.addName = function(name,range){
		if(self.getRangeAddress(name)== undefined){
			range.normalize();
			self.names[name]= range;
//			alert("nombre agregado " + name + self.names[name].toSource());
			return true; //if added
		}else
			return false; //Not added, its a Valid Range
	};
	
	self.deleteName = function(name){
		self.names[name]= undefined;
	};
	
	self.getColumnName = function(index){
//		if(index > this.maxCols) Should not never get here
//			return null;
		
		var base = this.columnSequence.length;
		var name = "";

		while(index>=0){
			name = this.columnSequence[parseInt(index)%base]+ name;
			index = parseInt(index /base) -1;
		}

		return name;
	};
	
	self.getColumnIndex = function(name){
		var base = this.columnSequence.length;
		var index = 0;
		len = 0;
		
		while(len<name.length){
			index = index*base +1+parseInt(this.columnIndexes[name[len]]);
			len++;
		}

		return index -1;
	};

	self.getNames = function(){
		var names = new Array();
		for(var name in self.names){
			if(name!="remove")
				names.push([name,self.getRangeName(self.names[name])])
		}
		return names;
	}
	
	self.construct = function(){
		this.names = new Array();
		this.maxRows = 65536;
		this.maxCols = 256;
		//this.maxColName = self.getColumnName(self.maxCols); //max column name, avoids calculation 
		
		this.columnSequence = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		this.columnIndexes = new Object();
		for(var i=0;i<this.columnSequence.length;i++){
			this.columnIndexes[this.columnSequence[i]] = i;
		}
	};
	
	self.construct();
	
	window.Names = self.names; //TODO:Borrar ya
	return self;
}

