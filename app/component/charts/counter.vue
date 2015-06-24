<template>

    <p v-repeat="result.totalsForAllResults">{{ $key | trans }}: {{ $value }}</p>

</template>

<script>

    module.exports = {

        chart: {
            id: 'counter',
            label: 'Counter',
            description: function () {

            },
            defaults: {}
        },

        data: function () {
            return {
                result: {
                    totalsForAllResults: ''
                }
            };
        },

        methods: {
            render: function (result) {
                this.$set('result', result);
            }
        }
    };

</script>