<template>

    <label class="uk-form-label" for="form-analytics-region">{{ 'Region' | trans }}</label>
    <div class="uk-form-controls">
        <select id="form-analytics-region" class="uk-width-1-1" v-model="config.region" options="regionOptions"></select>
    </div>

</template>

<script>

    var _ = require('lodash');

    module.exports = Vue.extend({

        inherit: true,

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            var options = [
                {
                    value: 0,
                    text: window.$analytics.geo.world
                },
                {
                    label: Vue.prototype.$trans('Continents'),
                    options: _.map(window.$analytics.geo.continents, function (continent, code) {
                        return {
                            value: code,
                            text: continent
                        }
                    })
                },
                {
                    label: Vue.prototype.$trans('Subcontinents'),
                    options: _.map(window.$analytics.geo.subcontinents, function (subcontinent, code) {
                        return {
                            value: code,
                            text: subcontinent
                        }
                    })
                },
                {
                    label: Vue.prototype.$trans('Countries'),
                    options: _.map(window.$analytics.geo.countries, function (country, code) {
                        return {
                            value: code,
                            text: country
                        }
                    })
                }
            ];

            return {
                regionOptions: options
            };
        },

        compiled: function () {
            if (!this.config.region) {
                // Add 'World' as default
                this.config.$add('region', 0);
            } else {
                this.config.$set('region', this.config.region);
            }
        },

        beforeDestroy: function () {
            if (this.config.charts !== 'geo') {
                this.config.$delete('region');
            }
        }

    });

</script>