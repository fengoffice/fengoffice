function isEmpty(variable) {
	if ( variable == undefined ) return true ;
	return  ( variable.length == 0 );
}

function isNumeric(variable) {
	return ( !isEmpty(variable) && !isNaN(variable) ) ;
}

function isArray(a) {
	return Object.prototype.toString.apply(a) === '[object Array]';
}

window.calculator = new FunctionHandler();


calculator.add(	new Funcion ('abs',[{type: 'numeric'}], function(values) {
	return Math.abs(values[0]); 		
},
//Implementation
'math', // Category
'<b>ABS(number)</b><br>Absolute value of a number.' // Description
)); 

calculator.add(	new Funcion ('average',[{type: 'numeric'}], function(values) {
	var value = 0 ;
	var total = 0 ;
	for(var i=0;i<values.length;i++){
		if ( isNumeric( values[i] )  ){
			value += parseFloat(values[i]);
			total++;
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length ; j++ ) {
				if ( isNumeric( values[i][j] )  ) 
					value += parseFloat(values[i][j]) ;
					total++;
			}
		}
	}
	if (total) value = value / total ;
	else value = DIV_ZERO ;
	return value ;
},
//Implementation
'statistical', // Category
'<b>AVERAGE(number1;number2;...)</b><br>Returns the average of a sample.' // Description

)); 


calculator.add(	new Funcion ('count',[{type: 'range'}], function(values) {
	var total = 0 ;
	for(var i=0;i<values.length;i++){
		if ( isNumeric( values[i] )  ){
			total++;
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length ; j++ ) {
				if ( isNumeric( values[i][j] )  ) 
					total++;
			}
		}
	}
	return total ;
},

//Implementation
'statistical', // Category
'<b>COUNT(value1;value2;...)</b><br>Counts how many numbers are in the list of arguments.' // Description
)); 

calculator.add(	new Funcion ('counta',[{type: 'range'}], function(values) {
	var total = 0 ;
	for(var i=0;i<values.length;i++){
		if ( isEmpty( values[i] )  ){
			total++;
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length ; j++ ) {
				if ( !isEmpty( values[i][j] )  ) 
					total++;
			}
		}
	}
	return total ;
},
//Implementation
'statistical', // Category
'<b>COUNTA(value1;value2;...)</b><br>Counts how many values are in the list of arguments.' // Description

));  

calculator.add(	new Funcion ('cos',[{type: 'numeric'}], function(values) {
	return Math.cos(values[0]);
},
//Implementation
'math', // Category
'<b>COS(number)</b><br>Returns the cosine of a number.' // Description

)); 

calculator.add(	new Funcion ('max',[{type: 'range'}], function(values) {
	var value = ( isArray(values[0]) ) ? parseFloat(values[0][0]) : parseFloat(values[0]) ;
	for(var i=0;i<values.length;i++){
		if ( isNumeric( values[i] )  ){
			if ( value < values[i] )
				value = parseFloat(values[i]) ;
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length;j++ ) {
				if ( isNumeric( values[i][j] )  ) 
					if ( value < parseFloat(values[i][j]) ) 
						value = parseFloat(values[i][j]) ;
			}
		}
	}
	return value;
},
//Implementation
'statistical', // Category
'<b>MAX(number1;number2;...)</b><br>Returns the maximun value in a list of arguments.' // Description

)); 

calculator.add(	new Funcion ('maxa',[{type: 'range'}], function(values) {
	return calculator.calc('max', values) ;
},
//Implementation
'statistical', // Category
'<b>MAXA(value1;value2;...)</b><br>Returns the maximun value in a list of arguments. Text is evaluated as zero.' // Description
)); 


calculator.add(	new Funcion ('min',[{type: 'range'}], function(values) {
	var value = ( isArray(values[0]) ) ? parseFloat(values[0][0]) : parseFloat(values[0]) ;
	for(var i=0;i<values.length;i++){
		if ( isNumeric( values[i] )  ){
			if ( value > values[i] )
				value = parseFloat(values[i]) ;
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length;j++ ) {
				if ( isNumeric( values[i][j] )  ) 
					if ( value > parseFloat(values[i][j]) ) 
						value = parseFloat(values[i][j]) ;
			}
		}
	}
	return value;
},
//Implementation
'statistical', // Category
'<b>MIN(number1;number2;...)</b><br>Returns the smallest value in a list of arguments.' // Description
)); 


calculator.add(	new Funcion ('mina',[{type: 'range'}], function(values) {
	return calculator.calc('min', values) ;
},
//Implementation
'math', // Category
'<b>MINA(value1;value2;...)</b><br>Returns the smallest value in a list of arguments. Text is evaluated as zero.' // Description

));


calculator.add(	new Funcion ('product',[{type: 'range'}], function(values) {
	var value = 1 ;
	for(var i=0;i<values.length;i++){
		if ( isNumeric( values[i] )  ){
			value *= parseFloat(values[i]);
		}else if( isArray(values[i]) ){
			for (var j=0; j<values[i].length ; j++ ) {
				if ( isNumeric( values[i][j] )  ) 
					value *= parseFloat(values[i][j]) ; 
			}
		}
	}
	return value;
},
'math', // Category
'<b>PRODUCT(number1;number2;...)</b><br>Multiplies the arguments.' // Description

));



calculator.add(	new Funcion (
	'sum', // Name
	[{type: 'range'}], // Params: array 
	function(values) {
		var value = 0 ;
		for(var i=0;i<values.length;i++){
			if ( isNumeric( values[i] )  ){
				value += parseFloat(values[i]);
			}else if( isArray(values[i]) ){
				for (var j=0; j<values[i].length ; j++ ) {
					if ( isNumeric( values[i][j] )  ) 
						value += parseFloat(values[i][j]) ; 
				}
			}
		}
		return value;
	}, // Implementation
	'math', // Category
	'<b>SUM(number1;number2;...)</b><br>Returns the sum of all arguments.' // Description
));


calculator.add(	new Funcion ('sin',[{type: 'numeric'}], function(values) {
	return Math.sin(values[0]);	
})); 

calculator.add(	new Funcion ('sqrt',[{type: 'numeric'}], function(values) {
	return Math.sqrt(value[0]);
})); 
