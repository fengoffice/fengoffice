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

var TYPE_GENERAL 	= 0; // Equals unspecified
var TYPE_ERROR	 	= 4;
var TYPE_STRING 	= 1;
var TYPE_NUMBER	 	= 2;
var TYPE_LOGIC	 	= 3;

//return true iif is a numeric type and its a valid number
function isNumeric(value){ 
	return  !isNaN(Number(value));
};


function Cell(row, column){
	var self = this;

	self.construct = function(row, column){
		this.row 	= row;
		this.column = column;
		this.formula = undefined;
		this.value = undefined;
		this.formatedValue = undefined;		
		this.decimals = undefined;
		//Set default Styles
		this.type = TYPE_GENERAL; //Specified Type by users (not necessarily the same as taken)
		this.valueType = TYPE_GENERAL; //Cell Value Type, this is the taken type as a result of defined type and the value of the cell
		this.fontStyleId = 0;
		this.layerStyleId = 0;
		this.references = new Array() ;
	}

	/**
	 * Determinates the taken type of the cell in function of the specified type (type) and the
	 * Cell value. 
	 * XE: value ='01234' and type = 'TYPE_GENERAL' => 'TYPE_NUMBER' with value '1234'
	 * XE: value ='01234' and type = 'TYPE_STRING'  => 'TYPE_STRING' with value '01234'
	 */
	self.processType = function(){
		if(this.value == undefined)
			this.valueType = this.type;
		else{
			if(this.type == TYPE_STRING)
				this.valueType = TYPE_STRING; //Always its the same
			else{
				if(isNumeric(this.value))
					this.valueType = TYPE_NUMBER;
				else{
					var strValue = this.value.toUpperCase();
					if(strValue=="TRUE" || strValue=="FALSE")
						this.valueType = TYPE_LOGIC;
					else
						if(strValue[0] == "#"){
							if ((",#NULL!,#DIV/0!,#VALUE!,#REF!,#NAME?,#NUM!,#N/A,").indexOf("," + strValue + ",") != -1) {
								this.valueType = TYPE_ERROR;
							}else
								this.valueType = TYPE_STRING;
						}else
							this.valueType = TYPE_STRING; 
				}
			}
		}
	}
	
	
	//return true iif is a numeric type and its a valid number
	self.isNumeric = function(){ 
		return (this.valueType == TYPE_NUMBER);
	};
	
	self.setType = function(type){
		this.type = type;
		self.processType();
	};
	
	self.getType = function(){
		return this.type;
	};
	
	self.getValueType = function(){
		return this.valueType;
	};
	
	self.getValueTypeName = function(){
		switch( self.valueType){
		case TYPE_ERROR:
			return "ERROR";
		case TYPE_NUMBER:
			return "NUMBER";
		case TYPE_LOGIC:
			return "LOGICAL";
		case TYPE_STRING:
			return "TEXT";
		default:
			return "GENERAL";
		}
	};
	
	self.deleteContents = function(){
		self.clearReferences();
		self.formula = self.value = self.formattedValue = undefined;
	};
	
	self.calculate = function(){
		if(this.formula!=undefined)
			if(this.formula.charAt(0)=="="){
				var ref = this.formula.substr(1);
				//this.value =(document.getElementById(ref)).value;
				this.value = ref;
			}else{
				this.value = this.formula;
			}
	}
	
	self.addReference = function(reference){
		this.references.push(reference) ;
	}
	
	
	self.clearReferences = function(reference){
		delete this.references  ;
		this.references = new Array() ;
	}
	
	self.getReferences = function() {
		return this.references ;
	}
	
	//Style Functions
	self.getFontStyleId = function(){
		return this.fontStyleId;
	}

	self.setFontStyleId = function(fontStyleId){
		this.fontStyleId = fontStyleId;
		//alert ("en cel" + this.fontStyleId);
	}

	self.getLayerStyleId = function(){
		return this.layerStyleId;
	}

	self.setLayerStyleId = function(layerStyleId){
		this.layerStyleId = layerStyleId;
	}


	//Contents Functions
	self.getValue = function(){
		return this.value;
	}

	self.getRawValue = function(){
		return this.value;
	}
	
	self.getFormattedValue = function(){
		return this.formattedValue;
	}

	self.getFormula = function(){
		return this.formula;
	}

	self.setFormula = function(value){
		self.formula = value;
		self.calculate();
		self.formatValue();
	}

	self.setDecimals = function(decimals){
		if(decimals != undefined)
			self.decimals = Number(decimals);
		else
			decimals == undefined;
		self.formatValue(); 
	};
	
	self.getDecimals = function(){
		return self.decimals;
	};
	
	self.formatValue = function(){
		if(self.valueType == TYPE_NUMBER && self.decimals != undefined){
			self.formattedValue = Number(self.value).toFixed(self.decimals);
		}else
			self.formattedValue = self.value;
	};
	
	self.getFormattedValue = function(){
		return self.formattedValue;
	};
	
	self.setValue = function(value){
		this.value = value;
		try{
			this.processType();
			this.formatValue();
		}catch(e){
			alert(e.toSource());
		}
		//this.calculate;
	}

	self.getRow = function(){
		return this.row;
	}

	self.getColumn = function(){
		return this.column;
	}

	self.construct(row, column);

	return self;
}

