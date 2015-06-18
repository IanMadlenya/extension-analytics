<template>

        <div class="uk-form-row" v-show="presetOptions.dimensions.length > 1">
            <label class="uk-form-label" for="form-analytics-dimension">{{ 'Dimension' | trans }}</label>
            <div class="uk-form-controls">
                <select id="form-analytics-dimension" class="uk-width-1-1" v-model="config.dimensions" options="presetOptions.dimensions"></select>
            </div>
        </div>

        <div class="uk-form-row" v-show="presetOptions.metrics.length > 1">
            <label class="uk-form-label" for="form-analytics-metric">{{ 'Metric' | trans }}</label>
            <div class="uk-form-controls">
                <select id="form-analytics-metric" class="uk-width-1-1" v-model="config.metrics" options="presetOptions.metrics"></select>
            </div>
        </div>

        <div class="uk-margin-top uk-grid uk-grid-small uk-grid-width-1-2">
            <div>

                <div class="uk-form-row" v-show="presetOptions.views.length > 1">
                    <label class="uk-form-label" for="form-analytics-chart">{{ 'Chart' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-analytics-chart" class="uk-width-1-1" v-model="config.views" options="presetOptions.views"></select>
                    </div>
                </div>

            </div>
            <div>

                <div class="uk-form-row" v-show="presetOptions.startDate">
                    <label class="uk-form-label" for="form-analytics-period">{{ 'Period' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-analytics-period" class="uk-width-1-1" v-model="config.startDate">
                            <option value="7daysAgo">Week</option>
                            <option value="30daysAgo">Month</option>
                            <option value="365daysAgo">Year</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../utils.js');

    module.exports = {
        props: ['config', 'preset'],

        compiled: function () {
            if (this.config.dimensions == undefined ||
                this.config.metrics == undefined ||
                this.config.startDate == undefined ||
                this.config.views == undefined) {
                this.setDefaults();
            }

            this.$watch('preset', function () {
                this.setDefaults();
            })
        },

        computed: {

            presetOptions: function () {
                var currentPreset = this.$parent.currentPreset;
                var presetOptions = {};
                var vm = this;

                ['dimensions', 'metrics', 'views'].forEach(function (key) {
                    if (currentPreset && _.isArray(currentPreset[key]) && currentPreset[key].length > 0) {
                        presetOptions[key] = currentPreset[key].map(function (el) {
                            var text;

                            if (key == 'views') {
                                text = _.result(_.find(vm.$parent.getViews(), {id: el}), 'label');
                            } else {
                                text = utils.transCol(el);
                            }

                            return {
                                value: el,
                                text: text
                            }
                        });
                    } else {
                        presetOptions[key] = [];
                    }
                });

                presetOptions['startDate'] = !this.$parent.currentPreset.realtime;

                return presetOptions;
            }
        },

        methods: {
            setDefaults: function () {
                var vm = this;

                this.$set('config', {});

                ['dimensions', 'metrics', 'views'].forEach(function (key) {
                    if (_.isArray(vm.presetOptions[key]) && vm.presetOptions[key].length > 0) {
                        vm.config.$set(key, vm.presetOptions[key][0].value);
                    }
                });

                if (!this.$parent.currentPreset.realtime) {
                    this.config.$set('startDate', '7daysAgo');
                }
            }
        }
    };

</script>