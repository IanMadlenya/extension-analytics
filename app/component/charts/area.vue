<template>

    <h3 class="uk-panel-title">{{ total }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="chart"></div>

</template>

<script>
    var utils = require('../../utils.js');

    module.exports = {

        chart: {
            id: 'area',
            label: 'Area Chart',
            description: function () {

            },
            defaults: {}
        },

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            return {
                options: {
                    areaOpacity: 0.1,
                    colors: ['#058DC7'],
                    legend: 'none',
                    lineWidth: 4,
                    pointSize: 8,
                    hAxis: {
                        baselineColor: '#fff',
                        gridlines: {'color': 'none'},
                        showTextEvery: 1,
                        textPosition: 'out',
                        textStyle: {'color': '#058DC7'}
                    },
                    vAxis: {
                        baselineColor: '#ccc',
                        gridlines: {'color': '#fafafa'},
                        textPosition: 'out',
                        textStyle: {'color': '#058DC7'}
                    },
                    chartArea: {
                        left: 35,
                        height: '85%',
                        top: 5
                    }
                }
            }
        },

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);

            this.$on('request', function (params) {
                if (params.dimensions == 'ga:date' && params.startDate == '365daysAgo') {
                    params.dimensions = 'ga:yearMonth';
                }
            });

            this.$on('render', function () {
                this.dataTable = new google.visualization.DataTable(this.result.dataTable);
                this.chart = new google.visualization.AreaChart(this.$$.chart);

                if (this.config.startDate == '7daysAgo') {
                    this.options.hAxis.format = 'E';
                } else if (this.config.startDate == '30daysAgo') {
                    var format = window.$globalize.main[window.$globalize.locale].dates.calendars.gregorian.dateFormats.medium;
                    format = format.replace(/[^md]*y[^md]*/i, '');
                    this.options.hAxis.format = format;
                } else if (this.config.startDate == '365daysAgo') {
                    this.options.hAxis.showTextEvery = 1;
                    this.options.hAxis.format = 'MMM';

                    var dateFormatter = new google.visualization.DateFormat({
                        pattern: "MMMM, y"
                    });

                    dateFormatter.format(this.dataTable, 0);
                }

                if (this.formatter) {
                    this.formatter.format(this.dataTable, 1);
                }

                if (this.config.metrics == 'ga:bounceRate' || this.config.metrics == 'ga:percentNewSessions') {
                    this.options.vAxis.format = '#\'%\'';
                }

                this.setSize();
                this.chart.draw(this.dataTable, this.options);
            });

            this.$on('resize', function () {
                if (this.chart) {
                    this.setSize();
                    this.chart.draw(this.dataTable, this.options);
                }
            });
        },

        methods: {

            setSize: function () {
                this.options.chartArea.width = this.$el.parentElement.offsetWidth - 45;
            }

        },

        computed: {

            total: function () {
                if (this.result && this.result.totalsForAllResults) {
                    var total = this.result.totalsForAllResults[this.config.metrics];
                    if (this.formatter !== false) {
                        return this.formatter.formatValue(total);
                    }

                    return total;
                }
            }
        }
    };

</script>