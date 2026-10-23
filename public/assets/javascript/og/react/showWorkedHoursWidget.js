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

var ReactDOM = require('react-dom');

var _require = require('recharts'),
    ResponsiveContainer = _require.ResponsiveContainer,
    AreaChart = _require.AreaChart,
    Area = _require.Area,
    XAxis = _require.XAxis,
    YAxis = _require.YAxis,
    CartesianGrid = _require.CartesianGrid,
    Tooltip = _require.Tooltip,
    Legend = _require.Legend;

var WorkedHoursWidget = /*#__PURE__*/function (_React$Component) {
  _inherits(WorkedHoursWidget, _React$Component);

  var _super = _createSuper(WorkedHoursWidget);

  function WorkedHoursWidget(props) {
    var _this;

    _classCallCheck(this, WorkedHoursWidget);

    _this = _super.call(this, props);
    _this.state = {
      worked: props.data.worked ? props.data.worked : 0,
      estimated: props.data.estimated ? props.data.estimated : 0,
      chartData: props.data.chartData ? props.data.chartData : ''
    };
    return _this;
  }

  _createClass(WorkedHoursWidget, [{
    key: "render",
    value: function render() {
      // Define variables that will be used in the returned component
      var worked = this.state.worked;
      var estimated = this.state.estimated;
      var chartData = this.state.chartData;
      var percentage = Math.round(worked / estimated * 100);
      var percentStyle = {
        width: percentage + '%'
      };
      var remaining = estimated - worked;
      var remaining_percent = 100 - percentage;
      var budgeted = Math.round(estimated * 0.96);
      var variance = estimated - budgeted;
      return /*#__PURE__*/React.createElement("div", {
        className: "worked-hours-widget"
      }, /*#__PURE__*/React.createElement("p", {
        style: percentStyle,
        "data-value": percentage
      }), /*#__PURE__*/React.createElement("div", {
        className: "progress-info"
      }, /*#__PURE__*/React.createElement("div", {
        className: "progress-bar-container"
      }, percentage <= 4 ? /*#__PURE__*/React.createElement("div", {
        className: "progress-label-small-number",
        style: percentStyle,
        "data-value": percentage
      }) : /*#__PURE__*/React.createElement("div", {
        className: "progress-label",
        style: percentStyle,
        "data-value": percentage
      }), /*#__PURE__*/React.createElement("div", {
        className: "progress-bar"
      }, /*#__PURE__*/React.createElement("span", {
        className: "progress-bar-fill",
        style: percentStyle
      })))), /*#__PURE__*/React.createElement("div", {
        className: "progress-info"
      }, /*#__PURE__*/React.createElement("div", {
        className: "progress-text-container"
      }, /*#__PURE__*/React.createElement("div", null, "Total worked hours"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("svg", {
        className: "circle-icon icon-worked",
        version: "1.1",
        xmlns: "http://www.w3.org/2000/svg",
        width: "24",
        height: "24",
        viewBox: "0 0 24 24"
      }, /*#__PURE__*/React.createElement("path", {
        d: "M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"
      })), /*#__PURE__*/React.createElement("span", {
        className: "widget-hours-number"
      }, worked), " ", /*#__PURE__*/React.createElement("span", {
        className: "font-weight-400"
      }, "hrs"))), /*#__PURE__*/React.createElement("div", {
        className: "progress-text-container"
      }, /*#__PURE__*/React.createElement("div", null, "Total estimated hours"), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("svg", {
        className: "circle-icon icon-estimated",
        version: "1.1",
        xmlns: "http://www.w3.org/2000/svg",
        width: "24",
        height: "24",
        viewBox: "0 0 24 24"
      }, /*#__PURE__*/React.createElement("path", {
        d: "M3.984 12q0-3.281 2.367-5.648t5.648-2.367 5.648 2.367 2.367 5.648-2.367 5.648-5.648 2.367-5.648-2.367-2.367-5.648z"
      })), /*#__PURE__*/React.createElement("span", {
        className: "widget-hours-number"
      }, estimated), " ", /*#__PURE__*/React.createElement("span", {
        className: "font-weight-400"
      }, "hrs")))), chartData && /*#__PURE__*/React.createElement("div", {
        className: "worked-hours-chart"
      }, /*#__PURE__*/React.createElement(ResponsiveContainer, {
        width: "100%",
        height: 200
      }, /*#__PURE__*/React.createElement(AreaChart, {
        data: chartData,
        margin: {
          top: 10,
          right: 0,
          left: 0,
          bottom: -10
        },
        padding: {}
      }, /*#__PURE__*/React.createElement("defs", null, /*#__PURE__*/React.createElement("linearGradient", {
        id: "colorEstimated",
        x1: "0",
        y1: "0",
        x2: "0",
        y2: "1"
      }, /*#__PURE__*/React.createElement("stop", {
        offset: "5%",
        stopColor: "#d9d9d9",
        stopOpacity: 0.8
      }), /*#__PURE__*/React.createElement("stop", {
        offset: "95%",
        stopColor: "#d9d9d9",
        stopOpacity: 0.1
      })), /*#__PURE__*/React.createElement("linearGradient", {
        id: "colorWorked",
        x1: "0",
        y1: "0",
        x2: "0",
        y2: "1"
      }, /*#__PURE__*/React.createElement("stop", {
        offset: "5%",
        stopColor: "#20a1f8",
        stopOpacity: 0.9
      }), /*#__PURE__*/React.createElement("stop", {
        offset: "95%",
        stopColor: "#20a1f8",
        stopOpacity: 0.2
      }))), /*#__PURE__*/React.createElement(CartesianGrid, {
        strokeDasharray: "3 3",
        vertical: false
      }), /*#__PURE__*/React.createElement(XAxis, {
        dy: 15,
        axisLine: false,
        tickSize: 0,
        stroke: "#888888",
        dataKey: "date",
        interval: "preserveStartEnd",
        minTickGap: 70,
        height: 40
      }), /*#__PURE__*/React.createElement(YAxis, {
        dx: 10,
        axisLine: false,
        tickSize: 0,
        stroke: "#888888",
        orientation: "right",
        width: 50
      }), /*#__PURE__*/React.createElement(Tooltip, null), /*#__PURE__*/React.createElement(Area, {
        type: "monotone",
        dataKey: "estimated",
        stroke: "#888888",
        fill: "url(#colorEstimated)",
        isAnimationActive: false
      }), /*#__PURE__*/React.createElement(Area, {
        type: "monotone",
        dataKey: "worked",
        stroke: "#20a1f8",
        fill: "url(#colorWorked)",
        isAnimationActive: false
      })))));
    }
  }]);

  return WorkedHoursWidget;
}(React.Component);

;

function showWorkedHoursWidget(data, element) {
  ReactDOM.render( /*#__PURE__*/React.createElement(WorkedHoursWidget, {
    data: data
  }), element);
}

;
module.exports = showWorkedHoursWidget;