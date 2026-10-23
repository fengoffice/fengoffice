"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); Object.defineProperty(subClass, "prototype", { writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

var React = require('react');

var PureComponent = require('react').PureComponent;

var ReactDOM = require('react-dom');

var _require = require('recharts'),
    AreaChart = _require.AreaChart,
    Area = _require.Area,
    XAxis = _require.XAxis,
    YAxis = _require.YAxis,
    CartesianGrid = _require.CartesianGrid,
    Tooltip = _require.Tooltip;

var data = [{
  name: 'Page A',
  uv: 4000,
  pv: 2400,
  amt: 2400
}, {
  name: 'Page B',
  uv: 3000,
  pv: 1398,
  amt: 2210
}, {
  name: 'Page C',
  uv: 2000,
  pv: 9800,
  amt: 2290
}, {
  name: 'Page D',
  uv: 2780,
  pv: 3908,
  amt: 2000
}, {
  name: 'Page E',
  uv: 1890,
  pv: 4800,
  amt: 2181
}, {
  name: 'Page F',
  uv: 2390,
  pv: 3800,
  amt: 2500
}, {
  name: 'Page G',
  uv: 3490,
  pv: 4300,
  amt: 2100
}];

var Example = /*#__PURE__*/function (_PureComponent) {
  _inherits(Example, _PureComponent);

  var _super = _createSuper(Example);

  function Example() {
    _classCallCheck(this, Example);

    return _super.apply(this, arguments);
  }

  _createClass(Example, [{
    key: "render",
    value: function render() {
      return /*#__PURE__*/React.createElement(AreaChart, {
        width: 400,
        height: 200,
        data: data,
        margin: {
          top: 10,
          right: 30,
          left: 0,
          bottom: 0
        }
      }, /*#__PURE__*/React.createElement(CartesianGrid, {
        strokeDasharray: "3 3"
      }), /*#__PURE__*/React.createElement(XAxis, {
        dataKey: "name"
      }), /*#__PURE__*/React.createElement(YAxis, null), /*#__PURE__*/React.createElement(Tooltip, null), /*#__PURE__*/React.createElement(Area, {
        type: "monotone",
        dataKey: "uv",
        stackId: "1",
        stroke: "#0589e3",
        fill: "#0589e3"
      }), /*#__PURE__*/React.createElement(Area, {
        type: "monotone",
        dataKey: "pv",
        stackId: "1",
        stroke: "#b4b3b3",
        fill: "#b4b3b3"
      }));
    }
  }]);

  return Example;
}(PureComponent);

function showChart(element) {
  ReactDOM.render( /*#__PURE__*/React.createElement(Example, null), element);
}

;
module.exports = showChart;