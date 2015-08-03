var DIV_ZERO = '#DIV/0!' ;
var NOT_NUM = '#VALUE!' ;
var INVALID = '#VALUE!' ;
var NAME = '#NAME?' ;

/* Param Datatype 
 * Describes function arguments (parameters) definition
 * type : type of param (required)
 * optional  : if param is optional
 * validateFn : validation  (setted on Function class)
 */ 
function Funcion(name, params, callback, description, category) {	
	var self = this ;
	
	self.construct = function ( name, params, callback, description, category ) {
		this.name = name ; 				//Function name 
		this.count = params.length ; 	//Number of params (undefined if variable)
		this.required = 0 ; 			//Number of required params
		this.description = (description) ? description : '' ;
		this.category = (category) ? category : '' ;
		
		for(var i=0; i<params.length;i++){
			var param = params[i] ;
			if(param.optional == true)
				this.required ++ ;
			
			switch(param.type){
				case 'numeric':
					param.validateFn = isNumeric;
				break;
				case 'range':
					param.validateFn = function(value) {return value && value.length } ;
 			}
			
		}
		this.params = params ; //Array of params definitions
		this.callback = callback ; //Function callback that returns value
	}

	self.validate  = function (args) {
		return true ;
		//alert("vAlidate " + args.toSource() );
		var valid = true;
		if (args.length != this.count )  {
			return false ;
		}
		for(var i=0;i<args.length;i++){
			var param = this.params[i];
			valid = valid && param.validateFn(args[i]);
		}
		return valid;
	}
	
	
	self.calc  = function (params) {
		if(self.validate(params))
			return self.callback(params);
		else 
			return INVALID ; 
	}
	
	self.setDescription = function (desc) {
		this.description = desc ;
	}
	self.construct ( name, params, callback,  category, description ) ;
	return self;
}



function FunctionHandler() {
	var self = this ;

	self.construct = function() {
		this.functions = {} ;  
	}
	
	self.add = function(func) {
		this.functions[func.name.toLowerCase()] = func ;// TODO
		
	}
	
	self.get = function(func_name) {
		return this.functions[func_name.toLowerCase()] ;
	}

	self.calc = function(func_name, params) {
		var func = self.get(func_name) ;
		if (func) {
			return func.calc(params) ;
		}else {
			return NAME ; 
		}
	}
	
	self.getFunctionList = function() {
		var result = new Array() ;
		for (var i in  this.functions ) {
			result.push( ["="+this.functions[i].name ,"="+this.functions[i].name ] ) ;
		}
		return result ;
	}
	
	self.getFunctionNameList = function() {
		var result = new Array() ;
		for (var i in  this.functions ) {
			result.push( [this.functions[i].name ,this.functions[i].name , this.functions[i].category , this.functions[i].description ] ) ;
		}
		return result ;
	}
	
	
	self.construct () ;
	return self;
}
    