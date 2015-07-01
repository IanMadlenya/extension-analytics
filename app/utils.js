var _ = require('lodash');

module.exports = {


    createMetricFormatter: function (metric) {
        if (metric == 'ga:bounceRate' || metric == 'ga:percentNewSessions') {
            return new google.visualization.NumberFormat({
                fractionDigits: 2,
                suffix: ' %'
            });
        }

        if (metric == 'ga:pageviewsPerSession'
            // || metric == 'ga:avgSessionDuration'
        ) {
            return new google.visualization.NumberFormat({
                fractionDigits: 2
            });
        }

        return false;
    },

    transCols: function (result) {
        _.forEach(result.dataTable.cols, function (value) {
            value.label = Vue.prototype.$trans(value.label);
        });
    },

    parseRows: function (result, params) {
        var self = this;

        _.forEach(result.dataTable.rows, function (value) {
            value.c[value.c.length - 1].v = parseFloat(value.c[value.c.length - 1].v);
            value.c[value.c.length - 1].f = self.parseLabel(value.c[value.c.length - 1].v, params.metrics);
        });

        _.forEach(result.totalsForAllResults, function (value, metric) {
            result.totalsForAllResults[metric] = self.parseLabel(value, metric);
        });
    },

    parseLabel: function (value, metric) {
        if (metric == 'ga:avgSessionDuration') {
            return this.secToTime(value);
        }

        value = parseFloat(value);
        return value.toFixed(2).replace(/\.00$/, '')
    },

    secToTime: function (value) {
        var sec_num = parseInt(value, 10);
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours < 10) {
            hours = '0' + hours;
        }

        if (minutes < 10) {
            minutes = '0' + minutes;
        }

        if (seconds < 10) {
            seconds = '0' + seconds;
        }

        return hours + ':' + minutes + ':' + seconds;
    }
};