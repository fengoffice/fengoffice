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

class WorkedHoursWidget extends React.Component {
    constructor(props) {
        super(props);
        
        // Initialize with unique widget ID
        this.widgetId = 'worked-hours';
        this.dateFilterHelper = new DateFilterWidgetBase(props, this.widgetId);
        
        this.state = {
            worked: props.data.worked || 0,
            estimated: props.data.estimated || 0,
            dateFormat: props.data.dateFormat || 'MM/DD/YYYY',
            workedTitle: props.data.workedTitle || 'Total worked hours',
            estimatedTitle: props.data.estimatedTitle || 'Total estimated hours',
            chartData: props.data.chartData || '',
            allChartData: props.data.chartData ? [...props.data.chartData] : [],
            rawDailyData: this.dateFilterHelper.convertCumulativeToDaily(props.data.chartData || []),
            decimals: props.data.decimals || 0,
            decimalsSeparator: props.data.decimalsSeparator || '.',
            thousandSeparator: props.data.thousandSeparator || ',', 
            DataisLoaded: true,
            no_obj_msg: props.no_obj_msg || 'No data available',
            tasks_without_due_date: props.data.tasks_without_due_date || [],
            tasks_without_due_date_msg: props.data.tasks_without_due_date_msg || '',
            completed_tasks_no_timeslots: props.data.completed_tasks_no_timeslots || [],
            completed_tasks_no_timeslots_msg: props.data.completed_tasks_no_timeslots_msg || '',
            tasks_without_estimated_time: props.data.tasks_without_estimated_time || [],
            tasks_without_estimated_time_msg: props.data.tasks_without_estimated_time_msg || '',
            list_tasks_with_missing_info: props.data.list_tasks_with_missing_info || false,
            ...this.dateFilterHelper.state
        };
        
        // Bind methods from helper
        this.filterDataByDateRange = this.filterDataByDateRange.bind(this);
        this.handleDateRangeChange = this.handleDateRangeChange.bind(this);
        this.performAllTimeFiltering = this.performAllTimeFiltering.bind(this);
        this.saveUserConfig = this.dateFilterHelper.saveUserConfig.bind(this.dateFilterHelper);
    }

    // ComponentDidMount is used to execute the code 
    componentDidMount() {
        // Expose this component instance globally so template can control it
        window.workedHoursWidgetInstance = this;
        
        // Set initial filter based on saved preference or default to YTD
        const selectedRange = this.state.selectedDateRange || 'ytd';
        
        // Apply initial filtering based on the selected range
        if (selectedRange === 'alltime') {
            this.performAllTimeFiltering();
        } else {
            // For other ranges, trigger the helper's date range logic
            this.dateFilterHelper.handleDateRangeChange(selectedRange, 'worked_hours_widget', this);
        }
        
        // Set up event handlers using helper - pass this component reference
        this.dateFilterHelper.setupDateRangeEventHandlers({
            currentYearStart: this.state.currentYearStart,
            currentYearEnd: this.state.currentYearEnd,
            today: this.state.today
        }, 'worked_hours_widget', this);
    }

    componentWillUnmount() {
        // Clean up global reference
        if (window.workedHoursWidgetInstance === this) {
            window.workedHoursWidgetInstance = null;
        }
    }

    handleDateRangeChange(range) {
        this.dateFilterHelper.handleDateRangeChange(range, 'worked_hours_widget', this);
    }

    filterDataByDateRange(fromDate, toDate) {
        const fullData = this.state.allChartData;
        const filteredData = fullData.filter(item => {
            const itemDate = moment(item.date, this.state.dateFormat);
            return itemDate.isBetween(fromDate, toDate, 'day', '[]');
        });

        // Calculate final values from filtered data
        const finalEstimated = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_budget : 0;
        const finalWorked = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_earned : 0;

        this.setState({
            chartData: filteredData,
            estimated: finalEstimated,
            worked: finalWorked
        });
    }

    // This method is called by the date filter helper
    performDateFiltering(startDate, endDate) {
        // Filter raw daily data by date range
        const filteredDailyData = this.state.rawDailyData.filter(item => {
            const itemDate = moment(item.date, 'MM/DD/YYYY');
            return itemDate.isBetween(startDate, endDate, 'day', '[]');
        });

        // Convert filtered daily data back to cumulative format for chart
        let cumulativeEstimated = 0;
        let cumulativeWorked = 0;
        const recalculatedData = filteredDailyData.map(item => {
            cumulativeEstimated += item.daily_budget;
            cumulativeWorked += item.daily_earned;
            return {
                date: item.date,
                estimated: cumulativeEstimated,
                worked: cumulativeWorked
            };
        });

        const finalEstimated = recalculatedData.length > 0 ? recalculatedData[recalculatedData.length - 1].estimated : 0;
        const finalWorked = recalculatedData.length > 0 ? recalculatedData[recalculatedData.length - 1].worked : 0;

        this.setState({
            chartData: recalculatedData,
            estimated: finalEstimated,
            worked: finalWorked
        });
    }

    // Handle 'All Time' filtering - show all available data
    performAllTimeFiltering() {
        // Use the original full data set
        const fullData = this.state.allChartData;
        const finalEstimated = fullData.length > 0 ? fullData[fullData.length - 1].total_budget : 0;
        const finalWorked = fullData.length > 0 ? fullData[fullData.length - 1].total_earned : 0;

        this.setState({
            chartData: fullData.map(item => ({
                date: item.date,
                estimated: item.total_budget || 0,
                worked: item.total_earned || 0
            })),
            estimated: finalEstimated,
            worked: finalWorked
        });
    }

    render() {
        // Define variables that will be used in the returned component
        const decimals = this.state.decimals;
        const decimalsSeparator = this.state.decimalsSeparator;
        const thousandSeparator = this.state.thousandSeparator;
        const worked = FormatNumber(this.state.worked, decimals, decimalsSeparator, thousandSeparator);
        const estimated = FormatNumber(this.state.estimated, decimals, decimalsSeparator, thousandSeparator);
        const workedtitle = this.state.workedTitle;
        const estimatedTitle = this.state.estimatedTitle;
        const dateFormat = this.state.dateFormat;
        const percentage = Math.round((worked / estimated) * 100);
        const percentStyle = {
            width: percentage+'%'
        }
        const remaining = estimated - worked;
        const remaining_percent = 100 - percentage;
        const budgeted = Math.round(estimated * 0.96);
        const variance = estimated - budgeted;
        const tasks_without_due_date = this.state.tasks_without_due_date;
        const tasks_without_due_date_msg = this.state.tasks_without_due_date_msg;
        const completed_tasks_no_timeslots= this.state.completed_tasks_no_timeslots;
        const completed_tasks_no_timeslots_msg = this.state.completed_tasks_no_timeslots_msg;
        const tasks_without_estimated_time = this.state.tasks_without_estimated_time;
        const tasks_without_estimated_time_msg = this.state.tasks_without_estimated_time_msg;
        const list_tasks_with_missing_info = this.state.list_tasks_with_missing_info;
        var chartData = this.state.chartData;
        if(chartData){
            chartData.forEach(d => {
                d.date = moment(d.date).valueOf();
            });
        }
        var DataisLoaded = this.state.DataisLoaded;

        if (!DataisLoaded) return <div className="statistics-widget__placeholder">
            <div className="statistics-widget__placeholder-circle"></div>
            <div>
            <div className="statistics-widget__placeholder-line-1"></div>
            <div className="statistics-widget__placeholder-line-2"></div>
            </div>
        </div> ;

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

            <p style={percentStyle} data-value={percentage}></p>

            <div className="progress-info-container">
                <div className="progress-total">
                    <div>{workedtitle}</div>
                    <div><svg className="progress-total__icon progress-total__icon--completed" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                    </svg><span className="progress-total__number">{worked}</span> <span className="progress-total__unit">hrs</span></div>
                </div>

                <div className="progress-total">
                    <div>{estimatedTitle}</div>
                    <div><svg className="progress-total__icon progress-total__icon--estimated" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                    </svg><span className="progress-total__number">{estimated}</span> <span className="progress-total__unit">hrs</span></div>
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
                            <linearGradient id="colorWorkedHours" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#20a1f8" stopOpacity={0.6}/>
                                <stop offset="95%" stopColor="#20a1f8" stopOpacity={0.6}/>
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
                            formatter={(value) => FormatNumber(value, decimals, decimalsSeparator,thousandSeparator)}
                        />
                        <Area type="monotone" dataKey="estimated" stroke="#888888" fill="url(#colorEstimated)" isAnimationActive={false}/>
                        <Area type="monotone" dataKey="worked" stroke="#20a1f8" fill="url(#colorWorkedHours)" isAnimationActive={false}/>
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
            }

            {tasks_without_due_date.length > 0 && 
                <div className="worked-hours-widget-warning">
                    <div>{tasks_without_due_date_msg}</div>
                    <ul>
                    {list_tasks_with_missing_info && tasks_without_due_date.map(task => (
                        <li><a href={task['view_url']}>{task['name']}</a></li>
                      ))}
                    </ul>
                </div>
            }

            {completed_tasks_no_timeslots.length > 0 && 
                <div className="worked-hours-widget-warning">
                    <div>{completed_tasks_no_timeslots_msg}</div>
                    <ul>
                    {list_tasks_with_missing_info && completed_tasks_no_timeslots.map(task => (
                        <li><a href={task['view_url']}>{task['name']}</a></li>
                      ))}
                    </ul>
                </div>
            }

            {tasks_without_estimated_time.length > 0 && 
                <div className="worked-hours-widget-warning">
                    <div>{tasks_without_estimated_time_msg}</div>
                    <ul>
                    {list_tasks_with_missing_info && tasks_without_estimated_time.map(task => (
                        <li><a href={task['view_url']}>{task['name']}</a></li>
                      ))}
                    </ul>
                </div>
            }
          </div>
        );
      }
  };
  
  function showWorkedHoursWidget(no_obj_msg, element, config_data){
    ReactDOM.render(<WorkedHoursWidget no_obj_msg={no_obj_msg} data={config_data || {}} />,
      element);
  };
  
  module.exports = showWorkedHoursWidget;