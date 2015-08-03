function addApplicationAPI(self){

	self.focusActiveCell = function() {
		document.getElementById('ActiveCell').focus() ;
	}
	
    self.editActiveCell = function(value){
    	self.model.editActiveCell(value);
    };
    self.getActiveCellValue = function(){
    	//alert(self.grid.getActiveCellValue());
    	
    	return self.grid.getActiveCellValue();
    };
    
    self.increaseDecimals = function(){
    	self.model.increaseDecimals();
    };
    
    self.decreaseDecimals = function(){
    	self.model.decreaseDecimals();
    };
    
    self.deleteSelection = function(){
    	self.model.deleteSelection() ;
    };
    
    self.bookLoaded = function(responseData){
    	var book = self.JsonManager.importBook(self.configs.sheet,responseData);

    	self.activeBook = book;
    	self.activeSheet = book.getSheet();
    	self.setBookName(book.name); //doing this will refresh application title
    	self.model.setDataModel(self.activeSheet);
    	self.model.refresh();
    };
    
    self.loadBook = function(bookId){
    	self.CommManager.loadBook(bookId,self.bookLoaded);
    };
    
    self.setBookName = function(bookName){
		self.activeBook.setName(bookName);
		document.title = self.configs.application.titlePrefix +" - " + bookName;
	};

    /**
     * Save As..
     */
	self.saveBook = function(bookName) {
		
		var bookId = "null";
		 
		if(bookName == undefined) { //SAVE.. 
			if(window.ogID) {
				bookName = self.activeBook.getName();
			} else {
				saveBookConfirm();
				return;
			}
			var id = self.activeBook.getId();
		}else {
			// SAVE AS  posta.. deberia no pasar 'id' al request a opengoo
			window.ogID = null ;
		}
		if(bookName == undefined) bookName = self.activeBook.getName();
		self.setBookName(bookName);
		var json = JsonManager.exportBook(id,self.activeBook,self.activeSheet); //on the future will not be needed to pass activeSheet
	    self.CommManager.sendBook(json, 'json');
	};
	
	self.exportBook = function(format){
		var json = JsonManager.exportBook(self.activeBook.getId(),self.activeBook,self.activeSheet); //on the future will not be needed to pass activeSheet
	    self.CommManager.exportBook(json, format);
	};
	
	self.newBook = function(){
		Ext.MessageBox.show({
			 title: lang('New_Book_Dialog_Title'),
			 msg: lang('New_Book_Dialog_Text') + "<br>" + lang('Do_you_want_to_continue'),
			 buttons: Ext.Msg.YESNOCANCEL,
			 icon: Ext.MessageBox.OK,
			 fn: function(btn){
				if(btn == 'yes'){
					self.activeBook = new Book(self.configs.book.defaultName);
					self.activeSheet = new Sheet(self.configs);
					self.setBookName(self.configs.book.defaultName);
					
					window.FormulaBar.setValue("") ;				
					//self.model = new GridModel(self.grid) ; //Do not clean cells
					self.model.setDataModel(self.activeSheet);
					self.model.goToHome();
					self.grid.reset(); 
					self.model.refresh();
					
					window.ogID = undefined; //if integrated, reset ogId
				}
			 }
		});
		
	};
	
	self.openFiles  = function(data){
		if(!self.openFileDialog)
			self.openFileDialog = new OpenFileDialog(50,50,300,300);
		for(var i=0 ;i < data.files.length;i++){
			self.openFileDialog.addFile(data.files[i]);
		}
		self.container.appendChild(self.openFileDialog);
	};
	
	self.switchViewMode = function(viewMode){
		self.model.changeViewMode(viewMode);
	};
	
	self.refresh=function () {
		self.model.refresh();
	};
	
	self.undo = function(){
		self.model.undo();
	};
	
	self.redo = function(){
		self.model.redo();
	};

}