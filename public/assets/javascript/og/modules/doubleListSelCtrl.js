og.doubleListSelCtrl = {};

og.doubleListSelCtrl.selectAll = function(id) {
	og.doubleListSelCtrl.listChangeAll(id + '_box1', id + '_box2', id, '1');
};

og.doubleListSelCtrl.deselectAll = function(id) {
	og.doubleListSelCtrl.listChangeAll(id + '_box2', id + '_box1', id, '0');
};

og.doubleListSelCtrl.selectOne = function(id) {
	og.doubleListSelCtrl.listChangeSelected(id + '_box1', id + '_box2', id, '1');
};

og.doubleListSelCtrl.deselectOne = function(id) {
	og.doubleListSelCtrl.listChangeSelected(id + '_box2', id + '_box1', id, '0');
};

og.doubleListSelCtrl.moveUp = function(id, box_id) {
	var box = document.getElementById(id + box_id);
	if (!box) return;
	var idx = box.selectedIndex;
	
	og.doubleListSelCtrl.swapOption(box, id, idx, idx-1);
};

og.doubleListSelCtrl.moveDown = function(id, box_id) {
	var box = document.getElementById(id + box_id);
	if (!box) return;
	var idx = box.selectedIndex;
	
	og.doubleListSelCtrl.swapOption(box, id, idx, idx+1);
};

og.doubleListSelCtrl.swapOption = function(box, id, from, to) {
	if (to < 0 || to >= box.options.length || from < 0 || from >= box.options.length) {
		return;
	}
		
	var opt = document.createElement('option');
	opt.text = box.options[to].text;
	opt.value = box.options[to].value;
	
	box.remove(to);
	box.options.add(opt, from);
	
	var hffrom = document.getElementById(id + '[' + box.options[from].value + ']');
	var hfto = document.getElementById(id + '[' + box.options[to].value + ']');
	var tmp = hffrom.value;
	hffrom.value = hfto.value;
	hfto.value = tmp;
}

og.doubleListSelCtrl.listChangeSelected = function(from, to, id, new_val) {
	var box1 = document.getElementById(from);
	var box2 = document.getElementById(to);
	if (!box1 || !box2) return;
	
	var idx = box1.selectedIndex;
	if (idx == -1) return;
	
	var opt = document.createElement('option');
	opt.text = box1.options[idx].text;
	opt.value = box1.options[idx].value;
	
	if (Ext.isIE) box2.add(opt);
	else box2.add(opt, null);
	
	box1.remove(idx);
	
	var hf_id = id + '[' + opt.value + ']';
	var hf = document.getElementById(hf_id);
	if (hf) {
		if (new_val != '0') {
			new_val = box2.options.length;
		} else {
			for (i=idx; i<box1.options.length; i++) {
				var nexthf = document.getElementById(id + '[' + box1.options[i].value + ']');
				if (nexthf) nexthf.value -= 1;
			}
		}
		hf.value = new_val;
	}
	box1.selectedIndex = idx < box1.options.length ? idx : box1.options.length - 1;
	box1.focus();
};

og.doubleListSelCtrl.listChangeAll = function(from, to, id, new_val) {
	var box1 = document.getElementById(from);
	var box2 = document.getElementById(to);
	if (!box1 || !box2) return;
	
	var count = box1.options.length;
	for (idx = 0; idx < count; idx++) {
		var opt = document.createElement('option');
		opt.text = box1.options[idx].text;
		opt.value = box1.options[idx].value;
		
		if (Ext.isIE) box2.add(opt);
		else box2.add(opt, null);
		
		var hf_id = id + '[' + opt.value + ']';
		var hf = document.getElementById(hf_id);
		if (hf) {
			if (new_val != '0') new_val = box2.options.length;
			hf.value = new_val;
		}
	}
	for (idx = count-1; idx >= 0; idx--) {
		box1.remove(idx);
	}
	box2.focus();
};