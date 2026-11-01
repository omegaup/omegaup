import en from './lang.en.json';
import es from './lang.es.json';
import pt from './lang.pt.json';

const PLACEHOLDER = /%\([A-Za-z0-9_-]+\)/g;

/** Returns the set of `%(name)` placeholders used in a translation. */
function placeholders(value: string): Set<string> {
  return new Set(value.match(PLACEHOLDER) ?? []);
}

describe('translation placeholders', () => {
  it.each([
    ['es', es],
    ['pt', pt],
  ])('%s keeps every placeholder that en defines', (locale, translations) => {
    const values: Record<string, string> = translations;
    const missing: string[] = [];
    for (const [key, enValue] of Object.entries(en)) {
      const translated = values[key];
      if (translated === undefined) {
        continue;
      }
      for (const placeholder of placeholders(enValue)) {
        if (!translated.includes(placeholder)) {
          missing.push(`${key}: ${placeholder}`);
        }
      }
    }
    expect(missing).toEqual([]);
  });

  it.each([
    ['es', es],
    ['pt', pt],
  ])('%s has no malformed placeholder', (locale, translations) => {
    const values: Record<string, string> = translations;
    const malformed: string[] = [];
    for (const [key, translated] of Object.entries(values)) {
      // A placeholder split by whitespace is never interpolated, so the value
      // reaches the user literally.
      if (/%\s+\(/.test(translated)) {
        malformed.push(key);
      }
    }
    expect(malformed).toEqual([]);
  });
});
