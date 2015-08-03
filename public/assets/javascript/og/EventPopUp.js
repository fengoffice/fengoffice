

og.EventPopUp = function(data,config) {
	if (!config) config = {};
        
    og.EventPopUp.superclass.constructor.call(this, Ext.apply(config, {
		y: 220,
		width: 350,
		height: 230,
		id: 'add-event',
		layout: 'border',
		modal: true,
		resizable: false,
		closeAction: 'hide',
		iconCls: 'ico-calendar',
		title: data.title,
		border: false,
		focus : function() {
            Ext.get('name').focus();
        },
		buttons: [{
			text: lang('add event'),
			handler: this.accept,
			scope: this
		},{
			text: lang('cancel'),
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',				
				items: [
					this.form = new Ext.FormPanel({
						id: data.genid + '-ev_popup_form',
				        labelWidth: 75, // label settings here cascade unless overridden
				        frame:false,
				        height: 140,
				        url: '',
				        bodyStyle:'padding:20px 20px 0',
				        defaultType: 'textfield',
						border:false,
						bodyBorder: false,
				        items: [
				        	{
				                fieldLabel: lang('name'),
				                name: 'event[name]',
				                id: 'name',
				                allowBlank:false,
				                enableKeyEvents: true,
				        		style: {width: '200px'},
				                blankText: lang('this field is required'),
				                listeners: {specialkey: function(field, ev){
				        			if (ev.getKey() == ev.ENTER) Ext.getCmp('add-event').accept();
				        		}}
				            },
				            {
				            	name: 'event[start_time]',
				                id: 'start_time',
				                xtype: 'timefield',
				                width: 80,
				                fieldLabel: lang('event start time'),
				                format: data.time_format,
				                editable: false,
				                value: data.start_time
							},
				            {
				            	name: 'event[duration]',
				                id: 'duration',
				                xtype: 'timefield',
				                width: 60,
				                fieldLabel: lang('duration'),
				                format: 'G:i',
				                editable: false,
				                value: data.durationhour + ':' + (data.durationmin < 10 ? '0':'') + data.durationmin
							},
							{
				            	xtype: 'checkbox',
				                name: 'event[all_day_event]',
				                id: 'all_day_event',
				                fieldLabel: lang('all day event'),
				                value: (data.type_id == 2)
				            },
				            {
				            	xtype: 'hidden',
				                name: 'members',
				                cls: 'ev_popup_members',
				                id: data.genid + 'ev_popup_members',
				                value: ''
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[start_day]',
				                id: 'day',
				                value: data.day
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[start_month]',
				                id: 'month',
				                value: data.month
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[start_year]',
				                id: 'year',
				                value: data.year
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[hour]',
				                id: 'hour',
				                value: data.hour
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[minute]',
				                id: 'min',
				                value: data.minute
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[type_id]',
				                id: 'type_id',
				                value: data.type_id
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[durationhour]',
				                id: 'durationhour',
				                value: data.durationhour
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[durationmin]',
				                id: 'durationmin',
				                value: data.durationmin
				            },
				            {
				            	xtype: 'hidden',
				                name: 'event[start_value]',
				                id: 'start_value',
				                value: data.start_value
				            },
				            {
				            	xtype: 'hidden',
				                id: 'hide_calendar_toolbar',
				                value: data.hide_calendar_toolbar
				            },
				            {
				            	xtype: 'hidden',
				                name: 'view',
				                id: 'view',
				                value: data.view
				            }
				        ]
				    })
				]
			},{
				region: 'south',
				height: 20,
		        html:"<div style='width:100%; text-align:right; padding-right:8px'><a href='#' onclick=\"og.EventPopUp.goToEdit()\">" + lang('edit event details') + "</a></div>"
			}
		]
	}));
    
}


Ext.extend(og.EventPopUp, Ext.Window, {
	accept: function() {		
		duration_split = Ext.getCmp('duration').getValue().split(':');
		Ext.getCmp('durationhour').setValue(duration_split[0]);
		Ext.getCmp('durationmin').setValue(duration_split[1]);
		
		this.hide();
		og.openLink(og.getUrl('event', 'add'),{post:'popup=true&event[start_day]='+Ext.getCmp('day').getValue()+'&event[start_month]='+Ext.getCmp('month').getValue()+'&event[start_year]='+Ext.getCmp('year').getValue()+'&event[hour]='+Ext.getCmp('hour').getValue()+'&event[minute]='+Ext.getCmp('min').getValue()+'&event[type_id]='+Ext.getCmp('type_id').getValue()+'&event[durationhour]='+Ext.getCmp('durationhour').getValue()+'&event[durationmin]='+Ext.getCmp('durationmin').getValue()+'&view='+Ext.getCmp('view').getValue()+'&event[start_value]='+Ext.getCmp('start_value').getValue()+'&event[start_time]='+Ext.getCmp('start_time').getValue()+'&event[name]='+Ext.getCmp('name').getValue()});
	},
	
	cancel: function() {
		this.hide();
	}
});

og.EventPopUp.show = function(callback, data, scope) {
	if (!this.dialog) {
		this.dialog = new og.EventPopUp(data);
	}
	this.the_data = data;
	this.dialog.setTitle(data.title);
	Ext.getCmp('year').setValue(data.year);	
	Ext.getCmp('month').setValue(data.month);	
	Ext.getCmp('day').setValue(data.day);
	Ext.getCmp('hour').setValue(data.hour);	
	Ext.getCmp('min').setValue(data.minute);	
	Ext.getCmp('type_id').setValue(data.type_id);	
	Ext.getCmp('name').setValue('');
	Ext.getCmp('durationhour').setValue(data.durationhour);
	Ext.getCmp('durationmin').setValue(data.durationmin);
	Ext.getCmp('start_value').setValue(data.start_value);
	Ext.getCmp('start_time').setValue(data.start_time);
	Ext.getCmp('view').setValue(data.view);
	Ext.getCmp('hide_calendar_toolbar').setValue(data.hide_calendar_toolbar);
	Ext.getCmp('duration').setValue(data.durationhour + ':' + (data.durationmin < 10 ? '0':'') + data.durationmin);
	this.dialog.purgeListeners();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
	Ext.getCmp('all_day_event').on('check', function(scope, checked) {
		if (checked) {
			Ext.getCmp('type_id').setValue('2');
			Ext.getCmp('start_time').disable();
			Ext.getCmp('duration').disable();
		} else {
			Ext.getCmp('type_id').setValue('1');
			Ext.getCmp('start_time').enable();
			Ext.getCmp('duration').enable();
		}
	});
	Ext.getCmp('all_day_event').setValue(data.type_id == 2 ? true : false);	
	Ext.getCmp('name').focus();
}


og.EventPopUp.goToEdit = function (){
	var sub = Ext.getCmp('name').getValue();	
	var st_time = Ext.getCmp('start_time').getValue();
	var ev_type = Ext.getCmp('type_id').getValue();
	var data = this.the_data;
	
	duration_split = Ext.getCmp('duration').getValue().split(':');
	data.durationhour = duration_split[0];
	data.durationmin = duration_split[1];
	
	var add_params = {name: sub, day:data.day , month: data.month, year: data.year, hour: data.hour, minute: data.minute, durationhour:data.durationhour, durationmin:data.durationmin, start_value:data.start_value, start_time:st_time, type_id:ev_type, view:data.view};
	og.render_modal_form('', {c:'event', a:'add', params: add_params});
	this.dialog.hide();	
}
