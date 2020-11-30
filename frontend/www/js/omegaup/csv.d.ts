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
  interface Source {
    file: File | null;
    encoding?: string;
    url?: string;
    data?: any;
  }
  export interface Dataset {
    fields: Array<string>;
    records: Array<any>;
  }

  interface Promise<T> {
    done(arg0: (dataset: Dataset) => void): Dataset;
  }
  export function serialize(dataToSerialize: any, dialect: Dialect): string;
  export function fetch<T>(dataset: Source): Promise<T>;
}
