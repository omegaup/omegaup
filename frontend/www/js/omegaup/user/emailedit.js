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
          currentEmail: this.currentEmail,
        },
        on: {
          'submit': function(emailId) {
            omegaup.API.User.updateMainEmail({email: emailId})
                .then(function(response) {
                  $('#status')
                      .text(omegaup.T.userEditSuccessfulEmailUpdate)
                      .addClass('alert-success')
                      .slideDown();
                })
                .fail(omegaup.UI.apiError);

            // Prevent page refresh on submit
            return false;
          }
        },
      });
    },
    data: {
      currentEmail: payload.email,
    },
    components: {
      'userEmailEdit': user_EmailEdit,
    },
  });
});
