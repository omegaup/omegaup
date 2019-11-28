import Vue from 'vue';
import { API, OmegaUp, T } from '../omegaup.js';
import school_Profile from '../components/schools/Profile.vue';

OmegaUp.on('ready', function() {
  const payload = JSON.parse(document.getElementById('payload').innerText);
  let schoolProfile = new Vue({
    el: '#school-profile',
    render: function(createElement) {
      return createElement('omegaup-school-profile', {
        props: {
          name: payload.name,
          country_name: payload.country_name,
          state_name: payload.state_name,
        },
      });
    },
    components: {
      'omegaup-school-profile': school_Profile,
    },
  });
});