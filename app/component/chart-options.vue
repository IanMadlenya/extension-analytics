<template>

    <div class="uk-form-row" v-if="presetOptions.dimensions.length > 1">
        <label class="uk-form-label" for="form-analytics-dimension">{{ 'Dimension' | trans }}</label>
        <div class="uk-form-controls">
            <select id="form-analytics-dimension" class="uk-width-1-1" v-model="config.dimensions" options="presetOptions.dimensions"></select>
        </div>
    </div>

    <div class="uk-form-row" v-if="presetOptions.metrics.length > 1">
        <label class="uk-form-label" for="form-analytics-metric">{{ 'Metric' | trans }}</label>
        <div class="uk-form-controls">
            <select id="form-analytics-metric" class="uk-width-1-1" v-model="config.metrics" options="presetOptions.metrics"></select>
        </div>
    </div>

    <div class="uk-margin-top uk-grid uk-grid-small uk-grid-width-1-2">
        <div>

            <div class="uk-form-row" v-if="presetOptions.charts.length > 1">
                <label class="uk-form-label" for="form-analytics-chart">{{ 'Chart' | trans }}</label>
                <div class="uk-form-controls">
                    <select id="form-analytics-chart" class="uk-width-1-1" v-model="config.charts" options="presetOptions.charts"></select>
                </div>
            </div>

        </div>
        <div>

            <div class="uk-form-row" v-if="presetOptions.startDate">
                <label class="uk-form-label" for="form-analytics-period">{{ 'Period' | trans }}</label>
                <div class="uk-form-controls">
                    <select id="form-analytics-period" class="uk-width-1-1" v-model="config.startDate">
                        <option value="7daysAgo">{{ '7daysAgo' | trans }}</option>
                        <option value="30daysAgo">{{ '30daysAgo' | trans }}</option>
                        <option value="365daysAgo">{{ '365daysAgo' | trans }}</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    <div class="uk-form-row uk-margin-top" v-show="customOptions">
        <div v-el="customOptions"></div>
    </div>

</template>

<script>
    var _ = require('lodash');

    module.exports = {

        props: ['config', 'preset'],

        data: function () {
            return {
                customOptions: false
            }
        },

        compiled: function () {

            if (this.config.metrics === undefined) {
                this.setDefaults();
            }

            this.$watch('preset', this.setDefaults);

            this.$watch('config.charts', function (chart) {
                    var Chart = this.$parent.getChart(chart);

                    if (this.customOptions) {
                        this.customOptions.$destroy(true);
                        this.$set('customOptions', false);
                    }

                    if (Chart.customOptions) {
                        this.$set(
                            'customOptions',
                            this.$addChild({}, Vue.extend(Chart.customOptions)).$appendTo(this.$$.customOptions)
                        );
                    }
                },
                {
                    immediate: true
                }
            );
        },

        computed: {

            presetOptions: function () {
                var currentPreset = this.currentPreset;
                var presetOptions = {};
                var vm = this;

                ['dimensions', 'metrics', 'charts'].forEach(function (key) {
                    if (_.isArray(currentPreset[key]) && currentPreset[key].length > 0) {
                        presetOptions[key] = currentPreset[key].map(function (el) {
                            var text;

                            if (key != 'charts') {
                                text = vm.$trans(el);
                            } else {
                                text = vm.$trans(vm.$parent.getChart(el).label);
                            }

                            return {
                                value: el,
                                text: text
                            }
                        });

                        presetOptions[key].sort(function (a, b) {
                            if (a.text < b.text) return -1;
                            if (a.text > b.text) return 1;
                            return 0;
                        });

                    } else {
                        presetOptions[key] = [];
                    }
                });

                presetOptions['startDate'] = !currentPreset.realtime;

                return presetOptions;
            },

            currentPreset: function () {
                return this.$parent.currentPreset;
            }
        },

        methods: {
            setDefaults: function () {
                var vm = this;

                this.$set('config', {});

                ['dimensions', 'metrics', 'charts'].forEach(function (key) {
                    if (_.isArray(vm.presetOptions[key]) && vm.presetOptions[key].length > 0) {
                        vm.config.$set(key, vm.presetOptions[key][0].value);
                    }
                });

                if (!this.currentPreset.realtime) {
                    this.config.$set('startDate', '7daysAgo');
                }
            }
        }
    };

</script>