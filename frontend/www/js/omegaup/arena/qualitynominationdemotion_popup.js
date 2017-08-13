import {API, UI, OmegaUp, T} from '../omegaup.js';
import qualitynominationdemotion_Popup from '../components/qualitynomination/DemotionPopup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let reportProblemPayload =
      JSON.parse(document.getElementById('reportProblem-payload').innerText);
  let qualitynominationdemotionForm = new Vue({
    el: '#qualitynominationdemotion-popup',
    render: function(createElement) {
      return createElement('qualitynominationdemotion-popup', {
        props: {},
        on: {
          submit: function(ev) {
            var rationale = ev.rationale.length > 0 ? ev.rationale :
                                                      'No additional comments.';
            API.QualityNomination.create({
                                   problem_alias:
                                       reportProblemPayload.problem_alias,
                                   nomination: 'demotion',
                                   contents: JSON.stringify({
                                     'rationale': rationale,
                                     'reason': ev.selectedReason,
                                   })
                                 })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {},
    components: {
      'qualitynominationdemotion-popup': qualitynominationdemotion_Popup,
    }
  });
});
