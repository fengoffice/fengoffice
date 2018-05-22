/**
 * drawing.js
 *
 * This module holds the rendering logic for printing groups
 *
 * @author Carlos Palma <chonwil@gmail.com>
 */


ogTasks.printAllGroups = function(){
	var printWindow = this.createPrintWindow();
 	for (var i = 0; i < this.Groups.length; i++){
 		if (this.Groups[i].group_tasks != {}){
 			this.printGroupHeader(this.Groups[i], printWindow);
 			this.printGroupTasks(this.Groups[i], printWindow);
 		}
 	}
 	
 	this.closePrintWindow(printWindow);
}


ogTasks.printGroup = function(group_id){
	var group = this.getGroup(group_id);
	if (group){
	 	var printWindow = this.createPrintWindow();
	 	
	 	this.printGroupHeader(group, printWindow);
	 	this.printGroupTasks(group, printWindow);
	 	
	 	this.closePrintWindow(printWindow);
	}
}


ogTasks.createPrintWindow = function(){
	var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,width="+ ($(document).outerWidth() - 100) +", height="+ ($(document).outerHeight() - 50) +", left=50, top=25";
	var printWindow = window.open("","",disp_setting);
	printWindow.document.open(); 
	printWindow.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml">');
	printWindow.document.write('<html><head><title>' + lang('tasks list') + '</title>'); 
	printWindow.document.write('<LINK href="' + og.hostName + '/public/assets/themes/default/stylesheets/og/printTasks.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<LINK href="' + og.hostName + '/public/assets/themes/default/stylesheets/og/tasks.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/website.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/ogmin.css" rel="stylesheet" type="text/css">');
	printWindow.document.write('<link href="' + og.hostName + '/public/assets/themes/default/stylesheets/general/rewrites.css" rel="stylesheet" type="text/css">');
	
	printWindow.document.write('</head><body onLoad="self.print()">');
	return printWindow;
}


ogTasks.closePrintWindow = function(printWindow){
	printWindow.document.write('</body></html>');    
	printWindow.document.close();
	printWindow.focus();
}


ogTasks.printGroupHeader = function(group, printWindow){
	printWindow.document.write('<h1>' + group.group_name + '</h1>');
}


ogTasks.printGroupTasks = function(group, printWindow){
	var sb = new StringBuffer();
	sb.append("<table style='width:100%;max-width:1000px'>");
	
	var bottomToolbar = Ext.getCmp('tasksPanelBottomToolbarObject');
	var topToolbar = Ext.getCmp('tasksPanelTopToolbarObject');
	var displayCriteria = bottomToolbar.getDisplayCriteria();
	var drawOptions = topToolbar.getDrawOptions();
	
	for (var i in group.group_tasks){
		sb.append("<tr><td>");
		var task = group.group_tasks[i];
		var level = 0;
		var parent = task.parent
		while (i - level > 0 && parent != null){
			level ++;
			parent = parent.parent;
		}
		var color = 'White';
		if (task.priority == 400) {
			color = '6';
		} else if (task.priority == 300) {
			color = '18';
		} else if (task.priority == 200) {
			color = '0';
		}
		
		sb.append("<div class='task' style='margin-left:" + (4 + (level * 15)) + "px;'>");
		sb.append("<table style='width:100%'><tr><td width=20><img src='" + og.hostName + "/public/assets/themes/default/images/16x16/wscolors/color" + color + ".png' style='padding-right:3px'/></td><td>");
		if(task.assignedToId && (displayCriteria.group_by != 'assigned_to' || task.assignedToId != group.group_id)) {
			sb.append("<b>" + og.clean(this.getUserCompanyName(task.assignedToId)) + '</b>:&nbsp;');
		}
		sb.append(og.clean(task.title));
		sb.append("</td>");		
		sb.append("<td width='1px' align=right><table><tr>");		
		//Draw dates
		if (drawOptions.show_end_dates || drawOptions.show_start_dates){
			sb.append('<td style="color:#888;font-size:10px;padding-left:6px;padding-right:6px;white-space:nowrap">');
			if (task.status == 1)
				sb.append('<span style="text-decoration:line-through;">');
			else
				sb.append('<span>');			
                if (task.estimatedTime){
                    sb.append(lang('estimated') + ': '+task.estimatedTime + '<br/>'); 
                    var task_percent = task.percentCompleted;
                    if(task.percentCompleted > 100){
                        task_percent = 100;
                    }
                    sb.append(lang('progress') + ": " + task_percent + '%<br/>');
                }
			if (task.startDate){
				var date = new Date(task.startDate * 1000);
				sb.append(lang('start') + ':&nbsp;' + date.dateFormat('M j'));
			}
			if (task.startDate && task.dueDate)
				sb.append('<br/>');
			
			if (task.dueDate){
				var date = new Date((task.dueDate) * 1000);
				var dueString = lang('due') + ':&nbsp;' + date.dateFormat('M j');
				if (task.status == 0){
					var now = new Date();
					if (date < now)
						dueString = '<span style="font-weight:bold;color:#F00">' + dueString + '</span>';
				}
				sb.append(dueString);
			}
			sb.append('</span></td>');
		}
		
		//Draw time tracking
		if (drawOptions.show_time && task.workingOnIds){
			var ids = (task.workingOnIds + ' ').split(',');
			var userIsWorking = false;
			for (var j = 0; j < ids.length; j++)
				if (ids[j] == this.currentUser.id){
					userIsWorking = true;
					var pauses = (task.workingOnPauses + ' ').split(',');
					var userPaused = pauses[j] == 1;
				}
			sb.append("<td><img src='" + og.hostName + "/public/assets/themes/default/images/16x16/time.png' style='padding-right:3px'/></td>");
			sb.append("<td class='ogTasksTimeTd' style='background-color:transparent;white-space:nowrap'>");
			for (var j = 0; j < ids.length; j++){
				var user = this.getUser(ids[j]);
				if (user){
					sb.append(og.clean(user.name));
					if (j < ids.length - 1)
						sb.append("<br/>");
				}
			}
			sb.append("</td>");
		}
		sb.append("</tr></table>");
		
		
		sb.append("</td></tr></table>");
		sb.append("</div></td></tr>");
	}
	sb.append("</table>");
	printWindow.document.write(sb.toString());
}
