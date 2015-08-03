function loadConfigs(){
	var configs = {
		application:{
			titlePrefix: 'GelSheet'
		},
		communication:{
			url: './server/index.php',
			method: 'POST'
		},
		theme:'opengoo',	
		style:{
			defaultFontStyle:{
				fontId: 1,
				size: 10,
				color: "#000000",
				bold: false,
				italic: false,
				underline: false,
				align:'general',
				valign:'bottom'
			}
		},
		grid:{
			height: 500, //will be overridden on inizialation 
			width: 700,  //will be overridden on inizialation
			rowHeader:{
				height: 18,
				width: 20
			},
			colHeader:{
				height: 18,
				width: 80
			},
			scrollbar:{
				height: 16,
				width: 17
			},
			resizeHandler:{size:5}
		},
		sheet:{
			rows: 65536,	//this are Excel 2003 support 
			cols: 256,		//this are Excel 2003 support
			defaultColumnWidth: 80,
			defaultRowHeight: 18
		},
		book:{
			defaultName:'book1',
			defaultSheets:3		//Not working yet ;-)
		}
		
	};
	
	return configs;
}