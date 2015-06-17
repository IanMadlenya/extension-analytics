var Analytics = require('./component/analytics.vue');
var utils = require('./utils.js');

// init google visualization api
google.load('visualization', '1.0', {'packages': ['corechart', 'table', 'geochart']});

window.Dashboard.component('analytics', Analytics);

Vue.filter('transGaCol', function (metric) {
    return utils.transCol(metric);
});