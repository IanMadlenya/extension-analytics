<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans}}</h3>

    <div v-el="chart"></div>

</template>

<script>

    module.exports = {

        chart: {
            id: 'column',
            label: 'Column Chart',
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
                    colors: ['#058DC7'],
                    legend: 'none',
                    lineWidth: 4,
                    pointSize: 8,
                    theme: 'maximized',
                    hAxis: {
                        baselineColor: '#fff',
                        format: 'E',
                        gridlines: {
                            color: 'none'
                        },
                        showTextEvery: 1,
                        textPosition: 'out',
                        textStyle: {
                            color: '#058DC7'
                        }
                    },
                    vAxis: {
                        baselineColor: '#ccc',
                        gridlines: {
                            color: '#fafafa'
                        },
                        textPosition: 'out',
                        textStyle: {
                            color: '#058DC7'
                        }
                    },
                    chartArea: {
                        height: '85%',
                        width: '85%',
                        top: 5
                    }
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
                this.$add('chart', new google.visualization.ColumnChart(this.$$.chart));

                if (this.config.startDate == '7daysAgo') {
                    this.options.hAxis.format = 'E';
                    this.options.hAxis.showTextEvery = 1;
                } else if (this.config.startDate == '30daysAgo') {
                    this.options.hAxis.format = 'MMM d';
                    this.options.hAxis.showTextEvery = 1;
                } else if (this.config.startDate == '365daysAgo') {
                    this.options.hAxis.showTextEvery = 4;
                    this.options.hAxis.format = 'MMM yy';

                    var dateFormatter = new google.visualization.DateFormat({
                        pattern: 'MMMM yyyy'
                    });

                    dateFormatter.format(this.dataTable, 0);
                }

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>