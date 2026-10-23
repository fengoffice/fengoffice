var moment = require('moment');

/**
 * Parse a calendar date string from the server (YYYY-MM-DD) as local midnight.
 * Using moment(iso) without a format treats some ISO strings as UTC and shifts the day in non-UTC zones.
 */
function parseCalendarYMD(value) {
    if (!value || typeof value !== 'string') {
        return moment.invalid();
    }
    const m = moment(value, 'YYYY-MM-DD', true);
    return m.isValid() ? m.startOf('day') : moment.invalid();
}

function isoYmdToDisplay(iso, momentFmt) {
    const m = parseCalendarYMD(iso);
    return m.isValid() ? m.format(momentFmt) : '';
}

/**
 * Base class for widgets with date filtering functionality
 */
class DateFilterWidgetBase {
    constructor(props, widgetId) {
        this.widgetId = widgetId;
        this.state = {
            // ...existing code...
            currentYearStart: props.data.currentYearStart,
            currentYearEnd: props.data.currentYearEnd,
            today: props.data.today,
            hasSpecificMembers: props.data.hasSpecificMembers || false,
            selectedDateRange: props.data.savedDateRange || 'ytd',
            savedCustomFrom: props.data.savedCustomFrom || '',
            savedCustomTo: props.data.savedCustomTo || '',
            saveConfigUrl: props.data.saveConfigUrl || '',
            momentDateFormat: props.data.dateFormat || 'MM/DD/YYYY',
            dateInputPlaceholder: props.data.dateInputPlaceholder || '',
            buttonLabels: props.data.buttonLabels || {
                ytd: 'YTD',
                thisYear: 'This Year', 
                thisQuarter: 'This Quarter',
                thisMonth: 'This Month',
                custom: 'Custom',
                alltime: 'All Time',
                from: 'From',
                to: 'To'
            }
        };
        
        this.filterDataByDateRange = this.filterDataByDateRange.bind(this);
        this.handleDateRangeChange = this.handleDateRangeChange.bind(this);
        this.saveUserConfig = this.saveUserConfig.bind(this);
        this.parseCustomInputToIso = this.parseCustomInputToIso.bind(this);
        this.formatIsoToDisplay = this.formatIsoToDisplay.bind(this);
    }

    /** Parse custom range field: user's display format, or legacy YYYY-MM-DD saved value. */
    parseCustomInputToIso(raw) {
        const mf = this.state.momentDateFormat;
        if (raw == null) return '';
        const t = String(raw).trim();
        if (t === '') return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(t)) return t;
        const m = moment(t, mf, true);
        return m.isValid() ? m.format('YYYY-MM-DD') : '';
    }

    formatIsoToDisplay(iso) {
        if (!iso) return '';
        return isoYmdToDisplay(iso, this.state.momentDateFormat);
    }

    // Convert cumulative data back to daily amounts for proper filtering
    convertCumulativeToDaily(cumulativeData) {
        if (!cumulativeData || cumulativeData.length === 0) return [];
        
        const dailyData = [];
        let prevBudget = 0;
        let prevEarned = 0;
        
        cumulativeData.forEach(item => {
            const dailyBudget = item.total_budget - prevBudget;
            const dailyEarned = item.total_earned - prevEarned;
            
            dailyData.push({
                date: item.date,
                dateKey: item.dateKey,
                daily_budget: dailyBudget,
                daily_earned: dailyEarned
            });
            
            prevBudget = item.total_budget;
            prevEarned = item.total_earned;
        });
        
        return dailyData;
    }

    saveUserConfig(optionName, optionValue) {
        if (!this.state.saveConfigUrl) {
            console.warn('Save config URL not available');
            return;
        }

        og.openLink(this.state.saveConfigUrl, {
            post: { option_name: optionName, option_value: optionValue },
            preventPanelLoad: true,
            silent: true,
            postProcess: function(ok) {
                if (!ok) console.error('Error saving config');
            }
        });
    }

    handleDateRangeChange(range, configPrefix = 'financials_widget', reactComponent) {
        // Update the component state instead of helper state
        if (reactComponent && reactComponent.setState) {
            reactComponent.setState({ selectedDateRange: range });
        }
        
        this.filterDataByDateRange(range, reactComponent);
        
        // Only save preferences when specific members are selected
        if (this.state.hasSpecificMembers) {
            this.saveUserConfig(`${configPrefix}_date_range`, range);
            
            // Save custom date values when range is custom
            if (range === 'custom') {
                const dateFrom = document.getElementById(`date-from-${this.widgetId}`);
                const dateTo = document.getElementById(`date-to-${this.widgetId}`);
                
                const fromIso = dateFrom ? this.parseCustomInputToIso(dateFrom.value) : '';
                const toIso = dateTo ? this.parseCustomInputToIso(dateTo.value) : '';
                if (fromIso) {
                    this.saveUserConfig(`${configPrefix}_custom_from`, fromIso);
                }
                if (toIso) {
                    this.saveUserConfig(`${configPrefix}_custom_to`, toIso);
                }
            }
        }
    }

    filterDataByDateRange(range, reactComponent) {
        let startDate, endDate;
        const today = parseCalendarYMD(this.state.today);
        
        switch(range) {
            case 'ytd':
                startDate = parseCalendarYMD(this.state.currentYearStart);
                endDate = today;
                break;
            case 'year':
                startDate = parseCalendarYMD(this.state.currentYearStart);
                endDate = parseCalendarYMD(this.state.currentYearEnd);
                break;
            case 'quarter':
                startDate = today.clone().startOf('quarter');
                endDate = today.clone().endOf('quarter');
                break;
            case 'month':
                startDate = today.clone().startOf('month');
                endDate = today.clone().endOf('month');
                break;
            case 'alltime':
                // For 'All Time', use the full data range - let the component handle this
                if (reactComponent && reactComponent.performAllTimeFiltering) {
                    reactComponent.performAllTimeFiltering();
                    return;
                }
                break;
            case 'custom':
                const dateFrom = document.getElementById(`date-from-${this.widgetId}`);
                const dateTo = document.getElementById(`date-to-${this.widgetId}`);
                const fromIso = dateFrom ? this.parseCustomInputToIso(dateFrom.value) : '';
                const toIso = dateTo ? this.parseCustomInputToIso(dateTo.value) : '';
                if (fromIso && toIso) {
                    startDate = parseCalendarYMD(fromIso);
                    endDate = parseCalendarYMD(toIso);
                } else if (this.state.savedCustomFrom && this.state.savedCustomTo) {
                    startDate = parseCalendarYMD(this.state.savedCustomFrom);
                    endDate = parseCalendarYMD(this.state.savedCustomTo);
                } else {
                    return;
                }
                break;
            default:
                startDate = parseCalendarYMD(this.state.currentYearStart);
                endDate = today;
        }

        // Call the component's performDateFiltering method
        if (reactComponent && reactComponent.performDateFiltering) {
            reactComponent.performDateFiltering(startDate, endDate);
        }
    }

    renderDateSelector(savedDateRange, savedCustomFrom, savedCustomTo, currentYearStart, today) {
        const React = require('react');
        const labels = this.state.buttonLabels;
        const mf = this.state.momentDateFormat;
        const fromIso = (savedDateRange == 'custom' && savedCustomFrom) ? savedCustomFrom : currentYearStart;
        const toIso = (savedDateRange == 'custom' && savedCustomTo) ? savedCustomTo : today;
        const fromDisplay = isoYmdToDisplay(fromIso, mf) || '';
        const toDisplay = isoYmdToDisplay(toIso, mf) || '';
        const ph = this.state.dateInputPlaceholder || undefined;
        const minNative = !this.state.hasSpecificMembers ? currentYearStart : '2000-01-01';
        const maxNative = !this.state.hasSpecificMembers
            ? (this.state.currentYearEnd || `${String(currentYearStart).slice(0, 4)}-12-31`)
            : '2050-12-31';
        const dateNativeTitle = (typeof lang === 'function') ? lang('select a date') : '';

        // Helper function to capitalize each word
        const capitalize = (str) => str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

        const renderCombinedDateField = (textId, nativeId, displayValue, isoValue) => React.createElement("div", {
            /* Do not add x-form-field-wrap: global CSS gives it padding-right:17px for Ext triggers */
            className: "widget-date-combined",
            key: textId,
            style: {
                display: "inline-flex",
                alignItems: "stretch",
                verticalAlign: "middle",
                boxSizing: "border-box",
                minHeight: "28px",
                border: "1px solid #ccc",
                borderRadius: "4px",
                background: "#fff",
                overflow: "hidden"
            }
        },
            React.createElement("input", {
                type: "text",
                id: textId,
                className: "widget-custom-date-text",
                autoComplete: "off",
                placeholder: ph,
                defaultValue: displayValue,
                style: {
                    flex: "1 1 auto",
                    minWidth: "6em",
                    width: "10em",
                    maxWidth: "14em",
                    boxSizing: "border-box",
                    height: "28px",
                    lineHeight: "26px",
                    border: "none",
                    outline: "none",
                    background: "#fff",
                    borderRadius: "4px 0 0 4px"
                }
            }),
            React.createElement("label", {
                className: "widget-date-native-launcher",
                title: dateNativeTitle,
                style: {
                    position: "relative",
                    flex: "0 0 26px",
                    minWidth: "26px",
                    width: "26px",
                    margin: 0,
                    padding: "0 4px 0 0",
                    cursor: "pointer",
                    border: "none",
                    background: "transparent",
                    display: "flex",
                    justifyContent: "flex-end",
                    alignItems: "center",
                    boxSizing: "border-box",
                    alignSelf: "stretch",
                    minHeight: "28px",
                    overflow: "visible"
                }
            },
                React.createElement("span", {
                    className: "x-form-date-trigger widget-date-icon-inline",
                    "aria-hidden": "true",
                    style: {
                        flex: "0 0 16px",
                        width: "16px",
                        height: "100%",
                        minHeight: "24px",
                        marginRight: "0",
                        marginLeft: "0",
                        backgroundPosition: "right center",
                        backgroundRepeat: "no-repeat"
                    }
                }),
                React.createElement("input", {
                    type: "date",
                    id: nativeId,
                    className: "widget-native-date-input",
                    min: minNative,
                    max: maxNative,
                    defaultValue: isoValue,
                    title: dateNativeTitle,
                    'aria-label': dateNativeTitle,
                    style: {
                        position: "absolute",
                        left: 0,
                        top: 0,
                        width: "100%",
                        height: "100%",
                        margin: 0,
                        padding: 0,
                        border: "none",
                        cursor: "pointer",
                        boxSizing: "border-box",
                        opacity: 0,
                        zIndex: 2,
                        fontSize: "14px"
                    }
                })
            )
        );
        
        return React.createElement("div", {
            className: "widget-date-selector",
            style: { marginBottom: "15px" }
        }, 
            React.createElement("div", {
                style: { display: "flex", alignItems: "center", gap: "15px", flexWrap: "wrap" }
            },
                React.createElement("div", {
                    style: { display: "flex", gap: "8px" }
                },
                    // Show 'All Time' button first when specific members are selected
                    ...(this.state.hasSpecificMembers ? [React.createElement("button", {
                        type: "button",
                        className: `date-range-btn ${savedDateRange == 'alltime' ? 'active' : ''}`,
                        "data-range": "alltime",
                        "data-widget-id": this.widgetId
                    }, labels.alltime)] : []),
                    React.createElement("button", {
                        type: "button",
                        className: `date-range-btn ${savedDateRange == 'ytd' ? 'active' : ''}`,
                        "data-range": "ytd",
                        "data-widget-id": this.widgetId
                    }, labels.ytd),
                    React.createElement("button", {
                        type: "button", 
                        className: `date-range-btn ${savedDateRange == 'year' ? 'active' : ''}`,
                        "data-range": "year",
                        "data-widget-id": this.widgetId
                    }, capitalize(labels.thisYear)),
                    React.createElement("button", {
                        type: "button",
                        className: `date-range-btn ${savedDateRange == 'quarter' ? 'active' : ''}`,
                        "data-range": "quarter",
                        "data-widget-id": this.widgetId
                    }, capitalize(labels.thisQuarter)),
                    React.createElement("button", {
                        type: "button",
                        className: `date-range-btn ${savedDateRange == 'month' ? 'active' : ''}`,
                        "data-range": "month",
                        "data-widget-id": this.widgetId
                    }, capitalize(labels.thisMonth)),
                    React.createElement("button", {
                        type: "button",
                        className: `date-range-btn ${savedDateRange == 'custom' ? 'active' : ''}`,
                        "data-range": "custom",
                        "data-widget-id": this.widgetId
                    }, labels.custom)
                ),
                React.createElement("div", {
                    id: `custom-date-selector-${this.widgetId}`,
                    style: { 
                        display: savedDateRange == 'custom' ? 'flex' : 'none',
                        alignItems: "center", 
                        gap: "10px" 
                    }
                },
                    React.createElement("span", null, labels.from + ":"),
                    renderCombinedDateField(
                        `date-from-${this.widgetId}`,
                        `date-from-native-${this.widgetId}`,
                        fromDisplay,
                        fromIso
                    ),
                    React.createElement("span", null, labels.to + ":"),
                    renderCombinedDateField(
                        `date-to-${this.widgetId}`,
                        `date-to-native-${this.widgetId}`,
                        toDisplay,
                        toIso
                    )
                )
            )
        );
    }

    setupDateRangeEventHandlers(dataForComponent, configPrefix = 'financials_widget', reactComponent) {
        setTimeout(() => {
            const buttons = document.querySelectorAll(`[data-widget-id="${this.widgetId}"]`);
            const customSelector = document.getElementById(`custom-date-selector-${this.widgetId}`);
            const dateFrom = document.getElementById(`date-from-${this.widgetId}`);
            const dateTo = document.getElementById(`date-to-${this.widgetId}`);
            const dateFromNative = document.getElementById(`date-from-native-${this.widgetId}`);
            const dateToNative = document.getElementById(`date-to-native-${this.widgetId}`);

            if (buttons.length === 0) {
                console.error(`No date range buttons found for widget ${this.widgetId}!`);
                return;
            }

            // Set up button click handlers
            buttons.forEach((button) => {
                button.addEventListener('click', (e) => {
                    const range = e.target.getAttribute('data-range');
                    
                    // Update button states
                    buttons.forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    
                    // Show/hide custom date selector
                    if (customSelector) {
                        customSelector.style.display = range === 'custom' ? 'flex' : 'none';
                    }
                    
                    // Trigger widget update
                    this.handleDateRangeChange(range, configPrefix, reactComponent);
                });
            });
            
            // Handle custom date changes (text inputs use app date_format — same as chart axis)
            if (dateFrom && dateTo) {
                const minIso = !this.state.hasSpecificMembers
                    ? dataForComponent.currentYearStart
                    : '2000-01-01';
                const maxIso = !this.state.hasSpecificMembers
                    ? dataForComponent.currentYearEnd
                    : '2050-12-31';

                if (!dateFrom.value.trim()) {
                    dateFrom.value = this.formatIsoToDisplay(dataForComponent.currentYearStart);
                }
                if (!dateTo.value.trim()) {
                    dateTo.value = this.formatIsoToDisplay(dataForComponent.today);
                }

                const self = this;

                const syncNativeFromText = (textEl, nativeEl) => {
                    if (!textEl || !nativeEl) {
                        return;
                    }
                    const iso = self.parseCustomInputToIso(textEl.value);
                    if (iso) {
                        nativeEl.value = iso;
                    }
                };

                const wireNativeDateInput = (textEl, nativeEl) => {
                    if (!nativeEl || !textEl) {
                        return;
                    }
                    if (nativeEl.getAttribute('data-evx-native-date-wired') === '1') {
                        return;
                    }
                    nativeEl.setAttribute('data-evx-native-date-wired', '1');
                    nativeEl.addEventListener('change', () => {
                        if (!nativeEl.value) {
                            return;
                        }
                        textEl.value = self.formatIsoToDisplay(nativeEl.value);
                        validateDateInput(textEl, minIso, maxIso);
                        validateDateRange();
                        self.handleDateRangeChange('custom', configPrefix, reactComponent);
                    });
                };

                const validateDateInput = (input, minIsoBound, maxIsoBound) => {
                    let corrected = false;
                    const value = input.value;
                    if (value && (value.includes('yyyy') || value.includes('mm') || value.includes('dd'))) {
                        input.value = this.formatIsoToDisplay(String(new Date().getFullYear()) + '-01-01');
                        corrected = true;
                    }
                    let iso = this.parseCustomInputToIso(input.value);
                    if (!iso) {
                        input.value = this.formatIsoToDisplay(minIsoBound);
                        iso = minIsoBound;
                        corrected = true;
                    }
                    const d = parseCalendarYMD(iso);
                    const minD = parseCalendarYMD(minIsoBound);
                    const maxD = parseCalendarYMD(maxIsoBound);
                    if (!d.isValid() || !minD.isValid() || !maxD.isValid() ||
                        d.isBefore(minD, 'day') || d.isAfter(maxD, 'day')) {
                        input.value = this.formatIsoToDisplay(minIsoBound);
                        alert('Please enter a valid date between ' +
                            this.formatIsoToDisplay(minIsoBound) + ' and ' +
                            this.formatIsoToDisplay(maxIsoBound));
                        corrected = true;
                    } else {
                        input.value = this.formatIsoToDisplay(iso);
                    }
                    return { isValid: !corrected, wasCorrected: corrected };
                };

                const validateDateRange = () => {
                    const fromIsoVal = this.parseCustomInputToIso(dateFrom.value);
                    const toIsoVal = this.parseCustomInputToIso(dateTo.value);
                    if (fromIsoVal && toIsoVal) {
                        if (parseCalendarYMD(fromIsoVal).isAfter(parseCalendarYMD(toIsoVal), 'day')) {
                            dateTo.value = this.formatIsoToDisplay(fromIsoVal);
                        }
                    }
                    syncNativeFromText(dateFrom, dateFromNative);
                    syncNativeFromText(dateTo, dateToNative);
                };

                syncNativeFromText(dateFrom, dateFromNative);
                syncNativeFromText(dateTo, dateToNative);
                wireNativeDateInput(dateFrom, dateFromNative);
                wireNativeDateInput(dateTo, dateToNative);

                // Track if user is actively typing vs using date picker
                let fromTyping = false, toTyping = false;
                let fromMouseDown = false, toMouseDown = false;

                // Track mouse interactions with date picker
                dateFrom.addEventListener('mousedown', () => { fromMouseDown = true; });
                dateFrom.addEventListener('focus', () => { 
                    // Only set typing flag if focus wasn't from mouse click
                    setTimeout(() => { 
                        if (!fromMouseDown) fromTyping = true; 
                        fromMouseDown = false; 
                    }, 10);
                });
                dateFrom.addEventListener('keydown', () => { fromTyping = true; });
                
                // Handle date picker selection and manual typing
                dateFrom.addEventListener('change', () => {
                    // If not typing (date picker was used), process immediately
                    if (!fromTyping) {
                        const result = validateDateInput(dateFrom, minIso, maxIso);
                        validateDateRange();
                        this.handleDateRangeChange('custom', configPrefix, reactComponent);
                    }
                });
                
                dateFrom.addEventListener('blur', () => { 
                    fromTyping = false;
                    // Validate only when user leaves the field (for manual typing)
                    setTimeout(() => {
                        if (!fromTyping) {
                            const result = validateDateInput(dateFrom, minIso, maxIso);
                            validateDateRange();
                            this.handleDateRangeChange('custom', configPrefix, reactComponent);
                        }
                    }, 100);
                });

                // Track mouse interactions with date picker
                dateTo.addEventListener('mousedown', () => { toMouseDown = true; });
                dateTo.addEventListener('focus', () => { 
                    // Only set typing flag if focus wasn't from mouse click
                    setTimeout(() => { 
                        if (!toMouseDown) toTyping = true; 
                        toMouseDown = false; 
                    }, 10);
                });
                dateTo.addEventListener('keydown', () => { toTyping = true; });
                
                // Handle date picker selection and manual typing
                dateTo.addEventListener('change', () => {
                    // If not typing (date picker was used), process immediately
                    if (!toTyping) {
                        const result = validateDateInput(dateTo, minIso, maxIso);
                        validateDateRange();
                        this.handleDateRangeChange('custom', configPrefix, reactComponent);
                    }
                });
                
                dateTo.addEventListener('blur', () => { 
                    toTyping = false;
                    // Validate only when user leaves the field (for manual typing)
                    setTimeout(() => {
                        if (!toTyping) {
                            const result = validateDateInput(dateTo, minIso, maxIso);
                            validateDateRange();
                            this.handleDateRangeChange('custom', configPrefix, reactComponent);
                        }
                    }, 100);
                });
            }
        }, 100);
    }
}

/**
 * Installs per-widget saveUserConfig and handleDateRangeChange overrides on a DateFilterWidgetBase
 * instance so date-range selections are saved to contact_widget_options instead of global user config.
 *
 * @param {DateFilterWidgetBase} helper
 * @param {string} widgetName  - value posted as `widget_name` (e.g. 'financials')
 * @param {string} configPrefix - option key prefix (e.g. 'financials_widget')
 * @param {string} saveWidgetOptionUrl - URL for the save_widget_option action
 */
function installWidgetOptionSaver(helper, widgetName, configPrefix, saveWidgetOptionUrl) {
    helper.saveUserConfig = function(optionName, optionValue) {
        og.openLink(saveWidgetOptionUrl, {
            post: { widget_name: widgetName, option_name: optionName, option_value: optionValue },
            preventPanelLoad: true,
            silent: true,
            postProcess: function(ok) {
                if (!ok) console.error('Error saving widget option');
            }
        });
    };

    helper.handleDateRangeChange = function(range, _configPrefix, reactComponent) {
        if (reactComponent && reactComponent.setState) {
            reactComponent.setState({ selectedDateRange: range });
        }
        helper.filterDataByDateRange(range, reactComponent);
        helper.saveUserConfig(configPrefix + '_date_range', range);
        if (range === 'custom') {
            var dateFrom = document.getElementById('date-from-' + helper.widgetId);
            var dateTo   = document.getElementById('date-to-'   + helper.widgetId);
            var fromIso  = dateFrom ? helper.parseCustomInputToIso(dateFrom.value) : '';
            var toIso    = dateTo   ? helper.parseCustomInputToIso(dateTo.value)   : '';
            if (fromIso) helper.saveUserConfig(configPrefix + '_custom_from', fromIso);
            if (toIso)   helper.saveUserConfig(configPrefix + '_custom_to',   toIso);
        }
    };
}

module.exports = { DateFilterWidgetBase, parseCalendarYMD, installWidgetOptionSaver };
