var Analytics = require('./component/analytics.vue');

// init google visualization api
google.load('visualization', '1.0', {'packages': ['corechart', 'table', 'geochart']});

window.Dashboard.component('analytics', Analytics);

