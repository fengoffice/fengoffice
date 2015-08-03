/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */
var EVT_CELL_CHANGE = 0; 			//When Cell Formula Changes
var EVT_CELL_FOCUS = 1;	 			//When Cell recieves Focus
var EVT_CELL_FONT_STYLE_CHANGE = 2; //When Cell changes Fonts Style, Param New Font Style ID
var EVT_CELL_EDITING = 6;			// When Cell is being Edited, Param "cellFormula"
var EVT_COLUMN_CHANGE = 3;
var EVT_ROW_CHANGE = 4;

var EVT_SELECTION_CHANGE = 5;

var EVT_BOOK_NAME_CHANGE = 7;


var EVT_COUNT = 8; //TOTAL Events count

function EventHandler(){
	var self = this;

	self.construct = function(){
		this.events = new Array(); //Fake events, internal events
		//Intialize all fake events array (stack of registered functions)
		for(var i=0;i<EVT_COUNT;i++){
			this.events[i] = new Array();
		}
	}


	/**
	* Fires an internal event to all registered callbacks on the event
	**/
	self.fire = function(event,args){
		var registered = this.events[event];
		for(var i=0;i<registered.length;i++){
			try{
				//if(event == EVT_SELECTION_CHANGE)
				//	alert(args);
				registered[i](args);
			}catch(e){alert("event " + event + " i: " + i+ " "  +  e.toSource());}
		}
	}


	/**
	* Register a function for been executed in event
	* returns the id reference of the event for unregister
	**/
	self.register = function(event,callback,fake){
		if(fake){
			this.events[event].push(callback);
		}else
			if(document.body.addEventListener){
				document.body.addEventListener(event,callback,true);
			}else
				document.body.attachEvent("on"+event,callback) //IE
	}

	self.unregister = function(event,callback){
		if(document.body.removeEventListener)
			document.body.removeEventListener(event,callback,false);
		else
			document.body.detachEvent(event,callback) //IE
	}

	self.construct();
	return self;
}