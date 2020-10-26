import { OmegaUp } from '../omegaup';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
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
            'update-problem-level': (levelTag) => {
              api.Problem.updateProblemLevel({
                problem_alias: nominationPayload.problem_alias,
                level_tag: levelTag,
              })
                .then(() => {
                  ui.success(T.problemLevelUpdated);
                  this.problemLevel = levelTag;
                })
                .catch(ui.apiError);
            },
            'add-tag': (alias, tagname, isPublic) => {
              api.Problem.addTag({
                problem_alias: alias,
                name: tagname,
                public: isPublic,
              })
                .then(() => {
                  ui.success(T.tagAdded);
                  if (isPublic) {
                    nominationPayload.selectedPublicTags.push(tagname);
                  } else {
                    nominationPayload.selectedPrivateTags.push(tagname);
                  }
                })
                .catch(ui.apiError);
            },
            'remove-tag': (alias, tagname, isPublic) => {
              api.Problem.removeTag({
                problem_alias: alias,
                name: tagname,
              })
                .then(() => {
                  ui.success(T.tagRemoved);
                  if (isPublic) {
                    nominationPayload.selectedPublicTags = nominationPayload.selectedPublicTags.filter(
                      (tag) => tag !== tagname,
                    );
                  } else {
                    nominationPayload.selectedPrivateTags = nominationPayload.selectedPrivateTags.filter(
                      (tag) => tag !== tagname,
                    );
                  }
                })
                .catch(ui.apiError);
            },
            'change-allow-user-add-tag': (alias, title, allowTags) => {
              api.Problem.update({
                problem_alias: alias,
                title: title,
                allow_user_add_tags: allowTags,
                message: `${T.problemEditFormAllowUserAddTags}: ${allowTags}`,
              })
                .then(() => {
                  ui.success(T.problemEditUpdatedSuccessfully);
                })
                .catch(ui.apiError);
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
