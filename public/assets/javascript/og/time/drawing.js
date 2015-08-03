/**
 *  Drawing
 *
 */


ogTimeManager.drawTimespans = function(genid){

	var table = document.getElementById(genid + 'TMTimespanTable');
	
	while (table.rows.length > 1) {
		table.deleteRow(1);
	}
	
	for (i in this.Timeslots) {
		if (this.Timeslots[i] && typeof(this.Timeslots[i]) != 'function' && this.Timeslots[i].id) {
			this.insertRow(genid, this.Timeslots[i], table.rows.length);
		}
	}
}

ogTimeManager.drawTasks = function(genid){
	if (this.Tasks.length > 0) {
		var table = document.getElementById(genid + 'active_tasks_table');
		if (table) table.style.display = '';
	}
	sb = new StringBuffer();
	this.orderTasks();
	for (var i = 0; i < this.Tasks.length; i++){
		sb.append(this.drawTimeTaskRow(this.Tasks[i]));
	}
	sb.append();
	
	var div = document.getElementById(genid + 'TMActiveTasksContents');
	div.innerHTML = sb.toString();
}

ogTimeManager.orderTasks = function(){
	for (var i = 0; i < this.Tasks.length - 1; i++) {
		for (var j = i+1; j < this.Tasks.length; j++) {
			if ((this.Tasks[i].pauseTime && !this.Tasks[j].pauseTime) || (this.Tasks[i].pauseTime && this.Tasks[j].pauseTime && this.Tasks[j].pauseTime > this.Tasks[i].pauseTime)){
				var aux = this.Tasks[i];
				this.Tasks[i] = this.Tasks[j];
				this.Tasks[j] = aux;
			}
		}
	}
}

 
 ogTimeManager.drawTimeTaskRow = function(task){
	var sb = new StringBuffer();
	var tgId = "T" + task.id;
	sb.append('<table id="ogTimePanelTaskTable' + tgId + '" class="ogTasksTaskTable' + (task.isChecked?'Selected':'') + '" onmouseover="ogTimeManager.mouseMovement(' + task.id + ',true)" onmouseout="ogTimeManager.mouseMovement(' + task.id + ',false)"><tr>');
	
	//Center td
	sb.append('<td align=left style="padding-left:10px">');
	
	var taskName = '';
	//Draw the Assigned user
	if (task.assignedToId){
		taskName += '<span class="bold">' + og.clean(this.getUserCompanyName(task.assignedToId)) + '</span>:&nbsp;';
	}
	//Draw the task name
	taskName += og.clean(task.title);
	if (task.status > 0){
		taskName = "<span style='text-decoration:line-through'>" + taskName + "</span>";
	}
	sb.append('<a class="internalLink" href="#" onclick="og.openLink(\'' + og.getUrl('task', 'view', {id: task.id}) + '\')">' + taskName + '</a>');
	
	sb.append('</td><td align=right><table style="height:100%"><tr>');
	
	//Draw task actions
	sb.append("<td><div id='ogTimePanelTaskActions" + tgId + "' class='ogTaskActions'><table><tr>");
	sb.append("<td style='padding-left:8px;'><a href='#' onclick='ogTimeManager.ToggleCompleteStatus(" + task.id + ", " + task.status + ")'>");
	if (task.status > 0){
		sb.append("<div class='ico-reopen coViewAction' title='" + lang('reopen this task') + "' style='cursor:pointer;height:16px;padding-top:0px'>" + lang('reopen') + "</div></a></td>");
	} else {
		sb.append("<div class='ico-complete coViewAction' title='" + lang('complete this task') + "' style='cursor:pointer;height:16px;padding-top:0px'>" + lang('do complete') + "</div></a></td>");
	}
	sb.append("</tr></table></div></td>");
	
	//Draw time tracking
	if (task.workingOnIds){
		var ids = (task.workingOnIds + ' ').split(',');
		var userIsWorking = false;
		for (var i = 0; i < ids.length; i++) {
			if (this.currentUser && ids[i] == this.currentUser.id){
				userIsWorking = true;
				var pauses = (task.workingOnPauses + ' ').split(',');
				var userPaused = pauses[i] == 1;
			}
		}
		sb.append("<td class='" + (userIsWorking?(userPaused?"ogTasksPausedTimeTd": "ogTasksActiveTimeTd") : "ogTasksTimeTd") + "'><table><tr>");
		if (userIsWorking){
			if (userPaused)
				sb.append("<td><a href='#' onclick='ogTimeManager.executeAction(\"resume_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time-play' title='" + lang('resume_work') + "'></div></a></td>");
			else
				sb.append("<td><a href='#' onclick='ogTimeManager.executeAction(\"pause_work\",[" + task.id + "])'><div class='ogTasksTimeClock ico-time-pause' title='" + lang('pause_work') + "'></div></a></td>");
			
			sb.append("<td><a href='#' onclick='ogTimeManager.closeTimeslot(\"" + tgId + "\")'><div class='ogTasksTimeClock ico-time-stop' title='" + lang('close_work') + "'></div></a></td>");
		}
		sb.append("<td style='white-space:nowrap'><b>");
		for (var i = 0; i < ids.length; i++){
			var user = this.getUser(ids[i]);
			if (user){
				sb.append("" + og.clean(user.name));
				if (i < ids.length - 1)
					sb.append(",");
				sb.append("&nbsp;");
			}
		}
		sb.append("</b>");
		if (userIsWorking){
			sb.append("<div id='ogTimePanelCWD" + tgId + "' style='display:none'><table><tr><td>" + lang('description') + ":<br/><textarea tabIndex=10100 style='height:54px;width:220px;margin-right:8px' id='ogTimePanelCWDescription" + tgId + "'></textarea></td></tr>");
			sb.append("<tr><td style='padding-bottom:5px'><button type='submit' tabIndex=10101 onclick='ogTimeManager.executeAction(\"close_work\",[" + task.id + "],document.getElementById(\"ogTimePanelCWDescription" + tgId + "\").value);return false'>" + lang('close work') + "</button>&nbsp;&nbsp;<button tabIndex=10102 type='submit' onclick='ogTimeManager.closeTimeslot(\"" + tgId + "\");return false'>" + lang('cancel') + "</button></td></tr></table></div>");
		}
		sb.append("</td></tr></table></td>");
	}
	
	sb.append("</tr></table></td></tr></table>");
	return sb.toString();
}


ogTimeManager.insertRow = function(genid, timeslot, position){
	var table = document.getElementById(genid + 'TMTimespanTable');
	if (!position)
		position = -1;
	
	var date = new Date(timeslot.date * 1000);
	var now = new Date();
	var time = timeslot.time / 3600;
	var minutes = time * 60;
	while(minutes > 60){
		minutes -= 60;
	}
	
	
	var hours;
	hours = time*60;
	hours -= minutes;
	hours /= 60;
	minutes = Math.round(minutes);
	if (minutes == 60){
		hours++;
		minutes = "00";
	}
	if (minutes<10 && minutes > 0){
		minutes = "0" + minutes;
	}
	var row = table.insertRow(position);
	row.id = genid + 'TMTimespanTableRow' + timeslot.id;
	row.height = '20px';
	
	if (position % 2 == 1)
		row.style.backgroundColor = '#F0F6FF';
	
	var pos = 0;
	
	var cell = row.insertCell(pos++);
	mem_path = "";
	var mpath = Ext.util.JSON.decode(timeslot.memPath);
	
	if (mpath){ 
		mem_path = "<div class='breadcrumb-container time-breadcrumb-container'>";
		mem_path += "<div style='display: inline;'>";
		mem_path += og.getEmptyCrumbHtml(mpath, '.breadcrumb-container');
		mem_path += "</div>";
		mem_path += "</div>";
	}
	cell.innerHTML = mem_path;
		
	cell = row.insertCell(pos++);
	textNode = document.createTextNode(timeslot.userName);
	cell.appendChild(textNode);
	
	cell = row.insertCell(pos++);
	if (date.dateFormat('Y') != now.dateFormat('Y'))
		var textNode = document.createTextNode(date.dateFormat('M j, Y'));
	else
		var textNode = document.createTextNode(date.dateFormat('M j'));
	cell.appendChild(textNode);
	
	cell = row.insertCell(pos++);
	textNode = document.createTextNode(hours + ":" + minutes);
	cell.appendChild(textNode);
	
	cell = row.insertCell(pos++);
		
	var e = document.createElement('div');
	e.innerHTML = timeslot.description.replace(/\n/g, "<br />");
	cell.appendChild(e);
	
	if (table.rows[0].cells.length >= 8) {
		cell = row.insertCell(pos++);
		textNode = document.createTextNode(timeslot.hourlyBilling + " (" + timeslot.totalBilling + ")");
		cell.appendChild(textNode);
	}
	
	cell = row.insertCell(pos++);
	updatedInfo = '';
	if (timeslot.lastUpdated != '') {
		updatedInfo = lang('last updated by on', timeslot.lastUpdatedBy, timeslot.lastUpdated);
	}
	textNode = document.createTextNode(updatedInfo);
	cell.appendChild(textNode);
	
	if (ogTimeManager.DrawInputs) {
		cell = row.insertCell(pos++);
		cell.innerHTML = '<a class="internalLink coViewAction ico-edit" href="javascript:ogTimeManager.EditTimeslot(' + timeslot.id + ')" style="display: block;width:0;padding-bottom:0;padding-top:0;line-height:18px" title="' + lang('edit') + '">&nbsp;</a>';
		cell.width = 18;
		
		cell = row.insertCell(pos++);
		cell.innerHTML = '<a class="internalLink coViewAction ico-delete" href="javascript:if(confirm(lang(\'confirm delete timeslot\'))) ogTimeManager.DeleteTimeslot(' + timeslot.id + ')" style="display: block;width:0;padding-bottom:0;padding-top:0;line-height:18px" title="' + lang('delete') + '">&nbsp;</a>';
		cell.width = 18;
	}
			
	cell = row.insertCell(pos++);
	var textNode = document.createTextNode(timeslot.id);
	cell.appendChild(textNode);
	cell.style.display = 'none';
}