/*
Copyright (c) 2006, Gustavo Ribeiro Amigo
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the author nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/


var Sound = function(swf) {
	if (!swf) {
		swf = "public/assets/flash/SoundBridge.swf";
	}
	if (!Sound.count) {
		Sound.count = 1;
	} else {
		Sound.count++;
	}
	this.id = 'sound' + Sound.count;
	if (!Sound.instances) {
		Sound.instances = {};
	}
	Sound.instances[this.id] = this;
		
	// create swf movie
	var movie = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="0" height="0"'; 
	movie += ' id="' + this.id + '"'; 
	movie += ' align="middle">';
	movie += '<param name="movie" value="' + swf + '" />';
	movie += '<param name="quality" value="high" />';
	movie += '<param name="bgcolor" value="#ffffff" />';
	movie += '<param name="FlashVars" value="id=' + this.id + '"/>';
	movie += '<param name="allowScriptAccess" value="always"/>';
	movie += '<embed src="' + swf + '" FlashVars="id=' + this.id + '"'; 
	movie += ' allowScriptAccess="always" quality="high" bgcolor="#ffffff" width="0" height="0"'; 
	movie += ' name="' + this.id + '" align="middle" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
	movie += '</object>';

	var sounds = document.getElementById('__flash_sounds__');
	if (!sounds) {
		sounds = document.createElement("div");
		sounds.id = "__flash_sounds__";
		document.body.appendChild(sounds);
	}
	sounds.innerHTML += movie; 
};

Sound.prototype.loadSound = function(url, streaming) {
	return this.call('loadSound', url, streaming);
};

Sound.prototype.start = function() {
	return this.call('start');
};

Sound.prototype.stop = function() {
	return this.call('stop');
};

Sound.prototype.getId3 = function() {
	return this.call('id3');
};

Sound.prototype.getPan = function() {
	return this.call('getPan');
};

Sound.prototype.getTransform = function() {
	return this.call('getTransform');
};

Sound.prototype.getVolume = function() {
	return this.call('getVolume');
};

Sound.prototype.setPan = function(value) {
	return this.call('setPan', value);
};

Sound.prototype.setTransform = function(transformObject) {
	return this.call('setTransform', transformObject);
};

Sound.prototype.setVolume = function(value) {
	return this.call('setVolume', value);
};

Sound.prototype.start = function(secondOffset, loops) {
	return this.call('start', secondOffset, loops);
};

Sound.prototype.getDuration = function() {
	return this.call('getDuration');
};

Sound.prototype.setDuration = function(value) {
	return this.call('setDuration', value);
};

Sound.prototype.getPosition = function() {
	return this.call('getPosition');
};

Sound.prototype.setPosition = function(value) {
	return this.call('setPosition', value);
};

Sound.prototype.getBytesLoaded = function() {
	return this.call('getBytesLoaded');
};

Sound.prototype.getBytesTotal = function() {
	return this.call('getBytesTotal');
};

Sound.prototype.onLoad = function(success) {
	this.debug('Sound: onLoad(' + success + ') event triggered');
};

Sound.prototype.onSoundComplete = function() {
	this.debug('Sound: onSoundComplete() event triggered');
};

Sound.prototype.onID3 = function() {
	this.debug('Sound: onID3() event triggered');
};

Sound.prototype.call = function(method) {
	this.debug('Sound: ' + method + ' method invoked by ' + this.id);
	var args = new Array();
	for (var i=1; i < arguments.length; i++) {
		args.push(arguments[i]);
	}
	var movie = Sound.getMovie(this.id);
	if (movie && typeof(movie.proxyMethods)=='function') {
		return movie.proxyMethods(method, args);
	}
	return null;
};

Sound.prototype.debug = function(value) {
	var debug = document.getElementById('sound_tracer');
	if (debug) {
		debug.value += value + '\n';
	}
};

Sound.getMovie = function(movieName) {
	if (navigator.appName.indexOf("Microsoft") != -1) {
		return window[movieName];
	} else {
		return document[movieName];
	}
};
