import Vue from 'vue';
import problem_Collection from '../components/problem/Collection.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListCollectionPayload();
  const problemCollection = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-collection', {
        props: {
          levelTags: payload.levelTags,
          problemCount: payload.problemCount,
        },
      });
    },
    components: {
      'omegaup-problem-collection': problem_Collection,
    },
  });
});
