import { API, UI, OmegaUp, T } from '../omegaup.js';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  const nominationPayload = JSON.parse(
    document.getElementById('qualitynomination-reportproblem-payload')
      .innerText,
  );
  console.log(nominationPayload);
  if (nominationPayload.reviewer && !nominationPayload.already_reviewed) {
    const qualityNominationForm = new Vue({
      el: '#qualitynomination-qualityreview',
      render: function(createElement) {
        return createElement('qualitynomination-popup', {
          props: {
            linkTitle: T.reviewerNomination,
            reviewerSuggestion: true,
          },
          on: {
            submit: function(ev) {
              console.log(ev);
            },
            dismiss: function(ev) {
              console.log('babai');
              console.log(ev);
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
