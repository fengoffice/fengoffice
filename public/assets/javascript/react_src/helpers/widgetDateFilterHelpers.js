var moment = require('moment');

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

        const formData = new FormData();
        formData.append('option_name', optionName);
        formData.append('option_value', optionValue);

        fetch(this.state.saveConfigUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.text())
        .catch(error => {
            console.error('Error saving config:', error);
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
                
                if (dateFrom && dateFrom.value) {
                    this.saveUserConfig(`${configPrefix}_custom_from`, dateFrom.value);
                }
                if (dateTo && dateTo.value) {
                    this.saveUserConfig(`${configPrefix}_custom_to`, dateTo.value);
                }
            }
        }
    }

    filterDataByDateRange(range, reactComponent) {
        let startDate, endDate;
        const today = moment(this.state.today);
        
        switch(range) {
            case 'ytd':
                startDate = moment(this.state.currentYearStart);
                endDate = today;
                break;
            case 'year':
                startDate = moment(this.state.currentYearStart);
                endDate = moment(this.state.currentYearEnd);
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
                if (dateFrom && dateTo && dateFrom.value && dateTo.value) {
                    startDate = moment(dateFrom.value);
                    endDate = moment(dateTo.value);
                } else if (this.state.savedCustomFrom && this.state.savedCustomTo) {
                    startDate = moment(this.state.savedCustomFrom);
                    endDate = moment(this.state.savedCustomTo);
                } else {
                    return;
                }
                break;
            default:
                startDate = moment(this.state.currentYearStart);
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
        
        // Helper function to capitalize each word
        const capitalize = (str) => str.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        
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
                    React.createElement("input", {
                        type: "date",
                        id: `date-from-${this.widgetId}`,
                        defaultValue: savedDateRange == 'custom' && savedCustomFrom ? savedCustomFrom : currentYearStart
                    }),
                    React.createElement("span", null, labels.to + ":"),
                    React.createElement("input", {
                        type: "date", 
                        id: `date-to-${this.widgetId}`,
                        defaultValue: savedDateRange == 'custom' && savedCustomTo ? savedCustomTo : today
                    })
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
            
            // Handle custom date changes
            if (dateFrom && dateTo) {
                // Apply date restrictions based on member selection
                if (!this.state.hasSpecificMembers) {
                    // No specific members: restrict to current year only
                    dateFrom.min = dataForComponent.currentYearStart;
                    dateFrom.max = dataForComponent.currentYearEnd;
                    dateTo.min = dataForComponent.currentYearStart;
                    dateTo.max = dataForComponent.currentYearEnd;
                } else {
                    // Specific members selected: allow broader range but prevent system breakage
                    // Restrict to reasonable date range: 2000/01/01 to 2050/12/31
                    dateFrom.min = '2000-01-01';
                    dateFrom.max = '2050-12-31';
                    dateTo.min = '2000-01-01';
                    dateTo.max = '2050-12-31';
                }
                
                if (!dateFrom.value) {
                    dateFrom.value = dataForComponent.currentYearStart;
                }
                if (!dateTo.value) {
                    dateTo.value = dataForComponent.today;
                }

                const validateDateInput = (input, minDate, maxDate) => {
                    const value = input.value;
                    let corrected = false;
                    
                    // Handle incomplete dates (yyyy, mm, dd placeholders)
                    if (value && (value.includes('yyyy') || value.includes('mm') || value.includes('dd'))) {
                        const currentYear = new Date().getFullYear();
                        input.value = `${currentYear}-01-01`;
                        corrected = true;
                    }
                    
                    if (input.value && input.value.length === 10) { // Only validate complete dates (YYYY-MM-DD format)
                        // Additional check: make sure year is at least 4 digits and realistic
                        const yearPart = input.value.split('-')[0];
                        if (yearPart && yearPart.length === 4) {
                            const year = parseInt(yearPart);
                            
                            // Check if date is valid and within range
                            const date = new Date(input.value);
                            const min = new Date(minDate);
                            const max = new Date(maxDate);
                            
                            if (isNaN(date.getTime()) || date < min || date > max) {
                                // Reset to a safe default value
                                input.value = dataForComponent.currentYearStart;
                                alert('Please enter a valid date between ' + minDate + ' and ' + maxDate);
                                corrected = true;
                            }
                            
                        }
                    }
                    
                    return { isValid: !corrected, wasCorrected: corrected };
                };

                const validateDateRange = () => {
                    const fromValue = dateFrom.value;
                    const toValue = dateTo.value;
                    
                    // Only validate if both dates are complete and realistic
                    if (fromValue && toValue && fromValue.length === 10 && toValue.length === 10) {
                        const fromYear = fromValue.split('-')[0];
                        const toYear = toValue.split('-')[0];
                        
                        // Only proceed if years are realistic (not like 0002)
                        if (parseInt(fromYear) >= 1000 && parseInt(toYear) >= 1000) {
                            const fromDate = new Date(fromValue);
                            const toDate = new Date(toValue);
                            
                            // If from date is later than to date, make them equal
                            if (fromDate > toDate) {
                                dateTo.value = fromValue;
                            }
                        }
                    }
                };

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
                        const result = validateDateInput(dateFrom, dateFrom.min, dateFrom.max);
                        validateDateRange();
                        this.handleDateRangeChange('custom', configPrefix, reactComponent);
                    }
                });
                
                dateFrom.addEventListener('blur', () => { 
                    fromTyping = false;
                    // Validate only when user leaves the field (for manual typing)
                    setTimeout(() => {
                        if (!fromTyping) {
                            const result = validateDateInput(dateFrom, dateFrom.min, dateFrom.max);
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
                        const result = validateDateInput(dateTo, dateTo.min, dateTo.max);
                        validateDateRange();
                        this.handleDateRangeChange('custom', configPrefix, reactComponent);
                    }
                });
                
                dateTo.addEventListener('blur', () => { 
                    toTyping = false;
                    // Validate only when user leaves the field (for manual typing)
                    setTimeout(() => {
                        if (!toTyping) {
                            const result = validateDateInput(dateTo, dateTo.min, dateTo.max);
                            validateDateRange();
                            this.handleDateRangeChange('custom', configPrefix, reactComponent);
                        }
                    }, 100);
                });
            }
        }, 100);
    }
}

module.exports = { DateFilterWidgetBase };
