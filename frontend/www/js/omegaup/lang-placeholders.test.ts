import en from './lang.en.json';
import es from './lang.es.json';
import pt from './lang.pt.json';

const PLACEHOLDER = /%\([^()]+\)/g;

/**
 * Returns the `%(name)` placeholders in a translation. Duplicates are kept
 * because every occurrence needs a value to interpolate.
 */
function placeholders(value: string): string[] {
  return (value.match(PLACEHOLDER) ?? []).sort();
}

const locales: [string, Record<string, string>][] = [
  ['es', es],
  ['pt', pt],
];

describe('translation placeholders', () => {
  it.each(locales)(
    '%s keeps every placeholder that en defines',
    (_locale, translations) => {
      const mismatches: string[] = [];
      for (const [key, enValue] of Object.entries(en)) {
        const translated = translations[key];
        if (translated === undefined) {
          continue;
        }
        const expected = placeholders(enValue);
        const actual = placeholders(translated);
        if (JSON.stringify(expected) !== JSON.stringify(actual)) {
          mismatches.push(
            `${key}: expected [${expected.join(', ')}], got [${actual.join(
              ', ',
            )}]`,
          );
        }
      }
      expect(mismatches).toEqual([]);
    },
  );

  it.each(locales)(
    '%s has no malformed placeholder',
    (_locale, translations) => {
      const malformed: string[] = [];
      for (const [key, translated] of Object.entries(translations)) {
        // A placeholder split by whitespace or left without its closing
        // parenthesis is never interpolated, so the user sees it literally.
        const opening = /%(\s*)\(/g;
        let match: RegExpExecArray | null;
        while ((match = opening.exec(translated)) !== null) {
          const rest = translated.slice(match.index);
          if (!/^%\([^()]+\)/.test(rest)) {
            malformed.push(`${key}: ${rest.slice(0, 40)}`);
          }
        }
      }
      expect(malformed).toEqual([]);
    },
  );
});
