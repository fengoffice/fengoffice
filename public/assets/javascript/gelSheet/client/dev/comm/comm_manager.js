function CommHandler(configs){	
	var self = this;
	
	 self.configs = {
			 	url: 'index.php',
				method: "POST"
		    };
		    
    //All thouse properties setted in configs will override defaults
    for(var prop in configs)
    	self.configs[prop] = configs[prop];
    
    
	self.construct = function(){
		Ext.Ajax.on({
				'beforerequest': function(){
				Ext.getBody().mask('Loading...');
			}});
	
		Ext.Ajax.on({
			'requestcomplete': function(){
			Ext.getBody().unmask();
		}});
	}
	
	self.recieveRequest = function(response,param,successFn,failureFn){
		try{
			var data = Ext.util.JSON.decode(response.responseText);
			if(data.success){
				if(successFn)
					successFn(data.data);
			}else{
				if(failureFn)
					failureFn(data.data);
				else
					Ext.MessageBox.show({
						 title: data.type,
						 msg: data.description,
						 buttons: Ext.Msg.OK,
						 icon: Ext.MessageBox.OK
					});
			}
		}catch(e){
			 Ext.MessageBox.show({
                 title: 'Communication Error',
                 msg: 'Bad response format.'+e.toSource(),
                 buttons: Ext.Msg.OK,
                 icon: Ext.MessageBox.ERROR
             });
		}
	}
	
	self.requestFailed = function(response){
		 Ext.MessageBox.show({
             title: 'Communication Error',
             msg: 'Request Failed',
             buttons: Ext.Msg.OK,
             icon: Ext.MessageBox.ERROR
         });
	}
	
	self.sendRequest = function(parameters,successFn,failureFn){
		Ext.Ajax.request({
			method: self.configs.method,
			waitMsg : 'Salvando datos...',
			url: self.configs.url,
			
			success: function(response,param){
				self.recieveRequest(response,param,successFn,failureFn);
			},
			failure: self.requestFailed,
			params: parameters
		});
	}
	
	self.loadBook = function(bookId,callback){
		self.sendRequest({
			c:'Spreadsheet',
			m:'loadBook',
			param1:bookId,
			ogId: window.ogID || 0,
			ogWid: window.ogWID || 0
			},
		callback
		);
	}
	
	self.bookSaveServerResponse = function(data){		
		application.activeBook.setId(data.BookId);
		bookId = application.activeBook.getId();
		parent.og.openLink(parent.og.getUrl('files', 'save_spreadsheet', {
				id: window.ogID || 0,
				book: bookId,
				name: application.activeBook.getName()
			}), {
			onSuccess: function(data) {
				window.ogID = data.sprdID;
			},
			onError: function(data) {
				deleteBook(bookId);
			}
		});
	}

	
	self.sendBook = function(data, format){
		var params = {
				c:'Spreadsheet',
				m:'saveBook',
				param1:escape(data),
				param2:'json',
				param3:'json',
				ogId: window.ogID || 0,
				ogWid: window.ogWID || 0
			};
			
		self.sendRequest(params,self.bookSaveServerResponse);
	};
	
	self.exportBook = function (data,format) {
		//See if the form has been created..
		if ( window.submitForm != undefined ) {
			var form = window.submitForm ; 
		}else {
			var form = document.createElement("FORM") ;
			window.submitForm = form 		;
			form.method = self.configs.method ;
			form.action = self.configs.url 	;
			form.target = "_blank" 			; 
			// Open in a new window !
			
			var inputs = new Array() ;
			for (var i = 0 ; i < 5 ; i++) {
				inputs[i] = document.createElement("INPUT") ;
				inputs[i].type = "hidden" 	;
				form.appendChild(inputs[i]) ;
			}
			document.body.appendChild(form) ;
		}
		
		//Set each input..	
		form.elements[0].name = "c" 			;
		form.elements[0].value = "Spreadsheet"  ;

		form.elements[1].name = "m" 			;
		form.elements[1].value = "saveBook" 	;
		
		form.elements[2].name = "param1" 		;
		form.elements[2].value = escape(data) 	;
		
		form.elements[3].name = "param2" 		;
		form.elements[3].value = "json"  		;
	
		form.elements[4].name = "param3" 		;
		form.elements[4].value = format  		;
		
		form.submit() 							; // Send request
		
	}
	
	self.construct();
	return self;
}