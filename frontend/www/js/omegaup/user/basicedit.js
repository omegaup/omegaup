import Vue from 'vue';
import user_BasicEdit from '../components/user/BasicEdit.vue';
import {OmegaUp, API} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let basicEdit = new Vue({
    el: '#user-basic-edit',
    render: function(createElement) {
      return createElement('omegaup-user-basic-edit', {
        props: {
          username: this.username,
        },
        on: {
          update: function(username, password) {
            API.User.updateBasicInfo({
                                         username, password,
                                     })
                .then(function(response) { window.location = '/profile/'; })
                .fail(omegaup.UI.apiError);
          }
        },
      });
    },
    data: {
      username: null,
    },
    components: {
      'omegaup-user-basic-edit': user_BasicEdit,
    },
  });

  API.User.profile({})
      .then(function(data) { basicEdit.username = data.userinfo.username; })
      .fail(omegaup.UI.apiError);
});
