var App = window.App || {};
App.engine  = {}; // engine namspace
App.modules = {}; // modules (such as AddTaskForm, AddMessageForm etc)
App.widgets = {}; // widgets (such as GroupedBlock, UserBoxMenu, PageAction)
App.engine = {
	showStatus: function(message) {
	},
	hideStatus: function() {
	}
};

// lang
if (typeof _lang != 'object') _lang = {};
if (typeof _langDefault != 'object') _langDefault = {};

function lang(name) {
	var value = _lang[name];
	if (!value) {
		value = _langDefault[name];
		if (!value) {
			return "Missing lang.js: " + name;
		}
	}
	for (var i=1; i < arguments.length; i++) {
		value = value.replace("{" + (i-1) + "}", arguments[i]);
	}
	return value;
}

function langhtml(name) {
	return '<span name="og-lang" id="og-lang-' + name + '">' + lang(name) + '</span>';
}

function addLangs(langs) {
	for (var k in langs) {
		_lang[k] = langs[k];
	}
}

function addLangsDefault(langs) {
	for (var k in langs) {
		_langDefault[k] = langs[k];
	}
}

var color_utils = {};
color_utils.base_convert = function(number, frombase, tobase) {
	return parseInt(number + '', frombase | 0).toString(tobase | 0);
}

color_utils.darker_html_color = function(htmlColor, percentage) {
	if (!percentage) percentage = 20;
	if (htmlColor[0] == '#') {
		htmlColor = htmlColor.substring(1);
	}
	if (htmlColor.length != 6) {
		return "#" + htmlColor;
	}
	var darkerColor = '';
	var pieces = [htmlColor[0]+""+htmlColor[1], htmlColor[2]+""+htmlColor[3], htmlColor[4]+""+htmlColor[5]];
	for (var i in pieces) {
    	var piece = pieces[i];
    	if (typeof(piece) == 'function') continue;
    	// convert from base16 to base10, reduce the value then come back to base16
		var tmp = color_utils.base_convert(piece, 16, 10);
		var amount = Math.floor(tmp * percentage / 100);
		var darkpiece = tmp - amount;
		if (darkpiece < 0) darkpiece = 0;
		if (darkpiece > 255) darkpiece = 255;
		var digits = color_utils.base_convert(darkpiece, 10, 16);
		while (digits.length <2) digits = "0"+digits;
		darkerColor += digits[0]+""+digits[1];
	}
	return '#'+darkerColor;
}

color_utils.make_transparent_color = function(htmlColor) {
	if (htmlColor[0] == '#') {
		htmlColor = htmlColor.substring(1);
	}
	if (htmlColor.length != 6) return null;
	var pieces = [color_utils.base_convert(htmlColor[0]+""+htmlColor[1], 16, 10), color_utils.base_convert(htmlColor[2]+""+htmlColor[3], 16, 10), color_utils.base_convert(htmlColor[4]+""+htmlColor[5], 16, 10)];
	while (pieces[0] < 192 || pieces[1] < 192 || pieces[2] < 192) {
		if (pieces[0] < 224) pieces[0] = parseInt(pieces[0]) + 16;
		if (pieces[1] < 224) pieces[1] = parseInt(pieces[1]) + 16;
		if (pieces[2] < 224) pieces[2] = parseInt(pieces[2]) + 16;
	}

	var transparentColor = '#';
	var i=0;
	while (i<pieces.length) {
    	var digits = color_utils.base_convert(pieces[i], 10, 16);
    	while (digits.length <2) digits = "0"+digits;
		transparentColor += digits[0]+""+digits[1];
		i++;
	}
	return transparentColor;
}