<template>

    <div class="uk-panel-badge">
        <ul class="uk-subnav pk-subnav-icon">
            <li v-show="!$parent.editing[widget.id] && gaUrl">
                <a href="{{ gaUrl }}" target="_blank" class="pk-icon-reply pk-icon-hover uk-hidden"></a>
            </li>
            <li v-show="!$parent.editing[widget.id] && !loading && result.time">
                <a class="pk-icon-edit pk-icon-hover uk-hidden" v-el="refresh" v-on="click: invalidCache"></a>
            </li>
            <li v-show="$parent.editing[widget.id]">
                <a class="pk-icon-edit pk-icon-hover" title="{{ 'Settings' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: openSettings"></a>
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

        <h3 class="uk-panel-title">{{ 'Analytics Widget' | trans }}</h3>

        <div class="uk-form-row">
            <label class="uk-form-label" for="form-analytics-type">{{ 'Type' | trans }}</label>

            <div class="uk-form-controls">
                <select id="form-analytics-type" class="uk-width-1-1" v-model="widget.preset"
                        options="presetOptions"></select>
            </div>
        </div>

        <chart-options class="uk-form-row uk-display-block" config="{{@ widget.config }}"
                       preset="{{@ widget.preset }}"></chart-options>

    </form>

    <div v-if="configured">

        <div class="uk-text-center" v-if="loading"><i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i></div>

        <div v-show="!loading" v-el="view"></div>

    </div>

    <div v-if="!configured">Google Analytics <a href="#" v-on="click: openSettings">authentication</a> needed.</div>

</template>

<script>
    var _ = require('lodash');
    var utils = require('../utils.js');

    module.exports = {

        type: {
            id: 'analytics',
            label: 'Analytics',
            disableToolbar: true,
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

            UIkit.tooltip(this.$$.refresh, {
                delay: 500,
                src: function () {
                    var string = '';

                    string += vm.$trans('Refresh');
                    string += ' (';
                    string += vm.$relativeDate(vm.result.time * 1000);
                    string += ')';

                    return string;
                }
            });

            if (this.widget.preset == undefined) {
                this.widget.$set('preset', this.globals.presets[0].id);
            }

            this.$watch('globals.connected', function () {
                this.configChanged();
            });

            this.$watch('globals.profile', function () {
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

            configured: function () {
                return this.globals.connected && this.globals.profile != false;
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

                if (!this.configured || !View) {
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

                if (!this.configured || !View) {
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
