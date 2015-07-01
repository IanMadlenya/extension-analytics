<template>

    <div v-if="result">
        <div class="uk-text-center" v-repeat="result.totalsForAllResults">
            <h3 class="pk-text-large">{{ $value }}</h3>
            <p>{{ $key | trans }}</p>
        </div>
    </div>
    <div v-if="false"><!-- Placeholder needed --></div>

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

        created: function () {
            this.formatter = utils.createMetricFormatter(this.config.metrics);
        }
    };

</script>