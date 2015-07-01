var _ = require('lodash');

module.exports = {


    createMetricFormatter: function (metric) {
        if (metric == 'ga:bounceRate' || metric == 'ga:percentNewSessions') {
            return new google.visualization.NumberFormat({
                fractionDigits: 1,
                suffix: '%'
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

        if (params.dimensions === 'ga:yearMonth') {
            result.dataTable.cols[0].type = 'date';
            result.columnHeaders[0].dataType = 'DATE';
        }

        _.forEach(result.dataTable.rows, function (value) {
            if (params.dimensions === 'ga:yearMonth') {
                var month = value.c[0].v.substr(4, 2) - 1;

                if (month < 10) {
                    month = '0' + month;
                }

                console.log(value.c[0].v)
                value.c[0].v = 'Date(' + value.c[0].v.substr(0, 4) + ',' + month + ',01)';
                console.log(value.c[0].v)
            }

            if (params.metrics === 'ga:avgSessionDuration') {
                value.c[value.c.length - 1].v = parseInt(value.c[value.c.length - 1].v, 10) / 60;
                value.c[value.c.length - 1].f = Math.round(value.c[value.c.length - 1].v * 100) / 100 + ' min';
            }
        });

        _.forEach(result.totalsForAllResults, function (value, metric) {
            if (params.metrics === 'ga:avgSessionDuration') {
                result.totalsForAllResults[metric] =  Math.round(value / 60 * 100) / 100 + ' min';
            }
        });
    }
};