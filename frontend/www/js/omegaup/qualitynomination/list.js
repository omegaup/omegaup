import Vue from 'vue';
import qualitynominations_List from '../components/qualitynomination/List.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  for (var idx in payload.nominations) {
    var nomination = payload.nominations[idx];
    OmegaUp.convertTimes(nomination);
  }
  var viewProgress = new Vue({
    el: '#nomination-list',
    render: function(createElement) {
      return createElement('omegaup-qualitynominations-list', {
        props: {
          nominations: payload.nominations,
          currentUser: payload.currentUser,
          myView: payload.myView,
        },
      });
    },
    components: {
      'omegaup-qualitynominations-list': qualitynominations_List,
    },
  });
});
