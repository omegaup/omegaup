import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {valid: this.valid, link: this.link},
        on: {
          'search-username': function(username) {
            adminSupport.valid = false;
            omegaup.API.User.profile({username: username})
                .then(function(data) { adminSupport.valid = true; })
                .fail(omegaup.UI.apiError);
          },
          'generate-token': function(username) {
            omegaup.API.Reset.generateToken({
                               email: username,
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
            adminSupport.link = '';
          }
        },
      });
    },
    data: {valid: false, link: ''},
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
