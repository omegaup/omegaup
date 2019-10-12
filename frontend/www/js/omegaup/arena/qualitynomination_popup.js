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
          nominatedBeforeAC: this.nominatedBeforeAC,
          solved: this.solved,
          tried: this.tried,
          dismissed: this.dismissed,
          dismissedBeforeAC: this.dismissedBeforeAC,
          canNominateProblem: this.canNominateProblem,
        },
        on: {
          submit: function(ev) {
            let contents = {};
            if (!ev.solved && ev.tried) {
              contents['before_ac'] = true;
            }
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
          dismiss: function(ev) {
            let contents = {};
            if (!ev.solved && ev.tried) {
              contents['before_ac'] = true;
            }
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
      nominatedBeforeAC: qualityPayload.nominatedBeforeAC,
      solved: qualityPayload.solved,
      tried: qualityPayload.tried,
      dismissed: qualityPayload.dismissed,
      dismissedBeforeAC: qualityPayload.dismissedBeforeAC,
      canNominateProblem: qualityPayload.can_nominate_problem,
    },
    components: {
      'qualitynomination-popup': qualitynomination_Popup,
    }
  });
});
