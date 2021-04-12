jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import { SocketOptions, SocketStatus, EventsSocket } from './events_socket';
import WS from 'jest-websocket-mock';

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

  const options: SocketOptions = {
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
      const socket = new EventsSocket(options);
      expect(socket.shouldRetry).toEqual(false);
      expect(socket.retries).toEqual(10);
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      socket.connect();
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);
    });

    it('should handle socket when it is disabled', () => {
      const socket = new EventsSocket({ ...options, disableSockets: true });
      expect(socket.shouldRetry).toEqual(false);
      expect(socket.retries).toEqual(10);
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      socket.connect();
      expect(socket.socketStatus).toEqual(SocketStatus.Failed);
    });

    it('should handle socket when it is closed', async () => {
      const server = new WS('ws://localhost:1234');
      server.on('connection', (socket) => {
        socket.close({ wasClean: false, code: 1003, reason: 'any' });
      });
      const client = new WebSocket('ws://localhost:1234');
      client.onclose = (event: CloseEvent) => {
        expect(event.code).toBe(1003);
        expect(event.wasClean).toBe(false);
        expect(event.reason).toBe('any');
      };

      expect(client.readyState).toBe(WebSocket.CONNECTING);

      await server.connected;
      expect(client.readyState).toBe(WebSocket.CLOSING);

      await server.closed;
      expect(client.readyState).toBe(WebSocket.CLOSED);

      const socket = new EventsSocket(
        Object.assign({}, options, { disableSockets: false }),
      );

      expect(socket.shouldRetry).toEqual(false);
      expect(socket.retries).toEqual(10);
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      socket.connect();
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      WS.clean();
    });

    it('should handle socket when it sends messages', async () => {
      const socket = new EventsSocket(
        Object.assign({}, options, { disableSockets: false }),
      );

      expect(socket.shouldRetry).toEqual(false);
      expect(socket.retries).toEqual(10);
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      socket.connect();
      expect(socket.socketStatus).toEqual(SocketStatus.Waiting);

      WS.clean();
    });
  });
});
