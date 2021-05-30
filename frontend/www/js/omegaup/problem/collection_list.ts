import Vue from 'vue';
import problem_CollectionList from '../components/problem/CollectionList.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CollectionDetailsByLevelPayload();
  const queryString = window.location.search;
  let sortOrder = 'desc';
  let columnName = 'problem_id';
  let language = 'all';
  let difficulty = 'all';
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
    if (urlParams.get('difficulty')) {
      const queryParam = urlParams.get('difficulty');
      if (queryParam) {
        difficulty = queryParam;
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
    components: {
      'omegaup-problem-collection-list': problem_CollectionList,
    },
    render: function (createElement) {
      return createElement('omegaup-problem-collection-list', {
        props: {
          data: payload,
          problems: payload.problems,
          loggedIn: payload.loggedIn,
          selectedTags: payload.selectedTags,
          pagerItems: payload.pagerItems,
          wizardTags: payload.tagData,
          language: payload.language,
          languages: payload.languages,
          keyword: payload.keyword,
          tagsList: payload.tagsList,
          publicTags: payload.publicTags,
          frequentTags: payload.frequentTags,
          sortOrder: sortOrder,
          columnName: columnName,
          difficulty: difficulty,
        },
        on: {
          'apply-filter': (
            columnName: string,
            sortOrder: omegaup.SortOrder,
            difficulty: string,
            tag: string[],
          ): void => {
            const queryParameters = {
              language,
              query,
              order_by: columnName,
              sort_order: sortOrder,
              difficulty,
              tag,
            };
            window.location.replace(
              `/problem/collection/${payload.level}/?${ui.buildURLQuery(
                queryParameters,
              )}`,
            );
          },
        },
      });
    },
  });
});
