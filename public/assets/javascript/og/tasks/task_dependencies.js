
og.pickPreviousTask = function(before, genid, task_id) {
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.addPreviousTask(this, obj, genid);
			}
		}
	}, before, {
		types: ['task'],
		selected_type: 'task'
	},'',task_id);
};

og.pickPreviousTemplateTask = function(before, genid, task_id, template_id) {
	var extra_list_params = {
			template_id:template_id
	};
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'template_task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.addPreviousTask(this, obj, genid);
			}
		}
	}, before, {
		types: ['template_task'],
		selected_type: 'template_task',
		extra_list_params : extra_list_params
	},'', task_id);
};

og.addPreviousTask = function(before, obj, genid) {
	var type = obj.type;
	if (type == 'template_task') type = 'task';	

	var div = document.createElement('div');
	div.className = "object-badge";
	div.innerHTML =
		'<input type="hidden" name="task[previous]['+og.previousTasksIdx+']" value="' + obj.object_id + '" />' +
		'<i class="icon-list-todo"></i>' +
		'<span class="name">' + og.clean(obj.name) + '</span>' +
		'<a href="#" onclick="og.removePreviousTask(this.parentNode, \''+genid+'\', '+og.previousTasksIdx+')" class="object-remove-btn" title="'+lang('remove')+'"><i class="icon-circle-x"></i></a>';
	
	var label = document.getElementById(genid + 'no_previous_selected');
	var targetContainer = null;
	if (label) {
		label.style.display = 'none';
		targetContainer = label.parentNode;
	} else {
		var badgeNameSpan = document.getElementById(genid + 'task_name');
		if (badgeNameSpan && badgeNameSpan.parentNode && badgeNameSpan.parentNode.parentNode) {
			targetContainer = badgeNameSpan.parentNode.parentNode;
		}
	}
	
	og.previousTasks[og.previousTasksIdx] = obj;
	
	if (targetContainer) {
		targetContainer.appendChild(div);
		var clearDiv = document.createElement('div');
		clearDiv.className = 'clear';
		targetContainer.appendChild(clearDiv);
	} else {
		var parent = before.parentNode;
		parent.insertBefore(div, before);
	}
	
	og.previousTasksIdx++;
};

og.removePreviousTask = function(div, genid, index) {
	var parent = div.parentNode;
	parent.removeChild(div);
	og.previousTasks = og.previousTasks.splice(index, 1);
	if (og.previousTasks.length == 0) {
		var label = document.getElementById(genid + 'no_previous_selected');
		if (label) label.style.display = 'inline';
	}
};

og.pickPreviousTaskFromView = function(tid) {
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.openLink(og.getUrl('taskdependency', 'add', {pt:obj.object_id, t:tid}));
			}
		}
	}, this, {
		types: ['task'],
		selected_type: 'task'
	},'',tid);
};

og.pickPreviousTemplateTaskFromView = function(task_id,template_id) {
	var extra_list_params = {
			template_id:template_id
	};
	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			var obj = objs[0].data;
			if (obj.type != 'template_task') {
				og.msg(lang("error"), lang("object type not supported"), 4, "err");
			} else {
				og.openLink(og.getUrl('taskdependency', 'add', {pt:obj.object_id, t:task_id}));
			}
		}
	}, this, {
		types: ['template_task'],
		selected_type: 'template_task',
		extra_list_params : extra_list_params
	},'',task_id);
};