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
      this.state = {
          data: [],
          DataisLoaded: false,
          api_url: props.api_url,
          no_obj_msg: props.no_obj_msg
      };
    }

    // ComponentDidMount is used to execute the code 
    componentDidMount() {
      const api_url = this.state.api_url;
      fetch(api_url)
          .then((res) => res.json())
          .then((json) => {
            this.setState({
              data: json,
              DataisLoaded: true
            });
          });
    }

    renderCustomizedLegend = (props) => {
        const payload = this.state.data;
        return (
          <table className="tasks-widget-customized-legend">
            <tbody>
            {
              payload.map((entry) => {
                const { name, value, color, list_url } = entry
                if (value > 0) {
                    return (
                        <tr key={`${name}-legend`} className="tasks-widget-legend-row">
                            <td className="tasks-widget-legend-name"><svg className="tasks-widget-legend-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill={color} viewBox="0 0 24 24">
                            <path d="M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"></path>
                            </svg>
                            <a className="internalLink" href={list_url}>{name}</a>
							</td>
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

         var { DataisLoaded, data, no_obj_msg } = this.state;
         var data = data.filter(item => item.value > 0);
         // Show loader while fetching data
         if (!DataisLoaded) return <div className="statistics-widget__placeholder">
            <div className="statistics-widget__placeholder-circle"></div>
            <div>
              <div className="statistics-widget__placeholder-line-1"></div>
              <div className="statistics-widget__placeholder-line-2"></div>
            </div>
         </div> ;
        
        if(data.length != 0){
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
                          innerRadius={40}
                          outerRadius={80}
                          fill="#8884d8"
                          paddingAngle={0}
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
                          />
                    </PieChart>
                    </ResponsiveContainer>
                  </div>
              
            </div>
          );
        } else {
          return (<div><p className="no-obj-widget-msg">{no_obj_msg}</p></div>);
        }
      }
  };
  
  function showProjectStatisticsWidget(api_url, no_obj_msg, element){
    ReactDOM.render(<ProjectStatisticsWidget api_url={api_url} no_obj_msg={no_obj_msg} />,
      element);
  };
  
  module.exports = showProjectStatisticsWidget;