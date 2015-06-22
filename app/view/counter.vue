<template>

    <p v-repeat="result.totalsForAllResults">{{ $key | transGaCol }}: {{ $value }}</p>

</template>

<script>

    module.exports = {

        view: {
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