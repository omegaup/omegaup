import T from './lang';
import { formatDate, formatDateTime } from './time';
import { omegaup } from './omegaup';

export function navigateTo(href: string): void {
  window.location.href = href;
}

function escapeString(s: string): string {
  if (typeof s !== 'string') return '';
  return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

export { escapeString as escape };

export function buildURLQuery(queryParameters: { [key: string]: any }): string {
  return Object.entries(queryParameters)
    .map(([key, value]) => {
      const encodedKey = encodeURIComponent(key);
      if (Array.isArray(value)) {
        return value
          .map((entry) => `${encodedKey}[]=${encodeURIComponent(entry)}`)
          .join('&');
      }
      return `${encodedKey}=${encodeURIComponent(value)}`;
    })
    .join('&');
}

export function isVirtual(contest: {
  rerun_id?: number;
  title: string;
}): boolean {
  return !!contest.rerun_id && contest.rerun_id > 0;
}

export function contestTitle(contest: {
  rerun_id?: number;
  title: string;
}): string {
  if (isVirtual(contest)) {
    return formatString(T.virtualContestSuffix, {
      title: contest.title,
    });
  }
  return contest.title;
}

export function formatString(
  template: string,
  values: { [key: string]: any },
): string {
  const re = new RegExp('%\\(([^!)]+)(?:!([^)]+))?\\)', 'g');
  return template.replace(re, (match, key, modifier) => {
    if (!Object.prototype.hasOwnProperty.call(values, key)) {
      // If the array does not provide a replacement for the key, just return
      // the original substring.
      return match;
    }
    const replacement = values[key];
    if (modifier === 'date' && typeof replacement === 'number') {
      return formatDate(new Date(replacement * 1000));
    }
    if (modifier === 'timestamp' && typeof replacement === 'number') {
      return formatDateTime(new Date(replacement * 1000));
    }
    return String(replacement);
  });
}

export function displayStatus(message: string, type: string): void {
  if ($('#status .message').length == 0) {
    console.error('Showing warning but there is no status div');
  }

  // Just in case this needs to be displayed but the UI wasn't set up yet.
  $('#loading').hide();
  $('#root').show();

  $('#status .message').html(message);
  const statusElement = $('#status');
  let statusCounter = parseInt(statusElement.attr('data-counter') || '0');
  if (statusCounter % 2 == 1) {
    statusCounter++;
  }
  statusElement
    .removeClass('alert-success alert-info alert-warning alert-danger')
    .addClass(type)
    .addClass('animating')
    .attr('data-counter', statusCounter + 1)
    .slideDown({
      complete: () => {
        statusElement
          .removeClass('animating')
          .attr('data-counter', statusCounter + 2);
        if (type == 'alert-success') {
          setTimeout(() => {
            dismissNotifications(statusCounter + 2);
          }, 5000);
        }
      },
    });
}

export function error(message: string): void {
  displayStatus(message, 'alert-danger');
}

export function info(message: string): void {
  displayStatus(message, 'alert-info');
}

export function success(message: string): void {
  displayStatus(message, 'alert-success');
}

export function warning(message: string): void {
  displayStatus(message, 'alert-warning');
}

export function apiError(response: { error?: string; payload?: any }): void {
  console.error(response);
  error(
    response.error && response.payload
      ? formatString(response.error, response.payload)
      : (response.error || 'error').toString(),
  );
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
export function ignoreError(response: { error?: string; payload?: any }): void {
  return;
}

export function dismissNotifications(originalStatusCounter?: number): void {
  const statusElement = $('#status');
  let statusCounter = parseInt(statusElement.attr('data-counter') || '0');
  if (
    typeof originalStatusCounter == 'number' &&
    statusCounter > originalStatusCounter
  ) {
    // This status has already been dismissed.
    return;
  }
  if (statusCounter % 2 == 1) {
    statusCounter++;
  }
  statusElement
    .addClass('animating')
    .attr('data-counter', statusCounter + 1)
    .slideUp({
      complete: () => {
        statusElement
          .removeClass('animating')
          .attr('data-counter', statusCounter + 2);
      },
    });
}

type JSONType = any;

export function prettyPrintJSON(json: JSONType): string {
  return syntaxHighlight(JSON.stringify(json, undefined, 4) || '');
}

export function syntaxHighlight(json: JSONType): string {
  const jsonRE = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g;
  return json
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(jsonRE, (match: string) => {
      let cls = 'number';
      if (/^"/.test(match)) {
        if (/:$/.test(match)) {
          cls = 'key';
        } else {
          cls = 'string';
        }
      } else if (/true|false/.test(match)) {
        cls = 'boolean';
      } else if (/null/.test(match)) {
        cls = 'null';
      }
      return `<span class="${cls}">${match}</span>`;
    });
}

export function columnName(idx: number): string {
  let name = String.fromCharCode('A'.charCodeAt(0) + (idx % 26));
  while (idx >= 26) {
    idx = (idx / 26) | 0;
    idx--;
    name = String.fromCharCode('A'.charCodeAt(0) + (idx % 26)) + name;
  }
  return name;
}

export function getProfileLink(username: string): string {
  return `<a href="/profile/${username}" >${username}</a>`;
}

export function getFlag(country: string): string {
  if (!country) {
    return '';
  }
  return ` <img src="/media/flags/${country.toLowerCase()}.png" width="16" height="11" title="${country}" />`;
}

export function copyToClipboard(value: string): void {
  const tempInput = document.createElement('textarea');

  tempInput.style.position = 'absolute';
  tempInput.style.left = '-1000px';
  tempInput.style.top = '-1000px';
  tempInput.value = value;

  document.body.appendChild(tempInput);

  try {
    tempInput.select(); // refactor-lint-disable
    document.execCommand('copy');
  } finally {
    document.body.removeChild(tempInput);
  }
}

declare global {
  interface Window {
    ga?: (command: string, ...fields: any[]) => void;
  }
}

export function reportEvent(
  category: string,
  action?: string,
  label?: string,
): void {
  if (typeof window.ga !== 'function') {
    return;
  }
  window.ga('send', 'event', category, action, label);
}

export function rankingUsername(
  rank: omegaup.User & { virtual?: boolean },
): string {
  let username = rank.username;
  if (!!rank.name && rank.name != rank.username)
    username += ` (${escapeString(rank.name)})`;
  if (rank.virtual)
    username = formatString(T.virtualSuffix, { username: username });
  return username;
}
