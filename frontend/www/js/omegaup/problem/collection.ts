import Vue from 'vue';
import problem_collections from '../components/problem/Collection.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListCollectionPayload();
  const problemCollection = new Vue({
    el: '#main-container',
    render: function (createElement) {
      return createElement('omegaup-problem-collection', {
        props: {
          level_Tags: payload.levelTags,
          problemCount: payload.problemCount,
        },
      });
    },
    components: {
      'omegaup-problem-collection': problem_collections,
    },
  });
});
