import Vue from 'vue';
import problem_Tags from '../components/problem/Tags.vue';
import { OmegaUp } from '../omegaup.js';
import { types } from '../api_types';
import T from '../lang';
import API from '../api.js';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemTagsPayload(
    'problem-tags-payload',
  );
  const problemsList = new Vue({
    el: '#problem-tags',
    render: function(createElement) {
      return createElement('omegaup-problem-tags', {
        props: {
          initialTags: payload.tags,
          initialSelectedTags: payload.selectedTags,
          alias: payload.alias,
          canAddNewTags: true,
        },
        on: {
          'add-tag': (alias: string, tagname: string, isPublic: boolean) => {
            API.Problem.addTag({
              problem_alias: alias,
              name: tagname,
              public: isPublic,
            })
              .then((response: types.AddTagResponse) => {
                ui.success(T.tagAdded);
              })
              .catch(ui.apiError);
          },
          'remove-tag': (alias: string, tagname: string) => {
            API.Problem.removeTag({
              problem_alias: alias,
              name: tagname,
            })
              .then((response: types.RemoveTagResponse) => {
                ui.success(T.tagRemoved);
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
