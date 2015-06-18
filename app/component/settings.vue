<template>

    <div class="uk-modal" v-el="modal">
        <div class="uk-modal-dialog">
            <a class="uk-modal-close uk-close"></a>

            <div class="uk-text-center" v-if="loading">
                <i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i>
            </div>


            <div class="uk-form uk-form-horizontal">

                <h3 class="wk-form-heading">{{ 'Google API' | trans }}</h3>

                <div class="uk-form-row" v-if="!globals.connected">
                    <label class="uk-form-label">{{ 'Credentials' | trans }}</label>

                    <div class="uk-form-controls">
                        <label>
                            <input type="checkbox" v-model="ownCredentials">
                            Use own credentials
                        </label>

                        <p class="uk-text-muted">
                            {{'To connect with Twitter, click the button above. Follow the instructions and copy the
                            provided PIN.' | trans}}
                        </p>

                    </div>
                </div>

                <div class="uk-form-row" v-if="!globals.connected && ownCredentials">
                    <label class="uk-form-label" for="form-client-id">{{ 'Client ID' | trans }}</label>

                    <div class="uk-form-controls">
                        <input id="form-client-id" type="text" v-model="client_id">
                    </div>
                </div>

                <div class="uk-form-row" v-if="!globals.connected && ownCredentials">
                    <label class="uk-form-label" for="form-client-id">{{ 'Client secret' | trans }}</label>

                    <div class="uk-form-controls">
                        <input id="form-client-id" type="text" v-model="client_secret">
                    </div>
                </div>

                <div class="uk-form-row" v-if="!globals.connected">
                    <label class="uk-form-label" for="form-auth-code">{{ 'Authorization' | trans }}</label>

                    <div class="uk-form-controls">

                        <input id="form-auth-code" type="text" placeholder="{{ 'Auth code' | trans }}" v-model="code">

                        <a class="uk-button" v-on="click: openAuthWindow">{{ 'Request code' | trans }}</a>

                        <i class="uk-icon-medium uk-icon-spinner uk-icon-spin" v-if="loading"></i>

                        <p class="uk-text-muted">
                            <span class="uk-badge uk-badge-danger uk-text-bold">Not configured</span> {{ 'To connect
                            with Google, click the button above. Follow the instructions and copy the provided code.' |
                            trans }}
                        </p>
                    </div>
                </div>

                <div class="uk-form-row" v-if="globals.connected">
                    <label class="uk-form-label" for="form-auth-code">{{ 'Authorization' | trans }}</label>

                    <div class="uk-form-controls">

                        <a class="uk-button" v-on="click: disconnect">{{ 'Disconnect' | trans }}</a>

                        <p class="uk-text-muted">{{ 'Disconnecting from Google will affect all widgets.' | trans }}</p>

                    </div>
                </div>

                <h3 class="wk-form-heading">{{ 'Google Analytics' | trans }}</h3>

                <div class="uk-form-row">
                    <label class="uk-form-label" for="wk-twitter-pin">{{ 'Profile' | trans }}</label>

                    <div class="uk-form-controls">
                        <select options="profileOptions" v-model="profile"
                                v-attr="disabled: profileList.length == 0" v-attr="selected: globals.profile"></select>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    var _ = require('lodash');

    module.exports = Vue.extend({

        el: function () {
            return document.createElement('div');
        },

        props: ['globals', 'state'],

        data: function () {
            return {
                loading: false,
                code: '',
                profile: 0,
                profileList: [],
                globals: window.$analytics
            }
        },

        compiled: function () {

            this.$watch('code', Vue.util.debounce(this.checkCode, 300));
            this.$watch('profile', Vue.util.debounce(this.saveProfile, 300));

            if (this.globals.profile) {
                this.profile = this.globals.profile;
            }

            this.modal = UIkit.modal(this.$$.modal);
        },

        computed: {
            profileOptions: function () {
                var options = [];

                options.push({value: 0, text: 'Select profile...'});
                this.profileList.forEach(function (profile) {
                    options.push({value: profile.id, text: profile.webPropertyId + ' - ' + profile.websiteUrl});
                });

                return options;
            }
        },

        methods: {

            show: function () {
                if (this.globals.connected) {
                    this.loadProfiles();
                }

                this.modal.show();
            },

            openAuthWindow: function () {
                this.popup = window.open('analytics/auth', '', 'width=800,height=500');
            },

            checkCode: function () {
                this.popup.close();
                this.loading = true;

                var request = this.$http.post('admin/analytics/code', {code: this.code});

                request.success(function () {
                    this.loading = false;
                    this.globals.connected = true;
                    this.code = '';
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
                    this.$set('profileList', res.items);
                });
            },

            saveProfile: function () {
                var request = this.$http.post('admin/analytics/profile', {profile: this.profile});

                this.loading = true;

                request.success(function () {
                    this.loading = false;
                    this.globals.profile = this.profile;
                });

                request.error(function () {

                });
            },

            disconnect: function () {
                var request = this.$http.delete('admin/analytics/disconnect');

                //this.$parent.loading = true;

                request.success(function () {
                    this.globals.connected = false;
                    this.globals.profile = false;
                    this.profile = 0;
                });

                request.error(function () {

                });
            }
        }
    });
</script>