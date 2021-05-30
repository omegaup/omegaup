import T from './lang';
import * as api from './api';
import { types } from './api_types';
import * as ui from './ui';

import '../../third_party/js/typeahead.jquery.js';

export type CallbackType<T> = (event: Event, val: T) => void;

declare global {
  interface Typeahead<T> {
    on: (eventName: string, callback: CallbackType<T>) => Typeahead<T>;
    trigger: (eventName: string) => Typeahead<T>;
  }

  interface JQuery {
    typeahead<T>(
      options: {
        minLength: number;
        highlight: boolean;
      },
      ...datasets: {
        source: (
          query: string,
          syncResults: (results: T[]) => void,
          asyncResults: (results: T[]) => void,
        ) => void;
        async: boolean;
        limit?: number;
        display: string;
        templates?: {
          empty?: string;
          suggestion: (val: T) => string;
        };
      }[]
    ): Typeahead<T>;
  }
}

function typeaheadWrapper<T>(
  searchFn: (options: {
    query: string;
    contest_alias?: string;
  }) => Promise<T[]>,
) {
  let lastRequest:
    | [string, (results: T[]) => void, (results: T[]) => void]
    | null = null;
  let pendingRequest = false;
  function wrappedCall(
    query: string,
    syncResults: (results: T[]) => void,
    asyncResults: (results: T[]) => void,
  ) {
    if (pendingRequest) {
      lastRequest = [query, syncResults, asyncResults];
      return;
    }
    pendingRequest = true;
    searchFn({ query: query })
      .then((data) => asyncResults(data))
      .catch(ui.ignoreError)
      .finally(() => {
        pendingRequest = false;

        // If there is a pending request, send it out now.
        if (!lastRequest) return;
        const currentRequest = lastRequest;
        lastRequest = null;
        wrappedCall(...currentRequest);
      });
  }
  return wrappedCall;
}

function typeahead<T extends { label: string; value: string }>(
  elem: JQuery<HTMLElement>,
  searchFn: (options?: { query?: string }) => Promise<T[]>,
  cb: CallbackType<T>,
) {
  elem
    .typeahead<T>(
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
          suggestion: (val) =>
            ui.formatString('<div data-value="%(value)">%(label)</div>', val),
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb)
    .trigger('change');
}

export function problemTypeahead(
  elem: JQuery<HTMLElement>,
  cb?: CallbackType<types.ProblemListItem>,
) {
  if (!cb) {
    cb = (event: Event, val: types.ProblemListItem) =>
      $(event.target as EventTarget).val(val.alias);
  }
  elem
    .typeahead<types.ProblemListItem>(
      {
        minLength: 3,
        highlight: false,
      },
      {
        source: typeaheadWrapper(
          (options: { query: string }) =>
            new Promise<types.ProblemListItem[]>((resolve, reject) =>
              api.Problem.list({ query: options.query })
                .then((data) => resolve(data.results))
                .catch(reject),
            ),
        ),
        async: true,
        limit: 10,
        display: 'alias',
        templates: {
          suggestion: (val) =>
            ui.formatString(
              '<div data-value="%(alias)"><strong>%(title)</strong> (%(alias))</div>',
              val,
            ),
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb)
    .trigger('change');
}

export function problemsetProblemTypeahead(
  elem: JQuery<HTMLElement>,
  problemDataset: () => { alias: string; title: string }[],
  cb?: CallbackType<{ alias: string; title: string }>,
) {
  const substringMatcher = (
    query: string,
    syncResults: (results: { alias: string; title: string }[]) => void,
  ) => {
    // regex used to determine if a string contains the query substring.
    const substringRegex = new RegExp(query, 'i');

    // Filter out the results that contain the query substring.
    syncResults(
      problemDataset().filter((problem) => substringRegex.test(problem.alias)),
    );
  };

  if (!cb) {
    cb = (event: Event, problem) =>
      $(event.target as EventTarget).val(problem.alias);
  }

  elem
    .typeahead<{ alias: string; title: string }>(
      {
        minLength: 3,
        highlight: false,
      },
      {
        source: substringMatcher,
        async: true,
        display: 'alias',
        templates: {
          suggestion: (val) =>
            ui.formatString('<div data-value="%(alias)">%(alias)</div>', val),
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb);
}

export function schoolTypeahead(
  elem: JQuery<HTMLElement>,
  cb?: CallbackType<{ id: number; label: string; value: string }>,
) {
  if (!cb) {
    cb = (event: Event, val: { id: number; label: string; value: string }) =>
      $(event.target as EventTarget).val(val.value);
  }
  elem
    .typeahead<{ id: number; label: string; value: string }>(
      {
        minLength: 2,
        highlight: true,
      },
      {
        source: typeaheadWrapper(api.School.list),
        async: true,
        limit: 10,
        display: 'label',
        templates: {
          empty: T.schoolToBeAdded,
          suggestion: (val) =>
            ui.formatString('<div data-value="%(value)">%(label)</div>', val),
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb);
}

export function tagTypeahead(
  elem: JQuery<HTMLElement>,
  cb?: CallbackType<{ name: string }>,
) {
  if (!cb) {
    cb = (event: Event, val: { name: string }) =>
      $(event.target as EventTarget).val(val.name);
  }
  elem
    .typeahead<{ name: string }>(
      {
        minLength: 2,
        highlight: true,
      },
      {
        source: typeaheadWrapper(api.Tag.list),
        async: true,
        limit: 10,
        display: 'name',
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb);
}

export function userContestTypeahead(
  elem: JQuery<HTMLElement>,
  contestAlias: string,
): void {
  const cb = (event: Event, val: { label: string; value: string }) =>
    $(event.target as EventTarget)
      .attr('data-value', val.value)
      .val(val.label);
  elem
    .typeahead<{ label: string; value: string }>(
      {
        minLength: 3,
        highlight: false,
      },
      {
        source: typeaheadWrapper(
          (options: { query: string; contest_alias?: string }) =>
            new Promise<{ label: string; value: string }[]>((resolve, reject) =>
              api.Contest.searchUsers({
                query: options.query,
                contest_alias: contestAlias,
              })
                .then((data) => resolve(data))
                .catch(reject),
            ),
        ),
        async: true,
        limit: 10,
        display: 'label',
        templates: {
          suggestion: (val) =>
            ui.formatString(
              '<div data-value="%(value)"><strong>%(label)</strong> (%(value))</div>',
              val,
            ),
        },
      },
    )
    .on('typeahead:select', cb)
    .on('typeahead:autocomplete', cb)
    .trigger('change');
}

export function userTypeahead(
  elem: JQuery<HTMLElement>,
  cb?: CallbackType<types.UserListItem>,
): void {
  if (!cb) {
    cb = (event: Event, val: types.UserListItem) =>
      $(event.target as EventTarget).val(val.label);
  }
  typeahead<types.UserListItem>(elem, api.User.list, cb);
}

export function groupTypeahead(
  elem: JQuery<HTMLElement>,
  cb?: CallbackType<{ label: string; value: string }>,
): void {
  if (!cb) {
    cb = (event: Event, val: { label: string; value: string }) =>
      $(event.target as EventTarget)
        .attr('data-value', val.value)
        .val(val.label);
  }
  typeahead<{ label: string; value: string }>(elem, api.Group.list, cb);
}
