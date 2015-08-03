/*
 *  Slimey - SLIdeshow Microformat Editor - http://slimey.sourceforge.net
 *  Copyright (C) 2007 - 2008 Ignacio de Soto
 *
 *  Slides navigation
 */

/**
 *  class SlimeyNavigation - implements functionality for navigating through slides
 *  	slimey: the slimey object that references the editor, navigation and toolbar
 */
var SlimeyNavigation = function(slimey) {
	this.slimey = slimey;
	this.slimey.editor.addEventListener('actionPerformed', this.saveCurrentSlide, this);
	this.container = document.createElement('div');
	this.container.className = 'slimeyNavigation';
	this.slides = new Array();
	this.doms = new Array();
	this.divSlides = new Array();
	this.divSpacers = new Array();

	// initialize slides content
    var file = unescapeSLIM(this.slimey.slimContent);
    var divslides = file.split('<div class="slide">');
    this.slides[0] = '';
    for (var i=1; i < divslides.length; i++) {
        this.slides[i] = divslides[i].substr(0, divslides[i].lastIndexOf("</div>"));
    }

    // initialize html
    var spacer = this.createSpacerDiv(1);
    this.container.appendChild(spacer);
    for (i=1; i < this.slides.length; i++) {
        var slide = this.createSlideDiv(i);
        this.container.appendChild(slide);
        var spacer = this.createSpacerDiv(i + 1);
        this.container.appendChild(spacer);
    }

	this.currentSlide = 0;
    if (this.slides.length > 1) {
        // select first slide
        this.getSlide(1);
    }
}

/**
 *  returns the navigation's container
 */
SlimeyNavigation.prototype.getContainer = function() {
	return this.container;
}

SlimeyNavigation.prototype.getSlide = function(num) {
    if (num == this.currentSlide) {
        return;
    }

    // save current slide and view clicked slide
    if (this.currentSlide != 0) {
        var html = this.slimey.editor.getHTML();
        this.divSlides[this.currentSlide].className = 'slidePreview';
		this.divSlides[this.currentSlide].parentNode.className = 'slideBorder';
        this.slides[this.currentSlide] = html;

		this.doms[this.currentSlide] = document.createElement('div');
		this.slimey.editor.getDOM(this.doms[this.currentSlide]);
		
        this.divSlides[this.currentSlide].innerHTML = html;
    }
	if (this.doms[num]) {
		this.slimey.editor.setDOM(this.doms[num]);
	} else {
		this.slimey.editor.setHTML(this.slides[num]);
	}

    this.currentSlide = num;
    this.divSlides[this.currentSlide].className = 'slidePreviewSel';
	this.divSlides[this.currentSlide].parentNode.className = 'slideBorderSel';

    return false;
}

SlimeyNavigation.prototype.saveCurrentSlide = function() {
	var html = this.slimey.editor.getHTML();
	this.slides[this.currentSlide] = html;
	var previewDiv = this.divSlides[this.currentSlide];
	if (previewDiv) {
		previewDiv.innerHTML = html;
	}
}

SlimeyNavigation.prototype.insertNewSlide = function(num, html, dom) {
	if (!html) {
		html = '<div style="font-size: 200%; font-weight: bold; font-family: sans-serif; position: absolute; left: 5%; top: 0%; width: 90%; height: 10%; text-align: center;">' + lang("new slide text") + '</div>';
	}

    var thisSpacer = this.divSpacers[num];

    // shift all slides
    for (i=this.divSlides.length - 1; i >= num; i--) {
        this.divSlides[i + 1] = this.divSlides[i];
        this.divSlides[i + 1].title = lang("slide {0}", i + 1);
        this.divSlides[i + 1].slideNumber = i + 1;
    }

    // shift all spacers including this one
    for (i=this.divSpacers.length - 1; i >= num; i--) {
        this.divSpacers[i + 1] = this.divSpacers[i];
        this.divSpacers[i + 1].spacerNumber = i + 1;
    }

    // shift slide data
    for (i=this.slides.length - 1; i >= num; i--) {
        this.slides[i + 1] = this.slides[i];
		this.doms[i + 1] = this.doms[i];
    }
    this.slides[num] = html;
	this.doms[num] = dom;

    // add new slide and spacer to DOM
    var parent = thisSpacer.parentNode;
    var newSpacer = this.createSpacerDiv(num);
    parent.insertBefore(newSpacer, thisSpacer);
    var newSlide = this.createSlideDiv(num);
    parent.insertBefore(newSlide, thisSpacer);

    // select newly added slide
    if (this.currentSlide >= num) {
        this.currentSlide++;
    }
    this.getSlide(num);
}

SlimeyNavigation.prototype.deleteSlide = function(num) {
    if (num < 1 || num > this.slides.length) {
        alert(lang("no slide to delete"));
        return;
    }
    var thisSpacer = this.divSpacers[num];

    // delete slide and spacer from DOM
    var slide = this.divSlides[num];
    slide.parentNode.parentNode.removeChild(slide.parentNode);
    var spacer = this.divSpacers[num];
    spacer.parentNode.removeChild(spacer);

    // shift all slides
    for (i=num; i < this.divSlides.length - 1; i++) {
        this.divSlides[i] = this.divSlides[i + 1];
        this.divSlides[i].slideNumber = i;
        this.divSlides[i].title = lang("slide {0}", i);
    }
    this.divSlides.length--;
    // shift all spacers
    for (i=num; i < this.divSpacers.length - 1; i++) {
        this.divSpacers[i] = this.divSpacers[i + 1];
        this.divSpacers[i].spacerNumber = i;
    }
    this.divSpacers.length--;
    // shift slide data
    for (i=num; i < this.slides.length - 1; i++) {
        this.slides[i] = this.slides[i + 1];
		this.doms[i] = this.doms[i + 1];
    }
    this.slides.length--;
	this.doms.length--;

    // select another slide
    this.currentSlide = 0;
    if (num < this.slides.length && num > 0) {
        this.getSlide(num);
    } else if (this.slides.length > 1) {
        this.getSlide(this.slides.length - 1);
    } else {
		this.slimey.editor.setHTML('<h1 align="center" style="color: #999999"><i>' + lang("click top left button to add a slide", lang("add new")) + '</i><h1>');
	}
}

SlimeyNavigation.prototype.moveSlide = function(num, to) {
    if (num < 1 || num > this.slides.length || num == to) {
        alert(lang("no slide to move"));
        return;
    }

	var diff = (to > num ? 1 : -1);

    // delete slide and spacer from DOM
    var slide = this.divSlides[num];
	var parent = slide.parentNode.parentNode;
    parent.removeChild(slide.parentNode);
    var spacer = this.divSpacers[num];
    parent.removeChild(spacer);
	if (num < to) {
		parent.insertBefore(spacer, this.divSlides[to].parentNode.nextSibling);
	} else {
		parent.insertBefore(spacer, this.divSlides[to].parentNode.previousSibling);
	}
	parent.insertBefore(slide.parentNode, spacer.nextSibling);

    // shift all slides
	var aux = this.divSlides[num];
    for (i=num; i != to; i+=diff) {
        this.divSlides[i] = this.divSlides[i + diff];
        this.divSlides[i].slideNumber = i;
        this.divSlides[i].title = lang("slide {0}", i);
    }
	this.divSlides[to] = aux;
	aux.slideNumber = to;
	aux.title = lang("slide {0}", to);
	// shift all spacers
	var aux = this.divSpacers[num];
    for (i=num; i != to; i+=diff) {
        this.divSpacers[i] = this.divSpacers[i + diff];
        this.divSpacers[i].spacerNumber = i;
    }
	this.divSpacers[to] = aux;
	aux.spacerNumber = to;
    // shift slide data
	var auxS = this.slides[num];
	var auxD = this.doms[num];
    for (i=num; i != to; i+=diff) {
        this.slides[i] = this.slides[i + diff];
		this.doms[i] = this.doms[i + diff];
    }
    this.slides[to] = auxS;
	this.doms[to] = auxD;
	
	this.currentSlide = to;
}

SlimeyNavigation.prototype.getSLIMContent = function() {
    // save current edited text
    var html = this.slimey.editor.getHTML();
    this.slides[this.currentSlide] = html;

    // generate SLIM content
    var slim = '';
    for (i=1; i < this.slides.length; i++) {
        slim += '<div class="slide">' + this.slides[i] + '</div>';
    }
	
	return slim;
}

SlimeyNavigation.prototype.createSlideDiv = function(num) {
    var slide = document.createElement('div');
    slide.slideNumber = num;
    slide.slimey = this.slimey;
    slide.className = 'slidePreview';
    slide.title = lang("slide {0}", num);
	slide.style.position = 'relative';
    slide.innerHTML = this.slides[num];
    setEventHandler(slide, "click", function() {
		var num = this.slideNumber;
		var action = new SlimeyChangeSlideAction(this.slimey, num);
		this.slimey.editor.performAction(action);
	});
	setEventHandler(slide, "mouseover", function() {
		this.className = 'slidePreviewH';
		this.parentNode.className = 'slideBorderH';
	});
    setEventHandler(slide, "mouseout", function() {
		var num = this.slideNumber;
		var sel = (num == this.slimey.navigation.currentSlide?'Sel':'');
		this.className = 'slidePreview' + sel;
		this.parentNode.className = 'slideBorder' + sel;
	});

	var border = document.createElement('div');
	border.className = 'slideBorder';
	border.appendChild(slide);
	this.divSlides[num] = slide;
    return border;
}

SlimeyNavigation.prototype.createSpacerDiv = function(num) {
    var spacer = document.createElement('div');
    spacer.spacerNumber = num;
    spacer.slimey = this.slimey;
    spacer.className = 'previewSpacer';
    spacer.title = lang("click to insert a new slide");
    setEventHandler(spacer, "click", function() {
		var action = new SlimeyInsertSlideAction(this.slimey, this.spacerNumber);
		this.slimey.editor.performAction(action);
		this.className = 'previewSpacer';
	});
    setEventHandler(spacer, "mouseover", function() { this.className = 'previewSpacerH'; });
    setEventHandler(spacer, "mouseout", function() { this.className = 'previewSpacer'; });
    this.divSpacers[num] = spacer;
    return spacer;
}

SlimeyNavigation.prototype.addNewSlide = function() {
	var action = new SlimeyInsertSlideAction(this.slimey, this.currentSlide + 1);
	this.slimey.editor.performAction(action);
}

SlimeyNavigation.prototype.deleteCurrentSlide = function() {
	if (this.currentSlide > 0) {
		var action = new SlimeyDeleteSlideAction(this.slimey, this.currentSlide);
		this.slimey.editor.performAction(action);
	}
}

SlimeyNavigation.prototype.moveSlideDown = function() {
	if (this.currentSlide < this.slides.length - 1) {
		var action = new SlimeyMoveSlideAction(this.slimey, this.currentSlide, this.currentSlide + 1);
		this.slimey.editor.performAction(action);
	}
}

SlimeyNavigation.prototype.moveSlideUp = function() {
	if (this.currentSlide > 1) {
		var action = new SlimeyMoveSlideAction(this.slimey, this.currentSlide, this.currentSlide - 1);
		this.slimey.editor.performAction(action);
	}
}
