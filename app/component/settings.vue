<template>

    <div class="uk-modal" v-el="modal">
        <div class="uk-modal-dialog uk-form uk-form-horizontal">

            <div class="uk-modal-header">
                <h2>{{ 'Google API' | trans }}</h2>
            </div>

            <div class="uk-form-row" v-show="!globals.connected">
                <label for="form-auth-code" class="uk-form-label">{{ 'Authorization' | trans }}</label>

                <div class="uk-form-controls">
                    <input id="form-auth-code" class="uk-form-width-large" type="text" placeholder="{{ 'Auth code' | trans }}" v-model="code">
                    <p>
                        <a class="uk-button" v-on="click: openAuthWindow">{{ 'Request code' | trans }}</a>
                        <v-loader v-show="loading"></v-loader>
                    </p>
                </div>
            </div>

            <div class="uk-form-row" v-show="!globals.connected">
                <span class="uk-form-label">{{ 'Quota Limit' | trans }}</span>

                <div class="uk-form-controls uk-form-controls-text">
                    <label><input type="checkbox" v-model="ownCredentials"> Use own credentials</label>
                    <p class="uk-form-help-block">{{ 'The Google Analytics API is limited by 50,000 requests per day.
                        Use your own credentials to obtain your own full quota.' | trans}}</p>
                </div>
            </div>

            <div class="uk-form-row" v-show="!globals.connected && ownCredentials">
                <label for="form-client-id" class="uk-form-label">{{ 'Client ID' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-client-id" class="uk-form-width-large" type="text" v-model="client_id">
                </div>
            </div>

            <div class="uk-form-row" v-show="!globals.connected && ownCredentials">
                <label for="form-client-secret" class="uk-form-label">{{ 'Client secret' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-client-secret" class="uk-form-width-large" type="text" v-model="client_secret">
                </div>
            </div>

            <div class="uk-form-row" v-show="globals.connected">
                <label for="form-profile" class="uk-form-label">{{ 'Profile' | trans }}</label>
                <div class="uk-form-controls">
                    <select id="form-profile" class="uk-form-width-large" options="profileOptions" v-model="profileId" v-attr="disabled: profileList.length == 0" v-attr="selected: globals.profile"></select>
                </div>
            </div>

            <div class="uk-form-row" v-show="globals.connected && (name || id)">
                <span class="uk-form-label">{{ 'Account' | trans }}</span>

                <div class="uk-form-controls uk-form-controls-text" v-show="name && id">
                    <p class="uk-form-controls-condensed">{{ name }}</p>
                    <p class="uk-form-controls-condensed">{{ id }}</p>
                </div>
            </div>

            <div class="uk-form-row" v-show="globals.connected">
                <span for="form-auth-code" class="uk-form-label">{{ 'Authorization' | trans }}</span>

                <div class="uk-form-controls">
                    <a class="uk-button" v-on="click: disconnect">{{ 'Disconnect' | trans }}</a>

                    <p class="uk-form-help-block">{{ 'Disconnecting from Google will affect all Analytics widgets.' |
                        trans }}</p>
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
                id: '',
                name: '',
                profileId: 0,
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
                    if (this.globals.profile && this.globals.profile.profileId) {
                        this.profileId = this.globals.profile.profileId;
                    }

                    if (this.globals.connected) {
                        this.loadProfiles();
                        this.loadUser();
                    }

                    this.$watch('code', Vue.util.debounce(this.checkCode, 300));
                    this.$watch('profileId', Vue.util.debounce(this.saveProfile, 300));
                    this.$watch('globals.connected', function () {
                        this.loadProfiles();
                        this.loadUser();
                    });

                    this.init = true;
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

            loadUser: function () {
                if (!this.globals.connected) {
                    return;
                }

                var request = this.$http.get('admin/analytics/user');
                this.loading = true;

                request.success(function (res) {
                    this.loading = false;
                    this.$set('id', res.id);
                    this.$set('name', res.name);
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
                var profile = _.find(this.profileList, {id: this.profileId});

                if (profile) {
                    profile = {
                        accountId: profile.accountId,
                        propertyId: profile.internalWebPropertyId,
                        profileId: profile.id
                    };
                } else {
                    profile = {
                        accountId: 0
                    };
                }

                var request = this.$http.post('admin/analytics/profile', profile);
                this.loading = true;

                request.success(function (res) {
                    this.loading = false;
                    this.globals.profile = res.profile;
                });

                request.error(function () {

                });
            },

            disconnect: function () {
                var request = this.$http.delete('admin/analytics/disconnect');

                request.success(function () {
                    this.globals.connected = false;
                    this.globals.profile = false;
                    this.profile = {};
                    this.profileId = 0;
                    this.id = '';
                    this.name = '';
                });

                request.error(function () {

                });
            }
        }
    });
</script>