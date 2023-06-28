const path = require('path');

module.exports = {
    entry: {
      showWorkedHoursWidget: './public/assets/javascript/react_src/showWorkedHoursWidget.js',
      showProjectStatisticsWidget: './public/assets/javascript/react_src/showProjectStatisticsWidget.js',
      showExpensesProgressWidget: './public/assets/javascript/react_src/showExpensesProgressWidget.js',
      showEarnedValueWidget: './public/assets/javascript/react_src/showEarnedValueWidget.js',
      showFinancialsWidget: './public/assets/javascript/react_src/showFinancialsWidget.js'

    },
    output: {
      libraryTarget: 'var',
      library: '[name]',
      filename: '[name].js',
      path: __dirname + '/public/assets/javascript/react_production',

    },
    module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /node_modules/,
            use: 'babel-loader'
          }
        ]
    },
    optimization: {
        splitChunks: {
            chunks: 'all',
        }
    }
  };
