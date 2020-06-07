import expect from 'expect';

import * as arena from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';

describe('arena', () => {
  describe('ArenaAdmin', () => {
    before(() => {
      // Create the mountpoint for the arena.Runs component.
      const runsDiv = document.createElement('div');
      runsDiv.id = 'runs';

      const runsTable = document.createElement('table');
      runsTable.className = 'runs';

      runsDiv.appendChild(runsTable);
      document.body.appendChild(runsDiv);

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
