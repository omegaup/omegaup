import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { OmegaUp } from '../omegaup.js';
import { types } from '../api_types';
import { omegaup } from '../omegaup.ts';
import T from '../lang';
import API from '../api.js';
import * as UI from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload('payload');
  const problemsList = new Vue({
    el: '#problem-list',
    render: function(createElement) {
      return createElement('omegaup-problem-list', {
        props: {
          problems: this.problems,
          loggedIn: this.loggedIn,
          currentTags: this.currentTags,
          pagerItems: this.pagerItems,
          wizardTags: this.tagData,
          language: this.language,
          languages: this.languages,
          keyword: this.keyword,
          modes: this.modes,
          columns: this.columns,
          mode: this.mode,
          column: this.column,
          tags: this.tags,
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
    data: {
      problems: payload.problems,
      loggedIn: payload.loggedIn,
      currentTags: payload.currentTags,
      pagerItems: payload.pagerItems,
      tagData: payload.tagData,
      language: payload.language,
      languages: payload.languages,
      keyword: payload.keyword,
      modes: payload.modes,
      columns: payload.columns,
      mode: payload.mode,
      column: payload.column,
      tags: payload.tags,
    },
    components: {
      'omegaup-problem-list': problem_List,
    },
  });
});
