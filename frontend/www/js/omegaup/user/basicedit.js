import Vue from 'vue';
import user_BasicEdit from '../components/user/BasicEdit.vue';
import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  let basicEdit = new Vue({
    el: '#user-basic-edit',
    render: function (createElement) {
      return createElement('omegaup-user-basic-edit', {
        props: {
          username: this.username,
        },
        on: {
          update: function (username, password) {
            api.User.updateBasicInfo({
              username,
              password,
            })
              .then(function (response) {
                window.location = '/profile/';
              })
              .catch(ui.apiError);
          },
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

  api.User.profile({})
    .then(function (data) {
      basicEdit.username = data.username;
    })
    .catch(ui.apiError);
});
