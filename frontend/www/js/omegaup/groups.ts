import { types } from './api_types';
import T from './lang';
import * as ui from './ui';
import * as CSV from '@/third_party/js/csv.js/csv.js';

export const identityRequiredFields = new Set(['username']);

export const identityOptionalFields = new Set([
  'name',
  'country_id',
  'state_id',
  'gender',
  'school_name',
  'usernames',
]);

export function downloadCsvFile({
  fileName,
  columns,
  records,
}: {
  fileName: string;
  columns: string[];
  records: types.Identity[];
}): void {
  const dialect = {
    dialect: {
      csvddfVersion: 1.2,
      delimiter: ',',
      doubleQuote: true,
      lineTerminator: '\r\n',
      quoteChar: '"',
      skipInitialSpace: true,
      header: true,
      commentChar: '#',
    },
  };
  const fields = columns.map((column) => ({ id: column }));
  const csv = CSV.serialize({ fields, records }, dialect);
  const hiddenElement = document.createElement('a');
  hiddenElement.href = `data:text/csv;charset=utf-8,${window.encodeURIComponent(
    csv,
  )}`;
  hiddenElement.target = '_blank';
  hiddenElement.download = fileName;
  hiddenElement.click();
}

// There's no other way to specify an arbitrary type that can be read from a CSV
// as a string -> string mapping.
// eslint-disable-next-line @typescript-eslint/ban-types
export function getCSVRecords<T extends object>({
  fields,
  records,
  requiredFields,
  optionalFields,
}: {
  fields: string[];
  records: (null | number | string)[][];
  requiredFields: Set<string>;
  optionalFields?: Set<string>;
}): Array<T> {
  // Ensure that all required fields are present.
  const fieldNames = new Set(fields);
  const missingFields: string[] = [];
  for (const field of requiredFields) {
    if (!fieldNames.has(field)) {
      missingFields.push(field);
    }
  }
  if (missingFields.length) {
    throw new Error(
      ui.formatString(T.teamsGroupsErrorFieldIsNotPresentInCsv, {
        missingFields: missingFields.join(','),
      }),
    );
  }

  return records.map(
    (record): T => {
      // This is not type-safe, but TypeScript has no way of converting a type
      // into a runtime object. This means that we can't validate that the fields
      // match the keys of `T`.
      const row: Record<string, string> = {};
      for (const [i, field] of fields.entries()) {
        if (record[i] === null) {
          if (requiredFields.has(field)) {
            throw new Error(
              ui.formatString(T.teamsGroupsErrorFieldIsRequired, { field }),
            );
          }
          continue;
        }
        if (!requiredFields.has(field) && !optionalFields?.has(field)) {
          continue;
        }
        row[field] = String(record[i]);
      }
      return row as T;
    },
  );
}

export function generatePassword(): string {
  const lowercase = 'acdefhjkmnpqruvwxy';
  const uppercase = 'ACDEFHJKLMNPQRUVWXY';
  const digits = '3469';
  const special = '!@#$%&*';
  const allChars = lowercase + uppercase + digits + special;
  const len = 8;

  const pickRandom = (chars: string): string =>
    chars.charAt(Math.floor(Math.random() * chars.length));

  // Ensure at least one character from each required category
  const guaranteed = [
    pickRandom(lowercase),
    pickRandom(uppercase),
    pickRandom(digits),
    pickRandom(special),
  ];

  // Fill the remaining characters
  const remaining: string[] = [];
  for (let i = 0; i < len - guaranteed.length; i++) {
    remaining.push(pickRandom(allChars));
  }

  // Shuffle all characters together
  const combined = [...guaranteed, ...remaining];
  for (let i = combined.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [combined[i], combined[j]] = [combined[j], combined[i]];
  }

  return combined.join('');
}

export function generateHumanReadablePassword() {
  const words = {
    es: [
      'Loro',
      'Perro',
      'Pollo',
      'Lagarto',
      'Gato',
      'Toro',
      'Vaca',
      'Sapo',
      'Oso',
      'Zorro',
    ],
    en: [
      'Parrot',
      'Dog',
      'Chicken',
      'Lizard',
      'Cat',
      'Bull',
      'Cow',
      'Frog',
      'Bear',
      'Fox',
    ],
    pt: [
      'Papagaio',
      'Cachorro',
      'Frango',
      'Lagarto',
      'Gato',
      'Touro',
      'Vaca',
      'Sapo',
      'Urso',
      'Raposa',
    ],
  };
  const wordsNumber = 12;
  const totalNumbers = 6;
  const specialChars = '!@#$%&*';

  let langWords: string[] = [];
  switch (T.locale) {
    case 'es':
      langWords = words.es;
      break;
    case 'pt':
      langWords = words.pt;
      break;
    default:
      langWords = words.en;
  }
  let password = '';
  for (let i = 0; i < wordsNumber; i++) {
    password += langWords[Math.floor(Math.random() * langWords.length)];
  }
  for (let i = 0; i < totalNumbers; i++) {
    password += Math.floor(Math.random() * 10); // random numbers
  }
  // Add a special character to meet password strength requirements
  password +=
    specialChars[Math.floor(Math.random() * specialChars.length)];
  return password;
}
