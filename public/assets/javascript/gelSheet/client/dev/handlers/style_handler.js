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
function StyleHandler(configs){
	var self = this;

	self.loadDefaultFont = function(fontStyle){
		var defaultFont = new FontStyle(fontStyle.fontId,fontStyle.size,fontStyle.color,fontStyle.bold,fontStyle.italic,fontStyle.underline,fontStyle.align,fontStyle.valign);
		this.fontStyles[defaultFont.id] = defaultFont;
		this.fontsIds[0] = defaultFont;
	}

	self.construct  = function(configs){
		this.fontStyles = new Array();
		this.fontsIds = new Array();
		this.layers = new Array();
		this.loadDefaultFont(configs.defaultFontStyle);
	}

	self.getFontName = function(fontId){
		return window.Fonts[fontId];
	}

	self.getFontStyle = function(styleId){
		var style = this.fontStyles[styleId];
		if(style == undefined) style = this.fontStyles[0];
		return style;
	}

	self.getFontStyleById = function(index){
		var style = this.fontsIds[index];
		if(style == undefined) style = this.fontsIds[0];
		return style;
	}
	
	self.getFontStyleIdByStyle = function(fontStyle){
		return this.getFontStyleId(fontStyle.font, fontStyle.size, fontStyle.color, fontStyle.bold, fontStyle.italic, fontStyle.underline,fontStyle.align,fontStyle.valign);
	}
	
	self.changeFontStyleProp = function(fontStyleId,prop,value){
		var fs = this.getFontStyleById(fontStyleId);
		var oldValue = fs[prop];
		fs[prop] = value;
		var newId = this.getFontStyleId(fs.font, fs.size, fs.color, fs.bold, fs.italic, fs.underline,fs.align,fs.valign);
		fs[prop] = oldValue;
		return newId;
	}

	//Function that should be called only on load of Styles
	self.addFontStyle = function(id, font, size, color, bold, italic, underline,align, valign){
		var fstyle = new FontStyle(font, size, color, bold, italic,underline,align, valign);
		this.fontStyles[fstyle.id] = fstyle;
		this.fontsIds[id] = fstyle;
	}
	
	self.getFontStyleId = function(font, size, color, bold, italic, underline,align, valign){
		var id = font+"|"+size+"|"+color+"|"+bold+"|"+italic+"|"+underline +"|"+align+"|"+valign;
		if(this.fontStyles[id]){
			return this.fontsIds.indexOf(this.fontStyles[id]);
		}else{
			var fstyle = new FontStyle(font, size, color, bold, italic,underline,align, valign);
			this.fontStyles[id] = fstyle;
			var newId = this.fontsIds.length;
			this.fontsIds[newId] = fstyle;
			return newId;
		}
	}
	
	//Export Functions
	self.getAlignName = function(align){
		switch(parseInt(align)){
		case 0:
			return "general";
			break;
		case 1:
			return "left"; 
			break;
		case 2:
			return "center";
			break;
		case 3:
			return "right";
			break;
		default:
			return "general";
			break;
		}
		
	};
	
	self.getAlignId = function(alignId){
		switch(alignId){
		case "general":
			return 0;
			break;
		case "left":
			return 1;
			break;
		case "center":
			return 2;
			break;
		case "right":
			return 3;
			break;
		default:
			return 0;
			break;
		}
		
	};
	
	self.getValignName = function(valign){
		switch(parseInt(valign)){
		case 0:
			return "bottom";
			break;
		case 1:
			return "middle"; 
			break;
		case 2:
			return "top";
			break;
		default:
			return "bottom";
		break;
		}
		
	};
	
	self.getValignId = function(valign){
		switch(valign){
		case "bottom":
			return 0;
			break;
		case "middle":
			return 1;
			break;
		case "top":
			return 2;
			break;
		default:
			return 0;
		break;
		}		
	};
	
	self.getAllFontsStyles = function(){
		return this.fontsIds;
	}
	self.construct(configs);
}

function FontStyle(font, size, color, bold, italic, underline,align, valign){
	var self = this;

	self.construct  = function(font, size, color, bold, italic, underline,align, valign){
		this.id = font+"|"+size+"|"+color+"|"+bold+"|"+italic+"|"+underline +"|"+align+"|"+valign ;
		this.font = font 		//Font Name (Familly) Id
		this.size = size;		//Font Size
		this.color = color;		//Font Color
		this.bold = bold;		//Is Bold?
		this.italic = italic;	//Is Italic?
		this.underline = underline;	//Is Underlined?
		this.align = align;	//Is Italic?
		this.valign = valign;	//Is Underlined?
	}

	self.construct(font, size, color, bold, italic, underline,align, valign);
	return self;
}


function LayoutStyle(bgcolor,border){
	self.contructor = function(){

	}

	return self;
}

function BlockStyle(wrap,valign,halign){
	self.contructor = function(){
		this.id = halign+"|"+valign+"|"+wrap;
		this.wrap = wrap	//Font Name (Familly) Id
		this.valign = valign;		//Font Size
		this.halign = halign;		//Font Color
	}
	
	return self;
}











