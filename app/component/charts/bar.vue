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

        data: function () {
            return {
                options: {
                    theme: "maximized"
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
                this.$add('chart', new google.visualization.BarChart(this.$$.chart));

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>