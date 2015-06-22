<template>

    <h3 class="uk-panel-title">{{ config.metrics | transGaCol }} this {{ config.startDate}}</h3>

    <div v-el="view"></div>

</template>

<script>

    module.exports = {

        view: {
            id: 'column',
            label: 'Column Chart',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                options: {
                  //  theme: "maximized",
                    legend: {
                        alignment: "center",
                        position: "bottom"
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
                this.$add('chart', new google.visualization.ColumnChart(this.$$.view));

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>