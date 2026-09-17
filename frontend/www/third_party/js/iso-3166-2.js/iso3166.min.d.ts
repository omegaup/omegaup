export as namespace iso3166;

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

export function country(code: string): Country;
export function subdivision(country:string, code: string): Subdivision;

interface CountryMapping {
  [code: string]: Country;
}
export const data: CountryMapping;
interface CodeMapping {
  [code: string]: string;
}
export const codes: CodeMapping;
