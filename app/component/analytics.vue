<template>

    <form class="pk-panel-teaser uk-form uk-form-stacked" v-show="editing" v-on="submit: $event.preventDefault()">

        <h3 class="uk-panel-title">{{ 'Analytics Widget' | trans }}</h3>

        <div class="uk-form-row">
            <label class="uk-form-label" for="form-analytics-type">{{ 'Type' | trans }}</label>
            <div class="uk-form-controls">
                <select id="form-analytics-type" class="uk-width-1-1" v-model="widget.preset" options="presetOptions"></select>
            </div>
        </div>

        <chart-options class="uk-form-row uk-display-block" config="{{@ widget.config }}" preset="{{@ widget.preset }}"></chart-options>

        <a class="uk-icon-cogs" v-on="click: openSettings"></a>

    </form>

    <div v-if="globals.configured">

        <div class="uk-text-center" v-show="loading"><i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i></div>

        <div v-show="!loading">
            <div v-el="view"></div>
            <small v-if="result.time">{{'Report gernerated at' | trans}}: {{ result.time | toDateString }}
                <a class="uk-icon-refresh" v-on="click: invalidCache"></a>
            </small>
        </div>

    </div>

    <div v-if="!globals.configured">Google Analytics <a href="#" v-on="click: openSettings">authentication</a> needed.</div>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../utils.js');

    module.exports = {

        type: {
            id: 'analytics',
            label: 'Analytics',
            description: function () {

            },
            defaults: {
                config: {
                    dimensions: '',
                    metrics: '',
                    startDate: '',
                    views: ''
                }
            }
        },

        props: ['widget', 'editing'],

        data: function () {
            return {
                loading: false,
                result: {},
                widget: {config: {}},
                globals: window.$analytics
            };
        },

        components: {
            'chart-options': require('./chart-options.vue'),

            // Views:
            area: require('../view/area.vue'),
            bar: require('../view/bar.vue'),
            column: require('../view/column.vue'),
            counter: require('../view/counter.vue'),
            geo: require('../view/geo.vue'),
            pie: require('../view/pie.vue'),
            table: require('../view/table.vue')
        },

        compiled: function () {
            var vm = this;

            window.addEventListener('resize', Vue.util.debounce(function () {
                vm.$broadcast('resize');
            }, 50));

            if (this.widget.preset == undefined) {
                this.widget.$set('preset', this.globals.presets[0].id);
            }

            this.$watch('globals.configured', function () {
                this.configChanged();
            });
            this.$watch('widget.config', Vue.util.debounce(function () {
                this.configChanged();
            }, 500), {
                deep: true
            });

            this.configChanged();
        },

        computed: {
            presetOptions: function () {
                var groups = this.globals.groups;

                return _(this.globals.presets)
                    .groupBy('groupID')
                    .map(function (group, id) {
                        return {
                            label: _.find(groups, {id: id}).label,
                            options: _.map(group, function (preset) {
                                return {
                                    value: preset.id,
                                    text: preset.label
                                };
                            })
                        };
                    }).value();
            },

            currentPreset: function () {
                return _.find(this.globals.presets, {id: this.widget.preset});
            }
        },

        filters: {
            toDateString: function (timestamp) {
                return new Date(timestamp * 1000).toLocaleString();
            }
        },

        methods: {

            openSettings: function () {
                this.globals.settingsVM.show();
            },

            getViews: function () {
                return _(this.$options.components.__proto__)
                    .filter(function (component) {
                        return _.has(component, 'options.view');
                    })
                    .map(function (component) {
                        return _.merge(component.options.view, {component: component.options.name});
                    })
                    .value();
            },

            invalidCache: function () {
                this.configChanged(true);
            },

            configChanged: function (invalidCache) {
                if (this.refreshIntervall) {
                    clearInterval(this.refreshIntervall);
                    this.refreshIntervall = null;
                }

                if (this.currentPreset.realtime) {
                    this.newRealtime(invalidCache);
                } else {
                    this.refreshView(invalidCache);
                }
            },

            refreshView: function (invalidCache) {
                var View = _.find(this.getViews(), {id: this.widget.config.views});
                var params = _.clone({
                    metrics: this.widget.config.metrics,
                    dimensions: this.widget.config.dimensions,
                    startDate: this.widget.config.startDate,
                    invalidCache: invalidCache
                });

                if (!this.globals.configured || !View) {
                    return;
                }

                this.$set('loading', true);
                var view = this.initView(View);
                view.$emit('request', params);

                var request = this.$http.post('admin/analytics/api', params);
                request.success(function (result) {
                    utils.parseRows(result.dataTable, params);
                    utils.parseCols(result.dataTable);

                    this.$set('loading', false);
                    this.$set('result', result);

                    if (_.has(view, 'render')) {
                        this.$nextTick(function () {
                            view.render(result);
                        });
                    }
                });

                this.view = view;
            },

            newRealtime: function (invalidCache) {
                var View = _.find(this.getViews(), {id: this.widget.config.views});

                if (!this.globals.configured || !View) {
                    return;
                }

                this.$set('loading', true);

                this.view = this.initView(View);
                this.refreshRealtime(invalidCache);
                this.refreshIntervall = setInterval(this.refreshRealtime, 1000 * 30);
            },

            refreshRealtime: function (invalidCache) {
                var params = _.clone({
                    metrics: this.widget.config.metrics,
                    dimensions: this.widget.config.dimensions,
                    invalidCache: invalidCache
                });

                this.view.$emit('request', params);
                var request = this.$http.post('admin/analytics/realtime', params);

                request.success(function (result) {
                    if (result.dataTable) {
                        utils.parseRows(result.dataTable, params);
                        utils.parseCols(result.dataTable);
                    }

                    this.$set('loading', false);
                    this.$set('result', result);

                    if (_.has(this.view, 'render')) {
                        this.$nextTick(function () {
                            this.view.render(result);
                        });
                    }
                });
            },

            initView: function (View) {
                var vm = this;

                return this.$addChild({
                    el: this.$$.view,
                    data: function () {
                        return {
                            config: _.clone(vm.widget.config)
                        }
                    }
                }, this.$options.components[View.component]);
            }
        }
    }

</script>
