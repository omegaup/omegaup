import T from './lang';

import * as moment from 'moment';

let momentInitialized: boolean = false;

export function formatDelta(delta: number): string {
  if (!momentInitialized) {
    moment.locale(T.locale);
    momentInitialized = true;
  }

  let months = delta / (30 * 24 * 60 * 60 * 1000);
  if (months >= 1.0) {
    return moment(delta + Date.now())
      .endOf()
      .fromNow();
  }

  let days = Math.floor(delta / (24 * 60 * 60 * 1000));
  delta -= days * (24 * 60 * 60 * 1000);
  let hours = Math.floor(delta / (60 * 60 * 1000));
  delta -= hours * (60 * 60 * 1000);
  let minutes = Math.floor(delta / (60 * 1000));
  delta -= minutes * (60 * 1000);
  let seconds = Math.floor(delta / 1000);

  let clock = '';

  if (days > 0) {
    clock += `${days}:`;
  }
  clock += `${String(hours).padStart(2, '0')}:`;
  clock += `${String(minutes).padStart(2, '0')}:`;
  clock += `${String(seconds).padStart(2, '0')}`;

  return clock;
}

export function toDDHHMM(durationSeconds: number): string {
  const days = Math.floor(durationSeconds / 86400);
  const hours = Math.floor((durationSeconds - days * 86400) / 3600);
  const minutes = Math.floor(
    (durationSeconds - days * 86400 - hours * 3600) / 60,
  );
  const seconds = durationSeconds - days * 86400 - hours * 3600 - minutes * 60;

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
  // will use UTC if given a timestamp with that format, instead of the local timezone.
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
    result.setMonth(Number.parseInt(matches[2], 10) - 1);
    result.setDate(Number.parseInt(matches[3], 10));
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
