<template>

    <h3>{{ config.metrics | transGaCol }} - {{ config.dimensions | transGaCol }}</h3>

    <div v-el="view"></div>

    <p>{{ config.startDate }}</p>

</template>

<script>
    var _ = require('lodash');

    module.exports = {

        view: {
            id: 'pie',
            label: 'Pie Chart',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                options: {
                    "theme": "maximized",
                    "height": 282,
                   // "pieHole": 0.5,
                    "legend": {
                        "alignment": "center",
                        "position": "top"
                    },
                    "chartArea": {
                        "top": 40,
                        "height": "82%"
                    },
                    "sliceVisibilityThreshold": 1/120
                }
            }
        },

        created: function () {
            this.$on('resize', function () {
                if (this.chart) {
                    this.chart.draw(this.dataTable, this.options);
                }
            });
        },

        methods: {
            render: function (result) {
                this.$add('dataTable', new google.visualization.DataTable(result.dataTable));
                this.$add('chart', new google.visualization.PieChart(this.$$.view));

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>