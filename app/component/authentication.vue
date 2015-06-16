<template>
    <h3>Global API Settings</h3>
    <p v-if="state == 'link'">Not configured!</p>

    <a v-on="click: openAuthWindow" v-if="state == 'link'">Authenticate</a>

    <a v-on="click: checkAuth" v-if="state == 'waiting'">Get Profiles</a>

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

        compiled: function () {
            if (this.globals.configured) {
                this.$set('state', 'configured');
            } else {
                this.$set('state', 'link');
            }
            this.$set('loading', false);
            this.$set('profile', '');

        },

        methods: {
            openAuthWindow: function () {
                var url = this.$url.route('analytics/auth');

                this.state = 'waiting';
                this.popup = window.open(url, '', 'width=800,height=500');
            },

            checkAuth: function () {
                this.popup.close();
                this.loading = true;

                var request = this.$http.get('analytics/profile');

                request.success(function (res) {
                    this.loading = false;
                    this.$set('profileOptions', this.getProfileOptions(res.items));
                    this.state = 'profiles';
                });

                request.error(function () {

                });
            },

            saveProfile: function () {
                var request = this.$http.post('analytics/profile', {profile: this.profile});

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
                var request = this.$http.delete('analytics/disconnect');

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