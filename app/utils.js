var _ = require('lodash');

module.exports = {

    parseRows: function (dataTable, params) {
        var self = this;
        _.forEach(dataTable.rows, function (value) {
            value.c[value.c.length - 1].v = parseFloat(value.c[value.c.length - 1].v);
            value.c[value.c.length - 1].f = self.parseLabel(value.c[value.c.length - 1].v, params);
        });
    },

    parseCols: function (dataTable) {
        var self = this;
        _.forEach(dataTable.cols, function (value) {
            value.label = Vue.prototype.$trans(value.label);
        });
    },

    parseLabel: function (value, params) {
        if (params.metrics == 'ga:avgSessionDuration') {
            return this.secToTime(value);
        }

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