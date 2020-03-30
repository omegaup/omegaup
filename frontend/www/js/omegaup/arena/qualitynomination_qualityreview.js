import { OmegaUp } from '../omegaup';
import API from '../api.js';
import * as UI from '../ui';
import qualitynomination_ReviewerPopup from '../components/qualitynomination/ReviewerPopup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const nominationPayload = JSON.parse(
    document.getElementById('qualitynomination-reportproblem-payload')
      .innerText,
  );
  if (nominationPayload.reviewer && !nominationPayload.already_reviewed) {
    const qualityNominationForm = new Vue({
      el: '#qualitynomination-qualityreview',
      render: function(createElement) {
        return createElement('qualitynomination-reviewerpopup', {
          on: {
            submit: function(tag, qualitySeal) {
              const contents = {};
              if (tag) {
                contents.tag = tag;
              }
              contents.quality_seal = qualitySeal;
              API.QualityNomination.create({
                problem_alias: nominationPayload.problem_alias,
                nomination: 'quality_tag',
                contents: JSON.stringify(contents),
              }).catch(UI.apiError);
            },
          },
        });
      },
      components: {
        'qualitynomination-reviewerpopup': qualitynomination_ReviewerPopup,
      },
    });
  }
});
