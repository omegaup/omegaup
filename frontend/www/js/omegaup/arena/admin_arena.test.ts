jest.mock('../../../third_party/js/diff_match_patch.js');

import * as arena from './arena';
import ArenaAdmin from './admin_arena';
import { OmegaUp } from '../omegaup';

describe('arena', () => {
  describe('ArenaAdmin', () => {
    beforeEach(() => {
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

      const arenaInstance = new arena.Arena(options);
      new ArenaAdmin(arenaInstance);
      expect(arenaInstance.problemsetAdmin).toEqual(true);
    });
  });
});
