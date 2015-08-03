/*
TODO:
- playlist filter
- playlist toolbar button for manually editing song info
*/

/**
 * Author: Ignacio de Soto <ignacio.desoto@fengoffice.com>
 * (c) 2008, 2009
 */

og.GooPlayer = function(config) {
	config = config || {};
	
	this.sound = config.sound || new Sound();
	this.playback = {
		paused: true,
		stopped: true,
		position: 0,
		duration: 0,
		bytesTotal: 0,
		loop: true,
		volume: 100,
		track: {}
	};
	
	this.fieldDesc = [
       {name: 'song'},
       {name: 'artist'},
       {name: 'album'},
       {name: 'track'},
       {name: 'year'},
       {name: 'duration'},
       {name: 'url'},
       {name: 'filename'},
       {name: 'id'}
    ];
    this.store = new Ext.data.SimpleStore({
        fields: this.fieldDesc
    });
    this.store.loadData([]);
    
    this.htmlTemplate = '<table class="gooplayer-panel"><tr><td><img width="48" height="48" src="{0}" /></td><td>' +
    		'<table class="gooplayer-info"><tr><td class="gooplayer-name">' + lang('song') + ':</td><td>{1}</td></tr>' +
    		'<tr><td class="gooplayer-name">' + lang('artist') + ':</td><td>{2}</td></tr>' +
    		'<tr><td class="gooplayer-name">' + lang('album') + ':</td><td>{3}</td></tr></table>';
    this.defaultImage = 'public/assets/themes/default/images/48x48/gooplayer.png';
	
	Ext.apply(config, {
		layout: 'border',
		cls: 'gooplayer',
		items: [{
			id: 'panel',
			xtype: 'panel',
			bodyBorder: false,
			border: false,
			region: 'north',
			html: String.format(this.htmlTemplate, this.defaultImage),
			height: 90,
			bbar: [{
            	tooltip: lang('previous'),
            	iconCls: 'ico-previous',
            	handler: this.previous,
            	scope: this
			},{
				id: 'play',
            	tooltip: lang('play'),
            	iconCls: 'ico-play',
            	handler: this.play,
            	scope: this
			},{
				id: 'pause',
            	tooltip: lang('pause'),
            	iconCls: 'ico-pause',
            	hidden: true,
            	handler: this.pause,
            	scope: this
			},{
            	tooltip: lang('stop'),
            	iconCls: 'ico-stop',
            	handler: this.stop,
            	scope: this
			},{
            	tooltip: lang('next'),
            	iconCls: 'ico-next',
            	handler: this.next,
            	scope: this
			},'-',{
				id: 'position',
				xtype: 'slider',
				width: 200,
				minValue: 0,
				maxValue: 200,
				value: 0,
				listeners: {
					'changecomplete': {
						fn: function(slider, value) {
							this.setProgress(value / 200);
						},
						scope: this
					}
				}
			},{
				id: 'time',
				xtype: 'label',
				text: '0:00 / 0:00',
				width: 70,
				style: 'padding-left: 10px'
			},'-',{
				id: 'mute',
            	tooltip: lang('mute'),
            	iconCls: 'ico-mute',
            	handler: this.mute,
            	scope: this
			},{
				id: 'unmute',
            	tooltip: lang('unmute'),
            	iconCls: 'ico-unmute',
            	hidden: true,
            	handler: this.unmute,
            	scope: this
			},{
				id: 'volume',
				xtype: 'slider',
				minValue: 0,
				maxValue: 100,
				value: 100,
				width: 70,
				listeners: {
					'change': {
						fn: function(slider, value) {
							this.setVolume(value);
						},
						scope: this
					}
				}
			}]
		},{
			id: 'grid',
			xtype: 'grid',
			region: 'center',
			bodyBorder: false,
			border: false,
			style: 'border-top-width: 1px',
			store: this.store,
			tbar: [{
				tooltip: lang('load from current workspace'),
				iconCls: 'ico-load-from-ws',
				handler: this.loadPlaylistFromWorkspace,
				scope: this
			},{
				tooltip: lang('load playlist from file'),
				iconCls: 'ico-playlist-load',
				handler: this.openPlaylistFromFile,
				scope: this
			},{
				tooltip: lang('save playlist to file'),
				iconCls: 'ico-playlist-save',
				handler: this.savePlaylist,
				scope: this
			},{
				tooltip: lang('clear playlist'),
				iconCls: 'ico-playlist-clear',
				handler: this.clearPlaylist,
				scope: this
			},'-',{
				tooltip: lang('remove selected from playlist'),
				iconCls: 'ico-delete',
				handler: this.removeSelectedFromPlaylist,
				scope: this	
			},'-',{
				tooltip: lang('shuffle playlist'),
				iconCls: 'ico-shuffle',
				handler: this.shufflePlaylist,
				scope: this
			},{
				tooltip: lang('toggle loop playlist'),
				iconCls: 'ico-loop',
				enableToggle: true,
	        	pressed: true,
	        	toggleHandler: function(item, pressed) {
	        		this.playback.loop = pressed;
	        	},
	        	scope: this
			}],
        	columns: [
        		{header: '#', width: 25, sortable: true, dataIndex: 'track', renderer: og.clean},
	            {id:'song', header: lang("song"), width: 120, sortable: true, dataIndex: 'song', renderer: function(v, p, r) { if (v) return v; else return og.clean(r.data.filename);}},
	            {header: lang("album"), width: 120, sortable: true, dataIndex: 'album', renderer: og.clean},
	            {header: lang("artist"), width: 120, sortable: true, dataIndex: 'artist', renderer: og.clean},
	            {header: lang("year"), width: 60, sortable: true, dataIndex: 'year', renderer: og.clean}
        	],
        	stripeRows: true,
        	autoExpandColumn: 'song',
        	title: lang('playlist'),
        	listeners: {
				'rowdblclick': {
        			fn: function(grid, row) {
        				//var record = this.getStore().getAt(row);
        				this.stop();
        				this.play();
        			},
        			scope: this
        		}
        	}
		}],
		listeners: {
			'beforedestroy': function() {
				this.stop();
			}
		}
	});
	og.GooPlayer.superclass.constructor.call(this, config);
};

Ext.extend(og.GooPlayer, Ext.Panel, {
	load: function() {
		
	},
	
	getGrid: function() {
		return this.findById('grid');
	},
	
	getPanel: function() {
		return this.findById('panel');
	},
	
	step: function() {
		this.playback.bytesTotal = this.sound.getBytesTotal();
		this.playback.bytesLoaded = this.sound.getBytesLoaded();
		if (this.playback.bytesTotal && this.playback.bytesLoaded) {
			var loaded = this.playback.bytesLoaded / this.playback.bytesTotal;
			// todo: show loaded progress
		}
		if (!this.playback.paused) {
			var position = this.sound.getPosition();
			if (position && position != this.playback.position) {
				// todo: show status playing
			} else {
				// todo: show status buffering
			}
			this.playback.position = position;
	
			var progress = 0;
			if (this.playback.track.duration && loaded < 1) {
				this.playback.duration = this.playback.track.duration;
			} else {
				this.playback.duration = this.sound.getDuration() / loaded;
			}
			if (position) {
				progress = position / this.playback.duration;
			}
			this.showProgress(progress, position, this.playback.duration);
			
			if (this.playback.record && this.sound.getId3()) {
				var id3 = this.sound.getId3();
				toSave = {};
				if (!this.playback.track.artist && (id3.artist || id3.TPE1)) {
					this.playback.record.set("artist", id3.artist || id3.TPE1);
					toSave.songartist = this.playback.track.artist;
				}
				if (!this.playback.track.album && (id3.album || id3.TALB)) {
					this.playback.record.set("album", id3.album || id3.TALB);
					toSave.songalbum = this.playback.track.album;
				}
				if (!this.playback.track.song && (id3.songname || id3.TIT2 || track.filename || track.url)) {
					this.playback.record.set("song", id3.songname || id3.TIT2 || track.filename || track.url);
					toSave.songname = this.playback.track.song;
				}
				if (!this.playback.track.year && id3.year) {
					this.playback.record.set("year", id3.year);
					toSave.songyear = this.playback.track.year;
				}
				if (!this.playback.track.track && id3.track) {
					this.playback.record.set("track", this.playback.track.track || id3.track);
					toSave.songtrack = this.playback.track.track;
				}
				if (loaded == 1 && this.playback.track.duration != this.playback.duration) {
					this.playback.record.set("duration", this.playback.duration);
					toSave.songduration = this.playback.duration;
				}
				var count = 0;
				for (var k in toSave) {
					count++;
				}
				if (this.playback.track.id && count) {
					og.openLink(og.getUrl('object', 'save_properties', {manager: 'ProjectFiles', id: this.playback.track.id}), {
						post: toSave
					}); 
				}
				if (count > 0) this.updateInfo();
			}
			
			if (progress >= 1 && loaded == 1 && this.playback.duration && position && !this.playback.paused) {
				if (this.playback.loop) {
					this.next();
				} else {
					this.stop();
				}
			}
		}
	},
	
	start: function() {
		this.stop();
		this.play();
	},
	
	play: function() {
		if (this.playback.paused) {
			var selected = this.getGrid().getSelectionModel().getSelected();
			if (!selected) {
				this.getGrid().getSelectionModel().selectFirstRow();
				var selected = this.getGrid().getSelectionModel().getSelected();
			}
			if (!selected) {
				// nothing to play
				return;
			} else {
				this.playback.track = selected.data;
			}
			this.getPanel().getBottomToolbar().items.get('play').hide();
            this.getPanel().getBottomToolbar().items.get('pause').show();
			this.playback.paused = false;
			if (this.playback.stopped) {
				this.loadTrack(this.playback.track, selected);
			}
			this.sound.setVolume(this.playback.volume);
			this.sound.start(this.playback.position / 1000, 1);
			this.playback.stopped = false;
			if (this.interval) clearInterval(this.interval);
			this.interval = setInterval(this.step.createDelegate(this), 500);
		}
	},
	
	pause: function() {
		if (!this.playback.paused) {
			this.getPanel().getBottomToolbar().items.get('pause').hide();
            this.getPanel().getBottomToolbar().items.get('play').show();
			this.playback.position = this.sound.getPosition();
			this.sound.stop();
			this.playback.paused = true;
			if (this.interval) clearInterval(this.interval);
		}
	},
	
	next: function() {
		this.playback.position = 0;
		this.playback.duration = 0;
		this.sound.start(0, 1);
		this.sound.stop();
		if (!this.getGrid().getSelectionModel().selectNext()) {
			this.getGrid().getSelectionModel().selectFirstRow();
		}
		var selected = this.getGrid().getSelectionModel().getSelected();
		if (selected) {
			this.playback.track = selected.data;
			this.loadTrack(this.playback.track, selected);
			this.playback.stopped = true;
			this.showProgress(0, 0, this.playback.duration);
			if (!this.playback.paused) {
				this.playback.paused = true;
				this.play();
			}
		} else {
			this.stop();
		}
	},
	
	previous: function() {
		this.playback.position = 0;
		this.playback.duration = 0;
		this.sound.start(0, 1);
		this.sound.stop();
		if (!this.getGrid().getSelectionModel().selectPrevious()) {
			this.getGrid().getSelectionModel().selectLastRow();
		}
		var selected = this.getGrid().getSelectionModel().getSelected();
		if (selected) {
			this.playback.track = selected.data;
			this.loadTrack(this.playback.track, selected);
			this.playback.stopped = true;
			this.showProgress(0, 0, this.playback.duration);
			if (!this.playback.paused) {
				this.playback.paused = true;
				this.play();
			}
		} else {
			this.stop();
		}
	},
	
	stop: function() {
		this.playback.paused = true;
		this.playback.stopped = true;
		this.playback.position = 0;
		this.sound.start(0, 1);
		this.sound.stop();
		this.showProgress(0, 0, this.playback.duration);
		this.getPanel().getBottomToolbar().items.get('pause').hide();
        this.getPanel().getBottomToolbar().items.get('play').show();
        if (this.interval) clearInterval(this.interval);
	},
	
	showProgress: function(progress, position, duration) {
		var progbar = this.getPanel().getBottomToolbar().items.get('position');
		if (!progbar.dragging) {
			progbar.suspendEvents();
			progbar.setValue(progress * 200);
			progbar.resumeEvents();
		}
		if (position) {
			var time = Math.floor((position / 1000) / 60) + ":" +
				String.leftPad(Math.floor((position / 1000) % 60), 2, '0') + " / " +
				Math.floor((duration / 1000) / 60) + ":" +
				String.leftPad(Math.floor((duration / 1000) % 60), 2, '0');
		} else {
			var time = "0:00 / 0:00";
		}
		var label = this.getPanel().getBottomToolbar().items.get('time');
		label.setText(time);
	},
	
	setPosition: function(position) {
		this.playback.position = position;
		if (this.playback.track.duration) {
			var progress = position / this.playback.track.duration;
		} else {
			var progress = position * this.sound.getBytesLoaded() / this.sound.getBytesTotal() / this.sound.getDuration();
		}
		var progbar = this.getPanel().getBottomToolbar().items.get('position');
		progbar.suspendEvents();
		progbar.setValue(progress * 200);
		progbar.resumeEvents();
		if (!this.playback.paused) {
			this.sound.start(this.playback.position / 1000, false);
		}
	},
	
	setProgress: function(progress) {
		if (this.playback.track.duration) {
			var position = progress * this.playback.track.duration;
		} else {
			var position = progress * this.sound.getBytesTotal() * this.sound.getDuration() / this.sound.getBytesLoaded();
		}
		this.setPosition(position);
	},
	
	setVolume: function(volume) {
		this.playback.volume = volume;
		this.sound.setVolume(volume);
		var vbar = this.getPanel().getBottomToolbar().items.get('volume');
		vbar.suspendEvents();
		vbar.setValue(volume);
		vbar.resumeEvents();
	},
	
	mute: function() {
		this.playback.previousVolume = this.sound.getVolume();
		this.sound.setVolume(0);
		this.getPanel().getBottomToolbar().items.get('mute').hide();
        this.getPanel().getBottomToolbar().items.get('unmute').show();
	},
	
	unmute: function() {
		this.sound.setVolume(this.playback.previousVolume || 100);
		this.getPanel().getBottomToolbar().items.get('unmute').hide();
        this.getPanel().getBottomToolbar().items.get('mute').show();
	},
	
	loadTrack: function(track, record) {
		this.playback.track = track;
		this.playback.record = record;
		this.sound.loadSound(track.url, true);
		this.updateInfo();
	},
	
	updateInfo: function() {
		var track = this.playback.track;
		var image = track.image || this.defaultImage;
		var artist = track.artist || lang('unknown');
		var album = track.album || lang('unknown');
		var song = track.song || track.filename || track.url;
		var html = String.format(this.htmlTemplate, og.clean(image), og.clean(song), og.clean(artist), og.clean(album));
		this.getPanel().body.update(html);
	},
	
	loadPlaylistFromWorkspace: function() {
		og.openLink(og.getUrl('files', 'get_mp3'), {
			onSuccess: function(data) {
				this.loadPlaylist(data.mp3);
			},
			scope: this
		});
	},
	
	clearPlaylist: function() {
		this.getGrid().getStore().loadData([]);
	},

	removeSelectedFromPlaylist: function() {
		var selected = [];
		var store = this.getGrid().getStore();
		var sm = this.getGrid().getSelectionModel();
		for (var i=0; i < store.getCount(); i++) {
			var record = store.getAt(i);
			if (sm.isSelected(record)) {
				store.remove(record);
				i--;
			}
		}
	},
	
	shufflePlaylist: function() {
		var store = this.getGrid().getStore();
		var sm = this.getGrid().getSelectionModel();
		var records = [];
		var c = store.getCount();
		for (var i=0; i < c - 1; i++) {
			var record = store.getAt(0);
			store.remove(record);
			records.push(record);
		}
		for (var i=0; i < records.length; i++) {
			store.insert(Math.floor(Math.random() * (store.getCount() + 1)), records[i]);
		}
	},
	
	loadPlaylist: function(playlist) {
		this.getGrid().getStore().loadData(playlist || []);
	},
	
	queueTrack: function(track) {
		var MP3Record = Ext.data.Record.create(this.fieldDesc);
		var r = new MP3Record({
			song: track[0],
			artist: track[1],
			album: track[2],
			track: track[3],
       		year: track[4],
       		duration: track[5],
       		url: track[6],
       		filename: track[7],
       		id: track[8]
		});
		this.getGrid().getStore().add(r);
	},
	
	loadPlaylistFromFile: function(id, autostart) {
		og.openLink(og.getUrl('files', 'download_file', {id: id}), {
			preventPanelLoad: true,
			scope: this,
			callback: function(success, data) {
				try {
					this.clearPlaylist();
					var track = og.xmlFetchTag(data, 'track');
					var count = 0;
					while (track.found) {
						var location = og.xmlFetchTag(track.value, 'location').value;
						var artist = og.xmlFetchTag(track.value, 'creator').value;
						var album = og.xmlFetchTag(track.value, 'album').value;
						var song = og.xmlFetchTag(track.value, 'title').value;
						var duration = og.xmlFetchTag(track.value, 'duration').value;
						var num = og.xmlFetchTag(track.value, 'trackNum').value;
						this.queueTrack([song, artist, album, num, '', duration, location]);
						track = og.xmlFetchTag(track.rest, 'track');
						count++;
					}
					if (count == 0) {
						og.msg(lang("error"), lang("file has no valid songs"));
					} else if (autostart) {
						this.start();
					}
				} catch (e) {
					og.err(lang("file has no valid songs"));
				}
			}
		});
	},
	
	openPlaylistFromFile: function() {
		og.ObjectPicker.show(function(objs) {
				if (objs.length < 1) return;
				if (objs[0].data.manager != 'ProjectFiles') {
					og.msg(lang("error"), lang("must choose a file"));
					return;
				}
				this.loadPlaylistFromFile(objs[0].data.object_id);
				
			}, this, {
				types: {
					'ProjectFiles': true
				}
			}
		);
	},
	
	savePlaylist: function() {
		Ext.Msg.prompt(lang('save'), lang('choose a filename') + ':',
			function(btn, text) {
				if (btn == 'ok') {
					if (text.length < 5 || text.substring(text.length - 5) != ".xspf") {
						text += ".xspf";
					}
					var file = '<?xml version="1.0" encoding="UTF-8"?>\n' +
							'<playlist version="1" xmlns="http://xspf.org/ns/0/">\n' +
    						'<trackList>\n';
					var store = this.getGrid().getStore();
					for (var i=0; i < store.getCount(); i++) {
						var record = store.getAt(i);
						var dur = record.data.duration;
						var url = record.data.url;
						var name = record.data.song;
						var artist = record.data.artist;
						var album = record.data.album;
						var num = record.data.track;
						file += "\t<track>\n";
						file += "\t\t<location>" + url + "</location>\n";
						if (artist) file += "\t\t<creator>" + artist + "</creator>\n";
						if (album) file += "\t\t<album>" + album + "</album>\n";
						if (name) file += "\t\t<title>" + name + "</title>\n";
						if (dur) file += "\t\t<duration>" + dur + "</duration>\n";
						if (num) file += "\t\t<trackNum>" + num + "</trackNum>\n";
						file += "\t</track>\n";
					}
					file += '</trackList>\n';
					file += '</playlist>\n';
					og.openLink(og.getUrl('files', 'save_document'), {
						post: {
							'file[name]': text,
							'fileContent': file,
							'fileMIME': 'application/xspf+xml'
						}
					});
				}
			}, this
		);
	}
});

Ext.reg("gooplayer", og.GooPlayer);

