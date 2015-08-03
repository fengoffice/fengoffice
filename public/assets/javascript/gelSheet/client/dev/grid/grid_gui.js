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

/********************* Grid Structure Overview **************************
*	->GridLayout (DIV)				-- Contains all Grid Components
*		->GridContainer(DIV)		-- Contains Grid Table (Visible Grid)
*			->Grid (Table)			-- Grid Main Structure
*			->GridHeader (THEAD) 	-- Contains Column Header
*			->GridBody (TBODY)  	-- Contains Row Headers and Cells
*				->EditorBox (INPUT)	-- Cell Formula Editor, move from on cell to another when its been selected
*			->SelectorBox (DIV)		-- Box that borders selected range
*				->FillArea (DIV)	-- Tiny Box for copying formulas
*		->SrollBar (DIV)			-- Background Area that handles scroll movement
*		->VerticalResizer (DIV)		-- Vertical Line that appears when a Column is being resized
*		->HorizontalResizer (DIV)	-- Horizontal Line that appears when a Row is being resized
*
****************************************************************************/
function createGridGui(self,width,height){
	
		self.setSize = function(width,height){
			var gridHeight = height - self.configs.scrollbar.height;
	    	var gridWidth = width - self.configs.scrollbar.width;
	    	
	    	self.style.height = px(height);
	        self.style.width = px(width);
	        
	        self.gridContainer.style.width = px(gridWidth);
			self.gridContainer.style.height = px(gridHeight);
			
			self.grid.style.height = px(gridHeight);
			self.grid.style.width = px(gridWidth);
		}
		
    	

		//OverAll Container "Grid"
        //self.id = "Grid";
        self.style.left = "0px";
        self.style.top = "0px";        
        self.style.position = "absolute";
        self.style.overflow = "hidden";
        
        
		//Grid Table Container
		self.gridContainer = document.createElement("DIV");
		self.gridContainer.id = "GridContainer";
		self.gridContainer.style.position = "absolute";
		self.gridContainer.style.top = "1px";
		self.gridContainer.style.left = "0px";
		self.gridContainer.style.overflow = "hidden";
		self.gridContainer.style.zIndex = 10;
		//self.gridContainer.style.backgroundColor = "#00F";
		self.gridContainer.style.backgroundColor = "transparent";
		
		//Table Containing the Visual Grid
		self.grid = document.createElement("TABLE");
		self.grid.id = "Grid";
		self.grid.style.top = px(0);
		self.grid.style.left = px(0);
		self.grid.style.tableLayout = "fixed";
		self.grid.position = "absolute";
		self.grid.style.zIndex = 2; //Over the ScrollBar Area
		self.grid.cellSpacing = 0;

		//Column Header Creation (THEAD)
		self.head = document.createElement("THEAD");
		self.colHeader = new VRow(0);
		self.colHeader.setInnerHTML("");
		self.colHeader.childNodes[0].style.width = "30px";
		self.colHeader.childNodes[0].innerHTML = "";

		//Body of Grid Table (Cells and Rows Headers)
		self.body = document.createElement("TBODY");

		self.scrollbars = new ScrollBar("ScrollBar",self.minDimension.width,self.minDimension.height);
		self.scrollbars.style.zIndex = 1;

		//Add Other Grid Components
		self.cellEditor = new CellEditor();
		self.selectorBox = new SelectorBox();

		
		self.setSize(width,height);
		
		//Add Resizing Managers
		self.verticalResizer = new SizeHandler(true);
		self.verticalResizer.style.left = "100px";

		self.horizontalResizer = new SizeHandler(false);
		self.horizontalResizer.style.top = "100px";

		//Finally make all components visible (appendChild)
		self.head.appendChild(self.colHeader);		//Appends Column Header on Grid Header
		self.grid.appendChild(self.head);			//Appends Grid Header on Grid Table
		self.grid.appendChild(self.body);			//Appends Grid Body on Grid Table

		self.gridContainer.appendChild(self.grid);			//Appends Grid Table on Grid Table Container
		self.gridContainer.appendChild(self.selectorBox);	//Appends Grid SelectorBox on Grid Table Container
		self.appendChild(self.gridContainer);	   	//Appends Grid Container on Grid Main Layer

		self.appendChild(self.scrollbars);			//Appends Grid ScrollBar Area on Grid Main Layer

		self.appendChild(self.verticalResizer);		//Appends Column Resizer on Grid Main Layer
		self.appendChild(self.horizontalResizer);	//Appends Row Resizer on Grid Main Layer

    }