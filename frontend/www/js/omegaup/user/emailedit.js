import user_EmailEdit from '../components/user/EmailEdit.vue';
import Vue from 'vue';
import {OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let EmailEdit = new Vue({
    el: '#user-email-edit',
    render: function(createElement) {
      return createElement('userEmailEdit', {
        props: {
          initialEmail: this.email,
        },
        on: {
          'submit': function(newEmail) {
            omegaup.API.User.updateMainEmail({email: newEmail})
                .then(function(response) {
                  $('#status')
                      .text(omegaup.T.userEditSuccessfulEmailUpdate)
                      .addClass('alert-success')
                      .slideDown();
                })
                .fail(UI.apiError);
          }
        },
      });
    },
    data: {
      email: payload.email,
    },
    components: {
      'userEmailEdit': user_EmailEdit,
    },
  });
});
