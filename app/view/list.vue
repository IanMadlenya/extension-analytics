<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <table class="uk-table">
        <thead>
        <tr>
            <th v-repeat="result.cols ">{{ label }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-repeat="result.rows | pagination page">
            <td v-repeat="c">{{ f || v }}</td>
        </tr>
        </tbody>
    </table>

    <ul class="uk-pagination" v-el="pageination"></ul>

</template>

<script>
    var _ = require('lodash');

    module.exports = {

        view: {
            id: 'list',
            label: 'List',
            description: function () {

            },
            defaults: {}
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
                params.maxResults = 30;
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
                }});

                this.$set('result', result.dataTable);
            }
        },

        filters: {
            pagination: function (data, page) {
                return _.chunk(data, this.itemsPerPage)[page] || [];
            }
        }
    };

</script>
