var React = require('react');
var ReactDOM = require('react-dom');
var { ResponsiveContainer,
      AreaChart,
      Area,
      XAxis,
      YAxis,
      CartesianGrid,
      Tooltip,
      Legend } = require('recharts');


class WorkedHoursWidget extends React.Component {
    constructor(props) {
      super(props);
      this.state = { worked: props.data.worked ? props.data.worked : 0,
                     estimated: props.data.estimated ? props.data.estimated : 0,
                     chartData: props.data.chartData ? props.data.chartData : ''
                    };
    }

      render() {
         // Define variables that will be used in the returned component
         var worked = this.state.worked;
         var estimated = this.state.estimated;
         var chartData = this.state.chartData;
         var percentage = Math.round((worked / estimated) * 100);
         var percentStyle = {
             width: percentage+'%'
         }
         var remaining = estimated - worked;
         var remaining_percent = 100 - percentage;
         var budgeted = Math.round(estimated * 0.96);
         var variance = estimated - budgeted;
         
        return (
          <div className="worked-hours-widget">
            <p style={percentStyle} data-value={percentage}></p>


            <div className="progress-info">
                <div className="progress-bar-container">
                    {percentage <= 4 ? (
                        <div className="progress-label-small-number" style={percentStyle} data-value={percentage}></div>
                    ) : (
                        <div className="progress-label" style={percentStyle} data-value={percentage}></div>
                    )}
                    <div className="progress-bar">
                        <span className="progress-bar-fill" style={percentStyle}></span>
                    </div>
                </div>

            </div>

            <div className="progress-info">
                <div className="progress-text-container">
                    <div>Total worked hours</div>
                    <div><svg className="circle-icon icon-worked" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                    </svg><span className="widget-hours-number">{worked}</span> <span className="font-weight-400">hrs</span></div>
                </div>

                <div className="progress-text-container">
                    <div>Total estimated hours</div>
                    <div><svg className="circle-icon icon-estimated" version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                    </svg><span className="widget-hours-number">{estimated}</span> <span className="font-weight-400">hrs</span></div>
                </div>
            </div>

            {/** Render chart if chartData exists */}
            {chartData &&
                
                <div className="worked-hours-chart">
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
                                <stop offset="5%" stopColor="#d9d9d9" stopOpacity={0.8}/>
                                <stop offset="95%" stopColor="#d9d9d9" stopOpacity={0.1}/>
                            </linearGradient>
                            <linearGradient id="colorWorked" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="5%" stopColor="#20a1f8" stopOpacity={0.9}/>
                                <stop offset="95%" stopColor="#20a1f8" stopOpacity={0.2}/>
                            </linearGradient>
                        </defs>
                        <CartesianGrid strokeDasharray="3 3" vertical={false}/>
                        <XAxis dy={15} axisLine={false} tickSize={0} stroke="#888888" dataKey="date" interval="preserveStartEnd" minTickGap={70} height={40}/>
                        <YAxis dx={10} axisLine={false} tickSize={0} stroke="#888888" orientation="right" width={50} />
                        <Tooltip />
                        <Area type="monotone" dataKey="estimated" stroke="#888888" fill="url(#colorEstimated)" isAnimationActive={false}/>
                        <Area type="monotone" dataKey="worked" stroke="#20a1f8" fill="url(#colorWorked)" isAnimationActive={false}/>
                        </AreaChart>
                    </ResponsiveContainer>
                </div>
            }
          </div>
        );
      }
  };
  
  function showWorkedHoursWidget(data, element){
    ReactDOM.render(<WorkedHoursWidget data={data} />,
      element);
  };
  
  module.exports = showWorkedHoursWidget;