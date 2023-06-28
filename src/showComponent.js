
var React = require('react');
var ReactDOM = require('react-dom');

class MyLayoutComponent extends React.Component {
  constructor(props) {
    super(props);
    this.state = { name: props.name };
  }
    render() {
      return (
        <div>
          <h3>React Loaded again, {this.state.name}</h3>
        </div>
      );
    }
};

function showComponent(name, element){
  ReactDOM.render(<MyLayoutComponent name={name} />,
    element);
};

module.exports = showComponent;

