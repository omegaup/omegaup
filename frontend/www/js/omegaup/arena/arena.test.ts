import expect from 'expect';

import * as arena from './arena';
import { OmegaUp } from '../omegaup';

describe('arena', () => {
  describe('GetOptionsFromLocation', () => {
    it('Should detect normal contests', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/test/'),
      );
      expect(options.contestAlias).toEqual('test');
      expect(options.isPractice).toEqual(false);
      expect(options.isOnlyProblem).toEqual(false);
      expect(options.disableClarifications).toEqual(false);
      expect(options.disableSockets).toEqual(false);
      expect(options.scoreboardToken).toEqual(null);
      expect(options.shouldShowFirstAssociatedIdentityRunWarning).toEqual(
        false,
      );
    });

    it('Should detect practice mode', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/test/practice'),
      );
      expect(options.contestAlias).toEqual('test');
      expect(options.isPractice).toEqual(true);
    });

    it('Should detect only problems', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/problem/test/'),
      );
      expect(options.contestAlias).toEqual(null);
      expect(options.onlyProblemAlias).toEqual('test');
      expect(options.isOnlyProblem).toEqual(true);
    });

    it('Should detect ws=off', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/test/?ws=off'),
      );
      expect(options.disableSockets).toEqual(true);
    });
  });

  describe('Arena', () => {
    beforeEach(() => {
      OmegaUp.ready = true;
    });

    it('can be instantiated', () => {
      const options = arena.GetDefaultOptions();

      const arenaInstance = new arena.Arena(options);
      expect(arenaInstance.problemsetAdmin).toEqual(false);
    });
  });
});
