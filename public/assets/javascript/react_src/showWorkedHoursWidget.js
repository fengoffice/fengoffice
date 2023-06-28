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

class WorkedHoursWidget extends React.Component {
    constructor(props) {
      super(props);
      this.state = {
        worked: 0,
        estimated: 0,
        dateFormat: 'MM/DD/YYYY',
        workedTitle: 'Total worked hours',
        estimatedTitle: 'Total estimated hours',
        chartData: '',
        decimals: 0,
        decimalsSeparator: '.',
        thousandSeparator: ',', 
        DataisLoaded: false,
        api_url: props.api_url,
        no_obj_msg: props.no_obj_msg,
        tasks_without_due_date: [],
        tasks_without_due_date_msg: '',
        completed_tasks_no_timeslots: [],
        completed_tasks_no_timeslots_msg: '',
        tasks_without_estimated_time: [],
        tasks_without_estimated_time_msg: '',
        list_tasks_with_missing_info: false
      };
    }

    // ComponentDidMount is used to execute the code 
    componentDidMount() {
        const api_url = this.state.api_url;
        fetch(api_url)
            .then((res) => res.json())
            .then((json) => {
              this.setState({
                worked: json.worked ? json.worked : 0,
                estimated: json.estimated ? json.estimated : 0,
                dateFormat: json.dateFormat ? json.dateFormat : 'MM/DD/YYYY',
                workedTitle: json.workedTitle ? json.workedTitle : 'Total worked hours',
                estimatedTitle: json.estimatedTitle ? json.estimatedTitle : 'Total estimated hours',
                chartData: json.chartData ? json.chartData : '',
                decimals: json.decimals ? json.decimals : 0,
                decimalsSeparator: json.decimalsSeparator ? json.decimalsSeparator : '.',
                thousandSeparator: json.thousandSeparator ? json.thousandSeparator : ',',
                DataisLoaded: true,
                tasks_without_due_date: json.tasks_without_due_date ? json.tasks_without_due_date : [],
                tasks_without_due_date_msg: json.tasks_without_due_date_msg ? json.tasks_without_due_date_msg : '',
                completed_tasks_no_timeslots: json.completed_tasks_no_timeslots ? json.completed_tasks_no_timeslots : [],
                completed_tasks_no_timeslots_msg: json.completed_tasks_no_timeslots_msg ? json.completed_tasks_no_timeslots_msg : '',
                tasks_without_estimated_time: json. tasks_without_estimated_time ? json. tasks_without_estimated_time : [],
                tasks_without_estimated_time_msg: json. tasks_without_estimated_time_msg ? json. tasks_without_estimated_time_msg : '',
                list_tasks_with_missing_info: json.list_tasks_with_missing_info ? json.list_tasks_with_missing_info : false
              });
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
  
  function showWorkedHoursWidget(api_url, no_obj_msg, element){
    ReactDOM.render(<WorkedHoursWidget api_url={api_url} no_obj_msg={no_obj_msg}/>,
      element);
  };
  
  module.exports = showWorkedHoursWidget;