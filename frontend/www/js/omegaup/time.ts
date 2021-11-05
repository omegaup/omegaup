import * as moment from 'moment';

import T from './lang';

let momentInitialized: boolean = false;
let remoteDeltaTime: number = 0;

export function formatFutureDateRelative(futureDate: Date): string {
  if (!momentInitialized) {
    moment.locale(T.locale);
    momentInitialized = true;
  }

  // moment is a weird library. The top-level import can be a function or an
  // object, and it depends on whether it was processed by webpack (in regular
  // compilation) or just babel (in tests).
  return ((moment as any)?.default ?? moment)(futureDate).endOf().fromNow();
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
  if (matches !== null) {
    result.setFullYear(Number.parseInt(matches[1], 10));
    // Months in JavaScript start at 0.
    result.setMonth(Number.parseInt(matches[2], 10) - 1);
    result.setDate(Number.parseInt(matches[3], 10));
  }
  result.setHours(0);
  result.setMinutes(0);
  result.setSeconds(0);
  result.setMilliseconds(0);
  return result;
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
  if (matches !== null) {
    result.setFullYear(Number.parseInt(matches[1], 10));
    // Months in JavaScript start at 0.
    // Specifying optional param 'date' so the method won't use the value from 'getDate()'.
    result.setMonth(
      Number.parseInt(matches[2], 10) - 1,
      Number.parseInt(matches[3], 10),
    );
    result.setHours(Number.parseInt(matches[4], 10));
    result.setMinutes(Number.parseInt(matches[5], 10));
  }
  result.setSeconds(0);
  result.setMilliseconds(0);
  return result;
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
 * @param date - The timestamp (in milliseconds) with the server clock source.
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
 * Converts a date to a GMT (UTC) date.
 *
 * @param date - The local date to be converted.
 * @returns The same date, but in GMT.
 */
export function convertLocalDateToGMTDate(date: Date): Date {
  return new Date(date.toUTCString().replace('GMT', ''));
}
