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
	
	/*******************************************
		DRAGGING & RESIZING
	*******************************************/

	// month view
	og.monthViewEventDD = Ext.extend(Ext.dd.DDProxy, {
	    startDrag: function(x, y) {
	        var dragEl = Ext.get(this.getDragEl());
	        var el = Ext.get(this.getEl());

	        dragEl.applyStyles({border:'','z-index':2000});
	        dragEl.update(el.dom.innerHTML);
	        if (el.getStyle('background-color') == 'transparent') {
	        	dragEl.setStyle('background-color', '#99CC66');
	        }
	        dragEl.applyStyles('opacity: 0.5; filter: alpha(opacity = 50);');
	    },
		onDragOver: function(e, targetId) {
			var target = Ext.get(targetId);
			if (target) this.lastTarget = target;
		},
		onDragOut: function(e, targetId) {
			var target = Ext.get(targetId);
			if (target) this.lastTarget = target;
	    },
		endDrag: function() {
			var ok = true;
			if (this.config.dragData.is_repe) {
				ok = confirm(lang('confirm repeating event edition'));
			}
			if (!ok) return;
			date = null;
			for (i=0; i<og.ev_cell_dates.length; i++) {
				if (og.ev_cell_dates[i].key == this.lastTarget.id) {
					date = og.ev_cell_dates[i];
					break;
				}
			}
			if (date != null) {
				var el = Ext.get(this.getEl());
				var parent = Ext.get(date.key);
				parent.appendChild(el);
			
				this.config.dragData.day = date.day;
				this.config.dragData.month = date.month;
				this.config.dragData.year = date.year;
				this.config.fn.apply(this.config.scope || window, [this, this.config.dragData]);
			} else {
				og.err('Invalid grid cell');
			}
		}
	});

	// week and day views
	og.eventDD = Ext.extend(Ext.dd.DDProxy, {
	    startDrag: function(x, y) {
	        var dragEl = Ext.get(this.getDragEl());
	        var el = Ext.get(this.getEl());

	        dragEl.applyStyles({border:'','z-index':2000});
	        dragEl.update(el.dom.innerHTML);
	        dragEl.applyStyles('opacity: 0.5; filter: alpha(opacity = 50);');
	    },
		onDragOver: function(e, targetId) {
			var target = Ext.get(targetId);
			if (target) {
				this.lastTarget = target;
			}
		},
		onDragOut: function(e, targetId) {
			var target = Ext.get(targetId);
			if (target) {
				this.lastTarget = target;
			}
	    },
		endDrag: function() {
			var el = Ext.get(this.getEl());
			if(this.lastTarget) {
				var str_temp = this.lastTarget.id.split	('_');
				isAllDay = (this.lastTarget.id.indexOf('alldayeventowner_') >= 0) || (this.lastTarget.id.indexOf('alldaycelltitle_') >= 0);
				var ok = true;
				if (this.config.dragData.is_repe) {
					ok = confirm(lang('confirm repeating event edition'));
				}
				if (!ok) return;
				
				if (isAllDay) {
					var parent = Ext.get('alldayeventowner_'+str_temp[1]);
					parent.appendChild(el);
					og.reorganizeAllDayGrid();
				} else {
					var grid = Ext.get('grid');
					var parent = Ext.get('eventowner');
					
					var lt = Ext.get(this.lastTarget);
					var top = lt.getTop() - parent.getTop();
					var left = 100 * (lt.getLeft() - parent.getLeft() + 3) / grid.getWidth();
					
					el.applyStyles('top:'+top+'px;left:'+left+'%;');				
					parent.appendChild(el);
					
					/*var cont = Ext.get('gridcontainer');
					if (cont.getTop() + cont.getHeight() < el.getTop() + el.getHeight()) {
						style = 'height:'+ (cont.getTop() + cont.getHeight() - el.getTop() - 1) +'px';
						el.applyStyles(style);
						Ext.get(this.getDragEl()).update(el.dom.innerHTML);
					}*/
				}	
				if(this.config.fn && 'function' === typeof this.config.fn) {
					if (isAllDay) {
						date = og.ev_cell_dates[str_temp[1]];
					} else {
						date = og.ev_cell_dates[str_temp[0].substr(1)];
					}
					if (date) {
						this.config.dragData.day = date.day;
						this.config.dragData.month = date.month;
						this.config.dragData.year = date.year;
						if (!isAllDay) {
							this.config.dragData.hour = Math.floor(str_temp[1] / 2);
							this.config.dragData.min = (str_temp[1] % 2 == 0 ? 0 : 30);
						}
						this.config.fn.apply(this.config.scope || window, [this, this.config.dragData]);
					} else {
						og.err('Invalid grid cell');
					}
				}
			}
		}
		
	});
	
	og.createEventDrag = function(div_id, obj_id, is_repetitive, origdate, type, isAllday, dropzone) {
		var obj_div = Ext.get(div_id);
		
		obj_div.dd = new og.eventDD(div_id, dropzone, {
			dragData: {id: obj_id, is_repe: is_repetitive, orig_date: origdate},
			scope: this,
			isTarget:false,
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
	}
	
	og.createMonthlyViewDrag = function(div_id, obj_id, is_repetitive, type, origdate) {
		var obj_div = Ext.get(div_id);
		obj_div.dd = new og.monthViewEventDD(div_id, 'ev_dropzone', {
			dragData: {id: obj_id, is_repe: is_repetitive, orig_date: origdate},
			scope: this,
			isTarget:false,
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
