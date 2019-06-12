declare module '../../third_party/js/iso-3166-2.js/iso3166.min.js' {
  interface Subdivision {
    type: string;
    name: string;
  }
  interface Subdivisions {
    [code: string]: Subdivision;
  }
  interface Country {
    name: string;
    sub: Subdivisions;
  }

  export function country(name: string): Country;

  export function subdivision(type:string, name: string): Subdivision;
}