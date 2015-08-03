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
function fontColorPalette() {
	//alert ("palete");
	var cp ;
	var container = document.getElementById('colorPalette') ;
		
	if ( !window.colorPaletteActive ) {
		cp = new Ext.ColorPalette({value:'993300'});  // initial selected color
		window.colorPaletteActive = true;
		container.style.visibility = 'visible' ;
		container.style.top = '25px' ;
		container.style.left = '289px' ;
		container.style.borderStyle = 'solid';
		container.style.borderWidth = '1px' ;
		cp.render('colorPalette');
		
		cp.on('select', function(palette, selColor){
			window.colorPaletteActive = false ;
			cp.hide();
		    container.style.visibility = 'hidden';
		    alert("colorPalette "+ selColor);
		    cmdSetFontColor("#"+selColor);
		});
	}else {
	}	
}


function bgColorPalette() {
	//alert ("palete");
	var cp ;
	var container = document.getElementById('colorPalette') ;
		
	if ( !window.colorPaletteActive ) {
		cp = new Ext.ColorPalette({value:'FFFFFF'});  // initial selected color
		window.colorPaletteActive = true;
		container.style.visibility = 'visible' ;
		container.style.top = '25px' ; //
		container.style.left = '313px' ;
		container.style.borderStyle = 'solid';
		container.style.borderWidth = '1px' ;
		cp.render('colorPalette');
		
		cp.on('select', function(palette, selColor){
			window.colorPaletteActive = false ;
			cp.hide();
		    container.style.visibility = 'hidden';
		    cmdSetBgColor("#"+selColor);
		});
	}else {
	}	
}