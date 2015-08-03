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
/*Key Pressed 	Javascript Key Code */
var CH_BACKSPACE= 8;
var CH_TAB= 9;
var CH_ENTER= 13;
var CH_SHIFT= 16;
var CH_CTRL= 17;
var CH_ALT= 18;
var CH_PAUSE_BREAK= 19;
var CH_CAPS_LOCK= 20;
var CH_ESCAPE= 27;
var CH_PAGE_UP= 33;
var CH_PAGE_DOWN= 34;
var CH_END= 35;
var CH_HOME= 36;
var CH_LEFT_ARROW= 37;
var CH_UP_ARROW= 38;
var CH_RIGHT_ARROW= 39;
var CH_DOWN_ARROW= 40;
var CH_INSERT= 45;
var CH_DELETE= 46;
var CH_0= 48;
var CH_1= 49;
var CH_2= 50;
var CH_3= 51;
var CH_4= 52;
var CH_5= 53;
var CH_6= 54;
var CH_7= 55;
var CH_8= 56;
var CH_9= 57;
var CH_A= 65;
var CH_B= 66;
var CH_C= 67;
var CH_D= 68;
var CH_E= 69;
var CH_F= 70;
var CH_G= 71;
var CH_H= 72;
var CH_I= 73;
var CH_J= 74;
var CH_K= 75;
var CH_L= 76;
var CH_M= 77;
var CH_N= 78;
var CH_O= 79;
var CH_P= 80;
var CH_Q= 81;
var CH_R= 82;
var CH_S= 83;
var CH_T= 84;
var CH_U= 85;
var CH_V= 86;
var CH_W= 87;
var CH_X= 88;
var CH_Y= 89;
var CH_Z= 90;
var CH_LEFT_WINDOW_KEY= 91;
var CH_RIGHT_WINDOW_KEY= 92;
var CH_SELECT_KEY= 93;
var CH_NUMPAD_0= 96;
var CH_NUMPAD_1= 97;
var CH_NUMPAD_2= 98;
var CH_NUMPAD_3= 99;
var CH_NUMPAD_4= 100;
var CH_NUMPAD_5= 101;
var CH_NUMPAD_6= 102;
var CH_NUMPAD_7= 103;
var CH_NUMPAD_8= 104;
var CH_NUMPAD_9= 105;
var CH_MULTIPLY= 106;
var CH_ADD= 107;
var CH_SUBTRACT= 109;
var CH_DECIMAL_POINT= 110;
var CH_DIVIDE= 111;
var CH_F1= 112;
var CH_F2= 113;
var CH_F3= 114;
var CH_F4= 115;
var CH_F5= 116;
var CH_F6= 117;
var CH_F7= 118;
var CH_F8= 119;
var CH_F9= 120;
var CH_F10= 121;
var CH_F11= 122;
var CH_F12= 123;
var CH_NUM_LOCK= 144;
var CH_SCROLL_LOCK= 145;
var CH_SEMICOLON= 186;
var CH_EQUAL_SIGN= 187;
var CH_COMMA= 188;
var CH_DASH= 189;
var CH_PERIOD= 190;
var CH_FORWARD_SLASH= 191;
var CH_GRAVE_ACCENT= 192;
var CH_OPEN_BRACKET= 219;
var CH_BACK_SLASH= 220;
var CH_CLOSE_BRAKET= 221;
var CH_SINGLE_QUOTE= 222;

var CH_SHIFT = 1000;
var CH_ALT = 2000;
var CH_CTRL = 4000;

/* Alt + Ctrl Combination not supported yet*/

function KeyAction(propagate, callback){
	var self = this;
	self.construct = function(propagate,callback){
		this.propagate = propagate;
		this.run = callback;
	};

	self.construct(propagate,callback);
	return self;
}

function KeyHandler(){
	var self = this;
	self.construct = function(){		
		this.callbacks = Array();
	};

	self.addAction = function(callback,propagate, keycode){
		if(keycode!=undefined){
			var item = new KeyAction(propagate, callback);
			this.callbacks[keycode]= item;
		}else{
			alert("Invalid action configuration. Keycode must be defined");
		}
	}
	self.runAction = function(keycode){
		if(keycode!=undefined){			
			var action = this.callbacks[keycode];
			
			if(action==undefined)
				return true;
			else{
				if(action.run) action.run();
				return action.propagate;
			}
		}else{
			alert("Invalid action configuration. Keycode must be defined");
		}
		return true;
	}
	self.keyHandler = function(e){
		e ? e : e =window.event; //get event for IE
	 	/*var pressedKey;
	 	if (document.all)	{ e = window.event; }
	 	if (document.layers || e.which) { pressedKey = e.which; }
	 	if (document.all)	{ pressedKey = e.keyCode; }
	 	alert(pressedKey +"ctrl1"+ pressedKey.ctrlKey);
	 	pressedCharacter = String.fromCharCode(pressedKey);
	 	alert(' Character = ' + pressedCharacter + ' [Decimal value = ' + pressedKey + ']');
	 	*/
		
		var propagate = true;	
		var targ = e ? e : window.event;
		key = targ.keyCode ? targ.keyCode : targ.charCode;
		
		if(targ.ctrlKey) 	key += CH_CTRL;
		if(targ.altKey)  	key += CH_ALT;
		if(targ.shiftKey)  	key += CH_SHIFT;
		propagate = self.runAction(key);
		
		if(!propagate){
			if (e.stopPropagation) 		
				e.stopPropagation();
			else 
				e.cancelBubble = true;
			
		} 
		return propagate;
	}

	self.construct();
	return self;
}
