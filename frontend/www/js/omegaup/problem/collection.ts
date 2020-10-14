import Vue from 'vue';
import problem_Collection from '../components/problem/Collection.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListCollectionPayload();
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-collection': problem_Collection,
    },
    render: function (createElement) {
      return createElement('omegaup-problem-collection', {
        props: {
          levelTags: payload.levelTags,
          problemCount: payload.problemCount,
          wizardTags: payload.tagData,
        },
        on: {
          'wizard-search': (queryParameters: {
            [key: string]: string;
          }): void => {
            window.location.search = ui.buildURLQuery(queryParameters);
          },
        },
      });
    },
  });
});
