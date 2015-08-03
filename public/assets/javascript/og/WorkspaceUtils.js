

//----------------------------------------
// Workspace PATH
//----------------------------------------

og.getWorkspaceColor = function(id){
	var tree = Ext.getCmp('workspaces-tree');
	var node = tree.tree.getNodeById('ws' + id);
	if (node)
		return node.ws.color;
}

og.getFullWorkspacePath = function(id, includeCurrent){
	var tree = Ext.getCmp('workspaces-tree');
	var node = tree.tree.getNodeById('ws' + id);
	var result = '';
	
	if (node != null && node.ws.id != 0){
		var activews = tree.tree.getActiveWorkspace();
		if (node.ws.id != activews.id){
			var originalNode = node;
			node = node.parentNode;
			while (node != null && node.ws.id != 0 && node.ws.id != activews.id){
				result = node.ws.name + "/" + result;
				node = node.parentNode;
			}
			result += originalNode.ws.name;
		}
		if (includeCurrent){
			if (node != null && node.ws.id != 0)
				if (result == '')
					result = node.ws.name;
				else
					result = node.ws.name + "/" + result;
		}
	}
	return result;
}

og.showWsPaths = function(containerItemName, showPath, showCurrent){
	var container = containerItemName != '' ? document.getElementById(containerItemName): null;
	if (container == null)		//Container name null or container not found
		container = document;
	
	var list = container.getElementsByTagName('span');
	var rand_no = Math.ceil(10000*Math.random());
	for(var i = 0; i < list.length; i++){

		if (list[i].className == 'project-replace'){
			list[i].className = '';
			var ids = list[i].innerHTML.split(',');
			var tree = Ext.getCmp('workspaces-tree');
			var activews = tree.tree.getActiveWorkspace();
			for (var j = 0; j < ids.length; j++)
				if (ids[j] == activews.id && !showCurrent){
					ids.splice(j,1);
					j--;
				}
			var html = '';
			if (ids.length > 2){
				html = '<span class="og-wsname og-wsname-color-0 ico-workspaces-expand" id="spshow' + rand_no + '-' + i + '" onclick="document.getElementById(\'sphide' + rand_no + '-' + i + '\').style.display =\'inline\';document.getElementById(\'spcont' + rand_no + '-' + i + '\').style.display =\'inline\';this.style.display=\'none\'">' + ids.length + '&nbsp;'+ lang('workspaces') + '</span>';
				html += '<span class="ico-workspaces-collapse" id="sphide' + rand_no + '-' + i + '" onclick="document.getElementById(\'spshow' + rand_no + '-' + i + '\').style.display =\'inline\';document.getElementById(\'spcont' + rand_no + '-' + i + '\').style.display =\'none\';this.style.display=\'none\'" style="display:none">&nbsp;</span>';
				html += '<span id="spcont' + rand_no + '-' + i + '" style="display:none">';
			}
			for(var j = 0; j < ids.length; j++){
				html = html + "<span>" + og.renderWsPath(ids[j].replace(/^\s*([\S\s]*?)\s*$/, '$1'),showPath, showCurrent) + "</span>&nbsp;";
			}
			if (ids.length > 2){
				html += '</span>';
			}
			list[i].innerHTML = html;
		}
	}
};

og.renderWsPath = function(id,showPath, showCurrent){
	var tree = Ext.getCmp('workspaces-tree');
	var node = tree.tree.getNodeById('ws' + id);
	var html = '';
	
	var shortLength = 4;
	var longLength = 12;
	var append = '&hellip;';
	
	var count = 0;
	if (node != null && node.ws.id != 0){
		//Count path depth
		var activews = tree.tree.getActiveWorkspace();
		if (node.ws.id != activews.id || showPath || showCurrent){
			var originalNode = node;
			node = node.parentNode;
			while (node != null && node.ws.id != 0 && (node.ws.id != activews.id || showPath || showCurrent)){
				count++;
				if (node.ws.id == activews.id && !showPath)
					break;
				node = node.parentNode;
			}
			count++;
		}
		//Adjust workspace label size
		if (count > 3){
			shortLength = 2;
			longLength = 8;
			append = '.';
		}else if (count > 5){
			shortLength = 1;
			longLength = 5;
			append = '.';
		}else if (count == 1) longLength = 16;
		//Render path
		node = tree.tree.getNodeById('ws' + id);
		if (node.ws.id != activews.id || showPath || showCurrent){
			originalNode = node;
			node = node.parentNode;
			while (node != null && node.ws.id != 0 && (node.ws.id != activews.id || showPath || showCurrent)){
				html = '<a class="og-wsname-color-' + originalNode.ws.color + '" href="#"  onclick="Ext.getCmp(\'workspace-panel\').select(' + node.ws.id + ')" name="' + og.clean(og.clean(node.ws.name)).replace('"', '\\"') + '">' + og.trimMax(node.ws.name, shortLength,append) + "</a>/" + html;
				if (node.ws.id == activews.id && !showPath)
					break;
				node = node.parentNode;
			}
			html = '<span class="og-wscont og-wsname"><span style="padding-left:1px;padding-right:1px" class="og-wsname-color-' + originalNode.ws.color + '" onmouseover="og.triggerFPT(this)" onmouseout="og.clearTriggerFPT()">'+ html + '<a href="#" onclick="Ext.getCmp(\'workspace-panel\').select(' + originalNode.ws.id + ')" name="' + og.clean(og.clean(originalNode.ws.name)).replace('"', '\\"') + '" class="og-wsname-color-' + originalNode.ws.color + '">' + og.trimMax(originalNode.ws.name, longLength, append) + "</a></span></span>";
		}
	}
	return html;
};

og.swapNames = function(object){
	var s = object.innerHTML;
	object.innerHTML = object.name;
	object.name = s;
};

og.showFullPathTooltip = function(object, isMouseOver){
	var object = og.triggerFPTObject;
	if (object){
		if (!Ext.isIE){
			if (object.currentStyle)
				var bgColor = object.currentStyle['background-color'];
			else if (window.getComputedStyle)
				var bgColor = document.defaultView.getComputedStyle(object,null).getPropertyValue('background-color');
		} else {
			var bgColor = object.currentStyle['backgroundColor'];
		}
		if (bgColor == 'transparent')
			bgColor = '#FCFCFC';
	
		var cn = object.childNodes;
		for (var i = 0; i < cn.length; i++) {
			if (cn[i].name != null && cn[i].name != ''){
				og.swapNames(cn[i]);
			}
		}
		Tip(object.innerHTML,FOLLOWMOUSE,false,FADEIN,300,STICKY,0,CLICKCLOSE,true,BGCOLOR,bgColor,BORDERCOLOR,bgColor);
		for (var i = 0; i < cn.length; i++) {
			if (cn[i].name != null && cn[i].name != ''){
				og.swapNames(cn[i]);
			}
		}
	}
};

og.triggerFPT = function(object){
	UnTip();
	og.triggerFPTObject = object;
	og.triggerFPTTO = setTimeout(og.showFullPathTooltip,400);
};


og.clearTriggerFPT = function(){
	clearTimeout(og.triggerFPTTO);
};

og.trimMax = function(str, size, append){
	if (append == null)
		append = '&hellip;';
	var result = str.replace(/^\s*/, "").replace(/\s*$/, ""); //Trims the input string
	if (result.length > size + 1){
		result = og.clean(result.substring(0,size).replace(/^\s*/, "").replace(/\s*$/, "")) + append;
	}
	return result;
};




//----------------------------------------
// Workspace CRUMBS
//----------------------------------------
		
		

og.expandSubWsCrumbs = function(id){
	var tree = Ext.getCmp('workspaces-tree');
	var node = tree.tree.getNode(typeof id != 'undefined' ? id : og.triggerSubWsCrumbsID);
	
	if (node && node.childNodes.length > 0){
		og.showSubWsMenu(node);
	}
};

og.showSubWsMenu = function(node){
	var html = "";
	for (var i = 0; i < node.childNodes.length; i++){
		var cn = node.childNodes[i];
		if (cn.id != 'trash' && cn.id != 'archived') {
			html += "<div class=\"subwscrumbs\"><a class=\"ico-color" + cn.ws.color + "\" style=\"padding-bottom:2px;padding-top:1px;padding-left:18px;background-repeat:no-repeat!important\" href=\"#\" onclick=\"Ext.getCmp('workspace-panel').select(" + cn.ws.id + ");og.clearSubWsCrumbs();return false;\">" + cn.ws.name + "</a></div>";
		}
	}
		
	var expander = document.getElementById('subWsExpander');
	expander.innerHTML = html;
	expander.style.display = 'block';
	clearTimeout(og.eventTimeouts['swst']);
	expander = Ext.get('subWsExpander');
	expander.slideIn("l", {duration: 0.5, useDisplay: true});
	og.eventTimeouts['swst'] = setTimeout("og.HideSubWsTooltip()", 5000);

};

og.adjustSubWsCrumbsPosition = function(){
	var expander = document.getElementById('subWsExpander');
	var wsCrumbs = document.getElementById('wsCrumbsDiv');
	expander.style.left = (wsCrumbs.offsetWidth + 70) + "px";
};

og.setSubWsTooltipTimeout = function(value){
	og.eventTimeouts['swst'] = setTimeout("og.HideSubWsTooltip()", value);
};

og.HideSubWsTooltip = function(){
	var expander = Ext.get('subWsExpander');
	expander.slideOut("l", {duration: 0.5, useDisplay: true});
};

og.clearSubWsCrumbs = function(){
	var expander = document.getElementById('subWsExpander');
   	expander.innerHTML = '';
   	expander.style.display = 'none';
	clearTimeout(og.eventTimeouts['swst']);
};

og.updateWsCrumbs = function(newWs) {
	var html = '';
	var first = true;
	var tree = Ext.getCmp('workspaces-tree');
	og.triggerSubWsCrumbsID = newWs.id;
	while (newWs.id != 0){
		var actNode = tree.tree.getNodeById('ws' + newWs.id);
		if (!actNode)
			break;
		if (first){
			first = false;
			html = '<div id="curWsDiv" style="font-size:150%;display:inline;"><a href="#" style="display:inline;line-height:28px" onmouseover="og.adjustSubWsCrumbsPosition()" onclick="og.expandSubWsCrumbs(' + actNode.ws.id + ')">' + actNode.text + '</a></div>' + html;
		} else
			html = '<a href="#" onclick="Ext.getCmp(\'workspace-panel\').select(' + actNode.ws.id + ')">' + actNode.text + '</a>' + html;
		
		html = ' / ' + html;
		var node = tree.tree.getNode(newWs.parent)
		if (node)
			newWs = node.ws;
		else
			break;
	}
	
	if (first){
		html = '<div id="curWsDiv" style="font-size:150%;display:inline;"><a href="#" style="display:inline;line-height:28px" onmouseover="og.adjustSubWsCrumbsPosition()" onclick="og.expandSubWsCrumbs(' + newWs.id + ')">' + newWs.name + '</a></div>' + html;
	} else html = '<a href="#" onclick="Ext.getCmp(\'workspace-panel\').select(0)">' + lang('all') + '</a>' + html;
	var crumbsdiv = Ext.get('wsCrumbsDiv');
	crumbsdiv.dom.innerHTML = html;
};

og.updateWsCrumbsTag = function(newTag) {
	var html = '';
	if (newTag.name != "") {
		html = '<div class="wsTagCrumbsElement" onmouseover="document.getElementById(\'wsTagCloseDiv\').style.display=\'block\'" onmouseout="document.getElementById(\'wsTagCloseDiv\').style.display=\'none\'">' + newTag.name + '<div id="wsTagCloseDiv" class="wsTagCloseDiv" title="' + lang('close this tag') + '" onclick="Ext.getCmp(\'tag-panel\').select(0)"></div></div>';
	}
	
	var crumbsdiv = Ext.get('wsTagCrumbs');
	crumbsdiv.dom.innerHTML = html;
};


if(document.addEventListener)
	document.addEventListener("mouseup", og.setSubWsTooltipTimeout.createCallback(null, 100), false);
else
	document.attachEvent("onmouseup", og.setSubWsTooltipTimeout.createCallback(null, 100));





//----------------------------------------
// Workspace SELECTOR
//----------------------------------------


og.drawWorkspaceSelector = function(renderTo, workspaceId, name, allowNone, extraWS, workspaces){
	var container = document.getElementById(renderTo);
	if (container){
		var tree = Ext.getCmp('workspaces-tree');
		var ws;
		var node = tree.tree.getNodeById('ws' + workspaceId);
		if (node) {
			ws = node.ws;
		}
		if (!ws && extraWS) {
			// look in the extra workspaces
			for (var i=0; i < extraWS.length; i++) {
				if (extraWS[i].id == workspaceId) {
					ws = extraWS[i];
					break;
				}
			}
		}
		if (!ws && workspaces) {
			// look in supplied workspaces
			for (var i=0; i < workspaces.length; i++) {
				if (workspaces[i].id == workspaceId) {
					ws = workspaces[i];
					break;
				}
			}
		}
		if (!ws) ws = tree.tree.getActiveOrPersonalWorkspace();
	
		var extra = Ext.util.JSON.encode(extraWS);
		var wss = Ext.util.JSON.encode(workspaces);
		var html = "<input type='hidden' id='" + renderTo + "Value' name='" + name + "' value='" + ws.id + "'/>";
		html +="<div class='x-form-field-wrap'><table><tr><td><div id='" + renderTo + "Header' class='og-ws-selector-header'>";
		var path = og.getFullWorkspacePath(ws.id,true);
		if (path == '')
			path = ws.id == 0 && allowNone || !ws.name ? lang('none') : ws.name;
		html += "<div class='coViewAction ico-color" + ws.color + " og-ws-selector-input' onclick='og.ShowWorkspaceSelector(\"" + renderTo + "\",\"" + ws.id + "\", " + (allowNone? 'true':'false') + ", " + extra + ", " + wss + ")' title='" + path + "'>" + path + "</div>";
		html +="</div></td><td><img class='x-form-trigger x-form-arrow-trigger og-ws-selector-arrow' onclick='og.ShowWorkspaceSelector(\"" + renderTo + "\",\"" + ws.id + "\", " + (allowNone? 'true':'false') + ", " + extra + ", " + wss + ")' src='s.gif'/></td></tr></table><div id='" + renderTo + "Panel'></div></div>";
		container.innerHTML = html;
	}
}

og.ShowWorkspaceSelector = function(controlName, workspaceId, allowNone, extra, wsList){
	if (document.getElementById(controlName + 'Panel').style.display == 'block')
		document.getElementById(controlName + 'Panel').style.display = 'none';
	else {
		if (document.getElementById(controlName + 'Panel').innerHTML == ''){
			var tree = Ext.getCmp('workspace-panel');
			if (!wsList) wsList = tree.getWsList();
			var newTree = new og.WorkspaceTree({
				id: controlName + 'Tree',
				renderTo: controlName + 'Panel',
				root:[],
				workspaces: wsList,
				isInternalSelector: true,
				width:200,
				height:250,
				selectedWorkspaceId: workspaceId,
				controlName: controlName,
				allowNone: allowNone,
				style: 'border:1px solid #99BBE8'
			});
			if (extra) {
				for (var i=0; i < extra.length; i++) {
					newTree.addWS(extra[i]);
				}
			}
		}
		document.getElementById(controlName + 'Panel').style.display = 'block';
	}
	//document.getElementById(controlName + 'Header').style.display = 'none';
}

og.WorkspaceSelected = function(controlName, workspace){
	var path =og.getFullWorkspacePath(workspace.id,true);
	if (path == '')
		path = workspace.name || lang('none');
	document.getElementById(controlName + 'Header').innerHTML = "<div class='coViewAction ico-color" + workspace.color + " og-ws-selector-input' onclick='og.ShowWorkspaceSelector(\"" + controlName + "\"," + workspace.id + ")'>" + og.clean(path) + "</div>";
	document.getElementById(controlName + 'Panel').style.display = 'none';
	document.getElementById(controlName + 'Header').style.display = 'block';
	document.getElementById(controlName + 'Value').value = workspace.id;	
}


og.IsWorkspaceParentOf = function(parentWsId, childWsId){
	var tree = Ext.getCmp('workspaces-tree');
	var node = tree.tree.getNodeById('ws' + childWsId);
	
	if (node != null && node.ws.id != 0){
		while (node != null && node.ws.id != 0 && node.ws.id != parentWsId){
			node = node.parentNode;
		}
		return node.ws.id == parentWsId;
	}
	return false;
}
