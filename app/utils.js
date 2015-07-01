var _ = require('lodash');

module.exports = {


    createMetricFormatter: function (metric) {
        if (metric == 'ga:bounceRate' || metric == 'ga:percentNewSessions') {
            return new google.visualization.NumberFormat({
                fractionDigits: 2,
                suffix: ' %'
            });
        }

        if (metric == 'ga:pageviewsPerSession' || metric == 'ga:avgSessionDuration') {
            return new google.visualization.NumberFormat({
                fractionDigits: 2
            });
        }

        return false;
    },

    transCols: function (dataTable) {
        _.forEach(dataTable.cols, function (value) {
            value.label = Vue.prototype.$trans(value.label);
        });
    }

    //secToTime: function (value) {
    //    var sec_num = parseInt(value, 10);
    //    var hours = Math.floor(sec_num / 3600);
    //    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    //    var seconds = sec_num - (hours * 3600) - (minutes * 60);
    //
    //    if (hours < 10) {
    //        hours = '0' + hours;
    //    }
    //
    //    if (minutes < 10) {
    //        minutes = '0' + minutes;
    //    }
    //
    //    if (seconds < 10) {
    //        seconds = '0' + seconds;
    //    }
    //
    //    return hours + ':' + minutes + ':' + seconds;
    //}
};