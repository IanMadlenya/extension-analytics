<template>
    <hr/>

    <div class="uk-grid">
        <div v-if="presetOptions.dimensions.length > 1">
            <label class="uk-form-label">Dimension</label>

            <div class="uk-form-controls">
                <select v-model="config.dimensions" options="presetOptions.dimensions"></select>
            </div>
        </div>

        <div v-if="presetOptions.metrics.length > 1">
            <label class="uk-form-label">Metric</label>

            <div class="uk-form-controls">
                <select v-model="config.metrics" options="presetOptions.metrics"></select>
            </div>
        </div>

        <div>
            <label class="uk-form-label">Period</label>

            <div class="uk-form-controls">
                <select v-model="config.startDate">
                    <option value="7daysAgo">Week</option>
                    <option value="30daysAgo">Month</option>
                    <option value="365daysAgo">Year</option>
                </select>
            </div>
        </div>

        <div v-if="presetOptions.views.length > 1">
            <label class="uk-form-label">{{ 'Chart' | trans }}</label>

            <div class="uk-form-controls">
                <select v-model="config.views" options="presetOptions.views"></select>
            </div>
        </div>
    </div>
</template>

<script>
    var _ = require('lodash');
    var utils = require('../utils.js');

    module.exports = {
        props: ['config', 'preset'],

        created: function () {
            console.log('Test')
        },

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
                var currentPreset = _.find(this.$parent.globals.presets, {id: this.preset});
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

                if (!this.config.startDate) {
                    this.config.$set('startDate', '7daysAgo');
                }
            }
        }
    };

</script>