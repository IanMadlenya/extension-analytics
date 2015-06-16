<template>

    <p v-repeat="result.totalsForAllResults">{{ $key | transMetric }}: {{ $value }}</p>

    <p>{{ config.startDate }}</p>

</template>

<script>
    var utils = require('../utils.js');

    module.exports = {

        view: {
            id: 'counter',
            label: 'Counter',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                result: {
                    totalsForAllResults: ''
                }
            };
        },

        methods: {
            render: function (result) {
                this.$set('result', result);
            }
        },

        filters: {
            transMetric: function (metric) {
                return utils.transCol(metric);
            }
        }
    };

</script>