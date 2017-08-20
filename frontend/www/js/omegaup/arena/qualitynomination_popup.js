import {API, UI, OmegaUp, T} from '../omegaup.js';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let qualityPayload =
      JSON.parse(document.getElementById('quality-payload').innerText);
  let problemStatement =
      document.getElementsByClassName('statement')[0].innerText;
  let sourceNode = document.getElementsByClassName('source-data');
  let source = (sourceNode.length > 0) ? sourceNode[0].innerText : '';

  let qualityNominationForm = new Vue({
    el: '#qualitynomination-popup',
    render: function(createElement) {
      return createElement('qualitynomination-popup', {
        props: {
          solved: this.solved,
          nominated: this.nominated,
          originalSource: source
        },
        on: {
          submit: function(ev) {
            let contents = {
              'rationale': 'promotion',
            };

            if (typeof(ev.difficulty) !== 'undefined') {
              contents.difficulty = Number.parseInt(ev.difficulty, 10);
            }
            if (typeof(ev.quality) !== 'undefined') {
              contents.quality = Number.parseInt(ev.quality, 10);
            }
            if (ev.topics.length > 0) {
              contents.topics = ev.topics;
            }
            API.QualityNomination.create({
                                   problem_alias: qualityPayload.problem_alias,
                                   nomination: 'promotion',
                                   contents: JSON.stringify(contents),
                                 })
                .fail(UI.apiError);
          },
          dismiss: function() {
            let contents = {
              'rationale': 'dismiss',
            };
            API.QualityNomination.create({
                                   problem_alias: qualityPayload.problem_alias,
                                   nomination: 'dismissal',
                                   contents: JSON.stringify(contents),
                                 })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {nominated: qualityPayload.nominated, solved: qualityPayload.solved},
    components: {
      'qualitynomination-popup': qualitynomination_Popup,
    }
  });
});
