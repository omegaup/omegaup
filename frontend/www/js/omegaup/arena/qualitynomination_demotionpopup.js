import { OmegaUp } from '../omegaup-legacy';
import * as api from '../api';
import * as ui from '../ui';
import qualitynomination_demotionPopup from '../components/qualitynomination/DemotionPopup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  let reportProblemPayload = JSON.parse(
    document.getElementById('qualitynomination-reportproblem-payload')
      .innerText,
  );
  let qualitynominationdemotionForm = new Vue({
    el: '#qualitynomination-demotionpopup',
    render: function (createElement) {
      return createElement('qualitynomination-demotionpopup', {
        props: {},
        on: {
          submit: function (ev) {
            api.QualityNomination.create({
              problem_alias: reportProblemPayload.problem_alias,
              nomination: 'demotion',
              contents: JSON.stringify({
                rationale: ev.rationale || 'N/A',
                reason: ev.selectedReason,
                original: ev.original,
              }),
            }).catch(ui.apiError);
          },
        },
      });
    },
    data: {},
    components: {
      'qualitynomination-demotionpopup': qualitynomination_demotionPopup,
    },
  });
});
