og.MemberCombo = Ext.extend(Ext.form.ComboBox, {

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
						rexp = new RegExp(Ext.escapeRe(q), 'i');
						this.store.filter(this.searchField, rexp);
					}
					this.onLoad();
				} else {
					if (q.length >= this.minChars) {
						this.store.baseParams[this.queryParam] = q;
						this.store.load({
							params: this.getParams(q)
						});
					}
					this.expand();
				}
			} else {
				this.selectedIndex = -1;
				this.onLoad();
			}
		}
	}
    
});
Ext.reg('membercombo', og.MemberCombo);