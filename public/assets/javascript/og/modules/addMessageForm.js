

App.modules.addMessageForm = {
   
	/**
   * Click on company checkbox in email notification box. If checkbox is checked
   * all company members need to be checked. If not all members are unchecked
   *
   * @param integer company_id Company ID
   */
  emailNotifyClickCompany: function(company_id, genid, div_id, type) {
	if (type == 'notification'){
  		var cos = Ext.getDom(genid + div_id).notify_companies;
  	}else if (type == 'invitation'){  		
  		var cos = Ext.getDom(genid + div_id).invite_companies;  		
  	}else{  		
  		return;
  	}  	
  	var company_details = cos['company_' + company_id]; // get company details from hash
    if(!company_details)return;
    var company_checkbox = Ext.getDom(genid + company_details.checkbox_id);
    for(var i = 0; i < company_details.users.length; i++) {
      //Ext.getDom(genid + company_details.users[i].checkbox_id).checked = company_checkbox.checked;
      userDiv = Ext.getDom('div'+genid + company_details.users[i].checkbox_id);
      og.checkUser(userDiv);
    } // if
  }, // emailNotifyClickCompany
  
  /**
   * Click on company member. If all members are checked company should be checked too,
   * false othervise
   *
   * @param integer company_id
   * @param integer user_id
   */
  emailNotifyClickUser: function(company_id, user_id, genid, div_id, type) {
  	if (type == 'notification')
  		var cos = Ext.getDom(genid + div_id).notify_companies;
  	else if (type == 'invitation')
  		var cos = Ext.getDom(genid + div_id).invite_companies;
  	else return;
    var company_details = cos['company_' + company_id]; // get company details from hash
    if(!company_details) return;
    
    // If we have all users checked check company box, else uncheck it... Simple :)
    var all_users_checked = true;
    for(var i = 0; i < company_details.users.length; i++) {
      if(!Ext.getDom(genid + company_details.users[i].checkbox_id).checked) all_users_checked = false;
    } // if
    
    Ext.getDom(genid + company_details.checkbox_id).checked = all_users_checked;
  }, // emailNotifyClickUser
  
  /**
   * Returned all checked user ids as a CSV string.
   *
   * @param integer company_id Company ID
   */
  getCheckedUsers: function(genid) {
  	var ids = '', ret = '';
  	var orig_subs_input = document.getElementById(genid + "original_subscribers");
	if (orig_subs_input) {
		ids = orig_subs_input.value;
	}
  	var notcomp = Ext.getDom(genid + 'notify_companies');
  	if (notcomp) {
  		var companies_divs = new Array();  		
  		for (var i=0;i<notcomp.childNodes.length;i++){
  			if (notcomp.childNodes[i].id){
  				companies_divs.push(notcomp.childNodes[i]);
  			}
  		}
  		for (var x=0; x<companies_divs.length;x++){
	  		var users_div = companies_divs[x];
	  		if (! users_div){
	  			return null;
	  		}
	  			var p = '';
	  			var usrs_div;
	  			for (var n = 0;n<users_div.childNodes.length;n++){	  				
	  				if (users_div.childNodes[n].id){	  					
	  					var pos = users_div.childNodes[n].id.length;  					
	  					var t = users_div.childNodes[n].id.substring(pos - 13); 
		  				if( t == 'company_users')
		  				{
		  					usrs_div = (users_div.childNodes[n]);
		  				}
	  				}
	  			}  			
	  			for (var d=0;d<usrs_div.childNodes.length;d++){
	  				if (usrs_div.childNodes[d].id)
	  				{  		  					
	  					var div_user = document.getElementById(usrs_div.childNodes[d].id);
	  					var user = document.getElementById(usrs_div.childNodes[d].id.substr(3));
						if(div_user.className == 'container-div checked-user'){						
							ret += (user.name.substring(17,user.name.length-1)) + ', ';
						}  					
	  					
	  				}
	  			} 
	  	}  		
  		return ret;  		
  	}
    return ids;
  } // emailNotifyClickCompany

};




