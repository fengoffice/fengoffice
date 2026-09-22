ogMemberCache = {};

//after load all root members in og.dimensions for a dim put the dim id here
ogMemberCache.dimensions_root_members = new Array();

ogMemberCache.addDimToDimRootMembers = function(dim_id){
	if(!ogMemberCache.areDimRootMembersLoaded(dim_id)){
		ogMemberCache.dimensions_root_members.push(dim_id);
	}	
}

ogMemberCache.areDimRootMembersLoaded = function(dim_id){
	if(ogMemberCache.dimensions_root_members.indexOf(dim_id) != -1){
		return true;
	}	
	return false;
}

ogMemberCache.reset_dimensions_cache = function(){
	og.dimensions = {};
	og._memberDimIndex = {};
	og.dimensions_check_date = new Date();
	ogMemberCache.dimensions_root_members.length = 0;
}

/**
 * Remove specific members from the cache so they will be re-fetched on next access.
 * Advances the check date so the server knows we are up to date.
 * @param {Array} member_ids Array of member IDs to invalidate
 */
ogMemberCache.invalidateMembersInCache = function(member_ids) {
	for (var i = 0; i < member_ids.length; i++) {
		var mem_id = member_ids[i];
		for (var did in og.dimensions) {
			if (og.dimensions[did] && og.dimensions[did][mem_id]) {
				delete og.dimensions[did][mem_id];
			}
		}
	}
	og.dimensions_check_date = new Date();
}

/*
 *This function search a member on og.dimensions and if is not there get it from the server. 
 *This function return an array with the member and it parents if you set include_parents. If the member is not found return an empty array.
 * you have to pass a function to execute after the member is found on the server.
 *@param mem_id	 required
 *@param include_parents  defualt value false
 *@param func_callback function to execute after found the member it must have 3 params (dimension_id ,member, callback_extra_params) (callback_extra_params is an object)
 *@return array with the member and it parents if you set include_parents. If the member is not found return an empty array.
 * */
og.getMemberFromOgDimensions = function(mem_id, include_parents, func_callback, callback_extra_params) {
	if (isNaN(mem_id)) return false;
	var members = [];
	if (typeof include_parents === 'undefined') include_parents = false;

	// Fast path: use inverted index if available.
	var _fastDid = og._memberDimIndex && og._memberDimIndex[mem_id];
	if (_fastDid && og.dimensions && og.dimensions[_fastDid]) {
		var _fastMember = og.dimensions[_fastDid][mem_id];
		if (typeof _fastMember !== 'undefined') {
			members.push(_fastMember);
			if (include_parents) {
				var _cur = _fastMember;
				while (_cur && _cur.parent && _cur.parent > 0) {
					_cur = og.dimensions[_fastDid][_cur.parent];
					if (_cur) members.push(_cur);
				}
			}
			return members;
		}
	}

	// Slow fallback: scan all dimensions (handles members added before index existed).
	for (var _did in og.dimensions_info) {
		if (isNaN(_did)) continue;
		if (og.dimensions && og.dimensions[_did]) {
			var _member = og.dimensions[_did][mem_id];
			if (typeof _member !== 'undefined') {
				members.push(_member);
				if (!include_parents) {
					return members;
				} else {
					var _p = _member;
					while (_p && _p.parent && _p.parent > 0) {
						_p = og.dimensions[_did][_p.parent];
						if (_p) members.push(_p);
					}
					return members;
				}
			}
		}
	}

	if (members.length <= 0) {
		og.getMemberFromServer(mem_id, func_callback, callback_extra_params);
	}
	return members;
};

/*
 *This function get a member from the server and all parents. 
 *@param mem_id	 required
 *@param func_callback the function to execute on the callback
 *@param callback_extra_params object with all the params for the callback function 
 * */
og.getMemberFromServer = function(mem_id, func_callback, callback_extra_params){
    if (isNaN(mem_id)) return false;
	if(searchingMemberOnTheServer.indexOf(mem_id) == -1){
		//add member to searchingMemberOnTheServer
		searchingMemberOnTheServer.push(mem_id);
		og.openLink(og.getUrl('dimension', 'get_member_parents', {member:mem_id}), {
	           hideLoading: true,
	           callback: function(s, d) {
	        	   
	        	   for (var prop in d.members) {
	        		   var mem = d.members[prop];
	        		   //add member to og.dimensions
	        		   og.addMemberToOgDimensions(d.dimension_id,mem);
	        		   
	        		   if(d.member_id == mem.id){
	        			   var member = mem;
	        		   }
	        	   }
	        	   		        	   
	        	   //remove member from searchingMemberOnTheServer 
        		   var index = searchingMemberOnTheServer.indexOf(d.member_id);
        		   searchingMemberOnTheServer.splice(index, 1);
	        	   	
        		   //execute the callback function 
        		   if (typeof member != "undefined") {
        			   if (typeof callback_extra_params == "undefined") {
        				   callback_extra_params = {};
        			   }
        			   
        			   if (typeof func_callback != "undefined") {
        				   func_callback(d.dimension_id,member,callback_extra_params);
        			   }	        			   
        		   }
        		 
	           }
		})
	}
}

/*
 *This function search a member on og.dimensions and if is not there get it from the server. 
 *This function return an array with the texts and the texts of parents if you set include_parents. If the member is not found return an empty array.
 * you have to pass a function to execute after the member is found on the server.
 *@param dim_id
 *@param mem_id
 *@param include_parents
 *@param func_callback function to execute after found the member it must have 2 params (dimension_id ,member)
 * */
og.getMemberTextsFromOgDimensions = function(mem_id, include_parents, func_callback, callback_extra_params) {
	var texts = [];
	
	var members = og.getMemberFromOgDimensions(mem_id, include_parents, func_callback, callback_extra_params);
		
	if (members.length > 0){
		for(i=0; i<members.length; i++) {
			var member = members[i];
			var member_info = {};
			member_info ={
					"id":member.id,
					"dim":member.dimension_id,
					"ot":member.object_type_id,
					"c":member.color,
					"text":member.name
			};
			texts.push(member_info);
		}
		
	}	
	return texts;
}

/*
 * This function add a member to og.dimensions. (if a member is added to og.dimensions always add their parents)
 * param dim_id the id of the dimension
 * param member the member 		
 * */
og.addMemberToOgDimensions = function(dim_id, member) {
	if (typeof og.dimensions === 'undefined') og.dimensions = {};
	if (typeof og.dimensions[dim_id] === 'undefined') og.dimensions[dim_id] = {};
	og.dimensions[dim_id][member.id] = member;
	// Maintain inverted index for O(1) lookups in getMemberFromOgDimensions.
	if (typeof og._memberDimIndex === 'undefined') og._memberDimIndex = {};
	og._memberDimIndex[member.id] = dim_id;
};

/*
 *This function search members on the server. 
 *@param dimension_id int required
 *@param search_params objetc (text, start, limit) only text is required
 *@param func_callback function to execute after search on the server (recive 1 param the data from the server)
 * */
og.searchMemberOnServer = function(dimension_id, search_params, func_callback){
	var params = {dimension_id: dimension_id};
	params.query = Ext.escapeRe(search_params.text.toLowerCase());
	
	if(typeof search_params.limit != 'undefined'){
		params.limit = search_params.limit;		
	}
	
	if(typeof search_params.order != 'undefined'){
		params.order = search_params.order;		
	}
	
	if(typeof search_params.start != 'undefined'){
		params.start = search_params.start;
	}
	
	if(typeof search_params.parents != 'undefined'){
		params.parents = search_params.parents;
	}
	
	if(typeof search_params.random != 'undefined'){
		params.random = search_params.random;
	}
	
	if(typeof search_params.allowed_member_types != 'undefined'){
		params.allowed_member_types = search_params.allowed_member_types;
	}
	
	params.ignore_context_filter = true;
	
	og.openLink(og.getUrl('dimension', 'search_dimension_members_tree', params), {
		hideLoading:true, 
		hideErrors:true,
		callback: function(success, data){
			if (typeof func_callback != "undefined") {
				func_callback(data);
			}
		}
	});		
}



/*
 *This function get members from server if they are not in og.dimensions yet
 *@param members_ids array required
 *@callback_extra_params
 *@param func_callback function to execute after search on the server (recive 1 param callback_extra_params)
 * */
og.getMembersFromServer = function(members_ids,func_callback, callback_extra_params){	
	var missing_members_ids = new Array();
	for (var i=0; i<members_ids.length; i++) {
		var mem_id = members_ids[i];
		var missing = true;
		for (did in og.dimensions_info) {
			if (isNaN(did)) continue;		
			if(og.dimensions && og.dimensions[did]){
				var member = og.dimensions[did][mem_id];
				if (typeof member != "undefined") {
					missing = false;		
				}
			}		
		}
		if(missing){
			missing_members_ids.push(mem_id);
		}
	}
	if(missing_members_ids.length > 0){
		og.openLink(og.getUrl('dimension', 'get_members'), {
			method: 'POST',
			post: {member_ids:Ext.encode(missing_members_ids)},
			hideLoading: true,
			callback: function(s, d) {
				for (var prop in d.members) {
					var mem_path = d.members[prop];
			        	   
			        for (var mem in mem_path) {
			        	var member = mem_path[mem];
			        	//add member to og.dimensions
			        	if(typeof(member.dimension_id) != "undefined"){
			        		og.addMemberToOgDimensions(member.dimension_id,member);
			        	}		        			   
			        }		        		  
				} 
			        	   	
		        //execute the callback function 
		        if (typeof callback_extra_params == "undefined") {
		        	callback_extra_params = {};
		        }
		        			   
		        if (typeof func_callback != "undefined") {
		        	func_callback(callback_extra_params);
		        }	        			   
		        		   
		        		 
			}
		})
	}else{
		//execute the callback function 
        if (typeof callback_extra_params == "undefined") {
        	callback_extra_params = {};
        }
        			   
        if (typeof func_callback != "undefined") {
        	func_callback(callback_extra_params);
        }		
	}
}