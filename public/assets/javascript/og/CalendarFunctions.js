	og.selectingCells = false;
	og.actualSelCell = '';
	og.selectedCells = [];
	og.paintingDay = 0;
	og.old_style = {'background-color':'transparent', 'opacity':'1', 'filter':'alpha(opacity = 100)'};

	var scroll_to = -1;
	var cant_tips = 0;
	var tips_array = [];
	
	og.currentHourLineTOut = null;
	og.drawCurrentHourLine = function(d, pre) {
		if (cal_actual_view == 'viewweek' && pre == 'w_' || cal_actual_view == 'viewweek5days' && pre == 'w5_' || cal_actual_view == 'viewdate' && pre == 'd_') {
			if (!og.startLineTime) og.startLineTime = new Date();
						
			var date = new Date();
			var h = date.format('H');
			var m = date.format('i');
			var cell = h*2 + (m > 30 ? 1 : 0);
			cell_id = 'h' + d + '_' + cell;
			
			cell = Ext.get(cell_id);
			
			if (cell) {
				if(m > 30) m -= 30;
				var top = m * 100 / 30;
				
				old_line = Ext.get(pre+"currentHourLine");
				if (old_line) old_line.remove();
				
				var title = date.format(og.preferences['time_format_use_24'] == 1 ? 'G:i' : 'g:i A');
				var new_top = cell.getTop(true) + cell.getHeight() * top / 100;
				var cant_d = pre == 'w_' ? 7 : (pre == 'w5_' ? 5 : 1);
				var html = '<div id="'+pre+'currentHourLine" title="'+title+'" style="height:2px; z-index:200; position:absolute; top:'+ new_top +'px; left:'+ (d*100/cant_d) +'%; border-top:2px solid #B95000; width:'+(100/cant_d)+'%; opacity:0.7; filter:alpha(opacity=70);"></div>';
				Ext.get("eventowner").insertHtml('afterBegin', html);
			}
			
			var tout = 60*1000;
			if (og.currentHourLineTOut) clearTimeout(og.currentHourLineTOut);
			og.currentHourLineTOut = setTimeout('og.drawCurrentHourLine('+d+', "'+pre+'")', tout);
		} else {
			og.currentHourLineTOut = null;
		}
	}
	
	og.eventSelected = function(checked) {
		if (checked) og.events_selected += 1;
		else if (og.events_selected > 0) og.events_selected -= 1;
		var topToolbar = Ext.getCmp('calendarPanelTopToolbarObject');
		if (topToolbar) topToolbar.updateCheckedStatus(og.events_selected);
	}

	// Client-side, non-persisted expand/collapse of a parent task's subtasks (blocks/chips
	// rendered with data-parent-task-id="<parentId>" and their own data-task-id="<id>").
	// Cascades through the whole subtree (subtasks of subtasks, etc.), since only the
	// top-most task in a chain gets an expander icon. Resets to expanded on every view reload.
	og.toggleCalendarSubtasks = function(parentId, iconEl) {
		var collapsed = iconEl.getAttribute('data-collapsed') == '1';
		var display = collapsed ? '' : 'none';

		var frontier = [String(parentId)];
		var visited = {};
		while (frontier.length) {
			var id = frontier.shift();
			if (visited[id]) continue;
			visited[id] = true;
			var nodes = document.querySelectorAll('[data-parent-task-id="' + id + '"]');
			for (var i = 0; i < nodes.length; i++) {
				nodes[i].style.display = display;
				var ownId = nodes[i].getAttribute('data-task-id');
				if (ownId) frontier.push(ownId);
			}
		}

		iconEl.setAttribute('data-collapsed', collapsed ? '0' : '1');
		iconEl.innerHTML = collapsed ? '&#9662;' : '&#9656;';
	}
	
	/*******************************************
		DRAGGING & RESIZING
	*******************************************/

	og.CALENDAR_DRAG_PIXEL_THRESH = 12;
	og.CALENDAR_DRAG_MIN_HOLD_MS = 0;
	og._calendarActiveDrag = null;
	og._calendarDragMoveListener = null;
	og._calendarDragUpListener = null;
	og._calendarEventPress = null;

	og.isCalendarInteractiveTarget = function(target) {
		var el = target;
		while (el && el !== document.body) {
			var tag = el.tagName ? el.tagName.toUpperCase() : '';
			if (tag === 'A' || tag === 'INPUT' || tag === 'BUTTON' || tag === 'SELECT' || tag === 'TEXTAREA' || tag === 'IMG') {
				return true;
			}
			el = el.parentNode;
		}
		return false;
	};

	og.clearCalendarEventPress = function() {
		og._calendarEventPress = null;
	};

	og.markCalendarEventPress = function() {
		og._calendarEventPress = {active: true, dragged: false};
	};

	og.shouldSuppressCalendarGridAction = function() {
		return og._calendarEventPress && og._calendarEventPress.active && !og._calendarEventPress.dragged;
	};

	og.getMonthCellDateByKey = function(key) {
		if (!og.ev_cell_dates) {
			return null;
		}
		for (var i = 0; i < og.ev_cell_dates.length; i++) {
			if (og.ev_cell_dates[i].key == key) {
				return og.ev_cell_dates[i];
			}
		}
		return null;
	};

	og.findCalendarDropTargetFromPoint = function(x, y, dragMode) {
		var target = document.elementFromPoint(x, y);
		while (target) {
			if (target.id) {
				if (dragMode === 'month' && og.getMonthCellDateByKey(target.id)) {
					return target;
				}
				if (dragMode === 'week' && /^h\d+_\d+$/.test(target.id)) {
					return target;
				}
				if (dragMode === 'week_allday' && (target.id.indexOf('alldayeventowner_') >= 0 || target.id.indexOf('alldaycelltitle_') >= 0)) {
					return target;
				}
			}
			target = target.parentNode;
		}
		return null;
	};

	og.cancelCalendarEventDrag = function() {
		var state = og._calendarActiveDrag;
		if (state) {
			if (state.ghost && state.ghost.parentNode) {
				state.ghost.parentNode.removeChild(state.ghost);
			}
			if (state.el) {
				state.el.setOpacity(1);
			}
		}
		if (og._calendarDragMoveListener) {
			document.removeEventListener('mousemove', og._calendarDragMoveListener);
			og._calendarDragMoveListener = null;
		}
		if (og._calendarDragUpListener) {
			document.removeEventListener('mouseup', og._calendarDragUpListener, true);
			og._calendarDragUpListener = null;
		}
		og._calendarActiveDrag = null;
		og.clearCalendarEventPress();
	};

	og.onCalendarEventDragMove = function(e) {
		var state = og._calendarActiveDrag;
		if (!state) {
			return;
		}

		var buttons = e.browserEvent ? e.browserEvent.buttons : null;
		if (buttons != null && (buttons & 1) === 0) {
			og.cancelCalendarEventDrag();
			return;
		}

		if (Date.now() < state.dragEligibleAfter) {
			return;
		}

		var x = e.getPageX();
		var y = e.getPageY();
		var dx = Math.abs(x - state.startX);
		var dy = Math.abs(y - state.startY);
		var thresh = og.CALENDAR_DRAG_PIXEL_THRESH;

		if (!state.dragging) {
			if (dx <= thresh && dy <= thresh) {
				return;
			}
			state.dragging = true;
			if (og._calendarEventPress) {
				og._calendarEventPress.dragged = true;
			}
			state.ghost = state.el.dom.cloneNode(true);
			state.ghost.id = state.divId + '_drag_ghost';
			state.ghost.style.position = 'absolute';
			state.ghost.style.zIndex = 10000;
			state.ghost.style.opacity = '0.55';
			state.ghost.style.pointerEvents = 'none';
			state.ghost.style.width = state.el.getWidth() + 'px';
			state.ghost.style.height = state.el.getHeight() + 'px';
			state.ghost.style.margin = '0';
			document.body.appendChild(state.ghost);
			state.el.setOpacity(0.35);
		}

		state.ghost.style.left = (x - state.el.getWidth() / 2) + 'px';
		state.ghost.style.top = (y - 10) + 'px';
	};

	og.finishCalendarEventDrag = function(state, dropTarget) {
		var config = state.config;
		var ddata = Ext.apply({}, config.dragData);
		var ok = true;

		if (ddata.is_repe) {
			ok = confirm(lang('confirm repeating event edition'));
		}
		if (!ok) {
			og.cancelCalendarEventDrag();
			return;
		}

		if (config.dragMode === 'month') {
			var date = og.getMonthCellDateByKey(dropTarget.id);
			if (!date || (state.originCellId && dropTarget.id === state.originCellId)) {
				og.cancelCalendarEventDrag();
				return;
			}
			var parent = Ext.get(date.key);
			parent.appendChild(state.el);
			ddata.day = date.day;
			ddata.month = date.month;
			ddata.year = date.year;
		} else if (config.dragMode === 'week_allday') {
			var str_temp = dropTarget.id.split('_');
			var parent = Ext.get('alldayeventowner_' + str_temp[1]);
			parent.appendChild(state.el);
			og.reorganizeAllDayGrid();
			date = og.ev_cell_dates[str_temp[1]];
			if (!date) {
				og.cancelCalendarEventDrag();
				return;
			}
			ddata.day = date.day;
			ddata.month = date.month;
			ddata.year = date.year;
			ddata.hour = -1;
			ddata.min = -1;
		} else {
			var str_temp = dropTarget.id.split('_');
			var grid = Ext.get('grid');
			var parent = Ext.get('eventowner');
			var lt = Ext.get(dropTarget);
			var top = lt.getTop() - parent.getTop();
			var left = 100 * (lt.getLeft() - parent.getLeft() + 3) / grid.getWidth();
			state.el.applyStyles('top:' + top + 'px;left:' + left + '%;');
			parent.appendChild(state.el);
			date = og.ev_cell_dates[str_temp[0].substr(1)];
			if (!date) {
				og.cancelCalendarEventDrag();
				return;
			}
			ddata.day = date.day;
			ddata.month = date.month;
			ddata.year = date.year;
			ddata.hour = Math.floor(str_temp[1] / 2);
			ddata.min = (str_temp[1] % 2 == 0 ? 0 : 30);
		}

		if (config.fn && typeof config.fn === 'function') {
			config.fn({}, ddata);
		}
		og.cancelCalendarEventDrag();
	};

	og.onCalendarEventDragEnd = function(e) {
		var state = og._calendarActiveDrag;
		if (!state) {
			return;
		}

		if (!state.dragging) {
			if (e && e.stopEvent) {
				e.stopEvent();
			}
			og.cancelCalendarEventDrag();
			return;
		}

		var dropTarget = og.findCalendarDropTargetFromPoint(e.getPageX(), e.getPageY(), state.config.dragMode);
		if (!dropTarget) {
			og.cancelCalendarEventDrag();
			return;
		}

		og.finishCalendarEventDrag(state, dropTarget);
	};

	og.wrapCalendarDragEvent = function(ev) {
		if (ev && ev.getPageX) {
			return ev;
		}
		return new Ext.EventObjectImpl(ev);
	};

	og.attachCalendarEventDrag = function(div_id, config) {
		var el = Ext.get(div_id);
		if (!el) {
			return;
		}

		el.on('mousedown', function(e) {
			if (e.button !== 0) {
				return;
			}
			if (og.isCalendarInteractiveTarget(e.target)) {
				return;
			}

			og.cancelCalendarEventDrag();
			og.markCalendarEventPress();
			var parentEl = el.dom.parentNode;
			og._calendarActiveDrag = {
				divId: div_id,
				el: el,
				config: config,
				startX: e.getPageX(),
				startY: e.getPageY(),
				dragging: false,
				ghost: null,
				originCellId: parentEl && parentEl.id ? parentEl.id : null,
				dragEligibleAfter: Date.now() + og.CALENDAR_DRAG_MIN_HOLD_MS
			};

			og._calendarDragMoveListener = function(ev) {
				og.onCalendarEventDragMove(og.wrapCalendarDragEvent(ev));
			};
			og._calendarDragUpListener = function(ev) {
				og.onCalendarEventDragEnd(og.wrapCalendarDragEvent(ev));
			};

			document.addEventListener('mousemove', og._calendarDragMoveListener);
			document.addEventListener('mouseup', og._calendarDragUpListener, true);

			e.stopPropagation();
		});
	};

	og.createEventDrag = function(div_id, obj_id, is_repetitive, origdate, type, isAllday, dropzone) {
		og.attachCalendarEventDrag(div_id, {
			dragMode: isAllday ? 'week_allday' : 'week',
			dragData: {id: obj_id, is_repe: is_repetitive, orig_date: origdate},
			fn: function(dd, ddata) {
				switch (type) {
					case 'event':
						if (isAllday) {
							ddata.hour = -1;
							ddata.min = -1;
						}
						og.openLink(og.getUrl('event', 'move_event', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day, hour:ddata.hour, min:ddata.min, orig_date:ddata.orig_date}), {
							callback: function(success, data) {
								if (!isAllday) {
									updateTip(div_id, data.ev_data.subject, data.ev_data.start + " - " + data.ev_data.end);
									var els = [];
									if (Ext.isIE) {
										var spans = document.getElementsByTagName('span');
										for(var i=0; i<spans.length; i++){
											if(spans.item(i).getAttribute('name') == div_id+'_info'){
										    	els.push(spans.item(i));
											}
										}
									} else els = document.getElementsByName(div_id+'_info');

									if (els.length > 0) {									
										for (i=0; i<els.length; i++) {
											els[i].innerHTML = data.ev_data.start + (cal_actual_view == 'viewweek' || cal_actual_view == 'viewweek5days' ? "" : " - " + data.ev_data.end);												
										}
									}
									
									var color_divs = $("." + div_id + "_colors");
									
									var event_offset = $("#" + div_id).offset()
									var total_w = $("#" + div_id).outerWidth();
									var idx = 0;
									while (idx < color_divs.length) {
										var color_div = color_divs[idx];
										var color_div_left = event_offset.left + idx * (total_w / color_divs.length);
										$(color_div).offset({top: event_offset.top, left: color_div_left});
										idx++;
									}
								}
							}
						});
						break;
					case 'milestone':
						og.openLink(og.getUrl('milestone', 'change_due_date', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day}), {});
						break;
					case 'task':
						var d_to_change = (div_id.indexOf('_end_') != -1 ? 'due' : (div_id.indexOf('_st_') != -1  ? 'start' : 'both'));
						og.openLink(og.getUrl('task', 'change_start_due_date', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day, hour:0, min:0, tochange:d_to_change}), {});
						break;
					default: break;
				}
			}
		});
	};
	
	og.createMonthlyViewDrag = function(div_id, obj_id, is_repetitive, type, origdate) {
		og.attachCalendarEventDrag(div_id, {
			dragMode: 'month',
			dragData: {id: obj_id, is_repe: is_repetitive, orig_date: origdate},
			fn: function(dd, ddata) {
				switch (type) {
					case 'event':
						og.openLink(og.getUrl('event', 'move_event', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day, hour:-1, min:-1, orig_date:ddata.orig_date}), {});
						break;
					case 'milestone':
						og.openLink(og.getUrl('milestone', 'change_due_date', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day, hour:-1, min:-1}), {});
						break;
					case 'task':
						var d_to_change = (div_id.indexOf('_end_') != -1  ? 'due' : (div_id.indexOf('_st_') != -1  ? 'start' : 'both'));
						og.openLink(og.getUrl('task', 'change_start_due_date', {id:ddata.id, year:ddata.year, month:ddata.month, day:ddata.day, conserve_times:1, tochange:d_to_change}), {});
						break;
					default: break;
				}
			}
		});
	};
	
	
	og.adjustAllDayEventsHeight = function(genid) {
		var h = $("#allDayGrid").outerHeight() - 31;
		for (var dow=0; dow<7; dow++) {
			var all_day_div = document.getElementById("alldayeventowner_"+dow);
			if (all_day_div) {
				$("#"+ genid +"view_calendar #alldayeventowner_"+dow).css('height', h+'px');
				
				var real_h = all_day_div.scrollHeight;
				if (h < real_h) {
					$("#all_ev_show_more_link_"+dow).css('visibility', 'visible');
				} else {
					$("#all_ev_show_more_link_"+dow).css('visibility', 'hidden');
				}
			}
		}
	}
	
	
	og.setResizableEvent = function(div_id, ev_id, w_day) {
		var resizer = new Ext.Resizable(div_id, {
		    adjustments: [0,-4],
		    handles: 's',
		    heightIncrement: 21,
		    resizeChild: 'inner_' + div_id,
		    pinned: true
		});
		var prev_height = 0;
		resizer.on('beforeResize', function() {
			el = resizer.getEl();
			if (el) prev_height = el.getHeight();
		});
		resizer.on('resize', function() {
			el = resizer.getEl();
			var grid = Ext.get('grid');
			width = 100 * el.getWidth() / grid.getWidth();
			el.applyStyles('width:'+width+'%;');

			rows = (el.getHeight() - prev_height) / 21;
			dur_h = rows < 0 ? Math.ceil(rows / 2) : Math.floor(rows / 2);
			dur_m = (rows % 2 == 0 ? 0 : 30) * (rows < 0 ? -1 : 1);
			og.openLink(og.getUrl('event', 'change_duration', {id:ev_id, hours:dur_h, mins:dur_m}), {
				callback: function(success, data) {
					ev_data = data.ev_data;
					if (ev_data) updateTip(div_id, ev_data.subject, ev_data.start + " - " + ev_data.end);
					$("." + div_id + "_colors").height($("#" + div_id).outerHeight()+'px');
				}
			});
		});
	}
	
	og.reorganizeAllDayGrid = function() {
		var container = Ext.get('allDayGrid');
		var max_height = 0;
		for (i=0; i<6; i++) {
			var parent = Ext.get('alldayeventowner_' + i);
			if (parent != null) {
				var obj = parent.first();
				var top = 5;
				while (obj) {
					obj.applyStyles('top:'+top+'px;');
					top += 21;
					obj = obj.next();
				}
				if (top > max_height) max_height = top;
			}
		}
		if (max_height > 0) {
			max_height += 16;
			container.applyStyles('height:'+max_height+'px;');
			for (i=0; i<6; i++) {
				var parent = Ext.get('alldayeventowner_' + i);
				if (parent != null) 
					parent.applyStyles('height:'+max_height+'px;');
			}
		}
	}

	/*******************************************
		END DRAGGING & RESIZING
	*******************************************/
	
	addTipToArray = function(pos, div_id, title, bdy) {
		tips_array[pos] = new Ext.ToolTip({
			target: div_id,
	        html: bdy,
	        title: title,
	        hideDelay: 1500,
	        closable: true
		});
	}
	
	addTip = function(div_id, title, bdy) {
		addTipToArray(cant_tips++, div_id, title, bdy);
	}
	
	updateTip = function(div_id, title, body) {
		for (i=0; i<cant_tips; i++) {
			tip = tips_array[i];
			if (tip && tip.target.id == div_id) {
				tip.disable();
				addTipToArray(i, div_id, title, body);				
				break;
			}
		}
	}
	
	og.change_link_incws = function(hrefid, checkid) {
		var link = document.getElementById(hrefid).href
		if (document.getElementById(checkid).checked) { 
			document.getElementById(hrefid).href = link.replace('isw=0', 'isw=1');
		} else {
			document.getElementById(hrefid).href = link.replace('isw=1', 'isw=0');
		}
	}
	
	og.overCell = function(cell_id) {
		var ele = Ext.get(cell_id);
		if (!ele) return;
		if (!og.selectingCells) og.old_style = ele.getStyles('background-color', 'opacity', 'filter');
		ele.applyStyles({'background-color':'#D3E9FF', 'opacity':'1', 'filter':'alpha(opacity = 100)'});
	}
	
	og.resetCell = function(cell_id) {
		var ele = Ext.get(cell_id);
		if (ele) ele.applyStyles(og.old_style);
	}
	
	og.minSelectedCell = function() {
		min_val = 99;
		for (i=0; i<og.selectedCells.length; i++) {
			if (og.selectedCells[i] != '') {
				str_temp = og.selectedCells[i].split('_');
				min_val = parseInt(str_temp[1]) < min_val ? parseInt(str_temp[1]) : min_val;
			}
		}
		return min_val;
	}
	
	og.paintSelectedCells = function(cell_id) {
		str_temp = cell_id.split('_');
		cell_id = 'h' + og.paintingDay + '_' + str_temp[1];

		if (og.selectingCells && og.actualSelCell != cell_id) {
			for (i=0; i<og.selectedCells.length; i++) {
				curr_split = og.selectedCells[i].split('_');
				if (parseInt(curr_split[1]) > parseInt(str_temp[1])/*cell_id*/) {
					og.resetCell(og.selectedCells[i]);
					og.selectedCells[i] = '';
				}
			}
		
			i = og.minSelectedCell();
			if (i == 99) i = str_temp[1];
			do {
				temp_cell = 'h' + og.paintingDay + '_' + i;
				og.overCell(temp_cell);
				og.selectedCells[og.selectedCells.length] = temp_cell;
				i++;
			} while (temp_cell != cell_id && i < 48);
			og.actualSelCell = cell_id;
		}
	}
	
	og.clearPaintedCells = function() {
		for (i=0; i<og.selectedCells.length; i++) {
			if (og.selectedCells[i] != '') og.resetCell(og.selectedCells[i]);
		}
		og.selectedCells = [];
		og.selectingCells = false;
		og.actualSelCell = '';
	}
	
	// hour range selection
	var ev_start_day, ev_start_month, ev_start_year, ev_start_hour, ev_start_minute;
	var ev_end_day, ev_end_month, ev_end_year, ev_end_hour, ev_end_minute;
	
	og.selectStartDateTime = function(day, month, year, hour, minute) {
		if (og.shouldSuppressCalendarGridAction()) {
			return;
		}
		og.selectingCells = true;
		og.selectDateTime(true, day, month, year, hour, minute);
	}
	
	og.selectEndDateTime = function(day, month, year, hour, minute) {
		og.selectDateTime(false, day, month, year, hour, minute);
	}
	
	og.selectDateTime = function(start, day, month, year, hour, minute) {
		if (start == true) {
			ev_start_day = day;
			ev_start_month = month; 
			ev_start_year = year; 
			ev_start_hour= hour; 
			ev_start_minute = minute; 
		} else {
			ev_end_day = day; 
			ev_end_month = month; 
			ev_end_year = year; 
			ev_end_hour = hour; 
			ev_end_minute = minute; 
		}
		
	}
	
	og.setSelectedStartTime = function() {
		min_val = og.minSelectedCell();
		ev_start_hour = Math.floor(min_val / 2);
		ev_start_minute = (min_val % 2 == 0) ? 0 : 30;
	}
	
	og.getDurationMinutes = function() {
		og.setSelectedStartTime();
		
		var s_val = new Date();
		s_val.setFullYear(ev_start_year);
		s_val.setMonth(ev_start_month);
		s_val.setDate(ev_start_day);
		s_val.setHours(ev_start_hour);
		s_val.setMinutes(ev_start_minute);
		s_val.setSeconds(0);
		s_val.setMilliseconds(0);
		
		var e_val = new Date();
		e_val.setFullYear(ev_start_year);
		e_val.setMonth(ev_start_month);
		e_val.setDate(ev_start_day);
		e_val.setHours(ev_end_hour);
		e_val.setMinutes(ev_end_minute);
		e_val.setSeconds(0);
		e_val.setMilliseconds(0);
		
		if (ev_end_hour == 0) e_val.setDate(e_val.getDate() + 1);
		
		var millis = e_val.getTime() - s_val.getTime();
		
		return ((millis / 1000) / 60); 		
	}
	
	og.showEventPopup = function(day, month, year, hour, minute, use_24hr, st_val, genid, type_id, viewMonth) {
		if (og.shouldSuppressCalendarGridAction()) {
			og.clearPaintedCells();
			og.clearCalendarEventPress();
			return;
		}
		var add_params;
		if (!viewMonth){
			var typeid = 1, hrs = 1, mins = 0;
			if (hour == -1 || minute == -1) {
				hour = 0;
				minute = 0;
				typeid = 2;
				ev_start_hour = ev_start_minute = durationhour = durationmin = 0;
				ev_start_day = day;
				ev_start_month = month;
				ev_start_year = year;
			} else {
				og.selectEndDateTime(day, month, year, hour, minute);
				hrs = 0;
				mins = og.getDurationMinutes();
				while (mins >= 60) {
					mins -= 60;
					hrs +=1;
				}
				if (hrs == 0) {
					hrs = 1;
					mins = 0;
				}
			}
			
			if (use_24hr) {
				st_hour = ev_start_hour;
				ampm = '';
			} else {
				if (ev_start_hour >= 12) {
					st_hour = ev_start_hour - (ev_start_hour > 12 ? 12 : 0);
					ampm = ' PM';
				} else {
					if (ev_start_hour == 0) st_hour = 12;
					else st_hour = ev_start_hour;
					ampm = ' AM';
				}
			}
			st_time = st_hour + ':' + ev_start_minute + (ev_start_minute < 10 ? '0' : '') + ampm;
			add_params = {day:ev_start_day , month: ev_start_month, year: ev_start_year, hour: ev_start_hour, minute: ev_start_minute, durationhour:hrs, durationmin:mins, start_value:st_val, start_time:st_time, type_id:typeid, view:'week'};
		}else{
			add_params = {day:day , month:month, year: year, hour: 9, minute: 0, durationhour:1, durationmin:0, start_value:st_val, start_time:'9:00', type_id:type_id, view:'month'};
		}
		
		og.render_modal_form('', {c:'event', a:'add', params: add_params});
		
		//og.clearPaintedCells();								
	}
	
	og.callEventAdd = function(day, month, year, hour, minute) {
		typeid = hour == -1 ? 2 : 1;
		if (typeid == 1) {
			og.selectEndDateTime(day, month, year, hour, minute);
			hrs = 0;
			mins = og.getDurationMinutes();
			while (mins >= 60) {
				mins -= 60;
				hrs +=1;
			}
			if (hrs == 0) {
				hrs = 1;
				mins = 0;
			}
		} else {
			hrs = mins = 0;
		}
		og.openLink(og.getUrl('event', 'add', {day:ev_start_day, month:ev_start_month, year:ev_start_year, hour:ev_start_hour, minute:ev_start_minute, durationhour:hrs, durationmin:mins, type_id:typeid}));
	}
