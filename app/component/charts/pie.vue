<template>

    <h3 class="uk-panel-title">{{ total }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="chart"></div>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../../utils.js');

    module.exports = {

        chart: {
            id: 'pie',
            label: 'Pie Chart',
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
                    theme: 'maximized',
                    pieHole: 0.5,
                    legend: {
                        alignment: 'center',
                        position: 'bottom'
                    },
                    chartArea: {
                        height: '85%',
                        top: 7
                    },
                    sliceVisibilityThreshold: 1 / 120
                }
            }
        },

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);

            this.$on('resize', function () {
                if (this.chart) {
                    this.setSize();
                    this.chart.draw(this.dataTable, this.options);
                }
            });

            this.$on('render', function () {
                _.forEach(this.result.dataTable.rows, function (value) {
                    value.c[value.c.length - 1].v = parseFloat(value.c[value.c.length - 1].v);
                });

                this.dataTable = new google.visualization.DataTable(this.result.dataTable);
                this.chart = new google.visualization.PieChart(this.$$.chart)

                if (this.formatter) {
                    this.formatter.format(this.dataTable, 1);
                }

                this.setSize();
                this.chart.draw(this.dataTable, this.options);
            });
        },

        methods: {

            setSize: function () {
                this.options.height = this.$el.parentElement.offsetWidth + 20;
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