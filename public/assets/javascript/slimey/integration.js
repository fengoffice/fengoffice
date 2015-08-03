Slimey.prototype.submitFile = function(newRevision, rename, checkin) {
	function doSubmit(filename) {
		if (filename.substring(filename.length - 5) != ".slim") filename += ".slim";
		this.filename = filename;
		og.openLink(this.saveUrl, {
			post: {
				'file[name]': this.filename,
				'file[id]': this.config.fileId,
				'slimContent': this.slimContent,
				'new_revision_document': (newRevision?"checked":""),
				'checkin': checkin?"1":""
			},
			scope: this
		});
	}
	var slim = this.navigation.getSLIMContent();
	this.slimContent = escapeSLIM(slim);
	if (this.filename && !rename) {
		doSubmit.call(this, this.filename);
	} else {
		getInput(lang('choose a filename') + ':', doSubmit, this, null, this.filename || '');
	}
};

Slimey.prototype.onInit = function() {
	
}

Slimey.prototype.onDirty = function() {
	var p = og.getParentContentPanel(this.container);
	if (p) var panel = Ext.getCmp(p.id);
	if (panel) panel.setPreventClose(this.isDirty);
}

/**
 *  lets the user pick an image and then calls a function passing it the chosen image's URL
 *  	func: function to call when the image is selected (func is passed the image's URL as the first argument)
 */
function chooseImage(func, scope, button) {
	og.ImageChooser.show(imagesUrl, func, scope, button);
}

/**
 *  lets the user pick a color and then calls a function passing it the chosen color's CSS code
 *  	func: function to call when the color is selected (func is passed the color's code as the first argument)
 */
function chooseColor(func, scope, button) {
	var menu = new Ext.menu.ColorMenu({
        handler : function(palette, code) {
			if (typeof(code) == "string") {
				func.call(scope, "#" + code);
			}
		}
	});
	menu.show(button);
}

/**
 *  gets a string input.
 */
function getInput(msg, func, scope, button, defVal) {
	Ext.Msg.prompt(lang('save'), msg,
		function(btn, text) {
			if (btn == 'ok') {
				func.call(this, text);
			}
		}, scope, false, defVal
	);
}
