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
function Sheet(configs){
	var self = this;

	self.getHeight = function(){
		return this.size.height;
	}

	self.getWidth = function(){
		return this.size.width;
	}
	
	self.getColumnCount = function(){
		return self.maxRange.col;
	};
	
	self.getRowCount = function(){
		return self.maxRange.row;
	};

	//Pre: Row[index] doesn't exists
	self.addRow = function(index){
		//Updates Sheet Height
		if(index > this.rows.length){ //TODO: check index > maxrange
			var offset = index - this.rows.length;
			this.size.height += this.defaultRowHeight*offset;
		}
		//Create new Row
		this.rows[index] = new Row(index);
		this.cells[index] = new Array();
		return this.rows[index];
	}

	//Pre: Column[index] doesn't exists
	self.addColumn = function(index){
		//Updates Sheet Height
		if(index > this.cols.length){ //TODO: check index > maxrange
			var offset = index - this.cols.length;
			this.size.width += configs.defaultColumnHeight*offset;
		}
		//Create new Column
		this.cols[index] = new Column(index);
		return this.cols[index];
	}

	self.addCell = function(row,col){
		if(this.rows[row]==undefined)
			this.addRow(row);
		
		if(this.cols[col]==undefined)
			this.addColumn(col);
		
		this.cells[row][col] = new Cell(row,col);
		
		return this.cells[row][col];
	}
	
	self.deleteCell = function(row,col){
		if(this.cells[row]!=undefined)
			this.cells[row][col] = undefined;
	}

	self.construct = function(configs){
		this.cells = new Array();
		this.rows = new Array();
		this.cols = new Array();

		this.namespace = new NameHandler(); //TODO: move to Book
		this.maxRange = {row:configs.rows,col:configs.cols};
		this.size = {height:0,width:0};
		this.store = new Store();// For the Control Zeta
	}

	self.beginTransaction = function () {
		this.store.beginTransaction() ;
	}

		
	self.rollBack = function () {
		var currentState = this.store.getCurrent() ;
		this.store.rollBack() ;

		for ( var i=0 ; i< currentState.length; i++  ) {
			var state = currentState[i] ;
			switch (state.property) {
				case 'formula' :
					this.setFormula(state.address.row, state.address.col, state.oldValue,true) ;
				break;
				case 'fstyle' : 
					this.setCellFontStyleId(state.address.row, state.address.col, state.oldValue,true) ;
				break;
				case 'decimal' : 
					this.setDecimals(state.address.row, state.address.col, state.oldValue,true) ;
					break;
				case 'size':
					if(state.address.row == undefined)
						this.setColumnSize(state.address.col,state.oldValue,true);
					else
						this.setRowSize(state.address.row,state.oldValue,true);
				break;
			}  
		} 
	}


	self.restore = function () { //TODO Eliminar codigo repetido
		if ( this.store.canRestore() ) {
			this.store.restore() ; 
			var currentState = this.store.getCurrent() ;
		
			for ( var i=0 ; i< currentState.length; i++  ) {
				var state = currentState[i] ;
				switch (state.property) {
					case 'formula' :
						this.setFormula(state.address.row, state.address.col, state.newValue,true) ; 
					break; 
					case 'fstyle' :
						this.setCellFontStyleId(state.address.row, state.address.col, state.newValue,true) ; 
					break;
					case 'decimal' :
						this.setDecimals(state.address.row, state.address.col, state.newValue,true) ; 
						break;
					case 'size':
						if(state.address.row == undefined)
							this.setColumnSize(state.address.col,state.newValue,true);
						else{
							
							this.setRowSize(state.address.row,state.newValue,true);
						}
					break;
				}  
			} 
		}  
	}
	
	/************* ModelData Interface Implementation ********************/
	self.getRowIndexByPosition = function(top){
		return parseInt(top/configs.defaultRowHeight);
	}

	self.getRowSize = function(row){
		if(this.rows[row])
			return this.rows[row].getSize();
		else
			return configs.defaultRowHeight; //TODO: use default configs
	}

	self.setRowSize = function(row,size,dontStore){
		var previousSize = 0;
		if(this.rows[row]==undefined)
			this.addRow(row);
		
		if(dontStore == undefined){
			var state = new State({row:row},'size',this.rows[row].getSize(),size) ;
			this.store.add(state);
		}
		var previousSize = this.rows[row].getSize(); 
		this.rows[row].setSize(size);
		
		//Adjust Sheet Height
		this.size.height += size - previousSize;
	}

	self.getColumnSize = function(column){
		if(this.cols[column])
			return this.cols[column].getSize();
		else
			return configs.defaultColumnWidth; //TODO: use default configs
	}


	self.setColumnSize = function(column,size,dontStore){
		if(this.cols[column]==undefined)
			this.addColumn(column);//this.cols[column] = new Column(column);

		if(dontStore == undefined){
			var state = new State({col:column},'size',this.cols[column].getSize(),size) ;
			this.store.add(state);
		}
		
		this.cols[column].setSize(size);
	}

	self.getColumnName = function(column){
		return this.namespace.getColumnName(column);
	}

	self.getRowName = function(row){
		return row+1;
	}


	self.getValue = function(row,column){
		if(this.cells[row])
			if(this.cells[row][column])
				return (this.cells[row][column]).getValue();
			else
				return undefined;
		else
			return undefined;
	}

	self.setValue = function(row,column,value){
		if(this.cells[row]==undefined)
			this.addCell(row,column);
		else
			if(this.cells[row][column] == undefined){
				this.addCell(row,column);
			}

		this.cells[row][column].setValue(value);
	}
	
	self.deleteRowValues = function(row){		
		if(self.rows[row]){			
			for(var i in self.cells[row]){
				if (i != 'remove')
					self.setFormula(row,i,undefined);
			}						
		}		
	}
	
	
	self.deleteColValues = function(column){
		if(self.rows){			
			for(var row in self.rows){				
				if (row != 'remove'){
					if (self.cells[row][column])
						self.setFormula(row,column,undefined);
				}
			}
		}
	}

	
	self.clearCellReferences = function(row,col){
		if(self.cells[row])
			if(self.cells[row][col])
				self.cells[row][col].clearReferences();
	}
	
	self.getCellReferences = function(row,col){
		if(self.cells[row])
			if(self.cells[row][col])
				return self.cells[row][col].getReferences();
	}

	
	self.checkCircularReferences = function(row,col,range){
		//First check that range doesn't have the cell(row,col)
		//If cell's inside range must throw error 300 --Palillo
		try {
			if(range.addressInside(row,col)){
				//return true;
				self.deleteCell(row, col);
				throw(new Error(300,""));
			}
		}catch(e){
			e.description += "<br>Address: " + self.getRangeName(new Range({row:i,col:j})) + " Formula: " + self.getFormula(i,j);
			throw (e);			
		}
		
		//Check that address isn't inside references in cells inside the range
		for(var i=range.start.row; i <= range.end.row;i++)
			for(var j=range.start.col; j <= range.end.col;j++){
				var refs = self.getCellReferences(i,j);
				if(refs != undefined){
					for(var r=0;r < refs.length;r++)
						try{
							if (self.checkCircularReferences(row,col,refs[r]))
								throw(new Error(300,""));
						}catch(e){
							e.description += "<br>Address: " + self.getRangeName(new Range({row:i,col:j})) + " Formula: " + self.getFormula(i,j);
							throw (e);
						}
				}
			}
			return false;
	}
	
	/**
	 * passive : dont touch references
	 */
	self.calculate = function (formula,row,col,passive) {
				
		var tokens = parseFormula(formula) ;//
		var result = null ;
		var strtoeval = '' ;
		var current_args = new Array();
		var current_func = null ;
		var current_prefix = "";
		var func_stack = new Array();
		
		var cell = self.cells[row][col];
		
		if(passive == undefined){
			cell.clearReferences();
			try{	
				References.clearReferences({row:row,col:col});
			}catch(e){
				//alert(e.toSource());
				}
		}
				
		while (tokens.moveNext()) {
    		var token = tokens.current();
    		   		
    		switch (token.type) {
    			case 'operator-prefix':
    				current_prefix = token.value ;
    				break;
				case 'operator-infix':
					strtoeval += token.value ;
				case 'operand' :
					switch (token.subtype) {
						case 'number' :
							if (current_func != undefined) {								
								current_args.push(current_prefix + token.value) ;
								current_prefix = "";
								//strtoeval += calculator.calc(current_func,token.value) ;
							}else {								
								strtoeval += current_prefix + token.value ;
								current_prefix = "";
							}
						break ;
						case 'text' :
							if (current_func != undefined) {
								current_args.push(current_prefix + token.value) ;
								current_prefix = "";
								//strtoeval += calculator.calc(current_func,token.value) ;
							}else {
								strtoeval += "'" +current_prefix + token.value + "'" ;
//								
								current_prefix = "";
							}
						break ;
						case 'range' :
		    				var range = this.namespace.getNameAddress(token.value) ;
		    				//TODO: check when range is not valid => range = undefined
		    				range.normalize();
		    				
		    				if(passive==undefined){
			    				try{
			    					self.checkCircularReferences(row,col,range);
			    				}catch(e){
			    					e.description = "Circular Reference Detected<br>Address: " + self.getRangeName(new Range({row:row,col:col})) + " Formula: " + formula +  e.description;
			    					throw(e);
			    				}
			    				
			    				cell.addReference(range);		    				
			    				try{
			    					References.addReference(range,{row:row,col:col});
								}catch(e){
									//alert(e.toSource());
									}
		    				}
		    				
		    				if (range != undefined ) {
								var values = new Array() ;
	    						for ( var i = range.start.row ; i <= range.end.row; i++ ) {
	    							for ( var j = range.start.col ; j <= range.end.col; j++ ) {
	    							    var value = this.getValue(i,j) ;
	    							    
	    							    if(typeof value == 'string')
	    							    	value = "'"+value+"'"; 
	    							    		    							    	
	    								if ( value != undefined ) values.push( value ) ;
	    							}
	    						}
	    						//strtoeval += calculator.calc(current_func,values) ;
	    						if (current_func != undefined) {
									current_args.push(values) ;
									current_prefix = "";
									//strtoeval += calculator.calc(current_func,token.value) ;
								}else {
									strtoeval += values ;//									
									current_prefix = "";
								}
	    						
			    			}
						break;						
					}				
				break;    		
    		
    			case 'function' :
    				if (token.subtype == 'start') {
    					if(current_func!=undefined){
	    					var old_func = {args:current_args,func:current_func};
	    					func_stack.push(old_func);
    					}
    					current_args = new Array() ;
    					current_func = token.value  ;
    				}else {
    					//stop
    					
    					var value = calculator.calc(current_func,current_args);
    					var current = func_stack.pop() ;
    					if(current==undefined)
    						strtoeval += calculator.calc(current_func,current_args) ;
    					else{
    						current.args.push(value);
    						current_func = current.func;
    						current_args = current.args;
    					}
    					
    				}
    			break ;
    			case 'subexpression' :
					if (token.subtype == 'start') {
						strtoeval += "(" ;
					}else {
						strtoeval += ")" ;
					}
				break; 
    		}
		}
				
		try {

		 	result = eval(strtoeval);
		 	
		}catch (e) {
			result =  INVALID;
		}
		
//		if(passive==undefined)
			//alert("refs: " + row + ":"  + col + " => " + References.getReferenced({row:row,col:col}).toSource());
		return result ;
	}

	self.setFormula = function(row,column,value,dontStore,passive){
		if(value == "") value = undefined;
		
		if(self.cells[row]==undefined)
			self.addCell(row,column);
		else
			if(self.cells[row][column] == undefined){
				self.addCell(row,column);
			} 
		
		if(dontStore == undefined){
			var state = new State({row:row, col:column},'formula',this.cells[row][column].getFormula(),value) ;
			this.store.add(state);
		}

		if(value != undefined){
			this.cells[row][column].setFormula(value);
			if (value != undefined  ) {		
				if (value.length) {
					if  ( (value[0] == '=') || (value[0] == '+') || (value[0] == '-') || isNumeric(value))  {
						
	//					this.changeCellFontStyleProp(row,column,'align','right'); //set alignment right for numbers
						
						var result= this.calculate(value, row, column,passive);
						
						if (result == 0)
							this.cells[row][column].setValue("0");									
						else
							this.cells[row][column].setValue(result);					
	//						this.cells[row][column].setValue(this.calculate(value,row,column));					
	//					this.cells[row][column].setValue(this.calculate(value));
					}
					else {
						this.cells[row][column].setValue(value) ;
					}
				}
			}else
				this.cells[row][column].setValue(value) ;
		}else
			this.cells[row][column].deleteContents();
		
		//TODO: this should not be done here
		if(this.cells[row][column].isNumeric())
			this.changeCellFontStyleProp(row,column,'align','right',dontStore); //set alignment right for numbers
		else
			this.changeCellFontStyleProp(row,column,'align','left',dontStore); //set alignment right for numbers
		
		self.updateReferences({row:row,col:column});
	}
	
	self.updateReferences = function(address){
		var references = References.getReferenced(address);
		if(references.length) //for ie
			for(var ref in references){
				if(ref!='remove'){
					var c = references[ref];
					this.setFormula(c.row,c.col,this.getFormula(c.row,c.col),undefined,true);
				}
			}
	}

	self.getFormula = function(row,column){
		if(this.cells[row])
			if(this.cells[row][column])
				return (this.cells[row][column]).getFormula();
			else
				return undefined;
		else
			return undefined;
	}
	
	self.setDecimals = function(row,col,decimals,dontStore){
		if(self.cells[row] == undefined)
			self.addCell(row,col);
		else
			if(self.cells[row][col] == undefined)
				self.addCell(row,col);
		
		if(dontStore == undefined){
			var state = new State({row:row,col:col},'decimal',self.cells[row][col].getDecimals(),decimals) ;
			self.store.add(state);
		}
		self.cells[row][col].setDecimals(decimals);
	};
	
	//Returns the number of decimals of a cell, null if is not defined
	self.getDecimals = function(row,col){
		if(self.cells[row] != undefined)
			if(self.cells[row][col] != undefined)
				return self.cells[row][col].getDecimals();
	};

	/**############# END ModelData Interface Implementation ##################*/

    //row must not be null
	self.setRow = function(index,row){
		this.rows[index] = row;
	};

	self.getRow = function(index){
		return this.rows[index];
	};

    //column must not be null
	self.setRow = function(index,column){
		this.cols[index] = column;
	};
	
	self.getColumn = function(index){
		return this.rows[index];
	};

	self.setCell = function(row,column,formula,style){
		if(this.cells[row] ==undefined)
			this.cells[row] = new Array();

		if(this.cells[row][column] == undefined)
			this.cells[row][column] = new Cell(row,column);

		this.cells[row][column].setFormula(formula);
	};

	self.getCell = function(row,column){
		//if(row >= this.cells.length)
		//	return undefined;
		if(this.cells[row])
			return this.cells[row][column];
		else
			return undefined;
	};

	self.createEmptyCell = function(row,column){
		var cell = new Cell(row,column);
		cell.isEmpty = true;
		return cell;
	};
	
	self.cloneRange = function (range){
		range.normalize();
		var clone = range.clone();
		clone.addCells(self.cells);
//		
//		for(var i = range.start.row;i<=range.end.row;i++)
//			for(var j = range.start.col;j<=range.end.col;j++){
//				
//			}
		
		return clone;
	};
	
	//Name Handling Operations
	self.getRangeName = function(range){
		return self.namespace.getRangeName(range);
	};
	
	self.addName = function(name,range){
		return self.namespace.addName(name,range);
	};
	
	self.deleteName = function(name){
		self.namespace.deleteName(name);
	};
	
	self.existsName = function(name){
		return self.namespace.existsName(name);
	};
	
	self.getNameAddress = function(name){
		return self.namespace.getNameAddress(name);
	};

	self.getNames = function(){		
		return self.namespace.getNames();
	};
	
	self.construct(configs);
	addSheetStyleOperations(self);

	return self;
}


