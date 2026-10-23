const path = require('path');

module.exports = {
    // bail:false lets entries without errors still produce output when one entry fails.
    // recharts alias forces CJS build to avoid recharts v3 / react-redux.mjs / React 16 incompatibility
    // (react-redux.mjs imports useSyncExternalStore which React 16 doesn't export).
    bail: false,
    resolve: {
        alias: {
            recharts: path.resolve(__dirname, 'node_modules/recharts/lib/index.js')
        }
    },
    entry: {
      showWorkedHoursWidget: './public/assets/javascript/react_src/showWorkedHoursWidget.js',
      showProjectStatisticsWidget: './public/assets/javascript/react_src/showProjectStatisticsWidget.js',
      showExpensesProgressWidget: './public/assets/javascript/react_src/showExpensesProgressWidget.js',
      showEarnedValueWidget: './public/assets/javascript/react_src/showEarnedValueWidget.js',
      showFinancialsWidget: './public/assets/javascript/react_src/showFinancialsWidget.js',
      showDimensionTableWidget: './public/assets/javascript/react_src/showDimensionTableWidget.js',
      showFeedWidget: './public/assets/javascript/react_src/showFeedWidget.js'

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
        splitChunks: false,
        minimizer: [
            new (require('terser-webpack-plugin'))({
                extractComments: false,
            }),
        ],
    }
  };
