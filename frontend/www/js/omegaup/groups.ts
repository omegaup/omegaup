import { types } from './api_types';
import T from './lang';
import * as CSV from '@/third_party/js/csv.js/csv.js';

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
  const fields: { id: string }[] = [];
  for (const column of columns) {
    fields.push({ id: column });
  }
  const csv = CSV.serialize({ fields, records }, dialect);
  const hiddenElement = document.createElement('a');
  hiddenElement.href = `data:text/csv;charset=utf-8,${window.encodeURIComponent(
    csv,
  )}`;
  hiddenElement.target = '_blank';
  hiddenElement.download = fileName;
  hiddenElement.click();
}

export function getFieldsObject(
  fields: string[],
  records: (null | number | string)[][],
): { [key: string]: null | number | string }[] {
  const result: { [key: string]: null | number | string }[] = [];
  for (const record in records) {
    const row: { [key: string]: null | number | string } = {};
    for (const field in fields) {
      row[fields[field]] = records[record][field];
    }
    result.push(row);
  }
  return result;
}

export function fieldsMatch(a: string[], b: string[]) {
  return a.length === b.length && a.every((val, index) => val === b[index]);
}

export function cleanRecords(
  records: { [key: string]: null | number | string }[],
): { [key: string]: undefined | string }[] {
  const cleanRecords: { [key: string]: undefined | string }[] = [];
  records.forEach((record) => {
    const cleanRecord: { [key: string]: undefined | string } = {};
    for (const [index, cell] of Object.entries(record)) {
      if (cell === null) {
        cleanRecord[index] = undefined;
        continue;
      }
      if (typeof cell !== 'string') {
        cleanRecord[index] = String(cell);
        continue;
      }
      cleanRecord[index] = cell;
    }
    cleanRecords.push(cleanRecord);
  });
  return cleanRecords;
}

export function generatePassword(): string {
  const validChars = 'acdefhjkmnpqruvwxyACDEFHJKLMNPQRUVWXY346';
  const len = 8;
  // Browser supports window.crypto
  if (typeof window.crypto == 'object') {
    const arr = new Uint8Array(2 * len);
    window.crypto.getRandomValues(arr);
    return Array.from(
      arr.filter((value) => value <= 255 - (255 % validChars.length)),
      (value) => validChars[value % validChars.length],
    )
      .join('')
      .substr(0, len);
  }

  // Browser does not support window.crypto
  let password = '';
  for (let i = 0; i < len; i++) {
    password += validChars.charAt(
      Math.floor(Math.random() * validChars.length),
    );
  }
  return password;
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
  return password;
}
