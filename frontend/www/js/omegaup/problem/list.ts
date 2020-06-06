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
    el: '#main-container',
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
          initialMode: this.initialMode,
          initialOrderBy: this.initialOrderBy,
        },
        on: {
          'wizard-search': (queryParameters: {
            [key: string]: string;
          }): void => {
            window.location.search = UI.buildURLQuery(queryParameters);
          },
          'apply-filter': (orderBy: string, mode: omegaup.OrderMode): void => {
            const queryString = window.location.search;
            if (!queryString) {
              window.location.replace(
                `/problem/?query=&order_by=${orderBy}&mode=${mode}`,
              );
              return;
            }
            const urlParams = new URLSearchParams(queryString);
            if (!urlParams.get('mode')) {
              window.location.replace(`${queryString}&mode=${mode}`);
              return;
            }
            if (!urlParams.get('order_by')) {
              window.location.replace(`${queryString}&order_by=${orderBy}`);
              return;
            }
            urlParams.set('mode', mode);
            urlParams.set('order_by', orderBy);

            const newQueryString = urlParams.toString();
            window.location.replace(`/problem/?${newQueryString}`);
          },
        },
      });
    },
    data: {
      initialMode: 'desc',
      initialOrderBy: 'problem_id',
    },
    components: {
      'omegaup-problem-list': problem_List,
    },
  });

  const queryString = window.location.search;
  if (queryString) {
    const urlParams = new URLSearchParams(queryString);
    if (urlParams.get('mode')) {
      const mode = urlParams.get('mode');
      if (mode) {
        problemsList.initialMode = mode;
      }
    }
    if (urlParams.get('order_by')) {
      const orderBy = urlParams.get('order_by');
      if (orderBy) {
        problemsList.initialOrderBy = orderBy;
      }
    }
  }
});
