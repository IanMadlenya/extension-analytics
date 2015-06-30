<template>

    <h3 class="uk-panel-title">{{ total }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <div v-el="chart"></div>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../../utils.js');

    var continents = require('../../data/continents.json');
    var subcontinents = require('../../data/sub-continents.json');

    module.exports = {

        chart: {
            id: 'geo',
            label: 'Geo Chart',
            description: function () {

            },
            defaults: {},

            customOptions: require('./geo-options.vue')
        },

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            return {
                options: {
                    colors: ['#AADFF3', '#058DC7'],
                    displayMode: 'auto'
                }
            }
        },

        created: function () {
            this.$on('request', function (params) {
                if (params.dimensions == 'ga:city') {
                    params.dimensions = 'ga:latitude,ga:longitude,'.concat(params.dimensions);
                }

                if (this.config.region && this.config.region != '0') {
                    var filter;

                    if (filter = _.result(_.find(continents, {code: this.config.region}), 'label')) {
                        // region is a continent
                        params.filters = 'ga:continent==' + filter;
                    } else if (filter = _.result(_.find(subcontinents, {code: this.config.region}), 'label')) {
                        // region is a subcontinent
                        params.filters = 'ga:subcontinent==' + filter;
                    } else {
                        // region is a country
                        params.filters = 'ga:countryIsoCode==' + this.config.region;
                    }
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

                if (this.config.region && this.config.region != '0') {
                    this.options.region = this.config.region;
                }

                switch (this.config.dimensions) {
                    case 'ga:city':
                        this.options.displayMode = 'markers';

                        result.dataTable.cols[0].type = 'number';
                        result.dataTable.cols[1].type = 'number';

                        break;

                    case 'ga:country':
                        this.options.resolution = 'countries';

                        break;

                    case 'ga:continent':
                        this.options.resolution = 'continents';

                        result.dataTable.rows = _.forEach(result.dataTable.rows, function (value) {
                            value.c[0].f = value.c[0].v;
                            value.c[0].v = _.result(_.find(continents, {label: value.c[0].v}), 'code');
                        });

                        break;

                    case 'ga:subContinent':
                        this.options.resolution = 'subcontinents';

                        result.dataTable.rows = _.forEach(result.dataTable.rows, function (value) {
                            value.c[0].f = value.c[0].v;
                            value.c[0].v = _.result(_.find(subContinents, {label: value.c[0].v}), 'code');
                        });

                        break;
                }

                this.$set('result', result);
                this.$add('dataTable', new google.visualization.DataTable(result.dataTable));
                this.$add('chart', new google.visualization.GeoChart(this.$$.chart));

                this.chart.draw(this.dataTable, this.options);
            }
        },

        computed: {

            total: function () {
                if (this.result && this.result.totalsForAllResults) {
                    return utils.parseLabel(this.result.totalsForAllResults[this.config.metrics], this.config);
                }
            }
        }

    };

</script>