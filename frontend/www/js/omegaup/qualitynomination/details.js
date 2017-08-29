import Vue from 'vue';
import qualitynomination_Details from '../components/qualitynomination/Details.vue';
import {OmegaUp} from '../omegaup.js';

OmegaUp.on('ready', function() {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let viewDetails = new Vue({
    el: '#qualitynomination-details',
    render: function(createElement) {
      return createElement('omegaup-qualitynomination-details', {
        props: {
          contents: payload.original_contents,
          nomination: payload.nomination,
          nominator: {
            username: payload.nominator.username,
            name: payload.nominator.name,
          },
          problem: {alias: payload.problem.alias, title: payload.problem.title},
          qualitynomination_id: parseInt(payload.qualitynomination_id),
          votes: payload.votes
        },
      });
    },
    components: {
      'omegaup-qualitynomination-details': qualitynomination_Details,
    },
  });
});
