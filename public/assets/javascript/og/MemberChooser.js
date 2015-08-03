og.MemberChooserNode = function (config) {
	self.construct = function(config){}
	self.construct(config);
	return self;
}


/**
 * Member Chooser 
 * @author Pepe
 */
og.MemberChooser = function(config) {
	/*
	var self = this ;
	
	var dimensionId = null ;
	
	var members = [] ;

	var isMultiple = false;
	
	var reloadDimensions = [];
	
	var checkBoxes = true ;
	
	var status = 0 ;
	
	var baseUrl = '' ;
	
	var ajaxUrl = '' ;
	
	var ct = null ;
*/

	var self = this ;
	self.construct = function(config){
		//this.members = [] ;
		this.isMultiple = false ;
		this.baseUrl = config.baseUrl ;
		this.dimensionId = config.dimensionId || "" ;
		this.objectTypeId = config.objectTypeId || "" ;
		this.ajaxUrl = this.baseUrl  
			+'&dimension_id='+this.dimensionId
			+'&object_type_id='+this.objectTypeId+
			+'&checkboxes=true'+
			+'&avoid_session=1';
	};
	
	self.renderMembers = function() {
		var ul = document.createElement('ul');
		var hdr = document.createElement ('div');
		hdr.className = "mc-header" ;
		var txtBox = document.createElement ('input');
		txtBox.type= "text" ;
		hdr.appendChild(txtBox);
		
		for (i in this.members) {
			if ( this.members[i] && typeof(this.members[i]) != 'function'){
				var member = this.members[i] ;
				var li = document.createElement('li');
				var ch = document.createElement('input');
				ch.type = 'checkbox';
				ch.name = 'dimension_members';
				ch.value = member.id ;
				li.appendChild(ch);
				li.innerHTML += "<span>"+member.name+"</span>" ;
				ul.appendChild(li);	
				
			}
		}
		
		this.ct.innerHTML = '' ;
		this.ct.appendChild(hdr);
		this.ct.appendChild(ul);
		$(this.ct).slideDown();
		this.filter("ad");
	};
	
	self.filter = function (text) {
		$(this.ct).find("li:not(:contains("+text+"))").addClass("hidden");
	};
	
	self.render = function(position) {
		if ( !this.status ) {
			var ct = document.createElement("div");
			ct.className = "mc-container";
			ct.style.diplay = "none" ;
			$(ct).offset(position);
			this.ct = ct ;
			setTimeout (function(){
				$('html').click(function() {
					$(".mc-container").slideUp();
				});
				$(ct).click(function(event){
					event.stopPropagation();
				});
			},1000);
		}else{
			$(this.ct).slideDown();
		}
		
		this.status = 1 ;
		document.body.appendChild (ct);
		 Ext.Ajax.request({
             url:self.ajaxUrl,
             success: function(data){
				var response = eval("("+ data.responseText+")");
				self.members = response.dimension_members ;
				self.renderMembers();
			},
            failure: function(data){
				alert("error in response");
			}
         });
		
		
		return ct ;
	};
	
	self.toggle = function (e) {
		position = e.offset();
		position.top = position.top + e.height() + 2; 
		this.render(position) ;
		
	};
	
	self.construct(config);
	return self;
}