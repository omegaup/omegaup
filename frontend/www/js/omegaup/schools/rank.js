import schools_Rank from '../components/schools/Rank.vue';
import { API, UI, OmegaUp, T } from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(
    document.getElementById('schools-rank-payload').innerText,
  );
  let schoolsRank = new Vue({
    el: '#omegaup-schools-rank',
    render: function(createElement) {
      return createElement('omegaup-schools-rank', {
        props: payload,
      });
    },
    components: {
      'omegaup-schools-rank': schools_Rank,
    },
  });
});
