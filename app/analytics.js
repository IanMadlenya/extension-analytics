var $ = require('jquery');

var Dashboard = require('dashboard');
var Analytics = require('./component/analytics.vue');
var Settings = require('./component/settings.vue');

Settings.http = Analytics.http = {
    beforeSend: function (request, options) {
        options.url = 'admin/' + options.url;
    }
};

// init google visualization api
google.load('visualization', '1', {
    language: document.documentElement.lang,
    packages: ['corechart', 'geochart']
});

// init dashboard widget
Dashboard.components['analytics'] = Analytics;

// inject settings modal
$(function () {

    window.$analytics.settingsVM = new Vue(Settings).$appendTo('body');

});
