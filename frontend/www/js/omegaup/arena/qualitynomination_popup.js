import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import qualitynomination_Popup from '../components/qualitynomination/Popup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  let qualityPayload = JSON.parse(
    document.getElementById('quality-payload').innerText,
  );

  let qualityNominationForm = new Vue({
    el: '#qualitynomination-popup',
    render: function (createElement) {
      return createElement('qualitynomination-popup', {
        props: {
          nominated: this.nominated,
          nominatedBeforeAc: this.nominatedBeforeAc,
          solved: this.solved,
          tried: this.tried,
          dismissed: this.dismissed,
          dismissedBeforeAc: this.dismissedBeforeAc,
          canNominateProblem: this.canNominateProblem,
          problemAlias: this.problemAlias,
        },
        on: {
          submit: function (ev) {
            let contents = {};
            if (!ev.solved && ev.tried) {
              contents.before_ac = true;
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
            api.QualityNomination.create({
              problem_alias: qualityPayload.problem_alias,
              nomination: 'suggestion',
              contents: JSON.stringify(contents),
            }).catch(ui.apiError);
          },
          dismiss: function (ev) {
            let contents = {};
            if (!ev.solved && ev.tried) {
              contents.before_ac = true;
            }
            api.QualityNomination.create({
              problem_alias: qualityPayload.problem_alias,
              nomination: 'dismissal',
              contents: JSON.stringify(contents),
            })
              .then(function (data) {
                ui.info(T.qualityNominationRateProblemDesc);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      nominated: qualityPayload.nominated,
      nominatedBeforeAc: qualityPayload.nominatedBeforeAc,
      solved: qualityPayload.solved,
      tried: qualityPayload.tried,
      dismissed: qualityPayload.dismissed,
      dismissedBeforeAc: qualityPayload.dismissedBeforeAc,
      canNominateProblem: qualityPayload.can_nominate_problem,
      problemAlias: qualityPayload.problem_alias,
    },
    components: {
      'qualitynomination-popup': qualitynomination_Popup,
    },
  });
});
