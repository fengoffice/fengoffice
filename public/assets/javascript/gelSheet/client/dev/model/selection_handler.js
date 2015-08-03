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

/******************* Selection Handler ************************************
**  Handles objects selection.
** 	Objects must implement interface with select() and unselect() methods
***************************************************************************/
function Address(row,column){
	this.row = row;
	this.col = column;
	return this;
}


function SelectionState(currentSelection,selection){
	this.currentSelection = currentSelection;
	this.selection = selection;
	return this;
}

function DataSelectionHandler(){
		var self = this;

		self.construct = function(){
			this.selection = new Array(); //Selection Ranges
			this.currentSelection = undefined;
			this.store = new SimpleStore();
		}

		self.unsetSelection = function(){
			while(this.selection.length>0){
				var item = this.selection.pop();
			}
		}

		self.setSelection = function(range){
			this.unsetSelection();
			this.selection.push(range);
			this.currentSelection = range;
		}

		self.getSelection = function(){
			return this.selection;
		}

		self.getActiveSelection = function(){
			return this.currentSelection;
		}
		
		self.addSelection = function(range){
			this.selection.push(range);
			this.currentSelection = range;
		}
		
		//################## STATE OPERATIONS (For Undo and Redo) ############3
		self.beginTransaction = function(){
			this.store.beginTransaction();
			self.saveState();
		}
		
		self.rollBack = function () {
			if(this.store.canRollBack()){
				var temp = this.store.getCurrent() ;
				this.store.rollBack(self.selection) ;
				self.selection = temp;
				self.currentSelection = self.selection[self.selection.length-1];
			}
		}


		self.restore = function () {
			if ( this.store.canRestore() ) {
				var temp = this.store.restore(self.selection) ; 
				self.selection = temp;
				self.currentSelection = self.selection[self.selection.length-1];
			}  
		}
		
		self.saveState = function(){
			var currentState = new Array() ;
			for(var i = 0; i< self.selection.length;i++)
				currentState.push(self.selection[i].clone());
			self.store.set(currentState);
		}

		self.construct();
		return self;
	}