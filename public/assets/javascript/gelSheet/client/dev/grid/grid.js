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

function Grid(configs){
    var self = document.createElement("DIV");
    //Now the defaults properties are set
    self.configs = {
		height:500,
		width:700,
		rowHeader:{height:18,width:20},
		colHeader:{height:18,width: 80},
		scrollbar:{height:16,width:17},
		resizeHandler:{size:5}
    };
    
    //All thouse properties set in configs will override defaults
    for(var prop in configs)
    	self.configs[prop] = configs[prop];
    
    WrapEvents(self);
    self.register('ColumnSizeChange');
    self.register('ColumnFormatChange');

    self.register('RowSizeChanged');    
    self.register('SelectionChange');

    self.register('CellValueChange');
    self.register('ActiveCellChange');
    
    self.register('RowAdded');
    self.register('ColumnAdded');
    self.register('EditingMode');

//	self.focusActiveCell = function(){
//		this.cellEditor.fitToCell(this.activeCell);
////		this.selectorBox.fitToRange(this.activeCell);
//	}
//	
//	self.updateActiveCell =function(row,col){
//		this.setActiveCell(this.cell[row][col]);
//	}
//	
//	self.setActiveCell = function(cell){
//		self.fire("CellValueChange",this.activeCell.getAddress(),this.cellEditor.getValue());
////		if(this.onCellValueChange) this.onCellValueChange(this.activeCell.getRow(),this.activeCell.getColumn(),this.cellEditor.getValue());
////		if(this.onCellFontStyleChange) this.onCellFontStyleChange(this.activeCell.getRow(),this.activeCell.getColumn(),this.cellEditor.getFontStyleId());
//		this.rows[this.activeCell.getRow()].deactivate();
//		this.cols[this.activeCell.getColumn()].deactivate();
//
////		this.selectionManager.setSelection(cell);
//		this.rows[cell.row].activate();
//		this.cols[cell.column].activate();
//		this.activeCell = cell;
//		this.focusActiveCell();
//		//if(this.onActiveCellChange) this.onActiveCellChange(this.cellEditor);
//	}
//	
//	self.editActiveCell = function(newValue){
//		this.cellEditor.updateValue(newValue);
//	}
//	

	self.addRow = function(passive){
		//Creates Visual Row
		var row = new VRow(this.rows.length);
		row.setHeight(this.configs.rowHeader.height);
		//Adds VRow to grids Rows
		var i = this.rows.push(row)-1;
		this.cells[i] = new Array();

		//Adds a new VCell for each column in Grid
		for(var j=0;j<this.cols.length;j++){
        	var cell = new VCell(i,j);
        	//cell.innerHTML = i + ", " + j;
   			this.cols[j].addCell(cell);
        	row.addCell(cell);
        	this.cells[i][j] = cell;
        	//Add events to new VCell
        	addGridCellEvents(self,cell);
        }
        //Overwrites Row events
		addGridRowEvents(self,row);
		//Finally add Row to Browser
   		this.body.appendChild(row);
//   		if(!passive) self.fire("RowAdded",row.getIndex(),row.getHeight());
	};


	self.addColumn = function(){
		var column = new VColumn(this.cols.length);
		column.setHeight(this.configs.rowHeader.height);
		column.setWidth(this.configs.colHeader.width);
		
		var idx = this.cols.push(column);
		addGridColumnEvents(self,column);

		for(var i=0;i<this.rows.length;i++){
        	var cell = new VCell(i,idx);
        	//cell.innerHTML = i;
        	this.rows[i].addCell(cell);
        	this.cells[i].push(cell);
        	column.addCell(cell);
        	addGridCellEvents(self,cell);
        }
   		this.colHeader.appendChild(column);
	};

	self.adjustViewPortX = function(){
		if(this.viewport.col >= this.cols.length)
			this.viewport.col = this.cols.length -1;

		var width = parseInt(this.offsetWidth);
		if(this.cols[this.viewport.col].offsetLeft >= width){
			for(var j = this.viewport.col; this.cols[j].offsetLeft > width; j--)
				this.viewport.col = j-1;

		}else{
			for(var j = this.viewport.col-1;(this.cols[j].offsetLeft + this.cols[j].offsetWidth) < width;j++)
				this.viewport.col = j + 1;
		}
	};

	self.adjustViewPortY = function(){
		if(this.viewport.row >= this.rows.length)
			this.viewport.row = this.rows.length -1;

		var height = parseInt(this.style.height);

		if(this.rows[this.viewport.row].offsetTop > height){
			for(var i = this.viewport.row; this.rows[i].offsetTop >= height; i--)
				this.viewport.row = i-1;
		}else{
			try{for(var i = this.viewport.row; (i<this.rows.length) && (this.rows[i].offsetTop + this.rows[i].offsetHeight) <= height;i++)
				this.viewport.row = i;
				}catch(e){}
		}
	};
	self.adjustViewPort = function(){
		self.adjustViewPortX();
		self.adjustViewPortY();
	};

	self.setDimensions = function(width,height){
		this.scrollbars.setHeight(height);
		this.scrollbars.setWidth(width);
	};

	self.getMinHeight = function(){
		return this.minDimension.height;
	};

	self.getHeight = function(){
		return this.scrollbars.getHeight();
	};

	self.getVisibleHeight = function(){
		return parseInt(this.grid.style.height);
	};

	self.setHeight = function(height){
		this.scrollbars.setHeight(height);
	};

	self.getVisibleWidth = function(){
		return parseInt(this.grid.style.width);
	};

	self.getMinWidth = function(){
		return this.minDimension.width;
	};

	self.getWidth = function(){
		return this.scrollbars.getWidth();
	};

	self.setWidth = function(width){
		this.scrollbars.setWidth(width);
	};

    self.construct = function(){
    	var width = this.configs.width;
    	var height = this.configs.height;
    	
	//Private Attributes definitions
		//Data Attributes
    	this.cols = new Array();
        this.rows = new Array();
        this.cells = new Array();

		//Events Handling Flags
		this.selecting = false;
		this.selectingRow = false;
		this.selectingCol = false;
		this.columnResizing = false;
		this.rowResizing = false;
		
		this.selection = {start:{row:0,col:0},end:undefined};
		
    	//Temporal References
		this.columnUsed = undefined; // used for maintain a reference for resizing

		//Main Properties Setup
    	var gridHeight = height - this.configs.scrollbar.height;
    	var gridWidth = width - this.configs.scrollbar.width;

    	var ncols = (gridWidth - this.configs.rowHeader.width)/ this.configs.colHeader.width;
    	var nrows = (gridHeight - this.configs.rowHeader.height)/ this.configs.rowHeader.height;

		//Visual Properties
    	this.viewport = {row:parseInt(nrows),col:parseInt(ncols)};
		this.minDimension = {width:width*2 , height:height*2};

		createGridGui(self,width,height);

		//Disable Text Selection
	//	document.onselectstart = function() {return false;}; // For IE

		//Grid Columns and Rows Creation
		for(var j=0;j<ncols;j++){
			self.addColumn();
		}

		for(var i=0;i<nrows;i++){
			self.addRow();
		}
    };
    
    self.adjustGrid = function(width,height){
    	if(width!=undefined && height !=undefined){
    		while(this.rows[this.viewport.row].offsetTop < height){
    			self.addRow(true);
    			this.viewport.row++;
    		}
    		
    		while(this.cols[this.viewport.col].offsetLeft < width){
    			self.addColumn(true);
    			this.viewport.col++;
    		}
    		self.adjustViewPort();
    	}
    };
    
	self.inicialize = function(){
		this.adjustViewPort();
	};

	self.resize = function(width,height){
		self.adjustGrid(width,height);
		self.setSize(width,height);
//		self.gridContainer.parentNode.style.width = px(width);
//		self.gridContainer.parentNode.style.height = px(height);
//		self.gridContainer.style.width = px(width);
//		self.gridContainer.style.height = px(height);
//		self.setDimensions(parseInt(width),parseInt(height));
	};
	
    self.construct();

	addGridOperations(self);
	addGridMethods(self);
	addGridSelectionOperations(self);
    return self;
}

