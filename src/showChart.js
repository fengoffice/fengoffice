var React = require('react');
var PureComponent = require('react').PureComponent;
var ReactDOM = require('react-dom');
var {  AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip } = require('recharts');


const data = [
    {
      name: 'Page A', uv: 4000, pv: 2400, amt: 2400,
    },
    {
      name: 'Page B', uv: 3000, pv: 1398, amt: 2210,
    },
    {
      name: 'Page C', uv: 2000, pv: 9800, amt: 2290,
    },
    {
      name: 'Page D', uv: 2780, pv: 3908, amt: 2000,
    },
    {
      name: 'Page E', uv: 1890, pv: 4800, amt: 2181,
    },
    {
      name: 'Page F', uv: 2390, pv: 3800, amt: 2500,
    },
    {
      name: 'Page G', uv: 3490, pv: 4300, amt: 2100,
    },
  ];
  
  class Example extends PureComponent {
  
    render() {
      return (
        <AreaChart
          width={400}
          height={200}
          data={data}
          margin={{
            top: 10, right: 30, left: 0, bottom: 0,
          }}
        >
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="name" />
          <YAxis />
          <Tooltip />
          <Area type="monotone" dataKey="uv" stackId="1" stroke="#0589e3" fill="#0589e3" />
          <Area type="monotone" dataKey="pv" stackId="1" stroke="#b4b3b3" fill="#b4b3b3" />
        </AreaChart>
      );
    }
  }
  

function showChart(element){
    ReactDOM.render(<Example />,
      element);
};

module.exports =  showChart;
