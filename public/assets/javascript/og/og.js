var og = {};
var ogTasks = {};
var ogTaskEvents = {};
ogTasksTask = {};
ogTasksMilestone = {};
ogTasksCompany = {};
ogTasksUser = {};
var vtindex = 0;
var vtlist = [];

var searchingMemberOnTheServer = [];

og.eventTimeouts = [];
og.otherData = [];

// default config (to be overridden by server)
og.hostName = '';
og.maxFileSize = 1024 * 1024;

og.showMailsTab = 0;
og.hiddenTabs = [];

// Set to true once the initial shell (tabs-panel, viewport, etc.) has been
// built in layout.js. Until then, og.openLink calls triggered by early
// clicks (e.g. header links rendered server-side before the shell exists)
// are queued instead of being silently dropped by og.newTab/processResponse.
og.appReady = false;
og.pendingLinks = [];

og.runWhenReady = function() {
	og.appReady = true;
	var pending = og.pendingLinks;
	og.pendingLinks = [];
	for (var i = 0; i < pending.length; i++) {
		og.openLink(pending[i].url, pending[i].options);
	}
};
// functions
og.msg =  function(title, text, timeout, classname, sound) {
	if (typeof timeout == 'undefined') timeout = 4;
	if (!classname) classname = "msg";

	var click_to_remove_msg = ''; // only show this message if message doesn't vanish by itself
	if (timeout == 0)
		click_to_remove_msg = '<div class="x-clear"></div><div class="click-to-remove">'+ lang('close') + ' X</div><div class="x-clear"></div>';


	var box = ['<div class="' + classname + '" title="' + lang('click to remove') + '">',
			'<div class="og-box-tl"><div class="og-box-tr"><div class="og-box-tc"></div></div></div>',
			'<div class="og-box-ml"><div class="og-box-mr"><div class="og-box-mc"><h3>{0}:</h3><p>{1}</p>',
			click_to_remove_msg,
			'</div></div></div>',
			'<input type="hidden" value="' + new Date().getTime() + '" />',
			'<div class="og-box-bl"><div class="og-box-br"><div class="og-box-bc"></div></div></div>',
			'</div>'].join('');

	if( !this.msgCt){
	    this.msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
	}
	//	this.msgCt.alignTo(document, 't-t');
	var m = Ext.DomHelper.append(this.msgCt, {html:String.format(box, title, text.replace(/([^>])\n/g, '$1<br/>\n'))}, true);
	Ext.get(m).on('click', function() {
		if (timeout > 0) {
			this.setStyle('display', 'none');
		} else {
			this.remove();
		}
	});
	m.timeout = 'aaaaa';
	if (timeout > 0) {
		m.slideIn('t').pause(timeout).ghost("t", {remove:true});
	} else {
		m.slideIn('t');
	}
	if (sound) {
		//og.systemSound.loadSound('public/assets/sounds/' + sound + '.mp3', true);
		//og.systemSound.start(0);
	}
};

og.updateClock = function(clockId){
	var clock = og.eventTimeouts[clockId + "clock"];
	if(clock) {
		clearTimeout(clock);
	}

	var startTime = og.otherData[clockId + "starttime"];
	var startSeconds = og.otherData[clockId + "startseconds"];



	var ts = document.getElementById(clockId + "timespan");
	if (ts){
		ts.innerHTML = og.calculateTimeForClock(startTime,startSeconds);

		og.eventTimeouts[clockId + "clock"] = setTimeout("og.updateClock('" + clockId + "')", 1002);
	} else {
		og.eventTimeouts[clockId + "clock"] = 0;
	}
};

og.calculateTimeForClock = function(startTime, startSeconds){
	var nowTime = new Date();
	var elapsed = ((nowTime.getElapsed(startTime) / 1000) + startSeconds).toFixed(0);
	var seconds = (elapsed % 60) / 1;
	var totalMinutes = (elapsed - seconds) / 60;
	var minutes = totalMinutes % 60;
	var totalHours = (totalMinutes - minutes) / 60;
	minutes = ( minutes < 10 ? "0" : "" ) + minutes;
  	seconds = ( seconds < 10 ? "0" : "" ) + seconds.toFixed(0);

  	return totalHours + ":" + minutes + ":" + seconds;
}

og.startClock = function(clockId, startSeconds){
	og.otherData[clockId + "starttime"] = new Date();
	og.otherData[clockId + "startseconds"] = startSeconds;
	og.updateClock(clockId);
};

og.err = function(text) {
	var errors = Ext.query("div.err");
	var maxErrors = 2;
	for (var i=0; i < errors.length - maxErrors + 1; i++) {
		Ext.fly(errors[i]).remove();
	}
	og.msg(lang("information"), text, 0, "err");
};

og.clearErrors = function(timeout) {
	var errors = Ext.query("div.err");
	for (var i=0; i < errors.length; i++) {
		if (timeout) {
			var inputs = Ext.fly(errors[i]).query('input');
			var ts = inputs[0].value;
			if (new Date().getTime() - ts > timeout*1000) {
				// clear error only if timeout seconds have ellapsed
				Ext.fly(errors[i]).remove();
			}
		} else {
			Ext.fly(errors[i]).remove();
		}
	}
};

og.hideAndShow = function(itemToHide, itemToDisplay){
	Ext.get(itemToHide).setDisplayed('none');
	Ext.get(itemToDisplay).setDisplayed('block');
};

og.hideAndShowByClass = function(itemToHide, classToDisplay, containerItemName, count){
	Ext.get(itemToHide).setDisplayed('none');

	var list;
	var container;
	if (containerItemName != ''){
		container = document.getElementById(containerItemName);
	} else container = document;

	list = container.getElementsByTagName("*");

	for(var i = 0; i < list.length; i++){
		var obj = list[i];
		if (obj.className != '' && obj.className.indexOf(classToDisplay) >= 0) {
			obj.style.display = '';
			if (count) {
				count--;
				if (count == 0) break;
			}
		}
	}
};


og.selectReportingMenuItem = function(link, divName, tab){
	var table = document.getElementById('reportingMenu');

	var list = table.getElementsByTagName('td');
	for(var i = 0; i < list.length; i++)
		if (list[i].className == 'report_selected_menu')
			list[i].className = 'report_unselected_menu';

	link.parentNode.className = 'report_selected_menu';
	link.blur();

	list = table.getElementsByTagName('div');
	for(var i = 0; i < list.length; i++)
		if (list[i].className == 'inner_report_menu_div')
			list[i].style.display = 'none';

	document.getElementById(divName).style.display='block';

	var url = og.getUrl('account', 'update_user_preference', {name: 'custom_report_tab', value: tab});
	og.openLink(url,{hideLoading:true});
}

og.dateselectchange = function(select, cls_selector) {
	if (!cls_selector) cls_selector = 'dateTr';
	var list = select.offsetParent.offsetParent.getElementsByTagName('tr');
	for(var i = 0; i < list.length; i++) {
		if (list[i].className == cls_selector) {
			list[i].style.display = select.value == '6'? 'table-row':'none';
		}
	}
}

og.timeslotTypeSelectChange = function(select, genid) {
	document.getElementById(genid + 'gbspan').style.display = select.value > 0? 'none':'inline';
	document.getElementById(genid + 'altgbspan').style.display = select.value > 0? 'inline':'none';

	document.getElementById(genid + 'task_ts_desc').style.display = select.value == 0 ? '' : 'none';
	document.getElementById(genid + 'general_ts_desc').style.display = select.value == 1 ? '' : 'none';

	var st_row = document.getElementById(genid + 'task_status_row');
	if (st_row) st_row.style.display = select.value == 1 ? 'none' : '';
}

og.switchDashboardView = function(view_as_list) {
	og.openLink(og.getUrl('account', 'update_user_preference', {name:'overviewAsList', value: view_as_list}), {
		hideLoading:true,
		callback: function(success, data) {
			var opanel = Ext.getCmp('overview-panel');
			opanel.defaultContent = {type: 'url', data: og.getUrl('dashboard', 'main_dashboard')};
			opanel.load(opanel.defaultContent);
		}
	});
}

og.switchToOverview = function(){
	og.switchDashboardView(1);
};

og.switchToDashboard = function(){
	og.switchDashboardView(0);
};

og.customDashboard = function (controller,action,params, reload) {
	if (!params) params = {};
	if (!controller) return false;
	if (!action) action = 'init'  ;
	var opanel = Ext.getCmp('overview-panel');
	if (opanel){
		var new_data = og.getUrl(controller,action, params) ;
		var content = {type: "url", data: new_data};
		opanel.defaultContent = content ;
		if (reload) {
			opanel.load(content);
		}
	}
}

og.resetDashboard = function () {
	var opanel = Ext.getCmp('overview-panel');
	if (opanel && opanel.defaultContent.data != "overview"){
		opanel.defaultContent = {type: "url", data: og.getUrl('dashboard','main_dashboard')};
		opanel.load(opanel.defaultContent);
	}
}

og.loading = function() {
	if (!this.loadingCt) {
		this.loadingCt = document.createElement('div');
		this.loadingCt.innerHTML = lang('loading');
		this.loadingCt.className = 'loading-indicator og-loading';
		this.loadingCt.onclick = function() {
			this.style.visibility = 'hidden';
			this.instances = 0;
		};
		this.loadingCt.instances = 0;
		document.body.appendChild(this.loadingCt);
	}
	this.loadingCt.instances++;
	this.loadingCt.style.visibility = 'visible';
};

og.hideLoading = function() {
	this.loadingCt.instances--;
	if (this.loadingCt.instances <= 0) {
		this.loadingCt.style.visibility = 'hidden';
	}
};

//get loading icon to put it wherever you want
og.getIndependentLoading = function() {
	loadingCt = document.createElement('div');
	loadingCt.innerHTML = lang('loading');
	loadingCt.className = 'loading-indicator';
	return loadingCt;
};

og.otherMsgCt = null;
og.showOtherMessage = function(msg, left_percent) {
	if (!og.otherMsgCt) {
		og.otherMsgCt = document.createElement('div');
		og.otherMsgCt.innerHTML = msg;
		og.otherMsgCt.className = 'loading-indicator';
		og.otherMsgCt.style.position = 'absolute';
		og.otherMsgCt.style.left = (left_percent != null ? left_percent : '15%');
		og.otherMsgCt.style.zIndex = 1000000;
		og.otherMsgCt.style.cursor = 'pointer';
		og.otherMsgCt.onclick = function() {
			this.style.visibility = 'hidden';
			this.instances = 0;
		};
		og.otherMsgCt.instances = 0;
		document.body.appendChild(og.otherMsgCt);
	}
	og.otherMsgCt.instances++;
	og.otherMsgCt.style.visibility = 'visible';
};

og.hideOtherMessage = function() {
	og.otherMsgCt.instances--;
	if (og.otherMsgCt.instances <= 0) {
		og.otherMsgCt.style.visibility = 'hidden';
	}
};

og.toggle = function(id, btn) {
	var obj = Ext.fly(id);
	if (obj.isDisplayed()) {
		obj.slideOut("t", {duration: 0.5, useDisplay: true});
		if (btn) Ext.fly(btn).replaceClass('toggle_expanded', 'toggle_collapsed');
	} else {
		obj.slideIn("t", {duration: 0.5, useDisplay: true});
		if (btn) Ext.fly(btn).replaceClass('toggle_collapsed', 'toggle_expanded');
	}
};

og.toggleAndBolden = function(id, btn) {
	var obj = Ext.get(id);
	if (obj.isDisplayed()) {
		obj.dom.style.display = 'none';
		if (btn) {
			btn.style.fontWeight = 'normal';
		}
	} else {
		obj.dom.style.display = 'block';
		$("#"+id).closest('form').parent().animate({
		   scrollTop: $("#"+id).offset().top - 250
		});
		if (btn) {
			btn.style.fontWeight = 'bold';
		}
	}
};

og.toggleSimpleTab = function(id, contentContainer, tabContainer, tab) {
	var tc = Ext.getDom(tabContainer);
	var child = tc.firstChild;
	while (child) {
		if (child.style) {
			child.style.fontWeight = 'normal';
		}
		child = child.nextSibling;
	}
	if (tab) {
		tab.style.fontWeight = 'bold';
		tab.blur();
	}

	var cc = Ext.getDom(contentContainer);
	var child = cc.firstChild;
	while (child) {
		if (child.style) {
			child.style.display = 'none';
		}
		child = child.nextSibling;
	}
	var obj = Ext.get(id);
	obj.dom.style.display = 'block';
};

og.showAndHide = function(idToShow, idsToHide, displayType){
	if (!displayType)
		displayType = 'block';
	var show = document.getElementById(idToShow);
	if(show){
		show.style.display = displayType;
		for(var i = 0; i < idsToHide.length; i++){
			var hide = document.getElementById(idsToHide[i]);
			if (hide) hide.style.display = 'none';
		}
	}
};

og.toggleAndHide = function(id, btn) {
	var obj = Ext.getDom(id);
	if (obj.style.display == 'block') {
		obj.style.display = 'none';
		if (btn)
			btn.style.display = 'none';
	} else {
		obj.style.display = 'block';
		if (btn)
			btn.style.display = 'none';
	}
};


og.getUrl = function(controller, action, args) {

	var url = og.getHostName() + "/index.php";
	url += "?c=" + controller;
	url += "&a=" + action;
	for (var key in args) {
		url += "&" + encodeURIComponent(key) + "=" + encodeURIComponent(args[key]);
	}
	return url;
};

og.getSandboxUrl = function(controller, action, args) {
	var url = og.getSandboxName() + "/index.php";
	url += "?c=" + controller;
	url += "&a=" + action;
	for (var key in args) {
		url += "&" + encodeURIComponent(key) + "=" + encodeURIComponent(args[key]);
	}
	return url;
};

og.filesizeFormat = function(fs) {
	if (fs > 1024 * 1024) {
		var total = Math.round(fs / 1024 / 1024 * 10);
		return total / 10 + "." + total % 10 + " MB";
	} else {
		var total = Math.round(fs / 1024 * 10);
		return total / 10 + "." + total % 10 + " KB";
	}
};


og.makeAjaxUrl = function(url, params) {
	//og.msg("","Make ajax url"+url , 15);
	//alert(params.toSource()) ;
	var q = url.indexOf('?');
	var n = url.indexOf('#');
	var ap = "";
	if ( url.indexOf("context") < 0 && (params && !params.context) ) {
		var ap = "context=" + og.contextManager.plainContext();
		if ( url.indexOf("currentdimension") < 0 && !params.currentdimension)  {
			ap += "&currentdimension=" + og.contextManager.currentDimension;
		}
	}

	if (url.indexOf("ajax=true") < 0) {
		var aj = "&ajax=true";
	} else {
		var aj = "";
	}
	var p = "";
	if (params) {
		if (typeof params == 'string') {
			if (params != ''){
				p = "&" + params;
			}
		} else {
			for (var k in params) {
				p += "&" + encodeURIComponent(k) + "=" + encodeURIComponent(params[k]);
			}
		}
	}

	if (q < 0) {
		if (n < 0) {
			return url + "?" + ap + aj +  p;
		} else {
			return url.substring(0, n) + "?" + ap + aj + (url.substring(n) != ''? "&":"") + url.substring(n) + p;
		}
	} else {
		return url.substring(0, q + 1) + ap + aj + (url.substring(q + 1) != ''? "&":"") + url.substring(q + 1) + p;
	}
};

og.createHTMLElement = function(config) {
	var tag = config.tag || 'p';
	var attrs = config.attrs || {};
	var content = config.content || {};
	var elem = document.createElement(tag);
	for (var k in attrs) {
		elem[k] = attrs[k];
	}
	if (typeof content == 'string') {
		elem.innerHTML = content;
	} else {
		for (var i=0; i < content.length; i++) {
			elem.appendChild(og.createHTMLElement(content[i]));
		}
	}
	return elem;
};

og.debug = function(obj, level) {
	if (!level) level = 0;
	if (level > 5) return "";
	var pad = "";
	var str = "";
	for (var i=0; i < level; i++) {
		pad += "  ";
	}
	if (!obj) {
		str = "NULL";
	} else if (typeof obj == 'object') {
		str = "";
		for (var k in obj) {
			str += ",\n" + pad + "  ";
			str += k + ": ";
			str += og.debug(obj[k], level + 1);
		}
		str = "{" + str.substring(1) + "\n" + pad + "}";
	} else if (typeof obj == 'string') {
		str = '"' + obj + '"';
	} else {
		str = obj;
	}
	return str;
};

og.captureLinks = function(id, caller) {
	var element = document.getElementById(id);
	if (!element) element = document;
	var links = element.getElementsByTagName("a");
	for (var i=0; i < links.length; i++) {
		var link = links[i];
		if (!link.href || Ext.isGecko && link.href == link.baseURI || link.href.indexOf('mailto:') == 0 || link.href.indexOf('javascript:') == 0 || link.href.indexOf('#') >= 0) continue;
		if (link.target && link.target[0] == '_') continue;
		if (caller && !link.target) {
			link.target = caller.id;
		}
		link.onvalidate = link.onclick;
		link.onclick = function(e) {
			if (typeof this.onvalidate != 'function') {
				var p = true;
			} else {
				var p = this.onvalidate(e);

			}

			if (this.getAttribute('disabled') != null && this.getAttribute('disabled') == 'disabled') return false ;

			if (p || typeof p == 'undefined' ) {
				if (!this.href || this.href.indexOf("c=access&a=index") != -1) {
					return false ;
				}
				og.openLink(this.href, {caller: this.target}) ;
			}
			return false;
		}
	};
	forms = element.getElementsByTagName("form");
	for (var i=0; i < forms.length; i++) {

		var form = forms[i];
		if (form.target && form.target[0] == '_') continue;
		if (caller && !form.target) {
			form.target = caller.id;
		}
		var onsubmit = form.onsubmit;
		form.onsubmit = function() {
			if (onsubmit && !onsubmit()) {
				return false;
			} else {
				og.ajaxSubmit(this, {caller: this.target});
			}
			return false;
		}
	};
};

og.log = function(msg) {
	if (!og._log) og._log = "";
	og._log += msg + "\n";
};

og.openLink = function(url, options) {
	//if (url.indexOf("c=dashborad&a=activity_feed") != -1) return ;

	if (!options) options = {};

	// The shell (tabs-panel, content panels) is only built once the initial
	// bootstrap call (panel/list_all in layout.js) finishes. Links clicked
	// before that (e.g. the server-rendered header) have nowhere to render,
	// so queue them and replay once the shell is ready instead of dropping
	// them silently.
	if (!og.appReady && !options.bootstrap) {
		og.pendingLinks.push({url: url, options: options});
		return;
	}

	if (typeof options.caller == "object") {
		options.caller = options.caller.id;
	}
	if (!options.caller) {
		var tabs = Ext.getCmp('tabs-panel');
		if (tabs) {
			var active = tabs.getActiveTab();
			if (active) options.caller = active.id;
		}
	}

	if (!options.hideLoading && !options.silent) {
		og.loading();
	}
	if (!options.hideLoading && !options.hideErrors && !options.silent) {
		og.clearErrors(5);
	}
	var params = options.get || {};
	if (typeof params == 'string' && params.indexOf('current=') < 0) {
		params += "&current=" + options.caller;
	} else {
		if (options.caller && ! params.current)
			params.current = options.caller;
	}
	if (url.substring(url.length - 5) != '.html') {
		// don't add params to HTML pages (this prevents 405 errors from apache 1.3)
		url = og.makeAjaxUrl(url, params);
	}
	if (typeof options.timeout != "undefined") {
		var oldTimeout = Ext.Ajax.timeout;
		Ext.Ajax.timeout = options.timeout;
	}
	var startTime = new Date().getTime();
	var requestId = Ext.Ajax.request({
		url: url,
		params: options.post,
		callback: function(options, success, response) {
			og.eventManager.fireEvent('ajax response', options);
			if (!options.options.hideLoading && !options.silent) {
				og.hideLoading();
			}

           og.eventManager.fireEvent('openLink callback', response);

			if (success) {
				UnTip(); //fixes ws tooltip is displayed some times when changing page
				if (og)
					clearTimeout(og.triggerFPTTO);
				try {
					try {
						var data = Ext.util.JSON.decode(response.responseText);
					} catch (e) {
						// response isn't valid JSON, display it on the caller panel or new tab
						if (!options.preventPanelLoad && !options.options.silent) {
							var p = Ext.getCmp(options.caller);
							if (p) {
								var tp = p.ownerCt;
								p.load(response.responseText);
								if (tp && tp.setActiveTab && options.options.show) {
									tp.setActiveTab(p);
								}
							} else {
								og.newTab(response.responseText);
							}
						}
					}
					var dont_process_response = typeof(data) != 'undefined' && data.dont_process_response;
					if (!dont_process_response) {
						og.processResponse(data, options);
					}
				} catch (e) {					
					og.err(e.message);
				}
				var ok = typeof data == 'object' && data.errorCode == 0;
				if (typeof options.postProcess == 'function') options.postProcess.call(options.scope || this, ok, data || response.responseText, options.options);
				if (ok) {
					if (typeof options.onSuccess == 'function') options.onSuccess.call(options.scope || this, data || response.responseText, options.options);
				} else {
					if (typeof options.onError == 'function') options.onError.call(options.scope || this, data || response.responseText, options.options);
				}
			} else {
				if (!options.options.hideErrors && !options.options.silent && response.status > 0) {
					og.err(lang("http error", response.status, response.statusText));
					og.httpErrLog = og.clean(response.responseText);
				}
				if (typeof options.postProcess == 'function') options.postProcess.call(options.scope || this, false, data || response.responseText, options.options);
				if (typeof options.onError == 'function') options.onError.call(options.scope || this, data || response.responseText, options.options);
			}
			var endTime = new Date().getTime();
			//og.log(url + ": " + (endTime - startTime) + " ms");
		},
		caller: options.caller,
		postProcess: options.callback || options.postProcess,
		onSuccess: options.onSuccess,
		onError: options.onError,
		scope: options.scope,
		preventPanelLoad: options.preventPanelLoad,
		options: options
	});
	if (typeof oldTimeout != "undefined") {
		Ext.Ajax.timeout = oldTimeout;
	}

	// if this function returns an object then when og.openLink is called in Firefox from an "href" attribute if fails and no action is performed
	//return requestId;
};

/**
 *  This function allows to submit a form containing a file upload without
 *  refreshing the whole page by using an iframe. The request will behave
 *  as an ajax request (openLink function). You can specify in
 *  the options parameter a forcedCallback property of type function that
 *  will be invoked after the upload.
 */
og.submit = function(form, options) {
	if (!options) options = {};
	// create an iframe
	var id = Ext.id();
	var frame = document.createElement('iframe');
	frame.id = id;
	frame.name = id;
	frame.className = 'x-hidden';
	document.body.appendChild(frame);
	if (Ext.isIE) frame.src = Ext.SSL_SECURE_URL;

	if(Ext.isIE){
	   document.frames[id].name = id;
	}
	options.panel = options.panel || Ext.getCmp('tabs-panel').getActiveTab().id;

	var origUrl = form.getAttribute('action');
	var origTarget = form.getAttribute('target');

	Ext.EventManager.on(frame, 'load', function() {
			if (frame.submitted) {
				form.setAttribute('action', origUrl);
				form.setAttribute('target', origTarget);

				if (typeof options.forcedCallback == 'function') {
					options.forcedCallback();
				}

				og.hideLoading();
				setTimeout(function(){Ext.removeNode(frame);}, 100);
			}
		}, frame
	);

	og.submit[id] = options;
	form.setAttribute('target', frame.name);
	var url = og.makeAjaxUrl(origUrl) + "&upload=true&current=" + options.panel + "&request_id=" + id;
	form.setAttribute('action', url);
	og.loading();
	frame.submitted = 1;
	form.submit();
	return false;
};

/**
 * Submits a form through an open link by serializing it.
 * Doesn't work with file uploads. Use og.submit for that purpose.
 */
og.ajaxSubmit = function(form, options) {
	if (!options) options = {};
	var params = Ext.Ajax.serializeForm(form);
	options[form.getAttribute('method').toLowerCase()] = params;
	og.openLink(form.getAttribute('action'), options);
	return false;
};

og.processResponse = function(data, options, url) {
	if (!data) return;

	// first load scripts
	og.loadScripts(data.scripts || [], {
		callback: function() {
			if (options) var caller = options.caller;

			if (data.errorCode == 2009 || data.u != og.loggedUser.id) {
				if (options) {
					og.LoginDialog.show(options.url, options.options);
				} else {
					og.LoginDialog.show();
				}
				return;
			}

			//Fire events
			if (data.events) {
				for (var i=0; i < data.events.length; i++) {
					og.eventManager.fireEvent(data.events[i].name, data.events[i].data);
				}
			}

			//Load data
			if (!options || !options.preventPanelLoad && (!options.options || !options.options.silent)){
				//Load data into more than one panel
				var loadedContentPanels = {};
				if (data.contents) {
					for (var k in data.contents) {
						var p = Ext.getCmp(k);
						if (p) {
							p.load(data.contents[k]);
							loadedContentPanels[k] = true;
						}
					}
				}

				//Loads data into a single panel
				if (data.current) {
					data.current.inlineScripts = data.inlineScripts;
					if (data.current.panel || caller) { //Loads data into a specific panel
						var panelName = data.current.panel ? data.current.panel : caller; //sets data into current.panel, otherwise into caller
						var p = Ext.getCmp(panelName);
						if (p) {
							var tp = p.ownerCt;
							// Avoid reloading the same panel twice in a single response: if this
							// panel was already reloaded via data.contents above and data.current is
							// just another reload of it, skip the redundant reload. Any other
							// data.current action (back, url, html...) is still applied normally.
							var redundantReload = loadedContentPanels[panelName] && data.current.type == 'reload';
							if (!redundantReload) {
								p.load(data.current);
							}
							if (tp && tp.setActiveTab && Ext.getCmp(panelName) && (options.options.show || data.current.panel)) {
								tp.setActiveTab(p);
							}

						} else {
							og.newTab(data.current, panelName, data); //Creates the panel if it doesn't exist
						}
					} else { //Loads the data into a new tab
						og.newTab(data.current);
					}
				}

				//Show help in content panel if help is available
				if (data.help_content){
					Ext.getCmp('help-panel').load(data.help_content);
				}
			}
			//Show messages if any
			if (data.errorCode != 0 && (!options.options || !options.options.hideErrors && !options.options.silent)) {
				og.err(data.errorMessage);
			} else if (data.errorMessage) {
				og.msg(lang("success"), data.errorMessage);
			}
		}
	});
};

og.newTab = function(content, id, data) {
	if (!data) data = {};
	if (!data.title) {
		data.title = id?lang(id):lang('new tab');
	}
	data.tabTip = data.tabTip || data.title;
	if (data.title.length >= 15) data.title = data.title.substring(0,12) + '...';
	data.iconCls = data.iconCls || data.icon || (id ? 'ico-' + id : 'ico-tab');
	var tp = Ext.getCmp('tabs-panel');
	var t = new og.ContentPanel(Ext.apply(data, {
		closable: true,
		id: id || Ext.id(),
		defaultContent: content
	}));
	if (tp) {
		tp.add(t);
		tp.setActiveTab(t);
	}
};

/**
 *  adds an event handler to an element, keeping the previous handlers for that event.
 *  	elem: element to which to add the event handler (e.g. document)
 *  	ev: event to handle (e.g. mousedown)
 *  	fun: function that will handle the event. Arguments: (event, handler_id)
 *  	scope: (optional) on which object to run the function
 *      returns: id of the event handler
 */
og.addDomEventHandler = function(elem, ev, fun, scope) {
	if (scope) fun = fun.createCallback(scope);
	if (!elem[ev + "Handlers"]) {
		elem[ev + "Handlers"] = {};
		if (typeof elem["on" + ev] == 'function') {
			elem[ev + "Handlers"]['original'] = elem["on" + ev];
		}
		elem["on" + ev] = function(event) {
			for (var id in this[ev + "Handlers"]) {
				this[ev + "Handlers"][id](event, id);
			}
		};
	}
	var id = Ext.id();
	elem[ev + "Handlers"][id] = fun;
};

/**
 *  Removes an event handler for the event that was added
 *  with og.addDomEventHandler.
 *  	elem: dom element
 *  	ev: event
 *  	id: id of the handler that was returned by og.addDomEventHandler.
 *
 */
og.removeDomEventHandler = function(elem, ev, id) {
	if (!elem || !id || !ev || !elem[ev + "Handlers"]) return;
	delete elem[ev + "Handlers"][id];
};

og.eventManager = {
	events: new Array() ,//new Array(),
	eventsById: new Array(),// new Array(),
	addListener: function(event, callback, scope, options) {
		if (!options) options = {};
		if (!this.events[event] || options.replace) {
			this.events[event] = new Array();
		}
		var id = Ext.id();
		var evobj = {
			id: id,
			callback: callback,
			scope: scope,
			options: options
		};
		this.events[event].push(evobj);
		this.eventsById[id] = evobj;
		return id;
	},

	removeListener: function(id) {
		var ev = this.eventsById[id];
		if (!ev) {
			return;
		}
		this.eventsById[id] = null;

		for ( var i in this.events ) {
			if ( (i) && this.events[i] ){
				if (this.events[i].length) {
					for ( var j in this.events[i] ) {
						if( j=="remove" ) continue;
						try {
							event = this.events[i][j];
							if (event) {
								if (event.id && event.id == id ) {
									this.events[i][j] = null ;
									this.events[i].splice(j,1);
									if (this.events[i].length == 0) {
										this.events[i] = null;
										this.events.splice(i,1);
									}

								}
							}
						} catch (e) {							
							if (!Ext.isIE) og.err(e.message);
						}
					}
				}
			}
		}
	},

	fireEvent: function(event, arguments) {
		var list = this.events[event];
		if (!list) {
			return;
		}
		for (var i=list.length-1; i >= 0; i--) {
			if (!this.eventsById[list[i].id]) {
				list.splice(i, 1);
			}
			var ret = "";
			try {
				if (list[i] && list[i].callback && typeof(list[i].callback) == 'function') {
					ret = list[i].callback.call(list[i].scope, arguments, list[i].id);
				}
			} catch (e) {			
				og.err(e.message);
			}
			if (list[i] && list[i].options.single || ret == 'remove') {
				list.splice(i, 1);;
			}
		}
	}
};

og.showHelp = function() {
	Ext.getCmp('help-panel').toggleCollapse();
};

og.extractScripts = function(html, onScriptsExecuted) {
	// Only <script> blocks that actually contain JavaScript should be executed and removed from
	// the markup. Non-JS <script> blocks - e.g. type="text/x-handlebars-template" used as inline
	// templates - must be left in the DOM untouched: evaluating their content as JavaScript throws
	// ("Unexpected token '<'" on the HTML, "Private field '#each'..." on Handlebars {{#each}}), and
	// stripping them removes the templates the page needs to read afterwards.
	var isJavascriptType = function(attrs) {
		if (!attrs) return true; // no attributes -> default is javascript
		// Require a boundary before "type" so we don't pick up data-type / content-type / etc.
		var m = attrs.match(/(?:^|\s)type\s*=\s*(['"]?)([^'"\s>]*)\1/i);
		if (!m) return true; // no type attribute -> default is javascript
		var type = m[2].toLowerCase();
		return type === '' || type === 'text/javascript' || type === 'application/javascript'
			|| type === 'text/ecmascript' || type === 'application/ecmascript' || type === 'module';
	};

	var id = Ext.id();
	html += '<span id="' + id + '"></span>';
	Ext.lib.Event.onAvailable(id, function() {
		try {
			var startTime = new Date().getTime();
			var re = /(?:<script([^>]*)?>)((\n|\r|.)*?)(?:<\/script>)/ig;
			var match;
			while (match = re.exec(html)) {
				if (match[2] && match[2].length > 0 && isJavascriptType(match[1])) {
					try {
						if (window.execScript) {
							window.execScript(match[2]);
						} else {
							window.eval(match[2]);
						}
					} catch (e) {
						og.err(e.message);
					}
				}
			}
			var endTime = new Date().getTime();
			//og.log("scripts: " + (endTime - startTime) + " ms");
			var el = document.getElementById(id);
			if (el) { Ext.removeNode(el); }
		} catch (e) { alert(e);}
		// the scripts only run once the html is in the DOM, so this is the earliest point where
		// the caller can measure the rendered content (e.g. after jQuery-UI tabs() collapsed the panels)
		if (typeof onScriptsExecuted == 'function') {
			try {
				onScriptsExecuted();
			} catch (e) {
				og.err(e.message);
			}
		}
	});

	return html.replace(/(?:<script([^>]*)?>)((\n|\r|.)*?)(?:<\/script>)/ig, function(full, attrs) {
		return isJavascriptType(attrs) ? "" : full;
	});
};

og.clone = function(o) {
	if('object' !== typeof o) {
		return o;
	}
	var c = 'function' === typeof o.pop ? [] : {};
	var p, v;
	for(p in o) {
		v = o[p];
		if('object' === typeof v) {
			c[p] = og.clone(v);
		}
		else {
			c[p] = v;
		}
	}
	return c;
};

og.closeView = function(obj){
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	if (currentPanel) currentPanel.back();
	var mails_cmp = Ext.getCmp('mails-manager');
	if (mails_cmp !== undefined) {
		var sm = mails_cmp.getSelectionModel();
		sm.clearSelections();
	}
};

og.activeTabHasBack = function(){
	return Ext.getCmp('tabs-panel').getActiveTab().hasBack();
}

og.slideshow = function(id) {
	var url = og.getUrl('files', 'slideshow', {fileId: id});
	var top = screen.height * 0.1;
	var left = screen.width * 0.1;
	var width = screen.width * 0.8;
	var height = screen.height * 0.8;
	window.open(url, 'slideshow', 'top=' + top + ',left=' + left + ',width=' + width + ',height=' + height + ',status=no,menubar=no,location=no,toolbar=no,scrollbars=no,directories=no,resizable=yes')
};


og.getParentContentPanel = function(dom) {
	return Ext.fly(dom).findParentNode('.og-content-panel', 100);
};

og.getParentContentPanelBody = function(dom) {
	return Ext.fly(dom).findParentNode('.x-panel-body', 100);
};




og.removeLinkedObjectRow = function (r, tblId, confirm_msg){
	if (confirm(confirm_msg)){
		var i=r.parentNode.parentNode.rowIndex;
		var tbl = document.getElementById(tblId);
		tbl.deleteRow(i);
		tbl.deleteRow(i-1);
	}
};

og.addLinkedObjectRow = function (tblId,obj_type,obj_id,obj_name, obj_manager, confirm_msg, unlink_msg){
	var tbl = document.getElementById(tblId);
	var cantRows = tbl.rows.length / 2;
	var row1=tbl.insertRow(tbl.rows.length);
	row1.className = 'linkedObject';
	row1.className += (cantRows% 2 == 0) ? 'even' : 'odd';

	var td1 = row1.insertCell(0);
	td1.rowSpan = 2;
	td1.style.paddingLeft = 1;
	td1.style.verticalAlign = 'middle';
	td1.style.width = '25px'
	td1.innerHTML = "<input type='hidden' value='"+obj_id+"' name='rel_objects[id_"+ cantRows +"]' />";
	td1.innerHTML += "<input type='hidden' value='"+obj_manager+"' name='rel_objects[type_"+ cantRows +"]' />";
	td1.innerHTML += "<div class='db-ico unknown ico-"+obj_type+ "' title='"+obj_type+"'></div>";

	var td2 = row1.insertCell(1);

	td2.innerHTML = "<b><span>"+obj_name+"</span></b>";

	var row2=tbl.insertRow(tbl.rows.length);
	row2.className = row1.className;
	var td2 = row2.insertCell(0);
	td2.innerHTML = '<a class="internalLink" href="#" onclick="og.removeLinkedObjectRow(this,\''+tblId+'\',\''+confirm_msg+'\')" title="' +unlink_msg+ ' object">' +unlink_msg+ '</a>';
};



/***********************************************************************/
/*********** Extending Ext.PagingToolbar  ******************************/
/***********************************************************************/

og.PagingToolbar	=	function (config) {
	og.PagingToolbar.superclass.constructor.call (this, config);
};

/**
 * Upper bound for the member/project list page-size preference.
 */
og.MEMBERS_PER_PAGE_MAX = 1000;

/**
 * Page size for member/project lists (per-user preference, falls back to files_per_page).
 */
og.getMembersPerPage = function() {
	var n = parseInt(og.config['members_per_page'], 10);
	if (!n || n < 1) {
		n = parseInt(og.config['files_per_page'], 10) || 50;
	}
	if (n > og.MEMBERS_PER_PAGE_MAX) n = og.MEMBERS_PER_PAGE_MAX;
	return n;
};

Ext.extend (og.PagingToolbar, Ext.PagingToolbar, {
	// override the private function 'getPageData' so that Ext.PagingToolbar
	// will read the 'start' parameter returned from server,
	// and set the specified page number, while presume the default behavior
	// when the server doesn't return the 'start' parameter.
	// (JSON example).
	getPageData : function(){
		var total = this.store.getTotalCount();

 		var	ap	=	Math.ceil((this.cursor+this.pageSize)/this.pageSize);
		if (this.store.reader.jsonData) {
			var start = parseInt(this.store.reader.jsonData.start);
			// go to the specified page
			ap	=	Math.ceil((start + this.pageSize)/this.pageSize);
			// also set the cursor so that 'prev' and 'next' buttons behave correctly
			this.cursor	= start;
		}

		return {
			total : total,
			activePage : ap,
			pages :  total < this.pageSize ? 1 : Math.ceil(total/this.pageSize)
		};
	}
});

og.getGooPlayerPanel = function(callback) {
	var gppanel = Ext.getCmp('gooplayer-panel');
	if (gppanel) {
		callback();
	} else {
		og.loadScripts([
				og.getScriptUrl('og/ObjectPicker.js'),
				og.getScriptUrl('og/GooPlayer.js')
			], {
			callback: function() {
				og.newTab({
						type: "panel",
						data: "gooplayer",
						config: {
							id: 'gooplayer',
							sound: og.musicSound
						}
					},
					'gooplayer-panel', {
						title: 'GooPlayer',
						icon: 'ico-gooplayer'
					}
				);
				gppanel = Ext.getCmp('gooplayer-panel');
				callback();
			}
		});
	}
	return gppanel;
};

og.playMP3 = function(track) {
	if (og.isFlashSupported()) {
		var gppanel = og.getGooPlayerPanel(function() {
			Ext.getCmp('tabs-panel').setActiveTab(gppanel);
			var gooplayer = Ext.getCmp('gooplayer');
			gooplayer.loadPlaylist([track]);
			gooplayer.start();
		});
	} else if (track[6]) {
		window.open(track[6]);
	}
};

og.queueMP3 = function(track) {
	if (og.isFlashSupported()) {
		var gppanel = og.getGooPlayerPanel(function() {
			var gooplayer = Ext.getCmp('gooplayer');
			gooplayer.queueTrack(track);
		});
	} else if (track[6]) {
		window.open(track[6]);
	}
};

og.playXSPF = function(id) {
	if (og.isFlashSupported()) {
		var gppanel = og.getGooPlayerPanel(function() {
			Ext.getCmp('tabs-panel').setActiveTab(gppanel);
			var gooplayer = Ext.getCmp('gooplayer');
			gooplayer.loadPlaylistFromFile(id, true);
		});
	} else {
		window.open(og.getUrl('files', 'download_file', {id: id}));
	}
};


og.xmlFetchTag = function(xml, tag) {
	var i1 = xml.indexOf("<" + tag + ">");
	var i2 = xml.indexOf("</" + tag + ">");
	if (i1 >= 0 && i2 > i1) {
		return {
			found: true,
			value: xml.substring(i1 + tag.length + 2, i2),
			rest: xml.substring(i2 + tag.length + 3)
		};
	} else {
		return {
			found: false,
			value: "",
			rest: xml
		};
	}
};

og.clean = function(text) {
	return Ext.util.Format.htmlEncode(text);
};

og.removeTags = function(text) {
	return Ext.util.Format.stripTags(text);
};

og.displayFileContents = function(genid, isFull){
	var text = document.getElementById(genid + 'file_contents').innerHTML;
	if (text.length > 1000 && !isFull){
		text = text.substring(0,900);
		text += '&hellip;&nbsp;&nbsp;<a href="#" onclick="og.displayFileContents(\'' + genid + '\',true)">[' + lang('show more') + '&hellip;]</a>';
	}
	document.getElementById(genid + 'file_display').innerHTML = text;
};

og.dashExpand = function(genid, expand_id){
	if (!expand_id) expand_id = '_widget_body';
	var widget = Ext.get(genid + expand_id);
	if (widget){
		var setExpanded = widget.dom.style.display == 'none';

		if (setExpanded) widget.slideIn('t', {useDisplay:true, duration:.3});
		else widget.slideOut('t', {useDisplay:true, duration:.3});

		var expander = document.getElementById(genid + 'expander');
		if (expander) expander.className = (setExpanded) ? "dash-expander ico-dash-expanded":"dash-expander ico-dash-collapsed";
	}
};

og.closeContextHelp = function(genid,option_name){
	var help = document.getElementById(genid + 'help');
	if (help){
		help.style.display = 'none';
		if(option_name != ''){
			var url = og.getUrl('account', 'update_user_preference', {name: 'show_' + option_name + '_context_help', value:0});
			og.openLink(url,{hideLoading:true});
		}
	}
};

og.billingEditValue = function(id){
	document.getElementById(id + 'bv').style.display = 'none';
	document.getElementById(id + 'bvedit').style.display = 'inline';
	document.getElementById(id + 'edclick').value = 1;
	document.getElementById(id + 'text').focus();
};

og.checkDownload = function(url, checkedOutById, checkedOutBy, file_id) {
	var checkOut = function() {
		og.ExtendedDialog.dialog.destroy();
		if (file_id) {
			og.openLink(og.getUrl('files', 'reload_file_view', {id:file_id, checkout:1}), {
				hideLoading:true,
				hideErrors:true,
				callback: function(){
					setTimeout(function(){location.href = url + "&checkout=0";},1000);
				}
			});
		} else {
			location.href = url + "&checkout=1";
		}
	};
	var readOnly = function() {
		og.ExtendedDialog.dialog.destroy();
		location.href = url + "&checkout=0";
	}
	var checkedOutByName = checkedOutBy;
	if (checkedOutByName == 'self') {
		checkedOutByName = lang('you');
	}
	if (checkedOutById > 0) {
		var config = {
			title :lang('checkout notification'),
			y :50,
			id :'checkDownloadDialog',
			modal :true,
			height :150,
			width :300,
			resizable :false,
			closeAction :'hide',
			iconCls :'op-ico',
			border :false,
			buttons : [ {
				text :lang('download'),
				handler :readOnly,
				id :'download_button',
				scope :this
			} ],
			dialogItems : [ {
				xtype :'label',
				name :'checked_label',
				id :'checkedout',
				hideLabel :true,
				style: 'font-size:100%;',
				text :lang('document checked out by', checkedOutByName)
			} ]
		};
	} else {
		var config = {
			title :lang('checkout confirmation'),
			y :50,
			id :'checkDownloadDialog',
			modal :true,
			height :150,
			width :300,
			resizable :false,
			closeAction :'hide',
			iconCls :'op-ico',
			border :false,
			buttons : [ {
				text :lang('checkout and download'),
				handler :checkOut,
				id :'checkOut_button',
				scope :this
			}, {
				text :lang('download only'),
				handler :readOnly,
				id :'readOnly_button',
				scope :this
			} ],
			dialogItems : [{
				xtype :'label',
				name :'checked_label',
				id :'checkedout',
				hideLabel :true,
				style: 'font-size:100%;',
				text :lang('checkout recommendation')
			}]
		};
	}
	og.ExtendedDialog.show(config);
};

og.getScriptUrl = function(script) {
	return og.getHostName() + "/public/assets/javascript/" + script;
};

og.loadScripts = function(urls, config) {
	if (!config) config = {};
	if (typeof urls == "string") urls = [urls];

	// first load scripts
	var scriptsLeft = urls.length;
	var scripts = [];
	for (var i=0; i < urls.length; i++) {
		if (og.loadedScripts[urls[i]]) {
			scriptsLeft--;
		} else {
			og.loadedScripts[urls[i]] = true;
			if (!config.hideLoading) og.loading();
			Ext.Ajax.request({
				disableCaching: false,
				url: urls[i],
				callback: function(options, success, response) {
					scriptsLeft--;
					if (!config.hideLoading) og.hideLoading();
					if (success) {
						scripts[options.index] = response.responseText;
					}
				},
				index: i
			});
		}
	}
	var success = {};
	var count = 0;
	var postScript = function() {
		// wait for scripts to load
		if (scriptsLeft > 0) {
			setTimeout(postScript, 100);
			return;
		}

		// run scripts
		for (var i=0; i < scripts.length; i++) {
			if (scripts[i]) {
				try {
					if (window.execScript) {
						window.execScript(scripts[i]);
					} else {
						window.eval(scripts[i]);
					}
					success[urls[i]] = true;
					count++;
				} catch (e) {
					og.err(e.message);
				}
			}
		}
		if (typeof config.callback == 'function') {
			config.callback.call(config.scope, count, success);
		}
	};
	postScript();
};

og.loadedScripts = {};

og.ToggleTrap = function(trapid, fsid) {
	if (Ext.isIE) {
		if (!Ext.get(fsid).isDisplayed()) {
			Ext.get(fsid).setDisplayed('block');
		} else {
			Ext.get(fsid).setDisplayed('none');
		}
	}
};

og.FileIsZip = function(mimetype, name) {
	if (!name) return false;
	var ix = name.lastIndexOf('.');
	var extension = ix >= 0 ? name.substring(ix + 1) : "";
	return (mimetype == 'application/zip' || mimetype == 'application/x-zip-compressed' ||
			(mimetype == 'application/x-compressed' && extension == 'zip') || extension == 'zip');
};

og.disableEventPropagation = function(event) {
	if (Ext.isIE) {
		window.event.cancelBubble = true;
	} else {
		event.stopPropagation();
	}
};

og.showMoreActions = function(genid) {
	$("#otherActions" + genid).slideToggle('slow');
	$("#moreOption" + genid).hide();
};

og.loadEmailAccounts = function(type) {
	og.openLink(og.getUrl('mail', 'list_accounts', {type: type}),{
		callback: function(success, data) {
			if (success) {
				if (type == 'view') og.email_accounts_toview = data.accounts;
				else if (type == 'edit') og.email_accounts_toedit = data.accounts;
			}
		}
	});
};
//	SUBSCRIBERS LIST FUNCTIONS

og.rollOver = function(div) {
	$(div).addClass("rolling-over");
};
og.rollOut = function(div,isCompany) {
	if (isCompany){
		isChecked=Ext.fly(div).hasClass("checked");
		div.className = "container-div company-name";
		if (isChecked){
			$(div).addClass("checked");
		} else {
			$(div).removeClass("checked");
		}
	}else{
		isChecked=Ext.fly(div).hasClass("checked-user");
		if (isChecked){
			$(div).addClass("checked-user");
			$(div).removeClass("user-name");
		}else{
			$(div).removeClass("checked-user");
			$(div).addClass("user-name");
		}
	}
	$(div).removeClass("rolling-over");
};
og.checkUser = function (div){
	var hiddy = document.getElementById(div.id.substring(3));
	if (hiddy) {
		if (hiddy.value == '1') {
			hiddy.value = '0';
			$(div).removeClass("checked-user");
			$(div).addClass("user-name");
		} else {
			hiddy.value = '1';
			$(div).addClass("checked-user");
			$(div).removeClass("user-name");
		}
	}
};
og.subscribeCompany = function (div){
		var isChecked = Ext.fly(div).hasClass("checked");
		var hids = div.parentNode.getElementsByTagName("input");
		for (var i=0; i < hids.length; i++) {
			var hiddenTag = hids[i];
			if (!isChecked && !hiddenTag.checked || isChecked && hiddenTag.checked) {
				og.checkUser(hiddenTag.parentNode);
			}
		}

		if (!isChecked) {
			Ext.fly(div).addClass('checked');
		} else {
			Ext.fly(div).removeClass('checked');
		}
};

og.confirmRemoveTags = function(manager) {
	var man = Ext.getCmp(manager);

	var removeAction = function() {
		og.ExtendedDialog.dialog.destroy();
		if (man) man.removeTags();
	};

	var cancelAction = function() {
		og.ExtendedDialog.dialog.destroy();
	};

	var config = {
		title: lang('remove tags'),
		y :50,
		id :'removeTags',
		modal :true,
		height :125,
		width :300,
		resizable :false,
		closeAction :'hide',
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('yes'),
			handler :removeAction,
			id :'yes_button',
			scope :this
		}, {
			text :lang('no'),
			handler :cancelAction,
			id :'no_button',
			scope :this
		} ],
		dialogItems : [ {
			xtype :'label',
			name :'moveadd_label',
			id :'moveadd',
			hideLabel :true,
			style: 'font-size:100%;',
			text: lang('confirm remove tags')
		} ]
	};
	og.ExtendedDialog.show(config);
}

og.confirmMoveToAllWs = function(manager, text) {
	var man = Ext.getCmp(manager);

	var moveAction = function() {
		og.ExtendedDialog.dialog.destroy();
		man.moveObjectsToAllWs();
	};

	var cancelAction = function() {
		og.ExtendedDialog.dialog.destroy();
	};

	var config = {
		title: '',
		y :50,
		id :'moveToAllWs',
		modal :true,
		height :125,
		width :300,
		resizable :false,
		closeAction :'hide',
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('yes'),
			handler :moveAction,
			id :'yes_button',
			scope :this
		}, {
			text :lang('no'),
			handler :cancelAction,
			id :'no_button',
			scope :this
		} ],
		dialogItems : [ {
			xtype :'label',
			name :'moveadd_label',
			id :'moveadd',
			hideLabel :true,
			style: 'font-size:100%;',
			text: text
		} ]
	};
	og.ExtendedDialog.show(config);
}

og.moveToWsOrMantainMembers = function(manager, ws) {
	var man = Ext.getCmp(manager);

	var moveAction = function() {
		if (og.ExtendedDialog.dialog) og.ExtendedDialog.dialog.destroy();
		man.moveObjectsToWsOrMantainMembers(0, ws);
//		man.getSelectionModel().clearSelections();
	};
	var mantainAction = function() {
		if (og.ExtendedDialog.dialog) og.ExtendedDialog.dialog.destroy();
		man.moveObjectsToWsOrMantainMembers(1, ws);
//		man.getSelectionModel().clearSelections();
	};

	if (og.preferences['drag_drop_prompt'] == 'move') {
		moveAction();
		return;
	} else if (og.preferences['drag_drop_prompt'] == 'keep') {
		mantainAction();
		return;
	}

	var config = {
		title :lang('move to workspace or keep old ones'),
		y :50,
		id :'moveToWsOrAddWs',
		modal :true,
		height :150,
		width :300,
		resizable :false,
		closeAction :'hide',
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('move to workspace'),
			handler :moveAction,
			id :'move_button',
			scope :this
		}, {
			text :lang('keep old workspaces'),
			handler :mantainAction,
			id :'add_button',
			scope :this
		} ],
		dialogItems : [ {
			xtype :'label',
			name :'moveadd_label',
			id :'moveadd',
			hideLabel :true,
			style: 'font-size:100%;',
			text :lang('do you want to move objects to this ws or keep old ones and add this ws')
		} ]
	};
	og.ExtendedDialog.show(config);
};

og.askToClassifyUnclassifiedAttachs = function(manager, mantain, ws) {
	var man = Ext.getCmp(manager);

	var classifyAction = function() {
		if (og.ExtendedDialog.dialog) og.ExtendedDialog.dialog.destroy();
		man.moveObjectsClassifyingEmails(mantain, ws, 1);
	};
	var leaveAction = function() {
		if (og.ExtendedDialog.dialog) og.ExtendedDialog.dialog.destroy();
		man.moveObjectsClassifyingEmails(mantain, ws, 0);
	};

	if (og.preferences['mail_drag_drop_prompt'] == 'classify') {
		classifyAction();
		return;
	} else if (og.preferences['mail_drag_drop_prompt'] == 'dont') {
		leaveAction();
		return;
	}

	var config = {
		title :lang('classify mail attachments'),
		y :50,
		id :'classifyAttachs',
		modal :true,
		height :150,
		width :300,
		resizable :false,
		closeAction :'hide',
		iconCls :'op-ico',
		border :false,
		buttons : [ {
			text :lang('yes'),
			handler :classifyAction,
			id :'yes_button',
			scope :this
		}, {
			text :lang('no'),
			handler :leaveAction,
			id :'no_button',
			scope :this
		} ],
		dialogItems : [ {
			xtype :'label',
			name :'classify_label',
			id :'classify_leave',
			hideLabel :true,
			style: 'font-size:100%;',
			text :lang('do you want to classify the unclassified emails attachments')
		} ]
	};
	og.ExtendedDialog.show(config);
};


og.replaceAllOccurrences = function(str, search, replace) {
	while (str.indexOf(search) != -1) {
		str = str.replace(search, replace);
	}
	return str;
};

og.isFlashSupported = function() {
	return navigator.mimeTypes["application/x-shockwave-flash"] ? true : false;
};

og.showHide = function(itemId, mode) {
	if (!mode || (mode != 'block' && mode != 'inline')) mode = 'block';
	var el = document.getElementById(itemId);
	if (el) {
		if (el.style.display == 'none') el.style.display = mode;
		else el.style.display = 'none';
	}
};

og.calculate_time_zone = function(server) {
	var client = new Date();
	var diff = client.getTime() - server.getTime();
	diff = Math.round(diff*2/3600000);
	return diff / 2;
};

og.redrawLinkedObjects = function(id) {
	var div = Ext.get("linked_objects_in_prop_panel");
	if (div) {
		div.load({url: og.getUrl('object', 'redraw_linked_object_list', {id:id}), scripts: true});
	}
}

og.redrawSubscribers = function(id, genid) {
	var div = Ext.get(genid + "subscribers_in_prop_panel");
	if (div) {
		div.load({url: og.getUrl('object', 'redraw_subscribers_list', {id:id}), scripts: true});
	}
}

og.show_hide_subscribers_list = function(id, genid) {
	og.openLink(og.getUrl('object', 'add_subscribers_list', {obj_id: id, genid: genid}), {
		preventPanelLoad:true,
		onSuccess: function(data) {

			og.ExtendedDialog.show({
        		html: data.current.data,
        		height: 450,
        		width: 685,
        		ok_fn: function() {
        			formy = document.getElementById(genid + "add-User-Form");
        			var params = Ext.Ajax.serializeForm(formy);
        			var options = {callback: function(data, success){
            			og.redrawSubscribers(id, genid);
        			}}
    				options[formy.method.toLowerCase()] = params;
    				og.openLink(formy.getAttribute('action'), options);
    				og.ExtendedDialog.hide();
    			}
        	});
        	return;
		}
	});
};

/*
 * Adds the listener to manage concurrency while editing objects.
 * it shows a yes or no dialog, if the answer is yes re-send the form data
 * and set "merge-changes" attribute to true so that the object list view is shown.
 *  If no is choosen it sent the form and overwrite the submited data.
 */
og.eventManager.addListener('handle edit concurrence',
	function (data) {
		var genid = data['genid'];
		var elem = document.getElementById( genid + 'merge-changes-hidden');
		elem.value = '';
		var hidden = document.getElementById(genid + "updated-on-hidden");
 		if (hidden) {
 			hidden.value = data['updatedon'];
 		}
		var dialog = '<div style="padding:10px;">';
		dialog += '<h1>' + lang('allready updated object') + '</h1><br />';
		dialog += '<div>' + lang('allready updated object desc') + '</div></div>';
		og.ExtendedDialog.show({
    		html: dialog,
    		height: 250,
    		width: 350,
    		YESNO: true,
    		ok_fn: function() {
    			og.ExtendedDialog.hide();
    			elem.value = 'true';
    			var form = document.getElementById(genid + "submit-edit-form");
    			form.onsubmit();
    			elem.value = '';
			}
    	});
	}
);

og.getCkEditorInstance = function(name) {
	var editor = null;
	for (instName in CKEDITOR.instances) {
		if (instName == name) {
			editor = CKEDITOR.instances[instName];
			break;
		}
	}
	return editor;
};

og.getDomEl = function(el) {
	if (!el) return null;
	if (el.nodeType) return el;
	if (el.dom) return el.dom;
	if (el.el) return og.getDomEl(el.el);
	return null;
};

og.collectCkEditorsInElement = function(el) {
	var names = [];
	var ids = [];
	var root = og.getDomEl(el);
	if (!root || typeof CKEDITOR == 'undefined' || !CKEDITOR.instances) {
		return { names: names, ids: ids };
	}
	for (var name in CKEDITOR.instances) {
		if (!Object.prototype.hasOwnProperty.call(CKEDITOR.instances, name)) continue;
		var editor = CKEDITOR.instances[name];
		try {
			var container = (editor.container && editor.container.$) || (editor.element && editor.element.$);
			if (container && (root === container || (root.contains && root.contains(container)))) {
				names.push(editor.name || name);
				if (editor.id) ids.push(editor.id);
			}
		} catch (e) {}
	}
	return { names: names, ids: ids };
};

og.destroyCkEditorsInElement = function(el) {
	var collected = og.collectCkEditorsInElement(el);
	if (typeof CKEDITOR == 'undefined' || !CKEDITOR.instances) return;
	for (var i = 0; i < collected.names.length; i++) {
		var editor = CKEDITOR.instances[collected.names[i]];
		if (!editor) continue;
		try {
			editor.destroy(true);
		} catch (e) {
			try { CKEDITOR.remove(editor); } catch (e2) {}
		}
	}
};

og.ckEditorDetachedUiBelongsTo = function(node, names, ids) {
	if (!node) return false;
	names = names || [];
	ids = ids || [];
	var classMap = {};
	var classes = (node.className || '').split(/\s+/);
	for (var c = 0; c < classes.length; c++) {
		if (classes[c]) classMap[classes[c]] = true;
	}
	var nodeId = node.id || '';
	for (var n = 0; n < names.length; n++) {
		var name = names[n];
		if (!name) continue;
		var safeName = String(name).replace(/\./g, '\\.');
		if (classMap['cke_editor_' + name] || classMap['cke_editor_' + safeName]) return true;
		if (classMap['cke_editor_' + name + '_dialog'] || classMap['cke_editor_' + safeName + '_dialog']) return true;
		if (nodeId === 'cke_' + name || nodeId.indexOf('cke_' + name + '_') === 0) return true;
	}
	for (var i = 0; i < ids.length; i++) {
		var editorId = ids[i];
		if (!editorId) continue;
		if (classMap[editorId]) return true;
		if (nodeId === editorId || nodeId.indexOf(editorId + '_') === 0) return true;
	}
	return false;
};

// CKEditor 4 appends dialogs/panels/floats to document.body, so they are never
// inside .og-content-panel. Only hide chrome that belongs to the given editors.
og.hideDetachedCkEditorUi = function(names, ids) {
	if ((!names || !names.length) && (!ids || !ids.length)) return;
	names = names || [];
	ids = ids || [];
	$('.cke_float, .cke_panel, .cke_dialog').each(function() {
		if (og.ckEditorDetachedUiBelongsTo(this, names, ids)) {
			$(this).hide();
		}
	});
	if (typeof CKEDITOR != 'undefined' && CKEDITOR.dialog && CKEDITOR.dialog._ && CKEDITOR.dialog._.currentTop) {
		var dlg = CKEDITOR.dialog._.currentTop;
		var editor = null;
		try {
			editor = dlg._.editor || (dlg.getParentEditor && dlg.getParentEditor());
		} catch (e) {}
		if (editor) {
			var belongs = false;
			for (var n = 0; n < names.length; n++) {
				if (names[n] === editor.name) belongs = true;
			}
			for (var i = 0; !belongs && i < ids.length; i++) {
				if (ids[i] === editor.id) belongs = true;
			}
			if (belongs) {
				try { dlg.hide(); } catch (e2) {}
				$('.cke_dialog_background_cover').hide();
			}
		}
	}
};

og.hideInactiveTabOverlays = function() {
	var tp = Ext.getCmp('tabs-panel');
	if (!tp || !tp.getActiveTab) return;
	var active = tp.getActiveTab();
	var names = [];
	var ids = [];
	tp.items.each(function(item) {
		if (item === active) return;
		if (item.el) {
			var editors = og.collectCkEditorsInElement(item.el);
			names = names.concat(editors.names);
			ids = ids.concat(editors.ids);
		}
		if (!item.hidden) {
			item.hide();
		} else if (item.el && item.el.dom && item.el.isVisible()) {
			// CardLayout already set hidden=true, but a tab-strip resize can
			// leave the DOM visible. Re-apply the hide class without firing
			// hide() again (that would re-dispatch hide events).
			item.onHide();
		}
	});
	og.hideDetachedCkEditorUi(names, ids);
};

/**
 * Shared helpers for WYSIWYG image paste/drop (kept on `og` so they can be unit-tested).
 */
og.wysiwygIsImageFile = function(file) {
	if (!file) {
		return false;
	}
	var type = (file.type || '').toLowerCase();
	if (type.indexOf('image/') === 0) {
		return true;
	}
	// Finder/Desktop drops on macOS/Chrome often arrive with an empty MIME type.
	var name = file.name || '';
	return /\.(jpe?g|png|gif|webp|bmp|svg|heic|heif|tiff?)$/i.test(name);
};

og.wysiwygIsFileDrag = function(dataTransfer) {
	if (!dataTransfer) {
		return false;
	}
	if (!dataTransfer.types) {
		// Some desktop drags expose types only at drop time.
		return true;
	}
	var types = dataTransfer.types;
	if (typeof types.contains === 'function') {
		if (types.contains('Files') || types.contains('application/x-moz-file') || types.contains('public.file-url')) {
			return true;
		}
	}
	for (var i = 0; i < types.length; i++) {
		var t = types[i];
		if (
			t === 'Files'
			|| t === 'application/x-moz-file'
			|| t === 'public.file-url'
			|| t === 'text/uri-list'
			|| t === 'application/x-finder-node'
		) {
			return true;
		}
	}
	return types.length === 0;
};

og.wysiwygImageFilesFromClipboard = function(clipboardData) {
	var files = [];
	if (!clipboardData) {
		return files;
	}
	if (clipboardData.items && clipboardData.items.length) {
		for (var i = 0; i < clipboardData.items.length; i++) {
			var item = clipboardData.items[i];
			if (item && item.type && item.type.indexOf('image/') === 0 && typeof item.getAsFile === 'function') {
				var fromItem = item.getAsFile();
				if (fromItem) {
					files.push(fromItem);
				}
			}
		}
	}
	if (!files.length && clipboardData.files && clipboardData.files.length) {
		for (var j = 0; j < clipboardData.files.length; j++) {
			if (og.wysiwygIsImageFile(clipboardData.files[j])) {
				files.push(clipboardData.files[j]);
			}
		}
	}
	return files;
};

og.wysiwygImageFilesFromDataTransfer = function(dataTransfer) {
	var files = [];
	var seen = {};
	if (!dataTransfer) {
		return files;
	}
	var fileKey = function(file) {
		if (!file) {
			return '';
		}
		return [
			file.name || '',
			file.type || '',
			typeof file.size === 'number' ? file.size : '',
			typeof file.lastModified === 'number' ? file.lastModified : ''
		].join('|');
	};
	var pushUnique = function(file) {
		if (!file || !og.wysiwygIsImageFile(file)) {
			return;
		}
		var key = fileKey(file);
		if (key && seen[key]) {
			return;
		}
		if (key) {
			seen[key] = true;
		}
		files.push(file);
	};
	if (dataTransfer.items && dataTransfer.items.length) {
		for (var i = 0; i < dataTransfer.items.length; i++) {
			var item = dataTransfer.items[i];
			if (item && item.kind === 'file' && typeof item.getAsFile === 'function') {
				pushUnique(item.getAsFile());
			}
		}
	}
	if (dataTransfer.files && dataTransfer.files.length) {
		for (var j = 0; j < dataTransfer.files.length; j++) {
			pushUnique(dataTransfer.files[j]);
		}
	}
	return files;
};

og.wysiwygDataTransferHasFiles = function(dataTransfer) {
	if (!dataTransfer) {
		return false;
	}
	if (dataTransfer.files && dataTransfer.files.length) {
		return true;
	}
	if (dataTransfer.items && dataTransfer.items.length) {
		for (var i = 0; i < dataTransfer.items.length; i++) {
			if (dataTransfer.items[i] && dataTransfer.items[i].kind === 'file') {
				return true;
			}
		}
	}
	return og.wysiwygIsFileDrag(dataTransfer);
};

og.wysiwygGuessImageFileName = function(file) {
	if (file && file.name && /\.(jpe?g|png|gif|webp|bmp)$/i.test(file.name)) {
		return file.name;
	}
	var type = ((file && file.type) || '').toLowerCase();
	var ext = 'png';
	if (type.indexOf('jpeg') >= 0 || type.indexOf('jpg') >= 0) {
		ext = 'jpg';
	} else if (type.indexOf('gif') >= 0) {
		ext = 'gif';
	} else if (type.indexOf('webp') >= 0) {
		ext = 'webp';
	} else if (type.indexOf('bmp') >= 0) {
		ext = 'bmp';
	}
	return 'image.' + ext;
};

/**
 * Uploads an image file for WYSIWYG insertion. Prefers a short /tmp URL over a huge data URI
 * so saving the task does not blow up POST size / PCRE persistence.
 */
og.wysiwygUploadImageFile = function(file, callback) {
	if (typeof callback !== 'function') {
		return;
	}
	if (!file || typeof FormData === 'undefined' || typeof XMLHttpRequest === 'undefined') {
		callback(null);
		return;
	}
	try {
		var fd = new FormData();
		fd.append('upload', file, og.wysiwygGuessImageFileName(file));
		var uploadUrl = (og.hostName || '').replace(/\/+$/, '') + '/ck_upload_handler.php?response=json';
		var xhr = new XMLHttpRequest();
		xhr.open('POST', uploadUrl, true);
		xhr.onreadystatechange = function() {
			if (xhr.readyState !== 4) {
				return;
			}
			if (xhr.status < 200 || xhr.status >= 300) {
				callback(null);
				return;
			}
			try {
				var data = JSON.parse(xhr.responseText);
				if (data && data.url && !data.error) {
					callback(data.url);
					return;
				}
			} catch (e) {}
			callback(null);
		};
		xhr.send(fd);
	} catch (e) {
		callback(null);
	}
};

/**
 * Enables paste (screenshot/cutout) and drag/drop of image files into a CKEditor instance.
 * Images are uploaded to /tmp when possible; otherwise inserted as data URIs.
 * The server persists them via process_wysiwyg_html_content().
 *
 * CKEditor 4.4.x uses a pastebin and does not expose clipboardData on editor paste events, so image
 * clipboard data is handled on the native paste event inside the editable iframe.
 */
og.bindCkEditorImagePasteAndDrop = function(editor) {
	if (!editor || typeof editor.on !== 'function' || editor._ogImagePasteDropBound) {
		return;
	}
	editor._ogImagePasteDropBound = true;

	var getDropPoint = function(evt) {
		if (!evt || typeof evt.clientX === 'undefined' || typeof evt.clientY === 'undefined') {
			return null;
		}
		var point = { clientX: evt.clientX, clientY: evt.clientY, fromParent: false };
		try {
			var editorWin = editor.window && editor.window.$ ? editor.window.$ : null;
			if (editorWin && evt.view && evt.view !== editorWin) {
				point.fromParent = true;
				var frameElement = editorWin.frameElement;
				if (frameElement && frameElement.getBoundingClientRect) {
					var rect = frameElement.getBoundingClientRect();
					point.clientX = evt.clientX - rect.left;
					point.clientY = evt.clientY - rect.top;
				}
			}
		} catch (e) {}
		return point;
	};

	var placeCaretAtPoint = function(point) {
		if (!point || !editor.document || !editor.document.$ || typeof CKEDITOR === 'undefined') {
			return false;
		}
		var doc = editor.document.$;
		var nativeRange = null;
		try {
			if (doc.caretRangeFromPoint) {
				nativeRange = doc.caretRangeFromPoint(point.clientX, point.clientY);
			} else if (doc.caretPositionFromPoint) {
				var position = doc.caretPositionFromPoint(point.clientX, point.clientY);
				if (position) {
					nativeRange = doc.createRange();
					nativeRange.setStart(position.offsetNode, position.offset);
					nativeRange.collapse(true);
				}
			}
			if (!nativeRange) {
				return false;
			}
			editor.focus();
			var range = new CKEDITOR.dom.range(editor.document);
			range.setStart(new CKEDITOR.dom.node(nativeRange.startContainer), nativeRange.startOffset);
			range.collapse(true);
			editor.getSelection().selectRanges([range]);
			return true;
		} catch (e) {
			return false;
		}
	};

	var insertImageHtml = function(src, point) {
		if (!src || typeof editor.insertHtml !== 'function') {
			return;
		}
		try {
			editor.focus();
			if (point) {
				placeCaretAtPoint(point);
			}
			editor.fire('saveSnapshot');
			editor.insertHtml('<img src="' + src + '" alt="" style="max-width:100%;" />');
			editor.fire('saveSnapshot');
		} catch (err) {}
	};

	var resolveImageSrc = function(file, callback) {
		if (!og.wysiwygIsImageFile(file) || typeof callback !== 'function') {
			if (typeof callback === 'function') {
				callback(null);
			}
			return;
		}
		og.wysiwygUploadImageFile(file, function(uploadedUrl) {
			if (uploadedUrl) {
				callback(uploadedUrl);
				return;
			}
			if (typeof FileReader === 'undefined') {
				callback(null);
				return;
			}
			var reader = new FileReader();
			reader.onload = function(e) {
				callback(e.target && e.target.result ? e.target.result : null);
			};
			reader.onerror = function() {
				callback(null);
			};
			reader.readAsDataURL(file);
		});
	};

	/**
	 * Resolve all files (uploads can finish in any order), then insert in the
	 * original file order. First image uses the drop caret; the rest append after it.
	 */
	var insertImagesInOrder = function(files, point) {
		if (!files || !files.length) {
			return;
		}
		var results = new Array(files.length);
		var pending = files.length;
		var finishOne = function() {
			pending--;
			if (pending > 0) {
				return;
			}
			var placed = false;
			for (var j = 0; j < results.length; j++) {
				if (!results[j]) {
					continue;
				}
				insertImageHtml(results[j], placed ? null : point);
				placed = true;
			}
		};
		for (var i = 0; i < files.length; i++) {
			(function(index, file) {
				resolveImageSrc(file, function(src) {
					results[index] = src;
					finishOne();
				});
			})(i, files[i]);
		}
	};

	var onPaste = function(evt) {
		var files = og.wysiwygImageFilesFromClipboard(evt.clipboardData);
		if (!files.length) {
			return;
		}
		evt.preventDefault();
		evt.stopPropagation();
		insertImagesInOrder(files, null);
	};

	var onDragOver = function(evt) {
		if (!og.wysiwygIsFileDrag(evt.dataTransfer)) {
			return;
		}
		evt.preventDefault();
		try {
			evt.dataTransfer.dropEffect = 'copy';
		} catch (e) {}
	};

	var onDrop = function(evt) {
		var dataTransfer = evt.dataTransfer;
		if (!dataTransfer) {
			return;
		}
		var hasFiles = og.wysiwygDataTransferHasFiles(dataTransfer);
		var files = og.wysiwygImageFilesFromDataTransfer(dataTransfer);
		if (!hasFiles && !files.length) {
			return;
		}
		// Prevent the browser from navigating to file://... (common with Desktop/Finder drops).
		evt.preventDefault();
		evt.stopPropagation();
		if (!files.length) {
			return;
		}
		insertImagesInOrder(files, getDropPoint(evt));
	};

	var isOverEditor = function(evt) {
		try {
			var container = editor.container && editor.container.$ ? editor.container.$ : null;
			if (!container || !container.getBoundingClientRect) {
				return false;
			}
			var rect = container.getBoundingClientRect();
			return evt.clientX >= rect.left && evt.clientX <= rect.right
				&& evt.clientY >= rect.top && evt.clientY <= rect.bottom;
		} catch (e) {
			return false;
		}
	};

	var bindEditable = function() {
		var targets = [];
		try {
			if (typeof editor.editable === 'function') {
				var editable = editor.editable();
				if (editable && editable.$) {
					targets.push(editable.$);
				}
			}
			if (editor.document && editor.document.$) {
				if (editor.document.$.documentElement) {
					targets.push(editor.document.$.documentElement);
				}
				if (editor.document.$.body) {
					targets.push(editor.document.$.body);
				}
			}
			if (editor.window && editor.window.$ && editor.window.$.frameElement) {
				targets.push(editor.window.$.frameElement);
			}
		} catch (e) {}

		for (var i = 0; i < targets.length; i++) {
			var el = targets[i];
			if (!el || !el.addEventListener || el._ogImagePasteDropEl) {
				continue;
			}
			el._ogImagePasteDropEl = true;
			el.addEventListener('paste', onPaste, true);
			// Capture phase so Finder/Desktop drops are accepted before CKEditor default handling.
			el.addEventListener('dragenter', onDragOver, true);
			el.addEventListener('dragover', onDragOver, true);
			el.addEventListener('drop', onDrop, true);
		}
	};

	// Parent-document safety net for drops that hit around/on the iframe chrome.
	if (!editor._ogImagePageDropBound) {
		editor._ogImagePageDropBound = true;
		document.addEventListener('dragenter', function(evt) {
			if (isOverEditor(evt) && og.wysiwygIsFileDrag(evt.dataTransfer)) {
				evt.preventDefault();
			}
		}, false);
		document.addEventListener('dragover', function(evt) {
			if (isOverEditor(evt) && og.wysiwygIsFileDrag(evt.dataTransfer)) {
				evt.preventDefault();
				try {
					evt.dataTransfer.dropEffect = 'copy';
				} catch (e) {}
			}
		}, false);
		document.addEventListener('drop', function(evt) {
			if (!isOverEditor(evt)) {
				return;
			}
			onDrop(evt);
		}, false);
	}

	editor.on('contentDom', bindEditable);
	bindEditable();
};

og.adjustCkEditorArea = function(genid, id, keep_bottom) {
	if(id == undefined) id = '';
	var el = document.getElementById('cke_' + genid + 'ckeditor' + id);
	if (el) el.style.padding = '0px';

	var cont = document.getElementById('cke_contents_' + genid + 'ckeditor' + id);
	if (cont) {
		cont.style.padding = '0px';
		cont.style.border = '0px none';
	}
	if (!keep_bottom) {
		var bot = document.getElementById('cke_bottom_' + genid + 'ckeditor' + id);
		if (bot) bot.style.display = 'none';
	}
};

og.hideFlashObjects = function() {
	var flash = document.getElementsByTagName('embed');
	for (var i=0; i < flash.length; i++) {
		flash[i].style.visibility = 'hidden';
		flash[i].hiddenFlashObject = true;
	}
};

og.restoreFlashObjects = function() {
	var flash = document.getElementsByTagName('embed');
	for (var i=0; i < flash.length; i++) {
		if (flash[i].hiddenFlashObject) flash[i].style.visibility = 'visible';
		flash[i].hiddenFlashObject = false;
	}
};

og.promptDeleteAccount = function(account_id, reload) {
	var check_id = Ext.id();
	var config = {
		genid: Ext.id(),
		title: lang('confirm delete mail account'),
		height: 150,
		width: 250,
		labelWidth: 150,
		ok_fn: function() {
			var checked = Ext.getCmp(check_id).getValue();
			og.openLink(og.getUrl('mail', 'delete_account', {
				id: account_id,
				deleteMails: checked ? 1 : 0,
				reload: reload ? 1 : 0
			}));
			og.ExtendedDialog.hide();
		},
		dialogItems: {
			xtype: 'checkbox',
			fieldLabel: lang('delete account emails'),
			id: check_id,
			value: false
		}
	};
	og.ExtendedDialog.show(config);
};

og.promptDeleteCalendar = function(calendar_id) {
	var check_id = Ext.id();
	var config = {
		genid: Ext.id(),
		title: lang('delete calendar'),
		height: 200,
		width: 250,
		labelWidth: 150,
		ok_fn: function() {
			var checked = Ext.getCmp(check_id).getValue();
			og.openLink(og.getUrl('event', 'delete_calendar', {
				cal_id: calendar_id,
				deleteCalendar: checked ? 1 : 0
			}));
			og.ExtendedDialog.hide();
		},
		dialogItems: {
			xtype: 'checkbox',
			fieldLabel: lang('delete calendar events'),
			id: check_id,
			value: false
		}
	};
	og.ExtendedDialog.show(config);
};

og.htmlToText = function(html) {
	// remove line breaks
	html = html.replace(/[\n\r]\s*/g, "");
	// change several white spaces for one
	html = html.replace(/[ \t][ \t]+|&nbsp;/g, " ");
	// insert line breaks were they belong
	html = html.replace(/(<\/table>|<\/tr>|<\/div>|<br *\/?>|<\/p>)/g, "$1\n");
	// insert tabs on tables
	html = html.replace(/(<\/td>)/g, "$1\t");
	// strip tags
	html = html.replace(/<[^>]*>/g, "");

	return html;
};

og.updateUnreadEmail = function(unread) {
	if (og.preferences['show_unread_on_title']) {
		var title = document.title;
		if (title.charAt(0) == '(' && title.indexOf(')') > 0) {
			title = title.substring(title.indexOf(')') + 2);
		}
		if (unread > 0) {
			document.title = "(" + unread  + ") " + title;
		} else {
			document.title = title;
		}
	}
	var panel = Ext.getCmp('mails-panel');
	if (panel) {
		if (unread > 0) {
			panel.setTitle(lang('email tab') + " (" + unread  + ")");
		} else {
			panel.setTitle(lang('email tab'));
		}
	} else {
		var tab = Ext.select("#tabs-panel__mails-panel span.x-tab-strip-text");
		tab.each(function() {
			if (unread > 0) {
				this.innerHTML = lang('email tab') + " (" + unread + ")";
			} else {
				this.innerHTML = lang('email tab');
			}
		});
	}
};

og.onChangeObjectCoType = function(genid, manager, id, new_cotype) {
	og.openLink(og.getUrl('object', 're_render_custom_properties', {id:id, manager:manager, req:1, co_type:new_cotype}),
		{callback: function(success, data) {
			if (success) {
				var div = Ext.get(genid + 'required_custom_properties');
				if (div) div.remove();
				var container = Ext.get(genid + 'required_custom_properties_container');
				if (container) {
					container.insertHtml('beforeEnd', '<div id="'+genid+'required_custom_properties">'+data.html+'</div>');
					eval(data.scripts);
				}
			}
		}}
	);
/*	og.openLink(og.getUrl('object', 're_render_custom_properties', {id:id, manager:manager, req:0, co_type:new_cotype}),
		{callback: function(success, data) {
			if (success) {
				var div = Ext.get(genid + 'not_required_custom_properties');
				if (div) div.remove();
				var container = Ext.get(genid + 'not_required_custom_properties_container');
				if (container) {
					container.insertHtml('beforeEnd', '<div id="'+genid+'not_required_custom_properties">'+data.html+'</div>');
					eval(data.scripts);
				}
			}
		}}
	);
*/
};

og.expandDocumentView = function(link) {
	var document_view = $(link).parent();
	var container = $(link).closest(".x-panel-body");

	if (link.expanded) {

		$(document_view).css({
			'height': link.old_height + 'px',
			'position':'relative'
		});
		link.title = lang('expand');
		link.className = 'ico-expand';

		$(container).animate({ scrollTop: 0 }, 0);
		$(container).css('overflow-y', 'auto');

		link.expanded = false;
	} else {

		link.old_height = $(document_view).height();
		$(document_view).css({
			'z-index': '500',
			'top': '0px',
			'height': '100%',
			'position':'absolute'
		});

		link.title = lang('collapse');
		link.className = 'ico-collapse';

		$(container).animate({ scrollTop: 0 }, 0);
		$(container).css('overflow-y', 'hidden');

		link.expanded = true;
	}
};

og.getHostName = function() {
	og.hostName = og.hostName.replace(/\/+$/, "");
	return og.hostName;
};

og.handleMemberChooserSubmit = function(genid, objectType, preHfId) {
	if (!preHfId) preHfId = "";
	var panels = Ext.getCmp(genid + "-member-chooser-panel-" + objectType);
	if (panels) {
		var memberChoosers = panels.items ;
		var members = [] ;
		if ( memberChoosers ) {
			memberChoosers.each(function(item, index, length) {
				var checked = item.getChecked("id");
				for (var j = 0 ; j < checked.length ; j++ ) {
					members.push(checked[j]);
				}
			});
			if (og.can_submit_members){
				document.getElementById(genid + preHfId + member_selector[genid].hiddenFieldName).value = Ext.util.JSON.encode(members);
			}
			var el = document.getElementById(genid + preHfId + "trees_not_loaded");
			if (el) el.value = og.can_submit_members ? 0 : 1;
		}
	}
	return true;
}

og.getSandboxName = function() {
	og.sandboxName = og.sandboxName ? og.sandboxName.replace(/\/+$/, "") : og.getHostName();
	return og.sandboxName;
};

og.formatPopupMemberChooserSelectedValues = function(genid, selected) {
	var html = '';
	var title = '';
	var memberChoosers = Ext.getCmp("menu-panel").items;
	for (i=0; i<selected.length; i++) {
		if ( memberChoosers ) {
			memberChoosers.each(function(item, index, length) {
				var node = item.getNodeById(selected[i]);
				if (node) {
					title += node.text;
					if (i < selected.length - 1) title += ',';
					title += ' ';
				}
			});
		}
	}
	html = title.length > 40 ? title.substring(0, 37) + "..." : title;
	var ico = Ext.get(genid + 'popup_ms_icon');
	if (ico) {
		ico.dom.className = 'ico-edit';
		ico.dom.innerHTML = lang('edit');
	}

	return {html:html, title:title};
}

og.popupMemberChooserHtml = function(genid, obj_type, hf_members_id, selected, no_label) {
	var ico_cls = 'db-ico ico-add';
	var action = lang('add');
	var to_show = {html:'<span class="desc">' + lang('none selected') + '</span>', title:''};
	if (selected) {
		to_show = og.formatPopupMemberChooserSelectedValues(genid, selected);
		ico_cls = 'db-ico ico-edit';
		action = lang('edit');
	}
	var onclick_ev = 'og.showPopupMemberChooser(\''+genid+'\', \''+obj_type+'\', \''+hf_members_id+'\', \''+selected+'\');';
	var html = '';
	if (!no_label)
		html += '<div style="padding-top:5px;"><label style="font-size:100%;display:inline;margin-right:30px;">'+lang('context')+':&nbsp;</label>';
	html += '<span id="'+genid+'popup_member_selector" onclick="'+ onclick_ev +'" style="cursor:pointer;" title="' + to_show.title + '">' + to_show.html + '</span>';
	html += '<span id="'+genid+'popup_ms_icon" class="'+ico_cls+'" onclick="'+ onclick_ev +'" style="cursor:pointer; padding:5px 0 0 20px; margin-left:5px;">' + action + '</span>'
	html += '</div>';
	return html;
};

og.showPopupMemberChooser = function(genid, obj_type, hf_members_id, selected) {
	og.openLink(og.getUrl('object', 'popup_member_chooser', {obj_type: obj_type, genid: genid, selected: selected}), {
		preventPanelLoad:true,
		onSuccess: function(data) {
			var dialog = og.ExtendedDialog.show({
				html: data.current.data,
				title: lang('select context members'),
				iconCls: 'ico-workspace',
				resizable: true,
				minHeight: 273,
				minWidth: 480,
				height: 273,
				width: 480,
				ok_fn: function() {
					og.handleMemberChooserSubmit(genid, obj_type);
					var sel_members = Ext.get(genid + member_selector[genid].hiddenFieldName).getValue();
					var hf = Ext.get(genid + hf_members_id);
					hf.dom.value = sel_members;

					og.ExtendedDialog.hide();
					if (sel_members != '') {
						var to_show = og.formatPopupMemberChooserSelectedValues(genid, Ext.util.JSON.decode(sel_members));
						var sel = Ext.get(genid + 'popup_member_selector');
						sel.dom.innerHTML = to_show.html;
						sel.dom.title = to_show.title;
					}
				}
			});

			return;
		}
	});
}

og.drawComboBox = function(config) {
	if (!config) config = {};
	if (!config.render_to) config.render_to = '';
	if (!config.id) config.id = Ext.id();
	if (!config.name) config.name = Ext.id();
	if (!config.selected) config.selected = 0;
	if (!config.store) config.store = [];
	if (!config.empty_text) config.empty_text = '';
	if (!config.tab_index) config.tab_index = '500';
	if (!config.width) config.width = 200;
	if (!config.typeAhead) config.typeAhead = false;
	//if (!config.editable) config.editable = true;

	var combo = new Ext.form.ComboBox({
		renderTo: config.render_to,
		name: config.name,
		id: config.id,
		value: config.selected,
		store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : config.store
	    }),
	    emptyText: config.empty_text,
	    width: config.width,
        listWidth: config.width,
        tabIndex: config.tab_index,
        displayField: 'text',
        editable: config.editable == true,
        typeAhead: config.typeAhead,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus: true,
        valueField: 'value',
        valueNotFoundText: '',
        disabled: config.disabled == true,
        hidden: config.hidden == true
	});

	Ext.get(config.id).setWidth(config.width - 18);
	combo.setWidth(config.width);
	return combo;
}


og.drawDateMenuPicker = function(config) {
	var datemenu = new Ext.menu.DateMenu({
	    id: config.id ? config.id : Ext.id(),
	    format: og.preferences['date_format'],
	    startDay: og.preferences['start_monday'],
		altFormats: lang('date format alternatives'),
		listeners: config.listeners,
		items: config.items
	});

	Ext.apply(datemenu.picker, {
		okText: lang('ok'),
		cancelText: lang('cancel'),
		monthNames: [lang('month 1'), lang('month 2'), lang('month 3'), lang('month 4'), lang('month 5'), lang('month 6'), lang('month 7'), lang('month 8'), lang('month 9'), lang('month 10'), lang('month 11'), lang('month 12')],
		dayNames:[lang('sunday'), lang('monday'), lang('tuesday'), lang('wednesday'), lang('thursday'), lang('friday'), lang('saturday')],
		monthYearText: '',
		nextText: lang('next month'),
		prevText: lang('prev month'),
		todayText: lang('today'),
		todayTip: lang('today')
	});

	return datemenu;
}

og.quickForm = function (config) {
	if (!config) return false ;
	switch (config.type) {
		case "member":
			var d = config.dimensionId ;
			var tree = Ext.getCmp("dimension-panel-"+d);
			if (tree) {
				var selected = tree.getSelectionModel().getSelectedNode();
				if (!selected || selected.getDepth() == 0  ) {
					var parent = 0 ;
				}else{
					var parent = selected.id ;
				}

				var e = $("#"+config.elId) ;
				if (d) {
					if (og.quick_form_visible == d && $("#quick-form").css('display') != 'none') {
						og.quick_form_visible = 0;
						$("#quick-form").slideUp();
					} else {
						og.openLink(og.getUrl('member', 'quick_add_form',{dimension_id: config.dimensionId, parent_member_id: parent}), {
							preventPanelLoad: true,
							callback: function(success, data) {

								og.quick_form_visible = d;

								if (data.draw_menu) {
									var html = '<ul class="quick-menu-list">';
									$("#quick-form").addClass("menu");
									for (var k=0; k<data.urls.length; k++) {
										var url_obj = data.urls[k];
										
										if (url_obj.group_name) {
											
											const li = document.createElement("li");
											li.classList.add("quick-menu-item");
											li.classList.add("has-submenu");
											li.innerHTML = '<a class="link-ico ico-dash-collapsed" href="#" >'+ url_obj.group_name +'</a>';

											const subUl = document.createElement("ul");
											subUl.classList.add("submenu");
											subUl.classList.add("quick-menu-list");

											const urls = Array.isArray(url_obj.urls) ? url_obj.urls : [url_obj.urls];
											urls.forEach(u => {
												const subLi = document.createElement("li");
												subLi.classList.add("quick-menu-item");
												subLi.classList.add("leaf-menu-item");
												subLi.innerHTML = '<a class="link-ico '+u.iconcls+'" href="#" onclick="og.render_modal_form(\'\', {url:\''+ u.url +'\'}); $(\'#quick-form\').hide(); return false;">'+ u.link_text +'</a>';
												subUl.appendChild(subLi);
											});
											li.appendChild(subUl);

											html += li.outerHTML;

										} else {
											html += '<li class="quick-menu-item">';
											html += '<a class="link-ico '+url_obj.iconcls+'" href="#" onclick="og.render_modal_form(\'\', {url:\''+ url_obj.url +'\'}); $(\'#quick-form\').hide(); return false;">'+ url_obj.link_text +'</a></li>';
										}
									}
									html += "</ul>";

									var offset = e.offset();
									$("#quick-form .form-container").html('').html(html);
									$("#quick-form .form-container").parent().css({top: offset.top + 15, left: offset.left + 15}).slideDown('normal', function(){
										var bottom = $("#quick-form .form-container").css('bottom').replace('px', '');
										if (bottom < 0) $("#quick-form .form-container").animate({"top" : "+="+bottom+"px"});
									});

									// hover events for submenus
									$("#quick-form .form-container li.has-submenu").hover(function() {
										$("#quick-form .form-container li.has-submenu a").addClass("ico-dash-collapsed").removeClass("ico-dash-expanded");
										$("#quick-form .form-container ul.submenu").hide();

										$(this).find("a").addClass("ico-dash-expanded").removeClass("ico-dash-collapsed");
										$(this).find("ul.submenu").css("position", "absolute").css('left', '100%').css('top', $(this).position().top).show();
									});
									// hide all submenus if hovering in a menu item
									$("#quick-form .form-container li:not(.has-submenu):not(.leaf-menu-item)").hover(function() {
										$("#quick-form .form-container li.has-submenu a").addClass("ico-dash-collapsed").removeClass("ico-dash-expanded");
										$("#quick-form .form-container ul.submenu").hide();
									});

								} else {
									if (data.urls && data.urls.length > 0) {
										var url_obj = data.urls[0];
										og.render_modal_form('', {url: url_obj.url});

										if ($("#quick-form").css('display') != 'none') {
											og.quick_form_visible = 0;
											$("#quick-form").slideUp();
										}
									}
								}

							}
						});

					}
				}
			}
			break;

		case "configFilter":

			var e = $("#" + config.genid + "configFilters") ;
            $("#quick-form .form-container").html('').load(og.getUrl('contact', 'quick_config_filter_activity',{members: config.members}),function(){
                var new_offset = {top:e.offset().top,left:e.offset().left-240,right:e.offset().right,bottom:e.offset().bottom};
                $(this).parent().css(new_offset).slideDown();
                return true ;
            });
            return false;

			break;

		case "expenses_graph":

			var e = $("#" + config.genid + "configFiltersExpense") ;
            $("#quick-form .form-container").html('').load(og.getUrl('widget', 'render_template_with_options',{type:config.type}),function(){
                var new_offset = {top:e.offset().top,left:e.offset().left-240,right:e.offset().right,bottom:e.offset().bottom};
                $(this).parent().css(new_offset).slideDown();
                return true ;
            });
            $("#quick-form").css('z-index',100);
            return false;

			break;

		default:
			break
	}
	return false ;
}

og.flash2img = function() {
	$.each ( $('embed') , function(k,elem) {
		var convert =  (typeof elem.get_img_binary == "function") ;
		if ( convert ) {
			var base64 = elem.get_img_binary();
			if ( base64 ){  // Arbitrary min size to check if is image
				var image = document.createElement('img');
				image.src = "data:image/jpg;base64,"+base64 ;
				$(elem).replaceWith( image );
			}
		}
	});

}

og.getMemberTreeNodeColor = function(node) {
	var color = "";
	if (node.ui && node.ui.getIconEl()) {
		classes = node.ui.getIconEl().className.split(" ");
		for(j=0; j<classes.length; j++) {
			if (classes[j].indexOf('ico-color') >= 0) color = classes[j].replace('ico-color', "");
		}
	}
	return color;
}


og.getMemberFromTrees = function(dim_id, mem_id, include_parents) {
	var texts = [];
	var tree = Ext.getCmp("dimension-panel-" + dim_id);
	if (tree) {
		//og.expandCollapseDimensionTree(tree);

		var selnode = tree.getSelectionModel().getSelectedNode();
		selnode_id = selnode ? selnode.id : -1;
		node = tree.getNodeById(mem_id);
		if (node) {
			//if (node.id != selnode_id) {
				texts.push({id:node.id, text:node.text, ot:node.object_type_id, c:og.getMemberTreeNodeColor(node)});
				if (include_parents) {
					while(node.parentNode && node.parentNode.id > 0 && node.parentNode.id != selnode_id) {
						node = node.parentNode;
						if (node) texts.push({id:node.id, text:node.text, ot:node.object_type_id, c:og.getMemberTreeNodeColor(node)});
					}
				}
			//}
		}
	}
	return texts;
}

og.expandCollapseDimensionTree = function(tree, previous_exp, selection_id) {
	if (tree && !tree.expanded_once) {
		if (previous_exp) {
			expanded = previous_exp;
		} else {
			expanded = [];
			tree.root.cascade(function(){
				if (this.isExpanded()) expanded.push(this.id);
			});
		}
		if (selection_id) {
			tree.root.expand(true, false, function(){tree.selectNodes([selection_id])});
		}else{
			tree.root.expand(true, false);
		}
		tree.root.collapse(true, false);

		for(i=0; i<expanded.length; i++) {
			node = tree.getNodeById(expanded[i]);
			if (node) node.expand(false);
		}

		tree.expanded_once = true;
	}
}

og.memberTreeExternalClick = function(tree_id, member_id) {
	var dimensions_panel = Ext.getCmp('menu-panel');
	dimensions_panel.items.each(function(item, index, length) {
		if (item.dimensionCode == tree_id) {
			item.onMemberExternalClick(member_id);
		}
	});

}

/*
 * The email must contain an @ sign and at least one dot (.).
 *  Also, the @ must not be the first character of the email address,
 *   and the last dot must be present after the @ sign, and minimum 2 characters before the end
 */
og.checkValidEmailAddress = function(email) {
	  var atpos=email.indexOf("@");
	  var dotpos=email.lastIndexOf(".");
	  if (atpos<1 || dotpos<atpos+2 || dotpos+2>=email.length){
	      return false;
	  }else{
		  return true;
	  }

}

og.checkEmailAddress = function(element, id_contact, genid, contact_type) {
	if (!genid || genid == '') {
		genid = $("#genid").val();
	}
	if (!id_contact || id_contact == '' || id_contact == 0) {
		id_contact = $('#'+genid+'existing_contact_id').val();
		if (typeof id_contact == 'undefined') id_contact = '';
	}
	if (!contact_type || isNaN(contact_type)) {
		contact_type = $('#'+genid+'existing_contact_type').val();
		if (typeof contact_type == 'undefined') contact_type = '';
	}

	var elementCheck = document.querySelector(element);
	elementCheck.addEventListener("keyup", (e) => {
		e.target.value = e.target.value.replace(/\s/g, '')
	})
	$(element).blur(function(){
		// if emails is marked as deleted then do not show error or disable the submit button
		let del_id = $(this).attr('id').replace('_email_address', '_deleted');
		if ($("#"+del_id).val() == 1) {
			$(element).removeClass("field-error");
			$('.submit').attr('disabled', false);
			$('.submit').removeClass('disabled');
			return;
		}
		
		var field = $(this);
		// Ajax to ?c=contact&a=check_existing_email&email=admin@admin.com&ajax=true
		var url = og.makeAjaxUrl(og.getUrl("contact", "check_existing_email", {email: field.val(), id_contact:id_contact, contact_type:contact_type}));
		og.loading();
		$.getJSON(url, function(data) {
			$(".field-error-msg").remove();
			var contact = data.contact;
			if (contact.status) {
				$(field).addClass("field-error");
				if (contact.is_trashed) {
					$(field).after("<div class='field-error-msg'>"+lang("There is a contact on the trash bin with this email address. User on trash bin",contact.name)+" </div>");
				} else if (contact.is_archived) {
					$(field).after("<div class='field-error-msg'>"+lang("There is a contact archived with this email address. User on archive",contact.name)+" </div>");
				} else {
					$(field).after("<div class='field-error-msg'>"+lang("email already taken by",contact.name)+" </div>");
				}				
				$('.submit').attr('disabled', true);
				$('.submit').addClass('disabled');
			}else{
				$(field).removeClass("field-error");
				if(contact.id){
					og.openLink(og.getUrl('contact', 'edit',{id:contact.id,isEdit:1}));
					$("#quick-form").hide();
				}
				$('.submit').attr('disabled', false);
				$('.submit').removeClass('disabled');
			}
			og.hideLoading();
		});

		setTimeout(function(){og.hideLoading()}, 5000); //If ajax fails
	});
}

og.selectDimensionTreeMember = function(data) {
	var tree = Ext.getCmp("dimension-panel-" + data.dim_id);
	if (tree) {
		if (data.node == 'root') {
			og.contextManager.cleanActiveMembers(data.dim_id);
			if (!tree.hidden) og.contextManager.addActiveMember(0, data.dim_id, tree.root);
			tree.selectRoot();
		} else {
			var treenode = tree.getNodeById(data.node);
			if (treenode) {
				treenode.select();
				treenode.ensureVisible();
				if (!tree.hidden) og.contextManager.addActiveMember(node, data.dim_id, treenode);
			}
		}
	}
}


og.loadWidget = function (name, callback ){
	var url = og.getUrl('dashboard', 'load_widget', {name: name});
	var params = {callback: callback} ;
	og.openLink(url , params) ;

}

og.quickAddTask = function (data, callback) {
	var name = data.name ;
	var due_date = data.due_date ;
	var due_time = data.due_time ;
	var assigned_to = data.assigned_to | 0;

	var ajaxOptions = {
		post : {
			'task[assigned_to_contact_id]': assigned_to ,
			'task[name]': name,
			'task[task_due_date]': due_date,
			'task[task_due_time]': due_time
		},
		callback : callback
	};
	var url = og.makeAjaxUrl(og.getUrl('task', 'quick_add_task', ajaxOptions));
	og.openLink(url, ajaxOptions);
}

og.quickAddWs = function (data, callback) {
	var name = data.name ;
	var parent = data.parent | 0;

	var ajaxOptions = {
		post : {
			'member[name]': name,
			'member[dimension_id]': data.dim_id,
			'member[parent_member_id]': parent,
			'member[object_type_id]': data.ot_id
		},
		callback : callback
	};
	var url = og.makeAjaxUrl(og.getUrl('member', 'add', ajaxOptions));
	og.openLink(url, ajaxOptions);
}



og.onPersonClose = function() {
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	if (currentPanel.id != 'overview-panel') {
		og.closeView();
		return;
	}

	var actual_sel = og.core_dimensions.prev_selection.pop();
	var prev_sel = null;
	if (og.core_dimensions.prev_selection.length > 0) {
		prev_sel = og.core_dimensions.prev_selection[og.core_dimensions.prev_selection.length-1];
	} else {
		if (currentPanel.closable) og.closeView();
	}

	var dimensions_panel = Ext.getCmp('menu-panel');
	dimensions_panel.items.each(function(item, index, length) {
		if (item.dimensionCode == 'feng_persons') {
			if (prev_sel) {
				og.expandCollapseDimensionTree(item);
				var n = item.getNodeById(prev_sel);
				if (n) {
					if (n.parentNode) item.expandPath(n.parentNode.getPath(), false);
					item.fireEvent('click', n);
					og.contextManager.addActiveMember(n.id, item.dimensionId);
				} else {
					item.selectRoot();
					n = item.getRootNode();
					item.fireEvent('click', n);
					og.contextManager.cleanActiveMembers(item.dimensionId);
				}
			} else {
				item.selectRoot();
				n = item.getRootNode();
				item.fireEvent('click', n);
				og.contextManager.cleanActiveMembers(item.dimensionId);
			}

			setTimeout(function() {
		//		og.Breadcrumbs.refresh(n);
				if (n.id == item.getRootNode().id) {
					item.getSelectionModel().fireEvent('selectionchange', item.getSelectionModel(), n);
				}
			}, 500);
		}
	});
}

og.checkRelated = function(type,related_id) {
    var return_data = false;
    var url;
    switch (type) {
        case "task":
            url = og.makeAjaxUrl(og.getUrl("task", "check_related_task"));
            $.ajax({
                url: url,
                dataType: 'json',
                async: false,
                data: {related_id: related_id},
                success: function(data) {
                    return_data = data.status;
                }
            });
            return return_data;
            break;
        case "event":
            url = og.makeAjaxUrl(og.getUrl("event", "check_related_event"));
            $.ajax({
                url: url,
                dataType: 'json',
                async: false,
                data: {related_id: related_id},
                success: function(data) {
                    return_data = data.status;
                }
            });
            return return_data;
        default:
            return return_data;
            break
    }
}

og.openTab = function (id) {
	Ext.getCmp('tabs-panel').activate(id);
}

og.reload_subscribers = function(genid, object_type_id, user_ids) {
	// wait a bit to see if there are more changes to effectively reload the subscribers, 
	// to avoid sending 3 parallel requests that will override one to another, only send the last one.
	if (og.reload_subscribers_timeout) {
		clearTimeout(og.reload_subscribers_timeout);
	}

	og.reload_subscribers_params = {
		genid: genid,
		object_type_id: object_type_id,
		user_uds: user_ids
	};

	og.reload_subscribers_timeout = setTimeout(function() {
		og.do_reload_subscribers(og.reload_subscribers_params.genid, og.reload_subscribers_params.object_type_id, og.reload_subscribers_params.user_ids);
	}, 500);
}

og.do_reload_subscribers = function(genid, object_type_id, user_ids) {
	if (!user_ids) {
		var uids = App.modules.addMessageForm.getCheckedUsers(genid);
	} else {
		var uids = user_ids;
	}
	
	var assigned_to = 0;
	var ot = og.objectTypes[object_type_id];
	if (ot && ot.name == 'task') {
		var combo = Ext.getCmp(genid + 'taskFormAssignedToCombo');
		if (combo) assigned_to = combo.getValue();
	}

	var subs = Ext.get(genid + 'add_subscribers_content');
	if (subs) subs.mask();

	og.openLink(og.getUrl('object', 'render_add_subscribers', {
		context: member_selector.get_selected_context(genid),
		users: uids,
		genid: genid,
		assigned_to: assigned_to,
		otype: object_type_id
	}), {
		preventPanelLoad: true,
		callback: function(success, data) {
			$('#' + genid + 'add_subscribers_content').html(data.current.data);
			var subs_content = Ext.get(genid + 'add_subscribers_content');
			if (subs_content) subs_content.unmask();
		}
	});
}

function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		 for(var item in arr) {
			  var value = arr[item];

			  if(typeof(value) == 'object') { //If it is an array,
				   dumped_text += level_padding + "'" + item + "' ...\n";
				   dumped_text += dump(value,level+1);
			  } else {
	 			   dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			  }
		 }
	} else { //Stings/Chars/Numbers etc.
	 dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}


/**
 * Clears all dimension tree selections
 */
og.clearDimensionSelection = function() {
	var dimensions_panel = Ext.getCmp('menu-panel');
	var n = null;
	var tree = null;

	// select all root nodes
	dimensions_panel.items.each(function(item, index, length) {
		item.selectRoot();
		if (n == null || tree == null) {
			n = item.getRootNode();
			tree = item;
		}
		og.contextManager.cleanActiveMembers(item.dimensionId);
	});

	// reset bradcrumbs with the first dimension onclick event
	if (tree != null && n != null) {
		tree.fireEvent('click', n);
	}

	// force all tab panels reload when needed
	Ext.getCmp('tabs-panel').items.each(function(tab, index, length) {
		tab.loaded = false;
	});

	// relaod current tab
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	if (currentPanel && currentPanel.id != 'overview') {
		currentPanel.reload();
	}
}

/**
 * Dimension column renderer for ext grid listings
 */
og.renderDimCol = function(value, p, r) {
	if (r.id == 'quick_add_row') return value;
	var dim_id = p.id.replace(/dim_/, '');

	var exclude_parents_path = false;
	if (og.dimensions_that_exclude_parents_in_breadcrumbs && og.dimensions_that_exclude_parents_in_breadcrumbs[dim_id]) {
		exclude_parents_path = og.dimensions_that_exclude_parents_in_breadcrumbs[dim_id];
	}

	var text = og.dimColEmptyBreadcrumb(dim_id, r.data.memPath, exclude_parents_path);
	return text;
}
og.dimColEmptyBreadcrumb = function(dim_id, memPath, exclude_parents_path) {
	var text = '';
	var mpath = null;
	if (memPath) {
		mpath = Ext.util.JSON.decode(memPath);
	}
	if (mpath) {
		var mpath_aux = {};
		mpath_aux[dim_id] = {};
		for (t in mpath[dim_id]) {
			mpath_aux[dim_id][t] = mpath[dim_id][t];
		}
		text = "<div class='breadcrumb-container' style='width: 100%;'>";
		text += og.getEmptyCrumbHtml(mpath_aux, '.breadcrumb-container', null, null, exclude_parents_path, true);
		text += "</div>";
	}
	return text;
}


og.renderAmountColumn = function(value, p, r) {
	if (typeof(value) == 'undefined') {
		return '';
	}

	var str_val = value;
	if (typeof(value) == 'object') {
		str_val = "<div class='amounts-container'>";
		for (curr_id in value) {
			var amount = value[curr_id];
			if (typeof(amount) == 'function') continue;
			str_val += "<div class='amount-container'>" + amount + "</div>";
		}
		str_val += "</div>";
	}
	return str_val;
}

og.expandMenuPanel = function(options) {
	var animate = options.animate ? options.animate : true;
	if (options.expand) Ext.getCmp('menu-panel').expand(animate);
	else if (options.collapse) Ext.getCmp('menu-panel').collapse(animate);
}

og.addNodesToTree = function(tree_id, more_nodes_left) {
	var tree = Ext.getCmp(tree_id);
	var o = og.tmp_members_to_add[tree_id].pop();

	if (o) {
		for (i=0; i<o.length; i++) {
			if (!o[i]) continue;

			// check if node is already in tree, prevent duplicated tree nodes.
			if (tree.getNodeById(o[i].id)) {
				continue;
			}

			var n = tree.loader.createNode(o[i]);
			n.object_id = o[i].object_id;
			n.options = o[i].options;
			n.object_controller = o[i].object_controller;
			n.allow_childs = o[i].allow_childs;

			if (n) og.tmp_node[tree_id].appendChild(n);
		}
	}

	if (more_nodes_left) {
		og.addViewMoreNode(tree.getRootNode(), tree_id, og.treeLoaderViewMoreCallback);
	} else {
		var old_view_more_node = tree.getNodeById('view_more_' + tree.getRootNode().id);
		if (old_view_more_node) old_view_more_node.remove();
	}
}

og.showHideWidgetMoreLink = function(cls, linkid, show) {
	og.showHide('hidelnk' + linkid);
	og.showHide('showlnk' + linkid);

	if (show) $(cls).show("slow");
	else $(cls).hide("slow");
}

og.showHideAppLogDetails = function(id) {
	og.showHide('hidelink' + id);
	og.showHide('showlink' + id);
	og.showHide('logdetails' + id);
}

og.getColorInputHtml = function(genid, field_name, value, col, label, disabled) {
	if (!col) col = 'color';
	if (!field_name) field_name = 'member';
	if (!value) value = 0;

	var html = '';
	if (label) {
		html += '<label for="'+ genid + field_name +'_' + col +'">' + label + ':</label>';
	}
	html += '<input name="'+field_name+'[' + col + ']" id="'+ genid + field_name +'_' + col +'" class="color-code" type="hidden" value="'+value+'" />';
	if (!disabled) {
		html += "<div class='ws-color-chooser'>";
		for (var i=0; i<=24; i++) {
			var cls = (value == i)?'selected':'';
			html += "<div  class='ico-color"+i+ " "+ cls + " color-cell'  onClick='$(\"input.color-code\").val(\""+i+"\");$(\".color-cell\").removeClass(\"selected\");$(this).addClass(\"selected\");'></div>";
			if (i==12) {
				html +=	'<div class="x-clear"></div><div style="width:20px;float:left;height:10px;"></div>';
			}
		}
		html += '<div class="x-clear"></div>';
		html += '</div>';
	} else {
		html += "<div class='ico-color"+value+"' style='float:left;width:16px;'>&nbsp;</div>";
	}

	return html;
}


og.showSelectTimezone = function(genid)	{
	var check = document.getElementById(genid + "userFormAutoDetectTimezoneYes");
	var div = document.getElementById(genid + "selecttzdiv");
	if (check && div) div.style.display = check.checked ? "none" : "";

	if (check) $("#"+genid+"autodetected_tz_div").css('display', check.checked ? "" : "none");
};

og.getTimezoneFromBrowser = function(server, genid) {
	var check = document.getElementById(genid + 'userFormAutoDetectTimezoneYes');
	var combo = document.getElementById(genid + 'userFormTimezone');
	if (check.checked){
		var client = new Date();
		var diff = client.getTime() - server.getTime();
		diff = Math.round(diff*2/3600000);
		for (var i=0; i<combo.options.length; i++) {
			if (combo.options[i].value == diff/2) {
				combo.options[i].selected = 'selected';
			}
		}
	}
};

og.goToOverview = function(close_active_tab) {
	var opanel = Ext.getCmp('overview-panel');
	if (opanel) {
		var active_tab = Ext.getCmp('tabs-panel').getActiveTab();
		Ext.getCmp('tabs-panel').setActiveTab(opanel);
		if (close_active_tab && active_tab) {
			Ext.getCmp('tabs-panel').remove(active_tab);
		}
	}
}

og.goback = function(btn) {
	var p = og.getParentContentPanel(Ext.fly(btn));
	if (p) Ext.getCmp(p.id).back();
};

og.fade_background_color = function(id, color) {
	if (!color) color = "white";
	$("#"+id).animate({
	   backgroundColor: color
	}, 'slow');
}


og.dimensionTreeDoLayout = function(genid, dim_id) {
	var memberChooserPanel = Ext.getCmp(genid + '_with_permissions_' + dim_id);
	if (memberChooserPanel && !memberChooserPanel.initialized) {
		memberChooserPanel.doLayout();
		memberChooserPanel.initialized = true;
	}
	var memberChooserPanel2 = Ext.getCmp(genid + '_without_permissions_' + dim_id);
	if (memberChooserPanel2 && !memberChooserPanel2.initialized) {
		memberChooserPanel2.doLayout();
		memberChooserPanel2.initialized = true;
	}
}


og.onParentMemberRemove = function (genid){
	$("#" + genid + "memberParent").val(0);
}


og.onParentMemberSelect = function (genid, dimension_id, member_id){
	member_selector.remove_all_selections(genid);
	if (!member_id) {
		// remove member
		$("#" + genid + "memberParent").val(0);
		return;
	}


	$("#" + genid + "memberParent").val(member_id);
	member_selector.add_relation(dimension_id, genid, member_id, false, false, true);
	og.prev_parent = member_id;

	og.userPermissions.reload_member_permissions(genid, dimension_id, member_id);

	// inherit color from parent member (only if new member)
	og.populateMemberColorInput(genid, member_id);
}

/**
 * Populate the member color input field with the parent member's color
 * @param {string} genid - the id of the generator
 * @param {number} parent_member_id - the id of the parent member
 */
og.populateMemberColorInput = function(genid, parent_member_id) {
	let is_new_member = $("#" + genid + "member_id").val() == 0;
	if (!is_new_member || parent_member_id === undefined || parent_member_id <= 0) {
		return;
	}

	var applyParentColor = function(member) {
		if (!member) return;
		var color = member.color !== undefined ? member.color : member.c;
		if (color === undefined || color === null || isNaN(color) || color <= 0) {
			return;
		}
		var colorSelector = "#" + genid + "tabs .color-cell.ico-color" + color
			+ ", #" + genid + "member_color_input .color-cell.ico-color" + color;
		var $colorCell = $(colorSelector);
		if ($colorCell.length > 0) {
			$colorCell.first().click();
		}
	};

	var members = og.getMemberFromOgDimensions(parent_member_id);
	if (members && members.length > 0) {
		applyParentColor(members[0]);
	} else {
		og.getMemberFromServer(parent_member_id, function(dimension_id, member) {
			applyParentColor(member);
		});
	}
}


/**
 * Create style sheet for current colors
 */
og.createBrandColorsSheet = function(brand_colors) {
	var header_back = brand_colors['brand_colors_head_back'];
	var tabs_back = brand_colors['brand_colors_tabs_back'];
	var tabs_font = brand_colors['brand_colors_tabs_font'];
	var header_font = brand_colors['brand_colors_head_font'];
	var brand_texture = brand_colors['brand_colors_texture'];

	var texture = '';
	if(brand_texture){
		texture = //'background-image: -moz-linear-gradient(45deg, rgba(0, 0, 0, 0.25) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, 0.25) 75%, rgba(0, 0, 0, 0.25)), -moz-linear-gradient(45deg, rgba(0, 0, 0, 0.25) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, 0.25) 75%, rgba(0, 0, 0, 0.25));'
			//+ 'background-image: -webkit-linear-gradient(45deg, rgba(0, 0, 0, .25) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, .25) 75%, rgba(0, 0, 0, .25)), -webkit-linear-gradient(45deg, rgba(0, 0, 0, .25) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, .25) 75%, rgba(0, 0, 0, .25));'
			'background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.05) 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 75%, rgba(255, 255, 255, 0.05) 75%, rgba(255, 255, 255, 0.05)), linear-gradient(45deg, rgba(255, 255, 255, 0.05) 25%, rgba(0, 0, 0, 0) 25%, rgba(0, 0, 0, 0) 75%, rgba(255, 255, 255, 0.05) 75%, rgba(255, 255, 255, 0.05));'
			+ 'background-position: 0 0pt, 2px 2px;'
			+ 'background-size: 4px 4px; ';
	}

	var cssRules = '.x-accordion-hd, ul.x-tab-strip li {background-color: #' + tabs_back + '; ' + texture + '}';
	cssRules += '.x-accordion-hd, ul.x-tab-strip {background-color: #' + tabs_back + '; ' + texture + '}';
	cssRules += 'ul.x-tab-strip li {border-color: #' + tabs_back + '}';
	cssRules += '#header {background-color: #' + header_back + '; ' + texture + '}';
	cssRules += '.x-accordion-hd, .x-tab-strip span.x-tab-strip-text {color: #' + tabs_font + '}';

	//cssRules += 'ul.x-tab-strip li.x-tab-strip-active span.x-tab-strip-text {color: #' + tabs_back + ' !important}';
	cssRules += '#logodiv h1, div.og-loading, div.header-breadcrumb-container {color: #' + header_font + '}';
	// dimension title
	cssRules += '.x-accordion-hd {background-color: #' + tabs_back + '}';

	// selected member
	var node_selected_back = color_utils.make_transparent_color('#' + tabs_back);
	if (node_selected_back) {
		cssRules += '.x-tree-node .x-tree-selected {background-color: ' + node_selected_back + '; border-color: ' + color_utils.darker_html_color(node_selected_back) + '}';
	}

	var styleElement = document.createElement("style");
	styleElement.type = "text/css";
	if (styleElement.styleSheet) {
		styleElement.styleSheet.cssText = cssRules;
	} else {
		styleElement.appendChild(document.createTextNode(cssRules));
	}
	document.getElementsByTagName("head")[0].appendChild(styleElement);
}



/**
 * Renders a modal form
 */
og.render_modal_form = function(genid, options) {
	var parameters = options.params || {};
	parameters.modal = 1;

	var form_url = '';
	if(typeof(options.url) != 'undefined'){
        form_url = options.url;
        for (pname in parameters) {
        	form_url += '&' + pname + '=' + parameters[pname];
        }
	}else{
        form_url = og.getUrl(options.c, options.a, parameters);
	}

	og.openLink(form_url, {
		preventPanelLoad: true,
		callback: function(success, data) {

			if (!success) return;

			var id = genid + (options.id ? options.id : '');
			var div = document.createElement('div');
			var close_cls = options.close_cls || '';

			div.className = options.cls || '';
			div.id = id;
			var modalHtml = data.current && data.current.data ? data.current.data : '';
			// simplemodal measures and positions the form the moment it is inserted, before its inline
			// scripts run (they are deferred until the html is in the DOM). Only then does tabs() collapse
			// the panels and apply their max-height, so a tall first tab (many custom properties /
			// property groups) gets measured at full height and the modal is pinned to the top. Refit and
			// re-center once the scripts have run so the position reflects the rendered content.
			div.innerHTML = og.extractScripts(modalHtml, function() {
				og.refit_modal_form(true);
				// tabs have different heights: make sure the form still fits after switching
				$(div).on('tabsactivate', function() {
					og.refit_modal_form();
				});
			});
			if (data.inlineScripts && data.inlineScripts.length) {
				for (var is = 0; is < data.inlineScripts.length; is++) {
					try {
						if (window.execScript) {
							window.execScript(data.inlineScripts[is]);
						} else {
							window.eval(data.inlineScripts[is]);
						}
					} catch (e) {
						og.err(e.message);
					}
				}
			}

			var modal_params = {
				'appendTo': '#modal-forms-container',
				'focus': typeof(options.focusFirst) != 'undefined' ? options.focusFirst : true,
				'escClose': typeof(options.escClose) != 'undefined' ? options.escClose : true,
				'overlayClose': typeof(options.overlayClose) != 'undefined' ? options.overlayClose : false,
				'closeHTML': '<a id="'+genid+'_close_link" class="'+close_cls+' modal-close" title="'+lang('close')+'"><i class="icon-circle-x"></i></a>',
				'onClose': function (dialog) {
					$("#modal-forms-container").hide();
					$.modal.close();
                    if(options.callbackOnClose && typeof options.callbackOnClose=='function'){
                        options.callbackOnClose();
                    }
				},
				'onShow': function (dialog) {
					$(".simplemodal-container").css('position', 'absolute');
					$("#modal-forms-container").show();

					// add close image to close-link
					if (options.hideCloseIcon) {
						$("#"+genid+"_close_link").hide();
					}

					// resize and reposition form when changing tab and content is larger than the available space
					//$(".ui-tabs-anchor").click(function(){ og.resize_modal_form(); });

					// first execution sometimes fails to center the modal
					/*var offset = $(".simplemodal-data").offset();
					if (offset.top == 0) {
						setTimeout(function(){
							var h = $(".simplemodal-data").innerHeight();
							var h2 = $(".simplemodal-data").closest(".simplemodal-container").innerHeight();
							var d = h2 - h;
							if (d > 0) {
								$(".simplemodal-container").css({top: Math.floor(d/2) + 'px'});
							}
						}, 100);
					}*/

					// adjust container height
					$(".simplemodal-container .simplemodal-wrap").css('min-height', ($("#simplemodal-data").outerHeight()+15)+'px');
					$(".simplemodal-container .simplemodal-wrap").css('height', 'auto');
					$(".simplemodal-container .simplemodal-wrap").css('overflow', '');
                    // $(".simplemodal-container").css('margin-top', '20px');

					// set main input width
					og.update_modal_main_input_width();

					// Update the position of the extjs dropdown lists when scrolling (member and contact selectors)
					$(".edit-form-tabs .form-tab").on('scroll', updateExtjsDropdownListPosition);
			    }
			};
			if (options.position) {
				modal_params.position = options.position;
			}
			setTimeout(function() {
				$.modal(div, modal_params);
			}, 100);
		}
	});
}

og.resize_modal_form = function() {
	//if form height is larger than screen height => resize it or reposition
	setTimeout(function() {
		var offset = $(".simplemodal-data").offset();
		var modalh = $(".simplemodal-data").height();
		var winh = $(".simplemodal-overlay").height();
		var headerh = $(".simplemodal-data .coInputHeader").height();
		var cont_offset = $(".simplemodal-container").offset();

		$(".simplemodal-data .form-tab").css({'max-height':(winh - (cont_offset && cont_offset.top ? cont_offset.top : 0) - 175)+'px', 'overflow-y':'auto', 'overflow-x':'hidden'});

		if (modalh > winh) {
			// resize container
			$(".simplemodal-container").css({height:(winh - 10)+'px'});
			$(".simplemodal-data .form-tab").css({'max-height':(winh - headerh - 125)+'px', 'overflow-y':'auto', 'overflow-x':'hidden'});
		} else if (offset && modalh + offset.top > winh) {
			// only reposition
			/*var otop = (winh - modalh) / 2;
			if (otop < 0) otop = 0;
			//$(".simplemodal-container").css({top:(otop)+'px'});
			$(".simplemodal-data .form-tab").css({'max-height':(winh - headerh - 175)+'px', 'overflow-y':'auto', 'overflow-x':'hidden'});*/
		}
	}, 500);
}


og.center_modal_form = function() {
	var modalh = $(".simplemodal-data").height();
	var winh = $(".simplemodal-overlay").height();

	var otop = (winh - modalh) / 2;
	if (otop < 0) otop = 0;
	$(".simplemodal-container").css({
		top: (otop)+'px'
	});
}

/**
 * Re-fits the modal container to the current height of its content.
 * simplemodal freezes the container height (capped at the window height) and og.render_modal_form's
 * onShow freezes the wrap min-height when the form is first shown; call this whenever the content
 * height changed afterwards (tabs initialised, tab switched, custom properties loaded by ajax, etc).
 *
 * @param center true to re-center the modal (used once, when the form has just been rendered).
 *   Otherwise the modal keeps its position and is only moved up when the content no longer fits
 *   in the window, so switching between tabs of different heights does not make it jump around.
 */
og.refit_modal_form = function(center) {
	var container = $(".simplemodal-container");
	if (!container.length) return;

	container.css('height', 'auto');
	container.find('.simplemodal-wrap').css('min-height', '');
	if ($.modal && $.modal.impl && $.modal.impl.d) {
		$.modal.impl.d.origHeight = null;
	}

	if (center) {
		og.center_modal_form();
		return;
	}

	var modalh = $(".simplemodal-data").height();
	var winh = $(".simplemodal-overlay").height();
	var top = parseInt(container.css('top'), 10) || 0;
	if (top + modalh > winh) {
		container.css({
			top: Math.max(0, winh - modalh) + 'px'
		});
	}
}

og.update_modal_main_input_width = function() {
	// use a small timout to let the form process the css first
	setTimeout(function() {
		var title_w = $(".simplemodal-data .coInputHeader .coInputHeaderUpperRow .coInputTitle").width();
		var buttons_w = $(".simplemodal-data .coInputHeader .coInputButtons").width();
		var prefix_w = $(".simplemodal-data .coInputHeader .coInputName .object-prefix").width();
		var total_w = $(".simplemodal-data .coInputHeader").width();
	
		var input_count = $(".simplemodal-data .coInputHeader .coInputName input.title").length;
		if (input_count < 1) input_count = 1;
	
		$(".simplemodal-data .coInputHeader .coInputName input.title").css('width', (((total_w - title_w - buttons_w - prefix_w) / input_count) - 35) + 'px');
	}, 100);
}

/**
 * Submits a modal form
 */
og.submit_modal_form = function(form_id, callback_fn, options) {
	//prevent double submit
	if ($('#'+form_id).data().isSubmitted) {
        return false;
    }

	options = options || {};
	var form = document.getElementById(form_id);
	var params = {modal: 1};

	var all_inputs = $('#'+form_id+' input');
	for (var i=0; i<all_inputs.length; i++) {
		if(all_inputs[i].type == 'checkbox') {
			params[all_inputs[i].name] = all_inputs[i].checked ? '1' : '0';
		} else if (all_inputs[i].type == 'radio') {
			if (all_inputs[i].checked) {
				params[all_inputs[i].name] = params[all_inputs[i].name] = all_inputs[i].value;
			}
		} else {
			//check multiple values (if thers [] at the end of the input name)
			//EXAMPLE object_custom_properties[2][] object_custom_properties[2][] => object_custom_properties[2][0] object_custom_properties[2][1]
			if(all_inputs[i].name.indexOf("[]") > -1 && all_inputs[i].name.slice(-2) == "[]"){
				var in_name = all_inputs[i].name;
				in_name = in_name.substring(0, in_name.length - 2);
				var arr_index = 0;
				while (arr_index < all_inputs.length) {
					var new_in_name = in_name + "[" + arr_index + "]";
					if(!(new_in_name in params)){
						params[new_in_name] = all_inputs[i].value;
						break;
					}
					arr_index++;
				}
			}else{
				params[all_inputs[i].name] = all_inputs[i].value;
			}
		}
	}
	var all_selects = $('#'+form_id+' select');
	for (var i=0; i<all_selects.length; i++) {
		var selopt = all_selects[i].options[all_selects[i].selectedIndex];
		if (selopt) {
			params[all_selects[i].name] = selopt.value;
		}
	}
	var all_textareas = $('#'+form_id+' textarea');
	for (var i=0; i<all_textareas.length; i++) {
		params[all_textareas[i].name] = all_textareas[i].value;
	}
	// Disable submit button when clicked
	$('#'+form_id+' .submit').prop('disabled', true);
	og.openLink(form.action, {
		post: params,
		preventPanelLoad: true,
		hideLoading: options.hideLoading || false,
		hideErrors: options.hideErrors || false,
		callback: function(success, data) {
			// Enable submit button once the callback is received
			$('#'+form_id+' .submit').prop('disabled', false);
			if (typeof(data) == "string") {
				try {
					data = eval(data);
				} catch (e) {}
			}
			if (data.errorCode > 0) {
				if (data.showMessage) og.err(data.errorMessage);
				$('#'+form_id).data().isSubmitted = false;
				return;
			} else {
				if (callback_fn && typeof(callback_fn) == 'function') {
					callback_fn.call(null, data);
				}
				if (data.msg) {
					og.msg(lang('success'), data.msg);
				}
				$('.modal-close').click();
			}
		}
	});

	// mark the form as processed, so we will not process it again
    $('#'+form_id).data().isSubmitted = true;
    setTimeout(function(){
    	if ($('#'+form_id).length){
    		$('#'+form_id).data().isSubmitted = false;
    	}
    }, 5000);
}

og.onWidgetDimensionChanged = function(checkbox, dim_id, is_default) {
	var enable = $(checkbox).attr('checked') == 'checked' ? '1' : '0';
	og.openLink(og.getUrl('config', 'enable_disable_widget_dimension', {dim_id:dim_id, enable:enable, is_default:is_default}), {
		silent:true,
		callback: function(success, data) {
			//og.advanced_billing.reload_allowed_dimensions(parseInt(dim_id));	
		}
	});
	
}


og.reload_active_tab = function() {
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	if (currentPanel) {
		currentPanel.reload();
	}
}

og.removeFileExtension = function(filename) {
	if (filename.lastIndexOf('.') > 0) {
		return filename.substring(0, filename.lastIndexOf('.'));
	} else {
		return filename;
	}
}

// animates an element using shake effect
og.single_shake = function(selector, direction, distance, times) {
	direction = direction || 'up';
	distance = distance || 3;
	times = times || 5;

	$(selector).effect("shake", {direction:direction, distance: distance, times: times});
}

// draws a highlight hint, used when highlighting a link
og.draw_highlight_hint = function(selector, hint_id, hint_text, hint_pos) {
	$('.highlight-hint').remove();
	if (!hint_pos) hint_pos = 'right';
	if (!hint_text) hint_text = lang('click here');
	$("<div>"+ hint_text +"</div>").attr('id', hint_id).addClass('highlight-hint').appendTo('body');

	var offset = $(selector).offset();
	$("#" + hint_id).click(function(){
		$(selector).click();
		$(selector).removeClass('highlighted');
		$(this).remove();
	});
	switch (hint_pos) {
		case 'right':
			$("#" + hint_id).css('top', offset.top + 'px');
			$("#" + hint_id).css('left', (offset.left + $(selector).width() + 25) + 'px');
			break;
		case 'left':
			$("#" + hint_id).css('top', offset.top + 'px');
			$("#" + hint_id).css('left', (offset.left - $("#" + hint_id).width() - 25) + 'px');
			break;
		case 'top':
			$("#" + hint_id).css('top', (offset.top - $("#" + hint_id).width() - 25)+ 'px');
			$("#" + hint_id).css('left', offset.left + 'px');
			break;
		case 'bottom':
			$("#" + hint_id).css('top', (offset.top + $(selector).height() + 25)+ 'px');
			$("#" + hint_id).css('left', offset.left + 'px');
			break;
		default: break;
	}


	$("#" + hint_id).show();
}

// highlights a link, with animation and hint
og.highlight_link = function(config) {
	var selector = config.selector;
	var step = config.step;
	var time_active = config.time_active;
	var timeout = config.timeout;
	var hint_text = config.hint_text;

	if (!selector) return;

	if (config.prev_click_selector) {
		$(config.prev_click_selector).click();
	}

	if ($(selector).length == 0) {
		setTimeout(function() {
			og.highlight_link(config);
		}, 500);
		return;
	}

	var hint_id = Ext.id();
	timeout = timeout || 10;
	time_active = time_active || 3000;

	setTimeout(function(){
		$(selector).addClass('highlighted');
		og.draw_highlight_hint(selector, hint_id, hint_text, config.hint_pos);
		// shake 2 times
		if (config.shake) {
			og.single_shake(selector, 'up', 3, 5);
			setTimeout(function(){ og.single_shake(selector, 'up', 3, 5); }, 1500);
		}
		if (config.animate_opacity > 0) {
			var animate_times = 0;
			while (animate_times < config.animate_opacity) {
				$(selector).animate({opacity:'.5'}, 'slow').animate({opacity:'1.0'}, 'slow');
				animate_times++;
			}
		}
		// remove class after time_active miliseconds
		setTimeout(function(){
			$(selector).removeClass('highlighted');
			$("#" + hint_id).remove();
		}, time_active);

		$(selector).click(function(){
			// on click remove class
			$(selector).removeClass('highlighted');
			$("#" + hint_id).remove();

			// update step after clicking hint
			if (step && step > 0) {
				var reload = config.reload_panel ? config.reload_panel : '0';
				og.openLink(og.getUrl('more','set_getting_started_step', {step: step, reload_panel: reload}), {hideLoading:true});
				if (step == 99) {
					$("#tabs-panel__more-panel").addClass("x-tab-strip-closable");
					$("#tabs-panel__more-panel .x-tab-strip-text").removeClass("ico-more-tab");
					$("#tabs-panel__more-panel .x-tab-strip-text").addClass("ico-administration");
					$("#tabs-panel__more-panel .x-tab-strip-text").html(lang('more-panel'));
				}
			}
		});
	}, timeout);
}

og.initPopoverBtns = function(btns){
	for (var i = 0; i < btns.length; i++) {
		var btn = $("#"+btns[i].id);

		// popover config
		// `content` is a function (not a string captured once here) so the
		// referenced template div's HTML is read live at show time, not at
		// init time. Reading it once up front is fragile: if this button's
		// row is initialised before its sibling template div is reliably
		// queryable (e.g. rows rendered/synced across multiple async batches),
		// the lookup can return undefined and Bootstrap silently falls back
		// to its bare default popover (showing only the title). Reading at
		// show time guarantees the button (and its template) are already in
		// the live DOM, since the user is actively interacting with it.
		var popover_options = {
			html: true,
			content: function () {
				return $("#" + $(this).data("templateid")).html();
			},
			delay: {
				show: "100",
				hide: "200"
			}
		}

		// hack for firefox
		if ($.browser.mozilla) {
			popover_options.trigger = 'focus';
			btn.on('click', function() {
				$(this).focus();
			});
		}

		btn.popover(popover_options);

	}
}

og.setSettingsClosed = function() {
	og.openLink(og.getUrl('more', 'set_settings_closed'), {hideLoading: true});
}



og.renderUserTypeSelector = function(config) {
	container_id = config.container_id;
	if (!config.id) config.id = Ext.id() + '_user_type_sel';

	var selected_type = 0;
	for (role_id in og.userRoles) {
		if (role_id == config.selected_value) {
			selected_type = og.userRoles[role_id].parent;
			break;
		}
	}

	var sel_type = document.createElement("select");
	sel_type.setAttribute("onchange", "og.reloadUserRoleSelector({id:'"+config.id+"', input_name:'"+config.input_name+"'}, this.value);");
	sel_type.setAttribute("class", "user-typegroup-selector");
	sel_type.id = config.id + '_type';
	document.getElementById(container_id).appendChild(sel_type);

	for (type_id in og.userTypes) {
	    var option = document.createElement("option");
	    option.value = type_id;
	    //option.text = og.userTypes[type_id].name;
	    option.innerHTML = og.userTypes[type_id].name;
	    if (selected_type == type_id) {
		    option.setAttribute('selected', 'selected');
	    }
	    sel_type.appendChild(option);
	}

	og.renderUserRoleSelector(config, selected_type);

	// explanation div
	$("#"+container_id).append('<div style="display:none;" id="'+genid+'user_role_explanation" class="user-role-explanation">'+ og.userRoles[config.selected_value].hint +'</div><div class="clear"></div>');
	setTimeout(function(){
		$("#"+genid+"user_role_explanation").css('width', ($("#"+container_id).width() - 275 - $("#"+config.id+"_type").outerWidth() - $("#"+config.id+"_role").outerWidth() ) + 'px').show();
	}, 100);
}

og.renderUserRoleSelector = function(config, parent_type) {
	container_id = config.container_id;

	var sel_role = document.createElement("select");
	if (config.input_name) sel_role.name = config.input_name;
	sel_role.onchange = "";
	sel_role.id = config.id + '_role';
	sel_role.className = "user-type-selector";
	var container = document.getElementById(container_id);
	if (container) {
		container.appendChild(sel_role);
	}

	for (role_id in og.userRoles) {
		if (og.userRoles[role_id].parent == parent_type) {
		    var option = document.createElement("option");
		    option.value = role_id;
		    //option.text = og.userRoles[role_id].name;
		    option.innerHTML = og.userRoles[role_id].name;
		    if (config.selected_value) {
			    if (config.selected_value == role_id) {
			    	option.setAttribute('selected', 'selected');
			    }
		    } else {
		    	if (role_id == og.defaultRoleByType[parent_type]) {
		    		option.setAttribute('selected', 'selected');
		    	}
		    }
		    sel_role.appendChild(option);
		}
	}
}

og.reloadUserRoleSelector = function(config, parent_type) {
	var sel_role = document.getElementById(config.id + '_role');
	for (var i=sel_role.options.length - 1; i>=0; i--) {
		sel_role.remove(i);
	}

	for (role_id in og.userRoles) {
		if (og.userRoles[role_id].parent == parent_type) {
		    var option = document.createElement("option");
		    option.value = role_id;
		    option.text = og.userRoles[role_id].name;
		    if (role_id == og.defaultRoleByType[parent_type]) {
	    		option.setAttribute('selected', 'selected');
	    	}
		    sel_role.appendChild(option);
		}
	}
	$(sel_role).change();
}


og.addUserGroupToUser = function(genid, container_id, group) {
	if (typeof(group) == 'string') group = Ext.util.JSON.decode(group);

	var values = $("#"+genid+"_user_groups").val();
	values = values.split(',');
	var idx = values.indexOf(group.id.toString());
	if (idx < 0) {

		var remove_link = '&nbsp;<a class="remove-link link-ico ico-delete" href="#" title="'+lang('remove')+'" onclick="og.removeUserFromUserGroup(\''+genid+'\', '+group.id+');">&nbsp;</a>';
		//var group_link = '<a href="#" onclick="og.openLink(og.getUrl(\'group\', \'view\', {id:'+group.id+'}))">'+ group.name +'<a/>';
		var group_link = group.name;

		var html = '<div class="user-group" id="'+genid+'user_group_'+group.id+'">' + group_link + remove_link + '</div>';
		$("#"+container_id).append(html);

		values.push(group.id);
		$("#"+genid+"_user_groups").val(values.join());
	}
}

og.removeUserFromUserGroup = function(genid, group_id) {
	var values = $("#"+genid+"_user_groups").val();
	values = values.split(',');

	var idx = values.indexOf(group_id.toString());
	if (idx >= 0) {
		values.splice(idx, 1);
		$("#"+genid+"_user_groups").val(values.join());
	}
	$("#"+genid+"user_group_"+group_id).remove();
}

og.download_exported_file = function(filename, filetype) {
	setTimeout(function() {
		var params = {};
		if (filename) params.fname = filename;
		if (filetype) params.file_type = filetype;
		window.location = og.getUrl('contact', 'download_exported_file', params);
	}, 1000);
}

og.expandAllChildNodes = function(node) {
	if (node) {
		setTimeout(function() {
			node.eachChild(function(n){
				if (n) n.expand(true, false, og.expandAllChildNodes);
			});
		}, 1000);
	}
}


/**
 * Keyword the user must type to confirm the deletion of a member.
 *
 * The confirmation modal renders this very value in its instructions, so the word shown to the
 * user and the word validated on confirm can never drift apart from one translation to another.
 *
 * @return {String} the localized confirmation keyword
 */
og.getDeleteConfirmationKeyword = function() {
	return lang('delete');
}

/**
 * Check whether the text typed in the delete confirmation modal confirms the deletion.
 *
 * Matching is case insensitive. Besides the localized keyword, the untranslated "DELETE" is
 * always accepted, because installs with customized language files may still be instructing
 * the user to type it.
 *
 * @param {String} typed text entered by the user
 * @return {Boolean} true if the deletion is confirmed
 */
og.isDeleteConfirmationKeyword = function(typed) {
	if (!typed) return false;
	typed = typed.replace(/^\s+|\s+$/g, '').toUpperCase();
	return typed == og.getDeleteConfirmationKeyword().toUpperCase() || typed == 'DELETE';
}

og.doDeleteMember = function(gen, delete_url) {
	var delMessage = $("#"+gen+"_keyword").val();
	var trashObjects = $("#"+gen+"_trash_objects_in_member").val();

	if (!og.isDeleteConfirmationKeyword(delMessage)) {
		// Keep the modal open and report the mismatch: silently closing it made the
		// confirm button look like it did nothing at all.
		$("#"+gen+"_keyword_error").show();
		$("#"+gen+"_keyword").focus();
		return;
	}

	if (trashObjects && parseInt(trashObjects) > 0) {
		delete_url += "&trash_objects_in_member=1";
		og.preferences.trash_objects_in_member_after_delete = 1;
	} else {
		og.preferences.trash_objects_in_member_after_delete = 0;
	}
	og.openLink(delete_url);
	$('#_close_link').click();
	og.ExtModal.hide();
}

og.deleteMember = function(delete_url, ot_name){
	var gen = Ext.id();
	var html = '<div class="modal-container">'+
				'<div style="display: inline-block;">'+
					'<div class="desc" style="margin-top:5px;">'+ lang('confirm delete permanently this member', ot_name) +'</div>'+
				'</div>'+
				'<div style="margin: 10px 10px 0 0;">'+
					'<label>'+ lang('confirm delete with keyword', og.getDeleteConfirmationKeyword()) +'</label>'+
					'<input type="text" name="keyword" id="'+ gen +'_keyword" style="width:100%;" onkeyup="$(\'#'+ gen +'_keyword_error\').hide();">'+
					'<div class="errorMessage" id="'+ gen +'_keyword_error" style="display:none;">'+ lang('delete confirmation keyword mismatch', og.getDeleteConfirmationKeyword()) +'</div>'+
				'</div><div class="clear"></div>'+

				'<div style="margin: 10px 10px 0 0;">'+
					'<label>'+ lang('trash objects in member', ot_name) +'</label>'+
					'<select name="trash_objects_in_member" id="'+ gen +'_trash_objects_in_member">'+
						'<option value="1">'+lang('yes')+'</option>'+
						'<option value="0">'+lang('no')+'</option>'+
					'</select>'+
					'<div class="desc">'+lang('trash objects in member desc', ot_name)+'</div>'+
				'</div><div class="clear"></div>'+
				
				'<div style="float:right;">'+
					'<button class="submit blue" onclick="og.doDeleteMember(\''+gen+'\',\''+delete_url+'\')">'+ lang('delete') +'</button>&nbsp;'+
					'<button class="submit" onclick="og.ExtModal.hide();">'+ lang('cancel') +'</button>'+
				'</div><div class="clear"></div></div>';

	og.ExtModal.show({
		html: html,
		title: lang('delete')+' '+ot_name,
		width: 400
	});
	// focus in the input field
	setTimeout(function() {
		$("#" + gen + "_keyword").focus();
		$("#" + gen + "_trash_objects_in_member").val(og.preferences.trash_objects_in_member_after_delete);
	}, 10);
}


og.renderContactDataFields = function(genid, value) {
	$(".contact-data-container").hide();
	$("#"+genid+"-contact-data-"+value).show(300);
	$("#"+genid+"existing_contact_combo_container").hide();

	var company_ot = null;
	var contact_ot = null;
	for (x in og.objectTypes) {
		if (og.objectTypes[x].name == 'contact') contact_ot = x;
		else if (og.objectTypes[x].name == 'company') company_ot = x;
	}

	//contact tab
	if(value == contact_ot){
		$("#"+genid+"contact_data_tab").parent( ".contact-data-container" ).show();
		$("#"+genid+"contact_additional_data_tab").parent( ".contact-data-container" ).show();
		$("#"+genid+"add_contact_custom_properties_div").show();
	}

	//company tab
	if(value == company_ot){
		$("#"+genid+"company_data_tab").parent( ".contact-data-container" ).show();
		$("#"+genid+"add_contact_custom_properties_div").show();
	}

	if(value == 0){
		$("#"+genid+"existing_contact_combo_container").show();
	}
}

/* for address custom properties and contact form inputs */
og.renderAddressTypeSelector = function(id, name, container_id, selected_value) {

	if (og.config.show_type_sel_on_address_field) {
		var select = $('<select name="'+name+'" id="'+id+'" class="address_type_input"></select>');
		for (var i=0; i<og.address_types.length; i++) {
			var type = og.address_types[i];
			var option = $('<option></option>');
			option.attr('value', type.id);
			if (selected_value == type.id) option.attr('selected', 'selected');
			option.text(type.name);
			select.append(option);
		}
		$('#'+container_id).empty().append(select);
	} else {
		$('#'+container_id).empty().append('<input type="hidden" name="'+name+'" id="'+id+'" value="'+selected_value+'" />');
	}
	
}


og.renderAddressInput = function(id, name, container_id, sel_type, sel_data) {
	if (!sel_data) sel_data = {};
	if (!sel_data.id) sel_data.id = 0;
	if (!sel_data.street) sel_data.street = '';
	if (!sel_data.city) sel_data.city = '';
	if (!sel_data.state) sel_data.state = '';
	if (!sel_data.zip_code) sel_data.zip_code = '';
	if (!sel_data.country) sel_data.country = og.config['default_country_address'] ? og.config['default_country_address'] : '';
	
	$('#'+container_id).append('<input type="hidden" name="'+name+'[id]" id="'+id+'_id" value="'+sel_data.id+'" />');
	$('#'+container_id).append('<input type="hidden" name="'+name+'[deleted]" id="'+id+'_deleted" value="0" />');

	$('#'+container_id).append('<span id="'+id+'_type" style="vertical-align:top;"></span>');
	og.renderAddressTypeSelector(id+'_type', name+'[type]', id+'_type', sel_type);

	if (!og.config.show_type_sel_on_address_field) {
		$('#'+container_id).addClass('no-type-selector');
	}

	var address_placeholder_str = navigator.appVersion.indexOf("MSIE") != -1 ? '' : ('placeholder="' + lang('street address') + '"');

	var address_placeholder_str = navigator.appVersion.indexOf("MSIE") != -1 ? '' : ('placeholder="'+lang('street address')+'"');
	var address_input = $('<textarea name="'+name+'[street]" id="'+id+'_street" class="address_street_input" '+address_placeholder_str+'>'+ sel_data.street +'</textarea>');
	$('#'+container_id).append(address_input);

	var city_input = $('<input name="'+name+'[city]" id="'+id+'_city" value="'+sel_data.city+'" class="address_city_input" placeholder="'+lang('city')+'"/>');
	$('#'+container_id).append(city_input);

	var state_input = $('<input name="'+name+'[state]" id="'+id+'_state" value="'+sel_data.state+'" class="address_state_input" placeholder="'+lang('state')+'"/>');
	$('#'+container_id).append(state_input);

	var zip_code_input = $('<input name="'+name+'[zip_code]" id="'+id+'_zip_code" value="'+sel_data.zip_code+'" class="address_zipcode_input" placeholder="'+lang('zip_code')+'"/>');
	$('#'+container_id).append(zip_code_input);

	var select_country = $('<select name="'+name+'[country]" id="'+id+'_country" class="address_country_input country-selector"></select>');
	$('#'+container_id).append(select_country);
	$('#template_select_country option').clone().appendTo('#'+id+'_country');
	if (sel_data.country != '') { 
		var selc = document.getElementById(id+'_country');
		if (selc && selc.options.length) {
			for (var i=0; i<selc.options.length; i++) {
				if (selc.options[i].value == sel_data.country) {
					selc.options[i].setAttribute('selected','selected');
				}
			}
		}
	}

	var delete_or_undo = $(`<div class="removeUndo addressRemoveUndo">
		<a href="#" tabindex="-1" onclick="og.markAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-delete delete-link" title="${lang('delete')}"></a>
		<a href="#" tabindex="-1" onclick="og.undoMarkAsDeleted(this, \'${container_id}\', \'${id}\');" class="coViewAction ico-undo undo-delete" style="display:none;" title="${lang('undo')}"></a>
	</div>`);
	$('#' + container_id).append(delete_or_undo);

	if (og.loggedUser.localization) {
		$('#'+container_id).addClass(og.loggedUser.localization);
	}

	$('#'+container_id).append('<div class="clear"></div>');

}
/* end address input */




og.onAssociatedMemberTypeRemove = function (genid, dimension_id, hf_id){
	var el = document.getElementById(genid + hf_id);
	if (el) el.value = [];
}

og.onAssociatedMemberTypeSelect = function (genid, dimension_id, member_id, hf_id, dont_reload_other_trees){
	member_selector.remove_all_selections(genid);
	if (!member_id) {
		// remove member
		var el = document.getElementById(genid + hf_id);
		if (el) el.value = [];
		return;
	}

	member_selector.add_relation(dimension_id, genid, member_id, false, dont_reload_other_trees);
	document.getElementById(genid + hf_id).value = "["+member_id+"]";
	
	// dont select associated members of an associated member
	if (member_selector[genid].hiddenFieldName.indexOf("associated_members[") == -1) {
		og.selectDefaultAssociatedMembers(genid, dimension_id, member_id);
	}
}


og.onAssociatedMemberTypeSelectMultiple = function (genid, dimension_id, member_id, hf_id, dont_reload_other_trees){

	member_selector.add_relation(dimension_id, genid, member_id, true, dont_reload_other_trees);
	document.getElementById(genid + hf_id).value = Ext.util.JSON.encode(member_selector[genid].sel_context[dimension_id]);

	// dont select associated members of an associated member
	if (member_selector[genid].hiddenFieldName.indexOf("associated_members[") == -1) {
		og.selectDefaultAssociatedMembers(genid, dimension_id, member_id);
	}
}

og.onAssociatedMemberTypeRemoveMultiple = function (genid, dimension_id, hf_id){
	document.getElementById(genid + hf_id).value = Ext.util.JSON.encode(member_selector[genid].sel_context[dimension_id]);
}




/**
 * Resets all tree filters in left panel
 */
og.clickRootNodeAndCallNext = function(dimId, currentCall) {
	// switch on the flag to prevent multiple loading
	og.resettingAllTrees = true;

	// clean active members for this dimension in contextManager
	og.contextManager.cleanActiveMembers(dimId);

	// click this root node if not already selected
	var members = og.contextManager.dimensionMembers[dimId];
	if (members.length > 0) {
		var tree =  Ext.getCmp("dimension-panel-"+dimId);
		og.memberTreeExternalClick(tree.dimensionCode, tree.getRootNode().id);
	}

	currentCall++;

	var total = 0;
	for (x in og.contextManager.dimensionMembers) {
		total++;
	}

	// Call next tree root node
	if (currentCall < total) {
		var i = 0;
		for (dimId in og.contextManager.dimensionMembers) {
			if (i < currentCall) {
				i++;
			} else {
				og.clickRootNodeAndCallNext(dimId, currentCall);
				break;
			}
		}
	} else {

		// reload all panels
		var all_tabs = Ext.getCmp('tabs-panel');
		for (i in all_tabs.items.items) {
			var tab = all_tabs.items.items[i];
			// update panel url if necessary
			if (tab && tab.content && tab.content.type == 'html' && tab.content.url) {
				tab.content.url = tab.content.url.replace("context=", "ignored=") + "&context=" + og.contextManager.plainContext();
			}
			// reset panel
			if (tab && typeof(tab.reset) == 'function') {
				tab.reset();
			}
		}

		// switch off the flag
		og.resettingAllTrees = false;
	}
};

og.uploadNewRevision = function(file_id, quickId) {
	og.openLink(og.getUrl('files', 'quick_add_files', {genid: quickId, object_id: file_id, new_rev_file_id: file_id}), {
		preventPanelLoad: true,
		onSuccess: function(data) {
			og.ExtendedDialog.show({
        		html: data.current.data,
        		height: 300,
        		width: 600,
        		title: lang('upload new revision'),
        		ok_fn: function() {
    				og.doFileUpload(quickId, {
    					callback: function() {
    						form = document.getElementById(quickId + 'quickaddfile');
    						og.ajaxSubmit(form);
    					}
    				});
            		og.ExtendedDialog.hide();
    			}
        	});
        	return;
		}
	});
};

/**
 * Dimension member tree: preserve scroll position when loading more nodes ("view more").
 * ExtJS may reset scroll or use a different scroll container after TreeLoader runs; we capture
 * scroll state before the request and restore it after render (sync + rAF + delayed correction
 * only when scroll drifts).
 */
og.addViewMoreNode = function(pnode, tree_id, callback) {

	var tree = Ext.getCmp(tree_id);

	var view_more_node = tree.loader.createNode({
		id: 'view_more_' + pnode.id,
		text: og.clean(lang('view more') + " ..."),
		iconCls: 'ico-view-more',
		cls: 'view-more-node',
		parent: pnode.id,
		object_id: -1,
		options: {},
		object_controller: '',
		object_type_id: 0,
		allow_childs: false,
		leaf: true
	});
	if (view_more_node) {
		view_more_node.on('click', function(node, e){
			if (e && typeof e.stopEvent == 'function') {
				e.stopEvent();
			}

			this.remove();

			if (typeof callback == 'function') {
				callback.call(null, tree, pnode);
			}

			return false;
		});

		var old_view_more = tree.getNodeById('view_more_' + pnode.id);
		if (!old_view_more) {
			if (pnode) pnode.appendChild(view_more_node);
		}

		// remove anchor from this node to prevent hiding the list after clicking the anchor, leave only the "view more" text
		if (view_more_node.ui && view_more_node.ui.anchor && view_more_node.ui.anchor.parentNode) {
			view_more_node.ui.anchor.parentNode.innerHTML = " " + og.clean(lang('view more') + " ...");
		}
	}
}

og.ajaxMemberTreeViewMoreCallbackRoot = function(tree) {
	if (!tree.last_offset) tree.last_offset = 0;

	if (og.config.member_selector_page_size) {
		tree.limit = og.config.member_selector_page_size;
		tree.last_offset = tree.last_offset + og.config.member_selector_page_size;
	} else {
		tree.limit = 100;
	}

	og.initialMemberTreeAjaxLoad(tree, tree.limit, tree.last_offset);

	// ensure tree is visible
	var tree_id = tree.id;
	setTimeout(function() {
		if ($("#" + tree.body.id).css('display') == 'none') {
			var t = Ext.getCmp(tree_id);
			$("#" + t.body.id).show();
		}
	}, 100);
}

og.ajaxMemberTreeViewMoreCallback = function(tree, pnode) {
	if (!tree.last_offset) tree.last_offset = 0;

	if (og.config.member_selector_page_size) {
		tree.limit = og.config.member_selector_page_size;
		tree.last_offset = tree.last_offset + og.config.member_selector_page_size;
	} else {
		tree.limit = 100;
	}

	og.memberTreeAjaxLoad(tree, pnode, tree.limit, tree.last_offset, {
		ignore_context_filters: '',
		context: og.contextManager.plainContext()
	});

	// ensure tree is visible
	var tree_id = tree.id;
	setTimeout(function() {
		if ($("#" + tree.body.id).css('display') == 'none') {
			var t = Ext.getCmp(tree_id);
			$("#" + t.body.id).show();
		}
	}, 100);
}

og.getTreeScrollCandidates = function(tree) {
	var out = [];
	var seen = {};
	function push(el) {
		if (!el || !el.nodeType) return;
		var key = el.tagName + ":" + (el.id || "") + ":" + (el.className || "");
		if (seen[key]) return;
		seen[key] = true;
		out.push(el);
	}

	if (!tree) return out;
	if (tree.body && tree.body.dom) push(tree.body.dom);
	if (tree.innerCt && tree.innerCt.dom) push(tree.innerCt.dom);
	if (tree.el && tree.el.dom) push(tree.el.dom);
	if (typeof tree.getTreeEl == 'function') {
		var treeEl = tree.getTreeEl();
		if (treeEl && treeEl.dom) push(treeEl.dom);
	}

	// Add nearby ancestors because actual scroll container can vary by Ext layout.
	for (var i = 0; i < out.length; i++) {
		var p = out[i] ? out[i].parentNode : null;
		var depth = 0;
		while (p && depth < 6) {
			push(p);
			p = p.parentNode;
			depth++;
		}
	}

	return out;
}

/**
 * Scroll container that actually moved (max scrollTop among candidates).
 * Restoring only this element avoids jitter from fighting multiple parents.
 */
og.captureTreeScrollState = function(tree) {
	if (!tree) return null;
	var els = og.getTreeScrollCandidates(tree);
	var bestEl = null;
	var bestTop = -1;
	for (var i = 0; i < els.length; i++) {
		var el = els[i];
		if (typeof el.scrollTop != 'number') continue;
		if (el.scrollTop > bestTop) {
			bestTop = el.scrollTop;
			bestEl = el;
		}
	}
	if (bestEl) {
		return { el: bestEl, top: bestTop, elId: bestEl.id || null };
	}
	if (tree.body && tree.body.dom) {
		var bd = tree.body.dom;
		return { el: bd, top: bd.scrollTop, elId: bd.id || null };
	}
	return null;
};

og.captureTreeScrollTop = function(tree) {
	var s = og.captureTreeScrollState(tree);
	return s ? s.top : null;
};

/**
 * After TreeLoader injects nodes, the DOM node that scrolls can be a sibling of the
 * previously captured element — restoring only one ref often leaves scroll_top at 0.
 * We set primary first, then every scrollable candidate; early frames avoid visible jump
 * to top while loading.
 */
og.restoreTreeScrollState = function(state, tree) {
	if (!state || typeof state.top != 'number') return;
	var targetTop = state.top;
	function resolveEl() {
		if (state.el && state.el.parentNode) return state.el;
		if (state.elId && document.getElementById(state.elId)) return document.getElementById(state.elId);
		if (tree && tree.body && tree.body.dom) return tree.body.dom;
		return null;
	}
	function applyScrollableCandidates() {
		if (!tree) return;
		var els = og.getTreeScrollCandidates(tree);
		for (var i = 0; i < els.length; i++) {
			var el = els[i];
			if (typeof el.scrollTop != 'number') continue;
			if (el.scrollHeight > el.clientHeight) {
				el.scrollTop = targetTop;
			}
		}
	}
	/** Full restore right after load (DOM may still be settling). */
	function applyFull() {
		var el = resolveEl();
		if (el) el.scrollTop = targetTop;
		applyScrollableCandidates();
	}
	/** Lighter touch for late corrections — less parent/child fighting. */
	function applyLight() {
		var el = resolveEl();
		if (el) el.scrollTop = targetTop;
		if (tree && tree.body && tree.body.dom) {
			var b = tree.body.dom;
			if (b.scrollHeight > b.clientHeight) b.scrollTop = targetTop;
		}
		if (tree && tree.innerCt && tree.innerCt.dom) {
			var ic = tree.innerCt.dom;
			if (ic.scrollHeight > ic.clientHeight) ic.scrollTop = targetTop;
		}
	}
	var driftPx = 8;
	function needsCorrection() {
		var cur = og.captureTreeScrollTop(tree);
		if (cur === null || cur === undefined) return true;
		return Math.abs(cur - targetTop) > driftPx;
	}
	applyFull();
	if (window.requestAnimationFrame) {
		requestAnimationFrame(function() {
			applyFull();
			requestAnimationFrame(applyFull);
		});
	}
	setTimeout(applyFull, 0);
	setTimeout(applyFull, 16);
	// Late timers: only if layout reset scroll (~0.5–1s). Re-applying when already
	// correct caused the visible "nudge upward" twitch.
	var marks = [50, 100, 200, 600, 950, 1100];
	for (var i = 0; i < marks.length; i++) {
		(function(delay) {
			setTimeout(function() {
				if (needsCorrection()) {
					applyLight();
				}
			}, delay);
		})(marks[i]);
	}
};

og.treeLoaderViewMoreCallback = function(tree, pnode) {
	var offset = !isNaN(tree.loader.baseParams.offset) ? tree.loader.baseParams.offset : 0;
	offset += og.config.member_selector_page_size;
	var prevState = og.captureTreeScrollState(tree);

	tree.loader.clearOnLoad = false;
	tree.loader.baseParams['offset'] = offset;
	tree.loader.baseParams['limit'] = og.config.member_selector_page_size;
	tree.loader.baseParams['tree_id'] = tree.id;

	tree.loader.load(pnode, function() {
		og.restoreTreeScrollState(prevState, tree);
	});
}

og.initialMemberTreeAjaxLoad = function(tree, limit, offset, add_params) {
	var tree_id = tree.id;
	var requested_offset = (!isNaN(offset) ? parseInt(offset, 10) : 0);
	var is_paginated_load = requested_offset > 0;
	
	var parameters = {
		dimension_id: tree.dimensionId,
		tree_id: tree_id
	};
	if (add_params) {
		for (key in add_params) {
			parameters[key] = add_params[key];
		}
	}

	if (limit && !isNaN(limit)) {
		parameters.limit = limit;
	} else {
		parameters.limit = og.config.member_selector_page_size;
	}
	if (offset && !isNaN(offset)) {
		parameters.offset = offset;
	}

	og.openLink(og.getUrl('dimension', 'initial_list_dimension_members_tree_root', parameters), {
		hideLoading:true,
		hideErrors:true,
		callback: function(success, data){
			
			if (!data) return;

			var dimension_tree = Ext.getCmp(data.tree_id);

			//add nodes to tree
			dimension_tree.addMembersToTree(data.dimension_members,data.dimension_id);

			dimension_tree.innerCt.unmask();

			//filter the tree
			if (!is_paginated_load) {
				dimension_tree.suspendEvents();
				dimension_tree.expandAll();
				dimension_tree.resumeEvents();
			}
			dimension_tree.render();

			var is_filtered = data.list_was_filtered_by && data.list_was_filtered_by.length > 0;

			if(typeof(data.dimensions_root_members) != "undefined" && !data.more_nodes_left && !is_filtered){
				ogMemberCache.addDimToDimRootMembers(data.dimension_id);
			}

			if (data.more_nodes_left) {
				og.addViewMoreNode(dimension_tree.getRootNode(), tree_id, og.ajaxMemberTreeViewMoreCallbackRoot);
			} else {
				var old_view_more_node = dimension_tree.getNodeById('view_more_' + dimension_tree.getRootNode().id);
				if (old_view_more_node) old_view_more_node.remove();
			}

			if (!is_paginated_load) {
				dimension_tree.initialized = true;
				if (typeof dimension_tree.onInitialLoadComplete === 'function') {
					dimension_tree.onInitialLoadComplete();
				}
			}

			if (og.after_member_tree_initial_load_functions) {
				fn_params = {add_params:add_params, data:data};
				for (var x=0; x<og.after_member_tree_initial_load_functions.length; x++) {
					var fn = og.after_member_tree_initial_load_functions[x];
					if (typeof(fn) == 'function') {
						fn.call(null, fn_params);
					}
				}
			}
			og.eventManager.fireEvent('end_callback member_tree loaded', {});
		}
	});
}

og.memberTreeAjaxLoad = function(tree, pnode, limit, offset, add_params) {
	var tree_id = tree.id;
	var requested_offset = (!isNaN(offset) ? parseInt(offset, 10) : 0);
	var is_paginated_load = requested_offset > 0;
	var prevState = og.captureTreeScrollState(tree);
	var parameters = {
		dimension_id: tree.dimensionId,
		ignore_context_filters: true,
		member: pnode.id,
		tree_id: tree_id
	};

	if (limit && !isNaN(limit)) {
		parameters.limit = limit;
	} else {
		parameters.limit = og.config.member_selector_page_size;
	}
	if (offset && !isNaN(offset)) {
		parameters.offset = offset;
	}

	if (add_params) {
		for (key in add_params) {
			parameters[key] = add_params[key];
		}
	}

	if (pnode.ownerTree.initialConfig.get_childs_params) {
		for (p_name in pnode.ownerTree.initialConfig.get_childs_params) {
			if (typeof(pnode.ownerTree.initialConfig.get_childs_params[p_name]) == 'function') continue;
			parameters[p_name] = pnode.ownerTree.initialConfig.get_childs_params[p_name];
		}
	}

	og.openLink(og.getUrl('dimension', 'get_member_childs', parameters), {
		hideLoading:true,
		hideErrors:true,
		callback: function(success, data){

			var dimension_tree = Ext.getCmp(data.tree_id);

			//add nodes to tree
			dimension_tree.addMembersToTree(data.members,data.dimension_id);

			dimension_tree.innerCt.unmask();

			//filter the tree
			if (!is_paginated_load) {
				dimension_tree.suspendEvents();
				dimension_tree.expandAll();
				dimension_tree.resumeEvents();
			}
			dimension_tree.render();
			og.restoreTreeScrollState(prevState, dimension_tree);

			if(typeof(data.dimensions_root_members) != "undefined" && !data.more_nodes_left){
				ogMemberCache.addDimToDimRootMembers(data.dimension_id);
			}

			if (data.more_nodes_left) {
				og.addViewMoreNode(pnode, tree_id, og.ajaxMemberTreeViewMoreCallback);
			} else {
				var old_view_more_node = dimension_tree.getNodeById('view_more_' + pnode.id);
				if (old_view_more_node) old_view_more_node.remove();
			}

			dimension_tree.initialized = true;
		}
	});
}

/**
 * Return the extra filters for the tree as a JSON string
 * @param tree {og.MemberTreeAjax} The tree to get the filters from
 * @return {String} The extra filters as a JSON string
 */
og.getMemberTreeExtraFilters = function(tree) {
	if (tree.initialConfig.extra_options && tree.initialConfig.extra_options.filters && tree.initialConfig.extra_options.filters.length > 0) {
		// Get the filters
		let filters = tree.initialConfig.extra_options.filters;
		// Find more information to add to the filters
		for (var i = 0; i < filters.length; i++) {
			if (filters[i].property_id == 'child_of' || filters[i].property_id == 'chained_association') {
				let assoc_id = filters[i].value;

				let sel_mem_ids = $('#'+ tree.id).closest('form').find('input[name="associated_members\\['+ assoc_id +'\\]"]').val();
				if (sel_mem_ids) {
					sel_mem_ids = JSON.parse(sel_mem_ids);
					if (sel_mem_ids.length > 0) {
						filters[i].selected_value = sel_mem_ids;
					} else {
						delete filters[i].selected_value;
					}
				} else {
					delete filters[i].selected_value;
				}
			}
		}

		// Return the filters as a JSON string
		return JSON.stringify(filters);
	} else {
		return null;
	}
}

og.reloadCurrentPanel = function() {
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	if (currentPanel) {
		currentPanel.reload();
	}
}


og.addTableCustomPropertyRow = function(parent, focus, values, col_count, ti, cpid, is_member_cp) {

	var field_name = is_member_cp ? 'member_custom_properties' : 'object_custom_properties';

	if (og.table_cps_last_row_id[cpid]) {
		var count = og.table_cps_last_row_id[cpid];
	} else {
		var count = $(parent).find("table tbody tr").length;
	}
	og.table_cps_last_row_id[cpid] = count + 1;

	var tbody = parent.getElementsByTagName("tbody")[0];
	var tr = document.createElement("tr");
	ti = ti + col_count * count;
	var cell_w = (600 / col_count) + 'px';
	for (row = 0; row < col_count; row++) {
		var td = document.createElement("td");
		var row_val = values && values[row] ? values[row] : "";
		td.innerHTML = '<input class="value" style="width:'+cell_w+';min-width:105px;" type="text" name="'+field_name+'[' + cpid + '][' + count + '][' + row + ']" value="' + row_val + '" tabindex=' + ti + '>';
		if (td.children && row == 0) var input = td.children[0];
		tr.appendChild(td);
		ti += 1;
	}
	tbody.appendChild(tr);
	var td = document.createElement("td");
	td.innerHTML = '<div class="ico ico-delete" style="width: 20px;height: 20px;cursor: pointer;margin-left: 2px;margin-top: 1px;" onclick="og.removeTableCustomPropertyRow(this.parentNode.parentNode);return false;">&nbsp;</div>';
	tr.appendChild(td);
	tbody.appendChild(tr);
	if (input && focus)
		input.focus();
}

og.selectDefaultAssociatedMembers = function(genid, dimension_id, member_id, current_associated_members) {


	var action_name = 'get_all_default_associated_members';
	if (current_associated_members) {
		action_name = 'get_all_associated_members';
	}

	og.openLink(og.getUrl('dimension', action_name, {member_id: member_id, dimension_id: dimension_id, genid: genid}), {
		hideLoading: true,
		callback: function(success, response_data) {
			if (!response_data) return;

			var d_associations = [];
			for (var ot in og.dimension_member_associations[response_data.dimension_id]) {
				for (var j=0; j<og.dimension_member_associations[response_data.dimension_id][ot].length; j++) {
					d_associations.push(og.dimension_member_associations[response_data.dimension_id][ot][j]);
				}
			}

			let processed_dimension_ids = [];

			for (var i=0; i<d_associations.length; i++) {
				var assoc = d_associations[i];
	  
				if (assoc && assoc.allows_default_selection) {

					// don't override the same dimension with anohter association (e.g: don't override client with billing client)
					if (processed_dimension_ids.indexOf(assoc.assoc_dimension_id) >= 0) continue;
					processed_dimension_ids.push(assoc.assoc_dimension_id);
					
					var data = response_data.associations_data[assoc.id];
					if (!data) continue;

					var hf = Ext.get(data.genid + member_selector[data.genid].hiddenFieldName);
			
					if (!hf.dom.form) {
						var dep_genid = data.genid;
					} else {
						
						var form_id = hf.dom.form.id;
						if (!form_id) return;
						
						var dep_genid = "";
						var selector_inputs = $("#" + form_id + ' .dimension-panel-textfilter');
						for (var x=0; x<selector_inputs.length; x++) {
							var sel_id = selector_inputs[x].id;
							var key = "-member-chooser-panel-"+ data.dimension_id +"-tree-textfilter";
							if (sel_id.indexOf(key) >= 0) {
								dep_genid = member_selector.get_genid_from_tree_textfilter_id(sel_id, data.dimension_id);
								break;
							}
						}
						if (dep_genid == '') dep_genid = data.genid;
					}

					var assoc_selector = member_selector[dep_genid];
					if (!assoc_selector) {
						dep_genid = dep_genid + '-' + data.dimension_id;
						assoc_selector = member_selector[dep_genid];
					}
		
					if (assoc_selector && assoc_selector.properties[data.dimension_id]) {
						// add the relations
						for (var z=0; z<data.member_ids.length; z++) {

							// if already selected then do nothing
							var hf_input = document.getElementById(dep_genid + assoc_selector.hiddenFieldName);
							var json_sel_ids = Ext.util.JSON.decode(hf_input.value);
							if (json_sel_ids.indexOf(parseInt(data.member_ids[z])) != -1) {
								continue;
							}
							
							// remove old selection in this dimension
							member_selector.remove_all_dimension_selections(dep_genid, data.dimension_id);
							
							if (og.dimensions && og.dimensions[data.dimension_id] && og.dimensions[data.dimension_id][data.member_ids[z]]) {
								// if member data already in cache -> use it and select the member
								member_selector.add_relation(data.dimension_id, dep_genid, data.member_ids[z], true, true);
							} else {
								// go to server and get the member data and then select the member
								var tmp_data = {member_id: data.member_ids[z], dimension_id: data.dimension_id, genid:dep_genid};
								og.getMemberFromServer(data.member_ids[z], og.member_selector_add_relation, tmp_data);
							}
						}
						// hide emtpy text
						if (data.member_ids.length > 0) {
							if (!assoc_selector.properties[data.dimension_id].isMultiple) {
								$("#"+dep_genid+"-member-chooser-panel-"+data.dimension_id+"-tree-current-selected .empty-text").hide();
							}
						}

					}
				}
			}
		}
	});
}

og.member_selector_add_relation = function(ignored1, ignored2, data) {
	member_selector.add_relation(data.dimension_id, data.genid, data.member_id);
}


og.get_dimension_member_association_by_id = function (dim_assoc_id) {
	for (dim in og.dimension_member_associations) {
		var assocs_by_ot = og.dimension_member_associations[dim];
		for (ot in assocs_by_ot) {
			if (isNaN(ot)) continue;
			for (i=0; i<assocs_by_ot[ot].length; i++) {
				var a = assocs_by_ot[ot][i];
				if (!a.is_reverse && a.id == dim_assoc_id) {
					return a;
				}
			}
		}
	}
	return null;
}


/**
 * Returns true if dimension_id is the id of an associated dimension (e.g.: client types)
 */
og.is_associated_dimension = function(dimension_id) {
	var dim_ot_assoc_data = og.dimension_member_associations[dimension_id];
	for (ot_id in dim_ot_assoc_data) {
		var associations = dim_ot_assoc_data[ot_id];
		for (var i=0; i<associations.length; i++) {
			var assoc = associations[i];
			if (assoc && assoc.is_reverse) {
				return true;
			}
		}
	}
	return false;
}

og.gridBooleanColumnRenderer = function(value, p, r) {
	if (r.data.id == 'quick_add_row') return value;
	if (r.data.id == '__total_row__' || r.data.object_id <= 0) return '';

	return value ? lang('yes') : lang('no');
}

og.gridObjectNameRenderer = function(value, p, r) {
	if (r.data.id == 'quick_add_row') {
		return value;
	}
	if (r.data.id == '__total_row__' || r.data.object_id <= 0) return '<span id="__total_row__">'+value+'</span>';

	var controller = r.data.type_controller ? r.data.type_controller : r.store.baseParams.url_controller;

	var onclick = "og.openLink(og.getUrl('"+ controller +"', 'view', {id: "+ r.data.object_id +"})); return false;";
	return String.format('<a href="#" onclick="{1}" title="{2}" style="font-size:120%;"><span class="bold">{0}</span></a>', og.clean(value), onclick, og.clean(value));
}

og.gridObjectNameRendererWithLink = function(value, p, r) {
	if (r.data.id == 'quick_add_row') {
		return value;
	}
	if (r.data.id == '__total_row__' || r.data.object_id <= 0) return '<span id="__total_row__">'+value+'</span>';

	var controller = r.data.type_controller ? r.data.type_controller : r.store.baseParams.url_controller;

	var object_link = og.getUrl(controller, 'view', {id: r.data.object_id});
	var onclick = "og.openLink(og.getUrl('"+ controller +"', 'view', {id: "+ r.data.object_id +"})); return false;";
	
	return String.format('<a href="{2}" onclick="{1}" title="{0}" style="font-size:120%;"><span class="bold">{0}</span></a>', og.clean(value), onclick, object_link);
}

og.gridPictureRenderer = function(value, p, r) {
	if (r.data.picture) {
		var picture_url = r.data.picture;
		return String.format('<div class="picture-file-small"><img src="{0}" alt="{1}" /></div>', picture_url, og.clean(r.data.name));
	} else {
		var classes = "db-ico ico-unknown ico-" + (r.data.type ? r.data.type : 'contact');
		return String.format('<div class="{0}" title="{1}" style="margin-left: 5px;"/>', classes, lang(r.data.type));
	}

}

og.getDateToolbarFilterComponent = function (config) {
    
	var genid = config.genid ? config.genid : '';

    var action_btn = new Ext.Action({
        //id: uid +config.name,
        id: genid + config.name,
        hidden:config.hidden,
        text: config.value ? config.value : lang('select a date'),
        value: config.value ? config.value : '',
        tooltip: config.tooltip ? config.tooltip : config.text,
        menu: new og.drawDateMenuPicker({
            id: config.name,
            items: [new Ext.menu.Item({
                    //id: uid + config.name + '_remove',
            		id: genid + config.name + '_remove',
                    text: lang('remove filter'),
                    iconCls: 'ico-delete',
                    hidden: true,
                    handler: function () {
                        var man = Ext.getCmp(config.grid_id);
						if(man.filters) {							
							man.filters[config.name].value = '';							                           
						} 
						
						man[config.name] = ''
						                       
                        man.load({
							start: 0 // start from the first page
						});
                        this.hide();
                        Ext.getCmp(genid + config.name).setText(lang('select a date'));
                        //Ext.getCmp(uid + config.name).setText(lang('select a date'));
                    }
                })],
            listeners: {
                'select': function (dp, date) {					
                    var man = Ext.getCmp(config.grid_id);
                    //Ext.getCmp(uid + config.name).setText(date.format(og.preferences['date_format']));
                    Ext.getCmp(genid + config.name).setText(date.format(og.preferences['date_format']));      										
					
					if(man.filters){
						man.filters[dp.id].value = date.format(og.preferences['date_format']);
					}
											
					man[dp.id] = date.format(og.preferences['date_format']);
															
                    man.load({
						start: 0 // start from the first page
					});
                    Ext.getCmp(genid + config.name + '_remove').show();
                    //Ext.getCmp(uid + config.name + '_remove').show();
                }
            }
        })
    });
    
    
    if (config.value)
        Ext.getCmp(config.name + '_remove').show();

    return action_btn;
}

og.getListToolbarFilterComponent = function(config) {
	
	var genid = config.genid ? config.genid : '';

	var combo = new Ext.form.ComboBox({
    	id: genid + config.name,
    	store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : config.options
	    }),
	    displayField: 'text',
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus: true,
        width: config.width ? config.width : 160,
        valueField: 'value',
        valueNotFoundText: '',
        grid_id: config.grid_id,
        listeners: {
        	'select' : function(combo, record) {
                    var man = Ext.getCmp(combo.grid_id);
                    man.filters[config.name].value = combo.getValue();
                    if (man.store_params) man.store_params[config.name] = combo.getValue();
                    man.load({
						start: 0 // start from the first page
					});
        	},
        }
	});

	if (typeof(config.initial_val) != 'undefined') {
		combo.setValue(config.initial_val);
	}

	return combo;
}
og.getListToolbarFilterComponentWithEvents = function(config) {

	var genid = config.genid ? config.genid : '';

	var combo = new Ext.form.ComboBox({
            id: genid + config.name,
            store: new Ext.data.SimpleStore({
                    fields: ['value', 'text'],
                    data : config.options
                }),
            displayField: 'text',
            hideLabel:false,
            mode: 'local',
            triggerAction: 'all',
            selectOnFocus: true,
            width: config.width ? config.width : 160,
            valueField: 'value',
            valueNotFoundText: '',
            listeners: {			
                'select': function (combo, record) {
                    var man = Ext.getCmp(config.grid_id);                    
					
					if(man.filters) {
						man.filters[config.name].value = combo.getValue();
					}
											
					man[config.name] = combo.getValue();
					
                    if (combo.id == genid + 'period_filter') {
                        if (record.data.value == 6) {							
                            Ext.getCmp(genid + 'label_from_filter').show();
                            Ext.getCmp(genid + 'from_filter').show();
                            Ext.getCmp(genid + 'label_to_filter').show();
                            Ext.getCmp(genid + 'to_filter').show();

							man.from_filter = Ext.getCmp(genid + 'from_filter').text;
							man.to_filter = Ext.getCmp(genid + 'to_filter').text;							
							
							if(Ext.getCmp(genid + 'label_period_filter')) {
								Ext.getCmp(genid + 'label_period_filter').hide();
							}							
                        } else {
                            Ext.getCmp(genid + 'label_from_filter').hide();
                            Ext.getCmp(genid + 'from_filter').hide();
                            Ext.getCmp(genid + 'label_to_filter').hide();
                            Ext.getCmp(genid + 'to_filter').hide();

							man.from_filter = '';
							man.to_filter = '';

							if(Ext.getCmp(genid + 'label_period_filter')) {
								Ext.getCmp(genid + 'label_period_filter').show();
							}			
                        }
                    }
                    man.load({
						start: 0 // start from the first page
					});
                }
            },
            'initComponent': function () {

            },
	});
        
	if (typeof(config.initial_val) != 'undefined') {
		combo.setValue(config.initial_val);
	}
        
       var test = new Ext.FormPanel({
            items: [combo]
        });

	return combo;
}


og.buildToolbarFilterAction = function(filter_name, filter_data, grid_id) {	
	var items = [];
	if (filter_name == 'text_filter') {
		items.push(filter_data);

	} else if (filter_data.type == 'date') {
		var btn_config = {name: filter_name, grid_id:grid_id};
		btn_config = Ext.apply(btn_config, filter_data);						
		var btn = og.getDateToolbarFilterComponent(btn_config);
		if (btn) {
			if (filter_data.label) items.push(filter_data.label);
			items.push(btn);
		}

	} else if (filter_data.type == 'list') {
		var combo_config = {name: filter_name, grid_id:grid_id};
		combo_config = Ext.apply(combo_config, filter_data);
		var combo = og.getListToolbarFilterComponent(combo_config);
		if (combo) {
			if (filter_data.label) items.push(filter_data.label);
			items.push(combo);
		}
	} else if (filter_data.type == 'period') {
		var combo_config = {name: filter_name, grid_id:grid_id};
		combo_config = Ext.apply(combo_config, filter_data);
		var combo = og.getListToolbarFilterComponentWithEvents(combo_config);
		if (combo) {			
			if (filter_data.label) items.push(filter_data.label);
			items.push(combo);
		}
	}


	return items;
}


og.onTzSelectorCountryChange = function(country_combo, timezones_id) {

	og.openLink(og.getUrl('account', 'get_country_timezones', {code: $(country_combo).val()}), {
		preventPanelLoad: true,
		callback: function(success, data) {

			if (data.timezones) {
				var combo = document.getElementById(timezones_id);
				while (combo.options.length > 0) combo.remove(0);

				for (var id in data.timezones) {
					var name = data.timezones[id];
					var option = document.createElement("option");
					option.text = name;
					option.value = id;

					combo.add(option);
				}
			}
		}
	});
}

og.showHiddenTimezoneSelector = function(genid) {
	$('#'+genid+'tz_text').hide();
	$('#'+genid+'tz_selector').show();
	$('#'+genid+'tz_edited').val(1);
	$('#'+genid+'tz_edit_link').hide();
}

og.ConfirmBoxDefaultCancelFn = function(close_btn_id) {
	$('#'+close_btn_id).click();
	return false;
}

og.ConfirmBoxDefaultAcceptFn = function(close_btn_id) {
	$('#'+close_btn_id).click();
	return true;
}

og.ConfirmBox = function(data) {

	var default_cancel_fn = 'og.ConfirmBoxDefaultCancelFn("'+data.genid+'_confirmbox_close_link");';
	var cancel_fn = default_cancel_fn;
	if (typeof(data.cancel_fn) !== 'undefined'){
		cancel_fn = data.cancel_fn + default_cancel_fn;
	}

	var default_accept_fn = 'og.ConfirmBoxDefaultAcceptFn("'+data.genid+'_confirmbox_close_link");';
	var accept_fn = accept_fn;
	if (typeof(data.accept_fn) !== 'undefined'){
		accept_fn = data.accept_fn + default_accept_fn;
	}

	//template data
	var data = {
		genid: data.genid,
		text: data.text,
		cancel_fn: cancel_fn,
		accept_fn: accept_fn
	}

	//get template
	var source = $("#confirm-dialog-box").html();
	//compile the template
	var template = Handlebars.compile(source);

	//instantiate the template
	var html = template(data);

	var modal_params = {
		'escClose': true,
		'minWidth' : 500,
		'minHeight' : 400,
		'overlayClose': true,
		'closeHTML': '<a id="'+data.genid+'_confirmbox_close_link" style="display: none;" class="modal-close modal-close-img"></a>'
	};

	$.modal(html,modal_params);
}


og.replaceStringAccents = function(str) {
	var rExps=[
		{re:/[\xC0-\xC6]/g, ch:'A'},
		{re:/[\xE0-\xE6]/g, ch:'a'},
		{re:/[\xC8-\xCB]/g, ch:'E'},
		{re:/[\xE8-\xEB]/g, ch:'e'},
		{re:/[\xCC-\xCF]/g, ch:'I'},
		{re:/[\xEC-\xEF]/g, ch:'i'},
		{re:/[\xD2-\xD6]/g, ch:'O'},
		{re:/[\xF2-\xF6]/g, ch:'o'},
		{re:/[\xD9-\xDC]/g, ch:'U'},
		{re:/[\xF9-\xFC]/g, ch:'u'},
		{re:/[\xD1]/g, ch:'N'},
		{re:/[\xF1]/g, ch:'n'}
	];

	for(var i=0, len=rExps.length; i<len; i++) {
		str=str.replace(rExps[i].re, rExps[i].ch);
	}

	return str;
}





og.openPDFOptions = function(genid) {
	var html = $("#pdfOptions").html();
	html = html.replace(/{gen_id}/g, genid);

	var modal_params = {
		'escClose': true,
		'overlayClose': true,
		'minWidth' : 400,
		'minHeight' : 200,
		'closeHTML': '<a id="pdf_options_close_link" class="modal-close modal-close-img"></a>'
	};

	$.modal(html, modal_params);
}

og.submit_pdf_form = function(genid) {

	var params = og.get_report_parameters_of_form(genid);
	params['exportPDF'] = true;
	params['pdfPageLayout'] = $("#"+genid+"pdfPageLayout").val();
	params['pdfPageSize'] = $("#"+genid+"pdfPageSize").val();

	var report_id = $("#report_id_"+genid).val();
	og.openLink(og.getUrl('reporting', 'export_custom_report_pdf', {id: report_id}), {
		post: params,
		callback: function(success, data) {
		  if (data.filename) {
			var $form = $("<form></form>");
			$form.attr("action", og.getUrl('reporting', 'download_file'));
			$form.attr("method", "post");
			$form.append('<input type="text" name="file_name" value="'+data.filename+'" />');
			$form.append('<input type="text" name="file_type" value="application/pdf" />');
			if (data.size) {
				$form.append('<input type="text" name="file_size" value="'+data.size+'" />');
			}

			$form.appendTo('body').submit().remove();
		  }
		}
	});
	$.modal.close();
	return false;
}

og.get_report_parameters_of_form = function(genid) {
	var form_id = 'form' + genid;
	var form = document.getElementById(form_id);

	var post_id = 'post' + genid;
	var post_el = document.getElementById(post_id);
	var post = Ext.util.JSON.decode(post_el.value);

	var params = {};
	for (x in post) {

		if(x == 'params'){
			params["report_params"] = Ext.util.JSON.encode(post[x]);
		}else if (x != 'c' && x != 'a' && x != 'ajax') {
			// array-valued fields (e.g. the task time report's selected 'columns') need the
			// '[]' suffix so the server receives them back as an array instead of collapsing
			// the repeated field down to just its last value
			params[Ext.isArray(post[x]) ? x + '[]' : x] = post[x];

			var i = document.createElement("input");
			i.type= "hidden";
			i.value = post[x];
			i.name = x;
			form.appendChild(i);
		}
	}

	// Prefer the current sort state from the form (matches what the user sees on screen)
	if (form && form.order_by) {
		params['order_by'] = form.order_by.value;
	}
	if (form && form.order_by_asc) {
		params['order_by_asc'] = form.order_by_asc.value;
	}

	// Always pin the report id from the visible form; never reuse a stale id from cached post
	var report_id_el = document.getElementById('report_id_' + genid);
	if (report_id_el && report_id_el.value) {
		delete params.id;
		delete params.report_id;
		params.id = report_id_el.value;
	}

	og.reports.fillDisabledParams(genid, params);

	return params;
}


og.submit_csv_form = function(genid, elem) {

	var params = og.get_report_parameters_of_form(genid);
	params['exportCSV'] = true;

	var report_id = $("#report_id_"+genid).val();
	og.openLink(og.getUrl('reporting', 'export_custom_report_csv', {id: report_id}), {
		post: params,
		callback: function(success, data) {
			var $form = $("<form></form>");
			$form.attr("action", og.getUrl('reporting', 'download_file'));
			$form.attr("method", "post");
			$form.append('<input type="text" name="file_name" value="'+data.filename+'" />');
			$form.append('<input type="text" name="file_type" value="application/csv" />');

			$form.appendTo('body').submit().remove();
		}
	});

	og.disable_export_report_link(elem);

	return false;
}

og.submit_fixed_report_csv_form = function(genid, elem) {
	var form_id = 'form' + genid;
	var form = document.getElementById(form_id);

	var params = og.get_report_parameters_of_form(genid);
	params['exportCSV'] = true;

	og.openLink(form.action, {
		post: params,
		callback: function(success, data) {
			var $form = $("<form></form>");
			$form.attr("action", og.getUrl('reporting', 'download_file'));
			$form.attr("method", "post");
			$form.append('<input type="text" name="file_name" value="'+data.filename+'" />');
			$form.append('<input type="text" name="file_type" value="application/csv" />');

			$form.appendTo('body').submit().remove();
		}
	});

	og.disable_export_report_link(elem);

	return false;
}



/******* object grid quick add row functions *******/


og.add_object_grid_quick_add_row = function(grid, config) {

	if (typeof(config.quick_add_row_fn) == 'function') {
		config.quick_add_row_fn.call(null, grid, config)
	}
}

og.on_quick_add_row_input_click = function(input, event) {
	$(input).focus();
}

og.quick_add_row_combo_input = function(config) {
	try {

		var uid = Ext.id();
		var combo_config = {
			renderTo: config.renderTo,
			id: config.id,
			genid: config.genid,
			name: config.name,
			store: new Ext.data.SimpleStore({
				fields: ['value', 'text'],
				data : config.options
			}),
			displayField: 'text',
			mode: 'local',
			triggerAction: 'all',
			selectOnFocus: true,
			width: config.width ? config.width : 160,
			valueField: 'value',
			valueNotFoundText: '',
			tabIndex: config.tabIndex
		};
		if (config.listeners) {
			combo_config.listeners = config.listeners;
		}
		var combo = new Ext.form.ComboBox(combo_config);
	
		if (typeof(config.initial_val) != 'undefined') {
			combo.setValue(config.initial_val);
		}
	
		return combo;

	} catch (e) {
		console.log(e);
	}
}

og.quick_add_row_date_input = function(config) {
	try {
		var picker = new og.DateField(Ext.apply(config, {
			width: 100,
			emptyText: og.preferences.date_format_tip
		}));
		return picker;
		
	} catch (e) {
		console.log(e);
	}
}

og.quick_add_row_time_input = function(config) {
	try {
		var picker = new Ext.form.TimeField(Ext.apply(config, {
			width: 80,
			format: og.config.time_format_use_24 ? 'G:i' : 'g:i A',
			emptyText: 'hh:mm'
		}));
		return picker;
		
	} catch (e) {
		console.log(e);
	}
}

og.quick_add_row_worked_time_input = function(config) {
	var html = '<table class="qa-worked-time-table"><tr><td class="qa-hours-cell">';
	var onkeydown = config.onkeydown ? 'onkeydown="'+config.onkeydown+'"' : '';
	html += '<input type="number" name="timeslot[hours]" tabindex="'+config.tabindex+'" id="'+config.id+'" '+onkeydown+' style="width:40px;" value="0"/> hs.';
	html += '</td><td class="qa-minutes-cell">';
	html += '<select name="timeslot[minutes]" tabindex="'+(config.tabindex + 1)+'"/>';
	for (var i=0; i<60; i++) {
		html += '<option value="'+ i +'">'+ i +'</option>';
	}
	html += '</select> mins.';
	html += '</td></tr></table>';

	return html;
}

og.quick_add_row_column_tabindex = function(grid, column) {
	var tabindex = 0;
	for (var i=0; i<grid.colModel.config.length; i++) {
		var col_conf = grid.colModel.config[i];
		if (col_conf.id == column) {
			tabindex = 100 + (i * 2);
			break;
		}
	}
	return tabindex;
}


/**
 * This function renders the member selector for each member visible in the time module quick-add row
 * It receives the context
 * @param dim_id It's the dimension that will be rendered
 * @param genid is the unique id of the input component
 * @param hf_name is the name of the hidden field, which is the one sent back to the backend on the submit request
 * @param sel_mem_ids
 * @param is_multiple
 * @param select_current_context
 * 
 */
og.quick_add_row_member_selector = function(dim_id, genid, hf_name, sel_mem_ids, is_multiple, select_current_context) {
	if (dim_id > 0) {
		if (!hf_name) hf_name = 'members_input_' + dim_id;
		if (!sel_mem_ids) sel_mem_ids = '';
		if (!is_multiple) is_multiple = 0;
		if (typeof(select_current_context) == 'undefined') select_current_context = true;
		
		var selector_config = {
				genid: genid,
				context: og.contextManager.plainContext(),
				dim_id: dim_id,
				is_multiple: is_multiple,
				hide_label: true,
				hf_name: hf_name,
				selected_member_ids: sel_mem_ids,
				select_current_context: select_current_context
		};

		og.openLink(og.getUrl('dimension', 'render_member_selector', selector_config), {
			preventPanelLoad: true,
			hideLoading: true,
			callback: function(success, data) {
				if (success) {
					var container_id = data.genid + 'members_' + data.dim_id;
					$("#" + container_id).html(data.current.data);
					
					// correct member list position that breaks if the gird was scrolled
					$("#" + data.genid+"-member-chooser-panel-"+data.dim_id+"-tree").click(function() {
						og.fix_quick_add_row_member_selector_list_position(data);
					});
					
				}
			}
		});
	}
}

og.fix_quick_add_row_member_selector_list_position = function(data) {

	var tree_el = Ext.get(data.genid+"-member-chooser-panel-"+data.dim_id+"-tree");
	var left = ($("#menu-panel-xsplit").position().left - Ext.get("menu-panel-xsplit").getOffsetsTo(tree_el)[0]);

	$("#"+data.genid+"-member-chooser-panel-"+data.dim_id+" .x-panel-body.collapsible-body").css('left', left + 'px');
}

/****************************************************/


/************** timeslots module quick add row ***************/


og.add_timeslot_module_quick_add_params = function(grid) {
	var params = {'dont_reload': true, 'members': '[]'};
	var add_row = grid.getView().getRow(0);
	var member_ids = [];

	$(add_row).find('input, select').each(function() {
		if ($(this).attr('name').indexOf('members_input_') == 0) {
			//var mem_ids = Ext.util.JSON.decode($(this).val());
			//member_ids = member_ids.concat(mem_ids);
		} else {
			params[$(this).attr('name')] = $(this).val();
		}
	});

	// Track which dimensions have selected members
	var dimensions_with_selection = {};

	for (did in og.simple_member_selectors[grid.genid]) {
		var mem_selector = og.simple_member_selectors[grid.genid][did];
		if (mem_selector) {
			var mem_id = mem_selector.getValue();
			if (mem_id > 0) {
				member_ids.push(mem_id);
				dimensions_with_selection[did] = true;
			}
		}
	}

	// Apply default dimension values for dimensions that don't have any selected members
	if (og.dimensions_info) {
		for (did in og.dimensions_info) {
			if (!isNaN(did) && !dimensions_with_selection[did]) {
				var dimension_info = og.dimensions_info[did];
				if (dimension_info && dimension_info.default_member && dimension_info.default_member > 0) {
					member_ids.push(dimension_info.default_member);
				}
			}
		}
	}
	if (member_ids.length > 0) {
		params['members'] = Ext.util.JSON.encode(member_ids);
	}

	var user_id = Ext.getCmp(grid.genid + 'add_ts_contact_id').getValue();
	params['timeslot[contact_id]'] = user_id;

	var task_id = Ext.getCmp(grid.genid + 'rel_object_id').getValue();
	params['object_id'] = task_id;

	params['req_channel'] = 'time list - quick add';

	return params;
}

/**
 * This functions clears the input fields for:
 *	 - Description
 *	 - Time
 * All other values are left as they were, because we assume that they will change much less.
 */
og.clean_timeslot_module_quick_add_params = function (grid) {
    var params = {'dont_reload': true, 'members': '[]'};
    var add_row = grid.getView().getRow(0);

    $(add_row).find('input, select').each(function () {
        if ($(this).attr('name') == 'timeslot[description]') {
            $(this).val('')
        }
        if ($(this).attr('name') == 'timeslot[start_time]' || $(this).attr('name') == 'timeslot[hours]' || $(this).attr('name') == 'timeslot[minutes]') {
            $(this).val('')
        }
    });
}

og.add_timeslot_module_quick_add_submit = function(grid_id, first_input_column) {
	var grid = Ext.getCmp(grid_id);
	// get timeslot params
	var form_params = og.add_timeslot_module_quick_add_params(grid);
        
	// submit timeslot
	og.openLink(og.getUrl('time','add'),{
		post: form_params,
		preventPanelLoad: true,
		callback: function(success, data) {
			if (success && data.timeslot) {
				data.timeslot.type = 'timeslot';
				if (data.timeslot.invoicing_status) {
					// set the is billable property to show
					data.timeslot.is_billable = data.timeslot.invoicing_status == 'pending' ? lang('yes') : lang('no');

					// add the status html to show
					if (!data.timeslot.invoicing_status_html) {
						let status_text = lang('invoicing_status ' + data.timeslot.invoicing_status);
						let status_class = 'invoicing-status-' + data.timeslot.invoicing_status;
						data.timeslot.invoicing_status_html = '<span class="additional-list-col"><span class="' + status_class + '">' + status_text + '</span></span>';
					}
				}

				// set the object id so the generic grid can use it in the toolbar actions
				data.timeslot.object_id = data.timeslot.id;

				// create the grid record to add to the grid's store
				var record = new Ext.data.Record(data.timeslot, data.timeslot.id);
                                
				og.clean_timeslot_module_quick_add_params(grid);
				
				// add new timeslot to the top of the grid
				grid.store.insert(1, record);

				og.eventManager.fireEvent('replace all empty breadcrumb', null);

				// focus in the first input
				if (first_input_column) {
					setTimeout(function() {
						$("#"+grid.genid+"add_ts_" + first_input_column).focus();
					}, 200);
				}
				
				// reload totals row if needed
				grid.reloadTotalsRow();
			}
		}
	});
}

og.add_timeslot_module_quick_add_enter = function(event, genid) {
	if (event.keyCode == 13) {
        $("#"+ genid +"ts_quick_add_btn").click();
	}
}

/**
 * Returns the user ID to pre-select in a quick-add row's user combo.
 * Uses the value from the given user-filter component when it belongs to the
 * same grid, falling back to the logged user otherwise.
 *
 * @param {Object} grid         - The ExtJS grid instance.
 * @param {string} filterCmpId  - The component ID of the user filter (e.g. "user_filter").
 * @returns {number} User ID to use as the initial value.
 */
og.get_quick_add_initial_user_id = function(grid, filterCmpId) {
	var user_filter_cmp = Ext.getCmp(filterCmpId);
	if (user_filter_cmp && user_filter_cmp.grid_id === grid.id && user_filter_cmp.getValue() > 0) {
		return user_filter_cmp.getValue();
	}
	return og.loggedUser.id;
};

og.add_timeslot_module_quick_add_row = function(grid, config) {

	var onclick = 'og.on_quick_add_row_input_click(this, event);';
	
	let labor_cat_dim_id = 0;
	for (did in og.dimensions_info) {
		if (!isNaN(did) && og.dimensions_info[did].code == "hour_types") {
			labor_cat_dim_id = did;
		}
	}
	var first_input_column = null;
	for (var i=0; i<grid.colModel.config.length; i++) {
		var col_conf = grid.colModel.config[i];
		if (col_conf.id == 'name' || col_conf.id == 'description' || col_conf.id == 'start_time' || col_conf.id == 'worked_time') {
			if (col_conf.id == 'name') {
				first_input_column = 'contact_id';
			} else {
				first_input_column = col_conf.id;
			}
			break;
		}
	}

	var record_config = {};
	record_config.type = 'add';
	record_config.description = '<input type="text" id="'+config.genid+'add_ts_description" name="timeslot[description]" value="" style="width:95%;" onmousedown="event.stopPropagation();"';
	record_config.description += 'onclick="'+onclick+'" tabindex="'+og.quick_add_row_column_tabindex(grid, 'description')+'" onkeydown="og.add_timeslot_module_quick_add_enter(event, \''+config.genid+'\')"/>';
	record_config.name = '<span id="'+config.genid+'usercombo"></span>';
	record_config.start_time = '<table class="qa-start-time-table"><tr><td class="qa-date-cell"><span id="'+config.genid+'start_date"></td><td class="qa-time-cell"><span id="'+config.genid+'start_time"></td></tr></table>';

	record_config.worked_time = og.quick_add_row_worked_time_input({
		id: config.genid + 'add_ts_worked_time',
		tabindex: og.quick_add_row_column_tabindex(grid, 'worked_time'),
		onkeydown: 'og.add_timeslot_module_quick_add_enter(event, \''+config.genid+'\')'
	});

	if (og.income && og.advanced_billing) {
		record_config.is_billable = '<span id="'+config.genid+'is_billable"></span>';
		record_config.is_billable += '<input type="hidden" id="'+ config.genid + 'add_ts_invoicing_status" name="timeslot[invoicing_status]" />';
	}

	// placeholder for the tasks selector
	record_config.rel_object_name = '<span id="'+config.genid+'rel_object_name"></span>';

	// submit button
	var quick_add_submit_fn = 'og.add_timeslot_module_quick_add_submit(\''+grid.id+'\', \''+first_input_column+'\'); return false;';
	var quick_add_btn_class = 'x-btn-text btn btn-sm btn-secondary';
	var ac_tindex = og.quick_add_row_column_tabindex(grid, 'actions');
	var quick_add_btn_blur = 'document.getElementById(\''+config.genid+'add_ts_'+first_input_column+'\').focus();';
	record_config.actions = '<button id="'+config.genid+'ts_quick_add_btn" class="'+quick_add_btn_class+'" onblur="'+quick_add_btn_blur+'" onclick="'+quick_add_submit_fn+'" tabindex="'+ac_tindex+'"><i class="icon-circle-plus"></i>'+lang('add')+'</button>';

	// dimension selector containers
	for (var i=0; i<grid.colModel.config.length; i++) {
		var col_conf = grid.colModel.config[i];
		if (col_conf.id.indexOf('dim_') == 0) {
			var dim_id = col_conf.id.substring(4);
			record_config[col_conf.id] = '<span id="'+ config.genid + 'members_' + dim_id +'"></span>';
		}
	}

	// insert row
	var record = new Ext.data.Record(record_config, 'quick_add_row');
	grid.store.insert(0, record);

	// remove checkbox and add background
	var add_row = grid.getView().getRow(0);
	$(add_row).find('.x-grid3-row-checker').removeClass('x-grid3-row-checker');
	$(add_row).addClass('quick-add-row');

	// render member selectors
	og.quick_add_row_dim_selectors = {};
	for (var i=0; i<grid.colModel.config.length; i++) {
		var col_conf = grid.colModel.config[i];
		if (col_conf.id.indexOf('dim_') == 0) {
			var dim_id = col_conf.id.substring(4);

			// after changing project or customer selector we must filter the tasks selector
			let is_client_or_project_dim = false;
			if ((og.projects && dim_id == og.projects.dimension_id) || (og.customers && dim_id == og.customers.dimension_id)) {
				is_client_or_project_dim = true;
			}

			// after changing exepnse category selector we must filter the product types selector
			var onselect = null;
			if (og.hour_types && dim_id == labor_cat_dim_id) {
				onselect = og.hour_types.after_quick_add_labor_cat_select;
			} else if (is_client_or_project_dim) {
				onselect = og.load_time_quick_add_row_tasks;
			}
		
			og.quick_add_row_dim_selectors[dim_id] = new og.SimpleMemberSelector({
				dimensionId: dim_id,
				genid: config.genid,
				renderTo: config.genid + 'members_' + dim_id,
				width: grid.colModel.getColumnWidth(i) - 10,
				select_current_context: true,
				tabIndex: og.quick_add_row_column_tabindex(grid, col_conf.id),
				onselect_fn: onselect
			});
		}
	}

	
	var user_combo_listeners = {};
	if (og.time_quick_add_user_combo_listeners) {
		for (x in og.time_quick_add_user_combo_listeners) {
			user_combo_listeners[x] = og.time_quick_add_user_combo_listeners[x];
		}
	}

	var quick_add_selected_user_id = og.get_quick_add_initial_user_id(grid, "user_filter");
	
	// user selector of the quick add
	var user_combo = og.quick_add_row_combo_input({
		id: config.genid + 'add_ts_contact_id',
		genid: config.genid,
		name: "timeslot[contact_id]",
		options: config.quick_add_row_user_options,
		initial_val: quick_add_selected_user_id,
		renderTo: config.genid + "usercombo",
		width: grid.colModel.getColumnById('name').width - 10,
		tabIndex: og.quick_add_row_column_tabindex(grid, 'name'),
		listeners: user_combo_listeners
	});
	// trigger selection listeners with the initial value
	if (user_combo.initialConfig.listeners && typeof(user_combo.initialConfig.listeners.select) == 'function') {
		user_combo.initialConfig.listeners.select.call(null, user_combo);
	}

	var start_time_tindex = og.quick_add_row_column_tabindex(grid, 'start_time');
	// start date selector
	var st_date_picker = og.quick_add_row_date_input({
		id: config.genid + 'add_ts_start_time',
		name: "timeslot[date]",
		renderTo: config.genid + "start_date",
		tabIndex: start_time_tindex,
		value: new Date()
	});
	st_date_picker.on('keydown', function(picker, event) {
		og.add_timeslot_module_quick_add_enter(event, config.genid);
	});

	// start time selector
	var st_time_picker = og.quick_add_row_time_input({
		id: config.genid + 'add_ts_start_time_min',
		genid: config.genid,
		name: "timeslot[start_time]",
		renderTo: config.genid + "start_time",
		tabIndex: start_time_tindex + 1
 	});

	if (og.income && og.advanced_billing) {
		let billable_combo_listeners = {
			'select': og.quick_add_row_time_on_is_billable_select
		}
		// is billable selector
		var billable_combo = og.quick_add_row_combo_input({
			id: config.genid + 'add_ts_is_billable',
			genid: config.genid,
			name: "timeslot[is_billable]",
			options: [['yes', lang('yes')], ['no', lang('no')]],
			initial_val: 'pending',
			renderTo: config.genid + "is_billable",
			width: grid.colModel.getColumnById('is_billable').width - 10,
			tabIndex: og.quick_add_row_column_tabindex(grid, 'is_billable'),
			listeners: billable_combo_listeners
		});
	}

	// Create the Task selector and render in its placeholder
	og.quick_add_row_task_combo = og.quick_add_row_combo_input({
		id: config.genid + 'rel_object_id',
		genid: config.genid,
		name: "timeslot_rel_object_id",
		options: [],
		width: grid.colModel.getColumnById('rel_object_name').width - 10,
		renderTo: config.genid + 'rel_object_name',
	});
	// Load the tasks
	og.load_time_quick_add_row_tasks();

	$("#"+ config.genid +"add_ts_start_time_min").keydown(function(event){
		var gid = $(this).attr('id').replace("add_ts_start_time_min", "");
		og.add_timeslot_module_quick_add_enter(event, gid);
	});
	$("#"+ config.genid +"add_ts_start_time").keydown(function(event){
		var gid = $(this).attr('id').replace("add_ts_start_time", "");
		og.add_timeslot_module_quick_add_enter(event, gid);
	});
	$("#"+ config.genid +"add_ts_contact_id").keydown(function(event){
		var gid = $(this).attr('id').replace("add_ts_contact_id", "");
		og.add_timeslot_module_quick_add_enter(event, gid);
	});

	// focus in the first input
	if (first_input_column && !config.dont_focus_in_first_input) {
		setTimeout(function() {
			$("#"+config.genid+"add_ts_" + first_input_column).focus();
		}, 200);
	}

	// when using tab if focus goes to a td then change it to the input inside it
	$("#" + grid.id + " .quick-add-row td").focus(function(){
		//$(this).find('input, textarea, select').first().focus();
	});

}

/**
 * If no project or client selected, then don't load the tasks
 * Otherwise, load all tasks in the context and populate the task selector
 * @return {undefined}
 */
og.load_time_quick_add_row_tasks = function () {

	if (!og.quick_add_row_task_combo) return;

	// id of the task selector
	let combo_id = og.quick_add_row_task_combo.id;

	// clone the context into another object
	let selected_context = structuredClone(og.contextManager.dimensionMembers);

	let sel_project = null;
	if (og.projects && og.quick_add_row_dim_selectors[og.projects.dimension_id]) {
		sel_project = og.quick_add_row_dim_selectors[og.projects.dimension_id].getValue();
	}
	let sel_client = null;
	if (og.customers && og.quick_add_row_dim_selectors[og.customers.dimension_id]) {
		sel_client = og.quick_add_row_dim_selectors[og.customers.dimension_id].getValue();
	}
	if (!sel_project && !sel_client) {
		og.quick_add_row_task_combo.store.loadData([[0, lang('please select a project or client filter')]]);
		og.quick_add_row_task_combo.setValue(0);
		$("#"+combo_id).addClass("desc");
		return;
	} else {
		if (sel_project) {
			selected_context[og.projects.dimension_id] = [0, sel_project];
		}
		if (sel_client) {
			selected_context[og.customers.dimension_id] = [0, sel_client];
		}
		$("#"+combo_id).removeClass("desc");
	}
	// ---
	
	og.openLink(og.getUrl('object', 'get_tasks_in_context', {context: JSON.stringify(selected_context)}), {
		callback: function(success, data) {
			
			if (!data || !data.tasks) return;

			og.quick_add_row_task_combo.store.loadData(og.buildNestedTaskStore(data.tasks));
			og.quick_add_row_task_combo.setValue(0);
		}
	});
}

/**
 * Builds a nested [value, text] store array from a flat tasks list, ordered by
 * parent-child relationships and indented by nesting depth.
 * Tasks whose parentId is absent from the list are treated as root tasks.
 * @param {Array} tasks - Array of task objects with `id`, `name`, and optional `parentId`
 * @return {Array} Store data array starting with a blank [[0, '--']] entry
 */
og.buildNestedTaskStore = function(tasks) {
	var task_store = [[0, '--']];
	var taskMap = {};
	var childrenMap = {0: []};
	for (var i = 0; i < tasks.length; i++) {
		taskMap[tasks[i].id] = tasks[i];
	}
	for (var i = 0; i < tasks.length; i++) {
		var t = tasks[i];
		var pid = (t.parentId && taskMap[t.parentId]) ? t.parentId : 0;
		if (!childrenMap[pid]) childrenMap[pid] = [];
		childrenMap[pid].push(t);
	}
	(function addTasksToStore(parentId, depth) {
		var children = childrenMap[parentId];
		if (!children) return;
		var indent = '';
		for (var d = 0; d < depth; d++) indent += '\u00a0\u00a0\u00a0';
		for (var j = 0; j < children.length; j++) {
			var child = children[j];
			task_store.push([child.id, indent + child.name]);
			addTasksToStore(child.id, depth + 1);
		}
	})(0, 0);
	return task_store;
};

og.quick_add_row_time_on_is_billable_select = function(combo, record, index) {
	let inv_status = combo.getValue() == 'yes' ? 'pending' : 'non_billable';
	$("#" + combo.genid + 'add_ts_invoicing_status').val(inv_status);
}

// listen to the scroll event of the grid contents, so we can fix the position 
// of the expanded member selectors lists
og.quick_add_row_event_to_fix_mem_selector_position = function(grid) {
	if (!grid.scroll_event_added) {

		grid.last_horizontal_scroll = $("#"+grid.id+" .x-grid3-scroller").scrollLeft();
		grid.scroll_event_added = true;

		$("#"+grid.id+" .x-grid3-scroller").scroll(function() {
	
			// use a timeout to try to execute this function after scroll ends, and not at every pixel move
			if (grid.fix_member_selector_position_timeout) {
				clearTimeout(grid.fix_member_selector_position_timeout);
			}
			grid.fix_member_selector_position_timeout = setTimeout(function() {
				var left_scroll_pos = $("#"+grid.id+" .x-grid3-scroller").scrollLeft();
				
				// if scroll is horizontal then fix the position of the expanded member selectors
				if (left_scroll_pos != grid.last_horizontal_scroll) {
					grid.last_horizontal_scroll = left_scroll_pos;
		
					$("#"+grid.id+" .x-panel-body.collapsible-body:visible").each(function(){
		
						var container_id = $(this).closest(".member-chooser-container").attr('id');
						var arr = container_id.split('-');
						var dim = arr[arr.length - 2];
		
						og.fix_quick_add_row_member_selector_list_position({genid: grid.genid, dim_id: dim});
					});
		
				} else {
					// if scroll is vertical then collapse the expanded member selectors
					$("#"+grid.id+" .dimension-panel-textfilter").blur();
				}
			}, 5);
		});
	}
}

/****************************************************/


og.prompt_delete_object = function(id, reload) {
	if (confirm(lang('confirm delete object'))) {
		og.openLink(og.getUrl('object', 'trash', {object_id:id, reload:reload}));
	}
}

og.render_default_grid_actions = function(value, p, r) {
	var actions = '';
	if (r.id == 'quick_add_row' || r.data.id == '__total_row__') return value;

	if (r.store && r.store.baseParams && r.store.baseParams.url_controller) {
		var controller = r.store.baseParams.url_controller;
		var action_edit = 'edit';
		var action_delete = 'delete';
		var obj_id = r.data.object_id ? r.data.object_id : r.data.id;

		actions += String.format(
			'<a class="list-action-icon edit" href="#" onclick="og.render_modal_form(\'\', {c:\''+controller+'\', a:\''+action_edit+'\', params:{id:'+obj_id+'}});" title="{0}"><i class="icon-pencil-line"></i></a>',
			lang('edit')
		);

		actions += String.format(
			'<a class="list-action-icon delete" href="#" onclick="og.prompt_delete_object('+obj_id+', 1);" title="{0}"><i class="icon-circle-x"></i></a>',
			lang('delete')
		);
	}

	return '<div>' + actions + '</div>';
}



/**
 * For date range config optoins, to show/hide the date pickers depending on the range type selected
 */
og.on_date_range_config_option_change = function(select, option_name) {
	if ($(select).val() == 'range') {
		$("."+option_name+".date-range-container").show();
	} else {
		$("."+option_name+".date-range-container").hide();
	}
}

/**
 *	Enable or disable confirmations to archive or delete items.
 */
og.confirmNorification = function(lang, status){

 	if(status)
 		return confirm(lang);
 	else
 		return true;

}



og.setPictureInfo = function(i, e) {
	$('#'+genid+'x').val(e.x1);
	$('#'+genid+'y').val(e.y1);
	$('#'+genid+'w').val(e.width);
	$('#'+genid+'h').val(e.height);
}

og.beforePictureSubmit = function() {
	$(".imgareaselect-selection").parent().remove();
	$(".imgareaselect-outer").remove();
}


og.tmpPictureFileUpload = function(genid, config) {
	var fileInput = document.getElementById(genid + 'uploadImage');
	var fileParent = fileInput.parentNode;
	fileParent.removeChild(fileInput);
	var form = document.createElement('form');
	form.method = 'post';
	form.enctype = 'multipart/form-data';
	form.encoding = 'multipart/form-data';
	form.action = og.getUrl('contact', 'tmp_picture_file_upload', {'id': genid});
	form.style.display = 'none';
	form.appendChild(fileInput);
	document.body.appendChild(form);

	og.submit(form, {
		callback: function(d) {
			form.removeChild(fileInput);
			fileParent.appendChild(fileInput);
			document.body.removeChild(form);
			if (typeof config.callback == 'function') {
				config.callback.call(config.scope, d);
			}
		}
	});
}

og.set_image_area_selection = function(genid, is_company) {
 	setTimeout(function() {
		var w = $('img#'+genid+'uploadPreview').width();
		var h = $('img#'+genid+'uploadPreview').height();
		// set 1:1 ratio for initial selection if is a contact
		if (!is_company) {
			var min = w > h ? h : w;
			var size = min < 200 ? min : 200;
			w = size;
			h = size;
		}

		og.area_sel.setSelection(0, 0, w, h, true);
		og.area_sel.setOptions({show: true});
		og.area_sel.update();
	}, 500);
}


og.formatAmount = function(id) {
	amount = $("#"+id).val();
	$("#"+id).val(og.format_money_amount(amount));
}

og.updatePictureFile = function (url) {
	og.openLink(url, {
		preventPanelLoad: true,
		callback: function(success, data) {
			og.ExtModal.show({
				html: data.current.data,
				title: lang('edit_picture')
			});

			var is_company = data.is_company;
			var genid = data.genid;

			var p = $("#"+genid+"uploadPreview");
			//p.focus();

			// implement imgAreaSelect plug in (http://odyniec.net/projects/imgareaselect/)
			if (!is_company) {
				og.area_sel = $('img#'+genid+'uploadPreview').imgAreaSelect({
					aspectRatio: '1:1',
					handles: true,
					instance: true,
					onSelectEnd: og.setPictureInfo
				});
			}

			// prepare instant preview
			$("#"+genid+"uploadImage").change(function(){
				// fadeOut or hide preview
				p.fadeOut();

				$("#"+genid+"current_picture").hide();
				
				$("#"+genid+"submit_btn").show(); // allow save after image is loaded

				// For browsers with HTML5 compatibility
				if (window.FileReader) {
					var fr = new FileReader();
					fr.readAsDataURL(document.getElementById(genid+"uploadImage").files[0]);

					fr.onload = function (fevent) {
				   		p.attr('src', fevent.target.result).fadeIn();
					};
					if (!is_company) {
						og.set_image_area_selection(genid);
					}
				} else {
					// For old browsers (IE 9 or older)
					og.tmpPictureFileUpload(genid, {
						callback: function(data) {
							$("#"+genid+"uploadPreview").attr('src', data.url).fadeIn();

							if (!is_company) {
								og.area_sel = $('img#'+genid+'uploadPreview').imgAreaSelect({
									aspectRatio: '1:1',
									handles: true,
									instance: true,
									type: Blob,
									onSelectEnd: og.setPictureInfo
								});
								og.set_image_area_selection(genid);
							}

						}
					});
				}
			});
		}
	});
}

og.delete_contact_picture = function(genid, delete_picture_url, confirm_message, reload_picture) {
	if (confirm(confirm_message)) {
		og.openLink(delete_picture_url, {
			preventPanelLoad: true,
			callback: function(success, data) {
				$("#"+genid+"current_picture").fadeOut();
				og.ExtModal.hide();
				$("#"+reload_picture).attr('src', data.default_image_url);
			}
		});
	}
};


// used to upload the file contents of a file input into a tmp folder in the server
og.upload_tmp_file_content = function(genid, file_input_id, file_input_name, hf_id, callback) {
	var fileInput = document.getElementById(file_input_id);

	var fileParent = fileInput.parentNode;
	fileParent.removeChild(fileInput);
	
	var form = document.createElement('form');
	form.method = 'post';
	form.enctype = 'multipart/form-data';
	form.encoding = 'multipart/form-data';
	form.action = og.getUrl('object', 'tmp_file_upload', {'id': hf_id, 'input_name': file_input_name});
	form.style.display = 'none';
	form.appendChild(fileInput);
	document.body.appendChild(form);
	
	og.submit(form, {
		callback: function(d) {
			$("button.submit").removeAttr("disabled").removeClass("disabled");
			
			form.removeChild(fileInput);
			fileParent.appendChild(fileInput);
			document.body.removeChild(form);
			
			// set the hidden input
			if (d && d.id) {
				$("#"+hf_id).val(d.id);
			} else {
				// if no file selected remove the previous value
				$("#"+hf_id).val("");
			}

			// allow to execute another function after uploading the file
			if (typeof callback == 'function') {
				callback(genid, file_input_id, hf_id);
			}
		}
	});
}

// drag & drop classification call

og.call_add_objects_to_member = function(e, ids, member_id, attachment, reclassify_in_associations, remove_prev, callback, dimension_id, remove_obj_task) {
	
	if (og.still_adding_objects_to_member) return;
	og.still_adding_objects_to_member = true;
	setTimeout(function(){ og.still_adding_objects_to_member = false; }, 1000);
	
	var params = {};
	// the ids of the objects to classify
	params.objects = Ext.util.JSON.encode(ids);

	if (isNaN(member_id) || member_id == null) {
		// when unclassifying we must specify in which dimension
		params.dimension = dimension_id;
	} else {
		// classify in this member
		params.member = member_id;
	}

	if (attachment) params.attachment = attachment;
	if (reclassify_in_associations) params.reclassify_in_associations = reclassify_in_associations;
	if (remove_prev) params.remove_prev = remove_prev;
	if (remove_obj_task) params.remove_obj_task = remove_obj_task;
	
	params.req_channel = 'drag and drop';
	
	og.openLink(og.getUrl('member', 'add_objects_to_member'),{
		method: 'POST',
		post: params,
		//post: {objects: Ext.util.JSON.encode(ids), member: e.target.id, attachment:attachment, reclassify_in_associations: true},
		callback: function(){
			if (e && e.data && e.data.grid) e.data.grid.load();
			if (typeof(callback) == 'function') {
				callback.call(null, ids, member_id);
			}
		}
	});
}

og.drag_drop_classification_keep_or_move_prompt = function (genid, e, ids, member_id, attachment, reclassify_in_associations, callback) {

	var div = document.createElement('div');
	var question = lang('do you want to mantain the current associations of this obj with members of', config.title);
	div.style = "border-radius: 5px; background-color: #fff; padding: 10px; width: 500px;";
	var genid = Ext.id();
	div.innerHTML = '<div><label class="coInputTitle">'+lang('classification')+'</label></div>'+
		'<div id="'+genid+'_question">'+ question+'</div>'+
		'<div id="'+genid+'_buttons">'+
		'<button class="replace submit blue">'+lang('replace with the new one')+'</button><button class="keep submit blue">'+lang('keep both current and new')+'</button>'+
		'</div><div class="clear"></div>';

	var modal_params = {
		'escClose': false,
		'overlayClose': false,
		'closeHTML': '<a id="'+genid+'_close_link" class="modal-close" title="'+lang('close')+'"></a>',
		'onShow': function (dialog) {
			$("#"+genid+"_close_link").addClass("modal-close-img");
			$("#"+genid+"_buttons").css('text-align', 'right').css('margin', '10px 0');
			$("#"+genid+"_question").css('margin', '10px 0');
			$("#"+genid+"_buttons button.replace").css('margin-right', '10px').click(function(){

				og.call_add_objects_to_member(e, ids, member_id, attachment, reclassify_in_associations, true, callback);
				$('.modal-close').click();
			});
			$("#"+genid+"_buttons button.keep").css('margin-right', '10px').click(function(){
				
				og.call_add_objects_to_member(e, ids, member_id, attachment, reclassify_in_associations, false, callback);
				$('.modal-close').click();
			});
		}
	};
	setTimeout(function() {
		$.modal(div, modal_params);
	}, 100);
}

// --


og.get_currency_by_id = function(id) {
	for (var i=0; i<og.all_currencies.length; i++) {
		if (og.all_currencies[i].id == id) return og.all_currencies[i];
	}
}


og.format_money_amount = function(amount, decimals) {
	if (amount=='' || typeof amount == 'undefined') amount = '0';
	amount = og.clean_thousand_separator(amount);

	// if decimal separator is , then replace it by .
	if (amount.toString().indexOf(',') > 0) {
		amount = amount.toString().replace(',','.');
	}

	// get the number
	amount = parseFloat(amount);

	if (isNaN(amount)) {
		amount = 0;
	}
	
	if (!decimals) {
		decimals = og.preferences.decimal_digits == '' ? 2 : og.preferences.decimal_digits;
	}

	// if not using any thousand separator then only put the correct decimals separator in the string
	if (og.preferences.thousand_separator == '') {
		return amount.toFixed(decimals).toString().replace('.', og.preferences.decimals_separator);
	}
	
	var locale = og.preferences.thousand_separator == ',' ? 'en' : 'it';
	
	return amount.toLocaleString(locale, {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
}

// renders an amount cell in extjs gird
og.render_grid_amount = function(value, p, r) {
	if (r.data.id == '__total_row__') return value;
	var currency = og.get_currency_by_id(r.data.currency_id);
	var sym = currency ? currency.symbol : '$';
	return '<div class="right">' + sym + ' ' + og.format_money_amount(value, 2) + '</div>';
}

og.updateElementMoneyAmount = function(id, value){
	var formatted_value = og.format_money_amount(value);
	$('#'+id).val(formatted_value);
}

og.clean_thousand_separator = function(amount) {
	if (!isNaN(amount)) return amount;
	if (typeof amount == 'undefined') return 0;
	
	var th_sep = og.get_locale_thousand_separator();
	if (th_sep == '') {
		// if not using any thousand separator then guess it using the decimal separator
		// so if the user inputs some thousands separator we can manage it
		th_sep = og.preferences.decimals_separator == ',' ? '.' : ',';
	}
	// remove thousand separator
	while (amount.toString().indexOf(th_sep) > 0) {
		amount = amount.toString().replace(th_sep, '');
	}
	
	if (th_sep == '.') {
		// set decimal separator to .
		amount = amount.toString().replace(',','.');
	}
	return amount;
}

og.get_locale_thousand_separator = function() {
	/*var num = 1000;
    var locale = og.preferences.thousand_separator == ',' ? 'en' : 'it';
	var numStr = num.toLocaleString(locale);
    if (numStr.length == 5) {
        return numStr.substr(1, 1);
    }*/
    return og.preferences.thousand_separator;
}


/**
 * Return array [year,month,day,hour,minute] 
 * @param {string} date
 * @param {string} time
 * @returns {Array|og.getDateArray.result}
 */
og.getDateArray = function (date, time) {
    var format = og.preferences.date_format;
    var result = [];
    switch (format) {
        case 'd-m-Y':
		case 'd-m-y':
		case 'j-n-Y':
		case 'j-n-y':
            var aux = date.split('-');
            result.push(aux[2]);
            result.push((aux[1] - 1));
            result.push(aux[0]);
            break;
        case 'd/m/Y':
		case 'd/m/y':
		case 'j/n/Y':
		case 'j/n/y':
            var aux = date.split('/');
            result.push(aux[2]);
            result.push((aux[1] - 1));
            result.push(aux[0]);
            break;
        case 'd.m.Y':
		case 'd.m.y':
		case 'j.n.Y':
		case 'j.n.y':
            var aux = date.split('.');
            result.push(aux[2]);
            result.push((aux[1] - 1));
            result.push(aux[0]);
            break;
        case 'm-d-Y':
		case 'm-d-y':
		case 'n-j-Y':
		case 'n-j-y':
            var aux = date.split('-');
            result.push(aux[2]);
            result.push((aux[0] - 1));
            result.push(aux[1]);
            break;
        case 'm/d/Y':
		case 'm/d/y':
		case 'n/j/Y':
		case 'n/j/y':
            var aux = date.split('/');
            result.push(aux[2]);
            result.push((aux[0] - 1));
            result.push(aux[1]);
            break;
        case 'm.d.Y':
		case 'm.d.y':
		case 'n.j.Y':
		case 'n.j.y':
            var aux = date.split('.');
            result.push(aux[2]);
            result.push((aux[0] - 1));
            result.push(aux[1]);
            break;
        case 'Y-m-d':
		case 'y-m-d':
		case 'Y-n-j':
		case 'y-n-j':
            var aux = date.split('-');
            result.push(aux[0]);
            result.push((aux[1] - 1));
            result.push(aux[2]);
            break;
        case 'Y/m/d':
		case 'y/m/d':
		case 'Y/n/j':
		case 'y/n/j':
            var aux = date.split('/');
            result.push(aux[0]);
            result.push((aux[1] - 1));
            result.push(aux[2]);
            break;
        case 'Y.m.d':
		case 'y.m.d':
		case 'Y.n.j':
		case 'y.n.j':
            var aux = date.split('.');
            result.push(aux[0]);
            result.push((aux[1] - 1));
            result.push(aux[2]);
            break;

        default:
            break;
    }
    
    //
    if (time == 'hh:mm') {
    	var now_time = new Date();
    	time = now_time.format('H:i');
    }
    var auxTime = time.split(' ');
    var times = auxTime[0].split(':');
    if (auxTime[1] == 'PM' && times[0] != 12) {
        result.push((parseInt(times[0]) + 12));
    } else {
        if (auxTime[1] == 'AM' && times[0] == 12) {
            result.push((parseInt(times[0]) + 12));
        }else{
            result.push(times[0]);
        }
    }
    result.push(times[1]);
    return result;
}


og.reload_tasks_list_with_status = function(status_id) {

	var url = og.getUrl('task', 'set_task_list_filters_to_reload', {status_id: status_id});
	og.openLink(url, {
		hideLoading:true,
		callback: function(success, data) {
			var t = Ext.getCmp("tasks-panel");
	        if (t) {
	        	var tp = Ext.getCmp('tabs-panel');
	        	tp.setActiveTab(t);
	        	t.reset();
	        }
		}
	});

}

og.get_object_type_by_name = function(ot_name) {
	for (x in og.objectTypes) {
		if (!isNaN(x)) {
			var ot = og.objectTypes[x];
			if (ot && ot.name == ot_name) {
				return ot;
			}
		}
	}
}

// render a member selector using standard html select box
// if og.dimensions does not have the data then queries the server for the data before trying to render the component
// also makes a queue of other components for the same dimension so we don't make the same request every time
og.render_plain_member_selector = function(config) {

	var dim_id = config.dim_id;

	if (!og.render_plain_member_selector_waiting_response) og.render_plain_member_selector_waiting_response = {};

	// if we are already waiting for server response to load the dimension members, then queue this selector to be rendered when data is loaded
	if (og.render_plain_member_selector_waiting_response[dim_id]) {
		og.render_plain_member_selector_waiting_response[dim_id].push(config);
		return;
	}

	// if we don't have the members data in og.dimensions variable -> query the server for the data and render the component after
	if (typeof og.dimensions[dim_id] == 'undefined') {

		// queue this selector config to be rendered after the server response is loaded
		og.render_plain_member_selector_waiting_response[dim_id] = [];
		og.render_plain_member_selector_waiting_response[dim_id].push(config);

		// query the server for members data
		og.openLink(og.getUrl('dimension', 'reload_dimensions_js', {dim_ids: [dim_id], vars: Ext.util.JSON.encode(config)}), {
			callback: function(success, data) {
				
				for (dim_id in data.dims) {

					// store the members data in og.dimensions variable
					let dim_members = data.dims[dim_id];
					if (dim_members.length > 0) {
						if (!og.dimensions) {
							og.dimensions = {};
						}
						if (!og.dimensions[dim_id]) {
							og.dimensions[dim_id] = {};
						}
						for (var i=0; i < dim_members.length; i++) {
							let member = dim_members[i];
							og.dimensions[dim_id][member.id] = member;
						}
					}

					// render all the queued components for this dimension
					for (var i=0; i < og.render_plain_member_selector_waiting_response[dim_id].length; i++) {
						// get the config of the component
						let conf = og.render_plain_member_selector_waiting_response[dim_id][i];

						// call the render function
						og.do_render_plain_member_selector(conf);
					}

					// reset the waiting for server variable
					delete og.render_plain_member_selector_waiting_response[dim_id];
				}
				
			}
		});

	} else {
		// dimension already in cache -> render component
		og.do_render_plain_member_selector(config);
	}

}

// function that renders the dimension selector component, og.dimensions must have the members data for the required dimension
og.do_render_plain_member_selector = function(config) {

	var dim_id = config.dim_id;
	var hf_name = config.hf_name;
	var container = config.container;
	var genid = config.genid;
	var sel_id = config.sel_id ? config.sel_id : 0;
	var class_add = config.class_add ? config.class_add : '';

	var selector_id = genid + 'member_selector_' + dim_id;

	// get the members
	var members = [];
	for (mem_id in og.dimensions[dim_id]) {
		members.push(og.dimensions[dim_id][mem_id]);
	}

	// sort the members
	members.sort(function (a, b){
		return ((a.sort_key < b.sort_key) ? -1 : ((a.sort_key > b.sort_key) ? 1 : 0));
	});

	// create the selector inside the container
	var sel = $('<select id="'+selector_id+'" name="'+ hf_name +'" class="'+class_add+'">');
	sel.append($('<option value="0">'));
	$("#" + container).html(sel);

	// add the members to the selector
	for (var j=0; j<members.length; j++) {
		var member = members[j];

		// indent the name depending on the depth
		var mem_name = "";
		for (var i=1; i<member.depth; i++) mem_name += '&nbsp;&nbsp;';
		mem_name += og.clean(member.name);

		// create the option element
		var option_id = "mem_" + member.id;
		var pid = member.parent == 0 ? member.id : member.parent;
		var sel_attr = sel_id == member.id ? 'selected="selected"' : "";
		var option_html = '<option id="'+option_id+'" class="parent_'+pid+'" value="'+member.id+'" '+sel_attr+'>'+mem_name+'</option>';

		if (member.parent == 0) {
			// append a root element
			sel.append(option_html);

		} else {
			// append an element below its parent
			parent_option_id = "mem_" + member.parent;
			$("#" + selector_id + " .parent_" + member.parent + ":last").after(option_html);

		}

	}

}


og.preload_small_dimension_members = function() {
	
	if (!og.simple_member_selectors) og.simple_member_selectors = {store_cache: {}};
	og.openLink(og.getUrl('dimension', 'preload_small_dimension_members'), {
		silent: true,
		callback: function(success, data) {
			if (data && data.dimension_members) {
				
				for (dim_id in data.dimension_members) {
					// format the store
					let store_data = [[0, lang('none')]];
					og.buildPlainStoreFromTree(data.dimension_members[dim_id], store_data);
	
					// store cache data for some time to avoid making so much queries to the server when redrawing quick add row
					og.simple_member_selectors.store_cache[dim_id] = {
						store: store_data,
						date: new Date(),
					}
				}
			}
		}
	});
}



/**
 * Opens the object picker and assigns the selected task to the selected objects.
 * @param {string} grid_id - The id of the grid where the selected objects are.
 * @param {string} controller - The name of the controller to send the request to.
 * @param {string} action - The action to perform on the controller.
 * @param {string} request_channel - The channel to use for the request.
 */
og.assign_task_to_objects = function(grid_id, controller, action, request_channel) {

	let cmp = Ext.getCmp(grid_id);
	// Get the ids of the selected objects
	let object_ids = cmp.getSelectedIds();

	og.select_task_and_make_reassign_request(object_ids, controller, action, request_channel);

}

/**
 * Opens a link to a specified controller with the given action and sends a POST request with the object_ids, task_id, and req_channel parameters.
 *
 * @param {string} object_id - The id of the object to be changed.
 * @param {string} controller - The name of the controller to send the request to.
 * @param {string} action - The action to perform on the controller.
 * @param {string} grid_id - The ID of the grid that contains the list.
 * @return {void} This function does not return anything.
 */
og.inline_change_object_task = function(object_id, controller, action, grid_id) {

	// open the object picker to select the new task
	og.select_task_and_make_reassign_request(object_id, controller, action, 'task link inline edit', grid_id);

	// remove the popover
	$('.task-link-popover').remove();
}

/**
 * Selects a task and makes a reassign request to the specified controller.
 *
 * @param {string} object_ids - The IDs of the objects to be reassigned.
 * @param {string} controller - The name of the controller to send the request to.
 * @param {string} action - The action to perform on the controller.
 * @param {string} request_channel - The channel of the request.
 * @param {string} grid_id - The ID of the grid that contains the list.
 * @return {void} This function does not return anything.
 */
og.select_task_and_make_reassign_request = function(object_ids, controller, action, request_channel, grid_id) {

	// Open the object picker and get the selected objects
	og.ObjectPicker.show(function(objs) {

		if (objs && objs.length > 0) {
			var obj = objs[0].data;

			// Check if the selected object is a task
			if (obj.type != 'task') {
				// If not, display an error message
				og.msg(lang("error"), lang("object type not supported"), 4, "err");

			} else {
				
				let task_id = obj.object_id;
				let inline_action = typeof grid_id !== 'undefined';
	
				// Send a request to the specified controller with the selected task and objects
				og.openLink(og.getUrl(controller, action), {
					post: {
						object_ids: object_ids,
						task_id: task_id,
						req_channel: request_channel,
						inline_action: inline_action
					},
					callback: function(success, data) {
						if (success && typeof grid_id !== 'undefined') {
							// redraw the task cell for the edited object
							if (data.object) {
								/** @TODO: replace this call for a function that only redraws the edited cell */
								og.reload_current_grid_page(data.object, grid_id);
							}
						}
					}
				});
	
			}
		}
	}, null, {
		types: ['task'],
		selected_type: 'task',
		ignore_context: false,
	});
}


/**
 * Sends a request to remove a task from an object.
 *
 * @param {string} object_id - The ID of the object.
 * @param {string} task_id - The ID of the task.
 * @param {string} grid_id - The ID of the grid that contains the list.
 * @return {void} This function does not return anything.
 */
og.inline_remove_task_from_object = function(object_id, task_id, grid_id) {

	// Send a request to the specified controller with the object_id and task_id
	og.openLink(og.getUrl('object', 'remove_task_from_object'), {
		post: {
			object_id: object_id,
			task_id: task_id,
			req_channel: 'task link inline remove'
		},
		callback: function(success, data) {
			if (success) {
				// redraw the task cell for the edited object
				if (data.object) {
					/** @TODO: replace this call for a function that only redraws the edited cell */
					og.reload_current_grid_page(data.object, grid_id);
				}
			}
		}
	});

	// remove the popover
	$('.task-link-popover').remove();

}

/**
 * Reloads the current grid page.
 *
 * @param {object} object - The object associated with the grid.
 * @param {string} grid_id - The ID of the grid to reload.
 * @return {void} This function does not return anything.
 */
og.reload_current_grid_page = function(object, grid_id) {
	let grid = Ext.getCmp(grid_id);
	if (grid) {
		grid.load(); // reloads current page
	}
}

	
/**
 * Initializes the popovers for task links in a grid.
 * 
 * @param {string} grid_id - The ID of the grid.
 */
og.init_task_link_popovers = function(grid_id) {

	// Initialize the popovers for task links in the grid.
	$("#" + grid_id + " .task-link").popover({
		
		// Define the content of the popover.
		content: function() {

			// Get the data attributes of the task link.
			let object_id = $(this).data("object-id");
			let task_id = $(this).data("task-id");
			let controller = $(this).data("controller");
			let action = $(this).data("action");

			// Define the click event handlers for the edit and delete links.
			let on_edit_click = String.format("og.inline_change_object_task('{0}', '{1}', '{2}', '{3}');", object_id, controller, action, grid_id);
			let on_delete_click = String.format("og.inline_remove_task_from_object('{0}', '{1}', '{2}');", object_id, task_id, grid_id);

			// Generate the HTML for the popover content.
			let html = '<div class="task-link-popover-actions">';
			html += '<a class="link-ico ico-edit" onclick="' + on_edit_click + '"></a>';
			html += '<a class="link-ico ico-delete" onclick="' + on_delete_click + '"></a>';
			html += '</div>';

			return html;
		},

		// Define the template for the popover.
		template: '<div class="popover task-link-popover"><div class="popover-body"><div class="popover-content"></div></div></div>',
		
		// Enable HTML content in the popover.
		html: true,
		
		// Define the delay for showing and hiding the popover.
		delay: { 
			show: '10',
			hide: '5000'
		},
		
		// Set the container for the popover.
		container: false, // to make the popover stick with the task link
		
		// Set the trigger for the popover.
		trigger: 'hover',
		
		// Set the placement of the popover.
		placement: 'right',
	});

	$("#" + grid_id + " .task-link").on('mouseover', function() {
		$('.task-link-popover').remove();
	});
}


/**
 * Returns a custom property object based on the given id and object type name.
 * The custom property object contains the id, name, type, description, default value, and other
 * properties of the custom property.
 * @param {number} cp_id The id of the custom property.
 * @param {string} object_type_name The name of the object type the custom property belongs to.
 * @return {object|null} The custom property object or null if no custom property is found.
 */
og.get_custom_property_by_id_and_type = function(cp_id, object_type_name) {
	let cp = null;
	// If the object type name is "client", change it to "customer" to match the property name in the
	// custom_properties_by_type object.
	if (object_type_name == 'client') object_type_name = 'customer';
	if (og.custom_properties_by_type && og.custom_properties_by_type[object_type_name]) {
		// Iterate through the custom properties of the given object type and find the one with the
		// given id.
		for (let i = 0; i < og.custom_properties_by_type[object_type_name].length; i++) {
			if (og.custom_properties_by_type[object_type_name][i].id == cp_id) {
				cp = og.custom_properties_by_type[object_type_name][i];
				break;
			}
		}
	}
	return cp;
}



/**
 * Returns an array of member IDs that are linked to the custom property with the given id
 * and object type name.
 * The linked members can be either associated members or classification members of a classification.
 * @param {string} genid - The GENID of the form.
 * @param {number} cp_id - The id of the custom property.
 * @param {string} linked_to - The name of the object type the custom property is linked to.
 * @return {array} An array of member IDs that are linked to the custom property.
 */
og.get_object_link_custom_property_linked_member_ids = function(genid, cp_id, linked_to) {
	let sel_mem_ids = [];
	if (linked_to.indexOf('assoc_') == 0) {
		let assoc_id = linked_to.replace('assoc_', '');
		sel_mem_ids = og.get_object_link_custom_property_linked_assoc_member_ids(genid, cp_id, assoc_id);		
	} else if (linked_to.indexOf('classification_') == 0) {
		let dim_id = linked_to.replace('classification_', '');
		sel_mem_ids = og.get_object_link_custom_property_linked_classification_member_ids(genid, cp_id, dim_id);	
	}

	return sel_mem_ids;
}


/**
 * Returns an array of member IDs that are linked to the custom property with the given id
 * and association id.
 * The linked members are associated members of the given association.
 * @param {string} genid - The GENID of the form.
 * @param {number} cp_id - The id of the custom property.
 * @param {number} assoc_id - The id of the association.
 * @return {array} An array of member IDs that are linked to the custom property.
 */
og.get_object_link_custom_property_linked_assoc_member_ids = function(genid, cp_id, assoc_id) {
	let sel_mem_ids = $('#'+ genid +'cp'+cp_id).closest('form').find('input[name="associated_members\\['+ assoc_id +'\\]"]').val();
	sel_mem_ids = JSON.parse(sel_mem_ids);

	return sel_mem_ids;	
};


/**
 * Returns an array of member IDs that are linked to the custom property with the given id
 * and dimension id.
 * The linked members are members of the given dimension.
 * @param {string} genid - The GENID of the form.
 * @param {number} cp_id - The id of the custom property.
 * @param {number} dim_id - The id of the dimension.
 * @return {array} An array of member IDs that are linked to the custom property.
 */
og.get_object_link_custom_property_linked_classification_member_ids = function(genid, cp_id, dim_id) {
	let sel_mem_ids = [];
	if ($('#'+ genid +'cp'+cp_id).closest('form').find('input[name="classfication\\['+ dim_id +'\\]"]').length == 0) {
		// when using old classfication selector (all members in the same input)
		sel_mem_ids = [];
		let form_genid = $('#'+ genid +'cp'+cp_id).closest('form').find('input[name="genid"]').val();
		if (member_selector[form_genid] && member_selector[form_genid].sel_context) {
			sel_mem_ids = member_selector[form_genid].sel_context[dim_id];
		}

	} else {
		// Get the selected values of the linked selector
		sel_mem_ids = $('#'+ genid +'cp'+cp_id).closest('form').find('input[name="classfication\\['+ dim_id +'\\]"]').val();
		sel_mem_ids = JSON.parse(sel_mem_ids);
	}
	return sel_mem_ids;
}

/**
 * Shows an object picker for selecting objects to link to a custom property, and calls
 * og.add_object_link_custom_property_value with the selected objects.
 * @param {string} genid - The ID of the generator.
 * @param {number} cp_id - The ID of the custom property.
 * @param {boolean} is_multiple - Whether the custom property is allowed to have multiple values.
 * @param {string} object_type - The name of the object type to filter by.
 * @param {string} linked_to - The name of the object type or classification type that the custom property is linked to.
 */
og.select_object_link_custom_property_value = function(genid, cp_id, is_multiple, object_type, linked_to) {
	// show object pickar and call og.add_object_link_custom_property_value

	let filter_types = [];
	if (object_type != '') {
		filter_types.push(object_type);
	}

	let member_ids = og.get_object_link_custom_property_linked_member_ids(genid, cp_id, linked_to);

	let object_picker_config = {
		genid: genid,
		cp_id: cp_id,
		is_multiple: is_multiple,
		ignore_context: true,
		hideFilters: true,
		sort: 'dateUpdated',
		dir: 'DESC',
		types: filter_types
	}
	if (object_type) {
		object_picker_config.selected_type = [object_type];
	}
	if (member_ids && member_ids.length > 0) {
		object_picker_config.extra_member_ids = JSON.stringify(member_ids);
	}

	og.ObjectPicker.show(function (objs) {
		if (objs && objs.length > 0) {
			for (var i=0; i < objs.length; i++) {
				var obj = objs[i].data;
				if (obj) {
					og.add_object_link_custom_property_value(this.genid, this.cp_id, this.is_multiple, obj);
				}
				if (!this.is_multiple) break;
			}
		}
	}, null, object_picker_config);
}

/**
 * Add the object id to the selected values array and update the hidden field value.
 * If not is multiple hide the select object link.
 * Append the selected object html.
 * 
 * @param {string} genid - the general id.
 * @param {string} cp_id - the custom property id.
 * @param {boolean} is_multiple - whether the custom property allows multiple values.
 * @param {object} object - the object to add.
 */
og.add_object_link_custom_property_value = function(genid, cp_id, is_multiple, object) {
	if (!object) return;

	let hf_id = genid+'cp'+cp_id;
	let selected_values_id = genid+'selected-values'+cp_id;

	let object_id = object.object_id;
	let object_name = object.name;
	let object_url = og.getUrl('object', 'view', {id:object_id});

	// add the object id to the selected values array
	let current_selected_object_ids = $("#"+hf_id).val();
	let current_selected_object_ids_array = JSON.parse(current_selected_object_ids);
	
	if (is_multiple && typeof current_selected_object_ids_array == 'object') {
		if (!current_selected_object_ids_array.includes(object_id)) {
			current_selected_object_ids_array.push(object_id);
		}
	} else {
		current_selected_object_ids_array = [object_id];
	}
	
	// update hidden field value
	$("#"+hf_id).val(JSON.stringify(current_selected_object_ids_array));

	// if not is multiple hide the select object link
	if (!is_multiple) {
		$("#"+genid+"cp-object-link-action"+cp_id).hide();
	}

	// append the selected object html
	let selected_value_id = genid+'selected-value'+cp_id+'-'+object_id;
	$("#"+selected_values_id).append(
		'<div id="'+selected_value_id+'" class="cp-object-link-selected-value">' +
			'<div class="name">' + 
				'<a href="' + object_url + '" target="_blank" class="link-ico '+ object.icon +'">' + object_name + '</a>' +
			'</div>' +
			'<div class="actions">' + 
				'<a href="#" class="link-ico ico-remove" onclick="og.remove_object_link_custom_property_value(\''+genid+'\', '+cp_id+', \''+hf_id+'\', '+object_id+')" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>' +
			'</div>' +
		'</div>'
	);
}

/**
 * Remove the object id from the selected values array and update the hidden field value.
 * Also remove the selected value div and show the add link.
 * 
 * @param {string} genid - the general id.
 * @param {string} cp_id - the custom property id.
 * @param {string} hf_id - the hidden field id.
 * @param {string} object_id - the object id to remove.
 */
og.remove_object_link_custom_property_value = function (genid, cp_id, hf_id, object_id) {

	// remove the object id from the selected values array
	let current_selected_object_ids = $("#"+hf_id).val();
	let current_selected_object_ids_array = JSON.parse(current_selected_object_ids);
	let index = current_selected_object_ids_array.indexOf(object_id);
	if (index > -1) {
		current_selected_object_ids_array.splice(index, 1);
	}
	
	// update hidden field value
	$("#"+hf_id).val(JSON.stringify(current_selected_object_ids_array));

	
	// remove the selected value div
	$('#'+genid+'selected-value'+cp_id+'-'+object_id).remove();

	// show the add link
	$("#"+genid+"cp-object-link-action"+cp_id).show();
}



og.showModal = function (content) {
var html = '<div class="modal-container" style="background-color: white;padding: 10px;">'+content+'</div>';
setTimeout(function() {
    $.modal (html, {
        close: true,
        escapeClose: true,
        clickClose: true,
        showClose: true
    });
}, 100);
}

og.showMailRuleModal = function (content) {
	var html = `
	<div class="modal-container modal-container-mail-rule">
		<button class="modal-close-button" onclick="$.modal.close();">×</button>
		${content}
	</div>
`;
	// var html = '<div class="modal-container modal-container-mail-rule" style="background-color: white;padding: 10px;">'+content+'</div>';
	setTimeout(function() {
		$.modal (html, {
			close: true,
			escapeClose: true,
			clickClose: true,
			showClose: true
		});
	}, 100);
	}

og.showConfirmationModal = function(options) {
    const config = {
        title: 'Confirm Action',
        message: 'Are you sure?',
        yesText: 'Yes',
        noText: 'No',
        onConfirm: function(){},
        onCancel: function(){},
        ...options
    };

    const modalId = 'confirmation-modal-' + Math.random().toString(36).substr(2, 9);

    const html = `
        <div id="${modalId}" class="confirmation-modal">
            <div class="confirmation-modal-title">${config.title}</div>
            <p class="confirmation-modal-message">${config.message}</p>
            <div class="confirmation-modal-buttons">
                <button id="${modalId}-cancel" class="confirmation-modal-btn cancel">${config.noText}</button>
                <button id="${modalId}-confirm" class="confirmation-modal-btn confirm">${config.yesText}</button>
            </div>
        </div>
    `;

    const $modal = $(html).appendTo('body').modal({
        closeExisting: false,
        escapeClose: true,
        clickClose: false,
        fadeDuration: 200
    });

    $(`#${modalId}-confirm`).click(function() {
        $.modal.close();
        config.onConfirm();
    });

    $(`#${modalId}-cancel`).click(function() {
        $.modal.close();
        config.onCancel();
    });

    // Hover effects
    $(`#${modalId}-cancel`).hover(
        function() { $(this).addClass('hover'); },
        function() { $(this).removeClass('hover'); }
    );

    $(`#${modalId}-confirm`).hover(
        function() { $(this).addClass('hover'); },
        function() { $(this).removeClass('hover'); }
    );
};

og.showConfirmationModalOLD = function(options) {
    const config = {
        title: 'Confirm',
        message: 'Are you sure?',
        yesText: 'Yes',
        noText: 'No',
        onConfirm: function () {},
        onCancel: function () {},
        ...options
    };

    const modalId = 'confirmation-modal-' + Math.random().toString(36).substr(2, 9);

    const html = `
        <div class="confirmation-modal-old">
            <div><label class="coInputTitle">${config.title}</label></div><br />
            <div id="${modalId}_question">${config.message}</div>
            <div id="${modalId}_buttons" class="confirmation-modal-old-buttons">
                <button class="yes submit blue">${config.yesText}</button>
                <button class="no submit blue">${config.noText}</button>
            </div>
            <div class="clear"></div>
        </div>
    `;

    const div = document.createElement('div');
    div.innerHTML = html;

    const modal_params = {
        escClose: true,
        overlayClose: true,
        closeHTML: `<a id="${modalId}_close_link" class="modal-close" title="close"></a>`,
        onShow: function (dialog) {
            $(`#${modalId}_close_link`).addClass("modal-close-img");

            $(`#${modalId}_buttons .yes`).click(function () {
                $.modal.close();
                config.onConfirm();
            });

            $(`#${modalId}_buttons .no`).click(function () {
                $.modal.close();
                config.onCancel();
            });
        }
    };

    setTimeout(function () {
        $.modal(div, modal_params);
    }, 100);
};


/**
 * Returns an array of member IDs that are selected in the object form.
 * If the HTML element with id <genid>members exists, it will be used to get the member IDs.
 * If the element does not exist, it will look for the classification dimension member IDs in the form.
 * @param {string} genid - The unique identifier of the object form.
 * @return {array} - An array of member IDs that are selected in the object form.
 */
og.getObjectFormSelectedMembers = function(genid) {
	let member_ids = [];
	if ($("#"+genid+"members").length > 0) {
		member_ids = Ext.util.JSON.decode($("#"+genid+"members").val());
	} else {
		$("#"+genid+"submit-edit-form input[name^='classification_']").each(function() {
			if ($(this).val()) {
				let dim_mem_ids = Ext.util.JSON.decode($(this).val());
				member_ids = member_ids.concat(dim_mem_ids);
			}
		});
	}

	return member_ids;
};

/**
 * Returns an array of member IDs that are selected in the object form.
 * If the HTML element with id <genid>members exists, it will be used to get the member IDs.
 * If the element does not exist, it will look for the dimension member IDs in the form.
 * @param {string} genid - The unique identifier of the object form.
 * @param {string} type_name - The name of the object type.
 * @return {array} - An array of member IDs that are selected in the object form.
 */
og.getMembersToFilterObjectPicker = function(genid, type_name) {
	if (!type_name) type_name = "task";

	let task_dimensions = og.dimensionsByObjectTypeInMemberSelector[type_name];
	if (!task_dimensions) task_dimensions = [];

	// get the dimension id of project managers and job phases
	let project_manager_dim_id = 0;
	let job_phase_dim_id = 0;
	for (did in og.dimensions_info) {
		if (!isNaN(did) && og.dimensions_info[did].code == "project_managers" && task_dimensions.includes(did)) {
			project_manager_dim_id = did;
		} else if (!isNaN(did) && og.dimensions_info[did].code == "job_phases" && task_dimensions.includes(did)) {
			job_phase_dim_id = did;
		}
	}

	// the dimensions to filter
	let context = {};
	let dimensions_to_filter = [og.projects.dimension_id];
	if (project_manager_dim_id > 0) dimensions_to_filter.push(project_manager_dim_id);
	if (job_phase_dim_id > 0) dimensions_to_filter.push(job_phase_dim_id);

	// get the selected members for the dimensions to filter
	let member_ids = og.getObjectFormSelectedMembers(genid);

	// build the context variable to use in the object picker
	for (let i=0; i<dimensions_to_filter.length; i++) {
		let dim_id = dimensions_to_filter[i];
		let dim_mem_ids = Object.keys(og.dimensions[dim_id]);
		let intersection = dim_mem_ids.filter(function(n) {
			return member_ids.indexOf(parseInt(n)) !== -1;
		});
		
		if (intersection && intersection.length > 0) {
			context[dim_id] = intersection;
		}
	}

	// encode the context
	return Ext.util.JSON.encode(context);
};


/**
 * Returns a custom property object based on the given id and object type id.
 * The custom property object contains the id, name, type, description, default value, and other
 * properties of the custom property.
 * @param {number|string} cp_id The id of the custom property.
 * @param {number|string} object_type_id The id of the object type the custom property belongs to.
 * @return {object|null} The custom property object or null if no custom property is found.
 */
og.get_custom_property_by_id_and_type_id = function(cp_id, object_type_id) {
	// Get the object type object based on the given object type id
	let ot = og.objectTypes[object_type_id];
	
	// If the custom property id starts with 'cp_', remove the 'cp_' prefix
	if (cp_id.indexOf('cp_') == 0) {
		cp_id = cp_id.replace('cp_', ''); // Remove the 'cp_' prefix from the custom property id
	}
	
	// If the object type is found, get the custom property based on the custom property id and the object type name
	if (ot) {
		return og.get_custom_property_by_id_and_type_name(cp_id, ot.name);
	}
}

/**
 * Returns a custom property object based on the given id and object type name.
 * The custom property object contains the id, name, type, description, default value, and other
 * properties of the custom property.
 * @param {number|string} cp_id The id of the custom property.
 * @param {string} object_type_name The name of the object type the custom property belongs to.
 * @return {object|null} The custom property object or null if no custom property is found.
 */
og.get_custom_property_by_id_and_type_name = function(cp_id, object_type_name) {
	if (og.custom_properties_by_type && og.custom_properties_by_type[object_type_name]) {
		// Iterate through the custom properties of the given object type and find the one with the
		// given id.
		for (let i = 0; i < og.custom_properties_by_type[object_type_name].length; i++) {
			if (og.custom_properties_by_type[object_type_name][i].id == cp_id) {
				return og.custom_properties_by_type[object_type_name][i];
			}
		}
	}
}

og.show_cp_generic_warning = function(element) {
	$("#" + element.id).closest('.input-container').find('.cp-warning-message').show();
}
og.hide_cp_generic_warning = function(element) {
	$("#" + element.id).closest('.input-container').find('.cp-warning-message').hide();
}

/**
 * Highlights a extjs based component based on the given value.
 * If the value is null, empty string, or undefined, the select field is highlighted in a dark gray color.
 * Otherwise, the select field is highlighted in a red color.
 * 
 * Used to highlight selected filters in lists
 * 
 * @param {string} cmp_id The ID of the custom property select field to highlight.
 * @param {string} value The value to check for highlighting the select field.
 */
og.highlight_selected_extjs_filter = function(cmp_id, value) {
	let color = (value === null || value === '' || value == undefined) ? '#333' : '#dd4b39';
	let cmp = Ext.getCmp(cmp_id);
	if (cmp) cmp.el.dom.style.color = color;
}

/** 
 * Returns the selected member IDs as a stringified JSON array
 * @param {string} genid - the id of the form
 * @returns {string} - the selected member IDs as a stringified JSON array
 */
og.get_selected_members_in_form = function(genid) {
	let member_ids_val = '';

	if ($("#"+genid+"members").length > 0) {
		member_ids_val = $("#"+genid+"members").val();
	} else {
		let member_ids_array = [];
		$("#"+genid+"submit-edit-form input[name^='classification_']").each(function(index, obj) {
			member_ids_array = member_ids_array.concat(JSON.parse($(obj).val()));
		});
		member_ids_val = JSON.stringify(member_ids_array);
	}

	return member_ids_val;
}


	og.initCustomNameSelects = function(count) {

		addSelectListeners(); // Add listeners to the "Add Select" buttons
		addDeleteListeners(); // Add listeners to the "Delete Select" buttons
	  
		function addSelectListeners() {
		  const buttons = document.querySelectorAll('button[id^="add_custom_name_select_"]');
		  buttons.forEach(function (button) {
			button.removeEventListener('click', handleAddSelect); // Avoid duplicates
			button.addEventListener('click', handleAddSelect);
		  });
		}
	  
		function handleAddSelect(event) {
		  const buttonId = event.target.id;
		  const wrapper = event.target.closest('tr').querySelector('.custom-name-wrapper');
	  
		  // Obtener las opciones del primer select (o del último si ya hay varios)
		  const firstSelect = wrapper.querySelector('select.custom-name-select');
		  const options = Array.from(firstSelect.options).map(option => option.cloneNode(true));
	  
		  // Crear un nuevo select con las mismas opciones
		  addNewSelect(wrapper, options);
		}
	  
		function addNewSelect(wrapper, options) {
		  count++;
	  
		  // Create the new select
		  const newSelect = document.createElement('select');
		  newSelect.className = 'custom-name-select';
	  
		  // Generate a unique id and name for the new select
		  const dimensionId = wrapper.getAttribute('data-dimension-id');
		  const objectTypeId = wrapper.getAttribute('data-object-type-id');
		  const uniqueId = `${dimensionId}_${objectTypeId}_${count}`;
		  newSelect.id = `custom_name_select_${uniqueId}`;
		  newSelect.name = `custom_name_select[${dimensionId}][${objectTypeId}][]`;
	  
		  // Add the options to the new select
		  options.forEach(option => newSelect.appendChild(option));
	  
		  // Create the delete button
		  const deleteButton = document.createElement('button');
		  deleteButton.type = 'button';
		  deleteButton.textContent = 'x';
		  deleteButton.className = 'delete-select';
	  
		  // Add new event listeners to the delete button
		  deleteButton.addEventListener('click', function () {
			newSelect.remove();
			deleteButton.remove();
	  
			// If all selects are deleted, add the default select
			if (wrapper.querySelectorAll('select').length === 0) {
			  addDefaultSelect(wrapper);
			}
		  });
	  
		  // Add the new select and delete button
		  wrapper.appendChild(newSelect);
		  wrapper.appendChild(deleteButton);
		}
	  
		function addDefaultSelect(wrapper) {
		  count++;
		  const newSelect = document.createElement('select');
		  newSelect.className = 'custom-name-select';
		  const dimensionId = wrapper.getAttribute('data-dimension-id');
		  const objectTypeId = wrapper.getAttribute('data-object-type-id');
		  const uniqueId = `${dimensionId}_${objectTypeId}_${count}`;
		  newSelect.id = `custom_name_select_${uniqueId}`;
		  newSelect.name = `custom_name_select[${dimensionId}][${objectTypeId}][]`;
	  
		  const defaultOption = document.createElement('option');
		  defaultOption.value = 'name';
		  defaultOption.textContent = 'Name';
		  newSelect.appendChild(defaultOption);
	  
		  wrapper.appendChild(newSelect);
		}
	  
		function addDeleteListeners() {
		  const deleteButtons = document.querySelectorAll('.delete-select');
		  deleteButtons.forEach(function (deleteButton) {
			deleteButton.removeEventListener('click', handleDelete); // Evitar duplicados
			deleteButton.addEventListener('click', handleDelete);
		  });
		}
	  
		function handleDelete(event) {
		  const button = event.target;
		  const select = button.previousElementSibling;
	  
		  if (select) select.remove();
		  button.remove();
	  
		  // check if all selects are deleted
		  const wrapper = button.closest('.custom-name-wrapper');
		  if (wrapper && wrapper.querySelectorAll('select').length === 0) {
			addDefaultSelect(wrapper);
		  }
		}
	  
		og.toggleInfo = function() {
		  var infoText = document.getElementById('more-info-text');
		  if (infoText.style.display === "none" || infoText.style.display === "") {
			infoText.style.display = "block";
		  } else {
			infoText.style.display = "none";
		  }
		}
	  }
	  