/**
 *  TimeManager
 *
 */

var ogTimeManager = {};
ogTimeManager.Tasks = [];
ogTimeManager.Timeslots = [];
ogTimeManager.Users = [];
ogTimeManager.Companies = [];
ogTimeEvents = {};

ogTimeTimeslot = function(){
	this.id;
	this.date;
	this.time;
	this.memberIds;
	this.userId;
	this.userName;
	this.lastUpdated;
	this.lastUpdatedBy;
	this.hourlyBilling;
	this.totalBilling;
	this.memPath;
	

	this.description = '';
	this.taskName;
	this.otid;
}

ogTimeTimeslot.prototype.setFromTdata = function(tdata){
	this.id = tdata.id;
	this.date = tdata.date;
	this.time = tdata.time;
	this.memberIds = tdata.mids;
	this.userId = tdata.uid;
	this.userName = tdata.uname;
	this.lastUpdated = tdata.lastupdated;
	this.lastUpdatedBy = tdata.lastupdatedby;
	this.hourlyBilling = tdata.hourlybilling || 0;
	this.totalBilling = tdata.totalbilling || 0;
	this.memPath = tdata.memPath || [];
	this.otid = tdata.otid;
	
	if (tdata.desc)	this.description = tdata.desc; else this.description = '';
	if (tdata.tn)	this.taskName = tdata.tn; else this.taskName = null;
}



//************************************
//*		Data loading
//************************************

ogTimeManager.loadDataFromHF = function(genid){
	var result = [];
	result['tasks'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfTasks').value);
	result['users'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfUsers').value);
	result['all_users'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfAllUsers').value);
	result['timeslots'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfTimeslots').value);
	result['companies'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfCompanies').value);
	result['drawinputs'] = Ext.util.JSON.decode(document.getElementById(genid + 'hfDrawInputs').value);
	this.genid = genid;
	
	return this.loadData(result);
}


ogTimeManager.loadData = function(data){
	var i;
	this.Tasks = [];
	for (var i=0; i<data['tasks'].length; i++){
		var tdata = data['tasks'][i];
		if (tdata.id){
			var task = new ogTasksTask();
			//for(x in tdata) alert(x);
			task.setFromTdata(tdata);
			if (tdata.s)
				task.statusOnCreate = tdata.s;
			this.Tasks[this.Tasks.length] = task;
		}
	}
	
	this.Users = [];
	for (var i=0; i<data['users'].length; i++){
		var udata = data['users'][i];
		if (udata.id){
			var user =  new ogTasksUser(udata.id,udata.name,udata.cid);
			this.Users[this.Users.length] = user;
			if (udata.isCurrent)
				this.currentUser = user;
		}
	}
	this.AllUsers = [];
	for (var i=0; i<data['all_users'].length; i++){
		var udata = data['all_users'][i];
		if (udata.id){
			var user = new ogTasksUser(udata.id,udata.name,udata.cid);
			this.AllUsers[this.AllUsers.length] = user;
			if (udata.isCurrent)
				this.currentUser = user;
		}
	}
	
	this.Timeslots = [];
	for (var i=0; i<data['timeslots'].length; i++){
		var tdata = data['timeslots'][i];
		if (tdata.id){
			var timeslot =  new ogTimeTimeslot();
			timeslot.setFromTdata(tdata);
			this.Timeslots[this.Timeslots.length] = timeslot;
		}
	}
	
	this.Companies = [];
	for (var i=0; i<data['companies'].length; i++){
		var cdata = data['companies'][i];
		if (cdata.id)
			this.Companies[this.Companies.length] = new ogTasksCompany(cdata.id,cdata.name);
	}
	
	this.DrawInputs = data['drawinputs'];
}



//************************************
//*		Methods
//************************************



ogTimeManager.GetNewTimeslotParameters = function(genid){
	var parameters = [];
	parameters["timeslot[date]"] = Ext.getCmp(genid + "timeslot[date]Cmp").getValue().format(og.preferences['date_format']);

	parameters["timeslot[hours]"] = document.getElementById(genid + 'tsHours').value;
        parameters["timeslot[minutes]"] = document.getElementById(genid + 'tsMinutes').value;
	parameters["timeslot[description]"] = document.getElementById(genid + 'tsDesc').innerHTML;
	var userSel = document.getElementById(genid + 'tsUser');
	if (userSel){
		parameters["timeslot[contact_id]"] = userSel.value;
	}
	parameters["timeslot[id]"] = document.getElementById(genid + 'tsId').value;
	
	return parameters;
}

ogTimeManager.insertTimeslot = function(timeslot, genid){
	for (var i = 0; i < this.Timeslots.length; i++){
		if (this.Timeslots[i].date <= timeslot.date){
			this.Timeslots.splice(i,0,timeslot);
			this.drawTimespans(genid);
			return;
		}
	}
	this.Timeslots[this.Timeslots.length] = timeslot;
	this.drawTimespans(genid);
}

ogTimeManager.SubmitNewTimeslot = function(genid,obj_type){
	var parameters = this.GetNewTimeslotParameters(genid);
	var isEdit = document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display == 'block';
	var action = 'add_timeslot';
	if (isEdit) {
		action = 'edit_timeslot';		
	}
	og.handleMemberChooserSubmit(genid, obj_type); //TODO Hardcoded object type. Create a general object type map on somewhere
	
	if(member_selector[genid] !== undefined){
		var members = $("#"+genid + member_selector[genid].hiddenFieldName).val();
		parameters.members=members;
	}
	
	og.openLink(og.getUrl('time', action), {
		method: 'POST',
		post: parameters,
		callback: function(success, data) {
			if (success && !data.errorCode) {
				var timeslot = new ogTimeTimeslot();
				timeslot.setFromTdata(data.timeslot);
				if (isEdit){
					this.deleteTimeslot(timeslot.id);
					this.CancelEdit();
				}
				document.getElementById(genid + 'tsDesc').innerHTML = '';
				document.getElementById(genid + 'tsHours').value = 0;
                document.getElementById(genid + 'tsMinutes').value = 0;
				this.insertTimeslot(timeslot, genid);
			} else {
				if (!data.errorMessage || data.errorMessage == '')
					og.err(lang("error adding timeslot"));
			}
			og.eventManager.fireEvent('replace all empty breadcrumb', null);
		},
		scope: this
	});
	Ext.getCmp(genid+"timeslot[date]Cmp").focus();
}

ogTimeManager.DeleteTimeslot = function(timeslotId){
	og.openLink(og.getUrl('time', 'delete_timeslot', {id:timeslotId}), {
		method: 'POST',
		callback: function(success, data) {
			if (success && !data.errorCode) {
				this.deleteTimeslot(data.timeslotId);
				this.drawTimespans(this.genid);
				og.eventManager.fireEvent('replace all empty breadcrumb', null);
			}
		},
		scope: this
	});
}

ogTimeManager.CancelEdit = function(){
	document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display = 'none';
	document.getElementById(this.genid + 'TMTimespanSubmitAdd').style.display = 'block';
	document.getElementById(this.genid + 'TMTimespanAddNew').className = 'TMTimespanAddNew';
	document.getElementById(this.genid + 'TMTimespanHeader').className = 'TMTimespanHeader';
	
	document.getElementById(this.genid + 'tsHours').value = '0';
	document.getElementById(this.genid + 'tsMinutes').value = '0';
	document.getElementById(this.genid + 'tsDesc').innerHTML = '';
	var datePick = Ext.getCmp(this.genid + 'timeslot[date]Cmp');
	if (datePick){
		datePick.setValue(new Date());
	}
	member_selector.remove_all_selections(this.genid);
}

ogTimeManager.EditTimeslot = function(timeslotId){
	var ts = this.getTimeslot(timeslotId);
	if (ts){
		document.getElementById(this.genid + 'TMTimespanSubmitEdit').style.display = 'block';
		document.getElementById(this.genid + 'TMTimespanSubmitAdd').style.display = 'none';
		document.getElementById(this.genid + 'TMTimespanAddNew').className = 'TMTimespanEdit';
		document.getElementById(this.genid + 'TMTimespanHeader').className = 'TMTimespanEditHeader';
		
		var time_edit = parseFloat(ts.time / 3600);
		var new_time = (time_edit + "").split(".");
                
		document.getElementById(this.genid + 'tsHours').value = new_time[0];
		new_time[1] = parseFloat("0."+new_time[1]);
		if( new_time[1] == 0 ){
			document.getElementById(this.genid + 'tsMinutes').value = 0;
		}else if( new_time[1] <= 0.0833333333333335 ){
			document.getElementById(this.genid + 'tsMinutes').value = 5;
		}else if( new_time[1] <= 0.1666666666666665 ){
			document.getElementById(this.genid + 'tsMinutes').value = 10;
		}else if( new_time[1] <= 0.25 ){
			document.getElementById(this.genid + 'tsMinutes').value = 15;
		}else if( new_time[1] <= 0.333333333333333 ){
			document.getElementById(this.genid + 'tsMinutes').value = 20;
		}else if( new_time[1] <= 0.416666666666667 ){
			document.getElementById(this.genid + 'tsMinutes').value = 25;
		}else if( new_time[1] <= 0.5 ){
			document.getElementById(this.genid + 'tsMinutes').value = 30;
		}else if( new_time[1] <= 0.583333333333334 ){
			document.getElementById(this.genid + 'tsMinutes').value = 35;
		}else if( new_time[1] <= 0.666666666666666 ){
			document.getElementById(this.genid + 'tsMinutes').value = 40;
		}else if( new_time[1] <= 0.75 ){
			document.getElementById(this.genid + 'tsMinutes').value = 45;
		}else if( new_time[1] <= 0.833333333333334 ){
			document.getElementById(this.genid + 'tsMinutes').value = 50;
		}else if( new_time[1] >= 0.916666666666666 ){
			document.getElementById(this.genid + 'tsMinutes').value = 55;
		}
		
		document.getElementById(this.genid + 'tsDesc').innerHTML = ts.description;
		document.getElementById(this.genid + 'tsId').value = timeslotId;
		
		var userSel = document.getElementById(this.genid + 'tsUser');
		if (userSel && userSel.options){
			for (var i = 0; i < userSel.options.length; i++){
				if (userSel.options[i].value == ts.userId){
					userSel.selectedIndex = i;
					break;
				}
			}
		}
		var datePick = Ext.getCmp(this.genid + 'timeslot[date]Cmp');
		if (datePick){
			datePick.setValue(new Date(ts.date * 1000));
		}
		document.getElementById(this.genid + 'tsHours').focus();
		
		member_selector.remove_all_selections(this.genid);
		
		member_selector.set_selected(this.genid, ts.memberIds, false);
	}
}


ogTimeManager.deleteTimeslot = function(id){
	for (var i = 0; i < this.Timeslots.length; i++) {
		if (this.Timeslots[i].id == id){
			this.Timeslots.splice(i,1);
			return;
		}
	}
}

ogTimeManager.getTimeslot = function(id){
	for (var i = 0; i < this.Timeslots.length; i++) {
		if (this.Timeslots[i].id == id) {
			return this.Timeslots[i];
		}
	}
	return null;
}

ogTimeManager.getTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id) {
			return this.Tasks[i];
		}
	}
	return null;
}

ogTimeManager.getUser = function(id){
	for (var i = 0; i < this.Users.length; i++) {
		if (this.Users[i].id == id) {
			return this.Users[i];
		}
	}
	return null;
}

ogTimeManager.getUserCompanyName = function(assigned_to){
	var name = '';
	
	var user = this.getUser(assigned_to);
	if (user) {
		name = user.name;
	} else {
		var company = this.getCompany(assigned_to);
		if (company) {
			name = company.name;
		}
	}
	return name;
}


ogTimeManager.mouseMovement = function(task_id, mouse_is_over){
	if (mouse_is_over){
		this.taskMouseOver(task_id);
		ogTimeEvents.lastTaskId = task_id;
	} else {
		ogTimeEvents.mouseOutTimeout = setTimeout('ogTimeManager.taskMouseOut(' + task_id + ')',20);
		ogTimeEvents.lastTaskId = null;
	}
}

ogTimeManager.taskMouseOver = function(task_id){
	var table = document.getElementById('ogTimePanelTaskTableT' + task_id);
	if (table)
		table.className = 'ogTasksTaskTableSelected';
	var actions = document.getElementById('ogTimePanelTaskActionsT' + task_id);
	if (actions)
		actions.style.visibility='visible';
}

ogTimeManager.taskMouseOut = function(task_id){
	if (!ogTimeEvents.lastTaskId || ogTimeEvents.lastTaskId != task_id){
		var table = document.getElementById('ogTimePanelTaskTableT' + task_id);
		if (table)
			table.className = 'ogTasksTaskTable';
		var actions = document.getElementById('ogTimePanelTaskActionsT' + task_id);
		if (actions)
			actions.style.visibility='hidden';
	}
}


ogTimeManager.executeAction = function(actionName, ids, options){
	
	og.openLink(og.getUrl('task', 'multi_task_action'), {
		method: 'POST',
		post: {
			"ids": ids.join(','),
			"action" : actionName,
			"options": options
		},
		callback: function(success, data) {
			if (success && ! data.errorCode) {
				for (var i = 0; i < data.tasks.length; i++){
					var tdata = data.tasks[i];
					/*if (actionName == 'close_work')
						ogTimeManager.removeTask(tdata.id);
					else {*/
						var task = ogTimeManager.getTask(tdata.id);
						task.setFromTdata(tdata);
					//}
				}
				this.drawTasks(this.genid);
			} else {
			
			}
		},
		scope: this
	});
}


ogTimeManager.removeTask = function(id){
	for (var i = 0; i < this.Tasks.length; i++) {
		if (this.Tasks[i].id == id){
			this.Tasks.splice(i,1);
			return true;
		}
	}
	return false;
}


ogTimeManager.closeTimeslot = function(tgId){
	var panel = document.getElementById('ogTimePanelCWD' + tgId);
	if (panel.style.display == 'block') {
		panel.style.display = 'none';
	} else {
		panel.style.display = 'block';
		document.getElementById('ogTimePanelCWDescription' + tgId).focus();
	}
}

ogTimeManager.ToggleCompleteStatus = function(task_id, status){
	var action = (status == 0)? 'complete_task' : 'open_task';
	
	og.openLink(og.getUrl('task', action, {id: task_id, quick: true}), {
		callback: function(success, data) {
			if (!success || data.errorCode) {
			} else {
				//Set task data
				var task = this.getTask(task_id);
				task.status = (status == 0)? 1 : 0;
				task.completedById = this.currentUser.id;
				var today = new Date();
				today = today.clearTime();
				task.completedOn = (today.format('U'));
				
				this.drawTasks(this.genid);
			}
		},
		scope: this
	});
}


ogTimeManager.getCompany = function(id){
	for (var i = 0; i < this.Companies.length; i++) {
		if (this.Companies[i].id == id) {
			return this.Companies[i];
		}
	}
	return null;
}

ogTimeManager.renderUserCombo = function(genid){
	//get selected members
	var member_ids_input = Ext.fly(Ext.get(genid + member_selector[genid].hiddenFieldName));
	var selected_members = member_ids_input.getValue();
	
	//render allowed users to assign in timeslot
	var time_user = $("#"+genid+"tsUser");
	var userSel = time_user.val();
	if (time_user && time_user.is(":visible")) {
		var get_params = {};	
		get_params['member_ids'] = JSON.parse(selected_members).toString(); 
		og.openLink(og.getUrl('task', 'allowed_users_to_assign', get_params), 
				{callback:function(success, data) {  
							time_user.empty();
							
							var companies = data.companies;
							var html = "";
							if (companies.length > 0){
								for (var i=0; i<companies.length; i++) {
									if (!companies[i]) continue;
									var users = companies[i].users;
									for(j=0; j<users.length; j++){
										var usu = users[j];
										if (usu.id == 'undefined') continue;
										var selected = false;
										if(usu.id == userSel){
											selected = true;											
										}
										html += '<option value="'+ usu.id+'" '+ (selected ? ' selected="selected"' : '' ) +'>'+ usu.name + '</option>';
									}
								}
							}else{
								html += '<option value="0">'+ lang ('no users to display') + '</option>';
							}
							time_user.append(html);	
				}
		});
	}
	return null;
}