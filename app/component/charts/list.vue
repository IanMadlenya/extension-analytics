<template>

    <h3 class="uk-panel-title">{{ total | format 1 }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <table class="uk-table" v-if="result.dataTable">
        <thead>
        <tr>
            <th v-repeat="result.dataTable.cols ">{{ label }}</th>
        </tr>
        </thead>

        <tbody>
        <tr v-repeat="result.dataTable.rows | pagination page">
            <td v-repeat="c">{{ f || v | format $index }}</td>
        </tr>
        </tbody>
    </table>

    <ul class="uk-pagination" v-el="pageination"></ul>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../../utils.js');

    module.exports = {

        chart: {
            id: 'list',
            label: 'List',
            description: function () {

            },
            defaults: {}
        },

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            return {
                itemsPerPage: 5,
                page: 0,
                result: {}
            };
        },

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);

            this.$on('request', function (params) {
                params.maxResults = 15;
                params.sort = '-' + params.metrics;
            });
        },

        methods: {
            render: function (result) {
                var vm = this;

                this.pageination = UIkit.pagination(this.$$.pageination, {
                    pages: Math.floor(result.dataTable.rows.length / this.itemsPerPage),
                    onSelectPage: function (page) {
                        vm.page = page;
                    }
                });

                this.$set('result', result);
            }
        },

        filters: {
            pagination: function (data, page) {
                return _.chunk(data, this.itemsPerPage)[page] || [];
            },

            format: function (value, col) {

                if (col == 1 && this.formatter) {
                    return this.formatter.formatValue(value);
                }

                return value;
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
