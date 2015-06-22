<template>

    <h3 class="uk-panel-title">{{ config.metrics | transGaCol }} this {{ config.startDate}}</h3>

    <div v-el="view"></div>

</template>

<script>

    module.exports = {

        view: {
            id: 'area',
            label: 'Area Chart',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                options: {
                    "height": 150,
                    "theme": "maximized",
                    "legend": "none",
                    "backgroundColor": "#FFF",
                    "colors": ["#058DC7"],
                    "areaOpacity": 0.1,
                    "pointSize": 8,
                    "lineWidth": 4,
                    "chartArea": {},
                    "hAxis": {
                        "format": "E",
                        "textPosition": "in",
                        "textStyle": {"color": "#058DC7"},
                        "showTextEvery": 1,
                        "baselineColor": "#fff",
                        "gridlines": {"color": "none"}
                    },
                    "vAxis": {
                        "textPosition": "in",
                        "textStyle": {"color": "#058DC7"},
                        "baselineColor": "#ccc",
                        "gridlines": {"color": "#fafafa"},
                        "maxValue": 0
                    }
                }
            }
        },

        created: function () {
            this.$on('request', function (params) {
                if (params.dimensions == 'ga:date' && params.startDate == '365daysAgo') {
                    params.dimensions = 'ga:yearMonth';
                }

                if (params.dimensions == 'ga:yearMonth' && params.startDate != '365daysAgo') {
                    params.dimensions = 'ga:date';
                }
            });

            this.$on('resize', function () {
                if (this.chart) {
                    this.chart.draw(this.dataTable, this.options);
                }
            });
        },

        methods: {
            render: function (result) {

                this.$add('dataTable', new google.visualization.DataTable(result.dataTable));
                this.$add('chart', new google.visualization.AreaChart(this.$$.view));

                if (this.config.startDate == '7daysAgo') {
                    this.options.hAxis.format = 'E';
                    this.options.hAxis.showTextEvery = 1;
                } else if (this.config.startDate == '30daysAgo') {
                    this.options.hAxis.format = 'MMM d';
                    this.options.hAxis.showTextEvery = 1;
                } else if (this.config.startDate == '365daysAgo') {
                    this.options.hAxis.showTextEvery = 1;
                    this.options.hAxis.format = 'MMM yy';

                    var dateFormatter = new google.visualization.DateFormat({
                        pattern: "MMMM yyyy"
                    });

                    dateFormatter.format(this.dataTable, 0);
                }

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>