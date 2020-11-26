import Vue from 'vue';
import qualitynomination_Details from '../components/qualitynomination/Details.vue';
import { OmegaUp } from '../omegaup-legacy';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', function () {
  let payload = JSON.parse(document.getElementById('payload').innerText);
  let viewDetails = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-qualitynomination-details', {
        props: {
          contents: payload.contents,
          nomination: payload.nomination,
          nominator: {
            username: payload.nominator.username,
            name: payload.nominator.name,
          },
          author: {
            username: payload.author.username,
            name: payload.author.name,
          },
          problem: {
            alias: payload.problem.alias,
            title: payload.problem.title,
          },
          qualitynomination_id: parseInt(payload.qualitynomination_id),
          reviewer: payload.reviewer,
          votes: payload.votes,
          initialRationale: payload.contents.rationale,
        },
        on: {
          'mark-resolution': function (viewDetails, newStatus, all) {
            if (!viewDetails.rationale) {
              ui.error(T.editFieldRequired);
              return;
            }
            api.QualityNomination.resolve({
              problem_alias: viewDetails.problem.alias,
              status: newStatus,
              qualitynomination_id: viewDetails.qualitynomination_id,
              rationale: viewDetails.rationale,
              all: all,
            })
              .then(function (data) {
                ui.success(T.qualityNominationResolutionSuccess);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-qualitynomination-details': qualitynomination_Details,
    },
  });
});
