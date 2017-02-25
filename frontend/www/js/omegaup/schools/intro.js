import API from '../api.js';
import UI from '../ui.js';
import Vue from 'vue';
import schools_Intro from '../components/schools/Intro.vue';
import {OmegaUp, T} from '../omegaup.js';

OmegaUp.on('ready', function() {
  var viewProgress = new Vue({
    el: '#intro div',
    render: function(createElement) {
      return createElement('omegaup-schools-intro', {
        props: {
          T: T,
        },
      });
    },
    components: {
      'omegaup-schools-intro': schools_Intro,
    },
  });
});
