/*
 *  Slimey - SLIdeshow Microformat Editor - http://slimey.sourceforge.net
 *  Copyright (C) 2007 - 2008 Ignacio de Soto
 *
 *  Editor variables and functions
 */

/**
 *  class SlimeyEditor - implements functionality for the editor
 *  	slimey: the slimey object that references the editor, navigation and toolbar
 */
var SlimeyEditor = function(slimey) {
	/* initialize variables */
	this.slimey = slimey;
	this.current = null;
	this.selected = null;
	this.container = document.createElement('div');
	this.undoStack = new SlimeyStack();
	this.redoStack = new SlimeyStack();
	this.events = new Array();
	this.events['selectionChange'] = new Array();
	this.events['actionPerformed'] = new Array();

	/* create content editor */
	this.contentEditor = document.createElement('textarea');
	this.contentEditor.slimey = this.slimey;
	this.contentEditor.id = 'contentEditor';
	this.contentEditor.style.position = 'absolute';
	this.contentEditor.style.zIndex = '20000';
	this.contentEditor.style.overflow = 'visible';
	this.contentEditor.style.visibility = 'hidden';
	this.contentEditor.style.top = -1000;
	this.contentEditor.style.backgroundColor = '#FFFFEE';
	this.contentEditor.systemElement = true;
	this.container.appendChild(this.contentEditor);

	/* create resize handle */
	this.resizeHandle = document.createElement('div');
	this.resizeHandle.slimey = this.slimey;
	this.resizeHandle.className = 'resizeHandle';
	this.resizeHandle.style.zIndex = 100000000;
	this.resizeHandle.style.position = 'absolute';
	this.resizeHandle.style.visibility = 'hidden';
	this.resizeHandle.systemElement = true;
	this.container.appendChild(this.resizeHandle);

	/* define container's style */
	this.container.className = 'slimeyEditor';
	this.container.slimey = this.slimey;
	this.container.style.position = 'relative';
	this.container.style.padding = '0px';
	this.container.style.margin = '0px';
	this.container.style.border = '1px solid black';
	this.container.style.overflow = 'hidden';
	this.container.style.cursor = 'default';
	// default sizes
	this.container.style.width = '640px';
	this.container.style.height = '480px';
	this.container.style.fontSize = '20px';

	/* add event handlers */
	setEventHandler(this.container, "mousemove", slimeyMove);
	setEventHandler(this.container, "mouseup", slimeyDrop);
	setEventHandler(this.container, "click", slimeyDeselect);
	setEventHandler(this.container, "resize", this.resized, this);
	setEventHandler(this.resizeHandle, "mousedown", slimeyDrag);
	setEventHandler(this.contentEditor, "blur", function() {
			var val = this.value;
			if (this.editElement.tagName == 'UL' || this.editElement.tagName == 'OL') {
				val = '<li>' + val + '</li>';
				val = val.replace(/\n/g, '</li><li>');
			} else if (this.editElement.tagName == 'DIV') {
				val = val.replace(/\n/g, '<br>');
			}
			var action = new SlimeyEditContentAction(this.slimey, val, this.editElement);
			this.slimey.editor.performAction(action);
			this.style.visibility = 'hidden';
			this.style.top = -1000;
			this.editElement = null;
		});
	setEventHandler(this.contentEditor, "keyup", function(e) {
			if (!e) var e = window.event;
			if (e.keyCode == 27) {
				this.onblur();
			}
		});
	setEventHandler(this.contentEditor, "click", function(e) {
			if (!e) e = window.event;
			if (this.editElement) {
				this.slimey.editor.click(this.editElement);
			}
			stopPropagation(e);
			return false;
		});
}


/**
 *  returns the editor's container
 */
SlimeyEditor.prototype.getContainer = function() {
	return this.container;
}

/**
 *  returns the generated HTML
 */
SlimeyEditor.prototype.getHTML = function() {
	if (this.selected) {
		this.selected.className = 'slimeyElement';
	}

	var html = '';
	for (var elem=this.container.firstChild; elem; elem = elem.nextSibling) {
		if (elem.nodeType == 1 && !elem.systemElement) {
			/* starting tag */
			html += '<' + elem.tagName.toLowerCase();
			if (elem.src) {
				html += ' src="' + elem.src + '"';
			}
			html += ' style="' + elem.style.cssText + '"';
			html += '>\n';
			
			if (elem.innerHTML) {
				/* inner HTML */
				html += elem.innerHTML + '\n';
			}
			
			/* closing tag */
			html += '</' + elem.tagName.toLowerCase() + '>\n';
		}
	}

	if (this.selected) {
		this.selected.className = 'slimeySelectedElement';
	}

	return html;
}

/**
 *  sets the editor's content as HTML
 */
SlimeyEditor.prototype.setHTML = function(html) {
	this.deselect();
	this.container.innerHTML = html;
	for (var elem=this.container.firstChild; elem; elem = elem.nextSibling) {
		if (elem.nodeType == 1) {
			elem.slimey = this.slimey;
			setEventHandler(elem, "mousedown", slimeyDrag);
			setEventHandler(elem, "click", slimeyClick);
			setEventHandler(elem, "mouseover", slimeyHighlight);
			setEventHandler(elem, "mouseout", slimeyLowshadow);
			setEventHandler(elem, "dblclick", slimeyEdit);
			if (elem.tagName == 'IMG') {
				elem.resizable = true;
				elem.title = lang("drag the bottom right corner to resize");
			} else {
				elem.editable = true;
				elem.resizable = true;
				elem.title = lang("double click to edit content");
			}
			elem.className = 'slimeyElement';
			if (!elem.style.zIndex) {
				elem.style.zIndex = 10000;
			}
			elem.style.cursor = 'move';
		}
	}
	this.container.appendChild(this.resizeHandle);
	this.container.appendChild(this.contentEditor);
}

/**
 *  moves the editor's DOM to another container
 */
SlimeyEditor.prototype.getDOM = function(container) {
	for (var i=0; i < this.container.childNodes.length; i++) {
		var elem = this.container.childNodes.item(i);
		if (!elem.systemElement) {
			this.container.removeChild(elem);
			i--;
			container.appendChild(elem);
		}
	}

	return container;
}

/**
 *  sets the editor's content as DOM
 */
SlimeyEditor.prototype.setDOM = function(container) {
	this.deselect();
	for (var i=0; i < this.container.childNodes.length; i++) {
		var elem = this.container.childNodes.item(i);
		if (!elem.systemElement) {
			this.container.removeChild(elem);
			i--;
		}
	}
	for (var i=0; i < container.childNodes.length; i++) {
		var elem = container.childNodes.item(i);
		container.removeChild(elem);
		i--;
		this.container.appendChild(elem);
	}
}

/**
 *  returns the currently selected element
 */
SlimeyEditor.prototype.getSelected = function() {
	return this.selected;
}

/**
 *  selects an element in the editor
 *  	obj: element to be selected
 */
SlimeyEditor.prototype.select = function(obj) {
	if (this.selected) {
		this.deselect();
	}
	this.selected = obj;
	obj.className = 'slimeySelectedElement';
	this.fireEvent('selectionChange');
	if (obj.resizable) {
		this.resizeHandle.style.visibility = 'visible';
		this.resizeHandle.style.left = (getPercentValue(obj.style.left) + getPercentValue(obj.style.width)) + '%';
		this.resizeHandle.style.top = (getPercentValue(obj.style.top) + getPercentValue(obj.style.height)) + '%';
	}
}

/**
 *  deselects the currently selected element
 */
SlimeyEditor.prototype.deselect = function() {
	if (this.selected) {
		this.selected.className = 'slimeyElement';
	}
	this.selected = null;
	this.resizeHandle.style.visibility = 'hidden';
	this.fireEvent('selectionChange');
}

/**
 *  performs an actions and adds it to the undo stack
 */
SlimeyEditor.prototype.performAction = function(action) {
	action.perform();
	this.undoStack.push(action);
	this.redoStack.clear();
	this.fireEvent('actionPerformed');
}

/**
 *  undoes last action
 */
SlimeyEditor.prototype.undo = function() {
	var action = this.undoStack.pop();
	if (action) {
		action.undo();
		this.redoStack.push(action);
	}
	this.fireEvent('actionPerformed');
}

/**
 *  redoes last action
 */
SlimeyEditor.prototype.redo = function() {
	var action = this.redoStack.pop();
	if (action) {
		action.perform();
		this.undoStack.push(action);
	}
	this.fireEvent('actionPerformed');
}

/*--- SlimeyEditor listeners ---*/
SlimeyEditor.prototype.addEventListener = function(event, callback, scope) {
	var listeners = this.events[event];
	listeners[listeners.length] = {
		callback: callback,
		scope: scope
	};
}

SlimeyEditor.prototype.removeEventListener = function(event, callback) {
	var listeners = this.events[event];
	for (i=0; i < listeners.length; i++) {
		if (listeners[i] == callback) {
			if (listeners.length > 1) {
				listeners[i] = listeners[listeners.length];
			}
			listeners.length--;
		}
	}
}

SlimeyEditor.prototype.fireEvent = function(event) {
	var listeners = this.events[event];
	for (i=0; i < listeners.length; i++) {
		listeners[i].callback.call(listeners[i].scope);
	}
}

/*--- SlimeyEditor events ---*/

/**
 *  handles click events - selects the clicked element in the editor
 *  	obj: clicked element
 *  	e: mouseclick event
 */
SlimeyEditor.prototype.click = function(obj, e) {
	if (obj != this.selected) {
		this.select(obj);
	}
}


/**
 *  handles double click events - begins editing of an element's content
 *  	obj: clicked element
 *  	e: mouseclick event
 */
SlimeyEditor.prototype.dblclick = function(obj, e) {
	if (obj != this.selected) {
		this.select(obj);
	}
	if (!obj.editable) {
		return;
	}
	this.contentEditor.editElement = obj;
	this.contentEditor.style.visibility = 'visible';
	this.contentEditor.style.fontFamily = obj.style.fontFamily;
	this.contentEditor.style.color = obj.style.color;
	this.contentEditor.style.fontSize = obj.style.fontSize;
	this.contentEditor.style.fontWeight = obj.style.fontWeight;
	this.contentEditor.style.fontStyle = obj.style.fontStyle;
	this.contentEditor.style.textDecoration = obj.style.textDecoration;
	this.contentEditor.style.textAlign = obj.style.textAlign;
	this.contentEditor.style.left = obj.offsetLeft + 'px';
	this.contentEditor.style.top = obj.offsetTop + 'px';
	this.contentEditor.style.width = obj.offsetWidth + 'px';
	this.contentEditor.style.height = obj.offsetHeight + 'px';
	var val = obj.innerHTML;
	if (obj.tagName == 'UL' || obj.tagName == 'OL') {
		val = val.replace(/<\/li><li>/gi, '\n');
		val = val.replace(/<li>|<\/li>/gi, '');
	} else if (obj.tagName == 'DIV') {
		val = val.replace(/<br>/gi, '\n');
	}
	this.contentEditor.value = val;
	this.contentEditor.focus();
}

/**
 *  handles dragging events - the dragged element becomes movable with the mouse
 *  	obj: dragged element
 *  	e: mousedown event
 */
SlimeyEditor.prototype.drag = function(obj, e) {
	this.current = obj;
	if (!obj.systemElement) {
		this.select(obj);
	}
	var pos = getMousePosition(e, this.container);
	this.hSize = this.container.offsetWidth;
	this.vSize = this.container.offsetHeight;
	var xpercent = getPercentValue(obj.style.left);
	var ypercent = getPercentValue(obj.style.top);
	if (xpercent > 100) xpercent = 50;
	if (ypercent > 100) ypercent = 50;
	var w = Math.round(xpercent * this.hSize / 100);
	var h = Math.round(ypercent * this.vSize / 100);
	this.difx = pos.x - w;
	this.dify = pos.y - h;
	this.dragx = this.movex = xpercent;
	this.dragy = this.movey = ypercent;
}

/**
 *  handles mousemove events - moves the currently dragged element
 *  	e: mousemove event
 */
SlimeyEditor.prototype.move = function(e) {
	if (this.current) {
		var pos = getMousePosition(e, this.container);
		this.movex = Math.round((pos.x - this.difx) * 100 / this.hSize);
		this.movey = Math.round((pos.y - this.dify) * 100 / this.vSize);
		this.current.style.left = this.movex + '%';
		this.current.style.top = this.movey + '%';
		this.printDebug("(" + this.current.style.left + ", " + this.current.style.top + ")");
		if (this.current.resizable) {
			this.resizeHandle.style.left = (getPercentValue(this.current.style.left) + getPercentValue(this.current.style.width)) + '%';
			this.resizeHandle.style.top = (getPercentValue(this.current.style.top) + getPercentValue(this.current.style.height)) + '%';
		}
	}
	if (this.current == this.resizeHandle) {
		this.selected.style.width = (getPercentValue(this.resizeHandle.style.left) - getPercentValue(this.selected.style.left)) + '%';
		this.selected.style.height = (getPercentValue(this.resizeHandle.style.top) - getPercentValue(this.selected.style.top)) + '%';
	}
}

/**
 *  handles mouseup events - drops the currently dragged element
 */
SlimeyEditor.prototype.drop = function() {
	if (this.current) {
		if (!this.current.systemElement) {
			/* an item was moved */
			if (this.current.style.position == 'absolute' && (this.dragx != this.movex || this.dragy != this.movey)) {
				var action = new SlimeyMoveAction(this.slimey, this.movex + '%', this.movey + '%', this.dragx + '%', this.dragy + '%');
				this.performAction(action);
			}
		} else {
			/* an item was resized */
			var neww = (getPercentValue(this.resizeHandle.style.left) - getPercentValue(this.selected.style.left)) + '%';
			var newh = (getPercentValue(this.resizeHandle.style.top) - getPercentValue(this.selected.style.top)) + '%';
			var oldw = (this.dragx - getPercentValue(this.selected.style.left)) + '%';
			var oldh = (this.dragy - getPercentValue(this.selected.style.top)) + '%';
			if (neww != oldw || newh != oldh) {
				var action = new SlimeyResizeAction(this.slimey, neww, newh, oldw, oldh);
				this.performAction(action);
				if (this.contentEditor.editElement) {
					this.contentEditor.style.width = neww;
					this.contentEditor.style.height = newh;
				}
			}
		}
	}
	this.current = null;
}

/**
 *  called when the editor is resized. Adjusts the editor's font size.
 */
SlimeyEditor.prototype.resized = function() {
	var h = this.container.offsetHeight;
	this.container.style.fontSize = (h / 24) + 'px';
}

SlimeyEditor.prototype.printDebug = function(text) {
	/*if (! this.debug) {
		this.debug = document.createElement('div');
		this.debug.style.position = 'absolute';
		this.debug.style.right = '0px';
		this.debug.style.top = '0px';
		this.container.appendChild(this.debug);
	}
	this.debug.innerHTML = text;*/
}

/*--------------- GLOBAL EVENTS -------------------------------------------------------*/

function slimeyClick(e) {
	this.slimey.editor.click(this);
	
	stopPropagation(e);
	return false;
}

function slimeyHighlight() {
	this.className = 'slimeyHighlightedElement';
}

function slimeyLowshadow() {
	if (this == this.slimey.editor.getSelected()) {
		this.className = 'slimeySelectedElement';
	} else {
		this.className = 'slimeyElement';
	}
}

function slimeyDrag(e) {
	if (!e) var e = window.event;
	this.slimey.editor.drag(this, e);
	
	stopPropagation(e);
	return false;
}

function slimeyMove(e) {
	if (!e) var e = window.event;
	try {
		this.slimey.editor.move(e);
		
		stopPropagation(e);
	} catch(e) {}
	return false;
}

function slimeyDrop(e) {
	this.slimey.editor.drop();
}

function slimeyDeselect(e) {
	try {
		this.slimey.editor.deselect();
	} catch (e) {}
}

function slimeyEdit(e) {
	this.slimey.editor.dblclick(this, e);
	
	stopPropagation(e);
	return false;
}
