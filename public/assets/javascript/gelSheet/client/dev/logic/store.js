function State(address,property, oldValue, newValue) {
	this.address = address;
	this.property = property ; 
	this.oldValue = oldValue ; 	
	this.newValue = newValue ; 	
	return this  ;
}

function Store() {
	var self = this ;
	
	self.construct = function() {
		this.transactions = new Array() ;
		this.currentId = -1 ;
		this.size = -1; 
	}

	self.beginTransaction = function() {
		this.currentId++;
		this.size = this.currentId;
		this.transactions[this.currentId] = new Array();		
	}
	
	self.add = function ( state ) {
		if(this.transactions[this.currentId]) //TODO: this if should not be needed 
			this.transactions[this.currentId].push(state) ; 
	}
	
	self.rollBack = function () {
		if ( this.currentId > -1) this.currentId-- ; 
	}
	
	self.restore = function () {
		if (this.size > this.currentId) this.currentId++ ; 
	}
	
	self.canRestore = function () {
		return (this.size > this.currentId) ; 
	}
	
	self.getCurrent = function() {
		if ( this.currentId > -1 )
			return this.transactions[this.currentId] ;
		else 
			return new Array() ;
	}
	
	self.construct() ;
	
	return self ;
}
function SimpleStore() {
	var self = this ;
	
	self.construct = function() {
		this.transactions = new Array() ;
		this.currentId = -1 ;
		this.size = -1; 
	}
	
	self.beginTransaction = function() {
		this.currentId++;
		this.size = this.currentId;
		this.transactions[this.currentId] = undefined;		
	}
	
	self.set = function ( state ) {
		this.transactions[this.currentId]= state ; 
	}
	
	self.canRollBack = function () {
		return (this.currentId > -1) ; 
	}
	
	self.rollBack = function (oldValue) {
		if ( this.currentId > -1){
			this.transactions[this.currentId] = oldValue;
			this.currentId-- ; 
		}
	}
	
	self.restore = function (newValue) {
		if (this.size > this.currentId){
			this.currentId++ ; 
			var temp = this.transactions[this.currentId];
			this.transactions[this.currentId] = newValue;
			return temp;
		}
	}
	
	self.canRestore = function () {
		return (this.size > this.currentId) ; 
	}
	
	self.getCurrent = function() {
		if ( this.currentId > -1 )
			return this.transactions[this.currentId] ;
		else 
			return undefined ;
	}
	
	self.construct() ;
	
	return self ;
}
