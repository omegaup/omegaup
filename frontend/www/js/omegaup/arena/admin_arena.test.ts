import expect from 'expect';

import * as arena from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';

describe('arena', () => {
  describe('ArenaAdmin', () => {
    before(() => {
      OmegaUp.ready = true;
    });

    it('can be instantiated', () => {
      const options = arena.GetDefaultOptions();
      options.contestAlias = 'test';

      const arenaInstance = new arena.Arena(options);
      const adminInstance = new ArenaAdmin(arenaInstance);
      expect(arenaInstance.problemsetAdmin).toEqual(true);
    });
  });
});
