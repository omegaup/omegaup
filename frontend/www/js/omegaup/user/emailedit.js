import Vue from 'vue';
import {OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  const user_EmailEdit = Vue.component(
      'userEmailEdit', require('../components/user/EmailEdit.vue'));
  const payload = JSON.parse(document.getElementById('payload').innerText);
  console.log(payload);
  let userEmailedit = new Vue({
    el: '#userEmailEdit',
    render: function(createElement) {
      return createElement('userEmailEdit', {
        props: {
          currentemail: this.currentemail,
        },
        on: {
          'submit': function(emailid){
            $('#wait').show();
            omegaup.API.User.updateMainEmail({email: emailid })
                .then(function(response) {
                  $('#status')
                      .text(omegaup.T.userEditSuccessfulEmailUpdate)
                      .addClass('alert-success')
                      .slideDown();
                })
                .fail(omegaup.UI.apiError)
                .always(function() { $('#wait')
                                         .hide(); });

            // Prevent page refresh on submit
            return false;
          }
        },
      });
    },
    data: {
      currentemail: payload.email,
    },
    components: {
      'userEmailEdit': user_EmailEdit,
    },
  });
});
