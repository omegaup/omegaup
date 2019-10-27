import LandingPage from '../components/landingpage/Homepage.vue';
import { OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let landingPage = new Vue({
    el: '#landing-page',
    render: function(createElement) {
      return createElement('omegaup-landing-page', {});
    },
    components: {
      'omegaup-landing-page': LandingPage,
    },
  });
});