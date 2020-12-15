declare module '@/third_party/js/csv.js/csv.js' {
  import * as $ from 'jquery';
  interface Dialect {
    dialect: {
      csvddfVersion: number;
      delimiter: string;
      doubleQuote: boolean;
      lineTerminator: string;
      quoteChar: string;
      skipInitialSpace: boolean;
      header: boolean;
      commentChar: string;
    };
  }
  type Source =
    | {
        file: File;
        encoding?: string;
      }
    | {
        url: string;
      }
    | {
        data: string;
      };
  export interface Dataset {
    fields?: string[];
    records: (null | string)[][];
  }

  export function fetch(source: Source): $.Promise;
  export function serialize(dataToSerialize: any, dialect: Dialect): string;
}
