import user_EmailEdit from '../components/user/EmailEdit.vue';
import Vue from 'vue';
import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let EmailEdit = new Vue({
    el: '#user-email-edit',
    render: function (createElement) {
      return createElement('userEmailEdit', {
        props: {
          initialEmail: this.email,
        },
        on: {
          submit: function (newEmail) {
            api.User.updateMainEmail({ email: newEmail })
              .then(function (response) {
                ui.success(T.userEditSuccessfulEmailUpdate);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      email: payload.email,
    },
    components: {
      userEmailEdit: user_EmailEdit,
    },
  });
});
