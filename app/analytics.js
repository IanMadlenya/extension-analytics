var Analytics = require('./component/analytics.vue');
var Settings = require('./component/settings.vue');

// init google visualization api
google.load('visualization', '1', {
    language: window.$globalize.locale,
    packages: ['corechart', 'geochart']
});

// init dashboard widget
window.Dashboard.component('analytics', Analytics);

// inject settings modal
$(function () {
    window.$analytics.settingsVM = new Settings().$appendTo('body');
});
