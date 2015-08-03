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
/**
* This function will create events to be overwritten by creator
* The grid will call them when some of then should be fired
*/

function addGridMethods(grid){
	
	grid.selectorBox.on("EditingMode",function(){
		grid.fire("EditingMode",true);
	});
	
	grid.scrollbars.onVerticalScroll = function(top){
		//if(grid.cells.[0][0].offsetTop
		if(grid.onVerticalScroll) grid.onVerticalScroll(parseInt(top));//+parseInt(grid.grid.style.height));
	}

	grid.scrollbars.onHorizontalScroll = function(left){
		//if(grid.cells.[0][0].offsetTop
		if(grid.onHorizontalScroll) grid.onHorizontalScroll(parseInt(left));//+parseInt(grid.grid.style.height));
	}
	
//	self.onmousedown = function(e){
////e ? e : e =window.event; //get event for IE
//////this.selecting = true;
////if(this.onActiveCellChange) this.onActiveCellChange(this.cellEditor);
////return false; 			//Disables Text Selection
//}

	grid.onmouseup = function(e){
		e ? e : e =window.event; //get event for IE
		this.selecting = false;
		this.selectingRow = false;
		this.selectingCol = false;
		
		if(this.columnResizing){
			this.resizeColumn();
		}
		
		if(this.rowResizing){
			this.resizeRow(e.clientY);
		}
		
		this.columnResizing = false;
		this.rowResizing = false;
	}
	
	grid.onmousemove = function(e){
		e ? e : e = window.event; //get event for IE
		if(grid.columnResizing){
			grid.verticalResizer.setLeft(e.clientX);
		}else if(grid.rowResizing){
			grid.horizontalResizer.setTop(e.clientY-59);
		}
	}

}


/**
* Adds events to cells where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/
function addGridCellEvents(grid, cell){
	cell.onmousedown = function(e){
		if(grid.activeCell!== cell){
		    e ? e : e=window.event; //get event for IE
		    grid.selecting = true;
//			grid.setActiveCell(cell);
//			grid.fire("SelectionChange",{start:cell.getAddress()});
		    
		    grid.fire("ActiveCellChange",cell.getAddress(),grid.cellEditor.getValue());
		}
	};

	cell.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		if(grid.selecting)
			var address =  cell.getAddress();
		else if (grid.selectingCol)
			var address = {col:cell.getColumn()};
		if(address!= undefined)
			grid.fire("SelectionChange", undefined, address);
	};
}

/**
* Adds events to Rows where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/
function addGridRowEvents(grid,row){
	row.on('resizemousedown',function(xrow,e){
		grid.rowUsed = row;
		grid.horizontalResizer.setTop(e.clientY-59);
		grid.horizontalResizer.startResizing(e.clientY-59);
		grid.rowResizing = true;
	});

	row.on('mousedown',function(xrow,e){
		if(!grid.rowResizing){
			grid.selectingRow = true;
			grid.fire("SelectionChange",row.getAddress());
		}
	});
	
	row.on('mouseover',function(xrow,e){
		if(grid.selectingRow){
			grid.selection.end = row.getAddress();
			grid.fire("SelectionChange",undefined, row.getAddress());
		}
	});
}

/**
* Adds events to Columns where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/

function addGridColumnEvents(grid,col){
	col.on('resizemousedown',function(xcol,e){
			grid.columnUsed = col;
			grid.verticalResizer.setLeft(e.clientX);
			grid.verticalResizer.startResizing();
			grid.columnResizing = true;
	});

	col.on('mousedown',function(xcol,e){
		if(!grid.columnResizing){
			grid.selectingCol = true;
			grid.fire("SelectionChange",col.getAddress());
		}
	});
	
	col.on('mouseover',function(xcol,e){
		if(grid.selectingCol){
			grid.fire("SelectionChange",undefined, col.getAddress());
		}
	});
}
