<template>
    <form class="uk-form uk-margin" v-show="editing" v-on="submit: $event.preventDefault()">

        <div class="uk-form-row">
            <div class="uk-form-controls">
                <select v-model="widget.preset" options="presetOptions"></select>
            </div>
        </div>

        <chart-options config="{{@ widget.config }}" preset="{{@ widget.preset }}"></chart-options>

        <authentication globals="{{@ globals }}" v-if="globals.configured"></authentication>

        <hr/>
    </form>

    <div class="uk-text-center" v-if="!globals.configured">


        <authentication globals="{{@ globals }}"></authentication>

    </div>

    <div class="uk-text-center" v-if="globals.configured">

        <br/><br/>

        <div class="uk-text-center" v-show="loading"><i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i></div>

        <div v-el="viewComponent" v-show="!loading"></div>

    </div>

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
            return  {
                widget: {config: {}},
                globals: window.$analytics};
        },

        components: {
            authentication: require('./authentication.vue'),
            'chart-options': require('./chartOptions.vue'),

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

            if (this.widget.preset == undefined) {
                this.widget.$set('preset', this.globals.presets[0].id)
            }

            var vm = this;
            window.addEventListener('resize', Vue.util.debounce(function () {
                vm.$broadcast('resize');
            }, 50));

            this.$watch('widget.config', Vue.util.debounce(this.updateChart, 500), {
                deep: true
            });

            this.$watch('globals.configured', this.updateChart);

            this.updateChart();
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
            }
        },

        methods: {

            getViews: function () {
                return _(this.$options.components.__proto__)
                    .filter(function (component) {
                        return _.has(component, 'options.view')
                    })
                    .map(function (component) {
                        return _.merge(component.options.view, {component: component.options.name})
                    })
                    .value();
            },

            updateChart: function () {
                var request;
                var child;
                var vm = this;
                var params = _.clone({
                        metrics: this.widget.config.metrics,
                        dimensions: this.widget.config.dimensions,
                        startDate: this.widget.config.startDate
                    }),
                    view = _.find(this.getViews(), {id: this.widget.config.views});

                if (!this.globals.configured) {
                    return;
                }

                if (!view) {
                    console.log('Chart not found');
                    return;
                }

                this.$set('loading', true);

                child = this.$addChild({
                    el: this.$$.viewComponent,
                    data: function () {
                        return {
                            config: _.clone(vm.widget.config)
                        }
                    }
                }, this.$options.components[view.component]);

                child.$emit('request', params);

                request = this.$http.post('admin/analytics/dashboard', params);

                request.success(function (result) {
                    utils.parseRows(result.dataTable, params);
                    utils.parseCols(result.dataTable);

                    this.$set('loading', false);

                    if (_.has(child, 'render')) {
                        Vue.nextTick(function () {
                            child.render(result);
                        });
                    }
                });

                this.$set('child', child);
            }
        }
    }

</script>
