<template>

    <h3 class="uk-panel-title">{{ config.metrics | transGaCol }} - {{ config.dimensions | transGaCol }}</h3>

    <div v-el="view"></div>

    <p>{{ config.startDate }}</p>

    <label>
        Max Results:
        <input type="number" v-model="$parent.widget.config.results">
    </label>

</template>

<script>

    module.exports = {

        view: {
            id: 'table',
            label: 'Table',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                options: {
                    sortColumn: 1,
                    sortAscending: false,
                    width: '100%'
                }
            }
        },

        created: function () {
            this.$on('request', function (params) {
                params.maxResults = this.config.results || 5;
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
                this.$add('chart', new google.visualization.Table(this.$$.view));

                this.chart.draw(this.dataTable, this.options);
            }
        }
    };

</script>
