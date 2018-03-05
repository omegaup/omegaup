import admin_Support from '../components/admin/Support.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  var adminSupport = new Vue({
    el: '#admin-support',
    render: function(createElement) {
      return createElement('omegaup-admin-support', {
        props: {
          valid: this.valid,
        },
        on: {
          'search-username': function(username) {
            adminSupport.valid = false;
            omegaup.API.User.profile({username: username})
                .then(function(data) { adminSupport.valid = true; })
                .fail(omegaup.UI.apiError);
          },
          'change-password': function(password, username) {
            omegaup.API.User.changePassword({
                              username: username,
                              password: password,
                            })
                .then(function() {
                  omegaup.UI.success(T.passwordResetResetSuccess);
                })
                .fail(omegaup.UI.apiError);
          },
          'reset': function() { adminSupport.valid = false; }
        },
      });
    },
    data: {
      valid: false,
    },
    components: {
      'omegaup-admin-support': admin_Support,
    },
  });
});
