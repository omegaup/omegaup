import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload();
  const queryString = window.location.search;
  let sortOrder = 'desc';
  let columnName = 'problem_id';
  let language = 'all';
  let query = '';
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
    if (urlParams.get('language')) {
      const languageParam = urlParams.get('language');
      if (languageParam) {
        language = languageParam;
      }
    }
    if (urlParams.get('query')) {
      const queryParam = urlParams.get('query');
      if (queryParam) {
        query = queryParam;
      }
    }
  }
  new Vue({
    el: '#main-container',
    render: function (createElement) {
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
            window.location.search = ui.buildURLQuery(queryParameters);
          },
          'apply-filter': (
            columnName: string,
            sortOrder: omegaup.SortOrder,
          ): void => {
            const queryParameters = {
              language,
              query,
              order_by: columnName,
              sort_order: sortOrder,
            };
            window.location.replace(
              `/problem?${ui.buildURLQuery(queryParameters)}`,
            );
          },
        },
      });
    },
    components: {
      'omegaup-problem-list': problem_List,
    },
  });
});
