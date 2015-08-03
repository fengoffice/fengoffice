/*
var store = new Ext.data.SimpleStore({
        fields: ['abbr', 'state', 'nick'],
        data : Ext.exampledata.states // from states.js
    });
var combo = new og.CSVCombo({
        store: store,
        displayField:'state',
        typeAhead: true,
        mode: 'local',
        forceSelection: true,
        triggerAction: 'all',
        emptyText:'Select a state...',
        selectOnFocus:true,
        applyTo: 'local-states'
    });
*/
og.CSVCombo = Ext.extend(Ext.form.ComboBox, {

	onTypeAhead: function() {
		if (this.store.getCount() > 0) {
			// TODO

		}
    },
    
	onLoad: function() {
		if (!this.hasFocus) {
			return;
		}
		if (this.store.getCount() > 0) {
			this.expand();
			this.restrictHeight();
			if (this.lastQuery == this.allQuery) {
				if (this.editable) {
					//this.el.dom.select();
				}
				if (!this.selectByValue(this.value, true)) {
					this.select(0, true);
				}
			} else {
				this.selectNext();
				if (this.typeAhead && this.lastKey != Ext.EventObject.BACKSPACE && this.lastKey != Ext.EventObject.DELETE) {
					this.taTask.delay(this.typeAheadDelay);
				}
			}
		} else {
			this.onEmptyResults();
		}
		this.el.on('change', function() {
			this.setValue(this.getRawValue());
		}, this);
		//this.el.focus();
	},

	onSelect: function(record, index) {
		if (this.fireEvent('beforeselect', this, record, index) !== false) {
			var val = this.getRawValue() || "";
			var l = val.lastIndexOf(",");
			val = val.substring(0, l + 1) + " " + record.data[this.valueField || this.displayField] + ", ";
			this.setValue(val);
			this.collapse();
			this.fireEvent('select', this, record, index);
		}
	},

	initQuery: function() {
		var val = this.getRawValue() || "";
		var l = val.lastIndexOf(",");
		val = val.substring(l + 1).trim();
		if (val) this.doQuery(val); else this.list.hide();
	},

	doQuery: function(q, forceAll) {
		if (q === undefined || q === null) {
			q = '';
		}
		var qe = {
			query: q,
			forceAll: forceAll,
			combo: this,
			cancel:false
		};
		if (this.fireEvent('beforequery', qe)===false || qe.cancel) {
			return false;
		}
		q = qe.query;
		forceAll = qe.forceAll;
		if (forceAll === true || (q.length >= this.minChars)) {
			if (this.lastQuery !== q) {
				this.lastQuery = q;
				if (this.mode == 'local') {
					this.selectedIndex = -1;
					if (forceAll) {
						this.store.clearFilter();
					} else {
						this.store.filter(this.displayField, q);
					}
					this.onLoad();
				} else {
					this.store.baseParams[this.queryParam] = q;
					this.store.load({
						params: this.getParams(q)
					});
					this.expand();
				}
			} else {
				this.selectedIndex = -1;
				this.onLoad();
			}
		}
	},

	onTriggerClick: function() {
		if (this.disabled) {
			return;
		}
		if (this.isExpanded()) {
			this.collapse();
			this.el.focus();
		} else {
			this.onFocus({});
			if(this.triggerAction == 'all') {
				this.doQuery(this.allQuery, true);
			} else {
				var val = this.getRawValue() || "";
				var l = val.lastIndexOf(",");
				val = val.substring(l + 1).trim();
				if (val) this.doQuery(val); else this.list.hide();
			}
        	this.el.focus();
		}
	},
	
	expand : function(){
        if(this.isExpanded() || !this.hasFocus){
            return;
        }
        var lw = this.el.getWidth();
        this.list.setWidth(lw);
        this.innerList.setWidth(lw - this.list.getFrameWidth('lr'));
        this.list.alignTo(this.wrap, this.listAlign);
        this.list.show();
        this.innerList.setOverflow('auto'); // necessary for FF 2.0/Mac
        Ext.getDoc().on('mousewheel', this.collapseIf, this);
        Ext.getDoc().on('mousedown', this.collapseIf, this);
        this.fireEvent('expand', this);
    }
});
Ext.reg('csvcombo', og.CSVCombo);