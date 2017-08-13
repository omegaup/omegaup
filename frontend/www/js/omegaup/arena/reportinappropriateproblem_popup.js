import {API, UI, OmegaUp, T} from '../omegaup.js';
import reportinappropriateproblem_Popup from '../components/reportinappropriateproblem/Popup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let reportProblemPayload =
      JSON.parse(document.getElementById('reportProblem-payload').innerText);
  let reportinappropriateproblemForm = new Vue({
    el: '#reportinappropriateproblem-popup',
    render: function(createElement) {
      return createElement('reportinappropriateproblem-popup', {
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
      'reportinappropriateproblem-popup': reportinappropriateproblem_Popup,
    }
  });
});
