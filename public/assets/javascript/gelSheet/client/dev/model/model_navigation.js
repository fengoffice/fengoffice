function addModelNavigation(self,grid){
	self.moveUp = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		if(self.activeCell.row > 0){
			self.activeCell.row --;
			self.changeActiveCell();
			self.selection.setSelection(new Range({row:self.activeCell.row,col:self.activeCell.col},{row:self.activeCell.row,col:self.activeCell.col}));
			if(self.viewport.start.row > self.activeCell.row )
//			if(!self.isRangeVisible(self.activeCell.row,self.activeCell.col))
				self.onMove(0,-1);
			self.refresh();
		}
	};

	self.moveDown = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		if(self.activeCell.row < 65000){//TODO:use sheet max
			self.activeCell.row ++;
			self.changeActiveCell();
			self.selection.setSelection(new Range({row:self.activeCell.row,col:self.activeCell.col},{row:self.activeCell.row,col:self.activeCell.col}));
			//if(self.activeCell.row >= grid.getViewport().row   ) //TODO: fix, use self viewport
//			if(!self.isRangeVisible(self.activeCell.row,self.activeCell.col))
			if(self.activeCell.row >= self.viewport.end.row   )
				self.onMove(0,1);
			self.refresh();
		}		
	};

	self.moveLeft = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		if(self.activeCell.col > 0){
			self.activeCell.col --;
			self.changeActiveCell();
			self.selection.setSelection(new Range({row:self.activeCell.row,col:self.activeCell.col},{row:self.activeCell.row,col:self.activeCell.col}));
			//if(self.viewport.start.col > self.activeCell.col )
			if(!self.isRangeVisible(self.activeCell.row,self.activeCell.col))
				self.onMove(-1,0);
			self.refresh();
		}		
	};

	self.moveRight = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		if(self.activeCell.row < 256){ //TODO:use sheet max
			self.activeCell.col ++;
			self.changeActiveCell();
			self.selection.setSelection(new Range({row:self.activeCell.row,col:self.activeCell.col},{row:self.activeCell.row,col:self.activeCell.col}));
			//if(self.activeCell.col >= grid.getViewport().col   ) //TODO: fix, use self viewport
			if(!self.isRangeVisible(self.activeCell.row, self.activeCell.col))
				self.onMove(1,0);
			self.refresh();
		}	
	};

	
	self.pageDown = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		var offset = self.viewport.end.row - self.viewport.start.row;
		if(self.activeCell.row + offset >= self.model.getRowCount()){
			self.activeCell.row = self.model.getRowCount() -1;
			offset = 0;
		}
		
		self.activeCell.row += offset;		
		self.changeActiveCell();
		self.setSelection(new Range(self.activeCell));
		grid.scrollDown(offset);
	};
	
	self.pageUp = function(){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		var offset = self.viewport.start.row - self.viewport.end.row;

		if(self.activeCell.row + offset < 0){
			offset = - self.activeCell.row;
		}
		
		self.activeCell.row += offset;			
		self.changeActiveCell();
		self.setSelection(new Range(self.activeCell));
		grid.scrollDown(offset);
	};
	
	self.goToHome = function(){
		if(self.onSpecialMove) self.onSpecialMove("HOME");
	};

	self.goToName = function(name){
		var range = self.model.getNameAddress(name);
		range = new Range(range.start,range.end);
		
		if(range!=undefined){
			self.selection.setSelection(range);
			self.activeCell = {row:range.start.row,col:range.start.col};
			self.changeActiveCell();
			if(self.isRangeVisible(self.activeCell.row,self.activeCell.col))
				self.drawSelections();
			else{
				self.viewport.start.row = range.start.row;
				self.viewport.start.col = range.start.col;
				self.viewport.end.row = self.viewport.start.row + grid.rows.length-1; //TODO:arreglar acoplamiento
				self.viewport.end.col= self.viewport.start.col + grid.cols.length-1; //TODO: arreglar acoplamiento
				self.refresh();
			}
			
		}
	};
	
	self.goToCell = function(row,col){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		var range = new Range({row:row,col:col});
		self.changeActiveCell({row:row,col:col});
		if (self.isRangeVisible(row,col)){
			
		}
		//var relative = self.getRelativeRange(range);
		self.selection.setSelection(new Range({row:row,col:col},{row:row,col:col}));
		self.refresh();
	};
	
	self.onSpecialMove = function(moveType){
		self.setActiveCellFormula(grid.cellEditor.getValue(),true);
		var offsetX = self.viewport.end.col - self.viewport.start.col;
		var offsetY = self.viewport.end.row - self.viewport.start.row;

		if(moveType == "HOME"){
			self.activeCell.row = 0;
			self.activeCell.col = 0;
			self.viewport.start.col = 0;
			self.viewport.end.col   = offsetX;
			self.viewport.start.row = 0;
			self.viewport.end.row   = offsetY;
			self.selection.setSelection(new Range(self.activeCell,self.activeCell));
		}

		self.refresh();
	};
	
	self.onMove = function(offsetX, offsetY){
		if(offsetY < 0){
			if((self.viewport.start.row + offsetY) >= 0){
				self.viewport.start.row += offsetY;
				self.viewport.end.row += offsetY;
				self.gridPosition.y +=offsetY*18;
			}
		}else{
			if(offsetY > 0){
				self.viewport.start.row += offsetY;
				self.viewport.end.row += offsetY;
				self.gridPosition.y += offsetY*18;
			}
		}

		if(offsetX < 0){
			if((self.viewport.start.col + offsetX) >= 0){
				self.viewport.start.col += offsetX;
				self.viewport.end.col += offsetX;
			}
		}else{
			if(offsetX > 0){
				self.viewport.start.col += offsetX;
				self.viewport.end.col += offsetX;
			}
		}
		
		//grid.updateActiveCell(grid.activeCell.getRow()+self,grid.activeCell.getColumn()+offsetX);
		self.refresh();
	};
	
	grid.onHorizontalScroll = function (left){
		var offset = self.viewport.end.col- self.viewport.start.col;
		self.viewport.start.col = parseInt(left/80);
		self.viewport.end.col = parseInt(left/80) + offset;

		if(self.viewport.end.col*80 + self.scrollPageOffset.x > grid.getWidth()){
			grid.setWidth(grid.getWidth() + self.scrollPageOffset.x);
		}
		
		self.refresh();
	};

	grid.onVerticalScroll = function (top){
		var offset = self.viewport.end.row - self.viewport.start.row;
		self.viewport.start.row = parseInt(top/18);
		grid.adjustViewPortY();
		self.viewport.end.row = self.viewport.start.row +grid.viewport.row;

		if(self.viewport.end.row*18 + self.scrollPageOffset.y > grid.getHeight()){
			grid.setHeight(grid.getHeight() + self.scrollPageOffset.y);
		}
	
		self.refresh();
	};
	
	grid.scrollDown = function (offset){
		var delta = self.viewport.start.row + offset;
		if(delta >= 0){			
			self.viewport.start.row = self.viewport.start.row + offset;
			//self.viewport.end.row =  self.viewport.end.row + offset;
			grid.adjustViewPortY();
			self.viewport.end.row = self.viewport.start.row +grid.viewport.row;
	
			if(self.viewport.end.row*18 + self.scrollPageOffset.y > grid.getHeight()){
				grid.setHeight(grid.getHeight() + self.scrollPageOffset.y);
			}
			self.refresh();
		}
	};
}