import Vue from 'vue';
import problem_List from '../components/problem/List.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';
import * as api from '../api';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemListPayload();
  const queryString = window.location.search;
  const searchResultProblems: types.ListItem[] = [];
  let sortOrder: omegaup.SortOrder = omegaup.SortOrder.Descending;
  let columnName = 'problem_id';
  let language = 'all';
  let onlyQualitySeal = false;
  let query: null | string = null;
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
    if (urlParams.get('only_quality_seal')) {
      const onlyQualitySealParam = urlParams.get('only_quality_seal');
      if (onlyQualitySealParam) {
        onlyQualitySeal = onlyQualitySealParam === 'true';
      }
    }
    if (urlParams.get('tag[]')) {
      const tagParam = urlParams.getAll('tag[]');
      if (tagParam) {
        tag = tagParam;
      }
    }
    if (urlParams.get('query')) {
      const queryParam = urlParams.get('query');
      if (queryParam) {
        query = queryParam;
      }
    }

    if (query) {
      searchResultProblems.push({ key: query, value: query });
    }
  }
  new Vue({
    el: '#main-container',
    components: {
      'omegaup-problem-list': problem_List,
    },
    data: () => ({
      searchResultProblems: searchResultProblems,
      solvedProblemAliases: [] as string[],
      unsolvedProblemAliases: [] as string[],
    }),
    mounted: function () {
      if (payload.loggedIn) {
        Promise.all([
          api.User.problemsSolved({}),
          api.User.listUnsolvedProblems({}),
        ])
          .then(([solvedRes, unsolvedRes]) => {
            this.solvedProblemAliases = (solvedRes.problems || []).map(
              (p: { alias: string }) => p.alias,
            );
            this.unsolvedProblemAliases = (unsolvedRes.problems || []).map(
              (p: { alias: string }) => p.alias,
            );
          })
          .catch(ui.apiError);
      }
    },
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
          onlyQualitySeal: onlyQualitySeal,
          sortOrder: sortOrder,
          columnName: columnName,
          searchResultProblems: this.searchResultProblems,
          solvedProblemAliases: this.solvedProblemAliases,
          unsolvedProblemAliases: this.unsolvedProblemAliases,
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
              query: query ?? '',
              only_quality_seal: onlyQualitySeal,
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
                data.results.push({ key: query, value: query });
                this.searchResultProblems = data.results;
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});
