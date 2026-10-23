
/**
 * A widget for selecting a date range.
 *
 * Extends Ext.Panel and accepts all config options from it.
 *
 * Additionally, it accepts the following config options:
 *
 * - `name`: The name of the form field.
 *
 * - `id`: The id of the form field.
 *
 * - `onselect`: A callback function to be called when the date range is changed.
 *
 * The widget has two Ext.form.DateField instances, one for the start date and one for the end date.
 *
 * @class
 * @extends Ext.Panel
 */
og.DateRangeField = function(config) {

	Ext.apply(this, config);
	og.DateRangeField.superclass.constructor.call(this, config);

};

Ext.extend(og.DateRangeField, Ext.Panel, {
    layout: 'table',
    border: false,

    initComponent: function() {

		let config = this.initialConfig;

        this.fromDate = this.getNewDateInput({
			id: config.id + 'From',
			name: config.id + 'From',
			onselect: config.onselect,
			highlight_selected: config.highlight_selected
		});

		this.toDate = this.getNewDateInput({
			id: config.id + 'To',
			name: config.id + 'To',
			onselect: config.onselect,
			highlight_selected: config.highlight_selected
		});

		this.separator = new Ext.form.Label({
			text: ' - ',
			style: 'margin-left: 5px; margin-right: 5px;',
		});

        this.items = [this.fromDate, this.separator, this.toDate];

        og.DateRangeField.superclass.initComponent.call(this);
    },

	/**
	 * Returns an object containing the values of the date range field.
	 * The returned object will have two properties: from and to.
	 * The values of these properties will be strings in the format 'Y-m-d' if the corresponding date fields have a value, otherwise they will be empty strings.
	 * @return {Object}
	 */
    getValue: function() {
		let fromStr = this.fromDate.getValue() ? this.fromDate.getValue().format('Y-m-d') : '';
		let toStr = this.toDate.getValue() ? this.toDate.getValue().format('Y-m-d') : '';
        return {
            from: fromStr,
            to: toStr,
        };
    },

	/**
	 * Sets the value of the date range field.
	 * @param {Object} values - Object containing two properties: from and to. The values can be either a Date object or a string in the format 'Y-m-d'.
	 */
    setValue: function(values) {
		if (typeof values == 'string') {
			values = Ext.util.JSON.decode(values);
		}
		if (typeof values.from == 'string' && values.from != '') {
			values.from = this.parseMysqlDate(values.from);
			if (this.highlight_selected) {
				og.highlight_selected_extjs_filter(this.fromDate.id, values.from);
			}
		}
		if (typeof values.to == 'string' && values.to != '') {
			values.to = this.parseMysqlDate(values.to);
			if (this.highlight_selected) {
				og.highlight_selected_extjs_filter(this.toDate.id, values.to);
			}
		}
		this.fromDate.setValue(values.from);
		this.toDate.setValue(values.to);
	},

	parseMysqlDate: function(str) {
		if (!str) return null;

		var parts = str.split('-');
		return new Date(
			parseInt(parts[0], 10), 
			parseInt(parts[1], 10) - 1,
			parseInt(parts[2], 10)
		);
	},

	/**
	 * Create a new og.DateField with default config and return it.
	 *
	 * @param {Object} config - config object for the new date field
	 * @param {string} config.name - name of the new date field
	 * @param {string} config.id - id of the new date field
	 * @param {function} [config.onselect] - callback function to be called when the date value changes
	 * @return {og.DateField} - the newly created date field
	 */
	getNewDateInput: function(config) {
		let input = new og.DateField({
			displayField : 'text',
			emptyText : og.preferences['date_format_tip'],
			name : config.name,
			id : config.id,
			value : '',
			allowBlank : true,
			listeners : {
				'change' : function(A, newValue, oldValue) {
					if (config.onselect && typeof(config.onselect) == 'function') {
						config.onselect(newValue);
					}
				}
			},
			menuListeners : {
				select : function(cmp, newValue) {
					this.setValue(newValue);
					if (config.onselect && typeof(config.onselect) == 'function') {
						config.onselect(cmp, newValue);
					}
				}
			}
		});
		return input;
	}
});

Ext.reg('daterangefield', og.DateRangeField);


