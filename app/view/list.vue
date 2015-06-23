<template>

    <h3 class="uk-panel-title">{{ config.metrics | trans }} this {{ config.startDate | trans }}</h3>

    <table class="uk-table">
        <caption></caption>
        <thead>
        <tr>
            <th v-repeat="result.cols">{{ label }}</th>
        </tr>
        </thead>

        <tbody>
        <tr v-repeat="result.rows">
            <td v-repeat="c">{{ f || v }}</td>
        </tr>
        </tbody>
    </table>

    <label>
        Max Results:
        <input type="number" v-model="$parent.widget.config.results">
    </label>

</template>

<script>

    module.exports = {

        view: {
            id: 'list',
            label: 'List',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
          return {
              result: {}
          };
        },

        created: function () {
            this.$on('request', function (params) {
                params.maxResults = this.config.results || 5;
                params.sort = '-' + params.metrics;
            });
        },

        methods: {
            render: function (result) {
                console.log(result.dataTable)
                this.$set('result', result.dataTable);
            }
        }
    };

</script>
