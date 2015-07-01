<template>

    <div class="uk-text-center" v-repeat="result.totalsForAllResults">
        <h3 class="pk-text-large">{{ $value }}</h3>
        <p>{{ $key | trans }}</p>
    </div>

</template>

<script>
    var utils = require('../../utils.js');

    module.exports = {

        chart: {
            id: 'counter',
            label: 'Counter',
            description: function () {

            },
            defaults: {}
        },

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            return {
                result: {
                    totalsForAllResults: ''
                }
            };
        },

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);
        },

        methods: {
            render: function (result) {
                this.$set('result', result);
            }
        }
    };

</script>