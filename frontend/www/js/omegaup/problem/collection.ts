import Vue from 'vue';
import problem_Collection from '../components/problem/Collection.vue';
import { types } from '../api_types';
import { OmegaUp } from '../omegaup';

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
          otherCollections: payload.otherCollections,
        },
      });
    },
  });
});
