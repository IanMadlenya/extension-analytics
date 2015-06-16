<template>

    <p v-if="state == 'link'">Not configured!</p>

    <a v-on="click: openAuthWindow" v-if="state == 'link'">Authenticate</a>


    <div v-if="state == 'waiting'">
        <label>
            {{ 'Authorization code' | trans }}:
            <input v-model="code" type="text">
        </label>
    </div>

    <div v-if="state == 'profiles'">
        <label class="uk-form-label">Profile</label>

        <div class="uk-form-controls">
            <select v-model="profile" options="profileOptions"></select>
        </div>
        <a v-on="click: saveProfile">Save</a>
    </div>

    <div v-if="state == 'configured'">
        <a v-on="click: disconnect">Disconnect</a>
    </div>

    <div class="uk-text-center" v-if="loading">
        <i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i>
    </div>


</template>

<script>
    var _ = require('lodash');

    module.exports = {

        props: ['globals', 'state'],

        data: function () {
            return {
                loading: false,
                code: '',
                profile: ''
            }
        },

        compiled: function () {
            if (this.globals.configured) {
                this.$set('state', 'configured');
            } else {
                this.$set('state', 'link');
            }

            this.$watch('code', Vue.util.debounce(this.checkCode, 500));
        },

        methods: {
            openAuthWindow: function () {
                var url = 'analytics/auth';

                this.state = 'waiting';
                this.popup = window.open(url, '', 'width=800,height=500');
            },

            checkCode: function () {
                this.popup.close();
                this.loading = true;

                var request = this.$http.post('admin/analytics/code', {code: this.code});

                request.success(function (res) {
                    this.loading = false;
                    this.loadProfiles();
                });

                request.error(function () {

                });
            },

            loadProfiles: function () {
                var request = this.$http.get('admin/analytics/profile');

                this.loading = true;

                request.success(function (res) {
                    this.loading = false;
                    this.$set('profileOptions', this.getProfileOptions(res.items));
                    this.state = 'profiles';
                });
            },

            saveProfile: function () {
                var request = this.$http.post('admin/analytics/profile', {profile: this.profile});

                this.loading = true;

                request.success(function () {
                    this.globals.configured = true;
                });

                request.error(function () {

                });
            },

            getProfileOptions: function (profiles) {
                var options = [];

                options.push({value: null, text: 'Select profile..'});
                profiles.forEach(function (profile) {
                    options.push({value: profile.id, text: profile.webPropertyId + ' - ' + profile.websiteUrl});
                });

                return options;
            },

            disconnect: function () {
                var request = this.$http.delete('admin/analytics/disconnect');

                this.$parent.loading = true;

                request.success(function () {
                    this.globals.configured = false;
                });

                request.error(function () {

                });
            }
        }
    };

</script>