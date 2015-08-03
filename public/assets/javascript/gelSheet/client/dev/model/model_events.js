
function ExtendModelEvents(self,grid){
	
	//Capture and Overwrite Grid Events
	grid.on("ActiveCellChange", function(caller,address,value){
		if (self.activeCell.row != address.row || self.activeCell.col != address.col ) { 
			self.setActiveCellFormula(value,true);
			if(address.row!=undefined) address.row += self.viewport.start.row; 
			if(address.col!=undefined) address.col += self.viewport.start.col; 
			self.changeActiveCell(address);
			self.setSelection(new Range(address));
			self.refresh();
		}
	});
	
	grid.on("RowAdded", function(caller,row,size){
		grid.getRow(row).setTitle(this.model.getRowName(this.viewport.start.row + row));
		grid.getRow(row).setSize(this.model.getRowSize(this.viewport.start.row + row));
		
		for(var j =0; j< (this.viewport.end.col - this.viewport.start.col +1);j++){
			var cell = this.model.getCell(this.viewport.start.row + row, this.viewport.start.col+ j);
			self.refreshVCell(row,j);
		}
		//TODO: do something with viewport
	});
	
	grid.on("ColumnAdded", function(caller,col,size){
		grid.getColumn(col).setTitle(this.model.getColumnName(this.viewport.start.col + col));
		grid.getColumn(col).setSize(this.model.getColumnSize(this.viewport.start.col + col));
		
		for(var i =0; i< (this.viewport.end.row - this.viewport.start.row +1);i++){
			var cell = this.model.getCell(this.viewport.start.row + i, this.viewport.start.col+ j);
			self.refreshGridCell(this.viewport.start.row+i,col);
		}
		//TODO: do something with viewport
	});

	//Capture and Overwrite Grid Events
//	grid.onCellFontStyleChange = function(i,j,fsId){
//		if(fsId!=undefined){
//			if(fsId!=self.model.getCellFontStyleId(self.viewport.start.row + i, self.viewport.start.col+ j)){
//				self.model.setCellFontStyleId(self.viewport.start.row + i, self.viewport.start.col+ j,fsId);
//				grid.setFontStyle(i,j,fsId);
//			}
//		}
//	}

	
	grid.cellEditor.on('ValueChanged',function(obj,value){
		self.fire("ActiveCellChanged",value,value,0);
	});
	
	grid.on("RowSizeChanged",function(obj,address,size){
		self.beginTransaction();
		if(self.selection.getActiveSelection().isRow()){
			var selections = self.selection.getSelection(); 
			for(var k=0;k < selections.length;k++){
				var selection = selections[k].normalize();
				for(var i=selection.start.row;i<=selection.end.row;i++)
					self.model.setRowSize(i, size);
			}
		}else
			self.model.setRowSize(address.row + self.viewport.start.row, size);
		
		self.refresh();
	});
	
	
	grid.onColumnSizeChange = function (column){
		self.beginTransaction();
		var size = column.getSize();
		if(self.selection.getActiveSelection().isColumn()){
			var selections = self.selection.getSelection(); 
			for(var k=0;k < selections.length;k++){
				var selection = selections[k].normalize();
				for(var i=selection.start.col;i<=selection.end.col;i++)
					self.model.setColumnSize(i, size);
			}
		}else
			self.model.setColumnSize(column.getIndex()+self.viewport.start.col, size);
		
		self.refresh();
//		grid.adjustViewPortX();
	};

}