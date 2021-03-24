jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import * as socket from './events_socket';
import { SocketStatus } from './events_socket';

describe('socket', () => {
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
    clarificationsOffset: 1,
    clarificationsRowcount: 30,
    navbarProblems: navbarProblems,
    currentUsername: 'omegaUp',
  };
  describe('EventsSocket', () => {
    beforeEach(() => {
      OmegaUp.ready = true;
    });

    it('can be instantiated', () => {
      const mySocket = new socket.EventsSocket(options);
      expect(mySocket.shouldRetry).toEqual(false);
      expect(mySocket.retries).toEqual(10);
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      mySocket.connect();
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);
    });

    it('should handle socket when is disabled', () => {
      const mySocket = new socket.EventsSocket(
        Object.assign({}, options, { disableSockets: true }),
      );
      expect(mySocket.shouldRetry).toEqual(false);
      expect(mySocket.retries).toEqual(10);
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      mySocket.connect();
      expect(mySocket.socketStatus).toEqual(SocketStatus.Failed);
    });
  });
});
