import {API, UI, OmegaUp, T} from '../omegaup.js';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let qualityPayload =
      JSON.parse(document.getElementById('quality-payload').innerText);
  let problemStatement =
      document.getElementsByClassName('statement')[0].innerText;

  let qualityNominationForm = new Vue({
    el: '#qualitynomination-popup',
    render: function(createElement) {
      return createElement('qualitynomination-popup', {
        props: {
          nominated: this.nominated,
          solved: this.solved,
          dismissed: this.dismissed,
          canNominateProblem: this.canNominateProblem,
        },
        on: {
          submit: function(ev) {
            let contents = {};

            if (ev.difficulty !== '') {
              contents.difficulty = Number.parseInt(ev.difficulty, 10);
            }
            if (ev.tags.length > 0) {
              contents.tags = ev.tags;
            }
            if (ev.quality !== '') {
              contents.quality = Number.parseInt(ev.quality, 10);
            }
            API.QualityNomination.create({
                                   problem_alias: qualityPayload.problem_alias,
                                   nomination: 'suggestion',
                                   contents: JSON.stringify(contents),
                                 })
                .fail(UI.apiError);
          },
          dismiss: function() {
            let contents = {};
            API.QualityNomination.create({
                                   problem_alias: qualityPayload.problem_alias,
                                   nomination: 'dismissal',
                                   contents: JSON.stringify(contents),
                                 })
                .then(function(data) {
                  UI.info(T.qualityNominationRateProblemDesc);
                })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {
      nominated: qualityPayload.nominated,
      solved: qualityPayload.solved,
      dismissed: qualityPayload.dismissed,
      canNominateProblem: qualityPayload.can_nominate_problem,
    },
    components: {
      'qualitynomination-popup': qualitynomination_Popup,
    }
  });
});
