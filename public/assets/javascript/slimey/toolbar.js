/*
 *  Slimey - SLIdeshow Microformat Editor - http://slimey.sourceforge.net
 *  Copyright (C) 2007 - 2008 Ignacio de Soto
 *
 *  Toolbar class definition
 */


/**
 *  class SlimeyToolbar - implements functionality for the toolbar
 *  	slimey: the slimey object that references the editor, navigation and toolbar
 */
var SlimeyToolbar = function(slimey, tools) {
	this.slimey = slimey;
	this.container = document.createElement('div');
	this.container.className = 'slimeyToolbar';
	this.tools = new Array();
	for (var i=0; i < tools.length; i++) {
		if (tools[i] == '-') {
			this.addSeparator();
		} else if (tools[i] == '\n') {
			this.addBreak();
		} else {
			this.addTool(tools[i]);
		}
	}
}

/**
 *  adds a SlimeyTool to the toolbar.
 *  	tool: SlimeyTool to add
 */
SlimeyToolbar.prototype.addTool = function(tool) {
	this.tools[this.tools.length++] = tool;
	this.container.appendChild(tool.getElement());
}

/**
 *  adds a separator between tools in the toolbar.
 */
SlimeyToolbar.prototype.addSeparator = function() {
	var sep = document.createElement('img');
	sep.src = Slimey.imagesDir + 'sep.png';
	sep.style.marginLeft = '8px';
	sep.style.marginRight = '4px';
	sep.style.verticalAlign = 'middle';
	this.container.appendChild(sep);
}

/**
 *  adds a line break between tools in the toolbar.
 */
SlimeyToolbar.prototype.addBreak = function() {
	this.container.appendChild(document.createElement('br'));
}