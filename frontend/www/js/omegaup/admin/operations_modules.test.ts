import {
  AccessLevel,
  AvailableModules,
  getModuleFromHash,
  hasAccessLevel,
} from './operations_modules';

describe('operations_modules', () => {
  describe('getModuleFromHash', () => {
    it('Should return the module for a valid hash', () => {
      expect(getModuleFromHash('#home')).toBe(AvailableModules.Home);
      expect(getModuleFromHash('#settings')).toBe(AvailableModules.Settings);
    });

    it('Should fall back to home when there is no hash', () => {
      expect(getModuleFromHash('')).toBe(AvailableModules.Home);
      expect(getModuleFromHash('#')).toBe(AvailableModules.Home);
    });

    it('Should fall back to home for an invalid hash', () => {
      expect(getModuleFromHash('#nonexistent')).toBe(AvailableModules.Home);
      expect(getModuleFromHash('#Settings')).toBe(AvailableModules.Home);
      expect(getModuleFromHash('#settings-extra')).toBe(AvailableModules.Home);
    });

    it('Should ignore anything after a second hash', () => {
      expect(getModuleFromHash('#settings#extra')).toBe(
        AvailableModules.Settings,
      );
    });
  });

  describe('hasAccessLevel', () => {
    it('Should let admins access every level', () => {
      expect(hasAccessLevel(AccessLevel.Admin, AccessLevel.Admin)).toBe(true);
      expect(hasAccessLevel(AccessLevel.Admin, AccessLevel.Support)).toBe(true);
    });

    it('Should only let support access support-level modules', () => {
      expect(hasAccessLevel(AccessLevel.Support, AccessLevel.Support)).toBe(
        true,
      );
      expect(hasAccessLevel(AccessLevel.Support, AccessLevel.Admin)).toBe(
        false,
      );
    });
  });
});
