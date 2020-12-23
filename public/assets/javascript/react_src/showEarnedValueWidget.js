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

class EarnedValueWidget extends React.Component {
    constructor(props) {
      super(props);
      this.state = { currencySymbol: props.data.currencySymbol ? props.data.currencySymbol : '$',
                     dateFormat: props.data.dateFormat ? props.data.dateFormat : 'MM/DD/YYYY',
                     executed: props.data.executed ? props.data.executed : 0,
                     estimated: props.data.estimated ? props.data.estimated : 0,
                     executedTitle: props.data.executedTitle ? props.data.executedTitle : 'Executed labor',
                     estimatedTitle: props.data.estimatedTitle ? props.data.estimatedTitle : 'Estimated labor',
                     chartData: props.data.chartData ? props.data.chartData : '',
                     decimals: props.data.decimals ? props.data.decimals : 0,
                     decimalsSeparator: props.data.decimalsSeparator ? props.data.decimalsSeparator : '.',
                     thousandSeparator: props.data.thousandSeparator ? props.data.thousandSeparator : ','
                    };
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
        const dateFormat = this.state.dateFormat;
        const estimatedTitle = this.state.estimatedTitle;
        var chartData = this.state.chartData;
        chartData.forEach(d => {
            d.date = moment(d.date).valueOf();
        });
        return (
            <div className="progress-widget-container">

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
                        <Area type="monotone" dataKey="estimated" stroke="#888888" fill="url(#colorEstimated)" isAnimationActive={false}/>
                        <Area type="monotone" dataKey="executed" stroke="#0cbe9b" fill="url(#colorExecuted)" isAnimationActive={false}/>
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
