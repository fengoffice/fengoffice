/*
 *  Slimey - SLIdeshow Microformat Editor - http://slimey.sourceforge.net
 *  Copyright (C) 2007 - 2008 Ignacio de Soto
 *
 *  Inclusion to a webpage.
 */

/**
 *  Initializes and writes an instance of Slimey
 *
 *  config options:
 *  	container: Where Slimey will be written to
 *  	rootDir: Slimey classes root dir
 *  	imagesDir: Slimey images dir
 *  	filename: name of the file that's going to be edited
 *  	slimContent: content of the file
 *  	saveUrl: where the modified file will be submited
 */
var Slimey = function(config) {
	if (config.rootDir) Slimey.rootDir = config.rootDir;
	if (config.imagesDir) Slimey.imagesDir = config.imagesDir;
	if (config.filename) this.filename = config.filename;
	if (config.slimContent) this.slimContent = config.slimContent;
	if (config.saveUrl) this.saveUrl = config.saveUrl;
	this.config = config;
	Slimey.preloadImages();

	this.editor = new SlimeyEditor(this);
	this.navigation = new SlimeyNavigation(this);
	this.edtoolbar = new SlimeyToolbar(this, [
			new SlimeySaveTool(this),
			'-',
			new SlimeyInsertTextTool(this), new SlimeyInsertImageTool(this),
			new SlimeyInsertOrderedListTool(this), new SlimeyInsertUnorderedListTool(this),
			new SlimeyDeleteTool(this),
			'-',
			new SlimeyUndoTool(this), new SlimeyRedoTool(this),
			'-',
			new SlimeyFontColorTool(this), new SlimeyFontFamilyTool(this),
			new SlimeyFontSizeTool(this),
			'-',
			new SlimeyStyleToggleTool(this, 'bold', lang("bold text"), 'fontWeight', 'bold', 'normal'),
			new SlimeyStyleToggleTool(this, 'underline', lang("underline text"), 'textDecoration', 'underline', 'none'),
			new SlimeyStyleToggleTool(this, 'italic', lang("italic text"), 'fontStyle', 'italic', 'normal'),
			'-',
			new SlimeyStyleGroupToggleTool(this, 'alignment', 'textAlign', [{
				name: 'left',
				title: lang("align text to the left"),
				value: 'left'
			},{
				name: 'center',
				title: lang("align text to the center"),
				value: 'center'
			},{
				name: 'right',
				title: lang("align text to the right"),
				value: 'right'
			}]),
			'-',
			new SlimeySendToBackTool(this), new SlimeyBringToFrontTool(this),
			'-',
			new SlimeyViewSourceTool(this), new SlimeyPreviewTool(this)
	]);
	this.navtoolbar = new SlimeyToolbar(this, [
		new SlimeyAddSlideTool(this), new SlimeyDeleteSlideTool(this),
		'-',
		new SlimeyMoveSlideDownTool(this), new SlimeyMoveSlideUpTool(this)
			
	]);
	
	if (typeof config.container == 'string') {
		this.container = document.getElementById(config.container);
	} else if (typeof config.container == 'object') {
		this.container = config.container;
	}
	if (!config.container) {
		this.container = document.body;
	}
	// container needs to be positioned
	if (this.container.style.position != 'relative' && this.container.style.position != 'absolute') {
		this.container.style.position = 'relative';
	}
	if (this.container.style.width == '') {
		this.container.style.width = config.width || "100%";
	}
	if (this.container.style.height == '') {
		this.container.style.height = config.height || "100%";
	}
	this.container.style.margin = '0px';
	this.container.style.padding = '0px';
	
	
	this.container.appendChild(this.navtoolbar.container);
	this.container.appendChild(this.edtoolbar.container);
	this.container.appendChild(this.navigation.container);
	this.container.appendChild(this.editor.container);
	this.isDirty = false;
	this.editor.addEventListener('actionPerformed', function() {
		if (this.editor.undoStack.peek().name != 'changeSlide') {
			if (!this.isDirty) {
				this.isDirty = true;
				this.onDirty();
			}
		}
	}, this);
	this.onInit();
}

Slimey.prototype.onInit = function() {
	addEventHandler(window, 'resize', this.layout, this);
	addEventHandler(window, 'load', this.layout, this);
}

Slimey.prototype.onDirty = function() {
	if (this.isDirty) {
		window.onbeforeunload = function() { return lang("unsaved changes will be lost.") };
	} else {
		window.onbeforeunload = null;
	}
}

Slimey.prototype.layout = function() {
	var a = this.aspect || 4/3;
	var h = this.container.offsetHeight - 5;
	var w = this.container.offsetWidth;
	this.navtoolbar.container.style.position = 'absolute';
	this.navtoolbar.container.style.left = '0';
	this.navtoolbar.container.style.top = '0';
	this.navtoolbar.container.style.width = this.navigation.container.offsetWidth + 'px';
	
	this.navigation.container.style.position = 'absolute';
	this.navigation.container.style.top = this.navtoolbar.container.offsetHeight + 'px';
	this.navigation.container.style.left = '0';
	this.navigation.container.style.height = h - this.navtoolbar.container.offsetHeight + 'px';
	
	this.edtoolbar.container.style.position = 'absolute';
	this.edtoolbar.container.style.top = '0';
	this.edtoolbar.container.style.left = this.navigation.container.offsetWidth + 'px';
	this.edtoolbar.container.style.width = (w - this.navigation.container.offsetWidth) + 'px';
	
	this.editor.container.style.position = 'absolute';
	var eh = h - this.edtoolbar.container.offsetHeight - 12;
	var ew = w - this.navigation.container.offsetWidth;
	if (ew > eh * a) {
		// there's extra width so base on height
		this.editor.container.style.height = eh + 'px';
		this.editor.container.style.width = eh * a + 'px';
		this.editor.container.style.left = this.navigation.container.offsetWidth + (w - this.navigation.container.offsetWidth - eh * a) / 2 + 'px';
		this.editor.container.style.top = this.edtoolbar.container.offsetHeight + 6 + 'px';
	} else {
		// there's extra height so base on width
		this.editor.container.style.height =ew / a + 'px';
		this.editor.container.style.width = ew + 'px';
		this.editor.container.style.left = this.navigation.container.offsetWidth + 'px';
		this.editor.container.style.top = this.edtoolbar.container.offsetHeight + 6 + 'px';
	}
	this.editor.resized();
}

Slimey.prototype.submitFile = function() {
	var form = document.createElement('form');
	form.method = 'POST';
	form.action = this.saveUrl;
	form.target = "_blank";
	var fn = document.createElement('input');
	fn.type = 'hidden';
	fn.name = 'filename';
	fn.value = this.filename;
	var sc = document.createElement('input');
	sc.type = 'hidden';
	sc.name = 'slimContent';
	sc.value = this.slimContent;
	form.appendChild(fn);
	form.appendChild(sc);
	document.body.appendChild(form);
	form.submit();
	this.isDirty = false;
	this.onDirty();
}

Slimey.imagesDir = 'images/';

Slimey.rootDir = '';

Slimey.preloadedImages = new Array();

Slimey.includeScripts = function() {
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'functions.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'stack.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'editor.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'navigation.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'actions.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'tools.js"></script>');
	document.write('<script language="javascript" src="' + Slimey.rootDir + 'toolbar.js"></script>');
}

Slimey.preloadImage = function(filename) {
	var ims = Slimey.preloadedImages;
	var i = ims.length;
	ims[i] = new Image(); ims[i].src = Slimey.imagesDir + filename;
}

Slimey.preloadToolbarImage = function(name) {
	Slimey.preloadImage(name + '.png');
	Slimey.preloadImage(name + 'h.png');
	Slimey.preloadImage(name + 'x.png');
	Slimey.preloadImage(name + 'd.png');
}

Slimey.preloadImages = function() {
	if (!Image) {
		return;
	}
	Slimey.preloadToolbarImage('bold');
	Slimey.preloadToolbarImage('bringToFront');
	Slimey.preloadToolbarImage('color');
	Slimey.preloadToolbarImage('delete');
	Slimey.preloadToolbarImage('empty');
	Slimey.preloadToolbarImage('insertImage');
	Slimey.preloadToolbarImage('insertOList');
	Slimey.preloadToolbarImage('insertUList');
	Slimey.preloadToolbarImage('insertText');
	Slimey.preloadToolbarImage('italic');
	Slimey.preloadToolbarImage('preview');
	Slimey.preloadToolbarImage('redo');
	Slimey.preloadToolbarImage('save');
	Slimey.preloadToolbarImage('sendToBack');
	Slimey.preloadToolbarImage('underline');
	Slimey.preloadToolbarImage('undo');
	Slimey.preloadToolbarImage('viewSource');
	Slimey.preloadToolbarImage('addslide');
	Slimey.preloadToolbarImage('delslide');
	Slimey.preloadToolbarImage('slidedown');
	Slimey.preloadToolbarImage('slideup');

	Slimey.preloadImage('sep.png');
}
