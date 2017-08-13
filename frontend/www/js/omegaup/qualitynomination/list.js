import Vue from 'vue';
import qualitynomination_List from '../components/qualitynomination/List.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  for (let nomination of payload.nominations) {
    OmegaUp.convertTimes(nomination);
  }
  var viewProgress = new Vue({
    el: '#qualitynomination-list',
    render: function(createElement) {
      return createElement('omegaup-qualitynomination-list', {
        props: {
          nominations: payload.nominations,
          currentUser: payload.currentUser,
          myView: payload.myView,
        },
      });
    },
    components: {
      'omegaup-qualitynomination-list': qualitynomination_List,
    },
  });
});
