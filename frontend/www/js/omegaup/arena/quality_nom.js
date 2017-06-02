import arena_QualityNomination from '../components/arena/QualityNomination.vue';
import {API, UI, OmegaUp, T} from '../omegaup.js';
import Vue from 'vue';

OmegaUp.on('ready', function() {
  let nominatedVal =
      !!parseInt(document.getElementById('nominated-payload').innerText);
  let solvedVal =
      !!parseInt(document.getElementById('solved-payload').innerText);
  let problemAlias = document.getElementById('problem-alias-payload').innerText;
  let problemStatement =
      document.getElementsByClassName('statement')[0].innerText;
  let sourceNode = document.getElementsByClassName('source-data');
  let source = (sourceNode.length > 0) ? sourceNode[0].innerText : '';

  let qualityNominationForm = new Vue({
    el: '#quality-nom-form',
    render: function(createElement) {
      return createElement('quality-nom-form', {
        props: {
          solved: this.solved,
          nominated: this.nominated,
          statement: problemStatement,
          source: source
        },
        on: {
          submit: function(ev) {
            API.QualityNomination.create({
                                   problem_alias: problemAlias,
                                   nomination: 'promotion',
                                   contents: JSON.stringify({
                                     'rationale': ev.rationale,
                                     'statement': ev.statement,
                                     'tags': [],
                                     'source': ev.source,
                                   })
                                 })
                .fail(UI.apiError);
          }
        }
      });
    },
    data: {nominated: nominatedVal, solved: solvedVal},
    components: {
      'quality-nom-form': arena_QualityNomination,
    }
  });
});
