declare module '@/third_party/js/csv.js/csv.js' {
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
    records: (null | number | string)[][];
  }

  export function fetch(source: Source): Promise<Dataset>;
  export function serialize(dataToSerialize: any, dialect: Dialect): string;
}
