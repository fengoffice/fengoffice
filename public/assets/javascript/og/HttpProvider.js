/**
 * Ext.ux.HttpProvider extension
 *
 * @author    Ing. Jozef Sakalos
 * @copyright (c) 2008, Ing. Jozef Sakalos
 *
 * @license Ext.ux.HttpProvider is licensed under the terms of
 * the Open Source LGPL 3.0 license.  Commercial use is permitted to the extent
 * that the code/component(s) do NOT become part of another Open Source or Commercially
 * licensed development library or toolkit without explicit permission.
 * 
 * License details: http://www.gnu.org/licenses/lgpl.html
 */

/**
 * @class og.HttpProvider
 * @extends Ext.state.Provider
 * @constructor
 * @param {Object} config Configuration object
 */
// {{{
og.HttpProvider = function(config) {

    this.addEvents(
        /**
         * @event readsuccess
         * Fires after state has been successfully received from server and restored
         * @param {HttpProvider} this
         */
         'readsuccess'
        /**
         * @event readfailure
         * Fires in the case of an error when attempting to read state from server
         * @param {HttpProvider} this
         */
        ,'readfailure'
        /**
         * @event savesuccess
         * Fires after the state has been successfully saved to server
         * @param {HttpProvider} this
         */
        ,'savesuccess'
        /**
         * @event savefailure
         * Fires in the case of an error when attempting to save state to the server
         * @param {HttpProvider} this
         */
        ,'savefailure'
    );

    // call parent 
    og.HttpProvider.superclass.constructor.call(this);

    Ext.apply(this, config, {
        // defaults
         delay:750 // buffer changes for 750 ms
        ,dirty:false
        ,started:false
        ,autoStart:true
        ,autoRead:true
        ,user:'user'
        ,id:1
        ,session:'session'
        ,logFailure:false
        ,logSuccess:false
        ,queue:[]
        ,url:'.'
        ,readUrl:undefined
        ,saveUrl:undefined
        ,method:'post'
        ,saveBaseParams:{}
        ,readBaseParams:{}
        ,paramNames:{
             id:'id'
            ,name:'name'
            ,value:'value'
            ,user:'user'
            ,session:'session'
            ,data:'data'
        }
    }); // eo apply

    if(this.autoRead) {
        this.readState();
    }

    this.dt = new Ext.util.DelayedTask(this.submitState, this);
    if(this.autoStart) {
        this.start();
    }
}; // eo constructor
// }}}

Ext.extend(og.HttpProvider, Ext.state.Provider, {

    // localizable texts
     saveSuccessText:'Save Success'
    ,saveFailureText:'Save Failure'
    ,readSuccessText:'Read Success'
    ,readFailureText:'Read Failure'
    ,dataErrorText:'Data Error'

    // {{{
    /**
     * Initializes state from the passed state object or array.
     * This method can be called early during page load having the state Array/Object
     * retrieved from database by server.
     * @param {Array/Object} state State to initialize state manager with
     */
    ,initState:function(state) {
        if(state instanceof Array) {
            Ext.each(state, function(item) {
                this.state[item.name] = this.decodeValue(item.value);
            }, this);
        }
        else {
            this.state = state ? state : {};
        }
    } // eo function initState
    // }}}
    // {{{
    /**
     * Sets the passed state variable name to the passed value and queues the change
     * @param {String} name Name of the state variable
     * @param {Mixed} value Value of the state variable
     */
    ,set:function(name, value) {
        if(!name) {
            return;
        }

        this.queueChange(name, value);

    } // eo function set
    // }}}
    // {{{
    /**
     * Starts submitting state changes to server
     */
    ,start:function() {
        this.dt.delay(this.delay);
        this.started = true;
    } // eo function start
    // }}}
    // {{{
    /**
     * Stops submitting state changes
     */
    ,stop:function() {
        this.dt.cancel();
        this.started = false;
    } // eo function stop
    // }}}
    // {{{
    /**
     * private, queues the state change if state has changed
     */
    ,queueChange:function(name, value) {
        var changed = undefined === this.state[name] || this.state[name] !== value;
        var o = {};
        var i;
        var found = false;
        if(changed) {
            o[this.paramNames.name] = name;
            o[this.paramNames.value] = this.encodeValue(value);
            for(i = 0; i < this.queue.length; i++) {
                if(this.queue[i].name === o.name) {
                    this.queue[i] = o;
                    found = true;
                }
            }
            if(false === found) {
                this.queue.push(o);
            }
            this.dirty = true;
        }
        return changed;
    } // eo function bufferChange
    // }}}
    // {{{
    /**
     * private, submits state to server by asynchronous Ajax request
     */
    ,submitState:function() {
        if(!this.dirty) {
            this.dt.delay(this.delay);
            return;
        }
        this.dt.cancel();

        var o = {
             url:this.saveUrl || this.url
            ,method:this.method
            ,scope:this
            ,success:this.onSaveSuccess
            ,failure:this.onSaveFailure
            ,queue:og.clone(this.queue)
            ,params:{}
        };

        var params = Ext.apply({}, this.saveBaseParams);
        params[this.paramNames.id] = this.id;
        params[this.paramNames.user] = this.user;
        params[this.paramNames.session] = this.session;
        params[this.paramNames.data] = Ext.encode(o.queue);

        Ext.apply(o.params, params);

        // be optimistic
        this.dirty = false;

        Ext.Ajax.request(o);
    } // eo function submitState
    // }}}
    // {{{
    /**
     * Clears the state variable
     * @param {String} name Name of the variable to clear
     */
    ,clear:function(name) {
        this.set(name, undefined);
    } // eo function clear
    // }}}
    // {{{
    /**
     * private, save success callback
     */
    ,onSaveSuccess:function(response, options) {
        if(this.started) {
            this.start();
        }
        var o = {};
        try {o = Ext.decode(response.responseText);}
        catch(e) {
            if(true === this.logFailure) {
                this.log(this.saveFailureText, e, response);
            }
            this.dirty = true;
            return;
        }
        if(true !== o.success) {
            if(true === this.logFailure) {
                this.log(this.saveFailureText, o, response);
            }
            this.dirty = true;
        }
        else {
            Ext.each(options.queue, function(item) {
                var name = item[this.paramNames.name];
                var value = this.decodeValue(item[this.paramNames.value]);

                if(undefined === value || null === value) {
                    og.HttpProvider.superclass.clear.call(this, name);
                }
                else {
                    // parent sets value and fires event
                    og.HttpProvider.superclass.set.call(this, name, value);
                }
            }, this);
            if(false === this.dirty) {
                this.queue = [];
            }
            else {
                var i, j, found;
                for(i = 0; i < options.queue.length; i++) {
                    found = false;
                    for(j = 0; j < this.queue.length; j++) {
                        if(options.queue[i].name === this.queue[j].name) {
                            found = true;
                            break;
                        }
                    }
                    if(true === found && this.encodeValue(options.queue[i].value) === this.encodeValue(this.queue[j].value)) {
                        delete(this.queue[j]);
                    }
                }
            }
            if(true === this.logSuccess) {
                this.log(this.saveSuccessText, o, response);
            }
            this.fireEvent('savesuccess', this);
        }
    } // eo function onSaveSuccess
    // }}}
    // {{{
    /**
     * private, save failure callback
     */
    ,onSaveFailure:function(response, options) {
        if(true === this.logFailure) {
            this.log(this.saveFailureText, response);
        }
        if(this.started) {
            this.start();
        }
        this.dirty = true;
        this.fireEvent('savefailure', this);
    } // eo function onSaveFailure
    // }}}
    // {{{
    /**
     * private, read state callback
     */
    ,onReadFailure:function(response, options) {
        if(true === this.logFailure) {
            this.log(this.readFailureText, response);
        }
        this.fireEvent('readfailure', this);

    } // eo function onReadFailure
    // }}}
    // {{{
    /**
     * private, read success callback
     */
    ,onReadSuccess:function(response, options) {
        var o = {}, data;
        try {o = Ext.decode(response.responseText);}
        catch(e) {
            if(true === this.logFailure) {
                this.log(this.readFailureText, e, response);
            }
            return;
        }
        if(true !== o.success) {
            if(true === this.logFailure) {
                this.log(this.readFailureText, o, response);
            }
        }
        else {
            try {data = Ext.decode(o[this.paramNames.data]);}
            catch(ex) {
                if(true === this.logFailure) {
                    this.log(this.dataErrorText, o, response);
                }
                return;
            }
            if(!(data instanceof Array) && true === this.logFailure) {
                this.log(this.dataErrorText, data, response);
                return;
            }
            Ext.each(data, function(item) {
                this.state[item[this.paramNames.name]] = this.decodeValue(item[this.paramNames.value]);
            }, this);
            this.queue = [];
            this.dirty = false;
            if(true === this.logSuccess) {
                this.log(this.readSuccessText, data, response);
            }
            this.fireEvent('readsuccess', this);
        }
    } // eo function onReadSuccess
    // }}}
    // {{{
    /**
     * Reads saved state from server by sending asynchronous Ajax request and processing the response
     */
    ,readState:function() {
        var o = {
             url:this.readUrl || this.url
            ,method:this.method
            ,scope:this
            ,success:this.onReadSuccess
            ,failure:this.onReadFailure
            ,params:{}
        };

        var params = Ext.apply({}, this.readBaseParams);
        params[this.paramNames.id] = this.id;
        params[this.paramNames.user] = this.user;
        params[this.paramNames.session] = this.session;

        Ext.apply(o.params, params);
        Ext.Ajax.request(o);
    } // eo function readState
    // }}}
    // {{{
    /**
     * private, logs errors or successes
     */
    ,log:function() {
        if(console) {
            console.log.apply(console, arguments);
        }
    } // eo log
    // }}}

});