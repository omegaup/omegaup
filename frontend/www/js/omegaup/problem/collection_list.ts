import Vue from 'vue';
import problem_CollectionList from '../components/problem/CollectionList.vue';
import { types } from '../api_types';
import { omegaup, OmegaUp } from '../omegaup';
import * as ui from '../ui';

type CollectionListURLParameters = {
  language: string;
  query: string;
  columnName: string;
  sortOrder: omegaup.SortOrder | string;
  difficulty: string;
  quality: string;
  selectedTags: string[];
  excludedTags: string[];
};

export function excludedTagsFromSearch(search: string): string[] {
  return new URLSearchParams(search).getAll('excluded_tag[]');
}

export function collectionListURL(
  level: string,
  parameters: CollectionListURLParameters,
): string {
  return `/problem/collection/${level}/?${ui.buildURLQuery({
    language: parameters.language,
    query: parameters.query,
    order_by: parameters.columnName,
    sort_order: parameters.sortOrder,
    difficulty: parameters.difficulty,
    quality: parameters.quality,
    tag: parameters.selectedTags,
    excluded_tag: parameters.excludedTags,
  })}`;
}

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.CollectionDetailsByLevelPayload();
  const queryString = window.location.search;
  let sortOrder = 'desc';
  let columnName = 'problem_id';
  let language = 'all';
  let difficulty = 'all';
  let quality = 'onlyQualityProblems';
  let query = '';
  let excludedTags: string[] = [];
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
    if (urlParams.get('quality')) {
      const queryParam = urlParams.get('quality');
      if (queryParam) {
        quality = queryParam;
      }
    }
    if (urlParams.get('query')) {
      const queryParam = urlParams.get('query');
      if (queryParam) {
        query = queryParam;
      }
    }
    excludedTags = excludedTagsFromSearch(queryString);
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
          excludedTags: excludedTags,
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
          quality: quality,
          hideProblemTagsPreference: payload.hideProblemTagsPreference,
        },
        on: {
          'apply-filter': (
            columnName: string,
            sortOrder: omegaup.SortOrder,
            difficulty: string,
            quality: string,
            tag: string[],
            excludedTag: string[],
          ): void => {
            window.location.replace(
              collectionListURL(payload.level, {
                language,
                query,
                columnName,
                sortOrder,
                difficulty,
                quality,
                selectedTags: tag,
                excludedTags: excludedTag,
              }),
            );
          },
        },
      });
    },
  });
});
