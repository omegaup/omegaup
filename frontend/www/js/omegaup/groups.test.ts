import { types } from './api_types';
import {
  generatePassword,
  generateHumanReadablePassword,
  getCSVRecords,
  identityRequiredFields,
  identityOptionalFields,
} from './groups';
import T from './lang';
import * as ui from './ui';

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
      expect(password).toMatch(/[a-z]/);
      expect(password).toMatch(/[A-Z]/);
      expect(password).toMatch(/[0-9]/);
      expect(password).toMatch(/[!@#$%&*]/);
    });
  });

  describe('getCSVRecords', () => {
    const fields = [
      'username',
      'name',
      'country_id',
      'state_id',
      'gender',
      'school_name',
    ];
    const records: (null | string | number)[][] = [
      ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', 'Best School'],
      ['username-2', 'Dev Diane', 'MX', 'QUE', null, 'Best School'],
    ];
    const expectedFormattedRecords: types.Identity[] = [
      {
        username: 'username-1',
        name: 'Developer Diana',
        country_id: 'MX',
        state_id: 'AGU',
        gender: 'female',
        school_name: 'Best School',
      },
      {
        username: 'username-2',
        name: 'Dev Diane',
        country_id: 'MX',
        state_id: 'QUE',
        school_name: 'Best School',
      },
    ];

    it('Should clean all null cells for optional fields', () => {
      const formattedRecords = getCSVRecords<types.Identity>({
        fields,
        records,
        requiredFields: identityRequiredFields,
        optionalFields: identityOptionalFields,
      });

      expect(formattedRecords).toEqual(expectedFormattedRecords);
    });

    it('Should parse all the cells to string', () => {
      const localRecords = JSON.parse(JSON.stringify(records));
      localRecords[0][1] = 2;
      const localExpectedFormattedRecords = JSON.parse(
        JSON.stringify(expectedFormattedRecords),
      );
      localExpectedFormattedRecords[0].name = '2';

      const formattedRecords = getCSVRecords<types.Identity>({
        fields,
        records: localRecords,
        requiredFields: identityRequiredFields,
        optionalFields: identityOptionalFields,
      });

      expect(formattedRecords).toEqual(localExpectedFormattedRecords);
    });

    it('Should throw error for null cells in required fields', () => {
      const localRecords = JSON.parse(JSON.stringify(records));
      localRecords[0][0] = null;
      const localExpectedFormattedRecords = JSON.parse(
        JSON.stringify(expectedFormattedRecords),
      );
      delete localExpectedFormattedRecords[0].username;

      expect(() =>
        getCSVRecords<types.Identity>({
          fields,
          records: localRecords,
          requiredFields: identityRequiredFields,
          optionalFields: identityOptionalFields,
        }),
      ).toThrow(
        ui.formatString(T.teamsGroupsErrorFieldIsRequired, {
          field: 'username',
        }),
      );
    });

    it('Should ignore extra fields that are not required nor optional', () => {
      const formattedRecords = getCSVRecords<types.Identity>({
        fields: fields.concat(['birthday']),
        records,
        requiredFields: identityRequiredFields,
        optionalFields: identityOptionalFields,
      });

      expect(formattedRecords).toEqual(formattedRecords);
    });

    it('Should throw an error when required fields are missing', () => {
      const localFields = JSON.parse(JSON.stringify(fields));
      localFields.splice(0, 1);

      expect(() =>
        getCSVRecords<types.Identity>({
          fields: localFields,
          records,
          requiredFields: identityRequiredFields,
          optionalFields: identityOptionalFields,
        }),
      ).toThrow(
        ui.formatString(T.teamsGroupsErrorFieldIsNotPresentInCsv, {
          missingFields: 'username',
        }),
      );
    });
  });
});
