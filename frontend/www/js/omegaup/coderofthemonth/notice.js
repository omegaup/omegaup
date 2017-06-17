import coder_of_the_month_Notice from '../components/coderofthemonth/Notice.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let coderPayload = JSON.parse(
      document.getElementById('coder-of-the-month-payload').innerText);
  let currentUserPayload =
      JSON.parse(document.getElementById('current-user-payload').innerText);

  let schoolsRank = new Vue({
    el: '#coder-of-the-month-notice',
    render: function(createElement) {
      return createElement('coder-of-the-month-notice', {
        props: {
          coderUsername: (coderPayload ? coderPayload.username : null),
          currentUsername:
              (currentUserPayload ? currentUserPayload.username : null)
        },
      });
    },
    components: {
      'coder-of-the-month-notice': coder_of_the_month_Notice,
    },
  });
});
