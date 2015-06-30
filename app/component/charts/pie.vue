<template>

    <h3 class="uk-panel-title">{{ total }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="chart"></div>

</template>

<script>
    var _ = require('lodash');

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
            this.$on('resize', function () {
                if (this.chart) {
                    this.options.height = this.$el.parentElement.offsetWidth + 20;
                    this.chart.draw(this.dataTable, this.options);
                }
            });
        },

        methods: {
            render: function (result) {

                _.forEach(result.dataTable.rows, function (value) {
                    value.c[value.c.length - 1].v = parseFloat(value.c[value.c.length - 1].v);
                });

                this.$set('result', result);
                this.$add('dataTable', new google.visualization.DataTable(result.dataTable));
                this.$add('chart', new google.visualization.PieChart(this.$$.chart));

                this.options.height = this.$el.parentElement.offsetWidth + 20;
                this.chart.draw(this.dataTable, this.options);
            }
        },

        computed: {

            total: function () {
                if (this.result && this.result.totalsForAllResults) {
                    return this.result.totalsForAllResults[this.config.metrics];
                }
            }
        }
    };

</script>