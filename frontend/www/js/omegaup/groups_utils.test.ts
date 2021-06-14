import {
  generatePassword,
  generateHumanReadablePassword,
  cleanRecords,
} from './groups_utils';

describe('groups_utils', () => {
  describe('generatePassword', () => {
    const simplePasswordLength = 8;

    it('Should be human-readable (greater than 8 characters)', () => {
      const password = generateHumanReadablePassword();
      expect(password.length).toBeGreaterThan(simplePasswordLength);
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
      ]) as string[][];

      expect(records).toEqual([
        ['username-1', 'Developer Diana', 'MX', 'AGU', 'female', undefined],
        ['username-2', undefined, 'MX', 'QUE', 'male', 'Best School'],
      ]);
    });
  });
});
