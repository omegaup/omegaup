import T from './lang';
import { formatDate, formatDateTime } from './time';
import { omegaup } from './omegaup';
import { types } from './api_types';
import notificationsStore, {
  MessageType,
  NotificationPosition,
} from './notificationsStore';

// Re-export MessageType and NotificationPosition for backward compatibility and convenience
export { MessageType, NotificationPosition };

export function navigateTo(href: string): void {
  const [pathname, hash] = href.split('#');
  if (pathname === window.location.pathname && hash != null) {
    window.location.hash = hash;
    return;
  }
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

export function contestURL(contest: types.ContestPublicDetails): string {
  if (isVirtual(contest)) {
    return `/arena/${contest.alias}/virtual/`;
  }
  return `/arena/${contest.alias}/`;
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

export function displayStatus({
  message,
  type,
  autoHide,
  position,
}: {
  message: string;
  type: MessageType;
  autoHide?: boolean;
  position?: NotificationPosition;
}): void {
  // Dispatch to Vuex store - the store action handles all visibility logic
  notificationsStore.dispatch('displayStatus', {
    message,
    type,
    autoHide,
    position,
  });
}

export function error(message: string): void {
  displayStatus({ message, type: MessageType.Danger });
}

export function info(message: string): void {
  displayStatus({ message, type: MessageType.Info });
}

export function success(message: string, autoHide: boolean = true): void {
  displayStatus({ message, type: MessageType.Success, autoHide });
}

export function warning(message: string): void {
  displayStatus({ message, type: MessageType.Warning });
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

export function dismissNotifications(): void {
  // Dispatch to Vuex store to hide notification
  notificationsStore.dispatch('dismissNotifications');
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
  if (navigator.clipboard && window.isSecureContext) {
    // Use the Clipboard API if available and in a secure context
    navigator.clipboard.writeText(value).catch((err) => {
      console.error('Failed to copy text in a secure context: ', err);
      fallbackCopyToclipboard(value);
    });
    return;
  }

  fallbackCopyToclipboard(value);
}

function fallbackCopyToclipboard(value: string): void {
  // Fallback to the deprecated method for older browsers
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
    gtag?: (command: string, ...fields: any[]) => void;
  }
}

export function reportEvent(
  category: string,
  action: string,
  label?: string,
): void {
  if (typeof window.ga === 'function') {
    window.ga('send', 'event', category, action, label);
  }
  if (typeof window.gtag === 'function') {
    window.gtag('event', action, {
      event_category: category,
      event_label: label,
    });
  }
}

export function reportPageView(page: string): void {
  if (typeof window.ga !== 'function') {
    return;
  }
  window.ga('send', 'pageview', page);
}

export enum NameDisplayOptions {
  None = 0,
  Name = 1,
  Username = 2,
  NameAndUsername = Name | Username,
}

export function rankingUsername(
  rank: omegaup.User & { virtual?: boolean },
  displayOptions: NameDisplayOptions = NameDisplayOptions.NameAndUsername,
): string {
  let username = '';
  if (
    (displayOptions & NameDisplayOptions.Username) ==
    NameDisplayOptions.Username
  ) {
    username = rank.username;
  }
  if (
    (displayOptions & NameDisplayOptions.Name) == NameDisplayOptions.Name &&
    !!rank.name &&
    rank.name != rank.username
  ) {
    username += ` (${escapeString(rank.name)})`;
  }
  if (username.length == 0) {
    // In case we can't use name or don't have it available, fall back to
    // username.
    username = rank.username;
  }
  if (rank.virtual)
    username = formatString(T.virtualSuffix, { username: username });
  return username;
}
