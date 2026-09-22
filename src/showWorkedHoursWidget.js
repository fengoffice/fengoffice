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


class WorkedHoursWidget extends React.Component {
    constructor(props) {
      super(props);
      this.state = { 
        worked: props.data.worked ? props.data.worked : 0,
        estimated: props.data.estimated ? props.data.estimated : 0,
        chartData: this.prepareChartData(props.data.chartData || []),
        allChartData: this.prepareChartData(props.data.chartData || [])
      };
      
      // Bind date filtering methods
      this.performDateFiltering = this.performDateFiltering.bind(this);
      this.performAllTimeFiltering = this.performAllTimeFiltering.bind(this);
    }

    // Prepare chart data by ensuring both formats are available
    prepareChartData(data) {
        return data.map(item => ({
            ...item,
            // Ensure both naming conventions are available for chart compatibility
            estimated: item.total_budget,
            worked: item.total_earned
        }));
    }

    performDateFiltering(startDate, endDate) {
        // Since backend provides cumulative data, filter by date range
        // But also handle cases where requested range extends beyond available data
        const allData = this.state.allChartData;
        
        // First, filter existing data within the requested range
        const filteredData = allData.filter(item => {
            const itemDate = moment(item.date, 'MM/DD/YYYY');
            return itemDate.isBetween(startDate, endDate, 'day', '[]');
        });

        // If no data exists in the requested range, create empty data points
        if (filteredData.length === 0) {
            // Create empty data points for the requested range
            const emptyData = this.createEmptyDataRange(startDate, endDate);
            this.setState({
                chartData: emptyData,
                estimated: 0,
                worked: 0
            });
            return;
        }

        // Get the actual data range from available data
        const dataStartDate = moment(allData[0].date, 'MM/DD/YYYY');
        const dataEndDate = moment(allData[allData.length - 1].date, 'MM/DD/YYYY');

        // Extend the filtered data to fill the entire requested range
        const extendedData = this.extendDataToRange(filteredData, startDate, endDate, dataStartDate, dataEndDate);

        // Use the final values from the filtered data (not extended empty data)
        const finalEstimated = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_budget : 0;
        const finalWorked = filteredData.length > 0 ? filteredData[filteredData.length - 1].total_earned : 0;

        this.setState({
            chartData: extendedData,
            estimated: finalEstimated,
            worked: finalWorked
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
                total_earned: 0,
                estimated: 0,
                worked: 0
            });
            current.add(1, 'day');
        }
        
        return emptyData;
    }

    // Extend data to fill the entire requested range with zeros where no data exists
    extendDataToRange(filteredData, requestedStart, requestedEnd, dataStart, dataEnd) {
        const result = [];
        const current = requestedStart.clone();
        
        while (current.isSameOrBefore(requestedEnd)) {
            const currentDateStr = current.format('MM/DD/YYYY');
            
            // Check if we have actual data for this date
            const existingData = filteredData.find(item => item.date === currentDateStr);
            
            if (existingData) {
                // Use actual data
                result.push(existingData);
            } else {
                // Create zero data point
                // If this date is before our data range, use zeros
                // If this date is after our data range, maintain the last known values
                let budget = 0;
                let earned = 0;
                
                if (current.isAfter(dataEnd) && filteredData.length > 0) {
                    // Date is after our data range - maintain final values
                    const lastData = filteredData[filteredData.length - 1];
                    budget = lastData.total_budget;
                    earned = lastData.total_earned;
                }
                
                result.push({
                    date: currentDateStr,
                    total_budget: budget,
                    total_earned: earned,
                    estimated: budget,
                    worked: earned
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
        const finalWorked = fullData.length > 0 ? fullData[fullData.length - 1].total_earned : 0;

        this.setState({
            chartData: fullData,
            estimated: finalEstimated,
            worked: finalWorked
        });
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