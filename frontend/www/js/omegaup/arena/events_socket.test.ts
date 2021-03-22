jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import * as socket from './events_socket';
import { SocketStatus } from './events_socket';

describe('socket', () => {
  describe('EventsSocket', () => {
    beforeEach(() => {
      OmegaUp.ready = true;
    });

    it('can be instantiated', () => {
      const navbarProblems: types.NavbarProblemsetProblem[] = [
        {
          acceptsSubmissions: true,
          alias: 'problem_alias',
          bestScore: 100,
          hasRuns: true,
          maxScore: 100,
          text: 'A. Problem',
        },
        {
          acceptsSubmissions: true,
          alias: 'problem_alias_2',
          bestScore: 80,
          hasRuns: true,
          maxScore: 100,
          text: 'B. Problem 2',
        },
      ];

      const options = {
        disableSockets: false,
        problemsetAlias: 'hello',
        locationProtocol: 'https',
        locationHost: 'localhost',
        problemsetId: 1,
        scoreboardToken: 'token',
        socketStatus: SocketStatus.Waiting,
        clarificationInterval: null,
        rankingInterval: null,
        clarificationsOffset: 0,
        clarificationsRowcount: 20,
        navbarProblems: navbarProblems,
        currentUsername: 'omegaUp',
      };
      const socketInstance = new socket.EventsSocket(options);
      expect(socketInstance.shouldRetry).toEqual(false);
      expect(socketInstance.retries).toEqual(10);
      expect(socketInstance.socketStatus).toEqual(SocketStatus.Waiting);
    });
  });
});
