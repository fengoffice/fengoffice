function addDataModelSelection(self,grid){
	grid.on("SelectionChange", function(caller,start,end){
		//Updates current Edited Value
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		
		var absRange = self.getAbsoluteRange(new Range(start,end));
		
		if(start == undefined) //Current Selection is changing
			self.selection.getActiveSelection().end = absRange.end;
		else
			self.selection.setSelection(absRange);
		
		var activeCell = self.selection.getActiveSelection().start;
		if(end == undefined)
			activeCell = {row:absRange.start.row,col:absRange.start.col};
		
		if(activeCell.row == undefined) //if a Column is selected
			activeCell = {row:self.viewport.start.row,col:activeCell.col};
		
		if(activeCell.col == undefined) //if a Row is selected
			activeCell = {row:activeCell.row,col:self.viewport.start.col};
		
		self.changeActiveCell(activeCell);
//		alert(active.toSource());
		self.fire("SelectionChanged",self.model.getRangeName(self.selection.getActiveSelection().clone()));
		self.drawSelections();
	});
	
	self.getVisibleRange = function(range){
		var result = range.clone();
		result.normalize();
		
		if(result.end.row < self.viewport.start.row)
			return undefined;
		
		if(result.end.col < self.viewport.start.col)
			return undefined;
		
		if(result.start.row < self.viewport.start.row)
			result.start.row = self.viewport.start.row;
		
		if(result.start.col < self.viewport.start.col)
			result.start.col = self.viewport.start.col;
		
		if(result.end.row > self.viewport.end.row)
			result.end.row = self.viewport.end.row;
		
		if(result.end.col > self.viewport.end.col)
			result.end.col = self.viewport.end.col;
		
		return result;
	};
	
	self.drawSelections = function(){
		grid.clearSelection();
		var selection = self.selection.getSelection();
		for(var i=0;i<selection.length;i++){
			var range = self.getVisibleRange(selection[i]);
			if(range!=undefined){
				range = self.getRelativeRange(range);
				grid.drawSelection(range.start,range.end);
			}
		}
		
		if(self.isRangeVisible(this.activeCell.row,this.activeCell.col)){
			grid.drawActiveCell(this.activeCell.row - self.viewport.start.row,
				this.activeCell.col - self.viewport.start.col,
				this.model.getFormula(this.activeCell.row,this.activeCell.col));
		}
	};
	

}