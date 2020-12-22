import admin_Support from '../components/admin/Support.vue';
import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function (createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          username: this.username,
          link: this.link,
          verified: this.verified,
          lastLogin: this.lastLogin,
        },
        on: {
          'search-email': function (email) {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.verified = false;
            api.User.extraInformation({ email: email })
              .then(function (data) {
                adminSupport.username = data.username;
                adminSupport.verified = data.verified;
                adminSupport.lastLogin =
                  data.last_login == null
                    ? null
                    : new Date(data.last_login * 1000);
              })
              .catch(ui.apiError);
          },
          'verify-user': function (email) {
            api.User.verifyEmail({ usernameOrEmail: email })
              .then(function () {
                adminSupport.verified = true;
                ui.success(T.userVerified);
              })
              .catch(ui.apiError);
          },
          'generate-token': function (email) {
            api.Reset.generateToken({
              email: email,
            })
              .then(function (data) {
                ui.success(T.passwordResetTokenWasGeneratedSuccessfully);
                adminSupport.link = data.link;
              })
              .catch(ui.apiError);
          },
          'copy-token': function () {
            ui.success(T.passwordResetLinkCopiedToClipboard);
          },
          reset: function () {
            adminSupport.username = null;
            adminSupport.link = null;
            adminSupport.verified = false;
            adminSupport.lastLogin = null;
          },
        },
      });
    },
    data: { username: null, link: null, verified: false, lastLogin: null },
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
