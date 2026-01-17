import formatDuration from 'date-fns/formatDuration';
import intervalToDuration from 'date-fns/intervalToDuration';
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import format from 'date-fns/format';
import esLocale from 'date-fns/locale/es';
import enLocale from 'date-fns/locale/en-US';
import ptLocale from 'date-fns/locale/pt-BR';
import T from './lang';

import * as Sugar from 'sugar';
import sugarEsLocale from './locales/es';
import sugarPtLocale from './locales/pt';

Sugar.Date.addLocale('es', sugarEsLocale);
Sugar.Date.addLocale('pt', sugarPtLocale);
Sugar.Date.addLocale('pseudo', Sugar.Date.getLocale('en'));
Sugar.extend({
  namespaces: [Date],
});

let remoteDeltaTime: number = 0;

export function formatFutureDateRelative(futureDate: Date): string {
  let currentLocale;
  switch (T.locale) {
    case 'pt':
      currentLocale = ptLocale;
      break;
    case 'en':
      currentLocale = enLocale;
      break;
    default:
      currentLocale = esLocale;
      break;
  }
  return formatDistanceToNow(futureDate, {
    addSuffix: true,
    locale: currentLocale,
  });
}

export function formatDelta(delta: number): string {
  const sign = delta < 0 ? '−' : '';
  if (delta < 0) {
    delta = -delta;
  }
  const months = delta / (30 * 24 * 60 * 60 * 1000);
  if (months >= 1.0) {
    return sign + formatFutureDateRelative(new Date(delta + Date.now()));
  }

  const days = Math.floor(delta / (24 * 60 * 60 * 1000));
  delta -= days * (24 * 60 * 60 * 1000);
  const hours = Math.floor(delta / (60 * 60 * 1000));
  delta -= hours * (60 * 60 * 1000);
  const minutes = Math.floor(delta / (60 * 1000));
  delta -= minutes * (60 * 1000);
  const seconds = Math.floor(delta / 1000);

  let clock = sign;
  if (days > 0) {
    clock += `${days}:`;
  }
  clock += `${String(hours).padStart(2, '0')}:${String(minutes).padStart(
    2,
    '0',
  )}:${String(seconds).padStart(2, '0')}`;

  return clock;
}

export function toDDHHMM(durationSeconds: number): string {
  const days = Math.floor(durationSeconds / 86400);
  const hours = Math.floor((durationSeconds - days * 86400) / 3600);
  const minutes = Math.floor(
    (durationSeconds - days * 86400 - hours * 3600) / 60,
  );

  let time = '';
  if (days > 0) time += `${days}d `;
  return `${time}${String(hours).padStart(2, '0')}h ${String(minutes).padStart(
    2,
    '0',
  )}m`;
}

export function formatDateLocal(date: Date): string {
  // The expected format is yyyy-MM-dd in the local timezone, which is
  // why we cannot use date.toISOSTring().
  return (
    String(date.getFullYear()).padStart(4, '0') +
    '-' +
    // Months in JavaScript start at 0.
    String(date.getMonth() + 1).padStart(2, '0') +
    '-' +
    String(date.getDate()).padStart(2, '0')
  );
}

export function parseDateLocal(dateString: string): Date {
  // The expected format is yyyy-MM-dd in the local timezone. Date.parse()
  // will use UTC if given a timestamp with that format, instead of the local
  // timezone.
  const result = new Date();
  const matches = /^(\d{4})-(\d{2})-(\d{2})$/.exec(dateString);
  if (matches === null) {
    result.setHours(0);
    result.setMinutes(0);
    result.setSeconds(0);
    result.setMilliseconds(0);
    return result;
  }
  return new Date(
    /*fullYear*/ Number.parseInt(matches[1], 10),
    /*month - In JavaScript starts at 0*/ Number.parseInt(matches[2], 10) - 1,
    /*day*/ Number.parseInt(matches[3], 10),
  );
}

export function formatDateTimeLocal(date: Date): string {
  // The expected format is yyyy-MM-ddTHH:MM in the local timezone, which
  // is why we cannot use date.toISOSTring().
  return (
    formatDateLocal(date) +
    'T' +
    String(date.getHours()).padStart(2, '0') +
    ':' +
    String(date.getMinutes()).padStart(2, '0')
  );
}

export function parseDateTimeLocal(dateString: string): Date {
  // The expected format is yyyy-MM-ddTHH:MM in the local timezone.
  // Date.parse() will use UTC if given a timestamp with that format, instead
  // of the local timezone.
  const result = new Date();
  const matches = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/.exec(dateString);
  if (matches === null) {
    result.setSeconds(0);
    result.setMilliseconds(0);
    return result;
  }

  return new Date(
    /*fullYear*/ Number.parseInt(matches[1], 10),
    /*month - In JavaScript starts at 0*/ Number.parseInt(matches[2], 10) - 1,
    /*day*/ Number.parseInt(matches[3], 10),
    /*hours*/ Number.parseInt(matches[4], 10),
    /*minutes*/ Number.parseInt(matches[5], 10),
  );
}

export function formatDateTime(date: Date): string {
  return date.toLocaleString(T.locale);
}

export function formatDate(date: Date): string {
  return date.toLocaleDateString(T.locale);
}

export function formatTimestamp(date: Date): string {
  return `${formatDateLocal(date)} ${String(date.getHours()).padStart(
    2,
    '0',
  )}:${String(date.getMinutes()).padStart(2, '0')}:${String(
    date.getSeconds(),
  ).padStart(2, '0')}`;
}

export function formatDateLocalHHMM(date: Date): string {
  return `${formatDateLocal(date)} ${String(date.getHours()).padStart(
    2,
    '0',
  )}:${String(date.getMinutes()).padStart(2, '0')}`;
}

export function parseDuration(str: string): number | null {
  let duration: number = 0;
  const durationRegexp = new RegExp(
    '(\\d+(?:\\.\\d+)?)(ns|us|µs|ms|s|m|h)?',
    'g',
  );
  const factor: { [suffix: string]: number } = {
    h: 3600000.0,
    m: 60000.0,
    s: 1000.0,
    ms: 1.0,
    us: 0.001,
    µs: 0.001,
    ns: 0.000001,
  };
  let lastIndex = 0;
  let match: RegExpExecArray | null = null;
  while ((match = durationRegexp.exec(str)) !== null) {
    if (match.index != lastIndex) {
      return null;
    }
    lastIndex += match[0].length;
    duration += parseFloat(match[1]) * factor[match[2] || 's'];
  }
  if (lastIndex != str.length) {
    return null;
  }
  return Math.round(duration);
}

declare global {
  interface DateConstructor {
    // This is defined by sugar.js.
    setLocale(locale: string): void;
  }
}

export function setSugarLocale() {
  Date.setLocale(T.locale);
}

/**
 * Sets the delta (in milliseconds) between the local and remote clock sources.
 *
 * @param delta - The delta (in milliseconds) between the local and remote
 * clock sources.
 */
export function _setRemoteDeltaTime(delta: number): void {
  remoteDeltaTime = delta;
}

/**
 * Converts a timestamp from the server clock source to the local clock source.
 *
 * @param timestamp - The timestamp (in milliseconds) with the server clock source.
 * @returns The same date, with the local clock source.
 */
export function remoteTime(timestamp: number): Date {
  return new Date(timestamp + remoteDeltaTime);
}

/**
 * Converts a date from the server clock source to the local clock source.
 *
 * @param date - The date with the server clock source.
 * @returns The same date, with the local clock source.
 */
export function remoteDate(date: Date): Date {
  return remoteTime(date.getTime());
}

/**
 * Recursively converts all Date objects to local time.
 *
 * This method traverses an Object hierarchy and converts Date objects that use
 * the server clock source into Date objects that use the local clock source.
 *
 * @param value - The value that will be converted.
 * @returns The same object with all its Date objects converted from remote to
 * local time.
 */
export function remoteTimeAdapter<T>(value: T): T {
  if (value instanceof Date) {
    return (remoteDate(value) as unknown) as T;
  }

  if (Array.isArray(value)) {
    for (let i = 0; i < value.length; ++i) {
      if (typeof value[i] !== 'object') {
        continue;
      }
      value[i] = remoteTimeAdapter(value[i]);
    }
  } else if (typeof value === 'object') {
    for (const p in value) {
      if (
        !Object.prototype.hasOwnProperty.call(value as any, p) ||
        typeof value[p] !== 'object'
      ) {
        continue;
      }
      value[p] = remoteTimeAdapter(value[p]);
    }
  }
  return value;
}

/**
 * Calculate the duration of a contest based on its start date and its end date.
 * @param startDate - The start date of a contest
 * @param finishDate - The finish date of a contest
 * @returns The duration of a contest in human readable format (Locale based)
 */
export function formatContestDuration(
  startDate: Date,
  finishDate: Date,
): string {
  let currentLocale;
  switch (T.locale) {
    case 'pt':
      currentLocale = ptLocale;
      break;
    case 'en':
      currentLocale = enLocale;
      break;
    default:
      currentLocale = esLocale;
      break;
  }
  const delta = finishDate.getTime() - startDate.getTime();
  const months = Math.floor(delta / (30 * 24 * 60 * 60 * 1000));
  if (months >= 1.0) {
    return formatDuration(
      intervalToDuration({
        start: startDate,
        end: finishDate,
      }),
      {
        locale: currentLocale,
      },
    );
  }
  return formatDelta(delta);
}

export function formatRelativeHoursMinutes(targetDate: Date): string {
  const diffMs = targetDate.getTime() - Date.now();
  const totalMinutes = Math.floor(Math.abs(diffMs) / (1000 * 60));
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;

  if (hours > 0 && minutes > 0) {
    return `${hours}h ${minutes}m`;
  }
  if (hours > 0) {
    return `${hours}h`;
  }
  return `${minutes}m`;
}

export function formatDateForContest(date: Date): string {
  let currentLocale;
  switch (T.locale) {
    case 'pt':
      currentLocale = ptLocale;
      break;
    case 'en':
      currentLocale = enLocale;
      break;
    default:
      currentLocale = esLocale;
      break;
  }
  return format(date, 'd MMM yyyy', { locale: currentLocale });
}

export function getContestDateForDisplay(targetDate: Date): string {
  const now = Date.now();
  const targetTime = targetDate.getTime();
  const diffMs = Math.abs(targetTime - now);
  const FORTY_EIGHT_HOURS = 48 * 60 * 60 * 1000;

  if (diffMs < FORTY_EIGHT_HOURS) {
    if (targetTime > now) {
      return formatRelativeHoursMinutes(targetDate);
    }
    return formatRelativeHoursMinutes(targetDate);
  }

  return formatDateForContest(targetDate);
}

/**
 * Converts a date to a GMT (UTC) date.
 *
 * @param date - The local date to be converted.
 * @returns The same date, but in GMT.
 */
export function convertLocalDateToGMTDate(date: Date): Date {
  return new Date(date.toUTCString().replace('GMT', ''));
}
