og.reminderDurations = [0, 1, 2, 5, 10, 15, 30];
og.reminderDurationTypes = {
	"1":"minutes",
	"60":"hours",
	"1440":"days",
	"10080":"weeks"
};
og.addReminder = function(parent, context, type, duration, duration_type, for_subscribers, button) {
	var count = parent.getElementsByTagName("div").length;
	var div = document.createElement("div");
	var html = '<select name="reminder_type[' + context + '][' + count + ']">';
	for (var i=0; i < og.reminderTypes.length; i++) {
		html += '<option value="' + og.reminderTypes[i] + '"';
		if (og.reminderTypes[i] == type) {
			html += ' selected="selected"';
		}
		html += '>' + lang(og.reminderTypes[i]) + '</option>';
	}
	html += '</select>';
	html += '<select name="reminder_duration[' + context + '][' + count + ']">';
	for (var i=0; i < og.reminderDurations.length; i++) {
		html += '<option value="' + og.reminderDurations[i] + '"';
		if (og.reminderDurations[i] == duration) {
			html += ' selected="selected"';
		}
		html += '>' + og.reminderDurations[i] + '</option>';
	}
	html += '</select>';
	html += '<select name="reminder_duration_type[' + context + '][' + count + ']">';
	for (var i in og.reminderDurationTypes) {
		html += '<option value="' + i + '"';
		if (i == duration_type) {
			html += ' selected="selected"';
		}
		html += '>' + lang(og.reminderDurationTypes[i]) + '</option>';
	}
	html += '</select>';
	html += '&nbsp;' + lang('before');
	html += '<span style="margin-left:30px;position:relative;"><input class="checkbox" type="checkbox" name="reminder_subscribers[' + context + '][' + count + ']" ' + (for_subscribers ? 'checked="checked"' : "") + ' id="' + id + '" />&nbsp;' + lang("apply to subscribers") + '</span>';
	var id = Ext.id();
	html += '<a href="#" onclick="og.removeReminder(this.parentNode, \'' + context + '\');return false;" style="margin-left:30px;">';
	html += '<img title="' + lang("remove object reminder") + '" class="ico ico-delete" src="s.gif" style="vertical-align:middle;width:16px;height:20px;margin: 0 5px;position:relative;top:-2px;cursor:pointer" />';
	html += lang('remove');
	html += '</a>';
	div.innerHTML = html;
	parent.insertBefore(div, button);
};
og.removeReminder = function(div, context) {
	var parent = div.parentNode;
	parent.removeChild(div);
	// reorder property names
	var row = parent.firstChild;
	var num = 0;
	while (row != null) {
		if (row.tagName == "DIV") {
			var inputs = row.getElementsByTagName("select");
			for (var i=0; i < inputs.length; i++) {
				var input = inputs[i];
				if (input.name.substring(0, 13) == "reminder_type") {
					input.name = "reminder_type[" + context + "][" + num + "]";
				} else if (input.name.substring(0, 20) == "reminder_subscribers") {
					input.name = "reminder_subscribers[" + context + "][" + num + "]";
				} else if (input.name.substring(0, 22) == "reminder_duration_type") {
					input.name = "reminder_duration_type[" + context + "][" + num + "]";
				} else {
					input.name = "reminder_duration[" + context + "][" + num + "]";
				}
			}
			num++;
		}
		row = row.nextSibling;
	}
};