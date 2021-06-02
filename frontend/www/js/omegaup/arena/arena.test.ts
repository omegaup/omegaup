jest.mock('../../../third_party/js/diff_match_patch.js');

import * as arena from './arena';
import { OmegaUp } from '../omegaup';
import { GetOptionsFromLocation } from './arena';
import fetchMock from 'jest-fetch-mock';

describe('arena', () => {
  describe('GetOptionsFromLocation', () => {
    it('Should detect normal contests', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/test/'),
      );
      expect(options.contestAlias).toEqual('test');
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
    });

    it('Should detect only problems', () => {
      const options = arena.GetOptionsFromLocation(
        new window.URL('http://localhost:8001/arena/problem/test/'),
      );
      expect(options.contestAlias).toEqual(null);
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

    it('should load problemset', () => {
      const now = Date.now();
      const serverTime = now - 3600;
      fetchMock.enableMocks();
      fetchMock.mockIf(/^\/api\/.*/, (req: Request) => {
        if (req.url == '/api/session/currentSession/') {
          return Promise.resolve({
            status: 200,
            body: JSON.stringify({
              status: 'ok',
              session: {
                valid: false,
              },
              time: serverTime,
            }),
          });
        }
        if (req.url == '/api/contest/details/') {
          return Promise.resolve({
            status: 200,
            body: JSON.stringify({
              status: 'ok',
              start_time: serverTime,
              finish_time: serverTime + 3600,
            }),
          });
        }
        if (req.url == '/api/problemset/scoreboard/') {
          return Promise.resolve({
            status: 200,
            body: JSON.stringify({
              status: 'ok',
              start_time: serverTime,
              finish_time: serverTime + 3600,
            }),
          });
        }
        if (req.url == '/api/contest/clarifications/') {
          return Promise.resolve({
            status: 200,
            body: JSON.stringify({
              status: 'ok',
            }),
          });
        }
        return Promise.resolve({
          ok: false,
          status: 404,
          body: JSON.stringify({
            status: 'error',
            error: `Invalid call to "${req.url}" in test`,
            errorcode: 403,
          }),
        });
      });

      let dateNowSpy: jest.SpyInstance<number, []> | null = null;
      dateNowSpy = jest.spyOn(Date, 'now').mockImplementation(() => now);
      jest.useFakeTimers();
      const arenaInstance = new arena.Arena(
        GetOptionsFromLocation(
          new window.URL('http://localhost:8001/arena/test/'),
        ),
      );
      arenaInstance.problemsetLoaded({
        start_time: new Date(serverTime),
        finish_time: new Date(serverTime + 3600),
      });
      jest.runOnlyPendingTimers();
      jest.useRealTimers();
      dateNowSpy.mockRestore();
    });
  });
});
