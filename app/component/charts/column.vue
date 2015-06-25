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

        data: function () {
            return {
                options: {
                    colors: ['#058DC7'],
                    legend: {
                        alignment: 'center',
                        position: 'bottom'
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

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>