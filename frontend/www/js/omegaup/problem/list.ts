import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as UI from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload();
  const queryString = window.location.search;
  let sortOrder = 'desc';
  let columnName = 'problem_id';
  if (queryString) {
    const urlParams = new URLSearchParams(queryString);
    if (urlParams.get('sort_order')) {
      const sortOrderParam = urlParams.get('sort_order');
      if (sortOrderParam) {
        sortOrder = sortOrderParam;
      }
    }
    if (urlParams.get('order_by')) {
      const columnNameParam = urlParams.get('order_by');
      if (columnNameParam) {
        columnName = columnNameParam;
      }
    }
  }
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
          tags: payload.tags,
          sortOrder: sortOrder,
          columnName: columnName,
        },
        on: {
          'wizard-search': (queryParameters: {
            [key: string]: string;
          }): void => {
            window.location.search = UI.buildURLQuery(queryParameters);
          },
          'apply-filter': (
            columnName: string,
            sortOrder: omegaup.SortOrder,
          ): void => {
            const queryString = window.location.search;
            if (!queryString) {
              window.location.replace(
                `/problem/?query=&order_by=${columnName}&sort_order=${sortOrder}`,
              );
              return;
            }
            const urlParams = new URLSearchParams(queryString);
            if (!urlParams.get('sort_order')) {
              window.location.replace(`${queryString}&sort_order=${sortOrder}`);
              return;
            }
            if (!urlParams.get('order_by')) {
              window.location.replace(`${queryString}&order_by=${columnName}`);
              return;
            }
            urlParams.set('sort_order', sortOrder);
            urlParams.set('order_by', columnName);

            const newQueryString = urlParams.toString();
            window.location.replace(`/problem/?${newQueryString}`);
          },
        },
      });
    },
    components: {
      'omegaup-problem-list': problem_List,
    },
  });
});
