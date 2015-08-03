og.DateField = function(config) {
	if (!config) config = {};
	
	Ext.apply(this, config, {
		format: og.preferences['date_format'],
		altFormats: lang('date format alternatives'),
		width: 120
	});
	
	og.DateField.superclass.constructor.call(this, config);
	
};


Ext.extend(og.DateField, Ext.form.DateField,{
	onTriggerClick : function(){
        if(this.disabled){
            return;
        }
        if(this.menu == null){
            this.menu = new Ext.menu.DateMenu();
        }
        Ext.apply(this.menu.picker,  {
        	nextText: lang('next month'),
			prevText: lang('prev month'),
			todayText: lang('today'),
			monthNames: [lang('month 1'), lang('month 2'), lang('month 3'), lang('month 4'), lang('month 5'), lang('month 6'), lang('month 7'), lang('month 8'), lang('month 9'), lang('month 10'), lang('month 11'), lang('month 12')],
			dayNames:[lang('sunday'), lang('monday'), lang('tuesday'), lang('wednesday'), lang('thursday'), lang('friday'), lang('saturday')],
			monthYearText: '',
			todayTip: lang('today'),
            minDate : this.minValue,
            maxDate : this.maxValue,
            disabledDatesRE : this.ddMatch,
            disabledDatesText : this.disabledDatesText,
            disabledDays : this.disabledDays,
            disabledDaysText : this.disabledDaysText,
            format : this.format,
            showToday : this.showToday,
            minText : String.format(this.minText, this.formatDate(this.minValue)),
            maxText : String.format(this.maxText, this.formatDate(this.maxValue)),
            startDay: og.preferences['start_monday'] ? 1 : 0
        });
        this.menu.on(Ext.apply({}, this.menuListeners, {
            scope:this
        }));
        this.menu.picker.setValue(this.getValue() || new Date());
        this.menu.show(this.el, "tl-bl?");
    }
});