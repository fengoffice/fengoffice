og.GooProxy = function(config) {
    og.GooProxy.superclass.constructor.call(this);

    Ext.apply(this, config);
};

Ext.extend(og.GooProxy, Ext.data.DataProxy, {
    load : function(params, reader, callback, scope, arg) {
        if (this.fireEvent("beforeload", this, params) !== false) {
        	og.openLink(this.url, {
        		get: params,
        		callback: this.loadResponse,
        		scope: this,
        		request: {
        			callback: callback,
        			scope: scope,
        			arg: arg
        		},
        		reader: reader,
        		timeout: this.timeout
        	});
        } else {
            callback.call(scope||this, null, arg, false);
        }
    },

    // private
    loadResponse : function(success, data, options) {
        if(!success){
            this.fireEvent("loadexception", this, options, data);
            options.request.callback.call(options.request.scope, null, options.request.arg, false);
            return;
        }
        var result;
        try {
            result = options.reader.readRecords(data);
        }catch(e){
            this.fireEvent("loadexception", this, options, data, e);
            options.request.callback.call(options.request.scope, null, options.request.arg, false);
            return;
        }
        this.fireEvent("load", this, result, options.request.arg);
        options.request.callback.call(options.request.scope, result, options.request.arg, true);
    },
    
    // private
    update : function(dataSet){
        
    },
    
    // private
    updateResponse : function(dataSet){
        
    }
});