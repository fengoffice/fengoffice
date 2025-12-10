var React = require('react');
var ReactDOM = require('react-dom');
var moment = require('moment');

var { ResponsiveContainer,
      AreaChart,
      Area,
      XAxis,
      YAxis,
      CartesianGrid,
      Tooltip,
      Legend } = require('recharts');
var { AxisNumberFormatter, FormatNumber } = require('./helpers/widgetChartHelpers');
var { DateFilterWidgetBase } = require('./helpers/widgetDateFilterHelpers');

class ExpensesProgressWidget extends React.Component {
    constructor(props) {
        super(props);
        
        // Initialize with unique widget ID
        this.widgetId = 'expenses';
        this.dateFilterHelper = new DateFilterWidgetBase(props, this.widgetId);
        
        this.state = { 
            currencySymbol: props.data.currencySymbol ? props.data.currencySymbol : '$',
            dateFormat: props.data.dateFormat ? props.data.dateFormat : 'MM/DD/YYYY',
            earned: props.data.earned ? props.data.earned : 0,
            estimated: props.data.estimated ? props.data.estimated : 0,
            earnedTitle: props.data.earnedTitle ? props.data.earnedTitle : 'Actual expenses',
            estimatedTitle: props.data.estimatedTitle ? props.data.estimatedTitle : 'Budgeted expenses',
            chartData: props.data.chartData ? props.data.chartData : '',
            allChartData: props.data.chartData ? [...props.data.chartData] : [],
            rawDailyData: this.dateFilterHelper.convertCumulativeToDaily(props.data.chartData || []),
            decimals: props.data.decimals ? props.data.decimals : 0,
            decimalsSeparator: props.data.decimalsSeparator ? props.data.decimalsSeparator : '.',
            thousandSeparator: props.data.thousandSeparator ? props.data.thousandSeparator : ',',
            ...this.dateFilterHelper.state
        };
        
        // Bind methods from helper
        this.filterDataByDateRange = this.filterDataByDateRange.bind(this);
        this.handleDateRangeChange = this.handleDateRangeChange.bind(this);
        this.performAllTimeFiltering = this.performAllTimeFiltering.bind(this);
        this.saveUserConfig = this.dateFilterHelper.saveUserConfig.bind(this.dateFilterHelper);
    }

    componentDidMount() {
        // Expose this component instance globally so template can control it
        window.expensesWidgetInstance = this;
        
        // Set initial filter based on saved preference
        this.filterDataByDateRange(this.state.selectedDateRange);
        
        // Set up event handlers using helper - pass this component reference
        this.dateFilterHelper.setupDateRangeEventHandlers({
            currentYearStart: this.state.currentYearStart,
            currentYearEnd: this.state.currentYearEnd,
            today: this.state.today
        }, 'expenses_widget', this);
    }

    componentWillUnmount() {
        // Clean up global reference
        if (window.expensesWidgetInstance === this) {
            window.expensesWidgetInstance = null;
        }
    }

    handleDateRangeChange(range) {
        this.dateFilterHelper.handleDateRangeChange(range, 'expenses_widget', this);
    }

    filterDataByDateRange(range) {
        this.dateFilterHelper.filterDataByDateRange(range, this);
    }

    // This method is called by the date filter helper
    performDateFiltering(startDate, endDate) {
        // Filter raw daily data by date range
        const allDailyData = this.state.rawDailyData;
        
        const filteredDailyData = allDailyData.filter(item => {
            const itemDate = moment(item.date, 'MM/DD/YYYY');
            return itemDate.isBetween(startDate, endDate, 'day', '[]');
        });

        // If no data exists in the requested range, create empty data points
        if (filteredDailyData.length === 0) {
            const emptyData = this.createEmptyDataRange(startDate, endDate);
            this.setState({
                chartData: emptyData,
                estimated: 0,
                earned: 0
            });
            return;
        }

        // Get the actual data range from available data
        const dataStartDate = allDailyData.length > 0 ? moment(allDailyData[0].date, 'MM/DD/YYYY') : startDate;
        const dataEndDate = allDailyData.length > 0 ? moment(allDailyData[allDailyData.length - 1].date, 'MM/DD/YYYY') : endDate;

        // Extend the daily data to fill the entire requested range
        const extendedDailyData = this.extendDailyDataToRange(filteredDailyData, startDate, endDate, dataStartDate, dataEndDate);

        // Convert extended daily data back to cumulative format for chart
        let cumulativeEstimated = 0;
        let cumulativeEarned = 0;
        const recalculatedData = extendedDailyData.map(item => {
            cumulativeEstimated += item.daily_budget;
            cumulativeEarned += item.daily_earned;
            return {
                date: item.date,
                total_budget: cumulativeEstimated,
                total_earned: cumulativeEarned
            };
        });

        // Use final values from actual filtered data (not extended empty data)
        const actualDataRecalculated = filteredDailyData.map(item => {
            return { daily_budget: item.daily_budget, daily_earned: item.daily_earned };
        });
        const finalEstimated = actualDataRecalculated.reduce((sum, item) => sum + item.daily_budget, 0);
        const finalEarned = actualDataRecalculated.reduce((sum, item) => sum + item.daily_earned, 0);

        this.setState({
            chartData: recalculatedData,
            estimated: finalEstimated,
            earned: finalEarned
        });
    }

    // Create empty data points for a date range
    createEmptyDataRange(startDate, endDate) {
        const emptyData = [];
        const current = startDate.clone();
        
        while (current.isSameOrBefore(endDate)) {
            emptyData.push({
                date: current.format('MM/DD/YYYY'),
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
        
        while (current.isSameOrBefore(requestedEnd)) {
            const currentDateStr = current.format('MM/DD/YYYY');
            
            // Check if we have actual data for this date
            const existingData = filteredDailyData.find(item => item.date === currentDateStr);
            
            if (existingData) {
                // Use actual data
                result.push(existingData);
            } else {
                // Create zero data point for dates outside our data range
                result.push({
                    date: currentDateStr,
                    daily_budget: 0,
                    daily_earned: 0
                });
            }
            
            current.add(1, 'day');
        }
        
        return result;
    }

    // Handle 'All Time' filtering - show all available data
    performAllTimeFiltering() {
        // Use the original full data set
        const fullData = this.state.allChartData;
        const finalEstimated = fullData.length > 0 ? fullData[fullData.length - 1].total_budget : 0;
        const finalEarned = fullData.length > 0 ? fullData[fullData.length - 1].total_earned : 0;

        this.setState({
            chartData: fullData,
            estimated: finalEstimated,
            earned: finalEarned
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
        const earned = formatToMoney(this.state.earned)
        const estimated = formatToMoney(this.state.estimated)
        const earnedTitle = this.state.earnedTitle;
        const estimatedTitle = this.state.estimatedTitle;
        const dateFormat = this.state.dateFormat;
        var chartData = this.state.chartData;
        if(chartData){
            chartData.forEach(d => {
                d.date = moment(d.date).valueOf();
            });
        }
        return (
            <div className="progress-widget-container">
                {/* Render date selector using helper */}
                {this.dateFilterHelper.renderDateSelector(
                    this.state.selectedDateRange,
                    this.state.savedCustomFrom, 
                    this.state.savedCustomTo,
                    this.state.currentYearStart,
                    this.state.today
                )}

                <div className="progress-info-container">
                    <div className="progress-total">
                        <div>{earnedTitle}</div>
                        <div><svg className="progress-total__icon progress-total__icon--green" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                        </svg><span className="progress-total__number">{earned}</span> <span className="font-weight-400">&nbsp; &nbsp;</span></div>
                    </div>
                    <div className="progress-total">
                        <div>{estimatedTitle}</div>
                        <div><svg className="progress-total__icon progress-total__icon--estimated" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                        </svg><span className="progress-total__number">{estimated}</span> <span className="progress-total__unit"></span>&nbsp; &nbsp;</div>
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
                            <linearGradient id="colorWorked" x1="0" y1="0" x2="0" y2="1">
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
                            interval="preserveStartEnd"
                            domain={['auto', 'auto']}
                            tickFormatter={(unixTime) => moment(unixTime).format(dateFormat)}
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
                        <Area type="monotone" dataKey="total_earned" stroke="#0cbe9b" fill="url(#colorWorked)" isAnimationActive={false}/>
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
                }
            </div>
        );
    };
};
  
function showExpensesProgressWidget(data, element){
ReactDOM.render(<ExpensesProgressWidget data={data} />,
    element);
};

module.exports = showExpensesProgressWidget;
