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
          statement: problemStatement,
          source: source
        },
        on: {
          submit: function(ev) {
            let statements = {};
            statements[qualityPayload.language] = {
              markdown: ev.statement,
            };
            API.QualityNomination
                .create({
                  problem_alias: qualityPayload.problem_alias,
                  nomination: 'promotion',
                  contents: JSON.stringify({
                    'rationale': ev.rationale,
                    'statements': statements,
                    'tags': [
                    ], /* TODO https://github.com/omegaup/omegaup/issues/1289 */
                    'source': ev.source,
                  })
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
