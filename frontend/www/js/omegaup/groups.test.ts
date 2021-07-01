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
    const requiredFields = new Set(fields);
    const records: (null | string | number)[][] = [
      ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', null],
      ['username-2', null, 'MX', 'QUE', 'male', 'Best School'],
    ];
    const expectedFormattedRecords: GroupCSVDatasetRecord[] = [
      {
        username: 'username-1',
        name: 'Developer Diana',
        country_id: 'MX',
        state_id: 'AGU',
        gender: 'female',
        school_name: undefined,
      },
      {
        username: 'username-2',
        name: undefined,
        country_id: 'MX',
        state_id: 'QUE',
        gender: 'male',
        school_name: 'Best School',
      },
    ];

    it('Should clean all null cells', () => {
      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields,
        records,
        requiredFields,
      });

      expect(formattedRecords).toEqual(expectedFormattedRecords);
    });

    it('Should parse all the cells to string', () => {
      records[0][2] = 4;
      records[0][5] = 'Best School';
      records[1][0] = 2;
      records[1][1] = 'Dev Diane';
      expectedFormattedRecords[0].country_id = '4';
      expectedFormattedRecords[0].school_name = 'Best School';
      expectedFormattedRecords[1].username = '2';
      expectedFormattedRecords[1].name = 'Dev Diane';

      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields,
        records,
        requiredFields,
      });

      expect(formattedRecords).toEqual(expectedFormattedRecords);
    });

    it('Should ignore extra fields that are not required nor optional', () => {
      const formattedRecords = getCSVRecords<GroupCSVDatasetRecord>({
        fields: fields.concat(['birthday']),
        records,
        requiredFields,
      });

      expect(formattedRecords).toEqual(formattedRecords);
    });

    it('Should throw an error when required fields are missing', () => {
      fields.splice(2, 2);
      expect(() =>
        getCSVRecords<GroupCSVDatasetRecord>({
          fields,
          records,
          requiredFields,
        }),
      ).toThrow(
        ui.formatString(T.teamsGroupsErrorFieldIsNotPresentInCsv, {
          missingFields: 'country_id,state_id',
        }),
      );
    });
  });
});
