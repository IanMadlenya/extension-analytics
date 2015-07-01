<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="chart"></div>

</template>

<script>

    module.exports = {

        chart: {
            id: 'bar',
            label: 'Bar Chart',
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
                        gridlines: {'color': 'none'},
                        showTextEvery: 1,
                        textPosition: 'in',
                        textStyle: {
                            color: '#058DC7',
                            auraColor: '#FFF'
                        }
                    },
                    vAxis: {
                        baselineColor: '#ccc',
                        gridlines: {'color': '#fafafa'},
                        textPosition: 'in',
                        textStyle: {
                            color: '#058DC7',
                            auraColor: '#FFF'
                        }
                    }
                }
            }
        },

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);

            this.$on('resize', function () {
                if (this.chart) {
                    this.chart.draw(this.dataTable, this.options);
                }
            });
        },

        methods: {
            render: function (result) {

                this.$add('result', result);
                this.dataTable = new google.visualization.DataTable(result.dataTable);
                this.chart = new google.visualization.BarChart(this.$$.chart)

                if (this.formatter) {
                    this.formatter.format(this.dataTable, 1);
                }

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>