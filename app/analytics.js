var $ = require('jquery');

var Dashboard = require('dashboard');
var Analytics = require('./component/analytics.vue');
var Settings = require('./component/settings.vue');

// init google visualization api
google.load('visualization', '1', {
    language: document.documentElement.lang,
    packages: ['corechart', 'geochart']
});

// init dashboard widget
Dashboard.component('analytics', Analytics);

// inject settings modal
$(function () {
    window.$analytics.settingsVM = new Settings().$appendTo('body');
});
