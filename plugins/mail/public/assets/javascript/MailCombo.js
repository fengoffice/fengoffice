og.EmailCombo = Ext.extend(og.CSVCombo, {

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
						rexp = new RegExp("^" + Ext.escapeRe(q) + "|\\s" + Ext.escapeRe(q) + "|\\s<" + Ext.escapeRe(q), 'i');
						this.store.filter(this.displayField, rexp);
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
	}
    
});
Ext.reg('emailcombo', og.EmailCombo);