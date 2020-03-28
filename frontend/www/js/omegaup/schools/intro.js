import Vue from 'vue';
import schools_Intro from '../components/schools/Intro.vue';
import { OmegaUp } from '../omegaup';
import API from '../api.js';
import UI from '../ui.js';
import T from '../lang';

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
