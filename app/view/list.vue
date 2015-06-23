<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <table class="uk-table">
        <thead>
        <tr>
            <th v-repeat="result.cols">{{ label }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-repeat="result.rows">
            <td v-repeat="c">{{ f || v }}</td>
        </tr>
        </tbody>
    </table>

</template>

<script>

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
                result: {}
            };
        },

        created: function () {
            this.$on('request', function (params) {
                params.maxResults = 20;
                params.sort = '-' + params.metrics;
            });
        },

        methods: {
            render: function (result) {
                this.$set('result', result.dataTable);
            }
        }
    };

</script>
