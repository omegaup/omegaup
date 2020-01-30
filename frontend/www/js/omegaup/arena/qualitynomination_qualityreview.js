import { API, UI, OmegaUp, T } from '../omegaup.js';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
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
        return createElement('qualitynomination-popup', {
          props: {
            linkTitle: T.reviewerNomination,
            reviewerNomination: true,
          },
          on: {
            submit: function(ev) {
              const contents = {};
              if (ev.tags.length) {
                contents.tag = ev.tags;
              }
              contents.quality_seal = ev.qualitySeal;
              API.QualityNomination.create({
                problem_alias: nominationPayload.problem_alias,
                nomination: 'quality_tag',
                contents: JSON.stringify(contents),
              }).fail(UI.apiError);
            },
          },
        });
      },
      data: {
        nominated: true,
      },
      components: {
        'qualitynomination-popup': qualitynomination_Popup,
      },
    });
  }
});
