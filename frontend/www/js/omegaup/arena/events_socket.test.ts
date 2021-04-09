jest.mock('../../../third_party/js/diff_match_patch.js');

import { types } from '../api_types';
import { OmegaUp } from '../omegaup';
import * as socket from './events_socket';
import { SocketStatus } from './events_socket';
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

    it('should handle socket when is closed', async () => {
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

      const mySocket = new socket.EventsSocket(
        Object.assign({}, options, { disableSockets: false }),
      );

      expect(mySocket.shouldRetry).toEqual(false);
      expect(mySocket.retries).toEqual(10);
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      mySocket.connect();
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      WS.clean();
    });

    it('should handle socket when send mesages', async () => {
      const server = new WS('ws://localhost:1234');
      const client = new WebSocket('ws://localhost:1234');

      await server.connected;
      client.send('hello');
      server.send('hello everyone');
      await expect(server).toReceiveMessage('hello');
      expect(server).toHaveReceivedMessages(['hello']);

      const messages: { client: string[] } = { client: [] };
      client.onmessage = (e: MessageEvent) => {
        messages.client.push(e.data);
      };

      server.send('hello everyone');
      expect(messages).toEqual({
        client: ['hello everyone'],
      });

      const mySocket = new socket.EventsSocket(
        Object.assign({}, options, { disableSockets: false }),
      );

      expect(mySocket.shouldRetry).toEqual(false);
      expect(mySocket.retries).toEqual(10);
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      mySocket.connect();
      expect(mySocket.socketStatus).toEqual(SocketStatus.Waiting);

      WS.clean();
    });
  });
});
