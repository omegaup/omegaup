import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import qualitynomination_ReviewerPopup from '../components/qualitynomination/ReviewerPopup.vue';
import Vue from 'vue';

OmegaUp.on('ready', function () {
  const nominationPayload = JSON.parse(
    document.getElementById('qualitynomination-reportproblem-payload')
      .innerText,
  );
  if (nominationPayload.reviewer && !nominationPayload.already_reviewed) {
    const qualityNominationForm = new Vue({
      el: '#qualitynomination-qualityreview',
      render: function (createElement) {
        return createElement('qualitynomination-reviewerpopup', {
          props: {
            allowUserAddTags: nominationPayload.allowUserAddTags,
            levelTags: nominationPayload.levelTags,
            problemLevel: nominationPayload.problemLevel,
            publicTags: nominationPayload.publicTags,
            selectedPublicTags: nominationPayload.selectedPublicTags,
            selectedPrivateTags: nominationPayload.selectedPrivateTags,
            problemAlias: nominationPayload.problem_alias,
            problemTitle: nominationPayload.problemTitle,
          },
          on: {
            submit: function (tag, qualitySeal) {
              const contents = {};
              if (tag) {
                contents.tag = tag;
              }
              contents.quality_seal = qualitySeal;
              api.QualityNomination.create({
                problem_alias: nominationPayload.problem_alias,
                nomination: 'quality_tag',
                contents: JSON.stringify(contents),
              }).catch(ui.apiError);
            },
          },
        });
      },
      components: {
        'qualitynomination-reviewerpopup': qualitynomination_ReviewerPopup,
      },
    });
  }
});
