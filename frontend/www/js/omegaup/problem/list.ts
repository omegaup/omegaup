import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload();
  const queryString = window.location.search;
  const searchResultEmpty: types.ListItem[] = [];
  let sortOrder: omegaup.SortOrder = omegaup.SortOrder.Descending;
  let columnName = 'problem_id';
  let language = 'all';
  let query = '';
  let tag: string[] = [];
  if (queryString) {
    const urlParams = new URLSearchParams(queryString);
    if (urlParams.get('sort_order')) {
      const sortOrderParam = urlParams.get('sort_order');
      if (sortOrderParam) {
        sortOrder =
          sortOrderParam === 'desc'
            ? omegaup.SortOrder.Descending
            : omegaup.SortOrder.Ascending;
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
    if (urlParams.get('tag[]')) {
      const tagParam = urlParams.getAll('tag[]');
      if (tagParam) {
        tag = tagParam;
      }
    }
  }
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-list': problem_List,
    },
    data: () => ({
      searchResultProblems: searchResultEmpty,
    }),
    render: function (createElement) {
      return createElement('omegaup-problem-list', {
        props: {
          problems: payload.problems,
          loggedIn: payload.loggedIn,
          selectedTags: payload.selectedTags,
          pagerItems: payload.pagerItems,
          wizardTags: payload.tagData,
          language: payload.language,
          languages: payload.languages,
          keyword: payload.keyword,
          tags: payload.tags,
          sortOrder: sortOrder,
          columnName: columnName,
          searchResultProblems: this.searchResultProblems,
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
              tag,
            };
            window.location.replace(
              `/problem?${ui.buildURLQuery(queryParameters)}`,
            );
          },
          'update-search-result-problems': (query: string) => {
            api.Problem.listForTypeahead({
              query,
              search_type: 'all',
            })
              .then((data) => {
                this.searchResultProblems = data.results.map(
                  ({ key, value }) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
