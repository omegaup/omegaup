import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload();
  const problemsList = new Vue({
    el: '#problem-list',
    render: function(createElement) {
      return createElement('omegaup-problem-list', {
        props: {
          problems: payload.problems,
          loggedIn: payload.loggedIn,
          currentTags: payload.currentTags,
          pagerItems: payload.pagerItems,
          wizardTags: payload.tagData,
          language: payload.language,
          languages: payload.languages,
          keyword: payload.keyword,
          modes: payload.modes,
          columns: payload.columns,
          mode: payload.mode,
          column: payload.column,
          tags: payload.tags,
        },
        on: {
          'wizard-search': (queryParameters: {
            [key: string]: string;
          }): void => {
            window.location.search = UI.buildURLQuery(queryParameters);
          },
        },
      });
    },
    components: {
      'omegaup-problem-list': problem_List,
    },
  });
});
