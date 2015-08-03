function Clipboard(){
	var self = this;
	self.consturct = function(){
		self.sourceAddress = undefined;
		self.range = undefined;
	};
	
	self.clear = function(){
		delete self.sourceAddress;
		delete self.range;
	};
	
	self.add = function(range){
		self.range = range;
	};
	
	self.construct();
	return self;
}