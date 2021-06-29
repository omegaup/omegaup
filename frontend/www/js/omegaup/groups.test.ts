import {
  generatePassword,
  generateHumanReadablePassword,
  cleanRecords,
} from './groups';
import T from './lang';

describe('groups_utils', () => {
  describe('generatePassword', () => {
    const simplePasswordLength = 8;

    it('Should be human-readable (greater than 8 characters)', () => {
      const password = generateHumanReadablePassword();
      expect(password.length).toBeGreaterThan(simplePasswordLength);
    });

    it('Should be human-readable for english language', () => {
      T.locale = 'en';
      const password = generateHumanReadablePassword();
      const words = [
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
        'Papagaio',
        'Cachorro',
        'Frango',
        'Touro',
        'Urso',
        'Raposa',
      ];
      for (const word of words) {
        expect(password).not.toContain(word);
      }
    });

    it('Should be human-readable for portuguese language', () => {
      T.locale = 'pt';
      const password = generateHumanReadablePassword();
      const words = [
        'Loro',
        'Perro',
        'Pollo',
        'Toro',
        'Oso',
        'Zorro',
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
      ];
      for (const word of words) {
        expect(password).not.toContain(word);
      }
    });

    it("Shouldn't be human-readable (equal to 8 characters)", () => {
      const password = generatePassword();
      expect(password.length).toBe(simplePasswordLength);
    });
  });

  describe('cleanRecords', () => {
    it('Should clean all null cells', () => {
      const records = cleanRecords([
        ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', null],
        ['username-2', null, 'MX', 'QUE', 'male', 'Best School'],
      ]);

      expect(records).toEqual([
        ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', undefined],
        ['username-2', undefined, 'MX', 'QUE', 'male', 'Best School'],
      ]);
    });

    it('Should parse all the cells to string', () => {
      const records = cleanRecords([
        ['username-1', 'Developer Diana', 4, 'AGU', 'female', 'Best School'],
        [2, 'Dev Diane', 'MX', 'QUE', 'male', 'Best School'],
      ]);

      expect(records).toEqual([
        ['username-1', 'Developer Diana', '4', 'AGU', 'female', 'Best School'],
        ['2', 'Dev Diane', 'MX', 'QUE', 'male', 'Best School'],
      ]);
    });
  });
});
