var Analytics = require('./component/analytics.vue');
var Settings = require('./component/settings.vue');
var utils = require('./utils.js');

// init google visualization api
google.load('visualization', '1.0', {'packages': ['corechart', 'table', 'geochart']});

// init dashboard widget
window.Dashboard.component('analytics', Analytics);

// inject settings modal
$(function () {
    window.$analytics.settingsVM = new Settings().$appendTo('body');
});

// add google translation filter
Vue.filter('transGaCol', function (metric) {
    return utils.transCol(metric);
});

