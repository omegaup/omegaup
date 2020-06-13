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
  export function serialize(dataToSerialize: any, dialect: Dialect): string;
}
