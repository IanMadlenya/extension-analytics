<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="view"></div>

</template>

<script>
    var _ = require('lodash');

    module.exports = {

        view: {
            id: 'geo',
            label: 'Geo Chart',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                options: {
                    colors: ['#AADFF3', '#058DC7'],
                    displayMode: 'auto'
                },
                continents: require('../data/continents.json'),
                subContinents: require('../data/subContinents.json')
            }
        },

        created: function () {
            this.$on('request', function (params) {
                if (params.dimensions == 'ga:city') {
                    params.maxResults = 20;
                    // params.dimensions = 'ga:latitude,ga:longitude,'.concat(params.dimensions);
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
                var vm = this;

                this.$add('dataTable', new google.visualization.DataTable(result.dataTable));
                this.$add('chart', new google.visualization.GeoChart(this.$$.view));

                switch (this.config.dimensions) {
                    case 'ga:city':
                        this.options.displayMode = 'markers';
                        break;

                    case 'ga:country':
                        this.options.resolution = 'countries';
                        break;

                    case 'ga:continent':
                        this.options.resolution = 'continents';

                        result.dataTable.rows = _.forEach(result.dataTable.rows, function (value) {
                            value.c[0].f = value.c[0].v;
                            value.c[0].v = _.result(_.find(vm.continents, {label: value.c[0].v}), 'code');
                        });
                        break;

                    case 'ga:subContinent':
                        this.options.resolution = 'subcontinents';

                        result.dataTable.rows = _.forEach(result.dataTable.rows, function (value) {
                            value.c[0].f = value.c[0].v;
                            value.c[0].v = _.result(_.find(vm.subContinents, {label: value.c[0].v}), 'code');
                        });
                        break;
                }

                this.chart.draw(this.dataTable, this.options);
            }
        }

    };

</script>