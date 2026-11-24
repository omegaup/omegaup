import latinize from 'latinize';

export function generateAlias(name: string, aliasLength: number = 32): string {
  // Remove accents
  let generatedAlias = latinize(name);

  // Replace whitespace
  generatedAlias = generatedAlias.replace(/\s+/g, '-');

  // Remove invalid characters
  generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');

  return generatedAlias.substring(0, aliasLength);
}
