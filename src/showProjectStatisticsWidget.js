var React = require('react');
var ReactDOM = require('react-dom');
var { ResponsiveContainer,
      PieChart,
      Pie,
      Sector,
      Cell,
      Tooltip,
      Legend } = require('recharts');

      


class ProjectStatisticsWidget extends React.Component {
    constructor(props) {
      super(props);
      this.state = { data: props.data};
    }

    renderCustomizedLegend = (props) => {
        const payload = this.state.data;
        return (
          <table className="tasks-widget-customized-legend">
            <tbody>
            {
              payload.map((entry) => {
                const { name, value, color } = entry
                if (value > 0) {
                    return (
                        <tr key={`${name}-legend`} className="tasks-widget-legend-row">
                            <td className="tasks-widget-legend-name"><svg className="tasks-widget-legend-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill={color} viewBox="0 0 24 24">
                            <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                            </svg>{name}</td>
                            <td className="tasks-widget-legend-value">{value}</td>
                        </tr>
                    )
                }
              })
            }
            </tbody>
          </table>
        )
      }
    
      render() {
          var originalData = this.state.data;
          var data = this.state.data.filter(item => item.value > 0);
          
        return (
          <div className="project-statistics-widget">

                
                <div className="tasks-pie-chart">
                    {/**
                    Guide on how to use recharts can be found here http://recharts.org/en-US/api
                    */}
                    <ResponsiveContainer width="100%" height={170}>
                    <PieChart>
                        <Pie
                        data={data}
                        cx={75}
                        cy={80}
                        innerRadius={60}
                        outerRadius={80}
                        fill="#8884d8"
                        paddingAngle={2}
                        dataKey="value"
                    >
                        {
                        data.map((entry, index) => <Cell key={`cell-${index}`} fill={entry.color} />)
                        }
                        </Pie>
                        <Tooltip />
                        <Legend
                        align="right"
                        verticalAlign="middle"
                        layout="vertical"
                        iconType="circle"
                        iconSize={10}

                        content={this.renderCustomizedLegend}
                        /*payload={
                            originalData.map(
                              item => ({
                                id: item.name,
                                type: "circle",
                                value: `${item.name} - ${item.value}`,
                                color: `${item.color}`     
                              })
                            )
                          }*/ 
                          />
                  </PieChart>
                  </ResponsiveContainer>
                </div>
            
          </div>
        );
      }
  };
  
  function showProjectStatisticsWidget(data, element){
    ReactDOM.render(<ProjectStatisticsWidget data={data} />,
      element);
  };
  
  module.exports = showProjectStatisticsWidget;