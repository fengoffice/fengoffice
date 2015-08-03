
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
	var parent = before.parentNode;
	var count = parent.getElementsByTagName('input').length;
	var div = document.createElement('div');
	var type = obj.type;
	if (type == 'template_task') type = 'task';	
	div.className = "og-add-template-object previous-task " + (count % 2 ? " odd" : "");
	div.innerHTML =
		'<input type="hidden" name="task[previous]['+og.previousTasksIdx+']" value="' + obj.object_id + '" />' +
		'<div class="previous-task-name action-ico ico-'+ type +'">' + og.clean(obj.name) + '</div>' +
		'<a href="#" onclick="og.removePreviousTask(this.parentNode, \''+genid+'\', '+og.previousTasksIdx+')" class="removeDiv link-ico ico-delete" style="display: block;">'+lang('remove')+'</a><div class="clear"></div>';
	var label = document.getElementById(genid + 'no_previous_selected');
	if (label) label.style.display = 'none';
	parent.insertBefore(div, before);
	og.previousTasks[og.previousTasksIdx] = obj;
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