import Vue from 'vue';
import problem_Tags from '../components/problem/Tags.vue';
import { OmegaUp } from '../omegaup.js';
import { types } from '../api_types';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemTagsPayload(
    'problem-tags-payload',
  );
  const problemTags = new Vue({
    el: '#problem-tags',
    render: function(createElement) {
      return createElement('omegaup-problem-tags', {
        props: {
          initialTags: payload.tags,
          publicTags: payload.publicTags,
          levelTags: payload.levelTags,
          initialSelectedTags: payload.selectedTags,
          alias: payload.alias,
          title: payload.title,
          initialAllowTags: payload.allowTags,
          canAddNewTags: true,
        },
        on: {
          'update-problem-level': (levelTag?: string) => {
            const params = levelTag
              ? {
                  problemAlias: payload.alias,
                  levelTag,
                }
              : {
                  problemAlias: payload.alias,
                };
            api.Problem.updateProblemLevel(params)
              .then(response => {
                ui.success(T.problemLevelUpdated);
              })
              .catch(ui.apiError);
          },
          'add-tag': (alias: string, tagname: string, isPublic: boolean) => {
            api.Problem.addTag({
              problem_alias: alias,
              name: tagname,
              public: isPublic,
            })
              .then(response => {
                ui.success(T.tagAdded);
              })
              .catch(ui.apiError);
          },
          'remove-tag': (alias: string, tagname: string) => {
            api.Problem.removeTag({
              problem_alias: alias,
              name: tagname,
            })
              .then(response => {
                ui.success(T.tagRemoved);
              })
              .catch(ui.apiError);
          },
          'change-allow-user-add-tag': (
            alias: string,
            title: string,
            allowTags: boolean,
          ) => {
            api.Problem.update({
              problem_alias: alias,
              title: title,
              allow_user_add_tags: allowTags,
              message: `${T.problemEditFormAllowUserAddTags}: ${allowTags}`,
            })
              .then(response => {
                ui.success(T.problemEditUpdatedSuccessfully);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    components: {
      'omegaup-problem-tags': problem_Tags,
    },
  });
});
