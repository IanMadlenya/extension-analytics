<template>

    <h3 class="uk-panel-title">{{ total }} {{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <table class="uk-table">
        <thead>
        <tr>
            <th v-repeat="result.dataTable.cols ">{{ label }}</th>
        </tr>
        </thead>

        <tbody>
        <tr v-repeat="result.dataTable.rows | pagination page">
            <td v-repeat="c">{{ f || v }}</td>
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
