import T from './lang';
import API from './api.js';

import {
  escape as escapeString,
  formatString,
  error,
  success,
  ignoreError,
} from './ui_transitional';
export * from './ui_transitional';
export * from './time';
export * from './markdown';

export function rankingUsername(rank) {
  let username = rank.username;
  if (!!rank.name && rank.name != rank.username)
    username += ` (${escapeString(rank.name)})`;
  if (rank.virtual)
    username = formatString(T.virtualSuffix, { username: username });
  return username;
}

export function contestUpdated(data, contestAlias) {
  if (data.status != 'ok') {
    error(data.error || 'error');
    return;
  }
  success(
    T.contestEditContestEdited +
      ` <a href="/arena/${contestAlias}">${T.contestEditGoToContest}</a>`,
  );
}

export function typeaheadWrapper(searchFn) {
  let lastRequest = null;
  let pendingRequest = false;
  function wrappedCall(query, syncResults, asyncResults) {
    if (pendingRequest) {
      lastRequest = arguments;
      return;
    }
    pendingRequest = true;
    searchFn({ query: query })
      .then(data => asyncResults(data.results || data))
      .catch(ignoreError)
      .finally(() => {
        pendingRequest = false;

        // If there is a pending request, send it out now.
        if (!lastRequest) return;
        let currentRequest = lastRequest;
        lastRequest = null;
        wrappedCall(...currentRequest);
      });
  }
  return wrappedCall;
}

export function typeahead(elem, searchFn, cb) {
  if (!cb) {
    cb = (event, val) => $(event.target).val(val.value);
  }
  elem
    .typeahead(
      {
        minLength: 2,
        highlight: true,
      },
      {
        source: typeaheadWrapper(searchFn),
        async: true,
        limit: 100,
        display: 'label',
        templates: {
          suggestion: val => {
            return formatString(
              '<div data-value="%(value)">%(label)</div>',
              val,
            );
          },
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb)
    .trigger('change');
}

export function problemTypeahead(elem, cb) {
  if (!cb) {
    cb = (event, val) => $(event.target).val(val.alias);
  }
  elem
    .typeahead(
      {
        minLength: 3,
        highlight: false,
      },
      {
        source: typeaheadWrapper(API.Problem.list),
        async: true,
        limit: 50,
        display: 'alias',
        templates: {
          suggestion: val => {
            return formatString(
              '<div data-value="%(alias)"><strong>%(title)</strong> (%(alias))</div>',
              val,
            );
          },
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb)
    .trigger('change');
}

export function problemContestTypeahead(elem, problemList, cb) {
  const substringMatcher = (query, syncResults) => {
    // regex used to determine if a string contains the query substring.
    const substringRegex = new RegExp(query, 'i');

    // Filter out the results that contain the query substring.
    syncResults(
      problemList.filter(problem => substringRegex.test(problem.alias)),
    );
  };

  if (!cb) {
    cb = (event, problem) => $(event.target).val(problem.alias);
  }

  elem
    .typeahead(
      {
        minLength: 3,
        highlight: false,
      },
      {
        source: substringMatcher,
        async: true,
        display: 'alias',
        templates: {
          suggestion: val => {
            return formatString(
              '<div data-value="%(alias)">%(alias)</div>',
              val,
            );
          },
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb);
}

export function schoolTypeahead(elem, cb) {
  if (!cb) {
    cb = (event, val) => $(event.target).val(val.value);
  }
  elem
    .typeahead(
      {
        minLength: 2,
        highlight: true,
      },
      {
        source: typeaheadWrapper(API.School.list),
        async: true,
        limit: 10,
        display: 'label',
        templates: {
          empty: T.schoolToBeAdded,
          suggestion: val => {
            return formatString(
              '<div data-value="%(value)">%(label)</div>',
              val,
            );
          },
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb);
}

export function userTypeahead(elem, cb) {
  typeahead(elem, API.User.list, cb);
}

export function groupTypeahead(elem, cb) {
  typeahead(elem, API.Group.list, cb);
}
