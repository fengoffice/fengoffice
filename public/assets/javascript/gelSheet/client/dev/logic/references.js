//TODO: make a better implementation of structure used 
function ReferenceHandler() {
	var self = this ;
	
	self.construct = function () {
		this.targets = {} ; //[row,col,ROW,COL]->array
//		this.sources = new Array() ; //[x,y]->
	}
	
	self.clearReferences = function(source){
		
		for ( i in this.targets ) {
			for (j in this.targets[i]) {
				for(k in this.targets[i][j]) {
					for (l in this.targets[i][j][k]) {
							var sources = this.targets[i][j][k][l];
							for(var m =0; m < sources.length; m++){
								var ref = sources[m];
//								alert(ref.toSource());
								if(ref != undefined)
									if(ref != 'remove')
										if(ref.col==source.col && ref.row==source.row){
//											alert("borra " + source.row + " "+ source.col);
											delete this.targets[i][j][k][l][m];
											
										}
							}
						
					}
				}
			}
		}
	}
	
	/**
	 * target: refers to a reference that has source
	 * source: range who has the target in reference 
	 */
	self.addReference = function (target, source) {
		//alert("el target start es= " + target.start.toSource());
		var end = (target.end)?target.end:target.start; 

//		if ( this.sources[source.row] == undefined ) 
//			this.sources[source.row] = new Array() ;
//		
		
//		if(this.sources[source.row][source.col])
//			delete this.sources[source.row][source.col];
		
//		if(this.sources[source.row][source.col]==undefined)
//			this.sources[source.row][source.col] = new Array();
//		
//		this.sources[source.row][source.col].push(target)
//			this.clearReference(source);		
		
//		this.sources[source.row][source.col]= (target);
//		alert(this.sources[source.row][source.col].toSource());
		
					
		if ( this.targets[target.start.row]	== undefined ) {
			this.targets[target.start.row] = {} ;
		}		
		if ( this.targets[target.start.row][target.start.col]	== undefined ) {
			this.targets[target.start.row][target.start.col] = {};
		}		
		if ( this.targets[target.start.row][target.start.col][end.row]	== undefined ) {
			this.targets[target.start.row][target.start.col][end.row] = {};
		}		
		if ( this.targets[target.start.row][target.start.col][end.row][end.col]	== undefined ) {
			this.targets[target.start.row][target.start.col][end.row][end.col] = new Array() ;
		}	
		
		this.targets[target.start.row][target.start.col][end.row][end.col].push(source) ;
		
		//alert(target.toSource());
		//alert(this.targets[target.start.row][target.start.col][end.row][end.col].toSource());
		
	}	
	
	self.getReferenced = function (source) {
		// a partir de una celda sacar todas la funciones que referencian a rengos que contienen a esa celda
		var references = new Array() ;
		var row = source.row ;
		var col = source.col ;
		
		for ( i in this.targets ) {
			for (j in this.targets[i]) {
				for(k in this.targets[i][j]) {
					for (l in this.targets[i][j][k]) {
						if (row <= k && row >=i && col >= j && col <= l) {
							for(ref in this.targets[i][j][k][l]){
								if(ref != 'remove')
									references.push(this.targets[i][j][k][l][ref]);
							}
						}
					}
				}
			}
		}
		
		//alert(this.sources.toSource());
//		for ( i in this.sources ) {
//			for ( j in this.sources[i] ) {
//				if(j != 'remove'){
//					var target = this.sources[i][j];
//					if(target.start){
//					
//						//alert(row  + " " + col + " " + target.toSource());
//						var end = (target.end)? target.end : target.start; 
//						
//						if (row <= end.row && row >= target.start.row && col >= end.col  && col <= target.start.col) {
//							//alert("entra");
//							references.push({row:i,col:j});
//						}
//					}
//				}
//			}
//		}
		
		return references ;
	}
	
	self.construct() ;
	return self ;
}

window.References = new ReferenceHandler();