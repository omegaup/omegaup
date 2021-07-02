import { GroupCSVDatasetRecord } from './group/edit';
import {
  generatePassword,
  generateHumanReadablePassword,
  getCSVRecords,
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
    const requiredFields = [
      'username',
      'name',
      'country_id',
      'state_id',
      'school_name',
    ];
    const requiredFieldsSet = new Set(requiredFields);
    const optionalFields = ['gender'];
    const optionalFieldsSet = new Set(optionalFields);
    const records: (null | string | number)[][] = [
      ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', 'Best School'],
      ['username-2', 'Dev Diane', 'MX', 'QUE', null, 'Best School'],
    ];
    const expectedFormattedRecords: { [key: string]: string }[] = [
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
      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields,
        records,
        requiredFields: requiredFieldsSet,
        optionalFields: optionalFieldsSet,
      });

      expect(formattedRecords).toEqual(expectedFormattedRecords);
    });

    it('Should throw error for null cells in required fields', () => {
      records[0][1] = null;
      delete expectedFormattedRecords[0].name;

      expect(() =>
        getCSVRecords<GroupCSVDatasetRecord>({
          fields,
          records,
          requiredFields: requiredFieldsSet,
          optionalFields: optionalFieldsSet,
        }),
      ).toThrow(
        ui.formatString(T.teamsGroupsErrorFieldIsRequired, {
          field: 'name',
        }),
      );
    });

    it('Should parse all the cells to string', () => {
      records[0][1] = 2;
      expectedFormattedRecords[0].name = '2';

      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields,
        records,
        requiredFields: requiredFieldsSet,
        optionalFields: optionalFieldsSet,
      });

      expect(formattedRecords).toEqual(expectedFormattedRecords);
    });

    it('Should ignore extra fields that are not required nor optional', () => {
      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields: fields.concat(['birthday']),
        records,
        requiredFields: requiredFieldsSet,
        optionalFields: optionalFieldsSet,
      });

      expect(formattedRecords).toEqual(formattedRecords);
    });

    it('Should throw an error when required fields are missing', () => {
      fields.splice(2, 2);
      expect(() =>
        getCSVRecords<GroupCSVDatasetRecord>({
          fields,
          records,
          requiredFields: requiredFieldsSet,
          optionalFields: optionalFieldsSet,
        }),
      ).toThrow(
        ui.formatString(T.teamsGroupsErrorFieldIsNotPresentInCsv, {
          missingFields: 'country_id,state_id',
        }),
      );
    });
  });
});
