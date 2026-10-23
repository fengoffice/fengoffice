App.modules.linkToObjectForm = {
	pickObject: function(before, config) {
		if (!config) config = {
			ignore_context: false,
		};
		og.ObjectPicker.show(function (objs) {
			if (objs) {
				for (var i=0; i < objs.length; i++) {
					var obj = objs[i].data;
					App.modules.linkToObjectForm.addObject(this, obj, config);
				}
			}
		}, before, config);
	},

	addObject: function(before, obj, config) {
		if (!config) config = {};
		var parent = before.parentNode;
		var count = parent.getElementsByTagName('span').length;
		
		var div = document.createElement('div');
		div.className = "selected-object-wrapper og-add-template-object";
		
		var name = og.clean(obj.name);
		if (typeof config.renderName == 'function') {
			name = config.renderName(obj, count);
		}
		if(name.length > 75){
			name = name.substring(0,75)+'...';
		}
		
		/* TODO: Use Lucide icons for each type */
		div.innerHTML =
			'<input type="hidden" name="linked_objects[' + count + ']" value="' + obj.object_id + '" />' +
			'<div class="object-badge">' +
				'<i class="icon-link-2" title="' + obj.type + '"></i>' +
				'<span class="name">' + name + '</span>' +
				'<a href="#" onclick="App.modules.linkToObjectForm.removeObject(this.parentNode.parentNode)" class="object-remove-btn" title="' + lang('remove') + '">' +
					'<i class="icon-circle-x"></i>' +
				'</a>' +
			'</div>';

		$(parent).siblings( ".no_linked_objects_desc" ).hide();
        $(parent).append($(div));
	},

	removeObject: function(div) {
		var parent = div.parentNode;
		parent.removeChild(div);
		
		var inputs = parent.getElementsByTagName('input');
		for (var i=0; i < inputs.length; i++) {
			inputs[i].name = 'linked_objects[' + i + ']';
		}
		
		var links = $(parent).children(".og-add-template-object").length;
		if (links > 0){			
			$(parent).siblings( ".no_linked_objects_desc" ).hide();
		} else {
			$(parent).siblings( ".no_linked_objects_desc" ).show();
		}
	}
};