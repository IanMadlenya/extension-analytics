<template>

    <div class="uk-modal" v-el="modal">
        <div class="uk-modal-dialog">
            <a class="uk-modal-close uk-close"></a>

            <div class="uk-text-center" v-show="loading">
                <i class="uk-icon-medium uk-icon-spinner uk-icon-spin"></i>
            </div>


            <div class="uk-form uk-form-horizontal">

                <h3 class="wk-form-heading">{{ 'Google API' | trans }}</h3>

                <div class="uk-form-row" v-show="!globals.connected">
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

                <div class="uk-form-row" v-show="!globals.connected && ownCredentials">
                    <label class="uk-form-label" for="form-client-id">{{ 'Client ID' | trans }}</label>

                    <div class="uk-form-controls">
                        <input id="form-client-id" type="text" v-model="client_id">
                    </div>
                </div>

                <div class="uk-form-row" v-show="!globals.connected && ownCredentials">
                    <label class="uk-form-label" for="form-client-id">{{ 'Client secret' | trans }}</label>

                    <div class="uk-form-controls">
                        <input id="form-client-id" type="text" v-model="client_secret">
                    </div>
                </div>

                <div class="uk-form-row" v-show="!globals.connected">
                    <label class="uk-form-label" for="form-auth-code">{{ 'Authorization' | trans }}</label>

                    <div class="uk-form-controls">

                        <input id="form-auth-code" type="text" placeholder="{{ 'Auth code' | trans }}" v-model="code">

                        <a class="uk-button" v-on="click: openAuthWindow">{{ 'Request code' | trans }}</a>

                        <i class="uk-icon-medium uk-icon-spinner uk-icon-spin" v-show="loading"></i>

                        <p class="uk-text-muted">
                            <span class="uk-badge uk-badge-danger uk-text-bold">Not configured</span> {{ 'To connect
                            with Google, click the button above. Follow the instructions and copy the provided code.' |
                            trans }}
                        </p>
                    </div>
                </div>

                <div class="uk-form-row" v-show="globals.connected">
                    <label class="uk-form-label" for="form-auth-code">{{ 'Account' | trans }}</label>

                    <div class="uk-form-controls">
                        <p v-show="uid">{{ 'UID' | trans }}: </p>

                        <p v-show="name">{{ 'Name' | trans }}:</p>
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
                init: false,
                loading: false,
                code: '',
                uid: '',
                name: '',
                profile: 0,
                profileList: [],
                globals: window.$analytics
            }
        },

        compiled: function () {
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
                if (!this.init) {
                    this.$watch('code', Vue.util.debounce(this.checkCode, 300));
                    this.$watch('profile', Vue.util.debounce(this.saveProfile, 300));
                    this.$watch('globals.connected', this.loadProfiles);

                    if (this.globals.profile) {
                        this.profile = this.globals.profile;
                    }

                    if (this.globals.connected) {
                        this.loadProfiles();
                    }
                }

                this.modal.show();
            },

            openAuthWindow: function () {
                this.popup = window.open('analytics/auth', '', 'width=800,height=500');
            },

            checkCode: function (code) {
                if (!code) {
                    return;
                }

                this.popup.close();
                this.loading = true;

                var request = this.$http.post('admin/analytics/code', {code: code});

                request.success(function () {
                    this.loading = false;
                    this.globals.connected = true;
                    this.code = '';
                });

                request.error(function () {

                });
            },

            loadProfiles: function () {
                if (!this.globals.connected) {
                    return;
                }

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