<template>

    <div class="uk-panel-badge">
        <ul class="uk-subnav pk-subnav-icon">
            <li v-show="!$parent.editing[widget.id] && gaUrl">
                <a href="{{ gaUrl }}" target="_blank" class="pk-icon-share pk-icon-hover uk-hidden" title="{{ 'Go to Google Analytics' | trans }}" data-uk-tooltip="{delay: 500}"></a>
            </li>
            <li v-show="!$parent.editing[widget.id] && !loading && result.time">
                <a class="pk-icon-refresh pk-icon-hover uk-hidden" v-el="refresh" v-on="click: configChanged(true)"></a>
            </li>
            <li v-show="$parent.editing[widget.id]">
                <a class="pk-icon-settings pk-icon-hover" title="{{ 'Settings' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: openSettings"></a>
            </li>
            <li v-show="$parent.editing[widget.id]">
                <a class="pk-icon-delete pk-icon-hover" title="{{ 'Delete' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: $parent.remove()" v-confirm="'Delete widget?'"></a>
            </li>
            <li v-show="!$parent.editing[widget.id]">
                <a class="pk-icon-edit pk-icon-hover uk-hidden" title="{{ 'Edit' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: $parent.edit()"></a>
            </li>
            <li v-show="$parent.editing[widget.id]">
                <a class="pk-icon-check pk-icon-hover" title="{{ 'Confirm' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: $parent.edit()"></a>
            </li>
        </ul>
    </div>

    <form class="pk-panel-teaser uk-form uk-form-stacked" v-if="editing" v-on="submit: $event.preventDefault()">
        <div class="uk-form-row">
            <label class="uk-form-label" for="form-analytics-type">{{ 'Type' | trans }}</label>
            <div class="uk-form-controls">
                <select id="form-analytics-type" class="uk-width-1-1" v-model="widget.preset" options="presetOptions"></select>
            </div>
        </div>

        <chart-options class="uk-form-row uk-display-block" config="{{@ widget.config }}" preset="{{@ widget.preset }}"></chart-options>
    </form>

    <div v-show="!loading && configured" v-el="chart"></div>

    <div class="uk-text-center" v-if="loading && configured">
        <v-loader></v-loader>
    </div>

    <div v-if="!configured">Google Analytics <a href="#" v-on="click: openSettings">authentication</a> needed.</div>

</template>

<script>

    var _ = require('lodash');
    var UIkit = require('uikit');
    var utils = require('../utils.js');

    module.exports = {

        type: {
            id: 'analytics',
            label: 'Analytics',
            disableToolbar: true,
            description: function () {

            },
            defaults: {
                config: {}
            }
        },

        props: ['widget', 'editing'],

        data: function () {
            return {
                loading: false,
                result: {},
                globals: window.$analytics
            };
        },

        components: {
            'chart-options': require('./chart-options.vue'),

            // Charts:
            area: require('./charts/area.vue'),
            column: require('./charts/column.vue'),
            counter: require('./charts/counter.vue'),
            geo: require('./charts/geo.vue'),
            pie: require('./charts/pie.vue'),
            list: require('./charts/list.vue')
        },

        created: function () {
            if (window.$analytics.root) {
                this.$options.url = {
                    root: this.$url.options.root + '/' + window.$analytics.root
                };
            }

            if (this.widget.preset == undefined) {
                this.widget.$set('preset', this.globals.presets[0].id);
            }
        },

        compiled: function () {
            var vm = this;

            window.addEventListener('resize', Vue.util.debounce(function () {
                vm.$broadcast('resize');
            }, 10));

            UIkit.tooltip(this.$$.refresh, {
                delay: 500,
                src: function () {
                    return vm.$trans('Refresh (%time%)', {time: vm.$relativeDate(vm.result.time * 1000)});
                }
            });

            this.$watch('configured', this.configChanged, {immediate: true});
            this.$watch('widget.config', Vue.util.debounce(this.configChanged, 500), {deep: true});
        },

        computed: {

            configured: function () {
                return this.globals.connected && Vue.util.isObject(this.globals.profile) && Object.keys(this.globals.profile).length;
            },

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
            },

            gaUrl: function () {
                if (!this.currentPreset.uri || !this.globals.profile) {
                    return false;
                }

                return 'https://www.google.com/analytics/web/?pli=1#report/' +
                    this.currentPreset.uri +
                    '/a' + this.globals.profile.accountId +
                    'w' + this.globals.profile.propertyId +
                    'p' + this.globals.profile.profileId + '/';
            }
        },

        methods: {

            openSettings: function () {
                this.globals.settingsVM.show();
            },

            getChart: function (id) {
                var charts = _(this.$options.components.__proto__)
                    .filter(function (component) {
                        return _.has(component, 'options.chart');
                    })
                    .map(function (component) {
                        return _.merge(component.options.chart, {component: component.options.id});
                    })
                    .value();

                return _.find(charts, {id: id});
            },

            configChanged: function (invalidCache) {
                invalidCache = typeof invalidCache === 'boolean' ? invalidCache : undefined;

                if (this.refreshIntervall) {
                    clearInterval(this.refreshIntervall);
                    this.refreshIntervall = null;
                }

                if (this.currentPreset.realtime) {
                    this.newRealtime(invalidCache);
                } else {
                    this.refreshChart(invalidCache);
                }
            },

            refreshChart: function (invalidCache) {
                var params = _.clone({
                    metrics: this.widget.config.metrics,
                    dimensions: this.widget.config.dimensions,
                    startDate: this.widget.config.startDate,
                    invalidCache: Boolean(invalidCache)
                });

                if (!this.configured || !this.initChart(this.widget.config.charts)) {
                    return;
                }

                this.$set('loading', true);

                this.chart.$emit('request', params);

                var request = this.$http.post('analytics/api', params);
                request.success(function (result) {
                    utils.parseRows(result, params);
                    utils.transCols(result);

                    this.$set('loading', false);
                    this.$set('result', result);
                    this.chart.$add('result', result);

                    this.$nextTick(function () {
                        this.chart.$emit('render');
                    });
                });
            },

            newRealtime: function (invalidCache) {
                if (!this.configured || !this.initChart(this.widget.config.charts)) {
                    return;
                }

                this.$set('loading', true);

                this.refreshRealtime(invalidCache);
                this.refreshIntervall = setInterval(this.refreshRealtime, 1000 * 30);
            },

            refreshRealtime: function (invalidCache) {
                var params = _.clone({
                    metrics: this.widget.config.metrics,
                    dimensions: this.widget.config.dimensions,
                    invalidCache: Boolean(invalidCache)
                });

                this.chart.$emit('request', params);
                var request = this.$http.post('analytics/realtime', params);

                request.success(function (result) {
                    if (result.dataTable) {
                        utils.parseRows(result.dataTable, params);
                        utils.transCols(result.dataTable);
                    }

                    this.$set('loading', false);
                    this.$set('result', result);

                    if (this.chart.result) {
                        this.chart.$set('result', result);
                    } else {
                        this.chart.$add('result', result);
                    }

                    this.$nextTick(function () {
                        this.chart.$emit('render');
                    });
                });
            },

            initChart: function (chart) {
                var vm = this;
                var Chart = this.getChart(chart);

                if (Chart) {
                    if (this.chart) {
                        this.chart.$destroy(true);
                    }

                    this.chart = this.$addChild({
                        data: function () {
                            return {
                                config: _.clone(vm.widget.config)
                            }
                        }
                    }, this.$options.components[Chart.component]).$appendTo(this.$$.chart);

                    return true;
                } else {
                    return false;
                }
            }
        }
    }

</script>
