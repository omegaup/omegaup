import { CSVDatasetRecord } from './group/edit';
import {
  generatePassword,
  generateHumanReadablePassword,
  getCSVRecords,
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
    const fields = [
      'alias',
      'name',
      'country_id',
      'state_id',
      'gender',
      'school_name',
    ];

    const expectedFields = [
      'alias',
      'name',
      'country_id',
      'state_id',
      'gender',
      'school_name',
    ];

    it('Should clean all null cells', () => {
      const records = [
        ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', null],
        ['username-2', null, 'MX', 'QUE', 'male', 'Best School'],
      ];

      const formattedRecords = getCSVRecords<CSVDatasetRecord>(
        fields,
        records,
        expectedFields,
      );

      expect(formattedRecords).toEqual([
        {
          alias: 'username-1',
          name: 'Developer Diana',
          country_id: 'MX',
          state_id: 'AGU',
          gender: 'female',
          school_name: undefined,
        },
        {
          alias: 'username-2',
          name: undefined,
          country_id: 'MX',
          state_id: 'QUE',
          gender: 'male',
          school_name: 'Best School',
        },
      ]);
    });

    it('Should parse all the cells to string', () => {
      const records = [
        ['username-1', 'Developer Diana', 4, 'AGU', 'female', 'Best School'],
        [2, 'Dev Diane', 'MX', 'QUE', 'male', 'Best School'],
      ];

      const formattedRecords = getCSVRecords<CSVDatasetRecord>(
        fields,
        records,
        expectedFields,
      );

      expect(formattedRecords).toEqual([
        {
          alias: 'username-1',
          name: 'Developer Diana',
          country_id: '4',
          state_id: 'AGU',
          gender: 'female',
          school_name: 'Best School',
        },
        {
          alias: '2',
          name: 'Dev Diane',
          country_id: 'MX',
          state_id: 'QUE',
          gender: 'male',
          school_name: 'Best School',
        },
      ]);
    });
  });
});
