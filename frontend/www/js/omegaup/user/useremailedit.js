import Vue from 'vue';
import {OmegaUp, T, API} from '../omegaup.js';

OmegaUp.on('ready', function() {
  const userEmail_edit = Vue.component(
      'userEmailEdit', require('../components/user/useremailedit.vue'));
  const user_profile = JSON.parse(document.getElementById('profile').innerText);
  let userEmailedit = new Vue({
    el: '#userEmailEdit',
    render: function(createElement) {
      return createElement('userEmailEdit', {
        props: {
          profile: this.profile,
        }
      });
    },
    data: {
      profile: user_profile,
    },
    components: {'userEmailEdit': userEmail_edit}
  });
});
