import T from './lang';

import {
  escape as escapeString,
  formatString,
  error,
  success,
} from './ui_transitional';
export * from './markdown';
export * from './time';
export * from './ui_transitional';

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
