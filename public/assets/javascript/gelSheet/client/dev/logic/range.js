//Range Type Cell Row or Column
var RNG_CELL 	= 0 ;
var RNG_ROW 	= 1 ;
var RNG_COLUMN 	= 2 ;

/**
 * Creates a new Range defined by 2 addresses {row,col}
 * Should be used as a Datatype
 * @param start Address
 * @param end	Address
 * @return new Range
 */

function Range(start,end){
	var self = this;
	
	this.start = start;
	this.end = end;
	
	self.isRow = function(){
		return this.start.col == undefined;
	};
	
	self.isColumn = function(){
		return this.start.row == undefined;
	};
	
	self.isCell = function(){
		return (this.start.row != undefined) && (this.start.row != undefined);
	}
	
	self.getType = function(){
		if(this.start.row == undefined)
			return RNG_COLUMN;
		else
			if(this.start.col == undefined)
				return RNG_ROW;
			else
				return RNG_CELL;
	};
	
	self.add = function(row,col){
		if(this.start != undefined){
			if(this.start.row != undefined)
				this.start.row +=row;
		
			if(this.start.col != undefined)
				this.start.col +=col;
		}
		
		if(this.end != undefined){
			if(this.end.row != undefined)
				this.end.row +=row;
		
			if(this.end.col != undefined)
				this.end.col +=col;
		}
		return this;
	};
	
	self.sub = function(row,col){
		if(this.start != undefined){
			if(this.start.row != undefined)
				this.start.row -=row;
		
			if(this.start.col != undefined)
				this.start.col -=col;
		}
		
		if(this.end != undefined){
			if(this.end.row != undefined)
				this.end.row -=row;
		
			if(this.end.col != undefined)
				this.end.col -=col;
		}
		return this;
	};
	
	self.clone = function(){
		if(this.end!=undefined)
			return new Range({row:this.start.row,col:this.start.col},{row:this.end.row,col:this.end.col});
		else 
			return new Range({row:this.start.row,col:this.start.col});
	};
	
	self.addCells = function(cells){
		self.cells = cells;
		
		//If cells added they can be accessed by function cells(row,col)
		self.cells = function(row,col){
			return self.cells[row + self.start.row][col + self.start.col];
		};
	}
		
	/**
	 * Adjust a Range to have a start and an end.
	 * Orders it: starting row < ending row; same for column
	 */
	self.normalize = function(){
		if(this.end == undefined){
			this.end = {};
			this.end.row = this.start.row;
			this.end.col = this.start.col;
		}else{
			if(this.start.row > this.end.row){
				var temp = this.start.row;
				this.start.row = this.end.row;
				this.end.row = temp;
			}
			
			if(this.start.col > this.end.col){
				var temp = this.start.col;
				this.start.col = this.end.col;
				this.end.col = temp;
			}
				
		}
		return self;
	};
	
	self.addressInside = function(row,col){
		self.normalize();
		return (row >= self.start.row && row <= self.end.row) 
			&& (col >= self.start.col && col <= self.end.col); 
	};
	
	return self;
}