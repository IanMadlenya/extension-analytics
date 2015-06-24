<template>

    <label class="uk-form-label" for="form-analytics-region">{{ 'Region' | trans }}</label>
    <div class="uk-form-controls">
        <select id="form-analytics-region" class="uk-width-1-1" v-model="config.region" options="regionOptions"></select>
    </div>

</template>

<script>
    var _ = require('lodash');

    var continents = require('../../data/continents.json');
    var subcontinents = require('../../data/subContinents.json');

    module.exports = Vue.extend({

        inherit: true,

        el: function () {
            return document.createElement('div');
        },

        data: function () {
            var options = _([
                {
                    label: 'Continents',
                    data: continents
                },
                {
                    label: 'Subcontinents',
                    data: subcontinents
                }
            ]).map(function (group) {
                return {
                    label: group.label,
                    options: _.map(group.data, function (entry) {
                        return {
                            value: entry.code,
                            text: entry.label
                        };
                    })
                };
            }).value();
            return {
                regionOptions: options
            };
        },

        compiled: function () {
           console.log(this)
        }

    });

</script>