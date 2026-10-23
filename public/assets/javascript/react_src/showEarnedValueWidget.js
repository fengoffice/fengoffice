var React = require('react');
var ReactDOM = require('react-dom');
var moment = require('moment');

var { ResponsiveContainer,
      AreaChart,
      Area,
      XAxis,
      YAxis,
      CartesianGrid,
      Tooltip } = require('recharts');
var { AxisNumberFormatter, FormatNumber } = require('./helpers/widgetChartHelpers');
var { DateFilterWidgetBase, parseCalendarYMD, installWidgetOptionSaver } = require('./helpers/widgetDateFilterHelpers');

class EarnedValueWidget extends React.Component {
    constructor(props) {
        super(props);
        
        // Initialize with unique widget ID
        this.widgetId = 'earned-value';
        this.dateFilterHelper = new DateFilterWidgetBase(props, this.widgetId);

        this.handleIgnoreToggle = this.handleIgnoreToggle.bind(this);

        installWidgetOptionSaver(this.dateFilterHelper, 'earned_value_on_labor', 'earned_value_widget', props.data.saveWidgetOptionUrl);

        this.state = {
            currencySymbol: props.data.currencySymbol ? props.data.currencySymbol : '$',
            dateFormat: props.data.dateFormat ? props.data.dateFormat : 'MM/DD/YYYY',
            executed: props.data.executed ? props.data.executed : 0,
            estimated: props.data.estimated ? props.data.estimated : 0,
            consolidatedMissingAmounts: props.data.consolidatedMissingAmounts ? props.data.consolidatedMissingAmounts : 0,
            executedTitle: props.data.executedTitle ? props.data.executedTitle : 'Executed labor',
            estimatedTitle: props.data.estimatedTitle ? props.data.estimatedTitle : 'Estimated labor',
            chartData: props.data.chartData ? props.data.chartData : '',
            allChartData: props.data.chartData ? [...props.data.chartData] : [],
            rawDailyData: this.dateFilterHelper.convertCumulativeToDaily(props.data.chartData || []),
            decimals: props.data.decimals ? props.data.decimals : 0,
            decimalsSeparator: props.data.decimalsSeparator ? props.data.decimalsSeparator : '.',
            thousandSeparator: props.data.thousandSeparator ? props.data.thousandSeparator : ',',
            ignoreTasksWithoutDate: props.data.ignoreTasksWithoutDate || false,
            ...this.dateFilterHelper.state
        };
        
        // Bind methods from helper
        this.filterDataByDateRange = this.filterDataByDateRange.bind(this);
        this.handleDateRangeChange = this.handleDateRangeChange.bind(this);
        this.performAllTimeFiltering = this.performAllTimeFiltering.bind(this);
        this.saveUserConfig = this.dateFilterHelper.saveUserConfig.bind(this.dateFilterHelper);
        this.parseItemDay = this.parseItemDay.bind(this);
    }

    parseItemDay(item) {
        if (item.dateKey) {
            return parseCalendarYMD(item.dateKey);
        }
        return moment(item.date, this.state.dateFormat, true).startOf('day');
    }

    componentDidMount() {
        // Expose this component instance globally so template can control it
        window.earnedValueWidgetInstance = this;
        
        // Set initial filter based on saved preference or default to YTD
        const selectedRange = this.state.selectedDateRange || 'ytd';
        
        // Apply initial filtering based on the selected range
        if (selectedRange === 'alltime') {
            this.performAllTimeFiltering();
        } else {
            // For other ranges, trigger the helper's date range logic
            this.dateFilterHelper.handleDateRangeChange(selectedRange, 'earned_value_widget', this);
        }
        
        // Set up event handlers using helper - pass this component reference
        this.dateFilterHelper.setupDateRangeEventHandlers({
            currentYearStart: this.state.currentYearStart,
            currentYearEnd: this.state.currentYearEnd,
            today: this.state.today
        }, 'earned_value_widget', this);
    }

    componentWillUnmount() {
        // Clean up global reference
        if (window.earnedValueWidgetInstance === this) {
            window.earnedValueWidgetInstance = null;
        }
    }

    handleDateRangeChange(range) {
        this.dateFilterHelper.handleDateRangeChange(range, 'earned_value_widget', this);
    }

    filterDataByDateRange(fromDate, toDate) {
        const fullData = this.state.allChartData;
        const filteredData = fullData.filter(item => {
            const itemDate = this.parseItemDay(item);
            return itemDate.isBetween(fromDate, toDate, 'day', '[]');
        });

        // Calculate final values from filtered data
        const finalEstimated = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_budget : 0;
        const finalExecuted = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_earned : 0;

        this.setState({
            chartData: filteredData,
            estimated: finalEstimated,
            executed: finalExecuted
        });
    }

    // This method is called by the date filter helper
    performDateFiltering(startDate, endDate) {
        // Filter raw daily data by date range
        const allDailyData = this.state.rawDailyData;
        
        const filteredDailyData = allDailyData.filter(item => {
            const itemDate = this.parseItemDay(item);
            return itemDate.isBetween(startDate, endDate, 'day', '[]');
        });

        // If no data exists in the requested range, create empty data points
        if (filteredDailyData.length === 0) {
            const emptyData = this.createEmptyDataRange(startDate, endDate);
            this.setState({
                chartData: emptyData,
                estimated: this.state.consolidatedMissingAmounts, // Include missing amounts even with no data
                executed: 0
            });
            return;
        }

        // Get the actual data range from available data
        const dataStartDate = allDailyData.length > 0 ? this.parseItemDay(allDailyData[0]) : startDate;
        const dataEndDate = allDailyData.length > 0 ? this.parseItemDay(allDailyData[allDailyData.length - 1]) : endDate;

        // Extend the daily data to fill the entire requested range
        const extendedDailyData = this.extendDailyDataToRange(filteredDailyData, startDate, endDate, dataStartDate, dataEndDate);

        // Convert extended daily data back to cumulative format for chart
        let cumulativeEstimated = 0;
        let cumulativeExecuted = 0;
        const recalculatedData = extendedDailyData.map(item => {
            cumulativeEstimated += item.daily_budget;
            cumulativeExecuted += item.daily_earned;
            return {
                date: item.date,
                dateKey: item.dateKey,
                total_budget: cumulativeEstimated,
                total_earned: cumulativeExecuted
            };
        });

        // Use final values from actual filtered data (not extended empty data)
        const actualDataRecalculated = filteredDailyData.map(item => {
            return { daily_budget: item.daily_budget, daily_earned: item.daily_earned };
        });
        const finalEstimated = actualDataRecalculated.reduce((sum, item) => sum + item.daily_budget, 0) + this.state.consolidatedMissingAmounts;
        const finalExecuted = actualDataRecalculated.reduce((sum, item) => sum + item.daily_earned, 0);

        this.setState({
            chartData: recalculatedData,
            estimated: finalEstimated,
            executed: finalExecuted
        });
    }

    // Create empty data points for a date range
    createEmptyDataRange(startDate, endDate) {
        const emptyData = [];
        const current = startDate.clone();
        const df = this.state.dateFormat;

        while (current.isSameOrBefore(endDate)) {
            emptyData.push({
                dateKey: current.format('YYYY-MM-DD'),
                date: current.format(df),
                total_budget: 0,
                total_earned: 0
            });
            current.add(1, 'day');
        }

        return emptyData;
    }

    // Extend daily data to fill the entire requested range with zeros where no data exists
    extendDailyDataToRange(filteredDailyData, requestedStart, requestedEnd, dataStart, dataEnd) {
        const result = [];
        const current = requestedStart.clone();
        const df = this.state.dateFormat;

        while (current.isSameOrBefore(requestedEnd)) {
            const currentKey = current.format('YYYY-MM-DD');
            const existingData = filteredDailyData.find(item =>
                (item.dateKey && item.dateKey === currentKey) ||
                (!item.dateKey && this.parseItemDay(item).format('YYYY-MM-DD') === currentKey)
            );

            if (existingData) {
                result.push(existingData);
            } else {
                result.push({
                    dateKey: currentKey,
                    date: current.format(df),
                    daily_budget: 0,
                    daily_earned: 0
                });
            }

            current.add(1, 'day');
        }

        return result;
    }

    handleIgnoreToggle(e) {
        const checked = e.target.checked;
        this.setState({ ignoreTasksWithoutDate: checked });
        const saveUrl = this.props.data.saveWidgetOptionUrl;
        const reloadUrl = this.props.data.reloadWidgetUrl;
        const reloadContainerId = this.props.data.reloadContainerId;

        og.openLink(saveUrl, {
            post: {
                widget_name: 'earned_value_on_labor',
                option_name: 'ignore_tasks_without_date',
                option_value: checked ? '1' : '0'
            },
            preventPanelLoad: true,
            silent: true,
            postProcess: function(ok) {
                if (!ok) { console.error('Error saving ignore option'); return; }
                og.openLink(reloadUrl, {
                    preventPanelLoad: true,
                    silent: true,
                    postProcess: function(ok2, html) {
                        if (!html) { window.location.reload(); return; }
                        var scripts = [];
                        var cleanHtml = html.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function(_, sc) {
                            if (sc.trim()) scripts.push(sc);
                            return '';
                        });
                        var widgetEl = document.getElementById(reloadContainerId);
                        if (widgetEl) {
                            jQuery(widgetEl).replaceWith(cleanHtml);
                        }
                        setTimeout(function() {
                            scripts.forEach(function(src) {
                                var s = document.createElement('script');
                                s.textContent = src;
                                document.head.appendChild(s);
                                document.head.removeChild(s);
                            });
                        }, 0);
                    }
                });
            }
        });
    }

    // Handle 'All Time' filtering - show all available data
    performAllTimeFiltering() {
        // Use the original full data set
        const fullData = this.state.allChartData;
        
        // If there's no data available, show empty data for this year
        if (!fullData || fullData.length === 0) {
            const currentYearStart = parseCalendarYMD(this.state.currentYearStart);
            const currentYearEnd = parseCalendarYMD(this.state.currentYearEnd);
            const emptyYearData = this.createEmptyDataRange(currentYearStart, currentYearEnd);
            
            this.setState({
                chartData: emptyYearData,
                estimated: this.state.consolidatedMissingAmounts,
                executed: 0
            });
            return;
        }
        
        const finalEstimated = (fullData.length > 0 ? fullData[fullData.length - 1].total_budget : 0) + this.state.consolidatedMissingAmounts;
        const finalExecuted = fullData.length > 0 ? fullData[fullData.length - 1].total_earned : 0;

        this.setState({
            chartData: fullData,
            estimated: finalEstimated,
            executed: finalExecuted
        });
    }

    render() {
        // Define variables that will be used in the returned component
        const currencySymbol = String(this.state.currencySymbol);
        const decimals = this.state.decimals;
        const decimalsSeparator = this.state.decimalsSeparator;
        const thousandSeparator = this.state.thousandSeparator;
        const formatToMoney = (value) => {
            return currencySymbol + ' ' + FormatNumber(value, decimals, decimalsSeparator, thousandSeparator);
        }
        const executed = formatToMoney(this.state.executed);
        const estimated = formatToMoney(this.state.estimated);
        const executedTitle = this.state.executedTitle;
        const dateFormat = this.state.dateFormat || 'MM/DD/YYYY';
        const estimatedTitle = this.state.estimatedTitle;
        var chartData = this.state.chartData;
        chartData.forEach(d => {
            const m = d.dateKey
                ? parseCalendarYMD(d.dateKey)
                : moment(d.date, dateFormat, true).startOf('day');
            d.date = m.isValid() ? m.valueOf() : 0;
        });
        return (
            <div className="progress-widget-container">
                <div className="progress-widget-filter-row">
                    {this.dateFilterHelper.renderDateSelector(
                        this.state.selectedDateRange,
                        this.state.savedCustomFrom,
                        this.state.savedCustomTo,
                        this.state.currentYearStart,
                        this.state.today
                    )}
                    <label className="mswitch-row">
                        <span className="mswitch">
                            <input type="checkbox" checked={this.state.ignoreTasksWithoutDate} onChange={this.handleIgnoreToggle} />
                            <span className="mswitch-slider"></span>
                        </span>
                        {(this.props.data.labels && this.props.data.labels.ignoreTasksWithoutDate) || 'Ignore tasks without date'}
                    </label>
                </div>

                <div className="progress-info-container">
                    <div className="progress-total">
                        <div>{executedTitle}</div>
                        <div><svg className="progress-total__icon progress-total__icon--green" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                        </svg><span className="progress-total__number">{executed}</span>&nbsp; &nbsp;</div>
                    </div>
                    <div className="progress-total">
                        <div>{estimatedTitle}</div>
                        <div><svg className="progress-total__icon progress-total__icon--estimated" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                        </svg><span className="progress-total__number">{estimated}</span>&nbsp; &nbsp;</div>
                    </div>
                </div>

                {/** Render chart if chartData exists */}
                {chartData &&
                    
                <div className="progress-widget-chart">
                    {/**
                    Guide on how to use recharts can be found here http://recharts.org/en-US/api
                    */}
                    <ResponsiveContainer width="100%" height={200}>
                        <AreaChart
                        data={chartData}
                        margin={{
                            top: 10, right: 0, left: 0, bottom: -10,
                        }}
                        padding={{}}
                        >
                        <defs>
                            <linearGradient id="colorEstimated" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#d9d9d9" stopOpacity={0.6}/>
                                <stop offset="95%" stopColor="#d9d9d9" stopOpacity={0.6}/>
                            </linearGradient>
                            <linearGradient id="colorExecuted" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#0cbe9b" stopOpacity={0.5}/>
                                <stop offset="95%" stopColor="#0cbe9b" stopOpacity={0.5}/>
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" vertical={false}/>
                        <XAxis 
                            dy={15} 
                            axisLine={false}
                            tickSize={0}
                            stroke="#888888"
                            dataKey="date"
                            type="number"
                            scale="time"
                            domain={['auto', 'auto']}
                            tickFormatter={(unixTime) => moment(unixTime).format(dateFormat)}
                            interval="preserveStartEnd"
                            minTickGap={70}
                            height={40}
                        />
                        <YAxis
                            dx={10}
                            tickFormatter={AxisNumberFormatter}
                            axisLine={false}
                            tickSize={0}
                            stroke="#888888"
                            orientation="right"
                            width={50}
                        />
                        <Tooltip
                            labelFormatter={(unixTime) => moment(unixTime).format(dateFormat)}
                            formatter={(value) => formatToMoney(value)}
                        />
                        <Area type="monotone" dataKey="total_budget" stroke="#888888" fill="url(#colorEstimated)" isAnimationActive={false}/>
                        <Area type="monotone" dataKey="total_earned" stroke="#0cbe9b" fill="url(#colorExecuted)" isAnimationActive={false}/>
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
                }
            </div>
        );
    };
};
  
function showEarnedValueWidget(data, element){
    ReactDOM.render(<EarnedValueWidget data={data} />,
        element);
};

module.exports = showEarnedValueWidget;
