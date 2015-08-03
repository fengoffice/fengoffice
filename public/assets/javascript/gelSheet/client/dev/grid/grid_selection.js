function addGridSelectionOperations(grid){
	
	grid.selectRange = function(start,end){
		if(start.row == end.row && start.col==end.col){
			this.selectorBox.fitToRange(this.cells[start.row][start.col]);
			return this.drawSimpleRange(start.row,start.col);
		}
		
		if (start.row < end.row){
			var rowStart = start.row;
			var rowEnd   = end.row;
		}else{
			var rowEnd 	= start.row;
			var rowStart = end.row;
		}
		
		if (start.col < end.col){
			var colStart = start.col;
			var colEnd   = end.col;
		}else{
			var colEnd 	= start.col;
			var colStart = end.col;
		}
		//this.selectorBox.setVisible(false);
		
		for(var i=rowStart;i<=rowEnd;i++)
			for(var j=colStart;j<=colEnd;j++)
				this.selectCell(i,j,true);
		
//		for(var i=rowStart;i<=rowEnd;i++){
//			this.cells[i][colStart].style.borderLeft = "3px solid #000";
//			this.cells[i][colEnd].style.borderRight = "3px solid #000";
//		}
//			
//		for(var j=colStart;j<=colEnd;j++){
//			this.cells[rowStart][j].style.borderTop = "3px solid #000";
//			this.cells[rowEnd][j].style.borderBottom = "3px solid #000";
//		}
		
		
//		var top = this.rows[rowStart].offsetTop;
//		var left = this.cols[colStart].offsetLeft;
//		var height = this.rows[rowEnd+1].offsetTop -top;
//		var width = this.cols[colEnd+1].offsetLeft -left;
//		                                    
//		var area = {
//				top:top,
//				left:left,
//				width:width,
//				height:height
//		};
//		
//		grid.selectorBox.fitToArea(area);
	};
	
	grid.selectColumn = function(index){
//		alert(index);
		this.cols[index].select();
		
		for(var i=0;i<this.rows.length;i++){
			this.rows[i].activate();
		}
	};
	
	grid.selectRow = function(index){
		this.rows[index].select();
		
		for(var i=0;i<this.cols.length;i++){
			this.cols[i].activate();
		}
	};
	
	grid.selectCell = function(row, col,inside){
		try{
		this.rows[row].activate();
		
		this.cols[col].activate();
		this.cells[row][col].select();
		if(!inside)
			this.cells[row][col].style.border = "3px solid #000";
		}catch(e){} //TODO: catch when this is happening
//	}catch(e){alert("row fuera de rango" + row);}
	};
	
	grid.clearSelection = function(){
//		
		//TODO: improve performance by restoring only ranges on selection
		for(var i=0;i<this.cols.length;i++){
			this.cols[i].unselect();
		}
		
		for(var i=0;i<this.rows.length;i++){
			this.rows[i].unselect();
		}
		
		this.clearActiveCell();
	};
	
	grid.drawColumnsSelection = function(start,end){
		 if(start < end)
			 for(var i=start;i<=end;i++)
				 this.selectColumn(i);
		 else
			 for(var i=end;i<=start;i++)
				 this.selectColumn(i);
			 
	};
	
	grid.drawRowsSelection = function(start,end){
		 if(start < end)
			 for(var i=start;i<=end;i++)
				 this.selectRow(i);
		 else
			 for(var i=end;i<=start;i++)
				 this.selectRow(i);
			 
	};
	
	grid.drawCurrentSelection = function(){
		var start = this.selection.start;
		var end = this.selection.end;
		this.clearSelection();
		
//		alert(this.selection.toSource());
		if(end!=undefined){
			if(start.col==undefined){
				 this.drawRowsSelection(start.row,end.row);
				 return;
			}
			
			if(start.row==undefined)
				this.drawColumnsSelection(start.col,end.col);
			else
				this.selectRange(start,end);

		}else
			this.drawSelection(start.row,start.col);
	};
	
	grid.drawSelection = function(start,end){
//		alert("draw "  + start.toSource());
		if(end!=undefined){
			if(start.col==undefined){
				 this.drawRowsSelection(start.row,end.row);
				 return;
			}
			
			if(start.row==undefined)
				this.drawColumnsSelection(start.col,end.col);
			else
				this.selectRange(start,end);

		}else
			this.drawSimpleRange(start.row,start.col);
	};
	
	grid.drawSimpleRange = function(row,col){
		if(row==undefined)
			grid.selectColumn(col);
		else
			if(col==undefined)
				grid.selectRow(row);
			else
				grid.selectCell(row,col,true);
	};
	
	grid.clearActiveCell = function(){
		this.cellEditor.style.visibilty = "hidden";
		this.selectorBox.setVisible(false);
	};
	
	grid.drawActiveCell = function(row,col,value){
		try{
		this.cellEditor.style.visibilty = "visible";
		this.cellEditor.fitToCell(this.cells[row][col]);
		this.cellEditor.setValue(value);
		}catch(e){
			alert(row + ", "+ col + " , " + value );
		}
	};
}
	