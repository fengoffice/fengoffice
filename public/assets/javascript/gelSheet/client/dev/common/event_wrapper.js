function WrapEvents(obj){
	obj.events = new Array();
	
	obj.register = function(eventName){
		this.events[eventName] = new Array();
	}
	
	obj.on = function(eventName,callback){
		if(this.events[eventName])
			this.events[eventName].push(callback);
	}
	
	obj.fire = function(eventName){
		
		var eventStack = this.events[eventName];
		if(eventStack)
			for(var i=0;i<eventStack.length;i++)
				switch (arguments.length){
				case 1:
					eventStack[i](obj);
					break;
				case 2:
					eventStack[i](obj,arguments[1]);
					break;
				case 3:
					eventStack[i](obj,arguments[1],arguments[2]);
					break;
				case 4:
					eventStack[i](obj,arguments[1],arguments[2],arguments[3]);
					break;
				}				
	}
}