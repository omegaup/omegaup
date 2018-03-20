import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          valid: this.valid,
          link: this.link,
          email: this.email,
          request_password_change: this.request_password_change
        },
        on: {
          'search-username': function(username) {
            adminSupport.valid = false;
            adminSupport.request_password_change = false;
            adminSupport.link = '';
            adminSupport.email = '';
            omegaup.API.User.profile({username: username})
                .then(function(data) {
                  adminSupport.valid = true;
                  adminSupport.email = data.userinfo.email;
                  adminSupport.request_password_change =
                      !!data.userinfo.request_password_change;
                })
                .fail(omegaup.UI.apiError);
          },
          'generate-token': function(email) {
            omegaup.API.Reset.generateToken({
                               email: email,
                             })
                .then(function(data) {
                  omegaup.UI.success(
                      T.passwordResetTokenWasGeneratedSuccessfully);
                  adminSupport.link = data.link;
                })
                .fail(omegaup.UI.apiError);
          },
          'copy-token': function() {
            let aux = document.createElement('input');
            let input = document.getElementsByName('link');
            aux.setAttribute('value', input[0].value);
            document.body.appendChild(aux);
            aux.trigger('select');
            document.execCommand('copy');
            document.body.removeChild(aux);
            omegaup.UI.success(T.passwordResetLinkCopiedToClipboard);
          },
          'reset': function() {
            adminSupport.valid = false;
            adminSupport.request_password_change = false;
            adminSupport.link = '';
            adminSupport.email = '';
          }
        },
      });
    },
    data: {valid: false, request_password_change: false, link: '', email: ''},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
